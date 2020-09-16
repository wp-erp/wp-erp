<?php
namespace WeDevs\ERP\HRM\Update;

function erp_acct_alter_acct_expenses_1_6_4() {
    global $wpdb;

    // Add hash column in `wp_erp_acct_expenses` table
    $table = $wpdb->prefix . 'wp_erp_acct_expenses';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( !in_array( 'transaction_charge', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `transaction_charge` decimal(20,2) DEFAULT 0 AFTER `trn_by`;"
            )
        );
    }

}

function erp_acct_alter_pay_purchase_1_6_4() {
    global $wpdb;

    // Add hash column in `wp_erp_acct_pay_purchase` table
    $table = $wpdb->prefix . 'wp_erp_acct_pay_purchase';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( !in_array( 'transaction_charge', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `transaction_charge` decimal(20,2) DEFAULT 0 AFTER `trn_by`;"
            )
        );
    }

    if (!in_array('ref', $cols)) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `ref` varchar(255) NULL DEFAULT NULL AFTER `trn_by`;"
            )
        );
    }

}
 
erp_acct_alter_acct_expenses_1_6_4();
erp_acct_alter_pay_purchase_1_6_4();
