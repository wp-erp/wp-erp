<?php

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * General class
 */
class ERP_API_Settings extends ERP_Settings_Page {

    function __construct() {
        $this->id       = 'erp-api';
        $this->label    = __( 'API', 'erp' );
        $this->sections = $this->get_sections();

        add_action( 'erp_admin_field_api', [ $this, 'keys' ] );
    }

   /**
     * Get sections
     *
     * @return array
     */
    public function get_sections() {
        $sections = [
            'general' => __( 'General', 'erp' ),
            'keys'    => __( 'Keys/Apps', 'erp' )
        ];

        return apply_filters( 'erp_get_sections_' . $this->id, $sections );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {

        $fields['general'] = [
            [ 'title' => __( 'General Options', 'erp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ],

            [
                'title'   => __( 'Enable API', 'erp' ),
                'id'      => 'enable_api',
                'type'    => 'radio',
                'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
                'default' => 'no'
            ],

            [ 'type' => 'sectionend', 'id' => 'script_styling_options' ],
        ];

        $fields['keys'] = [
            [
                'title' => __( '', 'erp' ),
                'type'  => 'title',
                'desc'  => __( '', 'erp' ),
                'id'    => 'erp-api-options'
            ],
            [
                'type' => 'api'
            ],
            [ 'type' => 'sectionend', 'id' => 'script_styling_options' ],
        ];

        $fields['keys']['submit_button'] = false;

        $section = ( $section === false ) ? $fields['checkout'] : isset( $fields[ $section ] ) ? $fields[ $section ] : [];

        return apply_filters( 'erp_settings_section_fields_' . $this->id , $section );
    }

    /**
     * Display keys html
     *
     * @return void
     */
    public function keys() {
        $api_keys = \WeDevs\ERP\Framework\Models\APIKey::all();
        ?>

        <tr valign="top">
            <td class="erp-settings-table-wrapper" colspan="2">
                <h3>
                    <?php _e( 'Keys/Apps', 'erp' ); ?>
                    <a class="button" id="erp-api-new-key-btn" href="#" data-id="0" data-title="<?php _e( 'New Key', 'erp' ); ?>"><?php _e( 'Add Key', 'erp' ); ?></a>
                </h3>
                <table class="erp-settings-table widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="erp-settings-table-name"><?php _e( 'Name', 'erp' ); ?></th>
                            <th class="erp-settings-table-key"><?php _e( 'Key', 'erp' ); ?></th>
                            <th class="erp-settings-table-user"><?php _e( 'User', 'erp' ); ?></th>
                            <th class="erp-settings-table-last-access"><?php _e( 'Last Access', 'erp' ); ?></th>
                            <th class="erp-settings-table-actions"><?php _e( 'Actions', 'erp' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>

                <?php
                    if ( ! $api_keys->isEmpty() ) {
                        foreach ( $api_keys as $api_key ) {
                    ?>
                            <tr class="erp-api-key-single">
                                <td class="erp-settings-table-name">
                                    <a class="edit-key" href="#" data-id="<?php echo $api_key->id; ?>" data-title="Edit Key" data-data='<?php echo json_encode( $api_key ); ?>' data-selected='<?php echo $api_key->user_id; ?>'><?php echo $api_key->name; ?></a>
                                </td>
                                <td class="erp-settings-table-key">
                                    <?php echo '<code>&hellip;' . esc_html( substr( $api_key->api_key, -7 ) ) . '</code>'; ?>
                                </td>
                                <td class="erp-settings-table-user">
                                    <?php
                                        $user = get_user_by( 'id', $api_key->user_id );
                                        echo $user->display_name;
                                    ?>
                                </td>
                                <td class="erp-settings-table-last-access">
                                    <?php echo ( $api_key->last_accessed_at != '0000-00-00 00:00:00' ) ? erp_format_date( $api_key->last_accessed_at ) : '-'; ?>
                                </td>
                                <td class="erp-settings-table-actions">
                                    <a class="edit-key" href="#" data-id="<?php echo $api_key->id; ?>" data-title="Edit Key" data-data='<?php echo json_encode( $api_key ); ?>' data-selected='<?php echo $api_key->user_id; ?>'><?php _e( 'Edit', 'erp' ); ?></a>
                                     / <a class="remove-key" href="#" data-id="<?php echo $api_key->id; ?>"><?php _e( 'Revoke', 'erp' ); ?></a>
                                </td>
                            </tr>
                <?php
                        }
                    } else {
                ?>
                        <tr>
                            <td colspan="5"><?php _e( 'No items found.', 'erp' ); ?></td>
                        </tr>
                <?php
                    }
                ?>
                    </tbody>
                </table>
            </td>
        </tr>

        <?php
    }
}

return new ERP_API_Settings();
