<?php

namespace WeDevs\ERP\CRM\Admin;

use WeDevs\ERP\Settings\Template;

/**
 * CRM Settings class
 */
class Settings extends Template {

    public $id;
    public $label;
    public $sections;
    public $icon;
    /**
     * Init CRMSettings initial data
     */
    public function __construct() {
        $this->id       = 'erp-crm';
        $this->label    = __( 'CRM', 'erp' );
        $this->sections = $this->get_sections();
        $this->icon     = WPERP_ASSETS . '/images/wperp-settings/crm.png';
    }

    /**
     * Get Option ID for CRM settings
     *
     * @param string $sub_sub_section
     *
     * @return string $option_id
     */
    public function get_option_id( $sub_sub_section = '' ) {
        if ( ! empty ( $sub_sub_section ) ) {
            return parent::get_option_id() . '_' . $sub_sub_section;
        }

        return parent::get_option_id();
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = [
            'contacts'      => __( 'Contacts', 'erp' ),
            'contact_forms' => __( 'Contact Forms', 'erp' ),
            'subscription'  => __( 'Subscription', 'erp' )
        ];

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
    public function get_section_fields( $section = '', $all_data = false ) {
        $fields['contacts'][] = [
            'title' => __( 'Contact Settings', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Settings for CRM Contact.', 'erp' ),
            'id'    => 'general_options',
        ];

        $fields['contacts'][] = [
            'title'   => __( 'Auto Import', 'erp' ),
            'id'      => 'user_auto_import',
            'type'    => 'select',
            'desc'    => __( 'Allow to auto import new user as crm contact.', 'erp' ),
            'options' => [ 1 => __( 'On', 'erp' ), 0 => __( 'Off', 'erp' ) ],
            'default' => 0,
        ];

        global $wp_roles;
        $roles = $wp_roles->get_names();

        $fields['contacts'][] = [
            'title'   => __( 'User&apos;s Roles', 'erp' ),
            'id'      => 'user_roles',
            'type'    => 'multicheck',
            'desc'    => __( 'Selected user roles are considered to auto import.', 'erp' ),
            'options' => $roles,
            'default' => ['subscriber'], // Default roles
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
            'id'   => 'script_styling_options',
        ];

        $fields['subscription'][] = [
            'title' => __( 'Contact Group subscription settings', 'erp' ),
            'type'  => 'title',
            'id'    => 'general_options',
        ];

        $fields['subscription'][] = [
            'title'        => __( 'Enable signup confirmation', 'erp' ),
            'id'           => 'is_enabled',
            'type'         => 'checkbox',
            'desc'         => __( 'Yes', 'erp' ),
            'options'      => [
                'yes'      => __( 'Yes', 'erp' ),
            ],
            'default'      => 'yes',
            'tooltip'      => true,
            'tooltip_text' => __( 'If you enable this option, your subscribers will first receive a confirmation email after they subscribe. Once they confirm their subscription (via this email), they will be marked as &apos;subscribed&apos;.', 'erp' )
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
                'cols' => 90,
            ],
            'desc'    => sprintf( __( "Don&apos;t forget to include: <code>[activation_link]Confirm your subscription.[/activation_link]</code>. <br><br>Optional: <code>[contact_groups_to_confirm]</code>.", 'erp' ) ),
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
            'default'            => __( 'You are now subscribed!', 'erp' ),
        ];

        $fields['subscription'][] = [
            'title_before_field' => __( 'Content', 'erp' ),
            'id'                 => 'confirm_page_content',
            'type'               => 'textarea',
            'default'            => __( "We've added you to our email list. You'll hear from us shortly.", 'erp' ),
            'custom_attributes'  => [
                'rows' => 5,
                'cols' => 46,
            ],
        ];

        $fields['subscription'][] = [
            'title'              => __( 'Unsubscribe Page', 'erp' ),
            'title_before_field' => __( 'Title', 'erp' ),
            'id'                 => 'unsubs_page_title',
            'type'               => 'text',
            'default'            => __( 'You are now unsubscribed', 'erp' ),
        ];

        $fields['subscription'][] = [
            'title_before_field' => __( 'Content', 'erp' ),
            'id'                 => 'unsubs_page_content',
            'type'               => 'textarea',
            'default'            => __( 'You are successfully unsubscribed from list(s):', 'erp' ),
            'custom_attributes'  => [
                'rows' => 5,
                'cols' => 46,
            ],
        ];

        $fields['subscription'][] = [
            'title'              => __( 'Edit Subscription Page', 'erp' ),
            'title_before_field' => __( 'Title', 'erp' ),
            'id'                 => 'edit_sub_page_title',
            'type'               => 'text',
            'default'            => __( 'Edit Your Subscription', 'erp' ),
        ];

        $fields['subscription'][] = [
            'title_before_field' => __( 'Content', 'erp' ),
            'id'                 => 'edit_sub_page_content',
            'type'               => 'textarea',
            'default'            => __( 'Update your preferences', 'erp' ),
            'custom_attributes'  => [
                'rows' => 5,
                'cols' => 46,
            ],
        ];

        $fields['subscription'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];

        $fields = apply_filters( 'erp_settings_crm_section_fields', $fields, $section );

        foreach ( $this->get_sections() as $sec => $name ) {
            if ( empty( $fields[ $sec ] ) ) {
                $fields = apply_filters( 'erp_settings_crm_section_fields', $fields, $sec );
            }
        }

        if ( $all_data ) {
            return $fields;
        }

        $section = $section === false ? $fields['contacts'] : $fields[$section];

        return $section;
    }

    /**
     * Override parent save to save sub section fields
     *
     * @since 1.3.14
     *
     * @param bool $section
     */
    public function save( $section = false ) {
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'erp-settings-nonce' ) ) {
            if ( ! isset( $_POST['sub_sub_section'] ) ) {
                parent::save( $section );

                return;
            }

            parent::save();
        }
    }
}
