<?php

/*
 * Alter some `option_name` value in `wp_options` table
 *
 * It's caused for moving the CRM email connectivity to Email Section
 */
function erp_settings_alter_options_table_crm_email_1_9_1() {
    global $wpdb;

    // Check if someone already added the new option_names, Then just delete the previous one or update
    $email_gmail_connect_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}options WHERE option_name = %s", [ 'erp_settings_erp-email_gmail' ]
        )
    );

    // Delete or Update `erp_settings_erp-crm_email_connect_gmail` to `erp_settings_erp-email_gmail`
    if ( empty( $email_gmail_connect_exists ) ) {
        $wpdb->query( "UPDATE `{$wpdb->prefix}options` SET `option_name` = 'erp_settings_erp-email_gmail' WHERE `option_name`= 'erp_settings_erp-crm_email_connect_gmail';" );
    } else {
        delete_option( 'erp_settings_erp-crm_email_connect_gmail' );
    }

    // Same for IMAP
    $email_imap_connect_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}options WHERE option_name = %s", [ 'erp_settings_erp-email_imap' ]
        )
    );

    // Delete or Update `erp_settings_erp-crm_email_connect_imap` to `erp_settings_erp-email_imap`
    if ( empty( $email_imap_connect_exists ) ) {
        $wpdb->query( "UPDATE `{$wpdb->prefix}options` SET `option_name` = 'erp_settings_erp-email_imap' WHERE `option_name`= 'erp_settings_erp-crm_email_connect_imap';" );
    } else {
        delete_option( 'erp_settings_erp-crm_email_connect_imap' );
    }
}

erp_settings_alter_options_table_crm_email_1_9_1();
