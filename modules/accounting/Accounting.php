<?php

namespace WeDevs\ERP\Accounting\Main;

use WeDevs\ERP\Accounting\API\REST_API;
use WeDevs\ERP\Accounting\Classes\Admin;
use WeDevs\ERP\Accounting\Classes\Assets;
use WeDevs\ERP\Accounting\Classes\Emailer;
use WeDevs\ERP\Accounting\Classes\LedgerMap;
use WeDevs\ERP\Accounting\Classes\PDFInstall;
use WeDevs\ERP\Accounting\Classes\SendEmail;
use WeDevs\ERP\Accounting\Classes\Settings;
use WeDevs\ERP\Accounting\Classes\UserProfile;

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
     * @var object
     */
    private $plugin;

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

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
        add_action( 'erp_loaded', [ $this, 'plugin_init' ] );

        // trigger after accounting module loaded
        do_action( 'erp_accounting_loaded' );

        if( is_admin() ){
        // pdf plugin is not installed notice
        if ( ! is_plugin_active( 'erp-pdf-invoice/wp-erp-pdf.php' ) ) {
            if ( 'hide' !== get_option( 'pdf-notice-dismissed' ) ) {
                add_action( 'admin_notices', [ $this, 'admin_pdf_notice' ] );
            }
        }

        add_action( 'wp_ajax_dismiss_pdf_notice', [ $this, 'dismiss_pdf_notice' ] );
        }
    }

    /**
     * Show notice if PDF plugin is not installed
     *
     * @return void
     */
    public function admin_pdf_notice() {
        if ( current_user_can( 'install_plugins' ) ) {
            $action      = empty( $_GET['erp-pdf'] ) ? '' : \sanitize_text_field( wp_unslash( $_GET['erp-pdf'] ) );
            $plugin      = 'erp-pdf-invoice/wp-erp-pdf.php';
            $pdf_install = new PDFInstall();

            if ( $action === 'install' ) {
                $pdf_install->install_plugin( 'https://downloads.wordpress.org/plugin/erp-pdf-invoice.zip' );
            } elseif ( $action === 'active' ) {
                $pdf_install->activate_pdf_plugin( $plugin );
            }

            if ( \file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
                if ( ! \is_plugin_active( $plugin ) ) {
                    $this->pdf_notice_message( 'active' );
                }
            } else {
                $this->pdf_notice_message( 'install' );
            }
        }
    }

    /**
     * PDF notice message
     *
     * @param string $type
     *
     * @return void
     */
    public function pdf_notice_message( $type ) {
        $host     = empty( $_SERVER['HTTP_HOST'] ) ? '' : esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) );
        $uri      = empty( $_SERVER['REQUEST_URI'] ) ? '' : esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
        $sign     = empty( $_GET ) ? '?' : '&';

        $actual_link = "{$host}{$uri}";

        echo '<div class="updated notice is-dismissible notice-pdf"><p>';
        // translators: %1$s: type, %2$s: link, %3$s: type
        echo wp_kses_post( sprintf( __( 'Please %1$s <a href="%2$serp-pdf=%3$s">WP ERP PDF</a> extension to get PDF export feature.', 'erp' ), $type, $actual_link . $sign, $type ) );
        echo '</p></div>';
    }

    /**
     * Dismiss PDF notice message
     *
     * @return void
     */
    public function dismiss_pdf_notice() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-nonce' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'erp' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( esc_html__( 'You have no permission to do that', 'erp' ) );
        }

        update_option( 'pdf-notice-dismissed', 'hide' );
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

        /*** Init emailer class **/
        new Emailer();
    }

    /**
     * Includes Rest API helper Functions
     */
    public function include_functions() {
        foreach ( glob( ERP_ACCOUNTING_INCLUDES . '/functions/*.php' ) as $filename ) {
            include_once $filename;
        }
    }

    /**
     * Includes Classes
     */
    public function include_classes() {
        new REST_API();
        new Assets();
        new SendEmail();
        new UserProfile();
    }

    /**
     * Init the plugin actions
     *
     * @return void
     */
    public function init_actions() {
        add_action( 'init', [ $this, 'init_classes' ] );
        add_action( 'template_redirect', [ $this, 'readonly_invoice_template' ] );
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
    public function add_settings_page( $settings = [] ) {
        $settings[] = new Settings();

        return $settings;
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes() {
        if ( $this->is_request( 'admin' ) ) {
            $this->container['admin'] = new Admin();
        }

        $this->container['rest']    = new REST_API();
        $this->container['assets']  = new Assets();
        $this->container['profile'] = new UserProfile();
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined( 'DOING_AJAX' );

            case 'cron':
                return defined( 'DOING_CRON' );

            case 'frontend':
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
        $is_rest_api_request = ( false !== strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $rest_prefix ) );

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
    public function deactive_addon() {
        /**
         * Detect plugin. For use on Front End only.
         */
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        // check for plugin using plugin name
        if ( is_plugin_active( 'accounting/accounting.php' ) ) {
            deactivate_plugins( 'accounting/accounting.php' );
        }
    }

    /**
     * Callback to template_redirect hook
     * Shows template when invoice readonly link is called
     *
     * @return mixed
     */
    public function readonly_invoice_template() {
        $query          = isset( $_REQUEST['query'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['query'] ) ) : '';
        $transaction_id = isset( $_REQUEST['trans_id'] ) ? intval( $_REQUEST['trans_id'] ) : '';
        $auth_id        = isset( $_REQUEST['auth'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['auth'] ) ) : '';
        $verified       = false;

        if ( ! $query || ! $transaction_id || ! $auth_id ) {
            return;
        }

        $transaction = erp_acct_get_transaction( $transaction_id );

        if ( $transaction ) {
            $verified = erp_acct_verify_invoice_link_hash( $transaction_id, $transaction['type'], $auth_id );
        }

        if ( $verified ) {
            add_action( 'erp_readonly_invoice_header', [ $this, 'invoice_frontend_style' ] );
            include ERP_ACCOUNTING_VIEWS . '/transactions/invoice-readonly.php';
            exit();
        }
    }

    /**
     * Handles CSS styling for invoice frontend css
     *
     * @return void
     */
    public function invoice_frontend_style() {
        ?>
        <link rel='stylesheet' href="<?php echo esc_url_raw( WPERP_ASSETS . '/css/invoice-front.css' ); ?>">
        <?php
    }
} // ERP_Accounting
