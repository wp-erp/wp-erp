<?php
namespace WeDevs\ERP\HRM;

/**
 * The HRM Class
 *
 * This is loaded in `init` action hook
 */
class Human_Resource {

    /**
     * Initializes the WeDevs_ERP() class
     *
     * Checks for an existing WeDevs_ERP() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Kick-in the class
     *
     * @return void
     */
    public function __construct() {

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

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

        require_once WPERP_HRM_PATH . '/includes/class-department.php';
        require_once WPERP_HRM_PATH . '/includes/class-walker-department.php';
        require_once WPERP_HRM_PATH . '/includes/class-designation.php';
        require_once WPERP_HRM_PATH . '/includes/class-ajax.php';
    }

    /**
     * Initialize WordPress action hooks
     *
     * @return void
     */
    private function init_actions() {
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts' ) );
    }

    /**
     * Initialize WordPress filter hooks
     *
     * @return void
     */
    private function init_filters() {

    }

    /**
     * Load admin scripts and styles
     *
     * @param  string
     *
     * @return void
     */
    public function admin_scripts( $hook ) {
        $suffix = ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_media( );
        wp_enqueue_script( 'wp-erp-hr', WPERP_HRM_ASSETS . "/js/hrm$suffix.js", array( 'wp-erp-script' ), date( 'Ymd' ), true );
        wp_localize_script( 'wp-erp-hr', 'wpErpHr', array(
            'nonce' => wp_create_nonce( 'wp-erp-hr-nonce' ),
            'popup' => array(
                'dept_title'   => __( 'New Department', 'wp-erp' ),
                'dept_submit'  => __( 'Create Department', 'wp-erp' ),
                'dept_update'  => __( 'Update Department', 'wp-erp' ),
                'desig_title'  => __( 'New Designation', 'wp-erp' ),
                'desig_submit' => __( 'Create Designation', 'wp-erp' ),
                'desig_update' => __( 'Update Designation', 'wp-erp' )
            ),
            'delConfirmDept' => __( 'Are you sure to delete this department?', 'wp-erp' )
        ) );
    }
}

Human_Resource::init();