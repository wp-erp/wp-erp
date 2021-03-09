<?php

namespace WeDevs\ERP\Framework;

/**
 * ERP Admin settings class
 */
class ERP_Admin_Settings {

    /**
     * Settings array
     *
     * @var array
     */
    private static $settings = [];

    /**
     * Sections
     *
     * @var array
     */
    private static $section = [];

    /**
     * Include all settings file
     *
     * @since 0.1
     *
     * @return array
     */
    public static function get_settings() {
        if ( !self::$settings ) {
            $settings = [];

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

            self::$settings = $settings;
        }

        return self::$settings;
    }

    /**
     * Get current tab and subtab/section
     *
     * @since 0.1
     *
     * @return array()
     */
    public static function get_current_tab_and_section() {
        $settings  = self::get_settings();
        $query_arg = [ 'tab' => false, 'subtab' => false ];

        if ( ! isset( $settings[0] ) ) {
            return $query_arg;
        }

        $default = $settings[0]->get_id();

        if ( empty( $default ) ) {
            return $query_arg;
        }

        $current_tab = $query_arg['tab'] = isset( $_GET['tab'] ) ? sanitize_title( wp_unslash( $_GET['tab'] ) ) : $settings[0]->get_id();

        foreach ( $settings as $obj ) {
            $sections[$obj->get_id()] = isset( $obj->sections ) ? $obj->sections : [];
        }

        if ( ! isset( $sections[$current_tab] ) ) {
            return $query_arg;
        }

        if ( ! is_array( $sections[$current_tab] ) ) {
            return $query_arg;
        }

        if ( ! count( $sections[$current_tab] ) ) {
            return $query_arg;
        }

        $query_arg['subtab'] = isset( $_GET['section'] ) ? sanitize_title( wp_unslash( $_GET['section'] ) ) : key( $sections[$current_tab] );

        return $query_arg;
    }

    /**
     * Show settings all tab and subtab fields
     *
     * @since 0.1
     *
     * @return void
     */
    public static function output() {
        global $current_section, $current_tab, $current_class;

        $settings        = self::get_settings();
        $query_arg       = self::get_current_tab_and_section();
        $current_tab     = $query_arg['tab'];
        $current_section = $query_arg['subtab'];
        $sections        = [];

        if ( ! $settings ) {
            return;
        }

        echo '<h2 class="nav-tab-wrapper erp-nav-tab-wrapper" style="margin-bottom: 20px;">';

        foreach ( $settings as $obj ) {
            $url   = sprintf( 'admin.php?page=erp-settings&tab=%s', $obj->get_id() );
            $class = ( $current_tab == $obj->get_id() ) ? ' nav-tab-active' : '';

            if ( $current_tab == $obj->get_id() && $current_class === null ) {
                $current_class = $obj;
            }

            printf( '<a class="nav-tab%s" href="%s">%s</a>', esc_attr( $class ), esc_url( $url ), esc_html( $obj->get_label() ) );
        }

        echo '</h2>';

        self::section_output();

        $current_class->save( $current_section );
        $current_class->output( $current_section );
    }

    /**
     * Show settings subtab fields
     *
     * @since 0.1
     *
     * @return void
     */
    public static function section_output() {
        $settings        = self::get_settings();
        $query_arg       = self::get_current_tab_and_section();
        $current_tab     = $query_arg['tab'];
        $current_section = $query_arg['subtab'];
        $sections        = [];

        if ( ! $current_section ) {
            return;
        }

        foreach ( $settings as $obj ) {
            $sections[$obj->get_id()] = isset( $obj->sections ) ? $obj->sections : [];
        }

        $tab_sections = $sections[$current_tab];

        // don't print sub-sections if only one section available
        if ( count( $tab_sections ) < 1 ) {
            return;
        }

        if ( ! array_key_exists( $current_section, $tab_sections ) ) {
            return;
        }

        ?>
        <div class="erp-custom-menu-container">
            <ul class="erp-nav">

            <?php foreach ( $tab_sections as $slug => $label ) : ?>
                <li class="<?php echo $current_section == $slug ? 'active' : ''; ?>"><a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-settings&tab=' . $current_tab . '&section=' . sanitize_title( $slug ) ) ); ?>"><?php esc_html_e( $label ); ?></a></li>
            <?php endforeach; ?>

            </ul>
            <br class="clear" />
        </div>
        <?php
    }
}
