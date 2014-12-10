<?php
namespace WeDevs\ERP\CRM;

/**
 * The HRM Class
 *
 * This is loaded in `init` action hook
 */
class Customer_Relationship {

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
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {
        require_once WPERP_HRM_PATH . '/admin/class-menu.php';
    }

    /**
     * Initialize WordPress action hooks
     *
     * @return void
     */
    private function init_actions() {

    }
}

Customer_Relationship::init();