<?php

namespace WeDevs\ERP\Accounting;

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Accounting class
 *
 * @class Accounting The class that holds the entire Accounting module
 */
final class Accounting {

    /**
     * The main plugin instance
     *
     * @var Object
     */
    private $plugin;

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Constructor for the ERP_Accounting class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct( $plugin ) {
        // prevent duplicate loading
        if ( did_action( 'erp_accounting_loaded' ) ) {
            return;
        }

        $this->plugin = $plugin;

        $this->deactive_addon();

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // load the module
        add_action( 'erp_loaded', array( $this, 'plugin_init' ) );

        // trigger after accounting module loaded
        do_action( 'erp_accounting_loaded' );
    }

    /**
     * Initializes the ERP_Accounting() class
     *
     * Checks for an existing ERP_Accounting() instance
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
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[$prop] );
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'ERP_ACCOUNTING_FILE', __FILE__ );
        define( 'ERP_ACCOUNTING_PATH', dirname( ERP_ACCOUNTING_FILE ) );
        define( 'ERP_ACCOUNTING_INCLUDES', ERP_ACCOUNTING_PATH . '/includes' );
        define( 'ERP_ACCOUNTING_API', ERP_ACCOUNTING_PATH . '/api' );
        define( 'ERP_ACCOUNTING_URL', plugins_url( '', ERP_ACCOUNTING_FILE ) );
        define( 'ERP_ACCOUNTING_ASSETS', ERP_ACCOUNTING_URL . '/assets' );
        define( 'ERP_ACCOUNTING_VIEWS', ERP_ACCOUNTING_PATH . '/includes/views' );
    }

    /**
     * Load the plugin after all plugins are loaded
     *
     * @return void
     */
    public function plugin_init() {
        $this->includes();
        $this->init_actions();
        $this->init_filters();
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes() {
        $this->include_functions();
        $this->include_classes();
    }

    /**
     *
     * Includes Rest API helper Functions
     *
     */
    public function include_functions() {
        foreach ( glob( ERP_ACCOUNTING_INCLUDES . '/functions/*.php' ) as $filename ) {
            include_once $filename;
        }
    }

    /**
     *
     * Includes Classes
     *
     */
    public function include_classes() {
        require_once ERP_ACCOUNTING_API . '/class-controller-rest-api.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/classes/class-assets.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/classes/class-ledger-map.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/classes/class-send-email.php';

        if ( $this->is_request( 'admin' ) ) {
            require_once ERP_ACCOUNTING_INCLUDES . '/classes/class-admin.php';
        }
    }

    /**
     * Init the plugin actions
     *
     * @return void
     */
    public function init_actions() {
        add_action( 'init', [ $this, 'init_classes' ] );
    }

    /**
     * Init the plugin filters
     *
     * @return void
     */
    public function init_filters() {
        add_filter( 'erp_settings_pages', [ $this, 'add_settings_page' ] );
    }

    /**
     * Add settings page
     *
     * @param array $settings
     *
     * @return array
     */
    public function add_settings_page( $settings = array() ) {
        $settings[] = include __DIR__ . '/includes/classes/class-settings.php';
        return $settings;
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes() {

        if ( $this->is_request( 'admin' ) ) {
            $this->container['admin'] = new \WeDevs\ERP\Accounting\Includes\Classes\Admin();
        }

        $this->container['rest']   = new \WeDevs\ERP\Accounting\API\REST_API();
        $this->container['assets'] = new \WeDevs\ERP\Accounting\Includes\Classes\Assets();
    }


    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'cron' :
                return defined( 'DOING_CRON' );

            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Returns true if the request is a non-legacy REST API request.
     *
     * Legacy REST requests should still run some extra code for backwards compatibility.
     *
     * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
     *
     * @return bool
     */
    public function is_rest_api_request() {
        if ( empty( $_SERVER['REQUEST_URI'] ) ) {
            return false;
        }

        $rest_prefix         = trailingslashit( rest_get_url_prefix() );
        $is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );

        return apply_filters( 'wperp_acct_is_rest_api_request', $is_rest_api_request );
    }

    /**
     * Backward compatibility
     *
     * Check if the previous accounting add-on installed.
     * If found, deactivate the add-on
     *
     * @return void
     */
    function deactive_addon() {
        /**
         * Detect plugin. For use on Front End only.
         */
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        // check for plugin using plugin name
        if ( is_plugin_active( 'accounting/accounting.php' ) ) {
            deactivate_plugins( 'accounting/accounting.php' );
        }
    }

} // ERP_Accounting

