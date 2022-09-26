<?php

namespace WeDevs\ERP\HRM\Admin;

use WeDevs\ERP\HRM\Employee;

/**
 * Admin Menu
 */
class AdminMenu {

    /**
     * Kick-in the class
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_print_footer_scripts', [ $this, 'highlight_menu' ] );
        add_filter( 'parent_file', [ $this, 'highlight_submenu' ], 100 );
    }

    /**
     * Add menu items
     *
     * @return void
     */
    public function admin_menu() {
        $dashboard = add_submenu_page( 'erp', __( 'HR', 'erp' ), 'HR', 'erp_list_employee', 'erp-hr', [ $this, 'router' ] );

        erp_add_menu_header( 'hr', 'HR', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 338.337 211.005"><defs><style>.cls-1{fill:#9da2a7}</style></defs><g id="Group_234" data-name="Group 234" transform="translate(0.097 0.001)"><path id="Path_275" data-name="Path 275" class="cls-1" d="M176.2,158.5c10.3-14.6,20.2-28.7,30.3-42.7a5.351,5.351,0,0,1,4.4-1.8c19.3,4.2,34.4,12.5,34.2,38.3-.1,12.1-1.2,24.2-1.9,36.3a3.553,3.553,0,0,1-.2,1c-3.5,13.8-7,17.8-21.2,18.8-23.4,1.6-46.9,2.5-70.3,2.6-13.3.1-26.5-2.1-39.8-3.3-7.3-.6-12-4.5-14-11.5-1.4-4.9-3.3-9.9-3.5-15a290.355,290.355,0,0,1-.5-34.9c.9-14.1,8.6-23.8,22-28.8,2.2-.8,4.4-1.6,6.6-2.3,7.7-2.3,7.5-2.1,11.9,4.5,3.4,5.1,7.7,9.6,11.2,14.6,5.6,7.8,10.9,15.8,17,24.6,1.3-8,3-15.2,3.4-22.5.2-3.3-1.9-6.9-3.6-10.1-1.3-2.6-3.7-4.7-5.3-7.3-1.5-2.3-1.1-4.4,2.1-4.5,6.8-.1,13.7-.2,20.5,0,3.2.1,3.4,2.2,2.1,4.5a20.26,20.26,0,0,1-3.3,4.4c-5.4,5.9-6.8,12.7-4.9,20.4C174.5,148.6,175.2,153.4,176.2,158.5Z"/><path id="Path_276" data-name="Path 276" class="cls-1" d="M169,103c-28.9,0-51.2-22.3-51.1-51.4,0-29,22.8-51.8,51.6-51.6,28.6.1,51,22.9,50.9,51.8S197.9,103,169,103Z"/><path id="Path_277" data-name="Path 277" class="cls-1" d="M262.2,179c0-12.1.3-23.5-.1-34.7-.4-13.1-4.7-24.8-15.3-33.5,6.1-3.6,9.9-3.5,15.6-.3,13.6,7.7,27.2,7.4,40.9-.3a13.984,13.984,0,0,1,8.7-1.5,56.625,56.625,0,0,1,13.2,4.3,19.3,19.3,0,0,1,11.4,14.4,86.106,86.106,0,0,1-1.3,39.1c-1.7,6.3-5.3,9.7-12.1,10.6A336.967,336.967,0,0,1,262.2,179Z"/><path id="Path_278" data-name="Path 278" class="cls-1" d="M91.9,110.3C79.6,120.7,76,134,76,148.7v29.9a16.3,16.3,0,0,1-2.1.5,299.084,299.084,0,0,1-59.8-2.2c-5.4-.8-8.9-3.4-10.5-8.5-4.3-14.2-5-28.6-1.3-42.8,2.4-9.2,10.1-13.1,18.5-15.8,5.7-1.8,10.7-2.1,16.5,1.3,12.2,7.2,25.1,6.6,37.6-.1C82,107.2,83.3,107,91.9,110.3Z"/><path id="Path_279" data-name="Path 279" class="cls-1" d="M282.7,30.8A37.153,37.153,0,0,1,320,68.3c-.1,21.3-16.5,37.4-37.9,37.4-20.9-.1-37-16.6-36.8-37.8C245.3,46.8,261.6,30.7,282.7,30.8Z"/><path id="Path_280" data-name="Path 280" class="cls-1" d="M55.9,30.8c21,0,37.2,16.2,37.3,37.3,0,21.2-16.1,37.5-37.1,37.6A37.093,37.093,0,0,1,18.4,68.6,37.02,37.02,0,0,1,55.9,30.8Z"/></g></svg>' );

        erp_add_menu( 'hr', [
            'title'         => __( 'Overview', 'erp' ),
            'capability'    => 'erp_list_employee',
            'slug'          => 'dashboard',
            'callback'      => [ $this, 'dashboard_page' ],
            'position'      => 1,
        ] );

        erp_add_menu( 'hr', [
            'title'       => __( 'People', 'erp' ),
            'capability'  => 'erp_list_employee',
            'slug'        => 'people',
            'callback'    => [ $this, 'people_page' ],
            'position'    => 5,
        ] );

        if ( current_user_can( 'employee' ) ) {
            erp_add_menu( 'hr', [
                'title'         => __( 'My Profile', 'erp' ),
                'capability'    => 'erp_list_employee',
                'slug'          => 'my-profile',
                'callback'      => [ $this, 'employee_my_profile_page' ],
                'position'      => 3,
            ] );
        }

        erp_add_menu( 'hr', [
            'title'         => __( 'Reports', 'erp' ),
            'capability'    => 'erp_hr_manager',
            'slug'          => 'report',
            'callback'      => [ $this, 'reporting_page' ],
            'position'      => 99,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'         => __( 'Age Profile', 'erp' ),
            'capability'    => 'erp_hr_manager',
            'slug'          => 'report&type=age-profile',
            'callback'      => [ $this, 'reporting_page' ],
            'position'      => 5,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'         => __( 'Salary History', 'erp' ),
            'capability'    => 'erp_hr_manager',
            'slug'          => 'report&type=salary-history',
            'callback'      => [ $this, 'reporting_page' ],
            'position'      => 5,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'         => __( 'Gender Profile', 'erp' ),
            'capability'    => 'erp_hr_manager',
            'slug'          => 'report&type=gender-profile',
            'callback'      => [ $this, 'reporting_page' ],
            'position'      => 5,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'         => __( 'Years of Service', 'erp' ),
            'capability'    => 'erp_hr_manager',
            'slug'          => 'report&type=years-of-service',
            'callback'      => [ $this, 'reporting_page' ],
            'position'      => 5,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'         => __( 'Head Count', 'erp' ),
            'capability'    => 'erp_hr_manager',
            'slug'          => 'report&type=headcount',
            'callback'      => [ $this, 'reporting_page' ],
            'position'      => 5,
        ] );

        erp_add_submenu( 'hr', 'report', [
            'title'         => __( 'Leaves', 'erp' ),
            'capability'    => 'erp_hr_manager',
            'slug'          => 'report&type=leaves',
            'callback'      => [ $this, 'reporting_page' ],
            'position'      => 5,
        ] );

        erp_add_menu( 'hr', [
            'title'         => __( '<span class="erp-help">Help</span>', 'erp' ),
            'capability'    => 'erp_hr_manager',
            'slug'          => 'help',
            'callback'      => [ $this, 'help_page' ],
            'position'      => 100,
        ] );

        $request_capabilities = 'erp_leave_manage';

        if ( class_exists( '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module' ) && get_option( 'erp_pro_multilevel_approval' ) === 'yes' ) {
            $request_capabilities = erp_hr_is_current_user_dept_lead() ? 'erp_list_employee' : 'erp_leave_manage';
        }

        erp_add_menu( 'hr', [
            'title'         => __( 'Leave', 'erp' ),
            'capability'    => $request_capabilities,
            'slug'          => 'leave',
            'callback'      => [ $this, 'leave_requests' ],
            'position'      => 30,
        ] );

        erp_add_submenu( 'hr', 'leave', [
            'title'         => __( 'Requests', 'erp' ),
            'capability'    => erp_hr_is_current_user_dept_lead() ? 'erp_list_employee' : 'erp_leave_manage',
            'slug'          => 'leave-requests',
            'callback'      => [ $this, 'leave_requests' ],
            'position'      => 5,
        ] );

        erp_add_submenu( 'hr', 'leave', [
            'title'         => __( 'Leave Entitlements', 'erp' ),
            'capability'    => 'erp_leave_manage',
            'slug'          => 'leave-entitlements',
            'callback'      => [ $this, 'leave_entitilements' ],
            'position'      => 10,
        ] );

        erp_add_submenu( 'hr', 'leave', [
            'title'         => __( 'Holidays', 'erp' ),
            'capability'    => 'erp_leave_manage',
            'slug'          => 'holidays',
            'callback'      => [ $this, 'holiday_page' ],
            'position'      => 15,
        ] );

        erp_add_submenu( 'hr', 'leave', [
            'title'         => __( 'Policies', 'erp' ),
            'capability'    => 'erp_leave_manage',
            'slug'          => 'policies',
            'callback'      => [ $this, 'leave_policy_page' ],
            'position'      => 25,
        ] );

        erp_add_submenu( 'hr', 'leave', [
            'title'         => __( 'Calendar', 'erp' ),
            'capability'    => 'erp_leave_manage',
            'slug'          => 'leave-calendar',
            'callback'      => [ $this, 'leave_calendar_page' ],
            'position'      => 35,
        ] );

        add_action( 'admin_print_styles-' . $dashboard, [ $this, 'hr_calendar_script' ] );
        // add_action( 'admin_print_styles-' . $calendar, array( $this, 'hr_calendar_script' ) );
    }

    /**
     * Route to approprite template according to current menu
     *
     * @since 1.3.14
     *
     * @return void
     */
    public function router() {
        $component = 'hr';
        $menu      = erp_menu();
        $menu      = $menu[ $component ];

        $section = ( isset( $_GET['section'] ) && isset( $menu[ $_GET['section'] ] ) ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'dashboard';
        $sub     = ( isset( $_GET['sub-section'] ) && ! empty( $menu[ $section ]['submenu'][ $_GET['sub-section'] ] ) ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : false;

        // check permission/capability
        $permission = $menu[ $section ]['capability'];

        erp_verify_page_access_permission( $permission );

        $callback = $menu[ $section ]['callback'];

        if ( $sub ) {
            $callback = $menu[ $section ]['submenu'][ $sub ]['callback'];
        }

        erp_render_menu( $component );

        call_user_func( $callback );
    }

    /**
     * Handles HR calendar script
     *
     * @return void
     */
    public function hr_calendar_script() {
        wp_enqueue_script( 'erp-fullcalendar' );
        enqueue_fullcalendar_locale();
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
     * Handles the people page
     *
     * @since 1.8.0
     * @since 1.8.5 Added requests page in the people menu items
     *
     * @return void
     */
    public function people_page() {
        $sub_section = isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : 'employee';

        switch ( $sub_section ) {
            case 'employee':
                $this->employee_page();
                break;

            case 'requests':
                $this->requests_page();
                break;

            case 'department':
                $this->department_page();
                break;

            case 'designation':
                $this->designation_page();
                break;

            case 'announcement':
                $this->announcement_page();
                break;

            default:
                do_action( "erp_hr_{$sub_section}_page" );
        }
    }

    /**
     * Handles the employee page
     *
     * @return void
     */
    public function employee_page() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ( $action ) {
            case 'view':
                $employee = new Employee( $id );

                if ( ! $employee->get_user_id() ) {
                    wp_die( esc_html__( 'Employee not found!', 'erp' ) );
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
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'view';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : intval( get_current_user_id() );

        switch ( $action ) {
            case 'view':
                $employee = new Employee( $id );

                if ( ! $employee->ID ) {
                    wp_die( esc_html__( 'Employee not found!', 'erp' ) );
                }

                $template = WPERP_HRM_VIEWS . '/employee/single.php';
                break;

            default:
                $template = WPERP_HRM_VIEWS . '/employee/single.php';
                break;
        }

        $template = apply_filters( 'erp_hr_employee_my_profile_templates', $template, $action, $id );

        if ( file_exists( $template ) ) {
            $is_my_profile_page = false;

            if ( get_current_user_id() == $id ) {
                $is_my_profile_page = true;
            }

            include $template;
        }
    }

    /**
     * Renders requests page template
     *
     * @since 1.8.5
     *
     * @return void
     */
    public function requests_page() {
        erp_verify_page_access_permission( 'erp_hr_manager' );

        $template = apply_filters( 'erp_hr_requests_templates', WPERP_HRM_VIEWS . '/requests.php' );

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
        erp_verify_page_access_permission( 'erp_manage_department' );

        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ( $action ) {
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
        erp_verify_page_access_permission( 'erp_manage_designation' );

        include WPERP_HRM_VIEWS . '/designation.php';
    }

    /**
     * Render the announcement page
     *
     * @since 1.10.0
     *
     * @return void
     */
    public function announcement_page() {
        erp_verify_page_access_permission( 'erp_manage_announcement' );

        include WPERP_HRM_VIEWS . '/announcements.php';
    }

    /**
     * Renders ERP HR Reporting Page
     *
     * @return void
     */
    public function reporting_page() {
        $action = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'main';

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

            case 'leaves':
                $template = WPERP_HRM_VIEWS . '/reporting/leave.php';
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
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list';
        $type   = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';

        switch ( $action ) {
            case 'list':
                if ( $type === 'policy-name' ) {
                    include WPERP_HRM_VIEWS . '/leave/policy-name.php';
                } else {
                    include WPERP_HRM_VIEWS . '/leave/leave-policies.php';
                }
                break;

            case 'edit':
                if ( $type === 'policy-name' ) {
                    include WPERP_HRM_VIEWS . '/leave/policy-name.php';
                } else {
                    include WPERP_HRM_VIEWS . '/leave/new-policy.php';
                }
                break;

            case 'new':
            case 'copy':
                include WPERP_HRM_VIEWS . '/leave/new-policy.php';
                break;
        }
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
        $view = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : 'list';

        switch ( $view ) {
            case 'new':
                include WPERP_HRM_VIEWS . '/leave/new-request.php';
                break;

            default:
                include WPERP_HRM_VIEWS . '/leave/requests.php';
                break;
        }
    }

    /**
     * Show HRM Help Page
     *
     * @since 1.0.0
     */
    public function help_page() {
        include WPERP_HRM_VIEWS . '/help.php';
    }

    /**
     * Highlight Menu for announcement
     */
    public function highlight_menu() {
        $screen = get_current_screen();

        if ( $screen->parent_file != 'admin.php?page=erp' ) {
            return;
        } ?>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                $('li.toplevel_page_erp').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
                $('li.toplevel_page_erp a:first').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
            });
        </script>
        <?php
    }

    /**
     * Highlight sunbmenu for announcement
     *
     * @param $parent_file
     *
     * @return string
     */
    public function highlight_submenu( $parent_file ) {
        global $parent_file, $submenu_file, $post_type;

        if ( 'erp_hr_announcement' == $post_type || 'erp_hr_questionnaire' == $post_type ) {
            $parent_file  = 'admin.php?page=erp';
            $submenu_file = 'erp-hr';
        }

        return $parent_file;
    }
}
