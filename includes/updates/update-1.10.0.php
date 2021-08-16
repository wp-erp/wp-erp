<?php

/*
 * Add shipping column in `erp_acct_invoices` table
 */
function erp_acct_alter_invoices_table_1_10_0() {
    global $wpdb;

    $cols = $wpdb->get_col( "DESC  {$wpdb->prefix}erp_acct_invoices" );

    if ( ! in_array( 'shipping_tax', $cols, true ) ) {
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}erp_acct_invoices
            ADD `shipping_tax` decimal(20,2) DEFAULT 0 AFTER `discount_type`;"
        );
    }

    if ( ! in_array( 'shipping', $cols, true ) ) {
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}erp_acct_invoices
            ADD `shipping` decimal(20,2) DEFAULT 0 AFTER `discount_type`;"
        );
    }
}

/*
 * Add tax_cat_id and shipping columns in `erp_acct_invoice_details` table
 */
function erp_acct_alter_invoice_details_table_1_10_0() {
    global $wpdb;

    $cols = $wpdb->get_col( "DESC {$wpdb->prefix}erp_acct_invoice_details" );

    if ( ! in_array( 'tax_cat_id', $cols, true ) ) {
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}erp_acct_invoice_details
            ADD `tax_cat_id` INT(11) DEFAULT NULL AFTER `tax`;"
        );
    }
}

/*
 * Add tax_cat_id column in `erp_acct_purchase_details` table
 */
function erp_acct_alter_purchase_details_table_1_10_0() {
    global $wpdb;

    $cols = $wpdb->get_col( "DESC {$wpdb->prefix}erp_acct_purchase_details" );

    if ( ! in_array( 'tax_cat_id', $cols, true ) ) {
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}erp_acct_purchase_details
            ADD `tax_cat_id` INT(11) DEFAULT NULL AFTER `amount`;"
        );
    }
}

/**
 * Create table erp_acct_synced_taxes
 */
function erp_acct_create_synced_taxes_table_1_10_0() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $schema = "CREATE TABLE {$wpdb->prefix}erp_acct_synced_taxes (
                id int NOT NULL AUTO_INCREMENT,
                system_id bigint NOT NULL,
                sync_id bigint DEFAULT NULL,
                sync_slug varchar(100) DEFAULT NULL,
                sync_type varchar(100) DEFAULT NULL,
                sync_source varchar(100) DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY system_id (system_id),
                KEY sync_id (sync_id),
                KEY sync_slug (sync_slug),
                KEY sync_type (sync_type),
                KEY sync_source (sync_source)
            ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $schema );
}

/**
 * Add necessary ledgers
 */
function erp_acct_dump_ledgers_table_data_1_10_0() {
    global $wpdb;

    $shipment_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s",
            [ 'shipment' ]
        )
    );

    if ( empty( $shipment_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` )
                VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Shipment', 'shipment', '1411', 1, gmdate( 'Y-m-d' ) ]
            )
        );
    }

    $shipment_tax_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s",
            [ 'shipment_tax' ]
        )
    );

    if ( empty( $shipment_tax_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` )
                VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 2, 'Shipment Tax', 'shipment_tax', '221', 1, gmdate( 'Y-m-d' ) ]
            )
        );
    }
}

/**
 * Modify pay_rate column in employee table
 */
function erp_hr_alter_employees_table_1_10_0() {
    global $wpdb;

    $wpdb->query(
        "ALTER TABLE {$wpdb->prefix}erp_hr_employees
        MODIFY `pay_rate` DECIMAL(20,2) unsigned NOT NULL DEFAULT 0"
    );
}

/**
 * Migrates incinsistent employee data
 */
function erp_hr_migrate_employee_data_1_10_0() {
    global $wpdb;
    global $erp_hr_bg_process_1_10_0;

    $employees = $wpdb->get_results(
        "SELECT user_id AS id, pay_rate as pay
        FROM {$wpdb->prefix}erp_hr_employees",
        ARRAY_A
    );

    foreach ( $employees as $employee ) {
        $erp_hr_bg_process_1_10_0->push_to_queue( $employee );
    }

    $erp_hr_bg_process_1_10_0->save()->dispatch();
}

/*
 * Alter some `option_name` value in `wp_options` table
 *
 * It's caused for moving the CRM email connectivity to Email Section
 */
function erp_settings_update_options_table_data_1_10_0() {
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

erp_disable_mysql_strict_mode();
erp_hr_alter_employees_table_1_10_0();
erp_acct_alter_invoices_table_1_10_0();
erp_acct_alter_invoice_details_table_1_10_0();
erp_acct_alter_purchase_details_table_1_10_0();
erp_acct_create_synced_taxes_table_1_10_0();
erp_acct_dump_ledgers_table_data_1_10_0();
erp_settings_update_options_table_data_1_10_0();
erp_hr_migrate_employee_data_1_10_0();
