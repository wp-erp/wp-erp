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
        $section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';

        if ( is_admin() ) {
            $screen = get_current_screen();
            if ( 'wp-erp_page_erp-settings' === $screen->base ) {
                wp_enqueue_script( 'accounting-helper', ERP_ACCOUNTING_ASSETS . '/js/accounting-helper.js', array( 'jquery', 'erp-tiptip' ), false, true );

                wp_localize_script(
                    'accounting-helper',
                    'erp_acct_helper',
                    array(
						'fin_overlap_msg'  => __( 'Financial year values must not be overlapped!', 'erp' ),
						'fin_val_comp_msg' => __( 'Second value must be greater than the first value!', 'erp' ),
                    )
                );
                return;
            } elseif ( 'wp-erp_page_erp-accounting' !== $screen->base && $section !== 'reimbursement' ) {
                return;
            }
        }

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
        $u_id       = $current_user->ID;
        $site_url   = site_url();
        $logout_url = esc_url( wp_logout_url() );
        $acct_url   = admin_url( 'admin.php' ) . '?page=erp-accounting#/';

        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPERP_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

        $menus = '';

        if ( is_admin() ) {
            $component = 'accounting';
            $menu      = erp_menu();
            $menus     = $menu[ $component ];

            //check items for capabilities
            $items = array_filter(
                $menus,
                function( $item ) {
					if ( ! isset( $item['capability'] ) ) {
						return false;
					}
					return current_user_can( $item['capability'] );
				}
            );

            //sort items for position
            uasort(
                $menus,
                function( $a, $b ) {
					return $a['position'] > $b['position'];
				}
            );
        }

        $erp_acct_dec_separator = erp_get_option( 'erp_ac_de_separator', false, '.' );
        $erp_acct_ths_separator = erp_get_option( 'erp_ac_th_separator', false, ',' );

        $fy_ranges    = erp_acct_get_date_boundary();
        $ledgers      = erp_acct_get_ledgers_with_balances();
        $trn_statuses = erp_acct_get_all_trn_statuses();

        wp_localize_script( 'accounting-bootstrap', 'erp_acct_var', array(
            'user_id'            => $u_id,
            'site_url'           => $site_url,
            'logout_url'         => $logout_url,
            'acct_assets'        => ERP_ACCOUNTING_ASSETS,
            'erp_assets'         => WPERP_ASSETS,
            'erp_acct_menus'     => $menus,
            'erp_acct_url'       => $acct_url,
            'decimal_separator'  => $erp_acct_dec_separator,
            'thousand_separator' => $erp_acct_ths_separator,
            'currency_format'    => erp_acct_get_price_format(),
            'symbol'             => erp_acct_get_currency_symbol(),
            'erp_debug_mode'     => erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ),
            'current_date'       => date( 'Y-m-d' ),
            'fy_lower_range'     => $fy_ranges['lower'],
            'fy_upper_range'     => $fy_ranges['upper'],
            'ledgers'            => $ledgers,
            'trn_statuses'       => $trn_statuses,
            'pdf_plugin_active'  => is_plugin_active( 'erp-pdf-invoice/wp-erp-pdf.php' ),
            'link_copy_success'  => __( 'Link has been successfully copied.', 'erp' ),
            'link_copy_error'    => __( 'Failed to copy the link.', 'erp' ),
            'banner_dimension'   => [
                'width'       => 600,
                'height'      => 600,
                'flex-width'  => true,
                'flex-height' => true
            ],
            'rest' => array(
                'root'    => esc_url_raw( get_rest_url() ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
                'version' => 'erp/v1',
            ),
        ) );
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
            'accounting-vendor'    => [
                'src'       => WPERP_ASSETS . '/js/vendor.js',
                'version'   => filemtime( WPERP_PATH . '/assets/js/vendor.js' ),
                'in_footer' => true,
            ],
            'accounting-bootstrap' => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/bootstrap.js',
                'deps'      => [ 'accounting-vendor' ],
                'version'   => filemtime( ERP_ACCOUNTING_PATH . '/assets/js/bootstrap.js' ),
                'in_footer' => true,
            ],
            'accounting-frontend'  => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/frontend.js',
                'deps'      => [ 'jquery', 'accounting-vendor' ],
                'version'   => filemtime( ERP_ACCOUNTING_PATH . '/assets/js/frontend.js' ),
                'in_footer' => true,
            ],
            'accounting-admin'     => [
                'src'       => ERP_ACCOUNTING_ASSETS . '/js/admin.js',
                'deps'      => [ 'jquery', 'accounting-vendor' ],
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
            'accounting-style'    => [
                'src' => ERP_ACCOUNTING_ASSETS . '/css/style.css',
            ],
            'accounting-frontend' => [
                'src' => ERP_ACCOUNTING_ASSETS . '/css/frontend.css',
            ],
            'accounting-admin'    => [
                'src' => ERP_ACCOUNTING_ASSETS . '/css/admin.css',
            ],
        ];

        return $styles;
    }

}
