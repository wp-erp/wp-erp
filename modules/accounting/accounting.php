<?php
namespace WeDevs\ERP\Accounting;

use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * WeDevs_ERP_Accounting class
 *
 * @class WeDevs_ERP_Accounting The class that holds the entire WeDevs_ERP_Accounting plugin
 */
class Accounting {

    use Hooker;

    /**
     * @var string
     */
    public $version = '1.1';


    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.4.0';

    /**
     * Initializes the WeDevs_ERP_Accounting() class
     *
     * Checks for an existing WeDevs_ERP_Accounting() instance
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
     * Constructor for the WeDevs_ERP_Accounting class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct() {
        //$this->deactive_accounting_module();
         // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // installation
        //register_activation_hook( __FILE__, array( $this, 'activate' ) );

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        // load the module
        add_action( 'erp_loaded', array( $this, 'plugin_init' ) );

        // plugin not installed notice
        add_action( 'admin_notices', array( $this, 'admin_notice' ) );

        //add_action( 'admin_init', array( $this, 'test' ) );
    }

    // function deactive_accounting_module() {
    //     /**
    //      * Detect plugin. For use on Front End only.
    //      */
    //     include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    //     // check for plugin using plugin name
    //     if ( is_plugin_active( 'accounting/accounting.php' ) ) {
    //         $accounting = dirname( WPERP_PATH ) . '/accounting/accounting.php';
    //         deactivate_plugins( $accounting );            
    //     } 
    // }

    /**
     * Plugin activation
     *
     * @return void
     */
    public function activate() {
        //$installer = new WeDevs\ERP\Accounting\Install();
        //$installer->install();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'accounting', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Init the accounting module
     *
     * @return void
     */
    public function plugin_init() {
        $this->init_classes();
        $this->init_actions();
        $this->init_filters();
    }

    /**
     * Define the plugin constants
     *
     * @return void
     */
    private function define_constants() {
        
        $this->define( 'WPERP_ACCOUNTING_VERSION', $this->version );
        $this->define( 'WPERP_ACCOUNTING_PATH', dirname( __FILE__ ) );
        $this->define( 'WPERP_ACCOUNTING_URL', plugins_url( '', __FILE__ ) );
        $this->define( 'WPERP_ACCOUNTING_ASSETS', WPERP_ACCOUNTING_URL . '/assets' );
        $this->define( 'WPERP_ACCOUNTING_JS_TMPL', WPERP_ACCOUNTING_PATH . '/includes/views/js-templates' );
        $this->define( 'WPERP_ACCOUNTING_VIEWS', WPERP_ACCOUNTING_PATH . '/includes/views' );
    }

    /**
     * Define constant if not already set
     *
     * @param  string $name
     * @param  string|bool $value
     * @return type
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {

        if ( function_exists( 'erp_ac_get_manager_role' ) ) {
            return;
        }        
    
        require_once WPERP_ACCOUNTING_PATH . '/includes/function-capabilities.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/actions-filters.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions-transaction.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions-chart.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions-dashboard.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions-reporting.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions-bulk-action.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions-url.php';
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions-tax.php';

        // cli command
        if ( defined('WP_CLI') && WP_CLI ) {
            include WPERP_ACCOUNTING_PATH . '/includes/cli/commands.php';
        }
    }

    /**
     * Initialize the classes
     *
     * @return void
     */
    public function init_classes() {
        new Logger();
        new Admin_Menu();
        new Form_Handler();
        new User_Profile();
        //new Updates();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new Ajax_Handler();
        }
    }

    /**
     * Init the plugin actions
     *
     * @return void
     */
    public function init_actions() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_footer', array( $this, 'admin_js_templates' ) );
    }

    /**
     * Init the plugin filters
     *
     * @return void
     */
    public function init_filters() {
        add_filter( 'erp_settings_pages', array( $this, 'add_settings_page' ) );
    }

    public function enqueue_scripts() {
       // styles
       wp_enqueue_style( 'wp-erp-ac-styles', WPERP_ACCOUNTING_ASSETS . '/css/accounting.css', false, date( 'Ymd' ) );
       wp_enqueue_script('erp-sweetalert');
       // scripts
       wp_enqueue_script( 'accounting', WPERP_ACCOUNTING_ASSETS . '/js/accounting.min.js', array( 'jquery' ), date( 'Ymd' ), true );
       wp_enqueue_script( 'wp-erp-ac-js', WPERP_ACCOUNTING_ASSETS . '/js/erp-accounting.js', array( 'jquery', 'erp-tiptip' ), date( 'Ymd' ), true );

       $erp_ac_de_separator = erp_get_option('erp_ac_de_separator');
       $erp_ac_th_separator = erp_get_option('erp_ac_th_separator');
       $erp_ac_nm_decimal = erp_get_option('erp_ac_nm_decimal');

       wp_localize_script( 'wp-erp-ac-js', 'ERP_AC', array(

           'nonce'              => wp_create_nonce( 'erp-ac-nonce' ),
           'confirmMsg'         => __( 'Are you sure?', 'erp-accounting' ),
           'ajaxurl'            => admin_url( 'admin-ajax.php' ),
           'decimal_separator'  => empty( $erp_ac_de_separator ) ? '.' : erp_get_option('erp_ac_de_separator'),
           'thousand_separator' => empty( $erp_ac_th_separator ) ? ',' : erp_get_option('erp_ac_th_separator'),
           'number_decimal'     => empty( $erp_ac_nm_decimal ) ? '2' : erp_get_option('erp_ac_nm_decimal'),
           'currency'           => erp_get_option('erp_ac_currency'),
           'symbol'             => erp_ac_get_currency_symbol(),
           'message'    => erp_ac_message(),
           'plupload'   => array(
               'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'erp_ac_featured_img' ),
               'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
               'filters'          => array( array('title' => __( 'Allowed Files', 'accounting' ), 'extensions' => '*')),
               'multipart'        => true,
               'urlstream_upload' => true,
           )
       ));

       wp_enqueue_style( 'erp-sweetalert' );
   }

    public function add_settings_page( $settings = array() ) {

        $settings[] = include __DIR__ . '/includes/class-settings.php';

        return $settings;
    }

    /**
     * Give notice if ERP is not installed
     *
     * @return void
     */
    public function admin_notice() {
        if ( ! function_exists( 'wperp' ) ) {
            echo '<div class="message error"><p>';
            echo __( '<strong>Error:</strong> WP ERP Plugin is required to use accounting plugin.', 'accounting' );
            echo '</p></div>';
        }
    }

    /**
     * Print JS templates in footer
     *
     * @return void
     */
    public function admin_js_templates() {
        global $current_screen;

        if ( $current_screen->base == 'accounting_page_erp-accounting-expense' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/vendor-credit-single.php', 'erp-ac-vendoer-credit-single-payment' );
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/vendor.php', 'erp-ac-new-vendor-content-pop' );
        }

        if ( $current_screen->base == 'accounting_page_erp-accounting-bank' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/bank.php', 'erp-ac-transfer-money-pop' );
        }

        if ( $current_screen->base == 'accounting_page_erp-accounting-sales' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/invoice.php', 'erp-ac-invoice-payment-pop' );
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/customer.php', 'erp-ac-new-customer-content-pop' );
        }

        if ( $current_screen->base == 'erp-settings_page_erp-settings' && isset( $_GET['section'] ) && $_GET['section'] == 'erp_ac_tax' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/new-tax-form.php', 'erp-ac-new-tax-form-popup' );
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/tax-items.php', 'erp-ac-items-details-popup' );   
        }
    }

} 




