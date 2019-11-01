<?php

function erp_updater_acct_add_ref_column_to_invoice_receipt() {
    global $wpdb;

    $wpdb->query(
        "ALTER TABLE {$wpdb->prefix}erp_acct_invoice_receipts ADD COLUMN `ref` VARCHAR(255) DEFAULT NULL AFTER amount"
    );
}

function erp_updater_acct_add_billing_column_to_purchase() {
    global $wpdb;

    $wpdb->query(
        "ALTER TABLE {$wpdb->prefix}erp_acct_purchase ADD COLUMN `billing_address` VARCHAR(255) DEFAULT NULL AFTER vendor_name"
    );
}

/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_1_5_6() {
    erp_updater_acct_add_ref_column_to_invoice_receipt();
    erp_updater_acct_add_billing_column_to_purchase();
}

wperp_update_1_5_6();
