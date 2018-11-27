<?php
namespace WeDevs\ERP\Accounting\INCLUDES;

/**
 * Admin Pages Handler
 */
class Admin {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'admin_init', [ $this, 'init_hooks' ], 5 );
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

        $slug           = 'erp-accounting';

        add_submenu_page( 'erp', __( 'Accounting', 'erp' ), 'Accounting', 'erp_hr_manager', $slug , [
            $this,
            'plugin_page'
        ] );

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
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'accounting-admin' );
        wp_enqueue_script( 'accounting-admin' );
    }

    /**
     * Render our admin page
     *
     * @return void
     */
    public function plugin_page() {
        echo '<div class="wrap"><div id="erp-accounting"></div></div>';
    }
}
