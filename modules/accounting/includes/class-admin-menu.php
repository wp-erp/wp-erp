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
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
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

        add_menu_page( __( 'Accounting', 'erp' ), __( 'Accounting', 'erp' ), $dashboard, 'erp-accounting', array( $this, 'dashboard_page' ), 'dashicons-chart-pie', null );

        $dashboard      = add_submenu_page( 'erp-accounting', __( 'Dashboard', 'erp' ), __( 'Dashboard', 'erp' ), $dashboard, 'erp-accounting', array( $this, 'dashboard_page' ) );
        $customer       = add_submenu_page( 'erp-accounting', __( 'Customers', 'erp' ), __( 'Customers', 'erp' ), $customer, 'erp-accounting-customers', array( $this, 'page_customers' ) );
        $vendor         = add_submenu_page( 'erp-accounting', __( 'Vendors', 'erp' ), __( 'Vendors', 'erp' ), $vendor, 'erp-accounting-vendors', array( $this, 'page_vendors' ) );
        $sale           = add_submenu_page( 'erp-accounting', __( 'Sales', 'erp' ), __( 'Sales', 'erp' ), $sale, 'erp-accounting-sales', array( $this, 'page_sales' ) );
        $expense        = add_submenu_page( 'erp-accounting', __( 'Expenses', 'erp' ), __( 'Expenses', 'erp' ), $expense, 'erp-accounting-expense', array( $this, 'page_expenses' ) );
        $account_charts = add_submenu_page( 'erp-accounting', __( 'Chart of Accounts', 'erp' ), __( 'Chart of Accounts', 'erp' ), $account_charts, 'erp-accounting-charts', array( $this, 'page_chart_of_accounting' ) );
        $bank           = add_submenu_page( 'erp-accounting', __( 'Bank Accounts', 'erp' ), __( 'Bank Accounts', 'erp' ), $bank, 'erp-accounting-bank', array( $this, 'page_bank' ) );
        $journal        = add_submenu_page( 'erp-accounting', __( 'Journal Entry', 'erp' ), __( 'Journal Entry', 'erp' ), $journal, 'erp-accounting-journal', array( $this, 'page_journal_entry' ) );
        $reports        = add_submenu_page( 'erp-accounting', __( 'Reports', 'erp' ), __( 'Reports', 'erp' ), $reports, 'erp-accounting-reports', array( $this, 'page_reports' ) );

        add_action( 'admin_print_styles-' . $dashboard, array( $this, 'dashboard_script' ) );
        add_action( 'admin_print_styles-' . $customer, array( $this, 'common_scripts' ) );
        add_action( 'admin_print_styles-' . $vendor, array( $this, 'common_scripts' ) );
        add_action( 'admin_print_styles-' . $bank, array( $this, 'bank_script' ) );
        add_action( 'admin_print_styles-' . $sale, array( $this, 'sales_chart_script' ) );
        add_action( 'admin_print_styles-' . $expense, array( $this, 'expense_chart_script' ) );
        add_action( 'admin_print_styles-' . $journal, array( $this, 'journal_script' ) );
        add_action( 'admin_print_styles-' . $account_charts, array( $this, 'chart_account_script' ) );
        add_action( 'admin_print_styles-' . $reports, array( $this, 'reports_script' ) );
        add_action( 'admin_print_styles-' . 'erp-settings_page_erp-settings', array( $this, 'accounting_settings_script' ) );
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
        $this->chart_script();
        $this->common_scripts();
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
        $this->chart_script();
        $this->common_scripts();
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
            'confirmMsg'         => __( 'Are you sure?', 'erp' ),
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
    }

    /**
     * Callback for dashboard view page
     *
     * @since 1.0
     *
     * @return void
     */
    public function dashboard_page() {
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
        $type   = isset( $_GET['type'] ) ? $_GET['type'] : '';
        $pagenum          = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $limit            = 20;
        $offset           = ( $pagenum - 1 ) * $limit;


        switch ( $type ) {
            case 'trial-balance':
                $template = dirname( __FILE__ ) . '/views/reports/trial-balance.php';
                break;

            case 'sales-tax':
                if ( isset( $_GET['action'] ) && intval( $_GET['id'] ) ) {
                    $tax_id  = $_GET['id'];
                    $taxs    = erp_ac_normarlize_tax_from_transaction( [ 'tax_id' => [$_GET['id']], 'offset'  => $offset, 'number' => $limit] );
                    $taxs    = $taxs['individuals'][$_GET['id']];
                    $count   = erp_ac_get_sales_tax_report_count( ['tax_id' => [$_GET['id']] ] );
                    $taxinfo = erp_ac_get_tax_info();

                    $template = dirname( __FILE__ ) . '/views/reports/tax/single-sales-tax.php';
                } else {
                    $taxs = erp_ac_normarlize_tax_from_transaction();
                    $taxs = $taxs['units'];
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
}
