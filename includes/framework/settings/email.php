<?php

use WeDevs\ERP\Framework\ERP_Settings_Page;

/**
 * General class
 */
class ERP_Email_Settings extends ERP_Settings_Page {


    function __construct() {
        $this->id    = 'erp-email';
        $this->label = __( 'Emails', 'wp-erp' );

        add_action( 'erp_admin_field_notification_emails', [ $this, 'notification_emails' ] );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $fields = [

            [
                'title' => __( 'Email Sender Options', 'wp-erp' ),
                'type'  => 'title',
                'desc'  => __( 'Email notification settings for WP ERP. Customize the look and feel of outgoing emails.', 'wp-erp' )
            ],

            [
                'title'   => __( '"From" Name', 'wp-erp' ),
                'id'      => 'from_name',
                'type'    => 'text',
                'default' => get_bloginfo( 'name' ),
                'tooltip' => true,
                'desc' => __( 'The senders name appears on the outgoing emails', 'wp-erp' )
            ],
            [
                'title'   => __( '"From" Address', 'wp-erp' ),
                'id'      => 'from_email',
                'type'    => 'text',
                'default' => get_option( 'admin_email' ),
                'tooltip' => true,
                'desc'    => __( 'The senders email appears on the outgoing emails', 'wp-erp' )
            ],

            [
                'title'             => __( 'Header Image', 'wp-erp' ),
                'id'                => 'header_image',
                'type'              => 'text',
                'desc'              => __( 'Upload a logo/banner and provide the URL here.', 'wp-erp' ),
                'tooltip'           => true,
                'custom_attributes' => [
                    'placeholder' => 'http://example.com/path/to/logo.png'
                ]
            ],
            [
                'title'   => __( 'Footer Text', 'wp-erp' ),
                'id'      => 'footer_text',
                'type'    => 'textarea',
                'css'     => 'min-width:300px;',
                'tooltip' => true,
                'default' => sprintf( '%s  - Powered by WP ERP', get_bloginfo( 'name' ) ),
                'desc'    => __( 'The text apears on each emails footer area.', 'wp-erp' )
            ],

            [ 'type' => 'sectionend', 'id' => 'script_styling_options' ],

            [
                'title' => __( 'Notification Emails', 'wp-erp' ),
                'desc'  => __( 'Email notifications sent from WP ERP are listed below. Click on an email to configure it.', 'wp-erp' ),
                'type'  => 'title',
                'id'    => 'email_notification_settings'
            ],

            [ 'type' => 'notification_emails' ],
            [ 'type' => 'sectionend', 'id' => 'script_styling_options' ],

        ]; // End general settings

        return apply_filters( 'erp_email_settings', $fields );
    }

    function notification_emails() {
        $email_templates = wperp()->emailer->get_emails();
        ?>
        <style type="text/css">
            td.erp-mail-wrapper {
                padding: 0 15px 10px 0;;
            }
            table.erp-email-notification-table th {
                padding: 9px 7px!important;
                vertical-align: middle;
            }

            table.erp-email-notification-table th.erp-email-settings-table-status,
            table.erp-email-notification-table td.erp-email-settings-table-status {
                width: 1em;
            }

            table.erp-email-notification-table td {
                padding: 7px;
                line-height: 2em;
                vertical-align: middle;
            }

            table.erp-email-notification-table th.erp-email-settings-table-name,
            table.erp-email-notification-table td.erp-email-settings-table-name {
                padding-left: 15px !important;
            }

            table.erp-email-notification-table td.erp-email-settings-table-name a {
                font-weight: 700;
            }

            table.erp-email-notification-table tr:nth-child(odd) td {
                background: #f9f9f9;
            }
        </style>
        <tr valign="top">
            <td class="erp-mail-wrapper" colspan="2">
                <table class="erp-email-notification-table widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <?php
                                $columns = apply_filters( 'erp_email_setting_columns', array(
                                    // 'status'   => '',
                                    'name'        => __( 'Email', 'erp' ),
                                    // 'module'   => __( 'Module', 'erp' ),
                                    'description' => __( 'Description', 'erp' ),
                                    'actions'     => ''
                                ) );

                                foreach ( $columns as $key => $column ) {
                                    echo '<th class="erp-email-settings-table-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
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
                                        echo '<td class="erp-email-settings-table-' . esc_attr( $key ) . '">
                                            <a href="' . admin_url( 'admin.php?page=erp-settings&tab=erp-email&section=' . strtolower( $email_key ) ) . '">' . $email->get_title() . '</a>
                                        </td>';
                                        break;

                                    case 'status':
                                    case 'module':
                                    case 'recipient':
                                        echo '<td class="erp-email-settings-table-' . esc_attr( $key ) . '">

                                        </td>';
                                        break;

                                    case 'description':
                                        echo '<td class="erp-email-settings-table-' . esc_attr( $key ) . '">
                                            <span class="help">' . $email->get_description() . '</span>
                                        </td>';
                                        break;

                                    case 'actions' :
                                        echo '<td class="erp-email-settings-table-' . esc_attr( $key ) . '">
                                            <a class="button alignright" href="' . admin_url( 'admin.php?page=erp-settings&tab=erp-email&section=' . strtolower( $email_key ) ) . '">' . __( 'Configure', 'wp-erp' ) . '</a>
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
        $current_section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : false;

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
            $current_section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : false;

            // saving individual email settings
            if ( $current_section ) {
                $email_templates = wperp()->emailer->get_emails();

                foreach ( $email_templates as $email_key => $email ) {
                    if ( strtolower( $email_key ) == $current_section ) {

                        $settings       = $email->get_form_fields();
                        $update_options = array();

                        // var_dump( $settings );
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
