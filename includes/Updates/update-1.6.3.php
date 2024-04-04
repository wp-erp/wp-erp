<?php

namespace WeDevs\ERP\HRM\Update;

function erp_acct_alter_invoice_receipts_1_6_3() {
    global $wpdb;

    // Add hash column in `erp_crm_contact_subscriber` table
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( ! in_array( 'transaction_charge', $cols ) ) {
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}erp_acct_invoice_receipts ADD `transaction_charge` decimal(20,2) DEFAULT 0 AFTER `amount`;"
        );
    }
}

function erp_acct_insert_to_erp_acct_ledgers_1_6_3() {
    global $wpdb;

    $check_data = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'bank_transaction_charge' ]
        )
    );

    if ( empty( $check_data ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                  [ 5, 'Bank Transaction Charge', 'bank_transaction_charge', '606', 0, gmdate( 'Y-m-d' ) ]
            )
        );
    }
}

erp_acct_alter_invoice_receipts_1_6_3();
erp_acct_insert_to_erp_acct_ledgers_1_6_3();
