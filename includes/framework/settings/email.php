<?php

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * Email settings class
 */
class ERP_Email_Settings extends ERP_Settings_Page {

    function __construct() {
        $this->id       = 'erp-email';
        $this->label    = __( 'Emails', 'erp' );
        $this->sections = $this->get_sections();

        add_action( 'erp_admin_field_notification_emails', [ $this, 'notification_emails' ] );
        add_action( 'erp_admin_field_smtp_test_connection', [ $this, 'smtp_test_connection' ] );
        add_action( 'erp_admin_field_imap_test_connection', [ $this, 'imap_test_connection' ] );
        add_action( 'erp_admin_field_imap_status', [ $this, 'imap_status' ] );

        add_action( 'erp_update_option', [ $this, 'cron_schedule' ] );
    }

    /**
     * Get registered tabs
     *
     * @return array
     */
    public function get_sections() {
        $sections = [
            'general' => __( 'General', 'erp' ),
            'smtp'    => __( 'SMTP', 'erp' ),
            'imap'    => __( 'IMAP/POP3', 'erp' ),
        ];

        return apply_filters( 'erp_settings_email_sections', $sections );
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
            'desc'  => __( 'Email notification settings for ERP. Customize the look and feel of outgoing emails.', 'erp' )
        ];

        $fields['general'][] = [
            'title'   => __( '"From" Name', 'erp' ),
            'id'      => 'from_name',
            'type'    => 'text',
            'default' => get_bloginfo( 'name' ),
            'tooltip' => true,
            'desc' => __( 'The senders name appears on the outgoing emails', 'erp' )
        ];

        $fields['general'][] = [
            'title'   => __( '"From" Address', 'erp' ),
            'id'      => 'from_email',
            'type'    => 'text',
            'default' => get_option( 'admin_email' ),
            'tooltip' => true,
            'desc'    => __( 'The senders email appears on the outgoing emails', 'erp' )
        ];

        $fields['general'][] = [
            'title'             => __( 'Header Image', 'erp' ),
            'id'                => 'header_image',
            'type'              => 'text',
            'desc'              => __( 'Upload a logo/banner and provide the URL here.', 'erp' ),
            'tooltip'           => true,
            'custom_attributes' => [
                'placeholder' => 'http://example.com/path/to/logo.png'
            ]
        ];

        $fields['general'][] = [
            'title'   => __( 'Footer Text', 'erp' ),
            'id'      => 'footer_text',
            'type'    => 'textarea',
            'css'     => 'min-width:300px;',
            'tooltip' => true,
            'default' => sprintf( '%s  - Powered by WP ERP', get_bloginfo( 'name' ) ),
            'desc'    => __( 'The text apears on each emails footer area.', 'erp' )
        ];

        $fields['general'][] = [
            'type' => 'sectionend', 'id' => 'script_styling_options'
        ];

        $fields['general'][] = [
            'title' => __( 'Notification Emails', 'erp' ),
            'desc'  => __( 'Email notifications sent from WP ERP are listed below. Click on an email to configure it.', 'erp' ),
            'type'  => 'title',
            'id'    => 'email_notification_settings'
        ];

        $fields['general'][] = [
            'type' => 'notification_emails'
        ];

        $fields['general'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];
        // End general settings

        $fields['smtp'][] = [
            'title' => __( 'SMTP Options', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Email outgoing settings for ERP.', 'erp' )
        ];

        $fields['smtp'][] = [
            'title'   => __( 'Enable SMTP', 'erp' ),
            'id'      => 'enable_smtp',
            'type'    => 'radio',
            'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
            'default' => 'no'
        ];

        $fields['smtp'][] = [
            'title'             => __( 'Mail Server', 'erp' ),
            'id'                => 'mail_server',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder'   => 'smtp.gmail.com'
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
            'options' => [ '' => __( 'None', 'erp'), 'ssl' => __( 'SSL', 'erp' ), 'tls' => __( 'TLS', 'erp') ],
        ];

        $fields['smtp'][] = [
            'title'             => __( 'Username', 'erp' ),
            'id'                => 'username',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder'   => 'email@example.com'
            ],
            'desc'              => __( 'Your email id.', 'erp' ),
        ];

        $fields['smtp'][] = [
            'title' => __( 'Password', 'erp' ),
            'id'    => 'password',
            'type'  => 'password',
            'desc'  => __( 'Your email password.', 'erp' )
        ];

        $fields['smtp'][] = [
            'title'   => __( 'Debug', 'erp' ),
            'id'      => 'debug',
            'type'    => 'radio',
            'options' => [ 'yes' => 'Yes', 'no' => 'No' ],
            'default' => 'no'
        ];

        $fields['smtp'][] = [
            'type' => 'smtp_test_connection',
        ];

        $fields['smtp'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];
        // End SMTP settings

        $fields['imap'] = $this->get_imap_settings_fields();
        // End IMAP settings

        $fields = apply_filters( 'erp_settings_email_section_fields', $fields, $section );

        $section = $section === false ? $fields['general'] : $fields[$section];

        return $section;
    }

    function notification_emails() {
        $email_templates = wperp()->emailer->get_emails();
        ?>
        <tr valign="top">
            <td class="erp-settings-table-wrapper" colspan="2">
                <table class="erp-settings-table widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <?php
                                $columns = apply_filters( 'erp_email_setting_columns', array(
                                    'name'        => __( 'Email', 'erp' ),
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
                        foreach ( $email_templates as $email_key => $email ) {
                            echo '<tr>';

                            foreach ( $columns as $key => $column ) {
                                switch ( $key ) {
                                    case 'name' :
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                            <a href="' . admin_url( 'admin.php?page=erp-settings&tab=erp-email&section=general&sub_section=' . strtolower( $email_key ) ) . '">' . $email->get_title() . '</a>
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
                                            <span class="help">' . $email->get_description() . '</span>
                                        </td>';
                                        break;

                                    case 'actions' :
                                        echo '<td class="erp-settings-table-' . esc_attr( $key ) . '">
                                            <a class="button alignright" href="' . admin_url( 'admin.php?page=erp-settings&tab=erp-email&section=general&sub_section=' . strtolower( $email_key ) ) . '">' . __( 'Configure', 'erp' ) . '</a>
                                        </td>';
                                        break;

                                    default :
                                        do_action( 'erp_email_setting_column_' . $key, $email );
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

    /**
     * Display imap test connection button.
     *
     * @return void
     */
    public function smtp_test_connection() {
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-text">
                <input type="email" id="smtp_test_email_address" class="regular-text" value="<?php echo get_option( 'admin_email' ); ?>" /><br>
                <p class="description"><?php _e( 'An email address to test the connection.', 'erp' ); ?></p>
                <a id="smtp-test-connection" class="button-secondary"><?php esc_attr_e( 'Send Test Email', 'erp' ); ?></a>
                <span class="erp-loader" style="display: none;"></span>
                <p class="description"><?php _e( 'Click on the above button before saving the settings.', 'erp' ); ?></p>
            </td>
        </tr>
        <?php
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
                <a id="imap-test-connection" class="button-secondary"><?php esc_attr_e( 'Test Connection', 'erp' ); ?></a>
                <span class="erp-loader" style="display: none;"></span>
                <p class="description"><?php _e( 'Click on the above button before saving the settings.', 'erp' ); ?></p>
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
        $options     = get_option( 'erp_settings_erp-email_imap', [] );
        $imap_status = (boolean) isset( $options['imap_status'] ) ? $options['imap_status'] : 0;
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php _e( 'Status', 'erp' ); ?>
            </th>
            <td class="forminp forminp-text">
                <span class="dashicons dashicons-<?php echo ( $imap_status ) ? 'yes green' : 'no red' ?>"></span><?php echo ( $imap_status ) ? __( 'Connected', 'erp' ) : __( 'Not Connected', 'erp' ); ?>
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

        $schedules = wp_get_schedules();

        $cron_schedules = [];
        foreach( $schedules as $key => $value ) {
            $cron_schedules[$key] = $value['display'];
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
                'placeholder'   => 'imap.gmail.com'
            ],
            'desc'              => __( 'IMAP/POP3 host address.', 'erp' ),
        ];

        $fields[] = [
            'title'             => __( 'Username', 'erp' ),
            'id'                => 'username',
            'type'              => 'text',
            'desc'              => __( 'Your email id.', 'erp' ),
            'custom_attributes' => [
                'placeholder'   => 'email@example.com'
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
            'options' => [ 'imap' => __( 'IMAP', 'erp' ), 'pop3' => __( 'POP3', 'erp') ],
            'default' =>  'imap',
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
            'options' => [ 'ssl' => __( 'SSL', 'erp' ), 'tls' => __( 'TLS', 'erp'), 'notls' => __( 'None', 'erp') ],
            'default' =>  'ssl',
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
     * Set cron schedule event to check new inbound emails
     *
     * @return void
     */
    public function cron_schedule( $value ) {
        if ( ! isset( $_GET['section'] ) || ( $_GET['section'] != 'imap' ) ) {
            return;
        }

        if ( ! isset( $value['id'] ) || ( $value['id'] != 'schedule' ) ) {
            return;
        }

        $recurrence = isset( $_POST['schedule'] ) ? $_POST['schedule'] : 'hourly';
        wp_clear_scheduled_hook( 'erp_crm_inbound_email_scheduled_events' );
        wp_schedule_event( time(), $recurrence, 'erp_crm_inbound_email_scheduled_events' );
    }

    /**
     * Output the settings.
     */
    public function output( $section = false ) {
        if ( ! isset( $_GET['sub_section'] ) ) {
            parent::output( $section );

            return;
        }

        $current_section = isset( $_GET['sub_section'] ) ? sanitize_key( $_GET['sub_section'] ) : false;

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

    function save( $section = false ) {
        if ( isset( $_POST['_wpnonce']) && wp_verify_nonce( $_POST['_wpnonce'], 'erp-settings-nonce' ) ) {

            if ( ! isset( $_GET['sub_section'] ) ) {
                parent::save( $section );

                return;
            }

            $current_section = isset( $_GET['sub_section'] ) ? sanitize_key( $_GET['sub_section'] ) : false;


            // saving individual email settings
            if ( $current_section ) {
                $email_templates = wperp()->emailer->get_emails();

                foreach ( $email_templates as $email_key => $email ) {
                    if ( strtolower( $email_key ) == $current_section ) {

                        $settings       = $email->get_form_fields();
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

                        update_option( $email->get_option_id(), $update_options );

                        break;
                    }
                }

            } else {
                parent::save();
            }
        }
    }
}

return new ERP_Email_Settings();
