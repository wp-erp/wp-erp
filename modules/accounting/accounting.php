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

        // dry check on older PHP versions, if found deactivate itself with an error
        register_activation_hook( __FILE__, array( $this, 'auto_deactivate' ) );

        // bail out if older PHP versions
        if ( ! $this->is_supported_php() ) {
            return;
        }

         // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        // installation
        register_activation_hook( __FILE__, array( $this, 'activate' ) );

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        // load the module
        add_action( 'erp_loaded', array( $this, 'plugin_init' ) );

        // plugin not installed notice
        add_action( 'admin_notices', array( $this, 'admin_notice' ) );

        //add_action( 'admin_init', array( $this, 'test' ) );
    }

    function faker() {
        $results = [];

        $fake_customers = [
            [
                'first_name'  => 'Baker',
                'last_name'   => 'Vingle',
                'email'       => 'InezBSimpson@inbound.plus',
                'company'     => 'Payless Cashways',
                'type'        => 'customer',
                'street_1'    => 'Dhanmondi',
                'city'        => 'Dhaka',
                'state'       => 'Bangladesh',
            ],
            [
                'first_name'  => 'Matthews',
                'last_name'   => 'Eugene',
                'email'       => 'EugeneMMatthews@inbound.plus',
                'company'     => 'The Original House of Pies',
                'type'        => 'customer',
                'street_1'    => 'Dhanmondi',
                'city'        => 'Dhaka',
                'state'       => 'Bangladesh',
            ],
            [
                'first_name'  => 'William',
                'last_name'   => 'Robertson',
                'email'       => 'WilliamCRobertson@inbound.plus',
                'company'     => 'Back To Basics Chiropractic Clinic',
                'type'        => 'customer',
                'street_1'    => 'Dhanmondi',
                'city'        => 'Dhaka',
                'state'       => 'Bangladesh',
            ]
        ];

        foreach ( $fake_customers as $customer ) { 
            $results[] = erp_insert_people( $customer );
        }

        return $results;
    }

    function faker_vendors() {
        $results = [];

        $fake_vendors = [
            [
                'first_name'  => 'Deborah',
                'last_name'   => 'Iverson',
                'email'       => 'DeborahRIverson@inbound.plus',
                'company'     => 'Endicott Johnson',
                'type'        => 'vendor',
                'street_1'    => 'Mohammadpur',
                'city'        => 'Dhaka',
                'state'       => 'Bangladesh',
            ],
            [
                'first_name'  => 'Linda',
                'last_name'   => 'Pinkston',
                'email'       => 'LindaJPinkston@inbound.plus',
                'company'     => 'Country Club Markets',
                'type'        => 'vendor',
                'street_1'    => 'Mohammadpur',
                'city'        => 'Dhaka',
                'state'       => 'Bangladesh',
            ],
            [
                'first_name'  => 'Raymond',
                'last_name'   => 'Reynolds',
                'email'       => 'RaymondAReynolds@inbound.plus',
                'company'     => 'Atlas Architectural Designs',
                'type'        => 'vendor',
                'street_1'    => 'Mohammadpur',
                'city'        => 'Dhaka',
                'state'       => 'Bangladesh',
            ]
        ];

        foreach ( $fake_vendors as $vendor ) { 
            $results[] = erp_insert_people( $vendor );
        }

        return $results;
    }

    function test() {
        $args       = array( 'type' => 'vendor' );
        $vendors    = erp_get_peoples( $args );
        $vendors_id = wp_list_pluck( $vendors, 'id' );
        
        if ( ! count( $vendors_id ) ) {
            $vendors_id = $this->faker_vendors();
        } 

        $sales_type     = ['invoice', 'payment'];
        $sales_ledger   = [53, 54, 55];
        $expense_type   = ['payment_voucher', 'vendor_credit'];
        $bank_accounts  = [7, 60];

        // insert some expense data
        for ($i = 0; $i <= 50; $i++) {
            $form_type   = $expense_type[ array_rand( $expense_type ) ];
            $trans_total = rand( 100, 20000 );
            $user_id     = $vendors_id[ array_rand( $vendors_id ) ];

            erp_ac_insert_transaction( [
                'type'            => 'expense',
                'form_type'       => $form_type,
                'account_id'      => ( $form_type == 'vendor_credit' ) ? 8 : $bank_accounts[ array_rand( $bank_accounts ) ],
                'status'          => 'closed',
                'user_id'         => $user_id,
                'billing_address' => 'Dhanmondi, Dhaka',
                'ref'             => '',
                'issue_date'      => date( 'Y-m-d', strtotime( '-' . $i . ' days' ) ),
                'due_date'        => ( $form_type == 'vendor_credit' ) ? date( 'Y-m-d', strtotime( '+' . $i + 7 . ' days' ) ) : null,
                'summary'         => '',
                'total'           => $trans_total,
                'trans_total'     => $trans_total,
                'files'           => '',
                'partial_id'      => [],
                'currency'        => '',
                'created_by'      => 1,
                'created_at'      => current_time( 'mysql' )
            ], [
                [
                    'account_id'  => rand( 24, 49 ),
                    'description' => 'Some random description',
                    'qty'         => 1,
                    'unit_price'  => $trans_total,
                    'discount'    => 0,
                    'line_total'  => $trans_total,
                ]
            ] );
        }

        $args = array( 'type'   => 'customer' );

        $peoples    = erp_get_peoples( $args );
        $peoples_id = wp_list_pluck( $peoples, 'id' );
        
        if ( ! count( $peoples ) ) {
            $peoples_id = $this->faker();
        } 
 
        $sales_ledger  = [53, 54, 55];
        $data_count    = 50;
        $expense_type  = ['payment_voucher', 'vendor_credit'];
        $sales_type    = ['invoice', 'payment'];
        $bank_accounts = [7, 60];
        
        for ($i = 0; $i < $data_count; $i++) {
            $form_type   = $sales_type[ array_rand( $sales_type ) ];
            $trans_total = rand( 100, 20000 );
            $user_id     = $peoples_id[ array_rand( $peoples_id ) ]; 
            
            erp_ac_insert_transaction( [
                'id'              => '',
                'type'            => 'sales',
                'form_type'       => $form_type,
                'account_id'      => ( $form_type == 'invoice' ) ? 1 : $bank_accounts[ array_rand( $bank_accounts ) ],
                'status'          => 'closed',
                'user_id'         => $user_id,
                'billing_address' => 'Dhanmondi, Dhaka',
                'ref'             => '',
                'issue_date'      => date( 'Y-m-d', strtotime( '-' . $i . ' days' ) ),
                'due_date'        => ( $form_type == 'invoice' ) ? date( 'Y-m-d', strtotime( '+' . $i + 7 . ' days' ) ) : null,
                'summary'         => '',
                'total'           => $trans_total,
                'trans_total'     => $trans_total,
                'files'           => '',
                'currency'        => erp_get_option('erp_ac_currency'),
                'created_by'      => 1,
                'created_at'      => current_time( 'mysql' ),
                'partial_id'      => [],
                'items_id'        => [$i => ''],
                'journals_id'     => [$i => ''],
                'line_total'      => [$trans_total]
            ], [
                [
                    'item_id'   => '',
                    'journal_id' => '',
                    'account_id'  => $sales_ledger[ array_rand( $sales_ledger ) ],
                    'description' => 'Some random description',
                    'qty'         => 1,
                    'unit_price'  => $trans_total,
                    'discount'    => 0,
                    'line_total'  => $trans_total,
                ]
            ] );
        }

        
    }

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
     * Check if the PHP version is supported
     *
     * @return bool
     */
    public function is_supported_php() {
        if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Bail out if the php version is lower than
     *
     * @return void
     */
    function auto_deactivate() {
        if ( $this->is_supported_php() ) {
            return;
        }

        deactivate_plugins( basename( __FILE__ ) );

        $error = __( '<h1>An Error Occured</h1>', 'accounting' );
        $error .= __( '<h2>Your installed PHP Version is: ', 'accounting' ) . PHP_VERSION . '</h2>';
        $error .= __( '<p>The <strong>Accounting</strong> plugin requires PHP version <strong>', 'accounting' ) . $this->min_php . __( '</strong> or greater', 'accounting' );
        $error .= __( '<p>The version of your PHP is ', 'accounting' ) . '<a href="http://php.net/supported-versions.php" target="_blank"><strong>' . __( 'unsupported and old', 'accounting' ) . '</strong></a>.';
        $error .= __( 'You should update your PHP software or contact your host regarding this matter.</p>', 'accounting' );
        wp_die( $error, __( 'Plugin Activation Error', 'accounting' ), array( 'response' => 200, 'back_link' => true ) );
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
        define( 'WPERP_ACCOUNTING_VERSION', $this->version );
        define( 'WPERP_ACCOUNTING_PATH', dirname( __FILE__ ) );
        define( 'WPERP_ACCOUNTING_URL', plugins_url( '', __FILE__ ) );
        define( 'WPERP_ACCOUNTING_ASSETS', WPERP_ACCOUNTING_URL . '/assets' );
        define( 'WPERP_ACCOUNTING_JS_TMPL', WPERP_ACCOUNTING_PATH . '/includes/views/js-templates' );
        define( 'WPERP_ACCOUNTING_VIEWS', WPERP_ACCOUNTING_PATH . '/includes/views' );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    private function includes() {
    
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




