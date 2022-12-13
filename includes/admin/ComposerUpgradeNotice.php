<?php

namespace WeDevs\ERP\Admin;

class ComposerUpgradeNotice {

    private $composer_update_in_pro_version = '1.3.0';

    /**
     * Class constructor
     */
    public function __construct() {
        // Version check
        if ( $this->need_to_upgrade() ) {
            add_action( 'admin_notices', [ $this, 'activation_notice' ] );
        }
    }

    /**
     * Check if the PHP version is supported
     *
     * @return bool
     */
    public function need_to_upgrade() {
        if ( class_exists( 'WP_ERP_Pro' ) && version_compare( ERP_PRO_PLUGIN_VERSION, $this->composer_update_in_pro_version, '<' ) ) {
            set_transient( 'erp_pro_composer_version_compare_failed', true );
            return true;
        } else {
            delete_transient( 'erp_pro_composer_version_compare_failed' );
            return false;
        }
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
            $pro_version_need_to_update = get_transient( 'erp_pro_composer_version_compare_failed' );
            include_once WPERP_VIEWS . '/upgrade-notice.php';
            exit();
        }
    }

}
