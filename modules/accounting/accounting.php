<?php
/*
Plugin Name: WP ERP Accounting
Plugin URI: https://wperp.com/
Description: WP ERP Accounting Plugin
Version: 2.0
Author: weDevs
Author URI: https://wperp.com/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: erp
Domain Path: /languages
*/



namespace WeDevs\ERP\Accounting;

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * ERP_Accounting class
 *
 * @class ERP_Accounting The class that holds the entire ERP_Accounting plugin
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
        do_action('erp_accounting_loaded');
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
            return $this->container[ $prop ];
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
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
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
    }

    /**
     * Load the plugin after all plugins are loaded
     *
     * @return void
     */
    public function plugin_init() {
        $this->includes();
        $this->init_hooks();
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
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/capabilities.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/common.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/taxes.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/products.php';
	    require_once ERP_ACCOUNTING_INCLUDES . '/functions/product_cats.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/ledger-accounts.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/invoices.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/payments.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/bills.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/pay_bills.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/purchases.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/pay_purchases.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/transfer.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/charts.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/functions/reports.php';
    }

    /**
     *
     * Includes Classes
     *
     */
    public function include_classes() {
        require_once ERP_ACCOUNTING_API . '/class-controller-rest-api.php';
        require_once ERP_ACCOUNTING_INCLUDES . '/class-assets.php';

        if ( $this->is_request( 'admin' ) ) {
            require_once ERP_ACCOUNTING_INCLUDES . '/class-admin.php';
        }
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks() {

        add_action( 'init', [ $this, 'init_classes' ] );

    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes() {

        if ( $this->is_request( 'admin' ) ) {
            $this->container['admin'] = new \WeDevs\ERP\Accounting\INCLUDES\Admin();
        }

        $this->container['rest'] = new \WeDevs\ERP\Accounting\API\REST_API();
        $this->container['assets'] = new \WeDevs\ERP\Accounting\INCLUDES\Assets();
    }


    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );

            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
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

