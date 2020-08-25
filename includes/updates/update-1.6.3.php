<?php
namespace WeDevs\ERP\HRM\Update;

function erp_acct_alter_invoice_receipts_1_6_3() {
    global $wpdb;

    // Add hash column in `erp_crm_contact_subscriber` table
    $table = $wpdb->prefix . 'erp_acct_invoice_receipts';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( !in_array( 'transaction_charge', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `transaction_charge` decimal(20,2) DEFAULT 0 AFTER `amount`;"
            )
        );
    }

}



function erp_acct_insert_to_erp_acct_ledgers_1_6_3() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_ledgers';
    $check_data = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table WHERE slug = %s", array( 'bank_transaction_charge' )
        )
    );

    if ( empty( $check_data ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO $table ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                  array( 5, 'Bank Transaction Charge', 'bank_transaction_charge', '606', 0, date('Y-m-d') )
            )
        );
    }

}


erp_acct_alter_invoice_receipts_1_6_3();
erp_acct_insert_to_erp_acct_ledgers_1_6_3();
