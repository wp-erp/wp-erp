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
     * @return void
     */
    private function includes() {
        require_once WPERP_HRM_PATH . '/admin/class-menu.php';

        require_once WPERP_HRM_PATH . '/includes/functions.php';
        require_once WPERP_HRM_PATH . '/includes/functions-department.php';
        require_once WPERP_HRM_PATH . '/includes/functions-designation.php';
        require_once WPERP_HRM_PATH . '/includes/functions-employee.php';
        require_once WPERP_HRM_PATH . '/includes/functions-leave.php';
    }

    /**
     * Initialize WordPress action hooks
     *
     * @return void
     */
    private function init_actions() {
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
        $this->action( 'admin_footer', 'admin_js_templates' );
    }

    /**
     * Initialize WordPress filter hooks
     *
     * @return void
     */
    private function init_filters() {

    }

    /**
     * Init classes
     *
     * @return void
     */
    private function init_classes() {
        new Ajax_Handler();
        new Form_Handler();
        new Settings();
    }

    /**
     * Load admin scripts and styles
     *
     * @param  string
     *
     * @return void
     */
    public function admin_scripts( $hook ) {
        // var_dump( $hook );
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_media();
        wp_enqueue_script( 'erp-select2' );
        wp_enqueue_script( 'wp-erp-hr', WPERP_HRM_ASSETS . "/js/hrm$suffix.js", array( 'wp-erp-script' ), date( 'Ymd' ), true );
        wp_enqueue_script( 'wp-erp-hr-leave', WPERP_HRM_ASSETS . "/js/leave$suffix.js", array(
            'wp-erp-script',
            'wp-color-picker'
        ), date( 'Ymd' ), true );
        $localize_script = apply_filters( 'erp_hr_localize_script', array(
            'nonce'              => wp_create_nonce( 'wp-erp-hr-nonce' ),
            'popup'              => array(
                'dept_title'        => __( 'New Department', 'wp-erp' ),
                'dept_submit'       => __( 'Create Department', 'wp-erp' ),
                'dept_update'       => __( 'Update Department', 'wp-erp' ),
                'desig_title'       => __( 'New Designation', 'wp-erp' ),
                'desig_submit'      => __( 'Create Designation', 'wp-erp' ),
                'desig_update'      => __( 'Update Designation', 'wp-erp' ),
                'employee_title'    => __( 'New Employee', 'wp-erp' ),
                'employee_create'   => __( 'Create Employee', 'wp-erp' ),
                'employee_update'   => __( 'Update Employee', 'wp-erp' ),
                'employment_status' => __( 'Employment Status', 'wp-erp' ),
                'update_status'     => __( 'Update', 'wp-erp' ),
                'policy'            => __( 'Leave Policy', 'wp-erp' ),
                'policy_create'     => __( 'Create Policy', 'wp-erp' ),
            ),
            'emp_upload_photo'   => __( 'Upload Employee Photo', 'wp-erp' ),
            'emp_set_photo'      => __( 'Set Photo', 'wp-erp' ),
            'confirm'            => __( 'Are you sure?', 'wp-erp' ),
            'delConfirmDept'     => __( 'Are you sure to delete this department?', 'wp-erp' ),
            'delConfirmEmployee' => __( 'Are you sure to delete this employee?', 'wp-erp' ),
            'delConfirmEmployeeNote' => __( 'Are you sure to delete this employee note?', 'wp-erp' ),
        ) );

        // if its an employee page
        if ( 'hr-management_page_erp-hr-employee' == $hook ) {
            wp_enqueue_script( 'post' );

            $employee                          = new Employee();
            $localize_script['employee_empty'] = $employee->to_array();
        }

        wp_localize_script( 'wp-erp-hr', 'wpErpHr', $localize_script );

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'erp-select2' );
        wp_enqueue_style( 'erp-style' );
    }

    /**
     * Print JS templates in footer
     *
     * @return void
     */
    public function admin_js_templates() {
        global $current_screen;

        //var_dump( $current_screen ); die();

        switch ($current_screen->base) {
            case 'hr-management_page_erp-hr-depts':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-dept.php', 'erp-new-dept' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/row-dept.php', 'erp-dept-row' );
                break;

            case 'hr-management_page_erp-hr-designation':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-designation.php', 'erp-new-desig' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/row-desig.php', 'erp-desig-row' );
                break;

            case 'hr-management_page_erp-hr-employee':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-employee.php', 'erp-new-employee' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/row-employee.php', 'erp-employee-row' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/employment-status.php', 'erp-employment-status' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/compensation.php', 'erp-employment-compensation' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/job-info.php', 'erp-employment-jobinfo' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/work-experience.php', 'erp-employment-work-experience' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/education-form.php', 'erp-employment-education' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/dependents.php', 'erp-employment-dependent' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-dept.php', 'erp-new-dept' );
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/new-designation.php', 'erp-new-desig' );
                break;

            case 'leave_page_erp-leave-policies':
                erp_get_js_template( WPERP_HRM_JS_TMPL . '/leave-policy.php', 'erp-leave-policy' );
                break;

            default:
                # code...
                break;
        }

    }
}
