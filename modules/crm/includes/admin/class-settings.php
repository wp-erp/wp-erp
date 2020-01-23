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
        add_action( 'erp_admin_field_render_email_providers', [ $this, 'render_email_providers' ] );

        add_action( 'erp_admin_field_imap_status', [ $this, 'imap_status' ] );
        add_action( 'erp_admin_field_imap_test_connection', [ $this, 'imap_test_connection' ] );

        add_action( 'erp_admin_field_gmail_api_settings', [ $this, 'gmail_api_settings' ] );
        add_action( 'erp_admin_field_gmail_redirect_url', [ $this, 'render_gmail_redirect_url' ] );
        add_action( 'erp_admin_field_gmail_api_connected', [ $this, 'render_gmail_api_connected' ] );

        add_action( 'erp_update_option', [ $this, 'cron_schedule' ] );

    }

    public function get_option_id() {
        $current_section = isset( $_GET['sub_section'] ) ? sanitize_key( $_GET['sub_section'] ) : false;
        if ( $current_section ) {
            return parent::get_option_id() .'_'. $current_section;
        }
        return parent::get_option_id();
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = array(
            'contacts'      => __( 'Contacts', 'erp' ),
            'templates'     => __( 'Templates', 'erp' ),
            'subscription'  => __( 'Subscription', 'erp' ),
            'email_connect' => __( 'Email Connectivity', 'erp' )
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

        $fields['email_connect'] = $this->get_email_connect_fields();

        $fields = apply_filters( 'erp_settings_crm_section_fields', $fields, $section );

        $section = $section === false ? $fields['contacts'] : $fields[$section];

        return $section;
    }

    public function get_email_connect_fields(){

        $schedules = wp_get_schedules();

        $cron_schedules = [];
        foreach ( $schedules as $key => $value ) {
            $cron_schedules[$key] = $value['display'];
        }

        $fields[] = [
            'title' => __( 'Email Connection Settings', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Settings for CRM Contact Emails Connectivity.', 'erp' ),
            'id'    => 'general_options'
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
            'type' => 'render_email_providers'
        ];

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

        return $fields;
    }

    /**
     * Get all fields for current Sub Section
     *
     * @since 1.3.14
     *
     * @return array fields
     */
    public function get_sub_section_fields() {
        $sub_section = isset( $_GET['sub_section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub_section'] ) ) : '';

        switch ( $sub_section ) {
            case 'gmail' :
                return $this->get_gmail_api_settings_fields();
            case 'imap' :
            default :
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
    function get_gmail_api_settings_fields() {
        $fields[] = [
            'title' => __( 'Gmail / G suite Authentication', 'erp' ),
            'type'  => 'title',
            'desc'  => __( '<a target="_blank" href="https://console.developers.google.com/flows/enableapi?apiid=gmail&pli=1">Create a Google App</a> and authorize your account to Send and Recieve emails using Gmail. Follow instructions from this <a target="_blank" href="https://wperp.com/docs/crm/tutorials/how-to-configure-gmail-api-connection-in-the-crm-settings/?utm_source=Free+Plugin&utm_medium=CTA&utm_content=Backend&utm_campaign=Docs">Documentation</a> to get started', 'erp' )
        ];

        if ( wperp()->google_auth->is_connected() ) {

            $fields[] = [
                'type' => 'gmail_api_connected',
            ];

            $fields[] = [
                'type' => 'sectionend',
                'id'   => 'script_styling_options'
            ];

            return $fields;
        }

        $fields[] = [
            'title' => __( 'Client ID', 'erp' ),
            'id'    => 'client_id',
            'type'  => 'text',
            'desc'  => __( 'Your APP Client ID', 'erp' )
        ];

        $fields[] = [
            'title' => __( 'Client Secret', 'erp' ),
            'id'    => 'client_secret',
            'type'  => 'text',
            'desc'  => __( 'Your APP Client Secret', 'erp' )
        ];

        $fields[] = [
            'type' => 'gmail_redirect_url',
        ];

        if ( wperp()->google_auth->has_credentials() ) {
            $fields[] = [
                'type' => 'gmail_api_settings',
            ];

            $fields[] = [
                'type' => 'sectionend',
                'id'   => 'script_styling_options'
            ];

            return $fields;
        }

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

        return $fields;
    }

    /**
     * Get IMAP Settings Fields.
     *
     * @return array
     */
    protected function get_imap_settings_fields() {
        if ( !extension_loaded( 'imap' ) || !function_exists( 'imap_open' ) ) {
            $fields[] = [
                'title' => __( 'IMAP/POP3 Options', 'erp' ),
                'type'  => 'title',
                'desc'  => sprintf(
                    '%s' . __( 'Your server does not have PHP IMAP extension loaded. To enable this feature, please contact your hosting provider and ask to enable PHP IMAP extension.', 'erp' ) . '%s',
                    '<section class="notice notice-warning"><p>',
                    '</p></section>'
                )
            ];

            return $fields;
        }

        $fields[] = [
            'title' => __( 'IMAP/POP3 Options', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Email incoming settings for ERP.', 'erp' )
        ];

        $fields[] = [
            'type' => 'imap_status',
        ];

        $fields[] = [
            'title'   => __( 'Enable IMAP', 'erp' ),
            'id'      => 'enable_imap',
            'type'    => 'radio',
            'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
            'default' => 'no'
        ];

        $fields[] = [
            'title'             => __( 'Mail Server', 'erp' ),
            'id'                => 'mail_server',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder' => 'imap.gmail.com'
            ],
            'desc'              => __( 'IMAP/POP3 host address.', 'erp' ),
        ];

        $fields[] = [
            'title'             => __( 'Username', 'erp' ),
            'id'                => 'username',
            'type'              => 'text',
            'desc'              => __( 'Your email id.', 'erp' ),
            'custom_attributes' => [
                'placeholder' => 'email@example.com'
            ]
        ];

        $fields[] = [
            'title' => __( 'Password', 'erp' ),
            'id'    => 'password',
            'type'  => 'password',
            'desc'  => __( 'Your email password.', 'erp' )
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
            'type' => 'imap_test_connection',
        ];

        $fields[] = [
            'id'      => 'imap_status',
            'type'    => 'hidden',
            'default' => 0,
        ];

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];

        return $fields;
    }

    /**
     * Display imap test connection button.
     *
     * @return void
     */
    public function imap_test_connection() {
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-text">
                <a id="imap-test-connection"
                   class="button-secondary"><?php esc_attr_e( 'Test Connection', 'erp' ); ?></a>
                <span class="erp-loader" style="display: none;"></span>
                <p class="description"><?php esc_attr_e( 'Click on the above button before saving the settings.', 'erp' ); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Imap connection status.
     *
     * @return void
     */
    public function imap_status() {
        $options = get_option( 'erp_settings_erp-crm_email_connect_imap', [] );
        $imap_status = (boolean)isset( $options['imap_status'] ) ? $options['imap_status'] : 0;
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php esc_attr_e( 'Status', 'erp' ); ?>
            </th>
            <td class="forminp forminp-text">
                <?php
                $status    = ( $imap_status ) ? 'yes green' : 'no red';
                $connected = ( $imap_status ) ? __( 'Connected', 'erp' ) : __( 'Not Connected', 'erp' );
                ?>
                <span class="dashicons dashicons-<?php echo esc_attr( $status ) ?>"></span><?php echo esc_attr( $connected ) ?>
            </td>
        </tr>
        <?php
    }

    function gmail_api_settings() {
        $url = wperp()->google_auth->get_client()->createAuthUrl();
        ?>
        <tr valign="top">
            <td class="forminp forminp-text">
                <a target="_blank" class="button-primary" href="<?php echo esc_url_raw( $url ) ?>"><?php esc_attr_e( 'Click to Authorize your gmail account', 'erp' ) ?> </a>
            </td>
        </tr>
        <?php
    }

    function render_gmail_redirect_url() {
        $url = wperp()->google_auth->get_redirect_url();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="redirect_url"><?php esc_attr_e( 'Redirect URL to use', 'erp' ); ?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="redirect_url" id="redirect_url" type="text" disabled value="<?php echo esc_url_raw( $url ) ?>"
                       class="regular-text">
                <p class="description"><?php esc_attr_e( 'Copy and Use this url when oAuth consent asks for Authorized Redirect URL', 'erp' ) ?></p>
            </td>
        </tr>

        <?php
    }

    function render_gmail_api_connected() {
        $connected_email = wperp()->google_auth->is_connected();
        $url = wperp()->google_auth->get_disconnect_url();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php esc_attr_e( 'Connected', 'erp' ); ?>
            </th>
            <td class="forminp forminp-text">
                <p><b><?php echo wp_kses_post( $connected_email ) ?></b></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
            </th>
            <td class="forminp forminp-text">
                <a style="background: #dc3232; color:#fff" class="button-secondary" href="<?php echo esc_url_raw( $url ) ?>"> <?php esc_attr_e( 'Disconnect','erp') ?> </a>
            </td>
        </tr>
        <?php
    }

    public function render_email_providers(){
        $providers = [];

        $providers['gmail'] = [
            'name'         => __('Gmail Connect', 'erp'),
            'description'  => __('Connect your Gmail or Gsuite account', 'erp'),
            'enabled'      => wperp()->google_auth->is_active(),
            'actions'      => '',
        ];

        $providers['imap']  = [
            'name'         => __('IMAP Connection', 'erp'),
            'description'  => __('Connect to Custom IMAP server', 'erp'),
            'enabled'      => erp_is_imap_active(),
            'actions'      => '',
        ];

        $settings_url = admin_url( 'admin.php?page=erp-settings&tab=erp-crm&section=email_connect&sub_section=');

        ?>
        <tr valign="top">
            <td class="erp-settings-table-wrapper" colspan="2">
                <table class="erp-settings-table widefat" cellspacing="0">
                    <thead>
                    <tr>
                        <?php
                        $columns = array(
                            'name'        => __( 'Provider', 'erp' ),
                            'description' => __( 'Description', 'erp' ),
                            'status'      => __( 'Status', 'erp' ),
                            'actions'     => ''
                        );

                        foreach ( $columns as $key => $item ) {
                            echo '<th class="erp-settings-table-' . esc_attr( $key ) . '">' . esc_html( $item ) . '</th>';
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ( $providers as $slug => $provider ) {
                        echo '<tr>';

                        foreach ( $provider as $key => $item ) {
                            switch ( $key ) {
                                case 'name' :
                                    echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                        <a href="' . esc_url_raw( $settings_url ) . esc_attr( strtolower( $slug ) ) . '">' . esc_attr( $item ) . '</a>
                                    </td>';
                                    break;

                                case 'description':
                                    echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                        <span class="help">' . esc_attr( $item ) . '</span>
                                    </td>';
                                    break;

                                case 'enabled' :
                                    $status = __( 'Disabled', 'erp' );
                                    $btn_class = 'email-status';
                                    if ( $item ) {
                                        $status = __( 'Enabled', 'erp' );
                                        $btn_class .= ' enabled';
                                    }
                                    echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                        <span class="help">' . esc_attr( $status ) . '</span>
                                    </td>';
                                    break;

                                case 'actions' :
                                    echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                        <a class="button alignright" href="' . esc_url_raw( $settings_url ) . esc_attr( strtolower( $slug ) ) . '">' . esc_html__( 'Settings', 'erp' ) . '</a>
                                    </td>';
                                    break;

                                default :

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
    <a href="#" class="erp-crm-add-save-replies button alignright" id="erp-crm-add-save-replies" title="<?php esc_attr_e( 'Add new Template', 'erp' ); ?>"><?php esc_attr_e( 'Add Templates', 'erp' ); ?></a>
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
                                            <a href="#">' . esc_attr( $save_reply->name ) . '</a>
                                        </td>';
                                        break;

                                    case 'subject':
                                        $subject = ( isset( $save_reply->subject ) && ! empty( $save_reply->subject ) ) ? esc_attr( $save_reply->subject ) : '----';
                                        echo '<td class="erp-templates-settings-table-' . esc_attr( $key ) . '">
                                            <span class="help">' . esc_attr( $subject ) . '</span>
                                        </td>';
                                        break;

                                    case 'actions' :
                                        echo '<td class="erp-templates-settings-table-' . esc_attr( $key ) . '">
                                            <a class="erp-crm-save-replies-edit erp-tips" title="'. esc_html__( 'Edit', 'erp' ) .'" href="#" data-id="' . esc_attr( $save_reply->id ) . '"><i class="fa fa-pencil-square-o"></i></a>
                                            <a class="erp-crm-delete-save-replies erp-tips" title="'. esc_html__( 'Delete', 'erp' ) .'" href="#" data-id="' . esc_attr( $save_reply->id ) . '"><i class="fa fa-trash-o"></i></a>
                                        </td>';
                                        break;

                                    default :
                                        if ( empty( $email ) ) {
                                            // why?
                                            $email = '';
                                        }

                                        do_action( 'erp_templates_setting_column_' . $key, $email );
                                    break;
                                }
                            }
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="3">' . esc_html__( 'No templates found', 'erp' ) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php
    }

    /**
     * Disable other provider if one is enabled
     *
     * @param $section
     *
     * @param $options
     */
    public function toggle_providers( $section, $options ) {
        switch ( $section ) {
            case 'gmail' :
                if ( wperp()->google_auth->is_active() ) {
                    $option = get_option( 'erp_settings_erp-crm_email_connect_imap', [] );
                    $option['enable_imap'] = 'no';
                    update_option( 'erp_settings_erp-crm_email_connect_imap', $option );
                }
                break;

            case 'imap' :
                if ( isset( $options['enable_imap'] ) && $options['enable_imap'] == 'yes' ) {
                    wperp()->google_auth->clear_account_data();
                }
                break;
            default:
                break;
        }
    }

    /**
     * Override Output of settings fields for sub sections.
     *
     * @since 1.3.14
     */
    public function output( $section = false ) {
        if ( !isset( $_GET['sub_section'] ) ) {
            parent::output( $section );
            return;
        }
        $current_section = isset( $_GET['sub_section'] ) ? sanitize_key( $_GET['sub_section'] ) : false;

        if ( $current_section ) {
            $this->render_sub_section( $this->get_sub_section_fields() );
        } else {
            parent::output();
        }
    }

    /**
     * Render fields for sub sections
     *
     * @since 1.3.14
     *
     * @param $fields
     */
    function render_sub_section( $fields ) {
        ?>
        <table class="form-table">
            <?php $this->output_fields( $fields ); ?>
        </table>
        <?php
    }

    /**
     * Override parent save to save sub section fields
     *
     * @since 1.3.14
     *
     * @param bool $section
     */
    function save( $section = false ) {
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'erp-settings-nonce' ) ) {

            if ( !isset( $_GET['sub_section'] ) ) {
                parent::save( $section );
                return;
            }

            $current_section = isset( $_GET['sub_section'] ) ? sanitize_key( wp_unslash( $_GET['sub_section'] ) ) : false;
            // saving individual email settings
            if ( $current_section ) {

                $settings = $this->get_sub_section_fields();
                $update_options = get_option(  $this->get_option_id(), [] );
                if ( $settings ) {
                    foreach ( $settings as $field ) {
                        if ( !isset( $field['id'] ) || !isset( $_POST[$field['id']] ) ) {
                            continue;
                        }

                        $option_value = $this->parse_option_value( $field );
                        if ( !is_null( $option_value ) ) {
                            $update_options[$field['id']] = $option_value;
                        }
                    }
                }
                update_option( $this->get_option_id(), $update_options );

                do_action('erp_settings_crm_updated_sub_section', $current_section, $update_options );
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

        $recurrence = isset( $_POST['schedule'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule'] ) ): 'hourly';
        wp_clear_scheduled_hook( 'erp_crm_inbound_email_scheduled_events' );
        wp_schedule_event( time(), $recurrence, 'erp_crm_inbound_email_scheduled_events' );
    }
}
