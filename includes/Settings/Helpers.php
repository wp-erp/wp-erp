<?php

namespace WeDevs\ERP\Settings;

/**
 * ERP Admin settings class
 */
class Helpers {

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
        if ( ! self::$settings ) {
            $settings = [];

            $settings   = apply_filters( 'erp_settings_pages', $settings );

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

        $current_tab = $query_arg['tab'] = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : $settings[0]->get_id();

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
                <li class="<?php echo $current_section == $slug ? 'active' : ''; ?>"><a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-settings&tab=' . $current_tab . '&section=' . sanitize_title( $slug ) ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
            <?php endforeach; ?>

            </ul>
            <br class="clear" />
        </div>
        <?php
    }

    /**
     * Get Options For Settings
     *
     * @since 1.9.0
     *
     * @param array $options - Setting options
     *
     * @return array $data settings data
     */
    public static function process_settings_data ( $options = [] ) {
        $data               = [];
        $single_option_data = [];

        if ( ! empty ( $options['single_option'] ) ) {
            if ( 'erp-integration' !== $options['section_id'] ) {
                $single_option_id = "erp_settings_{$options['single_option']}";

                // If sub_section_id provided, then append it to single_option_id to get data from database
                // Modify it, since In database, it's stored like `erp_settings_{section}_{sub_section_id}`
                if ( ! empty ( $options['sub_section_id'] ) && $options['single_option'] !== $options['sub_section_id'] ) {
                    $single_option_id .= "_{$options['sub_section_id']}";
                }
            } else {
                $single_option_id = 'erp_integration_settings';
            }

            if ( ! empty ( $options['sub_sub_section_id'] ) ) {
                $single_option_id .= "_{$options['sub_sub_section_id']}";
            }

            $single_option_data = ( array ) get_option( $single_option_id );
        }

        foreach ( $options as $option ) {
            if ( ! empty ( $option['id'] ) ) {
                $option_value = count ( $single_option_data ) === 0
                              ? get_option( $option['id'] )
                              : ( ! empty( $single_option_data[ $option['id'] ] )
                              ? $single_option_data[ $option['id'] ] : '' );

                if (empty($option_value) && isset($option['type']) && $option['type'] !== 'select') {
                    $option_value = ! empty ( $option['default'] ) ? $option['default'] : '';
                }

                // Process option value for different type input
                if (isset($option['type'])) {
                    switch ($option['type']) {
                        case 'checkbox':
                            $option_value = $option_value === 'yes' ? true : false;
                            break;

                        case 'image':
                            $option_value = (int) $option_value;
                            $option_value = $option_value ? wp_get_attachment_url($option_value) : '';
                            break;

                        default:
                            break;
                    }
                }

                $option['value'] = $option_value;

                array_push( $data, $option );
            }
        }

        return $data;
    }

    /**
     * Retrieves email templates that cannot be disabled
     *
     * @since 1.9.0
     *
     * @return array
     */
    public static function get_fixedly_enabled_email_templates() {
        return apply_filters( 'email_settings_enable_filter', [
            'erp_email_settings_new-leave-request',
            'erp_email_settings_approved-leave-request',
            'erp_email_settings_rejected-leave-request',
            'erp_email_settings_employee-asset-request',
            'erp_email_settings_employee-asset-approve',
            'erp_email_settings_employee-asset-reject',
            'erp_email_settings_employee-asset-overdue',
        ] );
    }
}
