<?php

namespace WeDevs\ERP\Admin;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

class Notice {

    /**
     * Class constructor
     */
    public function __construct() {
        add_action( 'admin_notices', [ $this, 'activation_notice' ] );
    }

    /**
     * Dokan main plugin activation notice
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function activation_notice() {
        $screen = get_current_screen();
        if ( 'erp' === $screen->parent_base && current_user_can( 'activate_plugins' ) ) {
            $pro_version_need_to_update = get_transient( 'erp_pro_version_compare_failed' );
            include_once WPERP_VIEWS . '/upgrade-notice.php';
            exit();
        }
    }

}
