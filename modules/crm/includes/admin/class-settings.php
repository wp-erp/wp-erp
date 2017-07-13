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
            'contacts'     => __( 'Contacts', 'erp' ),
            'templates'    => __( 'Templates', 'erp' ),
            'subscription' => __( 'Subscription', 'erp' ),
        );

        return apply_filters( 'erp_settings_crm_sections', $sections );
    }

    /**
     * Get sections fields
     *
     * @since 1.0.0
     * @since 1.1.17 Add subscription page settings
     * @since 1.2.2  Add edit subscription page settings
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

        $fields['subscription'][] = [
            'title' => __( 'Contact Group subscription settings', 'erp' ),
            'type'  => 'title',
            'id'    => 'general_options'
        ];

        $fields['subscription'][] = [
            'title'   => __( 'Enable signup confirmation', 'erp' ),
            'id'      => 'is_enabled',
            'type'    => 'checkbox',
            'desc'    => __( 'Yes', 'erp' ),
            'options' => [
                'yes' => __( 'Yes', 'erp' ),
            ],
            'default' => 'yes',
            'tooltip' => __( 'If you enable this option, your subscribers will first receive a confirmation email after they subscribe. Once they confirm their subscription (via this email), they will be marked as \'subscribed\'.', 'erp' ),
        ];

        $fields['subscription'][] = [
            'title'   => __( 'Email subject', 'erp' ),
            'id'      => 'email_subject',
            'type'    => 'text',
            'default' => sprintf( __( 'Confirm your subscription to %s', 'erp' ), get_bloginfo( 'name' ) ),
        ];

        $fields['subscription'][] = [
            'title'   => __( 'Email content', 'erp' ),
            'id'      => 'email_content',
            'type'    => 'textarea',
            'default' => sprintf(
                __( "Hello!\n\nThanks so much for signing up for our newsletter.\nWe need you to activate your subscription to the list(s): [contact_groups_to_confirm] by clicking the link below: \n\n[activation_link]Click here to confirm your subscription.[/activation_link]\n\nThank you,\n\n%s", 'erp' ),
                get_bloginfo( 'name' )
            ),
            'custom_attributes' => [
                'rows' => 12,
                'cols' => 90
            ],
            'desc'    => sprintf( __( "Don't forget to include: <code>[activation_link]Confirm your subscription.[/activation_link]</code>. <br><br>Optional: <code>[contact_groups_to_confirm]</code>.", 'erp' ) )
        ];

        $wp_pages = get_pages();

        $fields['subscription'][] = [
            'title'   => __( 'Subscription Page', 'erp' ),
            'id'      => 'page_id',
            'type'    => 'select',
            'options' => wp_list_pluck( $wp_pages, 'post_title', 'ID' ),
            'desc'    => __( 'When subscribers click on the activation link, they will be redirected to this page.', 'erp' ),
        ];

        $fields['subscription'][] = [
            'title'              => __( 'Confirmation Page', 'erp' ),
            'title_before_field' => __( 'Title', 'erp' ),
            'id'                 => 'confirm_page_title',
            'type'               => 'text',
            'default'            => __( 'You are now subscribed!', 'erp' )
        ];

        $fields['subscription'][] = [
            'title_before_field' => __( 'Content', 'erp' ),
            'id'                 => 'confirm_page_content',
            'type'               => 'textarea',
            'default'            => __( "We've added you to our email list. You'll hear from us shortly.", 'erp' ),
            'custom_attributes'  => [
                'rows' => 5,
                'cols' => 46
            ],
        ];

        $fields['subscription'][] = [
            'title'              => __( 'Unsubscribe Page', 'erp' ),
            'title_before_field' => __( 'Title', 'erp' ),
            'id'                 => 'unsubs_page_title',
            'type'               => 'text',
            'default'            => __( 'You are now unsubscribed', 'erp' )
        ];

        $fields['subscription'][] = [
            'title_before_field' => __( 'Content', 'erp' ),
            'id'                 => 'unsubs_page_content',
            'type'               => 'textarea',
            'default'            => __( 'You are successfully unsubscribed from list(s):', 'erp' ),
            'custom_attributes'  => [
                'rows' => 5,
                'cols' => 46
            ],
        ];

        $fields['subscription'][] = [
            'title'              => __( 'Edit Subscription Page', 'erp' ),
            'title_before_field' => __( 'Title', 'erp' ),
            'id'                 => 'edit_sub_page_title',
            'type'               => 'text',
            'default'            =>  __( 'Edit Your Subscription', 'erp' )
        ];

        $fields['subscription'][] = [
            'title_before_field' => __( 'Content', 'erp' ),
            'id'                 => 'edit_sub_page_content',
            'type'               => 'textarea',
            'default'            => __( 'Update your preferences', 'erp' ),
            'custom_attributes'  => [
                'rows' => 5,
                'cols' => 46
            ],
        ];

        $fields['subscription'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

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
