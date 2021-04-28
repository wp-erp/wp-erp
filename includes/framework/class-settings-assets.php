<?php

namespace WeDevs\ERP\Framework;

/**
 * Scripts and Styles Class
 */
class Settings_Assets {
    public function __construct() {
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
     * @param array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        global $current_user;
        $u_id           = $current_user->ID;
        $site_url       = site_url();
        $logout_url     = esc_url( wp_logout_url() );
        $settings_url   = admin_url( 'admin.php' ) . '?page=erp-settings#/';

        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPERP_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

        $menus = '';

        if ( is_admin() ) {
            $component = 'settings';
            $menu      = erp_menu();
            $menus     = $menu[ $component ];

            //check items for capabilities
            $items = array_filter(
                $menus,
                function ( $item ) {
                    if ( ! isset( $item['capability'] ) ) {
                        return false;
                    }

                    return current_user_can( $item['capability'] );
                }
            );

            //sort items for position
            uasort(
                $menus,
                function ( $a, $b ) {
                    return $a['position'] > $b['position'];
                }
            );
        }

        wp_localize_script( 'settings-bootstrap', 'erp_acct_var', [
            'user_id'            => $u_id,
            'site_url'           => $site_url,
            'logout_url'         => $logout_url,
            'acct_assets'        => ERP_ACCOUNTING_ASSETS,
            'erp_assets'         => WPERP_ASSETS,
            'erp_acct_menus'     => $menus,
            'erp_debug_mode'     => erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ),
            'current_date'       => erp_current_datetime()->format( 'Y-m-d' ),
            'pdf_plugin_active'  => is_plugin_active( 'erp-pdf-invoice/wp-erp-pdf.php' ),
            'link_copy_success'  => __( 'Link has been successfully copied.', 'erp' ),
            'link_copy_error'    => __( 'Failed to copy the link.', 'erp' ),
            'date_format'        => erp_get_date_format(),
            'rest' => [
                'root'    => esc_url_raw( get_rest_url() ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
                'version' => 'erp/v1',
            ],
        ] );
    }

    /**
     * Register styles
     *
     * @param array $styles
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
            'settings-vendor'    => [
                'src'       => WPERP_ASSETS . '/js/vendor.js',
                'version'   => filemtime( WPERP_PATH . '/assets/js/vendor.js' ),
                'in_footer' => true,
            ],
            'settings-bootstrap' => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/bootstrap.js',
                'deps'      => [ 'settings-vendor' ],
                'version'   => filemtime( ERP_ACCOUNTING_PATH . '/assets/js/bootstrap.js' ),
                'in_footer' => true,
            ],
            'settings-frontend'  => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/frontend.js',
                'deps'      => [ 'jquery', 'settings-vendor' ],
                'version'   => filemtime( ERP_ACCOUNTING_PATH . '/assets/js/frontend.js' ),
                'in_footer' => true,
            ],
            'settings-admin'     => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/admin.js',
                'deps'      => [ 'jquery', 'settings-vendor', 'settings-bootstrap' ],
                'version'   => filemtime( ERP_ACCOUNTING_PATH . '/assets/js/admin.js' ),
                'in_footer' => true,
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
            'settings-style'    => [
                'src' => ERP_ACCOUNTING_ASSETS . '/css/style.css',
            ],
            'settings-frontend' => [
                'src' => ERP_ACCOUNTING_ASSETS . '/css/frontend.css',
            ],
            'settings-admin'    => [
                'src' => ERP_ACCOUNTING_ASSETS . '/css/admin.css',
            ],
        ];

        return $styles;
    }


    /**
     * Undocumented function
     *
     * @since 1.7.5
     *
     * @return void
     */
    public function includes() {

    }
}
