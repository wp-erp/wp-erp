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
        $this->action( 'admin_menu', 'admin_menu' );
        $this->action( 'admin_menu', 'admin_settings', 99 );
        $this->action( 'admin_menu', 'hide_admin_menus', 100 );
        $this->action( 'wp_before_admin_bar_render', 'hide_admin_bar_links', 100 );
        add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
    }

    function add_admin_bar_menu(){
        global $wp_admin_bar, $wpdb;

        /* Check that the admin bar is showing and user has permission... */
        if ( !current_user_can( 'edit_themes' ) || !is_admin_bar_showing() ) {
            return;
        }

        $menu = erp_menu();

        /* Add the main siteadmin menu item */
        $wp_admin_bar->add_menu(
            array(
                'parent' => false,
                'id'        => 'wp-erp',
                'title'     => "WP ERP ",
                'meta'      => array(
                    'html'    => '<div class="wp-erp-admin-menu"></div>'
                )
            )
        );

        $wp_admin_bar->add_menu(
            array(
                'parent' => 'wp-erp',
                'id'        => 'wp-erp-child',
                'title'     => " ",
                'meta'      => array(
                    'html'    => '<div class="wp-erp-admin-bar-menu">
                                    <div>
                                        <h3><a href="#">HR</a></h3>
                                        <ul>
                                            <li><a href="#">Dashboard</a></li>
                                            <li><a href="#">Employees</a></li>
                                            <li><a href="#">Departments</a></li>
                                            <li><a href="#">Designations</a></li>
                                            <li><a href="#">Announcement</a></li>
                                            <li><a href="#">Reporting</a></li>
                                            <li><a href="#">Attendance</a></li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h3><a href="#">CRM</a></h3>
                                        <ul>
                                            <li><a href="#">Dashboard</a></li>
                                            <li><a href="#">Contacts</a></li>
                                            <li><a href="#">Companies</a></li>
                                            <li><a href="#">Activities</a></li>
                                            <li><a href="#">Schedules</a></li>
                                            <li><a href="#">Contact Group</a></li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h3><a href="#">Accounting</a></h3>
                                        <ul>
                                            <li><a href="#">Dashboard</a></li>
                                            <li><a href="#">Customers</a></li>
                                            <li><a href="#">Vendors</a></li>
                                            <li><a href="#">Sales</a></li>
                                            <li><a href="#">Expense</a></li>
                                            <li><a href="#">Chart of Accounts</a></li>
                                            <li><a href="#">Bank Accounts</a></li>
                                            <li><a href="#">Reports</a></li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h3><a href="#">Project Manager</a></h3>
                                        <ul>
                                            <li><a href="#">Projects</a></li>
                                            <li><a href="#">Add-Ons</a></li>
                                            <li><a href="#">My Tasks</a></li>
                                            <li><a href="#">Calendar</a></li>
                                            <li><a href="#">Reports</a></li>
                                            <li><a href="#">Progress</a></li>
                                        </ul>
                                    </div>
                                </div>',
                )
            )
        );
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

        add_menu_page( __( 'WP ERP', 'erp' ), 'WP ERP', 'manage_options', 'erp', array( $this, 'overview_page' ), "data:image/svg+xml;base64," . base64_encode( '<svg width="82px" height="83px" viewBox="0 0 82 83" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><!-- Generator:Sketch 51.2(57519) - http://www.bohemiancoding.com/sketch --><title>Group 2</title><desc>Created with Sketch.</desc><defs></defs><g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="Product-Logo" transform="translate(-386.000000, -995.000000)" fill-rule="nonzero"><g id="Group-2" transform="translate(386.000000, 995.000000)"><path d="M62.06,10.27 C60.8214151,9.95783732 59.5471586,9.80990253 58.27,9.83 C52.35,9.83 46.37,11.75 40.91,13.9 C36.3696469,15.649701 32.0148255,17.8472109 27.91,20.46 C25.26,22.18 19.44,25.18 19.13,28.75 C18.9,31.37 22.08,33.28 24.32,33.56 C26.38,33.82 28,32.78 29.72,31.68 C32.1,30.16 34.27,28.86 36.36,27.68 C44.36,23.26 58.08,17.5 66.96,14.43 C65.4577239,12.8959711 63.8174754,11.5034336 62.06,10.27 Z" id="Shape" fill="#C3C4C6"></path><path d="M62.76,61.24 L60.9,62.01 C58.7,62.93 56.42,63.89 54.11,64.68 C51.5494253,65.5307611 48.924186,66.1728579 46.26,66.6 C45.3614861,66.7547409 44.4517275,66.8350137 43.54,66.84 C38.87,66.84 36.72,64.43 35.74,62.21 C34.7800856,60.0302706 34.8165903,57.5406484 35.84,55.39 C39.69,47.39 66.44,37.58 69.48,36.49 C72.81,35.29 78.16,33.72 81.93,32.62 C82.35,26.11 75.84,18.49 72.42,16.98 C66.19,18.6 47.23,25.89 36.53,31.84 C34.47,32.99 32.33,34.27 29.98,35.77 C28.37,36.77 26.57,37.95 24.31,37.95 C24.0292377,37.9506396 23.7487039,37.9339412 23.47,37.9 C20.6317176,37.5705986 18.1845976,35.7524113 17.05,33.13 C15.22,28.45 18.05,25.54 20.17,23.41 L20.89,22.67 L20.94,22.62 C27.41,16.85 38.45,8.8 59.83,4.96 C44.2466606,-2.76365785 25.4816878,0.226613774 13.0708141,12.4112587 C0.659940449,24.5959036 -2.67499523,43.3026973 4.76071002,59.0254721 C12.1964153,74.7482468 28.7720308,84.0388279 46.0643824,82.1760523 C63.3567341,80.3132767 77.5734219,67.7056712 81.49,50.76 C75.5936642,54.8402426 69.3219942,58.3494312 62.76,61.24 Z" id="Shape" fill="#00A0E3"></path><path d="M77.6,39.09 C69.9804697,41.0519492 62.5300517,43.6201363 55.32,46.77 C51.6448977,48.3544704 48.1262102,50.2795373 44.81,52.52 C43.02,53.77 38.68,56.6 38.54,59.05 C38.35,62.43 43.05,64.1 45.8,63.49 C51.51,62.21 56.19,59.09 61.16,56.49 C66.6843247,53.7251141 71.9721163,50.5109662 76.97,46.88 C77.4014278,44.6727377 77.6191009,42.42903 77.62,40.18 C77.63,39.82 77.61,39.46 77.6,39.09 Z" id="Shape" fill="#C3C4C6"></path></g></g></g></svg>' ), 2 );
        add_submenu_page( 'erp', __('Dashboard', 'erp' ), __('Dashboard', 'erp' ), 'manage_options', 'erp', array( $this, 'overview_page' ) );

        // add_menu_page( __( 'ERP', 'erp' ), 'ERP Settings', 'manage_options', 'erp-company', array( $this, 'company_page' ), 'dashicons-admin-settings', $this->get_menu_position() );

        // add_submenu_page( 'erp-company', __( 'Company', 'erp' ), __( 'Company', 'erp' ), 'manage_options', 'erp-company', array( $this, 'company_page' ) );
        // add_submenu_page( 'erp-company', __( 'Tools', 'erp' ), __( 'Tools', 'erp' ), 'manage_options', 'erp-tools', array( $this, 'tools_page' ) );
        // add_submenu_page( 'erp-company', __( 'Audit Log', 'erp' ), __( 'Audit Log', 'erp' ), 'manage_options', 'erp-audit-log', array( $this, 'log_page' ) );
        // add_submenu_page( 'erp-company', __( 'Settings', 'erp' ), __( 'Settings', 'erp' ), 'manage_options', 'erp-settings', array( $this, 'settings_page' ) );
        // add_submenu_page( 'erp-company', __( 'Status', 'erp' ), __( 'Status', 'erp' ), 'manage_options', 'erp-status', array( $this, 'status_page' ) );
        // add_submenu_page( 'erp-company', __( 'Modules', 'erp' ), __( 'Modules', 'erp' ), 'manage_options', 'erp-modules', array( $this, 'module' ) );
        // add_submenu_page( 'erp-company', __( 'Add-Ons', 'erp' ), __( 'Add-Ons', 'erp' ), 'manage_options', 'erp-addons', array( $this, 'addon_page' ) );
    }

    public function admin_settings() {

        add_submenu_page( 'erp', __( 'Settings', 'erp' ), __( 'Settings', 'erp' ), 'manage_options', 'erp-settings', [ $this, 'router' ] );
        add_submenu_page( 'erp', __( 'Tools', 'erp' ), __( 'Tools', 'erp' ), 'manage_options', 'erp-tools', [ $this, 'tools_page' ] );
        add_submenu_page( 'erp', __( 'Company', 'erp' ), __( 'Company', 'erp' ), 'manage_options', 'erp-company', [ $this, 'company_page' ] );
        add_submenu_page( 'erp', __( 'Add-Ons', 'erp' ), __( 'Add-Ons', 'erp' ), 'manage_options', 'erp-addons', [ $this, 'addon_page' ] );
        add_submenu_page( 'erp', __( 'Modules', 'erp' ), __( 'Modules', 'erp' ), 'manage_options', 'erp-modules', [ $this, 'module' ] );

        erp_add_menu( 'settings', array(
            'title'         =>  __( 'Settings', 'erp' ),
            'slug'          =>  'settings',
            'capability'    =>  'manage_options',
            'callback'      =>  [ $this, 'settings_page' ],
            'position'      =>  5,
        ) );
    }

    /**
     * Route to approprite template according to current menu
     *
     * @since 1.3.14
     *
     * @return void
     */
    public function router() {
        $component = 'settings';
        $menu = erp_menu();
        $menu = $menu[$component];

        $section = ( isset( $_GET['section'] ) && isset( $menu[$_GET['section']] ) ) ? $_GET['section'] : 'settings';
        $sub = ( isset( $_GET['sub-section'] ) && !empty( $menu[$section]['submenu'][$_GET['sub-section']] ) ) ? $_GET['sub-section'] : false;

        $callback = $menu[$section]['callback'];
        if ( $sub ) {
            $callback = $menu[$section]['submenu'][$sub]['callback'];
        }

        // erp_render_menu( $component );

        call_user_func( $callback );
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
     * Erp Status page
     *
     * @return void
     */
    function status_page() {
        new \WeDevs\ERP\Status();
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
     * Render Overview page
     */
    function overview_page(){
        ?>
        <h1>WP ERP Overview</h1>
        <?php
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
//
return new Admin_Menu();
