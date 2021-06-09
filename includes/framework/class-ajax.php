<?php

namespace WeDevs\ERP\Framework;

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
        $this->action( 'wp_ajax_erp-settings-save', 'erp_settings_save' );
        $this->action( 'wp_ajax_erp-settings-get-data', 'erp_settings_get_data' );
    }

    /**
     * Save Settings Data For HRM's sections
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_save() {
        $this->verify_nonce( 'erp-settings-nonce' );

        $has_not_permission = ! current_user_can( 'manage_options' );
        $module             = sanitize_text_field( wp_unslash( $_POST['module'] ) );
        $section            = sanitize_text_field( wp_unslash( $_POST['section'] ) );

        switch ( $module ) {
            case 'general':
                $settings           = ( new \WeDevs\ERP\Framework\Settings\ERP_Settings_General() );
                break;

            case 'erp-hr':
                $settings           = ( new \WeDevs\ERP\HRM\Settings() );
                $has_not_permission = $has_not_permission && ! current_user_can( 'erp_hr_manager' );
                break;

            default:
                $settings = ( new \WeDevs\ERP\Framework\ERP_Settings_Page() );
                break;
        }

        if ( $has_not_permission ) {
            $this->send_error( erp_get_message ( ['type' => 'error_permission'] ) );
        }

        $result = $settings->save( $section );

        if ( is_wp_error( $result ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_process'] ) );
        }

        $this->send_success( [
            'message' => erp_get_message( [ 'type' => 'save_success', 'additional' => 'Settings' ] )
        ] );
    }

    /**
     * Get Settings Data For Common Sections
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_get_data() {
        $this->verify_nonce( 'erp-settings-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_permission'] ) );
        }

        $data = erp_settings_get_data( $_POST );

        if ( is_wp_error( $data ) ) {
            $this->send_error( erp_get_message( ['type' => 'error_process'] ) );
        }

        $this->send_success( $data );
    }
}
