<?php

/**
 * Administration Menu Class
 *
 * @package payroll
 */
class WDP_Admin_Menu {

    /**
     * Kick-in the class
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', array( $this, 'do_mode_switch' ) );

        // add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_menu', array( $this, 'hide_admin_menus' ) );

        add_action( 'admin_bar_menu', array( $this, 'admin_bar_mode_switch' ), 9999 );
    }

    /**
     * Get the admin menu position
     *
     * @return int the position of the menu
     */
    public function get_menu_position() {
        return apply_filters( 'payroll_menu_position', null );
    }


    /**
     * Mode/Context switch for ERP
     *
     * @param WP_Admin_Bar $wp_admin_bar The admin bar object
     */
    public function admin_bar_mode_switch( $wp_admin_bar ) {
        // bail if current user doesnt have cap
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $modules      = erp_get_modules();
        $current_mode = erp_get_current_module();

        $title        = __( 'Switch ERP Mode', 'wp-erp' );
        $icon         = '<span class="ab-icon dashicons-randomize"></span>';
        $text         = sprintf( '%s: %s', __( 'ERP Mode', 'wp-erp' ), $current_mode['title'] );


        $wp_admin_bar->add_menu( array(
            'id'        => 'erp-mode-switch',
            'title'     => $icon . $text,
            'href'      => '#',
            'position'  => 0,
            'meta'      => array(
                'title' => $title
            )
        ) );

        foreach ($modules as $key => $module) {
            $wp_admin_bar->add_menu( array(
                'id'     => 'erp-mode-' . $key,
                'parent' => 'erp-mode-switch',
                'title'  => $module['title'],
                'href'   => wp_nonce_url( add_query_arg( 'erp-mode', $key ), 'erp_mode_nonce', 'erp_mode_nonce' )
            ) );
        }
    }

    /**
     * Do the admin mode switch
     *
     * @return void
     */
    public function do_mode_switch() {
        global $current_user;

        // bail if current user doesnt have cap
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // check for our nonce
        if ( ! isset( $_GET['erp_mode_nonce'] ) || ! wp_verify_nonce( $_GET['erp_mode_nonce'], 'erp_mode_nonce' ) ) {
            return;
        }

        $modules = erp_get_modules();

        // now check for our query string
        if ( ! isset( $_REQUEST['erp-mode'] ) || ! array_key_exists( $_REQUEST['erp-mode'], $modules ) ) {
            return;
        }

        update_user_meta( $current_user->ID, '_erp_mode', $_REQUEST['erp-mode'] );

        wp_redirect( admin_url( 'index.php' ) );
        exit;
    }

    /**
     * Add menu items
     *
     * @return void
     */
    public function admin_menu() {

        /** HR Management **/
        add_menu_page( __( 'Human Resource', 'wp-erp' ), __( 'HR Management', 'wp-erp' ), 'manage_options', 'erp-hr', array( $this, 'dashboard_page' ), null, $this->get_menu_position() );

        add_submenu_page( 'erp-hr', __( 'Employee', 'wp-erp' ), __( 'Employee', 'wp-erp' ), 'manage_options', 'erp-hr', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-hr', __( 'Contractors', 'wp-erp' ), __( 'Contractors', 'wp-erp' ), 'manage_options', 'erp-hr-contractors', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-hr', __( 'Departments', 'wp-erp' ), __( 'Departments', 'wp-erp' ), 'manage_options', 'erp-hr-depts', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-hr', __( 'Positions', 'wp-erp' ), __( 'Positions', 'wp-erp' ), 'manage_options', 'erp-hr-positions', array( $this, 'dashboard_page' ) );

        /** Payroll Management **/
        add_menu_page( __( 'Payroll', 'wp-erp' ), __( 'Payroll', 'wp-erp' ), 'manage_options', 'erp-payroll', array( $this, 'dashboard_page' ), null, $this->get_menu_position() );

        add_submenu_page( 'erp-payroll', __( 'Overview', 'wp-erp' ), __( 'Overview', 'wp-erp' ), 'manage_options', 'erp-payroll', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-payroll', __( 'Compensation', 'wp-erp' ), __( 'Compensation', 'wp-erp' ), 'manage_options', 'erp-payroll-comp', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-payroll', __( 'Salary Structure', 'wp-erp' ), __( 'Salary Structure', 'wp-erp' ), 'manage_options', 'erp-payroll-structure', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-payroll', __( 'Pay Grade', 'wp-erp' ), __( 'Pay Grade', 'wp-erp' ), 'manage_options', 'erp-payroll-grade', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-payroll', __( 'Benefits', 'wp-erp' ), __( 'Benefits', 'wp-erp' ), 'manage_options', 'erp-payroll-benefits', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-payroll', __( 'Reports', 'wp-erp' ), __( 'Reports', 'wp-erp' ), 'manage_options', 'erp-payroll-reports', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-payroll', __( 'History', 'wp-erp' ), __( 'History', 'wp-erp' ), 'manage_options', 'erp-payroll-history', array( $this, 'dashboard_page' ) );

        /** Leave Management **/
        add_menu_page( __( 'Leave Management', 'wp-erp' ), __( 'Leave Manage', 'wp-erp' ), 'manage_options', 'erp-leave', array( $this, 'dashboard_page' ), null, $this->get_menu_position() );

        add_submenu_page( 'erp-leave', __( 'Leave Requests', 'wp-erp' ), __( 'Leave Requests', 'wp-erp' ), 'manage_options', 'erp-leave', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Assign Leave', 'wp-erp' ), __( 'Assign Leave', 'wp-erp' ), 'manage_options', 'erp-leave-assign', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Allocation Request', 'wp-erp' ), __( 'Allocation Request', 'wp-erp' ), 'manage_options', 'erp-leave-alloc', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Leave Types', 'wp-erp' ), __( 'Leave Types', 'wp-erp' ), 'manage_options', 'erp-leave-types', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Working Days', 'wp-erp' ), __( 'Working Days', 'wp-erp' ), 'manage_options', 'erp-leave-workdays', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Holidays', 'wp-erp' ), __( 'Holidays', 'wp-erp' ), 'manage_options', 'erp-leave-holidays', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Leave Calendar', 'wp-erp' ), __( 'Leave Calendar', 'wp-erp' ), 'manage_options', 'erp-leave-calendar', array( $this, 'dashboard_page' ) );

        /** CRM Sales Management **/
        add_menu_page( __( 'Sales', 'wp-erp' ), __( 'Sales', 'wp-erp' ), 'manage_options', 'erp-sales', array( $this, 'dashboard_page' ), null, $this->get_menu_position() );

        add_submenu_page( 'erp-sales', __( 'Overview', 'wp-erp' ), __( 'Overview', 'wp-erp' ), 'manage_options', 'erp-sales', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-sales', __( 'Customers', 'wp-erp' ), __( 'Customers', 'wp-erp' ), 'manage_options', 'erp-sales-customers', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-sales', __( 'Leads', 'wp-erp' ), __( 'Leads', 'wp-erp' ), 'manage_options', 'erp-sales-leads', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-sales', __( 'Oppurtunity', 'wp-erp' ), __( 'Oppurtunity', 'wp-erp' ), 'manage_options', 'erp-sales-oppurtunity', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-sales', __( 'Customer Category', 'wp-erp' ), __( 'Customer Category', 'wp-erp' ), 'manage_options', 'erp-sales-category', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-sales', __( 'Newsletter', 'wp-erp' ), __( 'Newsletter', 'wp-erp' ), 'manage_options', 'erp-sales-newsletter', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-sales', __( 'Sales Team', 'wp-erp' ), __( 'Sales Team', 'wp-erp' ), 'manage_options', 'erp-sales-team', array( $this, 'dashboard_page' ) );

        /** Phone Calls and SMS */
        add_menu_page( __( 'Calls & SMS', 'wp-erp' ), __( 'Calls & SMS', 'wp-erp' ), 'manage_options', 'erp-calls', array( $this, 'dashboard_page' ), null, $this->get_menu_position() );

        add_submenu_page( 'erp-calls', __( 'Overview', 'wp-erp' ), __( 'Overview', 'wp-erp' ), 'manage_options', 'erp-calls', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-calls', __( 'Logged Calls', 'wp-erp' ), __( 'Logged Calls', 'wp-erp' ), 'manage_options', 'erp-calls-logged', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-calls', __( 'Scheduled Calls', 'wp-erp' ), __( 'Scheduled Calls', 'wp-erp' ), 'manage_options', 'erp-calls-schedule', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-calls', __( 'SMS', 'wp-erp' ), __( 'SMS', 'wp-erp' ), 'manage_options', 'erp-calls-sms', array( $this, 'dashboard_page' ) );

        /** Accounting */
        add_menu_page( __( 'Accounting', 'wp-erp' ), __( 'Accounting', 'wp-erp' ), 'manage_options', 'erp-accounting', array( $this, 'dashboard_page' ), null, $this->get_menu_position() );

        add_submenu_page( 'erp-accounting', __( 'Dashboard', 'wp-erp' ), __( 'Dashboard', 'wp-erp' ), 'manage_options', 'erp-accounting', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-accounting', __( 'Quotes', 'wp-erp' ), __( 'Quotes', 'wp-erp' ), 'manage_options', 'erp-accounting-quotes', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-accounting', __( 'Invoices', 'wp-erp' ), __( 'Invoices', 'wp-erp' ), 'manage_options', 'erp-accounting-invoices', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-accounting', __( 'Suppliers', 'wp-erp' ), __( 'Suppliers', 'wp-erp' ), 'manage_options', 'erp-accounting-suppliers', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-accounting', __( 'Purchases', 'wp-erp' ), __( 'Purchases', 'wp-erp' ), 'manage_options', 'erp-accounting-purchases', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-accounting', __( 'Reports', 'wp-erp' ), __( 'Reports', 'wp-erp' ), 'manage_options', 'erp-accounting-reports', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-accounting', __( 'Bank Accounts', 'wp-erp' ), __( 'Bank Accounts', 'wp-erp' ), 'manage_options', 'erp-accounting-bank', array( $this, 'dashboard_page' ) );

        /** HR Management **/
        add_menu_page( __( 'ERP', 'wp-erp' ), __( 'ERP', 'wp-erp' ), 'manage_options', 'erp-dashboard', array( $this, 'dashboard_page' ), 'dashicons-businessman', $this->get_menu_position() );

        // add_submenu_page( 'erp-dashboard', __( 'Dashboard', 'wp-erp' ), __( 'Dashboard', 'wp-erp' ), 'manage_options', 'erp-dashboard', array( $this, 'dashboard_page' ) );
        // add_submenu_page( 'erp-dashboard', __( 'Employees', 'wp-erp' ), __( 'Employees', 'wp-erp' ), 'manage_options', 'erp-employees', array( $this, 'employee_page' ) );
        // add_submenu_page( 'erp-dashboard', __( 'Contractors', 'wp-erp' ), __( 'Contractors', 'wp-erp' ), 'manage_options', 'erp-contractors', array( $this, 'employee_page' ) );
        add_submenu_page( 'erp-dashboard', __( 'Company', 'wp-erp' ), __( 'Company', 'wp-erp' ), 'manage_options', 'erp-company', array( $this, 'company_page' ) );
        // add_submenu_page( 'erp-dashboard', __( 'Locations', 'wp-erp' ), __( 'Locations', 'wp-erp' ), 'manage_options', 'erp-locations', array( $this, 'locations_page' ) );
        // add_submenu_page( 'erp-dashboard', __( 'Benefits', 'wp-erp' ), __( 'Benefits', 'wp-erp' ), 'manage_options', 'erp-benefits', array( $this, 'employee_page' ) );
        add_submenu_page( 'erp-dashboard', __( 'Settings', 'wp-erp' ), __( 'Settings', 'wp-erp' ), 'manage_options', 'erp-settings', array( $this, 'employee_page' ) );
    }

    /**
     * Hide default WordPress menu's
     *
     * @return void
     */
    function hide_admin_menus() {
        // remove_menu_page( 'index.php' );                  //Dashboard
        remove_menu_page( 'edit.php' );                   //Posts
        remove_menu_page( 'upload.php' );                 //Media
        remove_menu_page( 'edit.php?post_type=page' );    //Pages
        remove_menu_page( 'edit-comments.php' );          //Comments
        remove_menu_page( 'themes.php' );                 //Appearance
        remove_menu_page( 'plugins.php' );                //Plugins
        remove_menu_page( 'users.php' );                  //Users
        remove_menu_page( 'tools.php' );                  //Tools
        remove_menu_page( 'options-general.php' );        //Settings
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
        include_once dirname( __FILE__ ) . '/views/company.php';
    }

    /**
     * Handles the company locations page
     *
     * @return void
     */
    public function locations_page() {
        include_once dirname( __FILE__ ) . '/views/locations.php';
    }
}

return new WDP_Admin_Menu();