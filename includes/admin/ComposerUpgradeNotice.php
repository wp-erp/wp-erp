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
     * Check if the PHPComposer  version needs to be updated.
     *
     * @return bool
     */
    public function need_to_upgrade() {
        if ( class_exists( 'WP_ERP_Pro' ) && version_compare( ERP_PRO_PLUGIN_VERSION, $this->composer_update_in_pro_version, '<' ) ) {
            return true;
        }

        return false;
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
            include_once WPERP_VIEWS . '/upgrade-notice.php';
            exit();
        }
    }

}
