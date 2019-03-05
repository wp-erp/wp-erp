<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * ===================================================
 * Trial Balance
 * ===================================================
 */

/**
 * Get account receivable
 */
function erp_acct_get_account_receivable() {
    global $wpdb;

    // mainly ( debit - credit )
    $sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_invoice_account_details WHERE trn_date <= CURDATE()
        GROUP BY invoice_no HAVING balance > 0 ) AS get_amount";

    return $wpdb->get_var($sql);
}

/**
 * Get account payble
 */
function erp_acct_get_account_payable() {
    global $wpdb;

    /**
     *? Why only bills, not expense?
     *? Expense is `direct expense`, and we don't include direct expense here
     */
    $bill_sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM wp_erp_acct_bill_account_details WHERE trn_date <= CURDATE() GROUP BY bill_no HAVING balance < 0 )
        AS get_amount";

    $purchase_sql = "SELECT SUM(balance) AS amount
    FROM ( SELECT SUM( debit - credit ) AS balance FROM wp_erp_acct_purchase_account_details WHERE trn_date <= CURDATE() GROUP BY purchase_no HAVING balance < 0 )
    AS get_amount";

    $bill_amount = $wpdb->get_var($bill_sql);
    $purchase_amount = $wpdb->get_var($purchase_sql);

    return $bill_amount + $purchase_amount;
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
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE trn_date <= CURDATE()
        GROUP BY ledger_detail.ledger_id";

    // All DB results are inside `rows` key
    $results['rows'] = $wpdb->get_results($sql, ARRAY_A);
    $results['rows'][] = [
        'name' => 'Accounts Payable',
        'balance' => erp_acct_get_account_payable()
    ];
    $results['rows'][] = [
        'name' => 'Accounts Receivable',
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


/**
 * ===================================================
 * Ledger Report
 * ===================================================
 */

function erp_acct_get_ledger_report( $ledger_id, $start_date, $end_date ) {
    global $wpdb;

    // opening balance
    $sql1 = $wpdb->prepare("SELECT SUM(debit - credit) AS opening_balance
        FROM {$wpdb->prefix}erp_acct_ledger_details
        WHERE ledger_id = %d AND trn_date < '%s'",
        $ledger_id, $start_date
    );

    $db_opening_balance = $wpdb->get_var( $sql1 );
    $opening_balance = (float) $db_opening_balance;

    // ledger details
    $sql2 = $wpdb->prepare("SELECT
        trn_no, particulars, debit, credit, trn_date, created_at
        FROM {$wpdb->prefix}erp_acct_ledger_details
        WHERE ledger_id = %d AND trn_date BETWEEN '%s' AND '%s'",
        $ledger_id, $start_date, $end_date
    );

    $details = $wpdb->get_results( $sql2, ARRAY_A );


    $total_debit = 0;
    $total_credit = 0;

    // Please refactor me
    foreach ( $details as $key => $detail ) {
        $total_debit  += (float) $detail['debit'];
        $total_credit += (float) $detail['credit'];

        if ( '0.00' === $detail['debit'] ) {
            // so we're working with credit
            if ( $opening_balance < 0 ) {
                // opening balance is negative
                $opening_balance = $opening_balance + (-(float) $detail['credit']);
                $details[$key]['balance'] = $opening_balance . ' Cr';

            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (-(float) $detail['credit']);

                // after calculation with credit
                if ( $opening_balance >= 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Cr';
                }

            } else {
                // opening balance is 0
                $details[$key]['balance'] = '0 Dr';
            }
        }

        if ( '0.00' === $detail['credit'] ) {
            // so we're working with debit

            if ( $opening_balance < 0 ) {
                // opening balance is negative
                $opening_balance = $opening_balance + (float) $detail['debit'];
                $details[$key]['balance'] = $opening_balance . ' Cr';

            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (float) $detail['debit'];

                // after calculation with debit
                if ( $opening_balance >= 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Cr';
                }

            } else {
                // opening balance is 0
                $details[$key]['balance'] = '0 Dr';
            }
        }
    }

    // Assign opening balance as first row
    if ( (float) $db_opening_balance > 0 ) {
        $balance = $db_opening_balance . ' Dr';
    } elseif( (float) $db_opening_balance < 0 ) {
        $balance = $db_opening_balance . ' Cr';
    } else {
        $balance = '0 Dr';
    }

    array_unshift( $details, [
        'trn_no'      => null,
        'particulars' => 'Opening Balance =',
        'debit'       => null,
        'credit'      => null,
        'trn_date'    => $start_date,
        'balance'     => $balance,
        'created_at'  => null
    ] );

    return [
        'details' => $details,
        'extra' => [
            'total_debit'  => $total_debit,
            'total_credit' => $total_credit
        ]
    ];
}
