<?php

namespace WeDevs\ERP\Settings;

/**
 * Scripts and Styles Class
 */
class Assets {

    public function __construct() {

        // Prevent duplicate loading
        if ( did_action( 'erp_settings_loaded' ) ) {
            return;
        }

        // Register & Enquee Assets
        $this->register_assets();
        $this->enquee_assets();

        /**
        * Trigger after settings loaded
        *
        * @since 1.8.6
        */
       do_action( 'erp_settings_loaded' );
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register_assets() {
        $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    public function enquee_assets() {
        // Load styles
        wp_enqueue_style( 'erp-settings' );
        wp_enqueue_style( 'erp-settings-bootstrap' );

        // Load scripts
        wp_enqueue_script( 'settings-vendor' );
        wp_enqueue_script( 'erp-settings-bootstrap' );
        wp_enqueue_script( 'erp-settings' );
    }

    /**
     * Register scripts
     *
     * @param array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        $u_id           = get_current_user_id();
        $site_url       = site_url();
        $logout_url     = esc_url( wp_logout_url() );
        $settings_url   = admin_url( 'admin.php' ) . '?page=erp-settings#/';

        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : WPERP_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

        $menus = $this->get_settings_menus();

        wp_localize_script( 'erp-settings-bootstrap', 'erp_settings_var', [
            'user_id'               => $u_id,
            'site_url'              => $site_url,
            'admin_url'             => admin_url( 'admin.php' ),
            'erp_pro_link'          => 'https://wperp.com/pricing/?nocache&utm_medium=modules&utm_source=erp-settings-page',
            'logout_url'            => $logout_url,
            'settings_assets'       => WPERP_ASSETS,
            'erp_assets'            => WPERP_ASSETS,
            'erp_settings_menus'    => $menus,
            'erp_settings_url'      => $settings_url,
            'erp_debug_mode'        => erp_get_option( 'erp_debug_mode', 'erp_settings_general', 0 ),
            'current_date'          => erp_current_datetime()->format( 'Y-m-d' ),
            'date_format'           => erp_get_date_format(),
            'ajax_url'              => admin_url( 'admin-ajax.php' ),
            'nonce'                 => wp_create_nonce( 'erp-settings-nonce' ),
            'action'                => 'erp-settings-save',
            'rest'                  => [
                'root'    => esc_url_raw( get_rest_url() ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
                'version' => 'erp/v1',
                'field'   => wp_nonce_field( 'erp-settings-nonce' )
            ],
        ] );
    }

    /**
     * Get settings menus/pages
     */
    public function get_settings_menus ( ) {
        $settings[] = new General();

        $settings   = apply_filters( 'erp_settings_pages', $settings );

        $pro_activated = false;
        $wc_purchased  = false;
        $wc_activated  = false;

        if ( class_exists( 'WP_ERP_Pro' ) ) {
            $pro_activated = true;
            $purchased_ext = wp_erp_pro()->update->get_licensed_extensions();

            if ( in_array( 'accounting/woocommerce', $purchased_ext  ) ) {
                $wc_purchased = true;
                $active_ext   = wp_erp_pro()->module->get_active_modules();

                if ( in_array( 'woocommerce', $active_ext  ) ) {
                    $wc_activated = true;
                }
            }
        }

        $wc_settings = new Woocommerce();
        
        $wc_settings->extra['pro_activated'] = $pro_activated;
        $wc_settings->extra['wc_purchased']  = $wc_purchased;
        $wc_settings->extra['wc_activated']  = $wc_activated;

        if ( ! $pro_activated || ! $wc_purchased || ! $wc_activated ) {
            if ( $pro_activated && ! $wc_purchased ) {
                $wc_settings->extra['notice'] = $this->get_wc_purchase_notice();
            } else if ( $pro_activated && $wc_purchased && ! $wc_activated ) {
                $wc_settings->extra['notice'] = $this->get_wc_activation_notice();
            } else if ( ! $pro_activated ) {
                $wc_settings->extra['pro_label'] = true;
            }

            $settings[] = $wc_settings;
        }
        
        $settings[] = new Email();

        if ( ! empty( wperp()->integration->get_integrations() ) ) {
            $settings[] = new Integration();
        }

        if ( ! empty( erp_addon_licenses() ) ) {
            $settings[] = new License();
        }

        $settings_data = [];

        foreach ( $settings as $setting ) {
            $settings_data[] = [
                'id'            => $setting->id,
                'slug'          => '/' . $setting->id,
                'sections'      => $setting->get_sections(),
                'icon'          => $setting->icon,
                'label'         => $setting->label,
                'single_option' => $setting->single_option,
                'extra'         => $setting->extra,
                'fields'        => $setting->get_section_fields( '', true )
            ];
        }

        return $settings_data;
    }

    /**
     * Generates notice when WooCommerce extension is not purchased
     * 
     * @since 1.9.0
     *
     * @return string
     */
    public function get_wc_purchase_notice() {
        $wc_url = trailingslashit( wp_erp_pro()->update->get_base_url() ) . 'pricing?utm_source=wp-admin&utm_medium=link&utm_campaign=erp-settings-page';
        
        return __( "We're Sorry, You Haven't Purchased Our WooCommerce Extension. Please Purchase<br><a target='_blank' href='{$wc_url}'>WP ERP WooCommerce Extension</a> to Unlock This feature.", "erp" );
    }

    /**
     * Generates notice when WooCommerce extension is not enabled
     * 
     * @since 1.9.0
     *
     * @return string
     */
    public function get_wc_activation_notice() {
        $modules_page_url = admin_url( 'admin.php?page=erp-extensions' );

        return __( "You're Just One Step Away from This Feature.<br>Please Activate <strong>WooCommerce</strong> Extension<br></a> from <a href='{$modules_page_url}'>Modules</a> to Unlock This feature.", "erp" );
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
            'settings-vendor' => [
                'src'       => WPERP_ASSETS . '/js/vendor.js',
                'version'   => filemtime( WPERP_PATH . '/assets/js/vendor.js' ),
                'in_footer' => true,
            ],

            'erp-settings-bootstrap' => [
                'src'       => WPERP_ASSETS . '/js/erp-settings-bootstrap.js',
                'deps'      => [ 'jquery', 'settings-vendor' ],
                'version'   => filemtime( WPERP_PATH . '/assets/js/erp-settings-bootstrap.js' ),
                'in_footer' => true,
            ],

            'erp-settings' => [
                'src'       => WPERP_ASSETS . '/js/erp-settings.js',
                'deps'      => [ 'jquery', 'erp-settings-bootstrap' ],
                'version'   => filemtime( WPERP_PATH . '/assets/js/erp-settings.js' ),
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
            'erp-settings'           => [
                'src' => WPERP_ASSETS . '/css/erp-settings.css',
            ],

            'erp-settings-bootstrap' => [
                'src'       => WPERP_ASSETS . '/css/erp-settings-bootstrap.css',
                'version'   => filemtime( WPERP_PATH . '/assets/js/erp-settings-bootstrap.js' ),
            ],
        ];

        return $styles;
    }
}
