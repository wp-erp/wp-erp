<?php
namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * Settings class
 */
class CRM_Settings extends ERP_Settings_Page {

    /**
     * [__construct description]
     */
    public function __construct() {
        $this->id            = 'erp-crm';
        $this->label         = __( 'CRM', 'erp' );

        $this->sections      = $this->get_sections();

        add_action( 'erp_admin_field_listing_save_templates', [ $this, 'listing_save_templates' ] );
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'templates' => __( 'Templates', 'erp' ),
        );

        return apply_filters( 'erp_settings_crm_sections', $sections );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {

        $fields['templates'][] = [
            'title' => __( 'Saved Replies', 'erp' ),
            'type'  => 'title',
            'desc'  => __( '', 'erp' ),
            'id'    => 'general_options'
        ];

        $fields['templates'][] = [
            'type' => 'listing_save_templates',
        ];

        $fields['templates'][] = [
            'type'  => 'sectionend',
            'id'    => 'script_styling_options'
        ];

        $fields = apply_filters( 'erp_settings_crm_section_fields', $fields, $section );

        $section = $section === false ? $fields['templates'] : $fields[$section];

        return $section;
    }

    public function listing_save_templates() {
        $save_replies = erp_crm_get_save_replies();
    ?>
    <style type="text/css">
        td.erp-crm-templates-wrapper {
            padding: 0 15px 10px 0;;
        }
        table.erp-crm-templates-table th {
            padding: 9px 7px!important;
            vertical-align: middle;
        }

        table.erp-crm-templates-table td {
            padding: 7px;
            line-height: 2em;
            vertical-align: middle;
        }

        table.erp-crm-templates-table th.erp-templates-settings-table-name,
        table.erp-crm-templates-table td.erp-templates-settings-table-name {
            padding-left: 15px !important;
        }

        table.erp-crm-templates-table td.erp-templates-settings-table-name a {
            font-weight: 700;
        }

        table.erp-crm-templates-table td.erp-templates-settings-table-actions{
            text-align: center;
        }
        table.erp-crm-templates-table td.erp-templates-settings-table-actions a{
            margin-right: 8px;
        }

        table.erp-crm-templates-table tr:nth-child(odd) td {
            background: #f9f9f9;
        }

        #erp-crm-add-save-replies {
            margin-right: 15px;
            margin-bottom: 10px;
        }
    </style>
    <a href="#" class="erp-crm-add-save-replies button alignright" id="erp-crm-add-save-replies" title="<?php _e( 'Add new Template', 'erp' ); ?>"><?php _e( 'Add Templates', 'erp' ); ?></a>
    <tr valign="top">
        <td class="erp-crm-templates-wrapper" colspan="2">
            <table class="erp-crm-templates-table widefat" cellspacing="0">
                <thead>
                    <tr>
                        <?php
                            $columns = apply_filters( 'erp_email_setting_columns', array(
                                'name'        => __( 'Template Name', 'erp' ),
                                'subject' => __( 'Subject', 'erp' ),
                                'actions'     => ''
                            ) );

                            foreach ( $columns as $key => $column ) {
                                echo '<th class="erp-templates-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
                            }
                        ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    foreach ( $save_replies as $replies_key => $save_reply ) {
                        echo '<tr>';

                        foreach ( $columns as $key => $column ) {
                            switch ( $key ) {
                                case 'name' :
                                    echo '<td class="erp-templates-settings-table-' . esc_attr( $key ) . '">
                                        <a href="#">' . $save_reply->name . '</a>
                                    </td>';
                                    break;

                                case 'subject':
                                    $subject = ( isset( $save_reply->subject ) && ! empty( $save_reply->subject ) ) ? $save_reply->subject : '----';
                                    echo '<td class="erp-templates-settings-table-' . esc_attr( $key ) . '">
                                        <span class="help">' . $subject . '</span>
                                    </td>';
                                    break;

                                case 'actions' :
                                    echo '<td class="erp-templates-settings-table-' . esc_attr( $key ) . '">
                                        <a class="erp-crm-edit-save-replies erp-tips" title="'. __( 'Edit', 'erp' ) .'" href="#" data-id="' . $save_reply->id . '"><i class="fa fa-pencil-square-o"></i></a>
                                        <a class="erp-crm-delete-save-replies erp-tips" title="'. __( 'Delete', 'erp' ) .'" href="#" data-id="' . $save_reply->id . '"><i class="fa fa-trash-o"></i></a>
                                    </td>';
                                    break;

                                default :
                                    do_action( 'erp_templates_setting_column_' . $key, $email );
                                break;
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php
    }
}
