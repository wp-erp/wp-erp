<?php

namespace WeDevs\ERP\Framework;

/**
 * Scripts and Styles Class
 */
class Settings_Assets {

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

        // dd( $menus );

        wp_localize_script( 'erp-settings-bootstrap', 'erp_settings_var', [
            'user_id'               => $u_id,
            'site_url'              => $site_url,
            'logout_url'            => $logout_url,
            'settings_assets'       => WPERP_ASSETS . '/src',
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
        $settings[] = include __DIR__ . '/settings/general.php';

        $settings   = apply_filters( 'erp_settings_pages', $settings );

        $settings[] = include __DIR__ . '/settings/email.php';

        // Display integrations tab only if any integration exist.
        $integrations = wperp()->integration->get_integrations();

        if ( ! empty( $integrations ) ) {
            $settings[] = include __DIR__ . '/settings/integration.php';
        }

        $licenses = erp_addon_licenses();

        if ( $licenses ) {
            $settings[] = include __DIR__ . '/settings/license.php';
        }

        $settings_data = [];

        foreach ( $settings as $setting ) {
            $settings_data[] = [
                'id'               => $setting->id,
                'slug'             => '/' . $setting->id,
                'sections'         => $setting->get_sections(),
                'icon'             => $setting->icon,
                'label'            => $setting->label,
                'single_option'    => $setting->single_option,
                'fields'           => $setting->get_section_fields( '', true )
            ];
        }

        return $settings_data;
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

            'erp-settings-bootstrap'     => [
                'src'       => WPERP_ASSETS . '/src/js/erp-settings-bootstrap.js',
                'deps'      => [ 'jquery', 'settings-vendor' ],
                'version'   => filemtime( WPERP_PATH . '/assets/src/js/erp-settings-bootstrap.js' ),
                'in_footer' => true,
            ],

            'erp-settings'     => [
                'src'       => WPERP_ASSETS . '/src/js/erp-settings.js',
                'deps'      => [ 'jquery', 'erp-settings-bootstrap' ],
                'version'   => filemtime( WPERP_PATH . '/assets/src/js/erp-settings.js' ),
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
            'erp-settings'    => [
                'src' => WPERP_ASSETS . '/src/css/erp-settings.css',
            ]
        ];

        return $styles;
    }
}
