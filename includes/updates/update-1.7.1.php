<?php
namespace WeDevs\ERP\HRM\Update;

/*
 * Add transaction_charge column in `erp_acct_expenses` table
 */
function erp_acct_alter_acct_invoice_details_1_7_1() {
    global $wpdb;

    $cols  = $wpdb->get_col( "DESC  {$wpdb->prefix}erp_acct_invoice_details" );

    if ( ! in_array( 'tax_cat_id', $cols, true ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$wpdb->prefix}erp_acct_invoice_details ADD `tax_cat_id` int DEFAULT null AFTER `tax`;"
            )
        );
    }
}

/*
 * Add transaction_charge column in `erp_acct_expenses` table
 */
function erp_acct_alter_acct_purchase_details_1_7_1() {
    global $wpdb;

    $cols  = $wpdb->get_col( "DESC  {$wpdb->prefix}erp_acct_purchase_details" );

    if ( ! in_array( 'tax_cat_id', $cols, true ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$wpdb->prefix}erp_acct_purchase_details ADD `tax_cat_id` int DEFAULT null AFTER `amount`;"
            )
        );
    }
}


erp_acct_alter_acct_invoice_details_1_7_1();
