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
    }

    /**
     * Save Settings Data
     *
     * @since 1.8.4
     *
     * @return void
     */
    public function erp_settings_save() {
        try {
            $posted = array_map( 'strip_tags_deep', $_POST );
            $settings_page = new ERP_Settings_Page();
            $settings_page->save();

            $this->send_success($posted);
        } catch (\Exception $e) {
            $this->send_error($e->getMessage());
        }
    }
}

function erp_save_settings_data () {
    wp_send_json_success();
}
