<?php

namespace WeDevs\ERP\HRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * The HRM Class
 *
 * This is loaded in `init` action hook
 */
class Human_Resource {

    use Hooker;

    private $plugin;

    /**
     * Kick-in the class
     *
     * @param \WeDevs_ERP $plugin
     */
    public function __construct( \WeDevs_ERP $plugin ) {

        // prevent duplicate loading
        if ( did_action( 'erp_hrm_loaded' ) ) {
            return;
        }

        $this->plugin = $plugin;

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // Initialize the classes
        $this->init_classes();

        // Initialize the action hooks
        $this->init_actions();

        // Initialize the filter hooks
        $this->init_filters();

        do_action( 'erp_hrm_loaded' );
    }

    /**
     * Define the plugin constants
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPERP_HRM_FILE', __FILE__ );
        define( 'WPERP_HRM_PATH', dirname( __FILE__ ) );
        define( 'WPERP_HRM_VIEWS', dirname( __FILE__ ) . '/views' );
        define( 'WPERP_HRM_JS_TMPL', WPERP_HRM_VIEWS . '/js-templates' );
        define( 'WPERP_HRM_ASSETS', plugins_url( '/assets', __FILE__ ) );
    }

    /**
     * Include the required files
     *
     * @since 1.0.0
     * @since 1.2.0 Include CLI class
     *
     * @return void
     */
    private function includes() {
        require_once WPERP_HRM_PATH . '/includes/functions-url.php';
        require_once WPERP_HRM_PATH . '/includes/functions.php';
        require_once WPERP_HRM_PATH . '/includes/functions-department.php';
        require_once WPERP_HRM_PATH . '/includes/functions-designation.php';
        require_once WPERP_HRM_PATH . '/includes/functions-employee.php';
        require_once WPERP_HRM_PATH . '/includes/functions-leave.php';
        require_once WPERP_HRM_PATH . '/includes/functions-capabilities.php';
        require_once WPERP_HRM_PATH . '/includes/functions-dashboard-widgets.php';
        require_once WPERP_HRM_PATH . '/includes/functions-reporting.php';
        require_once WPERP_HRM_PATH . '/includes/functions-announcement.php';
        require_once WPERP_HRM_PATH . '/includes/actions-filters.php';

        // cli command
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            include WPERP_HRM_PATH . '/includes/cli/commands.php';
        }
    }

    /**
     * Initialize WordPress action hooks
     *
     * @return void
     */
    private function init_actions() {
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
        $this->action( 'admin_footer', 'admin_js_templates' );
        $this->filter( 'erp_rest_api_controllers', 'load_hrm_rest_controllers' );
    }

    /**
     * Initialize WordPress filter hooks
     *
     * @return void
     */
    private function init_filters() {
        add_filter( 'erp_settings_pages', array( $this, 'add_settings_page' ) );
    }

    /**
     * Init classes
     *
     * @return void
     */
    private function init_classes() {
        new Ajax_Handler();
        new Form_Handler();
        new Announcement();
        new Admin\Admin_Menu();
        new Admin\User_Profile();
        new Hr_Log();
        new Emailer();
    }

    /**
     * Register HR settings page
     *
     * @param array
     */
    public function add_settings_page( $settings = [] ) {

        $settings[] = include __DIR__ . '/includes/class-settings.php';

        return $settings;
    }

    /**
     * Load admin scripts and styles
     *
     * @param  string
     *
     * @return void
     */
    public function admin_scripts( $hook ) {
        if ( 'wp-erp_page_erp-hr' !== $hook ) {
            return;
        }

        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_media();
        wp_enqueue_script( 'erp-tiptip' );

        if ( isset( $_GET['section'] ) && $_GET['section'] == 'employee' ) {
            wp_enqueue_style( 'erp-sweetalert' );
            wp_enqueue_script( 'erp-sweetalert' );
        }

        wp_enqueue_script( 'wp-erp-hr', WPERP_HRM_ASSETS . "/js/hrm$suffix.js", array( 'erp-script' ), date( 'Ymd' ), true );
        wp_enqueue_script( 'wp-erp-hr-leave', WPERP_HRM_ASSETS . "/js/leave$suffix.js", array(
            'erp-script',
            'wp-color-picker'
        ), date( 'Ymd' ), true );

        $localize_script = apply_filters( 'erp_hr_localize_script', array(
            'nonce'                  => wp_create_nonce( 'wp-erp-hr-nonce' ),
            'popup'                  => array(
                'dept_title'        => __( 'New Department', 'erp' ),
                'dept_submit'       => __( 'Create Department', 'erp' ),
                'location_title'    => __( 'New Location', 'erp' ),
                'location_submit'   => __( 'Create Location', 'erp' ),
                'dept_update'       => __( 'Update Department', 'erp' ),
                'desig_title'       => __( 'New Designation', 'erp' ),
                'desig_submit'      => __( 'Create Designation', 'erp' ),
                'desig_update'      => __( 'Update Designation', 'erp' ),
                'employee_title'    => __( 'New Employee', 'erp' ),
                'employee_create'   => __( 'Create Employee', 'erp' ),
                'employee_update'   => __( 'Update Employee', 'erp' ),
                'employment_status' => __( 'Employment Status', 'erp' ),
                'update_status'     => __( 'Update', 'erp' ),
                'policy'            => __( 'Leave Policy', 'erp' ),
                'policy_create'     => __( 'Create Policy', 'erp' ),
                'holiday'           => __( 'Holiday', 'erp' ),
                'holiday_create'    => __( 'Create Holiday', 'erp' ),
                'holiday_update'    => __( 'Update Holiday', 'erp' ),
                'new_leave_req'     => __( 'Leave Request', 'erp' ),
                'take_leave'        => __( 'Send Leave Request', 'erp' ),
                'terminate'         => __( 'Terminate', 'erp' ),
                'leave_reject'      => __( 'Reject Reason', 'erp' ),
                'already_terminate' => __( 'Sorry, this employee is already terminated', 'erp' ),
                'already_active'    => __( 'Sorry, this employee is already active', 'erp' )
            ),
            'asset_url'              => WPERP_ASSETS,
            'emp_upload_photo'       => __( 'Upload Photo', 'erp' ),
            'emp_set_photo'          => __( 'Set Photo', 'erp' ),
            'confirm'                => __( 'Are you sure?', 'erp' ),
            'delConfirmDept'         => __( 'Are you sure to delete this department?', 'erp' ),
            'delConfirmPolicy'       => __( 'If you delete this policy, the leave entitlements and requests related to it will also be deleted. Are you sure to delete this policy?', 'erp' ),
            'delConfirmHoliday'      => __( 'Are you sure to delete this Holiday?', 'erp' ),
            'delConfirmEmployee'     => __( 'Are you sure to delete this employee?', 'erp' ),
            'restoreConfirmEmployee' => __( 'Are you sure to restore this employee?', 'erp' ),
            'delConfirmEmployeeNote' => __( 'Are you sure to delete this employee note?', 'erp' ),
            'delConfirmEntitlement'  => __( 'Are you sure to delete this Entitlement? If yes, then all leave request under this entitlement also permanently deleted', 'erp' ),
            'make_employee_text'     => __( 'This user already exists, Do you want to make this user as a employee?', 'erp' ),
            'employee_exit'          => __( 'This employee already exists', 'erp' ),
            'employee_created'       => __( 'Employee successfully created', 'erp' ),
            'create_employee_text'   => __( 'Click to create employee', 'erp' ),
            'empty_entitlement_text' => sprintf( '<span>%s <a href="%s" title="%s">%s</a></span>', __( 'Please create entitlement first', 'erp' ), add_query_arg( [
                'page'          => 'erp-hr',
                'section'       => 'leave',
                'sub-section'   => 'leave-entitlements&tab=assignment'
            ], admin_url( 'admin.php' ) ), __( 'Create Entitlement', 'erp' ), __( 'Create Entitlement', 'erp' ) ),
        ) );


        $section    =   isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) :'dashboard';

        switch ( $section ) {
            case 'employee':
                wp_enqueue_script( 'post' );
                $employee                          = new Employee();
                $localize_script['employee_empty'] = $employee->to_array();

                wp_enqueue_script( 'erp-flotchart' );
                wp_enqueue_script( 'erp-flotchart-stack' );
                wp_enqueue_script( 'erp-flotchart-time' );
                wp_enqueue_script( 'erp-flotchart-pie' );
                wp_enqueue_script( 'erp-flotchart-orerbars' );
                wp_enqueue_script( 'erp-flotchart-axislables' );
                wp_enqueue_script( 'erp-flotchart-valuelabel' );
                wp_enqueue_style( 'erp-flotchart-valuelabel-css' );
            break;
            case 'report':
                wp_enqueue_script( 'erp-flotchart' );
                wp_enqueue_script( 'erp-flotchart-time' );
                wp_enqueue_script( 'erp-flotchart-pie' );
                wp_enqueue_script( 'erp-flotchart-orerbars' );
                wp_enqueue_script( 'erp-flotchart-axislables' );
                wp_enqueue_script( 'erp-flotchart-valuelabel' );
                wp_enqueue_style( 'erp-flotchart-valuelabel-css' );
            break;
            case 'my-profile':
                wp_enqueue_script( 'erp-flotchart' );
                wp_enqueue_script( 'erp-flotchart-stack' );
                wp_enqueue_script( 'erp-flotchart-time' );
                wp_enqueue_script( 'erp-flotchart-pie' );
                wp_enqueue_script( 'erp-flotchart-orerbars' );
                wp_enqueue_script( 'erp-flotchart-axislables' );
                wp_enqueue_script( 'erp-flotchart-valuelabel' );
                wp_enqueue_style( 'erp-flotchart-valuelabel-css' );
                break;

        }

        // if its an employee page

        wp_localize_script( 'wp-erp-hr', 'wpErpHr', $localize_script );

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'erp-select2' );
        wp_enqueue_style( 'erp-tiptip' );
        wp_enqueue_style( 'erp-style' );
    }

    /**
     * Print JS templates in footer
     *
     * @return void
     */
    public function admin_js_templates() {
        global $current_screen;
        // main HR menu
        $hook = str_replace( sanitize_title( __( 'HR Management', 'erp' ) ), 'hr-management', $current_screen->base );
        if ( 'wp-erp_page_erp-hr' === $hook ) {
            erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-leave-request.php', 'erp-new-leave-req' );
            erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-days.php', 'erp-leave-days' );
        }

        $section    =   isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'dashboard';
        switch ( $section ) {
            case 'department':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-dept.php', 'erp-new-dept' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/row-dept.php', 'erp-dept-row' );
                break;

            case 'designation':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-designation.php', 'erp-new-desig' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/row-desig.php', 'erp-desig-row' );
                break;

            case 'employee':
            case 'my-profile':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-employee.php', 'erp-new-employee' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/row-employee.php', 'erp-employee-row' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/employment-status.php', 'erp-employment-status' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/compensation.php', 'erp-employment-compensation' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/job-info.php', 'erp-employment-jobinfo' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/work-experience.php', 'erp-employment-work-experience' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/education-form.php', 'erp-employment-education' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/performance-reviews.php', 'erp-employment-performance-reviews' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/performance-comments.php', 'erp-employment-performance-comments' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/performance-goals.php', 'erp-employment-performance-goals' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/dependents.php', 'erp-employment-dependent' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-dept.php', 'erp-new-dept' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-designation.php', 'erp-new-desig' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/employee-terminate.php', 'erp-employment-terminate' );
                break;

            case 'leave':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-reject.php', 'erp-hr-leave-reject-js-tmp' );
        }

        // leave menu
        // $hook = str_replace( sanitize_title( __( 'Leave', 'erp' ) ), 'leave', $current_screen->base );
        $sub_section    =   isset( $_GET['sub-section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) ) : '';
        switch ( $sub_section ) {
            case 'policies':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-policy.php', 'erp-leave-policy' );
                break;

            case 'holidays':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/holiday.php', 'erp-hr-holiday-js-tmp' );
                break;

            case 'leave-requests':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-reject.php', 'erp-hr-leave-reject-js-tmp' );
                break;

            default:
                # code...
                break;
        }

    }


    /**
     * Load HRM rest controllers
     *
     * @since 1.3.0
     *
     * @param $controller
     *
     * @return array
     */
    public function load_hrm_rest_controllers( $controller ) {
        $hrm_controller = [
            '\WeDevs\ERP\HRM\API\Employees_Controller',
            '\WeDevs\ERP\HRM\API\Departments_Controller',
            '\WeDevs\ERP\HRM\API\Designations_Controller',
            '\WeDevs\ERP\HRM\API\Birthdays_Controller',
            '\WeDevs\ERP\HRM\API\HRM_Reports_Controller',
            '\WeDevs\ERP\HRM\API\Leave_Entitlements_Controller',
            '\WeDevs\ERP\HRM\API\Leave_Holidays_Controller',
            '\WeDevs\ERP\HRM\API\Leave_Policies_Controller',
            '\WeDevs\ERP\HRM\API\Leave_Requests_Controller',
            '\WeDevs\ERP\HRM\API\Announcements_Controller',
            '\WeDevs\ERP\HRM\API\Company_Controller',
        ];
        $hrm_controller = apply_filters( 'erp_hrm_rest_api_controllers', $hrm_controller );

        return array_merge( $controller, $hrm_controller );
    }
}

