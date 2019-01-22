<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function erp_acct_get_account_receivable() {
    global $wpdb;

    // mainly ( debit - credit )
    $sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_invoice_account_details
        GROUP BY invoice_no HAVING balance > 0 ) AS get_amount";

    return $wpdb->get_var($sql);
}

function erp_acct_get_account_payable() {
    global $wpdb;

    $sql = "SELECT SUM(balance) AS amount 
        FROM ( SELECT SUM( debit - credit ) AS balance FROM wp_erp_acct_bill_account_details GROUP BY bill_no HAVING balance > 0 )
        AS get_amount";

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

    // All DB results are inside `rows` key
    $results['rows'] = $wpdb->get_results($sql, ARRAY_A);
    $results['rows'][] = [
        'name' => 'Account Payable',
        'balance' => erp_acct_get_account_payable()
    ];
    $results['rows'][] = [
        'name' => 'Account Receivable',
        'balance' => erp_acct_get_account_receivable()
    ];

    // Totals are inside the root `result` array
    $results['total_debit'] = 0;
    $results['total_credit'] = 0;

    // Add-up all debit and credit
    foreach ($results['rows'] as $result) {
        if ( ! empty($result['balance']) ) {
            if ( $result['balance'] > 0 ) {
                $results['total_debit'] += $result['balance'];
            } else {
                $results['total_credit'] += $result['balance'];
            }
        }
    }

    return $results;
}
