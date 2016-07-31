<?php
namespace WeDevs\ERP\Admin;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Administration Menu Class
 *
 * @package payroll
 */
class Admin_Menu {

    use Hooker;

    /**
     * Kick-in the class
     */
    public function __construct() {
        $this->action( 'admin_menu', 'admin_menu', 99 );
        $this->action( 'admin_menu', 'hide_admin_menus', 100 );
        $this->action( 'wp_before_admin_bar_render', 'hide_admin_bar_links', 100 );
    }

    /**
     * Get the admin menu position
     *
     * @return int the position of the menu
     */
    public function get_menu_position() {
        return apply_filters( 'payroll_menu_position', 9999 );
    }

    /**
     * Add menu items
     *
     * @return void
     */
    public function admin_menu() {

        add_menu_page( __( 'ERP', 'erp' ), __( 'ERP Settings', 'erp' ), 'manage_options', 'erp-company', array( $this, 'company_page' ), 'dashicons-admin-settings', $this->get_menu_position() );

        add_submenu_page( 'erp-company', __( 'Company', 'erp' ), __( 'Company', 'erp' ), 'manage_options', 'erp-company', array( $this, 'company_page' ) );
        add_submenu_page( 'erp-company', __( 'Tools', 'erp' ), __( 'Tools', 'erp' ), 'manage_options', 'erp-tools', array( $this, 'tools_page' ) );
        add_submenu_page( 'erp-company', __( 'Audit Log', 'erp' ), __( 'Audit Log', 'erp' ), 'manage_options', 'erp-audit-log', array( $this, 'log_page' ) );
        add_submenu_page( 'erp-company', __( 'Settings', 'erp' ), __( 'Settings', 'erp' ), 'manage_options', 'erp-settings', array( $this, 'settings_page' ) );
        add_submenu_page( 'erp-company', __( 'Modules', 'erp' ), __( 'Modules', 'erp' ), 'manage_options', 'erp-modules', array( $this, 'module' ) );
        add_submenu_page( 'erp-company', __( 'Add-Ons', 'erp' ), __( 'Add-Ons', 'erp' ), 'manage_options', 'erp-addons', array( $this, 'addon_page' ) );

    }

    /**
     * Erp Settings page
     *
     * @return void
     */
    function settings_page() {
        new \WeDevs\ERP\Settings();
    }

    /**
     * Erp module
     *
     * @return void
     */
    function module() {
        new \WeDevs\ERP\Admin\Admin_Module();
    }

    /**
     * Hide default WordPress menu's
     *
     * @return void
     */
    function hide_admin_menus() {
        global $menu;

        $menus = get_option( '_erp_admin_menu', array() );

        if ( ! $menus ) {
            return;
        }

        foreach ($menus as $item) {
            remove_menu_page( $item );
        }

        remove_menu_page( 'edit-tags.php?taxonomy=link_category' );
        remove_menu_page( 'separator1' );
        remove_menu_page( 'separator2' );
        remove_menu_page( 'separator-last' );

        $position = 9998;
        $menu[$position] = array(
            0   =>  '',
            1   =>  'read',
            2   =>  'separator' . $position,
            3   =>  '',
            4   =>  'wp-menu-separator'
        );
    }

    /**
     * Hide default admin bar links
     *
     * @return void
     */
    function hide_admin_bar_links() {
        global $wp_admin_bar;

        $adminbar_menus = get_option( '_erp_adminbar_menu', array() );
        if ( ! $adminbar_menus ) {
            return;
        }

        foreach ($adminbar_menus as $item) {
            $wp_admin_bar->remove_menu( $item );
        }
    }

    /**
     * Handles the company page
     *
     * @return void
     */
    public function company_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';

        switch ($action) {
            case 'edit':
                $company    = new \WeDevs\ERP\Company();
                $template = WPERP_VIEWS . '/company-editor.php';
                break;

            default:
                $template = WPERP_VIEWS . '/company.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Handles the company locations page
     *
     * @return void
     */
    public function locations_page() {
        include_once dirname( __FILE__ ) . '/views/locations.php';
    }

    /**
     * Handles the tools page
     *
     * @return void
     */
    public function tools_page() {
        include_once dirname( __FILE__ ) . '/views/tools.php';
    }

    /**
     * Handles the log page
     *
     * @return void
     */
    public function log_page() {
        include_once dirname( __FILE__ ) . '/views/log.php';
    }

    /**
     * Handles the log page
     *
     * @return void
     */
    public function addon_page() {
        include_once dirname( __FILE__ ) . '/views/add-ons.php';
    }
}

return new Admin_Menu();
