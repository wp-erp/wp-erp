<?php

function erp_updater_acct_add_ref_column_to_invoice_receipt() {
    global $wpdb;

    $wpdb->query(
        "ALTER TABLE {$wpdb->prefix}erp_acct_invoice_receipts ADD COLUMN `ref` VARCHAR(255) DEFAULT NULL AFTER amount"
    );
}

/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_1_5_6() {
    erp_updater_acct_add_ref_column_to_invoice_receipt();
}

wperp_update_1_5_6();
