<?php

/*
 * Add tax_cat_id column in `erp_acct_invoice_details` table
 */
function erp_acct_alter_invoice_details_table_1_9_1() {
    global $wpdb;

    $cols  = $wpdb->get_col( "DESC  {$wpdb->prefix}erp_acct_invoice_details" );

    if ( ! in_array( 'tax_cat_id', $cols, true ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$wpdb->prefix}erp_acct_invoice_details ADD `tax_cat_id` INT(11) DEFAULT NULL AFTER `tax`;"
            )
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
            $wpdb->prepare(
                "ALTER TABLE {$wpdb->prefix}erp_acct_purchase_details ADD `tax_cat_id` INT(11) DEFAULT NULL AFTER `amount`;"
            )
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
                sync_id bigint NOT NULL,
                sync_type varchar(100),
                sync_source varchar(100),
                PRIMARY KEY  (id),
                KEY system_id (system_id),
                KEY sync_id (sync_id),
                KEY sync_type (sync_type),
                KEY sync_source (sync_source)
            ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $schema );
}

erp_acct_alter_invoice_details_table_1_9_1();
erp_acct_alter_purchase_details_table_1_9_1();
erp_acct_create_synced_taxes_table_1_9_1();