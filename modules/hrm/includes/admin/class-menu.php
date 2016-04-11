<?php
namespace WeDevs\ERP\HRM\Admin;
use WeDevs\ERP\HRM\Employee;

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
        add_menu_page( __( 'Human Resource', 'erp' ), __( 'HR Management', 'erp' ), 'erp_list_employee', 'erp-hr', array( $this, 'dashboard_page' ), 'dashicons-groups', null );

        $overview = add_submenu_page( 'erp-hr', __( 'Overview', 'erp' ), __( 'Overview', 'erp' ), 'erp_list_employee', 'erp-hr', array( $this, 'dashboard_page' ) );
        add_submenu_page( 'erp-hr', __( 'Employees', 'erp' ), __( 'Employees', 'erp' ), 'erp_list_employee', 'erp-hr-employee', array( $this, 'employee_page' ) );

        if ( current_user_can( 'employee' ) ) {
            add_submenu_page( 'erp-hr', __( 'My Profile', 'erp' ), __( 'My Profile', 'erp' ), 'erp_list_employee', 'erp-hr-my-profile', array( $this, 'employee_my_profile_page' ) );
        }

        add_submenu_page( 'erp-hr', __( 'Departments', 'erp' ), __( 'Departments', 'erp' ), 'erp_manage_department', 'erp-hr-depts', array( $this, 'department_page' ) );
        add_submenu_page( 'erp-hr', __( 'Designations', 'erp' ), __( 'Designations', 'erp' ), 'erp_manage_designation', 'erp-hr-designation', array( $this, 'designation_page' ) );
        add_submenu_page( 'erp-hr', __( 'Announcement', 'erp' ), __( 'Announcement', 'erp' ), 'erp_manage_announcement', 'edit.php?post_type=erp_hr_announcement' );
        add_submenu_page( 'erp-hr', __( 'Reporting', 'erp' ), __( 'Reporting', 'erp' ), 'erp_hr_manager', 'erp-hr-reporting', array( $this, 'reporting_page' ) );

        /** Leave Management **/
        add_menu_page( __( 'Leave Management', 'erp' ), __( 'Leave', 'erp' ), 'erp_leave_manage', 'erp-leave', array( $this, 'empty_page' ), 'dashicons-arrow-right-alt', null );

        $leave_request = add_submenu_page( 'erp-leave', __( 'Requests', 'erp' ), __( 'Requests', 'erp' ), 'erp_leave_manage', 'erp-leave', array( $this, 'leave_requests' ) );
        add_submenu_page( 'erp-leave', __( 'Leave Entitlements', 'erp' ), __( 'Leave Entitlements', 'erp' ), 'erp_leave_manage', 'erp-leave-assign', array( $this, 'leave_entitilements' ) );
        add_submenu_page( 'erp-leave', __( 'Holidays', 'erp' ), __( 'Holidays', 'erp' ), 'erp_leave_manage', 'erp-holiday-assign', array( $this, 'holiday_page' ) );
        add_submenu_page( 'erp-leave', __( 'Policies', 'erp' ), __( 'Policies', 'erp' ), 'erp_leave_manage', 'erp-leave-policies', array( $this, 'leave_policy_page' ) );
        $calendar = add_submenu_page( 'erp-leave', __( 'Calendar', 'erp' ), __( 'Calendar', 'erp' ), 'erp_leave_manage', 'erp-leave-calendar', array( $this, 'leave_calendar_page' ) );
        // add_submenu_page( 'erp-leave', __( 'Leave Calendar', 'erp' ), __( 'Leave Calendar', 'erp' ), 'manage_options', 'erp-leave-calendar', array( $this, 'empty_page' ) );

        add_action( 'admin_print_styles-' . $overview, array( $this, 'hr_calendar_script' ) );
        add_action( 'admin_print_styles-' . $calendar, array( $this, 'hr_calendar_script' ) );
    }

    /**
     * Handles HR calendar script
     *
     * @return void
     */
    function hr_calendar_script() {
        wp_enqueue_script( 'erp-momentjs' );
        wp_enqueue_script( 'erp-fullcalendar' );
        wp_enqueue_style( 'erp-fullcalendar' );
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
                    wp_die( __( 'Employee not found!', 'erp' ) );
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
     * Employee my profile page template
     *
     * @since 0.1
     *
     * @return void
     */
    public function employee_my_profile_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'view';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : intval( get_current_user_id() );

        switch ($action) {
            case 'view':
                $employee = new Employee( $id );
                if ( ! $employee->id ) {
                    wp_die( __( 'Employee not found!', 'erp' ) );
                }

                $template = WPERP_HRM_VIEWS . '/employee/single.php';
                break;

            default:
                $template = WPERP_HRM_VIEWS . '/employee/single.php';
                break;
        }

        $template = apply_filters( 'erp_hr_employee_my_profile_templates', $template, $action, $id );

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
     * Renders ERP HR Reporting Page
     *
     * @return void
     */
    public function reporting_page() {

        $action = isset( $_GET['type'] ) ? $_GET['type'] : 'main';

        switch ( $action ) {
            case 'age-profile':
                $template = WPERP_HRM_VIEWS . '/reporting/age-profile.php';
                break;

            case 'gender-profile':
                $template = WPERP_HRM_VIEWS . '/reporting/gender-profile.php';
                break;

            case 'headcount':
                $template = WPERP_HRM_VIEWS . '/reporting/headcount.php';
                break;

            case 'salary-history':
                $template = WPERP_HRM_VIEWS . '/reporting/salary-history.php';
                break;

            case 'years-of-service':
                $template = WPERP_HRM_VIEWS . '/reporting/years-of-service.php';
                break;

            default:
                $template = WPERP_HRM_VIEWS . '/reporting.php';
                break;
        }

        $template = apply_filters( 'erp_hr_reporting_pages', $template, $action );

        if ( file_exists( $template ) ) {

            include $template;
        }
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
     * Render the holiday page
     *
     * @return void
     */
    public function holiday_page() {
        include WPERP_HRM_VIEWS . '/leave/holiday.php';
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
     * Render the leave entitlements calendar
     *
     * @return void
     */
    public function leave_calendar_page() {
        include WPERP_HRM_VIEWS . '/leave/calendar.php';
    }

    /**
     * Render the leave requests page
     *
     * @return void
     */
    public function leave_requests() {
        $view = isset( $_GET['view'] ) ? $_GET['view'] : 'list';

        switch ($view) {
            case 'new':
                include WPERP_HRM_VIEWS . '/leave/new-request.php';
                break;

            default:
                include WPERP_HRM_VIEWS . '/leave/requests.php';
                break;
        }
    }

    /**
     * An empty page for testing purposes
     *
     * @return void
     */
    public function empty_page() {

    }

}
