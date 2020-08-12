<?php

function erp_acct_alter_invoice_receipts_1_6_3() {
    global $wpdb;

    // Add hash column in `erp_crm_contact_subscriber` table
    $table = $wpdb->prefix . 'erp_acct_invoice_receipts';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( !in_array( 'transaction_charge', $cols ) ) {
        $wpdb->query( "ALTER TABLE $table ADD `transaction_charge` decimal(20,2) DEFAULT 0 AFTER `amount`;" );
    }

}


erp_acct_alter_invoice_receipts_1_6_3();
