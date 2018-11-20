<?php
namespace WeDevs\ERP\Accounting\INCLUDES;

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
        $acc_aaset_url = '';

        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPERP_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

        wp_localize_script( 'accounting-admin', 'acc_var', array(
            'user_id'       => $u_id,
            'site_url'      => $site_url,
            'rest_nonce'    => $rest_nonce,
            'logout_url'    => $logout_url,
            'acc_aaset_url' => $acc_aaset_url,
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
        $prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

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
            ]
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
