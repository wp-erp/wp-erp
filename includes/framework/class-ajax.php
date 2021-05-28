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
    }

    /**
     * Save Settings Data For General Tab
     *
     * @since 1.8.4
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
        } catch (\Exception $e) {
            $this->send_error($e->getMessage());
        }
    }
}

function erp_save_settings_data () {
    wp_send_json_success();
}
