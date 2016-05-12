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
            'title'             => __( 'Mail Server', 'erp' ),
            'id'                => 'mail_server',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder'   => 'smtp.gmail.com'
            ],
            'desc'              => __( 'SMTP host address.', 'erp' ),
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
            'title'             => __( 'Port', 'erp' ),
            'id'                => 'port',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder'   => 465
            ],
            'desc'              => __( 'SMTP port.<br> SSL: 465<br> TLS: 587', 'erp' ),
        ];

        $fields['smtp'][] = [
            'title'   => __( 'Encryption', 'erp' ),
            'id'      => 'encryption',
            'type'    => 'select',
            'desc'    => __( 'Encryption type.', 'erp' ),
            'options' => [ '' => __( 'None', 'erp'), 'ssl' => __( 'SSL', 'erp' ), 'tls' => __( 'TLS', 'erp') ],
        ];

        $fields['smtp'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];
        // End SMTP settings

        $fields['imap'][] = [
            'title' => __( 'IMAP/POP3 Options', 'erp' ),
            'type'  => 'title',
            'desc'  => __( 'Email incoming settings for ERP.', 'erp' )
        ];

        $fields['imap'][] = [
            'title'             => __( 'Mail Server', 'erp' ),
            'id'                => 'mail_server',
            'type'              => 'text',
            'custom_attributes' => [
                'placeholder'   => 'imap.gmail.com'
            ],
            'desc'              => __( 'IMAP/POP3 host address.', 'erp' ),
        ];

        $fields['imap'][] = [
            'title'             => __( 'Username', 'erp' ),
            'id'                => 'username',
            'type'              => 'text',
            'desc'              => __( 'Your email id.', 'erp' ),
            'custom_attributes' => [
                'placeholder'   => 'email@example.com'
            ]
        ];

        $fields['imap'][] = [
            'title' => __( 'Password', 'erp' ),
            'id'    => 'password',
            'type'  => 'password',
            'desc'  => __( 'Your email password.', 'erp' )
        ];

        $fields['imap'][] = [
            'title'   => __( 'Protocol', 'erp' ),
            'id'      => 'protocol',
            'type'    => 'select',
            'desc'    => __( 'Protocol type.', 'erp' ),
            'options' => [ 'imap' => __( 'IMAP', 'erp' ), 'pop3' => __( 'POP3', 'erp') ],
            'default' =>  'imap',
        ];

        $fields['imap'][] = [
            'title'             => __( 'Port', 'erp' ),
            'id'                => 'port',
            'type'              => 'text',
            'desc'              => __( 'IMAP/POP3 port.<br> IMAP: 993<br> POP3: 995', 'erp' ),
            'custom_attributes' => [
                'placeholder'   => 993
            ]
        ];

        $fields['imap'][] = [
            'title'   => __( 'Encryption', 'erp' ),
            'id'      => 'encryption',
            'type'    => 'select',
            'options' => [ 'ssl' => __( 'SSL', 'erp' ), 'tls' => __( 'TLS', 'erp'), 'notls' => __( 'None', 'erp') ],
            'default' =>  'ssl',
            'desc'    => __( 'Encryption type.', 'erp' ),
        ];

        $fields['imap'][] = [
            'title'   => __( 'Certificate', 'erp' ),
            'id'      => 'certificate',
            'type'    => 'checkbox',
            'desc'    => __( 'Use encryption certificate.', 'erp' ),
        ];

        $fields['imap'][] = [
            'type' => 'sectionend',
            'id'   => 'script_styling_options'
        ];
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
