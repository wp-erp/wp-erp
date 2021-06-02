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
        $this->action( 'wp_ajax_erp-settings-get-data', 'erp_settings_get_general' );
        $this->action( 'wp_ajax_erp-settings-save-data', 'erp_settings_save' );
    }

    /**
     * Save Settings Data For HRM's sections
     *
     * @since 1.8.6
     *
     * @return void
     */
    public function erp_settings_save() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $module  = sanitize_text_field( wp_unslash( $_POST['module'] ) );
            $section = sanitize_text_field( wp_unslash( $_POST['section'] ) );

            switch ( $module ) {
                case 'general':
                    $settings = ( new \WeDevs\ERP\Framework\Settings\ERP_Settings_General() );
                    break;

                case 'hrm':
                    $settings = ( new \WeDevs\ERP\HRM\Settings() );
                    break;

                default:
                    $settings = ( new \WeDevs\ERP\Framework\ERP_Settings_Page() );
                    break;
            }

            $settings->save( $section );

            $this->send_success([
                'message' => __( 'Settings saved successfully !', 'erp' )
            ]);
        } catch ( \Exception $e ) {
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
    public function erp_settings_save_general() {
        try {

            if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
                $this->send_error( __( 'Error: Nonce verification failed', 'erp' ) );
            }

            $settings = ( new \WeDevs\ERP\Framework\Settings\ERP_Settings_General() );
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
}
