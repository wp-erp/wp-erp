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

erp_acct_alter_invoice_details_table_1_9_1();
erp_acct_alter_purchase_details_table_1_9_1();