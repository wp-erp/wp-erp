<?php
namespace WeDevs\ERP\HRM;

/**
 * Admin Menu
 */
class Admin_Menu {

    /**
     * Kick-in the class
     *
     * @return void
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    /**
     * Add menu items
     *
     * @return void
     */
    public function admin_menu() {

        /** HR Management **/
        add_menu_page( __( 'Human Resource', 'wp-erp' ), __( 'HR Management', 'wp-erp' ), 'manage_options', 'erp-hr', array( $this, 'dashboard_page' ), 'dashicons-groups', null );

        add_submenu_page( 'erp-hr', __( 'Employee', 'wp-erp' ), __( 'Employee', 'wp-erp' ), 'manage_options', 'erp-hr', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-hr', __( 'Contractors', 'wp-erp' ), __( 'Contractors', 'wp-erp' ), 'manage_options', 'erp-hr-contractors', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-hr', __( 'Departments', 'wp-erp' ), __( 'Departments', 'wp-erp' ), 'manage_options', 'erp-hr-depts', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-hr', __( 'Positions', 'wp-erp' ), __( 'Positions', 'wp-erp' ), 'manage_options', 'erp-hr-positions', array( $this, 'dashboard_page' ) );

        /** Leave Management **/
        add_menu_page( __( 'Leave Management', 'wp-erp' ), __( 'Leave Manage', 'wp-erp' ), 'manage_options', 'erp-leave', array( $this, 'dashboard_page' ), 'dashicons-arrow-right-alt', null );

        add_submenu_page( 'erp-leave', __( 'Leave Requests', 'wp-erp' ), __( 'Leave Requests', 'wp-erp' ), 'manage_options', 'erp-leave', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Assign Leave', 'wp-erp' ), __( 'Assign Leave', 'wp-erp' ), 'manage_options', 'erp-leave-assign', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Allocation Request', 'wp-erp' ), __( 'Allocation Request', 'wp-erp' ), 'manage_options', 'erp-leave-alloc', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Leave Types', 'wp-erp' ), __( 'Leave Types', 'wp-erp' ), 'manage_options', 'erp-leave-types', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Working Days', 'wp-erp' ), __( 'Working Days', 'wp-erp' ), 'manage_options', 'erp-leave-workdays', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Holidays', 'wp-erp' ), __( 'Holidays', 'wp-erp' ), 'manage_options', 'erp-leave-holidays', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-leave', __( 'Leave Calendar', 'wp-erp' ), __( 'Leave Calendar', 'wp-erp' ), 'manage_options', 'erp-leave-calendar', array( $this, 'dashboard_page' ) );
    }

    /**
     * Handles the dashboard page
     *
     * @return void
     */
    public function dashboard_page() {
        echo "Dashboard!";
    }
}

new Admin_Menu();