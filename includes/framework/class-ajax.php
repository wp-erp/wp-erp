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
        $this->init_actions();
    }

    public function init_actions () {
        // $this->action( 'wp_ajax_erp_settings_save', 'erp_settings_save' );
        add_action( 'wp_ajax_erp-settings-save', [ $this, 'erp_settings_save' ] );
        // var_dump('break it 2');
    }

    public function erp_settings_save() {
        // Do save functionality
        wp_send_json_success();
    }
}
