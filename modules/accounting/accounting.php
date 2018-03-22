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
     * The main plugin instance
     *
     * @var Object
     */
    private $plugin;

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

        // pdf plugin is not installed notice
        if ( empty( get_option( 'pdf-notice-dismissed' ) ) ) {
            add_action( 'admin_notices', array( $this, 'admin_notice' ) );
        }

        add_action( 'wp_ajax_dismiss_pdf_notice', array( $this, 'dismiss_pdf_notice' ) );
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
        require_once WPERP_ACCOUNTING_PATH . '/includes/functions-html.php';

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

    public function add_settings_page( $settings = array() ) {
        $settings[] = include __DIR__ . '/includes/class-settings.php';
        return $settings;
    }

    /**
     * Print JS templates in footer
     *
     * @return void
     */
    public function admin_js_templates() {
        global $current_screen;

        $hook = str_replace( sanitize_title( __( 'Accounting', 'erp' ) ) , 'accounting', $current_screen->base );

        if ( $hook == 'accounting_page_erp-accounting-expense' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/invoice.php', 'erp-ac-invoice-payment-pop' );
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/vendor.php', 'erp-ac-new-vendor-content-pop' );
        }

        if ( $hook == 'accounting_page_erp-accounting-bank' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/bank.php', 'erp-ac-transfer-money-pop' );
        }

        if ( $hook == 'accounting_page_erp-accounting-sales' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/invoice.php', 'erp-ac-invoice-payment-pop' );
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/customer.php', 'erp-ac-new-customer-content-pop' );
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/send-invoice.php', 'erp-ac-send-email-invoice-pop' );
        }

        if ( $hook == 'erp-settings_page_erp-settings' && isset( $_GET['section'] ) && $_GET['section'] == 'erp_ac_tax' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/new-tax-form.php', 'erp-ac-new-tax-form-popup' );
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/tax-items.php', 'erp-ac-items-details-popup' );
        }

        if ( $hook == 'accounting_page_erp-accounting-sales' || $hook == 'accounting_page_erp-accounting-expense' ) {
            erp_get_js_template( WPERP_ACCOUNTING_JS_TMPL . '/trash.php', 'erp-ac-trash-form-popup' );
        }
    }

    /**
     * Show notice if PDF plugin is not installed
     *
     * @since 1.3.6
     *
     * @return void
     */
    public function admin_notice() {
        if ( current_user_can( 'install_plugins' ) ) {

            $action      = empty($_GET['erp-pdf']) ? '' : \sanitize_text_field($_GET['erp-pdf']);
            $plugin      = 'erp-pdf-invoice/wp-erp-pdf.php';
            $pdf_install = new \WeDevs\ERP\Accounting\PDF_Install();

            if ($action === 'install') {
                $pdf_install->install_plugin('https://downloads.wordpress.org/plugin/erp-pdf-invoice.1.0.0.zip');
            } elseif ($action === 'active') {
                $pdf_install->activate_pdf_plugin($plugin);
            }

            if (\file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
                if (! \is_plugin_active($plugin)) {
                    $this->pdf_notice_message('active');
                }
            } else {
                $this->pdf_notice_message('install');
            }

        }
    }

    /**
     * PDF notice message
     *
     * @since 1.3.6
     *
     * @param String $type
     *
     * @return void
     */
    public function pdf_notice_message( $type ) {
        $actual_link = esc_url( (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" );
        $sign = empty( $_GET ) ? '?' : '&';

        echo '<div class="updated notice is-dismissible notice-pdf"><p>';
        echo __( 'Please ' . $type . ' <a href="' . $actual_link . $sign . 'erp-pdf=' . $type . '">WP ERP PDF</a> extension to get PDF export feature.', 'erp' );
        echo '</p></div>';
    }

    /**
     * Dismiss PDF notice message
     *
     * @since 1.3.6
     *
     * @return void
     */
    public function dismiss_pdf_notice() {
        update_option( 'pdf-notice-dismissed', 1 );
    }

}
