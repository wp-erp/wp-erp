<?php

namespace WeDevs\ERP\Framework;

use WeDevs\ERP\Framework\Settings\ERP_Settings_General;
use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax handler
 */
class Ajax_Handler {
    use Ajax;
    use Hooker;

    /**
     * Bind all the ajax event for Framework
     *
     * @since 1.8.4
     *
     * @return void
     */
    public function __construct () {
        add_action( 'admin_init', [ $this, 'init_actions' ] );
    }

    /**
     * Init all actions
     *
     * @since 1.8.4
     *
     * @return void
     */
    public function init_actions () {
        $this->action( 'wp_ajax_erp-settings-save', 'erp_settings_save_general' );
        $this->action( 'wp_ajax_erp-settings-get-data', 'erp_settings_get_general' );
        $this->action( 'wp_ajax_erp-settings-workdays-get-data', 'erp_settings_get_workdays' );
        $this->action( 'wp_ajax_erp-settings-workdays-save', 'erp_settings_save_workdays' );
        $this->action( 'wp_ajax_erp-settings-leave-get-data', 'erp_settings_get_leaves' );
        $this->action( 'wp_ajax_erp-settings-leave-save', 'erp_settings_save_leaves' );
        $this->action( 'wp_ajax_erp-settings-miscellaneous-get-data', 'erp_settings_get_miscellaneous' );
        $this->action( 'wp_ajax_erp-settings-miscellaneous-save', 'erp_settings_save_miscellaneous' );
        $this->action( 'wp_ajax_erp-settings-get-hr-financial-years', 'erp_settings_get_hr_financial_years' );
        $this->action( 'wp_ajax_erp-settings-financial-years-save', 'erp_settings_save_hr_financial_years' );
    }

    /**
     * Save Settings Data For General Tab
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_save_general() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $settings = new ERP_Settings_General();
            $settings->save();

            $this->send_success([
                'message' => __( 'Settings saved successfully !', 'erp' )
            ]);
        } catch ( \Exception $e ) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Get Settings Data For General Tab
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_get_general() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $data = erp_settings_get_general();

            $this->send_success( $data );
        } catch (\Exception $e) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Get Settings Data For HR Workdays Section
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_get_workdays() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $data = erp_settings_get_workdays();

            $this->send_success( $data );
        } catch (\Exception $e) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Save Settings Data For General Tab
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_save_workdays() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            erp_settings_save_workdays( $_POST );

            $this->send_success([
                'message' => __( 'Settings saved successfully !', 'erp' )
            ]);
        } catch ( \Exception $e ) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Get Settings Data For HR Workdays Section
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_get_leaves() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $data = erp_settings_get_leaves();

            $this->send_success( $data );
        } catch (\Exception $e) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Save Settings Data For General Tab
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_save_leaves() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            erp_settings_save_leaves( $_POST );

            $this->send_success([
                'message' => __( 'Settings saved successfully !', 'erp' )
            ]);
        } catch ( \Exception $e ) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Get Settings Data For Miscellaneous
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_get_miscellaneous() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $data = erp_settings_get_miscellaneous();

            $this->send_success( $data );
        } catch (\Exception $e) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Save Settings Data For Miscellaneous
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_save_miscellaneous() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            erp_settings_save_miscellaneous( $_POST );

            $this->send_success([
                'message' => __( 'Settings saved successfully !', 'erp' )
            ]);
        } catch ( \Exception $e ) {
            $this->send_error( $e->getMessage() );
        }
    }

    /**
     * Get Settings Data For HR Financial years
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_get_hr_financial_years() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $years = erp_get_hr_financial_years();

            $this->send_success( $years );
        } catch (\Exception $e) {
            $this->send_error( $e->getMessage() );
        }
    }

    public function erp_settings_save_hr_financial_years() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $inserted = erp_settings_save_leave_years( $_POST['fyears'] );

            if ( is_wp_error( $inserted ) ) {
                $this->send_error( __( $inserted->get_error_message(), 'erp' ) );
            }

            $this->send_success( [
                'data'    => $inserted,
                'message' => __( 'Settings saved successfully !', 'erp' )
            ] );
        } catch (\Exception $e) {
            $this->send_error( $e->getMessage() );
        }
    }
}
