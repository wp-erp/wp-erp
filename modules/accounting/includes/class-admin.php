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

        add_submenu_page( 'erp', __( 'Accounting', 'erp' ), 'Accounting', 'erp_ac_manager', $slug , [
            $this, 'plugin_page' ] );

        erp_add_menu_header( 'accounting', 'Accounting', '<svg id="Group_235" data-name="Group 235" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 239 341.4"><defs><style>.cls-1{fill:#9ca1a6}</style></defs><path id="Path_281" data-name="Path 281" class="cls-1" d="M221.9,0H17.1C6.8,0,0,6.8,0,17.1V324.3c0,10.2,6.8,17.1,17.1,17.1H221.9c10.2,0,17.1-6.8,17.1-17.1V17.1C238.9,6.8,232.1,0,221.9,0ZM68.3,307.2H34.1V273.1H68.2v34.1Zm0-68.3H34.1V204.8H68.2v34.1Zm0-68.2H34.1V136.6H68.2v34.1Zm68.2,136.5H102.4V273.1h34.1Zm0-68.3H102.4V204.8h34.1Zm0-68.2H102.4V136.6h34.1Zm68.3,136.5H170.7V273.1h34.1v34.1Zm0-68.3H170.7V204.8h34.1v34.1Zm0-68.2H170.7V136.6h34.1v34.1Zm0-68.3H34.1V34.1H204.8v68.3Zm0,0"/></svg>' );

        erp_add_menu( 'accounting', [
            'title'      => __( 'Dashboard', 'erp' ),
            'capability' => $dashboard,
            'slug'       => 'dashboard',
            'position'   => 1
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Users', 'erp' ),
            'capability' => $customer,
            'slug'       => 'users',
            'position'   => 5
        ] );
        erp_add_submenu( 'accounting', 'users', [
            'title'      => __( 'Customers', 'erp' ),
            'capability' => $customer,
            'slug'       => 'customers',
            'position'   => 5
        ] );
        erp_add_submenu( 'accounting', 'users',  [
            'title'      => __( 'Vendors', 'erp' ),
            'capability' => $vendor,
            'slug'       => 'vendors',
            'position'   => 10
        ] );
        erp_add_submenu( 'accounting', 'users',  [
            'title'      => __( 'Employees', 'erp' ),
            'capability' => 'erp_hr_manager',
            'slug'       => 'employees',
            'position'   => 15
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Transactions', 'erp' ),
            'capability' => $expense,
            'slug'       => 'transactions',
            'position'   => 25
        ] );
        erp_add_submenu( 'accounting', 'transactions', [
            'title'      => __( 'Sales', 'erp' ),
            'capability' => $sale,
            'slug'       => 'transactions/sales',
            'position'   => 15
        ] );
        erp_add_submenu( 'accounting', 'transactions', [
            'title'      => __( 'Expenses', 'erp' ),
            'capability' => $expense,
            'slug'       => 'transactions/expenses',
            'position'   => 25
        ] );
        erp_add_submenu( 'accounting', 'transactions', [
            'title'      =>  __( 'Journals', 'erp' ),
            'capability' => $journal,
            'slug'       => 'journals',
            'position'   => 25
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Chart of Accounts', 'erp' ),
            'capability' => $account_charts,
            'slug'       => 'charts',
            'position'   => 30
        ] );
        erp_add_menu( 'accounting', [
            'title'      => __( 'Bank Accounts', 'erp' ),
            'capability' => $bank,
            'slug'       => 'banks',
            'position'   => 35
        ] );
        erp_add_menu( 'accounting', [
            'title'      =>  __( 'Inventory', 'erp' ),
            'capability' => 'erp_ac_view_sale',
            'slug'       => 'inventory',
            'position'   => 45,
        ] );
        erp_add_submenu( 'accounting', 'inventory', [
            'title'         =>  __( 'Products', 'erp' ),
            'capability'    =>  'erp_ac_view_sale',
            'slug'          =>  'products',
            'position'      =>  5,
        ] );
        erp_add_submenu( 'accounting', 'inventory', [
            'title'         =>  __( 'Product Categories', 'erp' ),
            'capability'    =>  'erp_ac_view_sale',
            'slug'          =>  'product_categories',
            'position'      =>  10,
        ] );
        erp_add_submenu( 'accounting', 'inventory', [
            'title'      => __( 'Purchases', 'erp' ),
            'capability' => $sale,
            'slug'       => 'transactions/purchases',
            'position'   => 15
        ] );
        erp_add_menu( 'accounting', [
            'title'      =>  __( 'Tax', 'erp' ),
            'capability' => 'erp_ac_view_sale',
            'slug'       => 'tax',
            'position'   => 45,
        ] );
        erp_add_submenu( 'accounting', 'tax', [
            'title'      => __( 'Tax Rates', 'erp' ),
            'capability' => $sale,
            'slug'       => 'taxes',
            'position'   => 15
        ] );
//        erp_add_submenu( 'accounting', 'tax', [
//            'title'      => __( 'Tax Categories', 'erp' ),
//            'capability' => $sale,
//            'slug'       => 'tax-categories',
//            'position'   => 15
//        ] );
//        erp_add_submenu( 'accounting', 'tax', [
//            'title'      => __( 'Tax Agencies', 'erp' ),
//            'capability' => $sale,
//            'slug'       => 'tax-agencies',
//            'position'   => 15
//        ] );
        erp_add_submenu( 'accounting', 'tax', [
            'title'      => __( 'Tax Records', 'erp' ),
            'capability' => $sale,
            'slug'       => 'tax-records',
            'position'   => 15
        ] );
        erp_add_menu( 'accounting', [
            'title'      =>  __( 'Reports', 'erp' ),
            'capability' => $reports,
            'slug'       => 'reports',
            'position'   => 90
        ] );
        erp_add_menu( 'accounting', [
            'title'      =>  __( 'Help', 'erp' ),
            'capability' => $dashboard,
            'slug'       => 'erp-ac-help',
            'position'   => 200
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
