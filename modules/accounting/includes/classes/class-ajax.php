<?php
namespace WeDevs\ERP\Accounting;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;
use WP_Error;

/**
 * Ajax handler
 */
class Ajax_Handler {
    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for Accounting
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function __construct() {

        // AJAX hooks for Settings Actions
        $this->action( 'wp_ajax_erp-settings-get-ac-financial-years', 'get_accounting_financial_years' );
        $this->action( 'wp_ajax_erp-settings-ac-financial-years-save', 'save_accounting_financial_years_settings' );
    }


    /**
     * Get Accouting Financial Years for settings
     *
     * @return array
     */
    public function get_accounting_financial_years() {

        $this->verify_nonce( 'erp-settings-nonce' );

        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_ac_manager' ) ) {
            $this->send_error( erp_get_message ( ['type' => 'error_permission'] ) );
        }

        $years = erp_acct_get_opening_balance_names();

        $this->send_success( $years );
    }


    /**
     * Save Financial Year settings
     *
     * @param int   $id
     * @param array $data
     *
     * @return bool
     */
    public function save_accounting_financial_years_settings() {

        $this->verify_nonce( 'erp-settings-nonce' );

        if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'erp_ac_manager' ) ) {
            $this->send_error( erp_get_message ( ['type' => 'error_permission'] ) );
        }

        global $wpdb;

        $post_data  = $_POST['fyears'];
        $year_names = [];

        foreach ( $post_data as $key => $data ) {
            if ( empty( $data['name'] ) ) {
                $this->send_error( __( 'Please give a financial year name on row #' . ( $key + 1 ), 'erp' ) );
            }
            if ( empty( $data['start_date'] ) ) {
                $this->send_error( __( 'Please give a financial year start date on row #' . ( $key + 1 ), 'erp' ) );
            }
            if ( empty( $data['end_date'] ) ) {
                $this->send_error( __( 'Please give a financial year end date on row #' . ( $key + 1 ), 'erp' ) );
            }
            if ( ( strtotime( $data['end_date'] ) < strtotime( $data['start_date'] ) ) || strtotime( $data['end_date'] ) === strtotime( $data['start_date'] )  ) {
                $this->send_error( __( 'End date must be greater than the first date on row #' . ( $key + 1 ), 'erp' ) );
            }

            if ( in_array( $data['name'], $year_names ) ) {
                $this->send_error( __( 'Duplicate financial year name ' . $data['name'] . ' on row #' . ( $key + 1 ), 'erp' ) );
            } else {
                array_push( $year_names, $data['name'] );
            }
        }

        // Empty accounting financial years data
        $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'erp_acct_financial_years' );

        // Insert accounting financial years
        foreach ( $post_data as $data ) {

            $data['name']        = sanitize_text_field( wp_unslash( $data['name'] ) );
            $data['start_date']  = sanitize_text_field( wp_unslash( $data['start_date'] ) );
            $data['end_date']    = sanitize_text_field( wp_unslash( $data['end_date'] ) );
            $data['description'] = sanitize_text_field( wp_unslash( $data['description'] ) );
            $data['created_by']  = get_current_user_id();
            $data['created_at']  = date( 'Y-m-d' );

            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_financial_years',
                $data,
                ['%s', '%s', '%s', '%s', '%d']
            );
        }

        $this->send_success( [
            'message' => erp_get_message ( ['type' => 'save_success', 'additional' => 'Financial Years'] )
        ] );
    }
}
