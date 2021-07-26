<?php

namespace WeDevs\ERP\CRM;

use WeDevs\ERP\Settings\Template;

/**
 * CRM Settings class
 */
class CRM_Settings extends Template {

    /**
     * Init CRM_Settings initial data
     */
    public function __construct() {
        $this->id       = 'erp-crm';
        $this->label    = __( 'CRM', 'erp' );
        $this->sections = $this->get_sections();
        $this->icon     = WPERP_ASSETS . '/images/wperp-settings/crm.png';

        add_action( 'erp_update_option', [ $this, 'cron_schedule' ] );
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
            'subscription'  => __( 'Subscription', 'erp' ),
            'email_connect' => __( 'Email Connectivity', 'erp' ),
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

        $fields['email_connect'] = $this->get_email_connect_fields();

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

    public function get_email_connect_fields() {
        $schedules = wp_get_schedules();

        $cron_intervals = []; // Filter cron intervals time to get unique cron data
        $cron_schedules = [];

        foreach ( $schedules as $key => $value ) {
            if ( ! in_array( $value['interval'], $cron_intervals ) ) {
                array_push( $cron_intervals, $value['interval'] );
                $cron_schedules[$key] = $value['display'];
            }
        }

        $fields[] = [
            'title' => __( 'Email Connection Settings', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Settings for CRM Contact Emails Connectivity.', 'erp' ),
            'id'    => 'general_options',
        ];

        $fields[] = [
            'title'   => __( 'Check Emails ', 'erp' ),
            'id'      => 'schedule',
            'type'    => 'select',
            'desc'    => __( 'Interval time to run cron for checking inbound emails.', 'erp' ),
            'options' => $cron_schedules,
            'default' => 'hourly',
        ];

        $fields[] = [
            'type'         => 'sub_sections',
            'sub_sections' => $this->get_email_prodivers(),
        ];

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];

        return $fields;
    }

    /**
     * Get all fields for current Sub Section
     *
     * @since 1.3.14
     *
     * @param string $sub_section
     *
     * @return array fields
     */
    public function get_sub_section_fields( $sub_section ) {
        switch ( $sub_section ) {
            case 'gmail':
                return $this->get_gmail_api_settings_fields();

            case 'imap':
            default:
                return $this->get_imap_settings_fields();
        }
    }

    /**
     * Get all fields for GMAIL API sub section
     *
     * @since 1.3.14
     *
     * @return array
     */
    public function get_gmail_api_settings_fields() {
        $fields[] = [
            'title' => __( 'Gmail / G suite Authentication', 'erp' ),
            'type'  => 'title',
            'desc'  => __( '<a target="_blank" href="https://console.developers.google.com/flows/enableapi?apiid=gmail&pli=1">Create a Google App</a> and authorize your account to Send and Recieve emails using Gmail. Follow instructions from this <a target="_blank" href="https://wperp.com/docs/crm/tutorials/how-to-configure-gmail-api-connection-in-the-crm-settings/?utm_source=Free+Plugin&utm_medium=CTA&utm_content=Backend&utm_campaign=Docs">Documentation</a> to get started', 'erp' ),
        ];

        if ( wperp()->google_auth->is_connected() ) {
            $fields[] = [
                'type' => 'gmail_api_connected',
            ];

            $fields[] = [
                'type' => 'sectionend',
                'id'   => 'script_styling_options',
            ];

            return $fields;
        }

        $fields[] = [
            'title' => __( 'Client ID', 'erp' ),
            'id'    => 'client_id',
            'type'  => 'text',
            'desc'  => __( 'Your APP Client ID', 'erp' ),
        ];

        $fields[] = [
            'title' => __( 'Client Secret', 'erp' ),
            'id'    => 'client_secret',
            'type'  => 'text',
            'desc'  => __( 'Your APP Client Secret', 'erp' ),
        ];

        $fields[] = [
            'title'    => __( 'Redirect URL to use', 'erp' ),
            'id'       => 'redirect_url',
            'type'     => 'text',
            'desc'     => __( 'Copy and Use this url when oAuth consent asks for Authorized Redirect URL', 'erp' ),
            'default'  => esc_url_raw( wperp()->google_auth->get_redirect_url() ),
            'disabled' => true
        ];

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];

        return $fields;
    }

    /**
     * Get IMAP Settings Fields.
     *
     * @return array
     */
    protected function get_imap_settings_fields() {
        if ( ! extension_loaded( 'imap' ) || ! function_exists( 'imap_open' ) ) {
            $fields[] = [
                'title' => __( 'IMAP/POP3 Options', 'erp' ),
                'type'  => 'title',
                'desc'  => sprintf(
                    '%s' . __( 'Your server does not have PHP IMAP extension loaded. To enable this feature, please contact your hosting provider and ask to enable PHP IMAP extension.', 'erp' ) . '%s',
                    '<section class="notice notice-warning"><p>',
                    '</p></section>'
                ),
            ];

            return $fields;
        }

        $fields[] = [
            'title' => __( 'IMAP/POP3 Options', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Email incoming settings for ERP.', 'erp' ),
        ];

        $fields[] = [
            'title'   => __( 'Status', 'erp' ),
            'id'      => 'imap_status_label',
            'type'    => 'label',
            'default' => $this->imap_status( false )
        ];

        $fields[] = [
            'id'      => 'imap_status',
            'type'    => 'hidden',
            'default' => $this->imap_status( true )
        ];

        $fields[] = [
            'title'   => __( 'Enable IMAP', 'erp' ),
            'id'      => 'enable_imap',
            'type'    => 'radio',
            'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
            'default' => 'no',
        ];

        $fields[] = [
            'title'             => __( 'Mail Server', 'erp' ),
            'id'                => 'mail_server',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder' => 'imap.gmail.com',
            ],
            'desc'              => __( 'IMAP/POP3 host address.', 'erp' ),
        ];

        $fields[] = [
            'title'             => __( 'Username', 'erp' ),
            'id'                => 'username',
            'type'              => 'text',
            'desc'              => __( 'Your email id.', 'erp' ),
            'custom_attributes' => [
                'placeholder' => 'email@example.com',
            ],
        ];

        $fields[] = [
            'title' => __( 'Password', 'erp' ),
            'id'    => 'password',
            'type'  => 'password',
            'desc'  => __( 'Your email password.', 'erp' ),
        ];

        $fields[] = [
            'title'   => __( 'Protocol', 'erp' ),
            'id'      => 'protocol',
            'type'    => 'select',
            'desc'    => __( 'Protocol type.', 'erp' ),
            'options' => [ 'imap' => __( 'IMAP', 'erp' ), 'pop3' => __( 'POP3', 'erp' ) ],
            'default' => 'imap',
        ];

        $fields[] = [
            'title' => __( 'Port', 'erp' ),
            'id'    => 'port',
            'type'  => 'text',
            'desc'  => __( 'IMAP: 993<br> POP3: 995', 'erp' ),
        ];

        $fields[] = [
            'title'   => __( 'Authentication', 'erp' ),
            'id'      => 'authentication',
            'type'    => 'select',
            'options' => [ 'ssl' => __( 'SSL', 'erp' ), 'tls' => __( 'TLS', 'erp' ), 'notls' => __( 'None', 'erp' ) ],
            'default' => 'ssl',
            'desc'    => __( 'Authentication type.', 'erp' ),
        ];

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];

        return $fields;
    }

    /**
     * Imap connection status.
     *
     * @param string $is_label default false
     *
     * @return string|int imap_connection as input label
     */
    public function imap_status( $is_label = false ) {
        $options     = get_option( 'erp_settings_erp-crm_email_connect_imap', [] );
        $imap_status = (bool) isset( $options['imap_status'] ) ? $options['imap_status'] : 0;

        if ( $is_label ) {
            return $imap_status;
        } else {
            $status    = esc_attr( ( $imap_status ) ? 'yes green' : 'no red' );
            $connected = esc_attr( ( $imap_status ) ? __( 'Connected', 'erp' ) : __( 'Not Connected', 'erp' ) );

            return sprintf("<span class='dashicons dashicons-%s'>%s</span>", $status, $connected);
        }
    }

    /**
     * Get Email Providers List
     *
     * @since 1.9.0
     *
     * @return array $providers
     */
    public function get_email_prodivers() {
        $providers = [];

        $providers['gmail'] = [
            'name'         => __( 'Gmail Connect', 'erp' ),
            'description'  => __( 'Connect your Gmail or Gsuite account', 'erp' ),
            'enabled'      => wperp()->google_auth->is_active(),
            'actions'      => '',
            'fields'       => $this->get_sub_section_fields( 'gmail' )
        ];

        $providers['imap']  = [
            'name'         => __( 'IMAP Connection', 'erp' ),
            'description'  => __( 'Connect to Custom IMAP server', 'erp' ),
            'enabled'      => erp_is_imap_active(),
            'actions'      => '',
            'fields'       => $this->get_sub_section_fields( 'imap' )
        ];

        return $providers;
    }

    /**
     * Disable other provider if one is enabled
     *
     * @param $section
     * @param $options
     */
    public function toggle_providers( $section, $options ) {
        switch ( $section ) {
            case 'gmail':
                if ( wperp()->google_auth->is_active() ) {
                    $option                = get_option( 'erp_settings_erp-crm_email_connect_imap', [] );
                    $option['enable_imap'] = 'no';
                    update_option( 'erp_settings_erp-crm_email_connect_imap', $option );
                }
                break;

            case 'imap':
                if ( isset( $options['enable_imap'] ) && $options['enable_imap'] == 'yes' ) {
                    wperp()->google_auth->clear_account_data();
                }
                break;
            default:
                break;
        }
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

            $sub_section = isset( $_POST['sub_sub_section'] ) ? sanitize_key( wp_unslash( $_POST['sub_sub_section'] ) ) : false;

            // Saving individual email settings
            if ( $sub_section ) {
                $settings       = $this->get_sub_section_fields( $sub_section );
                $update_options = get_option(  $this->get_option_id( $sub_section ), [] );

                if ( $settings ) {
                    foreach ( $settings as $field ) {
                        if ( ! isset( $field['id'] ) || ! isset( $_POST[ $field['id'] ] ) ) {
                            continue;
                        }

                        $option_value = $this->parse_option_value( $field );

                        if ( ! is_null( $option_value ) ) {
                            $update_options[$field['id']] = $option_value;
                        }
                    }
                }
                update_option( $this->get_option_id( $sub_section ), $update_options );

                do_action( 'erp_settings_crm_updated_sub_section', $sub_section, $update_options );
            } else {
                parent::save();
            }
        }
    }

    /**
     * Set cron schedule event to check new inbound emails
     *
     * @return void
     */
    public function cron_schedule( $value ) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-nonce' ) ) {
            // die();
        }

        if ( !isset( $_GET['section'] ) || ( $_GET['section'] != 'email_connect' ) ) {
            return;
        }

        if ( !isset( $value['id'] ) || ( $value['id'] != 'schedule' ) ) {
            return;
        }

        $recurrence = isset( $_POST['schedule'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule'] ) ) : 'hourly';
        wp_clear_scheduled_hook( 'erp_crm_inbound_email_scheduled_events' );
        wp_schedule_event( time(), $recurrence, 'erp_crm_inbound_email_scheduled_events' );
    }
}
