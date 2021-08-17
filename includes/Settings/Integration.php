<?php

namespace WeDevs\ERP\Settings;

/**
 * Integration class
 */
class Integration extends Template {

    /**
     * Class constructor
     */
    public function __construct() {
        $this->id    = 'erp-integration';
        $this->label = __( 'Integration', 'erp' );
        $this->icon  = WPERP_ASSETS . '/images/wperp-settings/integration.png';

        $this->extra = [
            'integrations' => wperp()->integration->get_integrations()
        ];

        add_action( 'erp_admin_field_integrations', [ $this, 'integrations' ] );
    }

    /**
     * Get section fields
     *
     * @since 1.10.0
     *
     * @return array
     */
    public function get_section_fields( $section = false ) {
        $fields = [
            [
                'title' => __( 'Integration Management', 'erp' ),
                'desc'  => __( 'Various integrations to WP ERP. Click <strong>Configure</strong> to manage the settings.', 'erp' ),
                'type'  => 'title',
                'id'    => 'integration_settings',
            ],
            [ 'type' => 'sectionend', 'id' => 'script_styling_options' ],
        ];

        return apply_filters( 'erp_integration_settings', $fields );
    }

    /**
     * Save the settings.
     *
     * @param bool $section (optional)
     *
     * @return void
     */
    public function save( $section = false ) {
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
            $current_section = ! empty( $_REQUEST['section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['section'] ) ) : false;

            // saving individual integration settings
            if ( ! empty( $current_section ) ) {
                $integrations = wperp()->integration->get_integrations();

                foreach ( $integrations as $integration_key => $integration ) {
                    if ( strtolower( $integration_key ) == $current_section ) {
                        $settings       = $integration->get_form_fields();
                        $update_options = [];

                        if ( 'sms' === $current_section ) {
                            $processed_fields = [];

                            foreach ( $settings as $key => $setting ) {
                                foreach ( $setting as $field ) {
                                    $processed_fields[] = $field;
                                }
                            }

                            $settings = $processed_fields;

                            $update_options = get_option( $integration->get_option_id() );
                        }

                        foreach ( $settings as $field ) {

                            if ( ! isset( $field['id'] ) || ! isset( $_POST[ $field['id'] ] ) ) {
                                continue;
                            }

                            $option_value = $this->parse_option_value( $field );

                            if ( ! is_null( $option_value ) ) {
                                $update_options[ $field['id'] ] = $option_value;
                            }
                        }

                        $update_options = apply_filters( $integration->get_option_id() . '_filter', $update_options );

                        if ( is_wp_error( $update_options ) ) {
                            return $update_options;
                        }

                        update_option( $integration->get_option_id(), $update_options );

                        break;
                    }
                }
            } else {
                parent::save();
            }
        }
    }
}
