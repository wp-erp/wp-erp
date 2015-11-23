<?php
namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * The HRM Class
 *
 * This is loaded in `init` action hook
 */
class Customer_Relationship {

    use Hooker;

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

        // Initialize the classes
        $this->init_classes();

        // Initialize the action hooks
        $this->init_actions();

        // Initialize the filter hooks
        $this->init_filters();

        // Trigger after CRM module loaded
        do_action( 'erp_crm_loaded' );
    }

    /**
     * Define the plugin constants
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPERP_CRM_FILE', __FILE__ );
        define( 'WPERP_CRM_PATH', dirname( __FILE__ ) );
        define( 'WPERP_CRM_VIEWS', dirname( __FILE__ ) . '/views' );
        define( 'WPERP_CRM_JS_TMPL', WPERP_CRM_VIEWS . '/js-templates' );
        define( 'WPERP_CRM_ASSETS', plugins_url( '/assets', __FILE__ ) );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {
        require_once WPERP_CRM_PATH . '/includes/function-customer.php';
        require_once WPERP_CRM_PATH . '/admin/class-menu.php';
    }

    /**
     * Init classes
     *
     * @return void
     */
    private function init_classes() {
        if ( is_admin() ) {
            new Ajax_Handler();
            new Form_Handler();
        }
    }

    /**
     * Initialize WordPress action hooks
     *
     * @return void
     */
    private function init_actions() {
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
        $this->action( 'admin_footer', 'load_js_template', 10 );
    }

    /**
     * Initialize WordPress filter hooks
     *
     * @return void
     */
    private function init_filters() {

    }

    public function admin_scripts( $hook ) {
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_enqueue_media();
        wp_enqueue_script( 'erp-tiptip' );
        wp_enqueue_script( 'wp-erp-crm', WPERP_CRM_ASSETS . "/js/crm$suffix.js", array( 'wp-erp-script' ), date( 'Ymd' ), true );

        $localize_script = apply_filters( 'erp_crm_localize_script', array(
            'nonce'                 => wp_create_nonce( 'wp-erp-crm-nonce' ),
            'popup'                 => array(
                'customer_title'         => __( 'Add New Customer', 'wp-erp' ),
                'customer_update_title'  => __( 'Edit Customer', 'wp-erp' ),
            ),
            'add_submit'            => __( 'Add New', 'wp-erp' ),
            'update_submit'         => __( 'Update', 'wp-erp' ),
            'customer_upload_photo' => __( 'Upload Photo', 'wp-erp' ),
            'customer_set_photo'    => __( 'Set Photo', 'wp-erp' ),
            'confirm'               => __( 'Are you sure?', 'wp-erp' ),
            'delConfirmCustomer'     => __( 'Are you sure to delete this customer?', 'wp-erp' ),
        ) );

        // if its an customer page
        if ( 'crm_page_erp-sales-customers' == $hook ) {
            wp_enqueue_script( 'post' );

            $customer = new Customer();
            $country  = \WeDevs\ERP\Countries::instance();

            $localize_script['customer_empty'] = $customer->to_array();
            $localize_script['wpErpCountries'] = $country->load_country_states();
        }


        wp_localize_script( 'wp-erp-crm', 'wpErpCrm', $localize_script );
    }

    public function load_js_template() {
        global $current_screen;

        // var_dump( $current_screen ); die();

        switch ( $current_screen->base ) {
            case 'crm_page_erp-sales-customers':
                erp_get_js_template( WPERP_CRM_JS_TMPL . '/new-customer.php', 'erp-crm-new-contact' );
                break;

            default:
                # code...
                break;
        }
    }
}
