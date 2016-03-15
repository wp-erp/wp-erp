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

        $this->action( 'init', 'tools_page_handler' );
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

        add_menu_page( __( 'ERP', 'wp-erp' ), __( 'ERP Settings', 'wp-erp' ), 'manage_options', 'erp-company', array( $this, 'company_page' ), 'dashicons-admin-settings', $this->get_menu_position() );

        add_submenu_page( 'erp-company', __( 'Company', 'wp-erp' ), __( 'Company', 'wp-erp' ), 'manage_options', 'erp-company', array( $this, 'company_page' ) );
        add_submenu_page( 'erp-company', __( 'Tools', 'wp-erp' ), __( 'Tools', 'wp-erp' ), 'manage_options', 'erp-tools', array( $this, 'tools_page' ) );
        add_submenu_page( 'erp-company', __( 'Audit Log', 'wp-erp' ), __( 'Audit Log', 'wp-erp' ), 'manage_options', 'erp-audit-log', array( $this, 'log_page' ) );
        add_submenu_page( 'erp-company', __( 'Settings', 'wp-erp' ), __( 'Settings', 'wp-erp' ), 'manage_options', 'erp-settings', array( $this, 'settings_page' ) );
        add_submenu_page( 'erp-company', __( 'Modules', 'wp-erp' ), __( 'Modules', 'wp-erp' ), 'manage_options', 'erp-modules', array( $this, 'module' ) );
        add_submenu_page( 'erp-company', __( 'Add-Ons', 'wp-erp' ), __( 'Add-Ons', 'wp-erp' ), 'manage_options', 'erp-addons', array( $this, 'addon_page' ) );

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
     * Handle all the forms in the tools page
     *
     * @return void
     */
    public function tools_page_handler() {

        // admin menu form
        if ( isset( $_POST['erp_admin_menu'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'erp-remove-menu-nonce' ) ) {

            $menu     = isset( $_POST['menu'] ) ? array_map( 'strip_tags', $_POST['menu'] ) : [];
            $bar_menu = isset( $_POST['admin_menu'] ) ? array_map( 'strip_tags', $_POST['admin_menu'] ) : [];

            update_option( '_erp_admin_menu', $menu );
            update_option( '_erp_adminbar_menu', $bar_menu );
        }
    }

    /**
     * Handles the dashboard page
     *
     * @return void
     */
    public function dashboard_page() {
        echo "Dashboard!";
    }

    /**
     * Handles the employee page
     *
     * @return void
     */
    public function employee_page() {
        echo "employee!";
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