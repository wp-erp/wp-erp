<?php
namespace WeDevs\ERP\Accounting\Includes\Classes;

/**
 * Scripts and Styles Class
 */
class Assets {

    function __construct() {

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register() {
        if ( is_admin() ) {
            $screen = get_current_screen();
            if ( $screen->base == 'wp-erp_page_erp-settings') {
                wp_enqueue_script( 'accounting-helper', ERP_ACCOUNTING_ASSETS . '/js/accounting.js', array( 'jquery' ), false, true );
                return;
            } elseif ( $screen->base != 'wp-erp_page_erp-accounting' ) {
                return;
            }
        }

        $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        global $current_user;
        $u_id =  $current_user->ID;
        $site_url = site_url();
        $rest_nonce = wp_create_nonce( "wp_rest" );
        $logout_url = esc_url( wp_logout_url() );
        $acct_url = admin_url( 'admin.php' ) . '?page=erp-accounting#/';

        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPERP_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

        $menus = '';

        if ( is_admin() ) {
            $component = 'accounting';
            $menu  = erp_menu();
            $menus = $menu[$component];

            //check items for capabilities
            $items = array_filter( $menus, function( $item ) {
                if ( !isset( $item['capability'] ) ) {
                    return false;
                }
                return current_user_can( $item['capability'] );
            } );

            //sort items for position
            uasort( $menus, function ( $a, $b ) {
                return $a['position'] > $b['position'];
            } );
        }

        wp_localize_script( 'accounting-admin', 'erp_acct_var', array(
            'user_id'        => $u_id,
            'site_url'       => $site_url,
            'rest_nonce'     => $rest_nonce,
            'logout_url'     => $logout_url,
            'acct_assets'    => ERP_ACCOUNTING_ASSETS,
            'erp_assets'     => WPERP_ASSETS,
            'erp_acct_menus' => $menus,
            'erp_acct_url'   => $acct_url,
            'erp_debug_mode' => erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ),
            'current_date'   => date( 'Y-m-d' ),
        ) );
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, WPERP_VERSION );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        $scripts = [
            'accounting-vendor' => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/vendor.js',
                'version'   => filemtime( ERP_ACCOUNTING_PATH . '/assets/js/vendor.js' ),
                'in_footer' => true
            ],
            'accounting-frontend' => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/frontend.js',
                'deps'      => [ 'jquery', 'accounting-vendor' ],
                'version'   => filemtime( ERP_ACCOUNTING_PATH . '/assets/js/frontend.js' ),
                'in_footer' => true
            ],
            'accounting-admin' => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/admin.js',
                'deps'      => [ 'jquery', 'accounting-vendor' ],
                'version'   => filemtime( ERP_ACCOUNTING_PATH . '/assets/js/admin.js' ),
                'in_footer' => true
            ],
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles() {
        $styles = [
            'accounting-style' => [
                'src' =>  ERP_ACCOUNTING_ASSETS . '/css/style.css'
            ],
            'accounting-frontend' => [
                'src' =>  ERP_ACCOUNTING_ASSETS . '/css/frontend.css'
            ],
            'accounting-admin' => [
                'src' =>  ERP_ACCOUNTING_ASSETS . '/css/admin.css'
            ],
        ];

        return $styles;
    }

}
