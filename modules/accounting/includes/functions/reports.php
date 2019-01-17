<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Get trial balance
 */

function erp_acct_get_trial_balance() {
    global $wpdb;

    $sql = "SELECT
        ledger.name,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id
        GROUP BY ledger_detail.ledger_id";

    return $wpdb->get_results($sql, ARRAY_A);
}
