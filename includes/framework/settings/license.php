<?php

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * Integration class
 */
class ERP_License_Settings extends ERP_Settings_Page {
    /**
     * Class constructor
     */
    function __construct() {
        $this->id    = 'erp-license';
        $this->label = __( 'Licenses', 'erp' );

        add_action( 'erp_admin_field_licenses', [ $this, 'integrations' ] );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings() {
        $fields = [
            [
                'title' => __( 'License Manager', 'erp' ),
                'desc'  => sprintf( __( 'Enter your extension license keys here to receive updates for purchased extensions. Visit <a href="%s" target="_blank">your account</a> page.', 'erp' ), 'https://wperp.com/my-account/' ),
                'type'  => 'title',
                'id'    => 'integration_settings'
            ],

            [ 'type' => 'licenses' ],
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
        $licenses = erp_addon_licenses();
        ?>
        <tr valign="top">
            <td class="erp-settings-table-wrapper" colspan="2">
                <table class="erp-settings-table widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <?php
                                $columns = apply_filters( 'erp_license_setting_columns', array(
                                    'name'    => __( 'Extension', 'erp' ),
                                    'version'    => __( 'Version', 'erp' ),
                                    'license' => __( 'License Key', 'erp' ),
                                ) );

                                foreach ( $columns as $key => $column ) {
                                    echo '<th class="erp-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
                                }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ( $licenses as $addon ) {
                            echo '<tr>';

                            foreach ( $columns as $key => $column ) {
                                switch ( $key ) {
                                    case 'name' :
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                            <strong>' . esc_html( $addon['name'] ) . '</strong>
                                        </td>';
                                        break;

                                    case 'version':
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                            ' . esc_html( $addon['version'] ) . '
                                        </td>';
                                        break;

                                    case 'license':
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                            <input type="text" name="' . esc_attr( $addon['id'] ) .'" value="' . esc_attr( $addon['license'] ) .'" class="regular-text" />';
                                        echo wp_kses_post( erp_get_license_status( $addon ) );
                                        echo '</td>';
                                        break;
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <style>
        table.erp-settings-table th.erp-settings-table-name, table.erp-settings-table td.erp-settings-table-name {
            width: 35%;
        }
        </style>
        <?php
    }

    /**
     * Save the settings.
     *
     * @param  boolean $section (optional)
     *
     * @return void
     */
    function save( $section = false ) { }
}

return new ERP_License_Settings();
