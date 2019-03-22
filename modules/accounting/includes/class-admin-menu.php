<?php
namespace WeDevs\ERP\Accounting;

/**
 * Admin Menu
 */
class Admin_Menu {

    /**
     * Kick-in the class
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_common_script' ) );
    }

    /**
     * Load common scripts in settings page
     */
    public function load_common_script() {
        global $current_screen;

        if ( $current_screen->base == 'wp-erp_page_erp-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'accounting' ) {
            $this->common_scripts();
        }

    }

    /**
     * Register the admin menu
     *
     * @return void
     */
    public function admin_menu() {
        $dashboard      = 'erp_ac_view_dashboard';
        $customer       = 'erp_ac_view_customer';
        $vendor         = 'erp_ac_view_vendor';
        $sale           = 'erp_ac_view_sale';
        $expense        = 'erp_ac_view_expense';
        $account_charts = 'erp_ac_view_account_lists';
        $bank           = 'erp_ac_view_bank_accounts';
        $journal        = 'erp_ac_view_journal';
        $reports        = 'erp_ac_view_reports';
//
//        add_menu_page( __( 'Accounting', 'erp' ), 'Accounting', $dashboard, 'erp-accounting', array( $this, 'dashboard_page' ), 'dashicons-chart-pie', null );
//
//        $dashboard      = add_submenu_page( 'erp-accounting', __( 'Dashboard', 'erp' ), __( 'Dashboard', 'erp' ), $dashboard, 'erp-accounting', array( $this, 'dashboard_page' ) );
//        $customer       = add_submenu_page( 'erp-accounting', __( 'Customers', 'erp' ), __( 'Customers', 'erp' ), $customer, 'erp-accounting-customers', array( $this, 'page_customers' ) );
//        $vendor         = add_submenu_page( 'erp-accounting', __( 'Vendors', 'erp' ), __( 'Vendors', 'erp' ), $vendor, 'erp-accounting-vendors', array( $this, 'page_vendors' ) );
//        $sale           = add_submenu_page( 'erp-accounting', __( 'Sales', 'erp' ), __( 'Sales', 'erp' ), $sale, 'erp-accounting-sales', array( $this, 'page_sales' ) );
//        $expense        = add_submenu_page( 'erp-accounting', __( 'Expenses', 'erp' ), __( 'Expenses', 'erp' ), $expense, 'erp-accounting-expense', array( $this, 'page_expenses' ) );
//        $account_charts = add_submenu_page( 'erp-accounting', __( 'Chart of Accounts', 'erp' ), __( 'Chart of Accounts', 'erp' ), $account_charts, 'erp-accounting-charts', array( $this, 'page_chart_of_accounting' ) );
//        $bank           = add_submenu_page( 'erp-accounting', __( 'Bank Accounts', 'erp' ), __( 'Bank Accounts', 'erp' ), $bank, 'erp-accounting-bank', array( $this, 'page_bank' ) );
//        $journal        = add_submenu_page( 'erp-accounting', __( 'Journal Entry', 'erp' ), __( 'Journal Entry', 'erp' ), $journal, 'erp-accounting-journal', array( $this, 'page_journal_entry' ) );
//        $reports        = add_submenu_page( 'erp-accounting', __( 'Reports', 'erp' ), __( 'Reports', 'erp' ), $reports, 'erp-accounting-reports', array( $this, 'page_reports' ) );
//
//        //Help page
//        add_submenu_page( 'erp-accounting', __( 'Help', 'erp' ), __( '<span style="color:#f18500">Help</span>', 'erp' ), 'erp_ac_view_dashboard', 'erp-ac-help', array( $this, 'help_page' ) );

        add_submenu_page( 'erp', __( 'Accounting', 'erp' ), 'Accounting', 'erp_ac_view_dashboard', 'erp-accounting', [ $this, 'router' ]);

        erp_add_menu_header( 'accounting', 'Accounting', '<svg id="Group_235" data-name="Group 235" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 239 341.4"><defs><style>.cls-1{fill:#9ca1a6}</style></defs><path id="Path_281" data-name="Path 281" class="cls-1" d="M221.9,0H17.1C6.8,0,0,6.8,0,17.1V324.3c0,10.2,6.8,17.1,17.1,17.1H221.9c10.2,0,17.1-6.8,17.1-17.1V17.1C238.9,6.8,232.1,0,221.9,0ZM68.3,307.2H34.1V273.1H68.2v34.1Zm0-68.3H34.1V204.8H68.2v34.1Zm0-68.2H34.1V136.6H68.2v34.1Zm68.2,136.5H102.4V273.1h34.1Zm0-68.3H102.4V204.8h34.1Zm0-68.2H102.4V136.6h34.1Zm68.3,136.5H170.7V273.1h34.1v34.1Zm0-68.3H170.7V204.8h34.1v34.1Zm0-68.2H170.7V136.6h34.1v34.1Zm0-68.3H34.1V34.1H204.8v68.3Zm0,0"/></svg>' );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Dashboard', 'erp' ),
            'capability' => $dashboard,
            'slug'       => 'dashboard',
            'callback'   => [ $this, 'dashboard_page' ],
            'position'   => 1
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Customers', 'erp' ),
            'capability' => $customer,
            'slug'       => 'customers',
            'callback'   => [ $this, 'page_customers' ],
            'position'   => 5
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Vendors', 'erp' ),
            'capability' => $vendor,
            'slug'       => 'vendors',
            'callback'   => [ $this, 'page_vendors' ],
            'position'   => 10
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Sales', 'erp' ),
            'capability' => $sale,
            'slug'       => 'sales',
            'callback'   => [ $this, 'page_sales' ],
            'position'   => 15
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Expenses', 'erp' ),
            'capability' => $expense,
            'slug'       => 'expense',
            'callback'   => [ $this, 'page_expenses' ],
            'position'   => 20
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Chart of Accounts', 'erp' ),
            'capability' => $account_charts,
            'slug'       => 'charts',
            'callback'   => [ $this, 'page_chart_of_accounting' ],
            'position'   => 25
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Bank Accounts', 'erp' ),
            'capability' => $bank,
            'slug'       => 'bank',
            'callback'   => [ $this, 'page_bank' ],
            'position'   => 30
        ] );
        erp_add_menu( 'accounting', [
            'title'      =>  __( 'Journal Entry', 'erp' ),
            'capability' => $journal,
            'slug'       => 'journal',
            'callback'   => [ $this, 'page_journal_entry' ],
            'position'   => 35
        ] );
        erp_add_menu( 'accounting', [
            'title'      =>  __( 'Reports', 'erp' ),
            'capability' => $reports,
            'slug'       => 'reports',
            'callback'   => [ $this, 'page_reports' ],
            'position'   => 90
        ] );
        erp_add_menu( 'accounting', [
            'title'      =>  __( '<span class="erp-help">Help</span>', 'erp' ),
            'capability' => $dashboard,
            'slug'       => 'erp-ac-help',
            'callback'   => [ $this, 'help_page' ],
            'position'   => 99
        ] );


//        add_action( 'admin_print_styles-' . $dashboard, array( $this, 'dashboard_script' ) );
//        add_action( 'admin_print_styles-' . $customer, array( $this, 'sales_chart_script' ) );
//        add_action( 'admin_print_styles-' . $vendor, array( $this, 'sales_chart_script' ) );
//        add_action( 'admin_print_styles-' . $bank, array( $this, 'bank_script' ) );
//        add_action( 'admin_print_styles-' . $sale, array( $this, 'sales_chart_script' ) );
//        add_action( 'admin_print_styles-' . $expense, array( $this, 'expense_chart_script' ) );
//        add_action( 'admin_print_styles-' . $journal, array( $this, 'journal_script' ) );
//        add_action( 'admin_print_styles-' . $account_charts, array( $this, 'chart_account_script' ) );
//        add_action( 'admin_print_styles-' . $reports, array( $this, 'reports_script' ) );
//        add_action( 'admin_print_styles-' . 'erp-settings_page_erp-settings', array( $this, 'accounting_settings_script' ) );
    }

    /**
     * Load dashboard scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function dashboard_script() {
        $this->chart_script();
        $this->common_scripts();
    }

    /**
     * Load Bank page scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function bank_script() {
        $this->chart_script();
        $this->common_scripts();
    }

    /**
     * Load sales chart scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function sales_chart_script() {
        $this->common_scripts();
        $this->chart_script();
        wp_localize_script( 'wp-erp-ac-js', 'erp_ac_tax', [ 'rate' => erp_ac_get_tax_info() ] );
    }

    /**
     * Load expense chart scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function expense_chart_script() {
        $this->common_scripts();
        $this->chart_script();
        wp_localize_script( 'wp-erp-ac-js', 'erp_ac_tax', [ 'rate' => erp_ac_get_tax_info() ] );
    }

    /**
     * Load Journal scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function journal_script() {
        $this->common_scripts();
    }

    /**
     * Load chart of account scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function chart_account_script() {
        $this->common_scripts();
    }

    /**
     * Load account settings in ERP Settings menu scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function accounting_settings_script() {
        if ( isset( $_GET['tab'] ) && 'accounting' == $_GET['tab'] ) {
            $this->common_scripts();
        }
    }

    /**
     * Load Reports scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function reports_script() {
        $this->common_scripts();
    }

    /**
     * Load all chart scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function chart_script() {
        wp_enqueue_script( 'plupload-handlers' );
        wp_enqueue_script( 'erp-file-upload' );
        wp_enqueue_script( 'erp-flotchart' );
        wp_enqueue_script( 'erp-flotchart-resize' );
        wp_enqueue_script( 'erp-flotchart-pie' );
        wp_enqueue_script( 'erp-flotchart-time' );
        wp_enqueue_script( 'erp-flotchart-tooltip' );
        wp_enqueue_script( 'erp-flotchart-orerbars' );
        wp_enqueue_script( 'erp-flotchart-axislables' );
        wp_enqueue_script( 'erp-flotchart-navigate' );
        wp_enqueue_script('erp-flotchart-selection');
    }

    /**
     * Load common scripts
     *
     * @since 1.1.4
     *
     * @return void
     */
    public function common_scripts() {
        $erp_ac_de_separator = erp_get_option('erp_ac_de_separator');
        $erp_ac_th_separator = erp_get_option('erp_ac_th_separator');
        $erp_ac_nm_decimal   = erp_get_option('erp_ac_nm_decimal');

        // styles
        wp_enqueue_style('erp-tiptip');
        wp_enqueue_style( 'erp-sweetalert' );
        wp_enqueue_style( 'erp-tether-drop-theme' );
        wp_enqueue_style( 'wp-erp-ac-styles', WPERP_ASSETS . '/css/accounting.css', array( 'wp-color-picker' ), WPERP_VERSION );

        // scripts
        wp_enqueue_script( 'erp-tiptip' );
        wp_enqueue_script( 'erp-sweetalert' );
        wp_enqueue_script( 'erp-tether-main' );
        wp_enqueue_script( 'erp-tether-drop' );
        wp_enqueue_script( 'erp-clipboard' );
        wp_enqueue_script( 'accounting', WPERP_ACCOUNTING_ASSETS . '/js/accounting.min.js', array( 'jquery' ), date( 'Ymd' ), true );
        wp_enqueue_script( 'wp-erp-ac-js', WPERP_ACCOUNTING_ASSETS . '/js/erp-accounting.js', array( 'jquery', 'wp-color-picker', 'erp-tiptip' ), date( 'Ymd' ), true );

        wp_localize_script( 'wp-erp-ac-js', 'ERP_AC', array(
            'nonce'              => wp_create_nonce( 'erp-ac-nonce' ),
            'emailConfirm'       => __( 'Sent', 'erp' ),
            'emailConfirmMsg'    => __( 'The email has been sent', 'erp' ),
            'confirmMsg'         => __( 'You are about to permanently delete this item.', 'erp' ),
            'copied'             => __( 'Copied', 'erp' ),
            'ajaxurl'            => admin_url( 'admin-ajax.php' ),
            'decimal_separator'  => empty( $erp_ac_de_separator ) ? '.' : erp_get_option('erp_ac_de_separator'),
            'thousand_separator' => empty( $erp_ac_th_separator ) ? ',' : erp_get_option('erp_ac_th_separator'),
            'number_decimal'     => empty( $erp_ac_nm_decimal ) ? '2' : erp_get_option('erp_ac_nm_decimal'),
            'currency'           => erp_get_option('erp_ac_currency'),
            'symbol'             => erp_ac_get_currency_symbol(),
            'message'            => erp_ac_message(),
            'plupload'           => array(
                'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'erp_ac_featured_img' ),
                'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters'          => array( array('title' => __( 'Allowed Files', 'erp' ), 'extensions' => '*')),
                'multipart'        => true,
                'urlstream_upload' => true,
            )
        ));

        $country = \WeDevs\ERP\Countries::instance();
        wp_localize_script( 'wp-erp-ac-js', 'wpErpCountries', $country->load_country_states() );
    }

    /**
     * Route to approriate template according to current menu
     *
     * @since 1.3.14
     *
     * @return void
     */
    public function router() {
        $component = 'accounting';
        $menu = erp_menu();
        $menu = $menu[$component];
        $section = ( isset( $_GET['section'] ) && isset( $menu[$_GET['section']] ) ) ? $_GET['section'] : 'dashboard';
        $sub = ( isset( $_GET['sub-section'] ) && !empty( $menu[$section]['submenu'][$_GET['sub-section']] ) ) ? $_GET['sub-section'] : false;
        $callback = $menu[$section]['callback'];
        if ( $sub ) {
            $callback = $menu[$section]['submenu'][$sub]['callback'];
        }
        erp_render_menu( $component );
        call_user_func( $callback );
    }


    /**
     * Callback for dashboard view page
     *
     * @since 1.0
     *
     * @return void
     */
    public function dashboard_page() {
        $this->dashboard_script();

        include dirname( __FILE__ ) . '/views/dashboard.php';
    }

    /**
     * Render page sales view page
     *
     * @since 1.0
     *
     * @return void
     */
    public function page_sales() {
        $this->sales_chart_script();

        $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $type     = isset( $_GET['type'] ) ? $_GET['type'] : 'pv';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'new':

                if ( $type == 'invoice' && ( erp_ac_create_sales_invoice() || erp_ac_publish_sales_invoice() ) ) {
                    $template = dirname( __FILE__ ) . '/views/sales/invoice-new.php';
                } else if ( $type == 'payment' && ( erp_ac_create_sales_payment() || erp_ac_publish_sales_payment() ) ) {
                    $template = dirname( __FILE__ ) . '/views/sales/payment-new.php';
                }else {
                    $template = apply_filters( 'erp_ac_invoice_transaction_template', $template );
                }

                break;

            case 'view':
                $transaction = Model\Transaction::find( $id );

                if ( $transaction->form_type == 'invoice' ) {
                    $template = dirname( __FILE__ ) . '/views/sales/invoice-single.php';
                } else {
                    $template = dirname( __FILE__ ) . '/views/sales/payment-single.php';
                }

                break;

            default:
                $template = dirname( __FILE__ ) . '/views/sales/transaction-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo sprintf( '<h1>%s</h1>', __( 'You do not have sufficient permissions to access this page.', 'erp' ) );
        }
    }

    /**
     * Render page expense page
     *
     * @since 1.0
     *
     * @return void
     */
    public function page_expenses() {
        $this->expense_chart_script();

        $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $type     = isset( $_GET['type'] ) ? $_GET['type'] : 'pv';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'new':

                if ( $type == 'payment_voucher' && ( erp_ac_create_expenses_voucher() || erp_ac_publish_expenses_voucher() ) ) {

                    $template = dirname( __FILE__ ) . '/views/expense/payment-voucher.php';

                } else if ( $type == 'vendor_credit' && ( erp_ac_create_expenses_credit() || erp_ac_publish_expenses_credit() ) ) {

                    $template = dirname( __FILE__ ) . '/views/expense/vendor-credit.php';

                } else {

                    $template = apply_filters( 'erp_ac_sales_transaction_template', $template );

                }

                break;

            case 'view':
                $transaction = Model\Transaction::find( $id );

                if ( $transaction->form_type == 'payment_voucher' ) {
                    $template    = dirname( __FILE__ ) . '/views/expense/payment-voucher-single.php';
                } else {
                    $template    = dirname( __FILE__ ) . '/views/expense/vendor-credit-single.php';
                }

                break;

            default:
                $template = dirname( __FILE__ ) . '/views/expense/transaction-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo sprintf( '<h1>%s</h1>', __( 'You do not have sufficient permissions to access this page.', 'erp' ) );
        }
    }

    /**
     * Render page chart accounting page
     *
     * @since 1.0
     *
     * @return void
     */
    public function page_chart_of_accounting() {
        $this->chart_account_script();

        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'view':
                if ( erp_ac_view_single_account() ) {
                    $ledger = Model\Ledger::find( $id );

                    $template = dirname( __FILE__ ) . '/views/accounts/single.php';
                }
                break;

            case 'edit':
                if ( erp_ac_edit_account() ) {
                    $template = dirname( __FILE__ ) . '/views/accounts/edit.php';
                }
                break;

            case 'new':
                if ( erp_ac_create_account() ) {
                    $template = dirname( __FILE__ ) . '/views/accounts/new.php';
                }
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/chart-of-accounts.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo sprintf( '<h1>%s</h1>', __( 'You do not have sufficient permissions to access this page.', 'erp' ) );
        }
    }

    /**
     * Render bank page
     *
     * @since 1.0
     *
     * @return void
     */
    public function page_bank() {
        $this->bank_script();

        $action   = isset( $_GET['action'] ) ? $_GET['action'] : '';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ( $action ) {
            case 'view':
                $transaction = Model\Transaction::find( $id );
                $template = dirname( __FILE__ ) . '/views/bank/invoice.php';
                break;
            default:
                $template = dirname( __FILE__ ) . '/views/bank/bank.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Render page reports
     *
     * @since 1.0
     *
     * @return void
     */
    public function page_reports() {
        $this->reports_script();

        $type   = isset( $_GET['type'] ) ? $_GET['type'] : '';
        $pagenum          = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $limit            = 20;
        $offset           = ( $pagenum - 1 ) * $limit;


        switch ( $type ) {
            case 'trial-balance':
                $template = dirname( __FILE__ ) . '/views/reports/trial-balance.php';
                break;

            case 'sales-tax':

                $start = ! empty( $_GET['start'] ) ? $_GET['start'] : date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
                $end   = ! empty( $_GET['end'] ) ? $_GET['end'] : date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

                if ( isset( $_GET['action'] ) && intval( $_GET['id'] ) ) {
                    $tax_id  = $_GET['id'];
                    $taxs    = erp_ac_normarlize_individual_tax( [ 'start' => $start, 'end' => $end, 'tax_id' => [$_GET['id']], 'offset'  => $offset, 'number' => $limit] );
                    //$taxs    = $taxs['individuals'][$_GET['id']];
                    $count   = erp_ac_get_sales_tax_report_count( ['tax_id' => [$_GET['id']] ] );
                    $taxinfo = erp_ac_get_tax_info();

                    $template = dirname( __FILE__ ) . '/views/reports/tax/single-sales-tax.php';
                } else {
                    $taxs = erp_ac_normarlize_tax_from_transaction( [ 'start' => $start, 'end' => $end ] );
                    $template = dirname( __FILE__ ) . '/views/reports/tax/sales-tax.php';
                }
                break;

            case 'income-statement':
                $template = dirname( __FILE__ ) . '/views/reports/income-statement/statement.php';
                break;

            case 'balance-sheet':
                $template = dirname( __FILE__ ) . '/views/reports/balance-sheet/balance-sheet.php';
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/reports.php';
                break;
        }

        $template = apply_filters( 'erp_ac_reporting_pages', $template, $type );

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Render journal entry page
     *
     * @since 1.0
     *
     * @return void
     */
    public function page_journal_entry() {
        $this->reports_script();

        $action   = isset( $_GET['action'] ) ? $_GET['action'] : '';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ( $action ) {
            case 'view':
                $transaction = Model\Transaction::find( $id );
                $template = dirname( __FILE__ ) . '/views/journal/single.php';
                break;

            case 'new':
                if ( erp_ac_create_journal() ) {
                    $journal_id = isset( $_GET['journal_id'] ) ? intval( $_GET['journal_id'] ) : false;
                    $journal    = [];

                    if ( $journal_id ) {
                        $journal = erp_ac_get_transaction( $journal_id, [
                            'join' => ['journals', 'items'],
                            'type' => 'journal'
                        ]);
                    }

                    $template = dirname( __FILE__ ) . '/views/journal/new.php';
                }
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/journal/list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo sprintf( '<h1>%s</h1>', __( 'You do not have sufficient permissions to access this page.', 'erp' ) );
        }
    }

    /**
     * Handles the plugin page
     *
     * @return void
     */
    public function page_customers() {
        $this->sales_chart_script();

        $action   = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id       = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'view':
                if ( erp_ac_current_user_can_view_single_customer() ) {
                    $customer = new \WeDevs\ERP\People( $id );
                    $template = dirname( __FILE__ ) . '/views/customer/single.php';
                }
                break;

            case 'edit':
                $customer = new \WeDevs\ERP\People( $id );
                if ( erp_ac_current_user_can_edit_customer( $customer->created_by ) ) {
                    $template = dirname( __FILE__ ) . '/views/customer/edit.php';
                }
                break;

            case 'new':
                if ( erp_ac_create_customer() ) {
                    $template = dirname( __FILE__ ) . '/views/customer/new.php';
                }
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/customer/list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo sprintf( '<h1>%s</h1>', __( 'You do not have sufficient permissions to access this page.', 'erp' ) );
        }
    }

    /**
     * Handles the plugin page
     *
     * @return void
     */
    public function page_vendors() {
        $this->sales_chart_script();

        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $template = '';

        switch ($action) {
            case 'view':
                if ( erp_ac_current_user_can_view_single_vendor() ) {
                    $vendor = new \WeDevs\ERP\People( $id );
                    $template = dirname( __FILE__ ) . '/views/vendor/single.php';
                }
                break;

            case 'edit':
                $vendor = new \WeDevs\ERP\People( $id );
                if ( erp_ac_current_user_can_edit_vendor( $vendor->created_by ) ) {
                    $template = dirname( __FILE__ ) . '/views/vendor/edit.php';
                }
                break;

            case 'new':
                if ( erp_ac_create_vendor() ) {
                    $template = dirname( __FILE__ ) . '/views/vendor/new.php';
                }
                break;

            default:
                $template = dirname( __FILE__ ) . '/views/vendor/list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Show Accounting Help Page
     * @since 1.0.0
     */
    public function help_page() {
        include WPERP_ACCOUNTING_PATH . '/includes/views/help.php';
    }
}
