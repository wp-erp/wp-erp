<?php
namespace WeDevs\ERP\HRM;

/**
 * Admin Menu
 */
class Admin_Menu {

    /**
     * Kick-in the class
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

        add_submenu_page( 'erp-hr', __( 'Overview', 'wp-erp' ), __( 'Overview', 'wp-erp' ), 'manage_options', 'erp-hr', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-hr', __( 'Employees', 'wp-erp' ), __( 'Employees', 'wp-erp' ), 'manage_options', 'erp-hr-employee', array( $this, 'employee_page' ) );
        add_submenu_page( 'erp-hr', __( 'Departments', 'wp-erp' ), __( 'Departments', 'wp-erp' ), 'manage_options', 'erp-hr-depts', array( $this, 'department_page' ) );
        add_submenu_page( 'erp-hr', __( 'Designations', 'wp-erp' ), __( 'Designations', 'wp-erp' ), 'manage_options', 'erp-hr-designation', array( $this, 'designation_page' ) );
        add_submenu_page( 'erp-hr', __( 'Settings', 'wp-erp' ), __( 'Settings', 'wp-erp' ), 'manage_options', 'erp-hr-settings', array( $this, 'settings_page' ) );

        /** Leave Management **/
         add_menu_page( __( 'Leave Management', 'wp-erp' ), __( 'Leave', 'wp-erp' ), 'manage_options', 'erp-leave', array( $this, 'empty_page' ), 'dashicons-arrow-right-alt', null );

         add_submenu_page( 'erp-leave', __( 'Requests', 'wp-erp' ), __( 'Requests', 'wp-erp' ), 'manage_options', 'erp-leave', array( $this, 'leave_requests' ) );
         add_submenu_page( 'erp-leave', __( 'Leave Entitlements', 'wp-erp' ), __( 'Leave Entitlements', 'wp-erp' ), 'manage_options', 'erp-leave-assign', array( $this, 'leave_entitilements' ) );
         add_submenu_page( 'erp-leave', __( 'Policies', 'wp-erp' ), __( 'Policies', 'wp-erp' ), 'manage_options', 'erp-leave-policies', array( $this, 'leave_policy_page' ) );
         // add_submenu_page( 'erp-leave', __( 'Leave Calendar', 'wp-erp' ), __( 'Leave Calendar', 'wp-erp' ), 'manage_options', 'erp-leave-calendar', array( $this, 'empty_page' ) );
    }

    /**
     * Handles the dashboard page
     *
     * @return void
     */
    public function dashboard_page() {
        include WPERP_HRM_VIEWS . '/dashboard.php';
    }

    /**
     * Handles the dashboard page
     *
     * @return void
     */
    public function employee_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ($action) {
            case 'view':
                $employee = new Employee( $id );
                if ( ! $employee->id ) {
                    wp_die( __( 'Employee not found!', 'wp-erp' ) );
                }

                $company = erp_get_current_company();
                if ( ! $company->has_employee( $employee->id ) ) {
                    wp_die( __( 'This employee does not belong to this company', 'wp-erp' ) );
                }

                $template = WPERP_HRM_VIEWS . '/employee/single.php';
                break;

            default:
                $template = WPERP_HRM_VIEWS . '/employee.php';
                break;
        }

        $template = apply_filters( 'erp_hr_employee_templates', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Handles the dashboard page
     *
     * @return void
     */
    public function department_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ($action) {
            case 'view':
                $template = WPERP_HRM_VIEWS . '/departments/single.php';
                break;

            default:
                $template = WPERP_HRM_VIEWS . '/departments.php';
                break;
        }

        $template = apply_filters( 'erp_hr_department_templates', $template, $action, $id );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Render the designation page
     *
     * @return void
     */
    public function designation_page() {
        include WPERP_HRM_VIEWS . '/designation.php';
    }

    /**
     * Render the settings page
     *
     * @return void
     */
    public function settings_page() {
        include WPERP_HRM_VIEWS . '/settings.php';
    }

    /**
     * Render the leave policy page
     *
     * @return void
     */
    public function leave_policy_page() {
        include WPERP_HRM_VIEWS . '/leave/leave-policies.php';
    }

    /**
     * Render the leave entitlements page
     *
     * @return void
     */
    public function leave_entitilements() {
        include WPERP_HRM_VIEWS . '/leave/leave-entitlements.php';
    }

    /**
     * Render the leave requests page
     *
     * @return void
     */
    public function leave_requests() {
        include WPERP_HRM_VIEWS . '/leave/requests.php';
    }

    /**
     * An empty page for testing purposes
     *
     * @return void
     */
    public function empty_page() {

    }

}

new Admin_Menu();