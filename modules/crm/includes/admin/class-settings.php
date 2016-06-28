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
        $this->id       = 'erp-crm';
        $this->label    = __( 'CRM', 'erp' );
        $this->sections = $this->get_sections();

        add_action( 'erp_admin_field_listing_save_templates', [ $this, 'listing_save_templates' ] );
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'contacts'  => __( 'Contacts', 'erp' ),
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

        $fields['contacts'][] = [
            'title' => __( 'Contact Settings', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Settings for CRM Contact.', 'erp' ),
            'id'    => 'general_options'
        ];

        $fields['contacts'][] = [
            'title'   => __( 'Auto Import', 'erp' ),
            'id'      => 'user_auto_import',
            'type'    => 'select',
            'desc'    => __( 'Allow to auto import new user as crm contact.', 'erp' ),
            'options' => [ 1 => __('On', 'erp'), 0 => __( 'Off', 'erp') ],
            'default' =>  0,
        ];

        global $wp_roles;
        $roles = $wp_roles->get_names();

        $fields['contacts'][] = [
            'title'   => __( 'User\'s Roles', 'erp' ),
            'id'      => 'user_roles',
            'type'    => 'multicheck',
            'desc'    => __( 'Selected user roles are considered to auto import.', 'erp' ),
            'options' => $roles,
            'default' => ['subscriber'] // Default roles
        ];

        $life_stages = erp_crm_get_life_stages_dropdown_raw();
        $crm_users   = erp_crm_get_crm_user();

        $users = ['' => __( '&mdash; Select Owner &mdash;', 'erp' )];

        foreach ( $crm_users as $user ) {
            $users[ $user->ID ] = $user->display_name . ' &lt;' . $user->user_email . '&gt;';
        }

        $fields['contacts'][] = [
            'title'   => __( 'Default Contact Owner', 'erp' ),
            'id'      => 'contact_owner',
            'type'    => 'select',
            'desc'    => __( 'Default contact owner for contact.', 'erp' ),
            'options' => $users,
        ];

        $fields['contacts'][] = [
            'title'   => __( 'Default Life Stage', 'erp' ),
            'id'      => 'life_stage',
            'type'    => 'select',
            'desc'    => __( 'Default life stage for contact.', 'erp' ),
            'options' => $life_stages,
        ];

        $fields['contacts'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

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
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

        $fields['templates']['submit_button'] = false;

        $fields = apply_filters( 'erp_settings_crm_section_fields', $fields, $section );

        $section = $section === false ? $fields['contacts'] : $fields[$section];

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
                    if ( $save_replies ) {
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
                                            <a class="erp-crm-save-replies-edit erp-tips" title="'. __( 'Edit', 'erp' ) .'" href="#" data-id="' . $save_reply->id . '"><i class="fa fa-pencil-square-o"></i></a>
                                            <a class="erp-crm-delete-save-replies erp-tips" title="'. __( 'Delete', 'erp' ) .'" href="#" data-id="' . $save_reply->id . '"><i class="fa fa-trash-o"></i></a>
                                        </td>';
                                        break;

                                    default :
                                        do_action( 'erp_templates_setting_column_' . $key, $email );
                                    break;
                                }
                            }
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3">' . __( 'No templates found', 'erp' ) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php
    }
}
