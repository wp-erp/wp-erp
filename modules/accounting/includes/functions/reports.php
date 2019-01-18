<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function erp_acct_get_account_account_receivable() {
    global $wpdb;

    // mainly ( debit - credit )
    $sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( invoice_acc_detail.debit - invoice_acc_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_invoices AS invoice
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details AS invoice_acc_detail ON invoice.voucher_no = invoice_acc_detail.invoice_no
        GROUP BY invoice.voucher_no HAVING balance > 0 ) AS get_amount";

    return $wpdb->get_var($sql);
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

    $results = $wpdb->get_results($sql, ARRAY_A);

    $results[] = [
        'name' => 'Account Payable',
        'balance' => 0
    ];

    $results[] = [
        'name' => 'Account Receivable',
        'balance' => erp_acct_get_account_account_receivable()
    ];

    return $results;
}
