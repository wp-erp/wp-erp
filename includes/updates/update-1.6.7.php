<?php
namespace WeDevs\ERP\HRM\Update;

/*
 * Add transaction_charge column in `erp_acct_expenses` table
 */
function erp_acct_alter_acct_expenses_1_6_7() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_expenses';
    $cols  = $wpdb->get_col( $wpdb->prepare( "DESC $table" ) );

    if ( ! in_array( 'transaction_charge', $cols, true ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$table} ADD `transaction_charge` decimal(20,2) DEFAULT 0 AFTER `trn_by`;"
            )
        );
    }
}

/*
 *  Add transaction_charge and ref column in `erp_acct_pay_purchase` table
 */
function erp_acct_alter_pay_purchase_1_6_7() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_pay_purchase';
    $cols  = $wpdb->get_col( $wpdb->prepare( "DESC $table" ) );

    if ( ! in_array( 'transaction_charge', $cols, true ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$table} ADD `transaction_charge` decimal(20,2) DEFAULT 0 AFTER `trn_by`;"
            )
        );
    }

    if ( ! in_array( 'ref', $cols, true ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$table} ADD `ref` varchar(255) NULL DEFAULT NULL AFTER `trn_by`;"
            )
        );
    }
}

/*
 * Add transaction_charge column in `erp_acct_pay_bill` table
 */
function erp_acct_alter_pay_bill_1_6_7() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_pay_bill';
    $cols  = $wpdb->get_col( $wpdb->prepare( "DESC $table" ) );

    if ( ! in_array( 'ref', $cols, true ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$table} ADD `ref` varchar(255) NULL DEFAULT NULL  AFTER `particulars`;"
            )
        );
    }
}

erp_acct_alter_acct_expenses_1_6_7();
erp_acct_alter_pay_purchase_1_6_7();
erp_acct_alter_pay_bill_1_6_7();
