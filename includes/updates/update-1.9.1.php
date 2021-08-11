<?php

/*
 * Add shipping column in `erp_acct_invoices` table
 */
function erp_acct_alter_invoices_table_1_9_1() {
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
function erp_acct_alter_invoice_details_table_1_9_1() {
    global $wpdb;

    $cols = $wpdb->get_col( "DESC  {$wpdb->prefix}erp_acct_invoice_details" );

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
function erp_acct_alter_purchase_details_table_1_9_1() {
    global $wpdb;

    $cols = $wpdb->get_col( "DESC  {$wpdb->prefix}erp_acct_purchase_details" );

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
function erp_acct_create_synced_taxes_table_1_9_1() {
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
 * Modify pay_rate column in employee table
 */
function erp_hr_alter_employees_table_1_9_1() {
    global $wpdb;

    $wpdb->query(
        "ALTER TABLE {$wpdb->prefix}erp_hr_employees
        MODIFY `pay_rate` DECIMAL(20,2) unsigned NOT NULL DEFAULT 0"
    );
}

/**
 * Migrates incinsistent employee data
 */
function erp_hr_migrate_employee_data_1_9_1() {
    global $wpdb;
    global $erp_hr_bg_process_1_9_1;

    $employees = $wpdb->get_results(
        "SELECT user_id AS id, pay_rate as pay
        FROM {$wpdb->prefix}erp_hr_employees",
        ARRAY_A
    );

    foreach ( $employees as $employee ) {
        $erp_hr_bg_process_1_9_1->push_to_queue( $employee );
    }

    $erp_hr_bg_process_1_9_1->save()->dispatch();
}

erp_disable_mysql_strict_mode();
erp_hr_alter_employees_table_1_9_1();
erp_acct_alter_invoices_table_1_9_1();
erp_acct_alter_invoice_details_table_1_9_1();
erp_acct_alter_purchase_details_table_1_9_1();
erp_acct_create_synced_taxes_table_1_9_1();
erp_acct_dump_ledgers_table_data();
erp_hr_migrate_employee_data_1_9_1();