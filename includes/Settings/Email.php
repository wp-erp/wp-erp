<?php

namespace WeDevs\ERP\Settings;

/**
 * Email settings class
 */
class Email extends Template {

    /**
     * Class constructor
     */
    public function __construct() {
        $this->id            = 'erp-email';
        $this->label         = __( 'Emails', 'erp' );
        $this->sections      = $this->get_sections();
        $this->icon          = WPERP_ASSETS . '/images/wperp-settings/email.png';
        $this->single_option = false;

        add_action( 'erp_admin_field_notification_emails', [ $this, 'notification_emails' ] );
        add_action( 'erp_admin_field_smtp_test_connection', [ $this, 'smtp_test_connection' ] );
        add_action( 'admin_footer', 'erp_email_settings_javascript' );
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        return apply_filters( 'erp_settings_email_sections', [
            'general'       => __( 'General', 'erp' ),
            'email_connect' => __( 'Email Connect', 'erp' ),
            'notification'  => __( 'Notifications & Templates', 'erp' )
        ] );
    }

    /**
     * Get sections fields
     *
     * @return array
     */
    public function get_section_fields( $section = '' ) {
        $fields['general'][] = [
            'title' => __( 'Email Sender Options', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Email notification settings for ERP. Customize the look and feel of outgoing emails.', 'erp' ),
        ];

        $fields['general'][] = [
            'title'   => __( 'Sender Name', 'erp' ),
            'id'      => 'from_name',
            'type'    => 'text',
            'default' => get_bloginfo( 'name' ),
            'tooltip' => true,
            'desc'    => __( 'The senders name appears on the outgoing emails', 'erp' ),
        ];

        $fields['general'][] = [
            'title'   => __( 'Sender Address', 'erp' ),
            'id'      => 'from_email',
            'type'    => 'text',
            'default' => get_option( 'admin_email' ),
            'tooltip' => true,
            'desc'    => __( 'The senders email appears on the outgoing emails', 'erp' ),
        ];

        $fields['general'][] = [
            'title'             => __( 'Header Image', 'erp' ),
            'id'                => 'header_image',
            'type'              => 'text',
            'desc'              => __( 'Upload a logo/banner and provide the URL here.', 'erp' ),
            'tooltip'           => true,
            'custom_attributes' => [
                'placeholder' => 'http://example.com/path/to/logo.png',
            ],
        ];

        $fields['general'][] = [
            'title'   => __( 'Footer Text', 'erp' ),
            'id'      => 'footer_text',
            'type'    => 'textarea',
            'css'     => 'min-width:300px;',
            'tooltip' => true,
            'default' => sprintf( '%s  - Powered by WP ERP', get_bloginfo( 'name' ) ),
            'desc'    => __( 'The text apears on each emails footer area.', 'erp' ),
        ];

        $fields['general'][] = [
            'type' => 'sectionend', 'id' => 'script_styling_options',
        ];

        if ( ! empty( wperp()->emailer->get_emails() ) ) {
            $fields['general'][] = [
                'title' => __( 'Notification Emails', 'erp' ),
                'desc'  => __( 'Email notifications sent from WP ERP are listed below. Click on an email to configure it.', 'erp' ),
                'type'  => 'title',
                'id'    => 'email_notification_settings',
            ];

            $fields['general'][] = [
                'desc'  => '<ul class="email_tab_view"><li id="bt_hrm" class="bt_active">' . __( 'HRM', 'erp' ) . '</li><li id="bt_crm">' . __( 'CRM', 'erp' ) . '</li><li id="bt_accounting">' . __( 'Accounting', 'erp' ) . '</li><li id="bt_others">' . __( 'Others', 'erp' ) . '</li></ul>',
                'type'  => 'title',
                'id'    => 'email_notification_tab',
            ];

            $fields['general'][] = [
                'type' => 'notification_emails',
            ];
        }

        $fields['general'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];
        // End general settings

        // Email Connect Settings

        $fields['email_connect'][] = [
            'title' => __( 'Email Connect', 'erp' ),
            'type'  => 'title',
            'desc'  => '',
        ];

        $fields['email_connect']['providers'] = $this->get_email_prodivers();
        $fields['email_connect']['cron_schedules'] = $this->get_incoming_email_schedule_field();
        // End Email Connect Settings

        $fields['smtp'][] = [
            'title'        => __( 'SMTP Options', 'erp' ),
            'type'         => 'title',
            'desc'         => __( 'Email outgoing settings for ERP.', 'erp' )
        ];

        $fields['smtp'][] = [
            'title'   => __( 'Enable SMTP', 'erp' ),
            'id'      => 'enable_smtp',
            'type'    => 'radio',
            'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
            'default' => 'no',
        ];

        $fields['smtp'][] = [
            'title'             => __( 'Mail Server', 'erp' ),
            'id'                => 'mail_server',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder' => 'smtp.gmail.com',
            ],
            'desc'              => __( 'SMTP host address.', 'erp' ),
        ];

        $fields['smtp'][] = [
            'title' => __( 'Port', 'erp' ),
            'id'    => 'port',
            'type'  => 'text',
            'desc'  => __( 'SSL: 465<br> TLS: 587', 'erp' ),
        ];

        $fields['smtp'][] = [
            'title'   => __( 'Authentication', 'erp' ),
            'id'      => 'authentication',
            'type'    => 'select',
            'desc'    => __( 'Authentication type.', 'erp' ),
            'options' => [ '' => __( 'None', 'erp' ), 'ssl' => __( 'SSL', 'erp' ), 'tls' => __( 'TLS', 'erp' ) ],
        ];

        $fields['smtp'][] = [
            'title'             => __( 'Username', 'erp' ),
            'id'                => 'username',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder' => 'email@example.com',
            ],
            'desc'              => __( 'Your email id.', 'erp' ),
        ];

        $fields['smtp'][] = [
            'title' => __( 'Password', 'erp' ),
            'id'    => 'password',
            'type'  => 'password',
            'desc'  => __( 'Your email password.', 'erp' ),
        ];

        $fields['smtp'][] = [
            'title'   => __( 'Enable Debugging', 'erp' ),
            'id'      => 'debug',
            'type'    => 'radio',
            'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
            'default' => 'no',
        ];

        $fields['smtp'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];
        // End SMTP settings

        // Mailgun Email Settings Options
        $fields['mailgun']  = $this->get_mailgun_settings_fields();

        // IMAP Email Settings Options
        $fields['imap']  = $this->get_imap_settings_fields();

        // Gmail Email Settings Options
        $fields['gmail'] = $this->get_gmail_api_settings_fields();

        // Email Templates
        $fields['templates'][] = [
            'title' => __( 'Saved Replies', 'erp' ),
            'type'  => 'title',
            'desc'  => '',
            'id'    => 'general_options',
        ];
        // End of Email Templates

        // Notification & Templates settings
        $fields['notification'][] = [
            'title' => __( 'Notification & Template Settings', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Email notifications and templates management for ERP', 'erp' ),
        ];

        $fields['notification']['sub_sections'] = [
            'hrm'  => __( 'HRM', 'erp' ),
            'crm'  => __( 'CRM', 'erp' ),
            'acct' => __( 'Accounting', 'erp' ),
        ];
        // End of Notification & Templates settings

        $fields = apply_filters( 'erp_settings_email_section_fields', $fields, $section );

        return $fields;
    }

    /**
     * Get Incoming Email schedule field
     *
     * @since 1.10.0
     *
     * @return array Schedule input fields
     */
    public function get_incoming_email_schedule_field() {
        $schedules = wp_get_schedules();

        $cron_intervals = []; // Filter cron intervals time to get unique cron data
        $cron_schedules = [];

        foreach ( $schedules as $key => $value ) {
            if ( ! in_array( $value['interval'], $cron_intervals ) ) {
                array_push( $cron_intervals, $value['interval'] );
                $cron_schedules[ $key ] = $value['display'];
            }
        }

        return [
            'title'   => __( 'Check Emails ', 'erp' ),
            'id'      => 'schedule',
            'type'    => 'select',
            'desc'    => __( 'Interval time to run cron for checking inbound emails.', 'erp' ),
            'options' => $cron_schedules,
            'default' => 'hourly',
        ];
    }

    /**
     * Get Email Providers of incoming and outgoing emails
     *
     * @since 1.10.0
     *
     * @return array email providers list
     */
    public function get_email_prodivers() {
        $providers = [];

        $erp_is_enable_smtp    = erp_is_smtp_enabled();
        $erp_is_enable_mailgun = erp_is_mailgun_enabled();
        $erp_is_enable_imap    = erp_is_imap_active();
//        $erp_is_enable_gmail   = wperp()->google_auth->is_active();

        $providers['smtp'] = [
            'type'         => 'outgoing',
            'name'         => __( 'SMTP', 'erp' ),
            'description'  => __( 'Email outgoing settings for ERP.', 'erp' ),
            'enabled'      => $erp_is_enable_smtp,
            'is_active'    => $erp_is_enable_smtp,
            'actions'      => '',
            'icon_enable'  => WPERP_ASSETS . '/images/wperp-settings/email-smtp-enable.png',
            'icon_disable' => WPERP_ASSETS . '/images/wperp-settings/email-smtp-disable.png',
        ];

        $providers['mailgun'] = [
            'type'         => 'outgoing',
            'name'         => __( 'Mailgun', 'erp' ),
            'description'  => '',
            'enabled'      => $erp_is_enable_mailgun,
            'is_active'    => $erp_is_enable_mailgun,
            'actions'      => '',
            'icon_enable'  => WPERP_ASSETS . '/images/wperp-settings/email-mailgun-enable.png',
            'icon_disable' => WPERP_ASSETS . '/images/wperp-settings/email-mailgun-disable.png',
        ];

        $providers['imap']  = [
            'type'         => 'incoming',
            'name'         => __( 'IMAP Connection', 'erp' ),
            'description'  => __( 'Connect to Custom IMAP server', 'erp' ),
            'enabled'      => $erp_is_enable_imap,
            'is_active'    => $erp_is_enable_imap,
            'actions'      => '',
            'icon_enable'  => WPERP_ASSETS . '/images/wperp-settings/email-imap-enable.png',
            'icon_disable' => WPERP_ASSETS . '/images/wperp-settings/email-imap-disable.png',
        ];

//        $providers['gmail'] = [
//            'type'         => 'incoming',
//            'name'         => __( 'Google Connect', 'erp' ),
//            'description'  => __( 'Connect your Gmail or Gsuite account', 'erp' ),
//            'enabled'      => $erp_is_enable_gmail,
//            'is_active'    => $erp_is_enable_gmail,
//            'actions'      => '',
//            'icon_enable'  => WPERP_ASSETS . '/images/wperp-settings/email-google-enable.png',
//            'icon_disable' => WPERP_ASSETS . '/images/wperp-settings/email-google-disable.png',
//        ];

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
                    $option                = get_option( 'erp_settings_erp-email_imap', [] );
                    $option['enable_imap'] = 'no';
                    update_option( 'erp_settings_erp-email_imap', $option );
                }
                break;

            case 'imap':
                if ( isset( $options['enable_imap'] ) && $options['enable_imap'] == 'yes' ) {
                    wperp()->google_auth->clear_account_data();
                }
                break;

            case 'smtp':
                if ( isset( $options['enable_smtp'] ) && $options['enable_smtp'] == 'yes' ) {
                    $option                   = get_option( 'erp_settings_erp-email_mailgun', [] );
                    $option['enable_mailgun'] = 'no';
                    update_option( 'erp_settings_erp-email_mailgun', $option );
                }
                break;

            case 'mailgun':
                if ( isset( $options['enable_mailgun'] ) && $options['enable_mailgun'] == 'yes' ) {
                    $option                = get_option( 'erp_settings_erp-email_smtp', [] );
                    $option['enable_smtp'] = 'no';
                    update_option( 'erp_settings_erp-email_smtp', $option );
                }
                break;

            default:
                break;
        }
    }

    /**
     * Imap connection status.
     *
     * @param string $is_label default false
     *
     * @return string|int imap_connection as input label
     */
    public function imap_status( $is_label = false ) {
        $options     = get_option( 'erp_settings_erp-email_imap', [] );
        $imap_status = (bool) isset( $options['imap_status'] ) ? $options['imap_status'] : 0;

        if ( $is_label ) {
            return $imap_status;
        } else {
            $status    = esc_attr( ( $imap_status ) ? 'yes green' : 'no red' );
            $connected = esc_attr( ( $imap_status ) ? __( 'Connected', 'erp' ) : __( 'Not Connected', 'erp' ) );

            return sprintf( "<span class='dashicons dashicons-%s'>%s</span>", esc_attr( $status ), esc_html( $connected ) );
        }
    }

    public function notification_emails() {
        $email_templates = wperp()->emailer->get_emails();
        $columns         = apply_filters( 'erp_email_setting_columns', [
            'name'        => __( 'Email', 'erp' ),
            'description' => __( 'Description', 'erp' ),
            'actions'     => '',
        ] );
        ?>
        <tr valign="top">
            <td class="erp-settings-table-wrapper" colspan="2">
                <table class="erp-settings-table widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <?php foreach ( $columns as $key => $column ) : ?>
                                <th class="erp-settings-table-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody id="email_list_view">
                    <?php
                    foreach ( $email_templates as $email_key => $email ) :
                        if (
                            false !== strpos( get_class( $email ), 'HRM' )  ||
                            false !== strpos( get_class( $email ), 'ERP_Document' ) ||
                            false !== strpos( get_class( $email ), 'ERP_Recruitment' ) ||
                            false !== strpos( get_class( $email ), 'Training' )
                        ) :
                            $tr_class = 'hrm';
                        elseif ( false !== strpos( get_class( $email ), 'CRM' ) ) :
                            $tr_class = 'crm';
                        elseif ( false !== strpos( get_class( $email ), 'Accounting' ) ) :
                            $tr_class = 'accounting';
                        else :
                            $tr_class = 'others';
                        endif;
                        ?>
                        <tr class="tag_<?php echo esc_attr( $tr_class ); ?>">
                            <?php
                            foreach ( $columns as $key => $column ) :
                                switch ( $key ) :
                                    case 'name':
                                        ?>
                                        <td class="erp-settings-table-<?php echo esc_attr( $key ); ?>">
                                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-settings&tab=erp-email&section=general&sub_section=' . esc_attr( strtolower( $email_key ) ) ) ); ?>">
                                                <?php echo esc_html( $email->get_title() ); ?>
                                            </a>
                                        </td>
                                        <?php
                                        break;

                                    case 'status':
                                    case 'module':
                                    case 'recipient':
                                        ?><td class="erp-settings-table-<?php echo esc_attr( $key ); ?>"></td><?php
                                        break;

                                    case 'description':
                                        ?>
                                        <td class="erp-settings-table-<?php echo esc_attr( $key ); ?>">
                                            <span class="help"><?php echo esc_html( $email->get_description() ); ?></span>
                                        </td>
                                        <?php
                                        break;

                                    case 'actions':
                                        ?>
                                        <td class="erp-settings-table-<?php echo esc_attr( $key ); ?>">
                                            <a class="button alignright" href="<?php echo esc_url( admin_url( 'admin.php?page=erp-settings&tab=erp-email&section=general&sub_section=' . esc_attr( strtolower( $email_key ) ) ) ); ?>">
                                                <?php esc_html_e( 'Configure', 'erp' ); ?>
                                            </a>
                                        </td>
                                        <?php
                                        break;

                                    default:
                                        do_action( 'erp_email_setting_column_' . $key, $email );
                                        break;
                                endswitch;
                            endforeach;
                        ?>
                        </tr>
                        <?php
                    endforeach;
                    ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
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
                'desc'  => ''
            ];

            $fields[] = [
                'title' => '',
                'id'    => 'label_imap',
                'type'  => 'label',
                'desc'  => sprintf(
                    /* translators: 1) opening tags of <section> and <p>, 2) closing tags of </p> and </section> */
                    __( '%1$sYour server does not have PHP IMAP extension loaded. To enable this feature, please contact your hosting provider and ask to enable PHP IMAP extension. %2$s', 'erp' ),
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
            'type' => 'imap_status',
        ];

        $fields[] = [
            'title'   => __( 'Enable IMAP', 'erp' ),
            'id'      => 'enable_imap',
            'type'    => 'radio',
            'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
            'default' => 'no',
        ];

        $schedules = wp_get_schedules();

        $cron_schedules = [];

        foreach ( $schedules as $key => $value ) {
            $cron_schedules[ $key ] = $value['display'];
        }

        $fields[] = [
            'title'   => __( 'Cron Schedule', 'erp' ),
            'id'      => 'schedule',
            'type'    => 'select',
            'desc'    => __( 'Interval time to run cron.', 'erp' ),
            'options' => $cron_schedules,
            'default' => 'hourly',
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
            'type' => 'imap_test_connection',
        ];

        $fields[] = [
            'id'      => 'imap_status',
            'type'    => 'hidden',
            'default' => 0,
        ];

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];

        return $fields;
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
            'desc'  => '',
        ];

//        if ( wperp()->google_auth->is_connected() ) {
//            $fields[] = [
//                'type' => 'gmail_api_connected',
//            ];
//
//            $fields[] = [
//                'type' => 'sectionend',
//                'id'   => 'script_styling_options',
//            ];
//
//            return $fields;
//        }

        $fields[] = [
            'title' => '',
            'id'    => 'label_gmail',
            'type'  => 'label',
            'desc'  => sprintf(
                /* translators: 1) opening anchor tag with google developers link, 2) closing anchor tag, 3) opening anchor tag with doc link, 4) closing anchor tag */
                __( '%1$sCreate a Google App%2$s and authorize your account to Send and Recieve emails using Gmail. Follow instructions from this %3$sDocumentation%4$s to get started', 'erp' ),
                '<a target="_blank" href="https://console.developers.google.com/flows/enableapi?apiid=gmail&pli=1">',
                '</a>',
                '<a target="_blank" href="https://wperp.com/docs/crm/tutorials/how-to-configure-gmail-api-connection-in-the-crm-settings/?utm_source=Free+Plugin&utm_medium=CTA&utm_content=Backend&utm_campaign=Docs">',
                '</a>'
            ),
        ];

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

//        $fields[] = [
//            'title'    => __( 'Redirect URL to use', 'erp' ),
//            'id'       => 'redirect_url',
//            'type'     => 'text',
//            'desc'     => __( 'Copy and Use this url when oAuth consent asks for Authorized Redirect URL', 'erp' ),
//            'default'  => esc_url_raw( wperp()->google_auth->get_redirect_url() ),
//            'disabled' => true
//        ];

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];

        return $fields;
    }

    /**
     * Get all fields for Mailgun API sub section
     *
     * @since 1.10.0
     *
     * @return array
     */
    public function get_mailgun_settings_fields() {
        $fields[] = [
            'title' => __( 'Mailgun', 'erp' ),
            'type'  => 'title',
            'desc'  => ''
        ];

        $fields[] = [
            'title'   => __( 'Enable Mailgun', 'erp' ),
            'id'      => 'enable_mailgun',
            'type'    => 'radio',
            'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
            'default' => 'no'
        ];

        $fields[] = [
            'title' => __( 'Private API Key', 'erp' ),
            'id'    => 'private_api_key',
            'type'  => 'password',
            'desc'  => __( 'Get private API key from your Mailgun account <a href="https://app.mailgun.com/app/account/security/api_keys" target="_blank">Mailgun account</a>', 'erp' ),
        ];

        $fields[] = [
            'title' => __( 'Domain', 'erp' ),
            'id'    => 'domain',
            'type'  => 'text',
            'desc'  => __( 'Get sending domain from your Mailgun account <a href="https://app.mailgun.com/app/sending/domains" target="_blank">Mailgun account</a><br /><mark>Notice:</mark> In Sandbox domain with Free plan, only 5 Authorized Recipients are allowed. <a href="https://help.mailgun.com/hc/en-us/articles/217531258-Authorized-Recipients" target="_blank">Learn More</a>', 'erp' ),
        ];

        $fields[] = [
            'title'    => __( 'Region', 'erp' ),
            'id'       => 'region',
            'type'     => 'select',
            'desc'     => __( 'Mailgun API Region', 'erp' ),
            'options'  => [
                'api.mailgun.net'    => __( 'United States (US)', 'erp' ),
                'api.eu.mailgun.net' => __( 'Europe (EU)', 'erp' )
            ],
            'default'  => 'api.mailgun.net'
        ];

        $fields[] = [
            'title'   => __( 'Limit', 'erp' ),
            'id'      => 'limit',
            'type'    => 'text',
            'desc'    => __( 'Hourly sending limit, That&apos;s 1 email per 1 second(s)', 'erp' ),
            'default' => 3600
        ];

        $fields[] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options',
        ];

        return $fields;
    }

    /**
     * Output the settings.
     */
    public function output( $section = false ) {
        if ( !isset( $_GET['sub_section'] ) ) {
            parent::output( $section );

            return;
        }

        $current_section = isset( $_GET['sub_section'] ) ? sanitize_text_field( wp_unslash( $_GET['sub_section'] ) ) : false;

        // Define emails that can be customised here
        $email_templates = wperp()->emailer->get_emails();

        if ( $current_section ) {
            foreach ( $email_templates as $email_key => $email ) {
                if ( strtolower( $email_key ) == $current_section ) {
                    $email->admin_options();
                    break;
                }
            }
        } else {
            parent::output();
        }
    }

    /**
     * Saves settings.
     *
     * @param $section (Optional)
     *
     * @return void
     */
    public function save( $section = false ) {
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
            $from_sections = false;

            if ( isset( $this->sections ) && is_array( $this->sections ) && count( $this->sections ) ) {
                $options       = $this->get_section_fields( $section );
                $options       = $options[ $section ];
                $from_sections = true;
            } else {
                $options = $this->get_settings();
            }

            // Modify options data for some sub sections
            $sub_section = isset( $_POST['sub_section' ] ) ? sanitize_text_field( wp_unslash( $_POST['sub_section' ] ) ) : null;

            if ( ! empty ( $sub_section ) ) {
                $options = $options[ $sub_section ];
            }

            // Options to update will be stored here
            $update_options = [];

            // Loop options and get values to save
            foreach ( $options as $value ) {
                if ( ! isset( $value['id'] ) ) {
                    continue;
                }

                $option_value = $this->parse_option_value( $value );

                if ( ! is_null( $option_value ) ) {
                    // Check if option is an array
                    if ( strstr( $value['id'], '[' ) ) {
                        parse_str( $value['id'], $option_array );

                        // Option name is first key
                        $option_name = current( array_keys( $option_array ) );

                        // Get old option value
                        if ( ! isset( $update_options[ $option_name ] ) ) {
                            $update_options[ $option_name ] = get_option( $option_name, [] );
                        }

                        if ( ! is_array( $update_options[ $option_name ] ) ) {
                            $update_options[ $option_name ] = [];
                        }

                        // Set keys and value
                        $key = key( $option_array[ $option_name ] );

                        $update_options[ $option_name ][ $key ] = $option_value;

                    // Single value
                    } else {
                        $update_options[ $value['id'] ] = $option_value;
                    }
                }

                // Custom handling
                do_action( 'erp_update_option', $value );
            }

            // finally, update the option
            if ( $update_options ) {
                if ( $this->single_option ) {
                    foreach ( $update_options as $name => $value ) {
                        update_option( $name, $value );
                    }
                } else {
                    $section   = sanitize_text_field( wp_unslash( $_POST['section'] ) );
                    $option_id = 'erp_settings_' . $this->id . '_' . $section;

                    // If it's incoming/outgoing email, then toggle email providers
                    $this->toggle_providers( $section, map_deep( wp_unslash( $_POST ), 'sanitize_text_field' ) );

                    if ( 'imap' === $section ) {
                        $imap_settings = get_option( 'erp_settings_erp-email_imap', [] );
                        $update_options['imap_status'] = ! empty( $imap_settings['imap_status'] ) ? intval( $imap_settings['imap_status'] ) : 0;
                    }

                    update_option( $option_id, $update_options );
                }
            }

            do_action( 'erp_after_save_settings' );
        }
    }
}
