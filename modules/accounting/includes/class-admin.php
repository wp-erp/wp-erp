<?php
namespace WeDevs\ERP\Accounting\INCLUDES;

/**
 * Admin Pages Handler
 */
class Admin {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu() {
        global $submenu;

        $capability = 'manage_options';
        $slug       = 'accounting';

        $hook = add_menu_page( __( 'Accounting', 'erp' ), __( 'Accounting', 'erp' ), $capability, $slug, [ $this, 'plugin_page' ], 'dashicons-text' );

        if ( current_user_can( $capability ) ) {
            $submenu[ $slug ][] = array( __( 'Dashboard', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting' );
            $submenu[ $slug ][] = array( __( 'Customers', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-customers' );
            $submenu[ $slug ][] = array( __( 'Vendors', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-vendors' );
            $submenu[ $slug ][] = array( __( 'Employees', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-employees' );
            $submenu[ $slug ][] = array( __( 'Sales', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-sales' );
            $submenu[ $slug ][] = array( __( 'Bills', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-bills' );
            $submenu[ $slug ][] = array( __( 'Purchases', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-purchases' );
            $submenu[ $slug ][] = array( __( 'Taxes', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-tax' );
            $submenu[ $slug ][] = array( __( 'Products', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-products' );
            $submenu[ $slug ][] = array( __( 'Product Categories', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-product-cats' );
            $submenu[ $slug ][] = array( __( 'Journals', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-journals' );
            $submenu[ $slug ][] = array( __( 'Chart of Accounts', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-charts' );
            $submenu[ $slug ][] = array( __( 'Reports', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-reports' );
            $submenu[ $slug ][] = array( __( 'Help', 'erp' ), $capability, 'admin.php?page=' . $slug . '#/erp-accounting-help' );
        }

        add_action( 'load-' . $hook, [ $this, 'init_hooks'] );
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
        echo '<div class="wrap"><div id="erp-accounting-app"></div></div>';
    }
}
