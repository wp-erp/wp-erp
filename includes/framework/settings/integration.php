<?php

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * Integration class
 */
class ERP_Integration_Settings extends ERP_Settings_Page {
    /**
     * Class constructor
     */
    function __construct() {
        $this->id    = 'erp-integration';
        $this->label = __( 'Integrations', 'erp' );

        add_action( 'erp_admin_field_integrations', [ $this, 'integrations' ] );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings() {
        $fields = [
            [
                'title' => __( 'Integrations', 'erp' ),
                'desc'  => __( 'Various integrations to WP ERP. Click <strong>Configure</strong> to manage the settings.', 'erp' ),
                'type'  => 'title',
                'id'    => 'integration_settings'
            ],

            [ 'type' => 'integrations' ],
            [ 'type' => 'sectionend', 'id' => 'script_styling_options' ],

        ]; // End general settings

        return apply_filters( 'erp_integration_settings', $fields );
    }

    /**
     * Display integrations settings.
     *
     * @return void
     */
    function integrations() {
        $integrations = wperp()->integration->get_integrations();
        ?>
        <tr valign="top">
            <td class="erp-settings-table-wrapper" colspan="2">
                <table class="erp-settings-table widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <?php
                                $columns = apply_filters( 'erp_integration_setting_columns', array(
                                    'name'        => __( 'Integration', 'erp' ),
                                    'description' => __( 'Description', 'erp' ),
                                    'actions'     => ''
                                ) );

                                foreach ( $columns as $key => $column ) {
                                    echo '<th class="erp-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
                                }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ( $integrations as $integration_key => $integration ) {
                            echo '<tr>';

                            foreach ( $columns as $key => $column ) {
                                switch ( $key ) {
                                    case 'name' :
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                            <a href="' . esc_url( admin_url( 'admin.php?page=erp-settings&tab=erp-integration&section=' . esc_attr( strtolower( $integration_key ) ) ) ) . '">' .  esc_html( $integration->get_title() ) . '</a>
                                        </td>';
                                        break;

                                    case 'status':
                                    case 'module':
                                    case 'recipient':
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">

                                        </td>';
                                        break;

                                    case 'description':
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                            <span class="help">' . esc_html( $integration->get_description() ) . '</span>
                                        </td>';
                                        break;

                                    case 'actions' :
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                            <a class="button alignright" href="' . esc_url( admin_url( 'admin.php?page=erp-settings&tab=erp-integration&section=' . strtolower( $integration_key ) ) ) . '">' . esc_html__( 'Configure', 'erp' ) . '</a>
                                        </td>';
                                        break;

                                    default :
                                        do_action( 'erp_integration_setting_column_' . $key, $integration );
                                    break;
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>

        <style>p.submit { display: none; }</style>
        <?php
    }

    /**
     * Output the settings.
     *
     * @param  boolean $section (optional)
     *
     * @return void
     */
    public function output( $section = false ) {
        $current_section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : false;

        // Define integrations that can be customised here
        $integrations = wperp()->integration->get_integrations();

        if ( $current_section ) {
            foreach ( $integrations as $integration_key => $integration ) {
                if ( strtolower( $integration_key ) == $current_section ) {
                    $integration->admin_options();
                    break;
                }
            }
        } else {
            parent::output();
        }
    }

    /**
     * Save the settings.
     *
     * @param  boolean $section (optional)
     *
     * @return void
     */
    function save( $section = false ) {
        if ( isset( $_POST['_wpnonce']) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
            $current_section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : false;

            // saving individual integration settings
            if ( $current_section ) {
                $integrations = wperp()->integration->get_integrations();

                foreach ( $integrations as $integration_key => $integration ) {
                    if ( strtolower( $integration_key ) == $current_section ) {

                        $settings       = $integration->get_form_fields();
                        $update_options = array();

                        if ( $settings) {
                            foreach ($settings as $field) {
                                if ( ! isset( $field['id'] ) || ! isset( $_POST[ $field['id'] ] ) ) {
                                    continue;
                                }

                                $option_value = $this->parse_option_value( $field );

                                if ( ! is_null( $option_value ) ) {
                                    $update_options[ $field['id'] ] = $option_value;
                                }
                            }
                        }

                        do_action( $integration->get_option_id() . '_action', $update_options );

                        $update_options = apply_filters( $integration->get_option_id() . '_filter', $update_options );

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

return new ERP_Integration_Settings();
