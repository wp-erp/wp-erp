<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get closest next financial year
 *
 * @param string $date
 *
 * @return void
 */
function erp_acct_get_closest_next_fn_year( $date ) {
    global $wpdb;

    $sql = "SELECT id, start_date FROM {$wpdb->prefix}erp_acct_financial_years WHERE start_date > '%s' ORDER BY start_date ASC LIMIT 1";

    return $wpdb->get_row( $wpdb->prepare( $sql, $date ) );
}

/**
 * Close balance sheet now
 *
 * @param array $args
 *
 * @return void
 */
function erp_acct_close_balance_sheet_now( $args ) {
    $balance_sheet    = erp_acct_get_balance_sheet($args);
    $assets           = $balance_sheet['rows1'];
    $liability_equity = array_merge( $balance_sheet['rows2'], $balance_sheet['rows3'] );
    $next_f_year_id   = $args['f_year_id'];

    // ledgers
    global $wpdb;
    $sql     = "SELECT id, chart_id, name, slug FROM {$wpdb->prefix}erp_acct_ledgers";
    $ledgers = $wpdb->get_results( $sql, ARRAY_A );

    foreach ( $ledgers as $ledger ) {
        // assets
        foreach ( $assets as $asset ) {
            if ( ! empty( $asset['id'] ) ) {
                if ( $asset['id'] === $ledger['id'] ) {

                    erp_acct_insert_into_opening_balance(
                        $next_f_year_id,
                        $ledger['chart_id'],
                        $ledger['id'],
                        'ledger',
                        $asset['balance'],
                        0.00
                    );

                }
            }
        } // assets loop

        // liability + equity
        foreach ( $liability_equity as $liab_equ ) {
            if ( ! empty( $liab_equ['id'] ) ) {
                if ( $liab_equ['id'] === $ledger['id'] ) {

                    erp_acct_insert_into_opening_balance(
                        $next_f_year_id,
                        $ledger['chart_id'],
                        $ledger['id'],
                        'ledger',
                        0.00,
                        $liab_equ['balance']
                    );

                }
            }
        } // liability + equity loop
    } // ledger loop

    // get accounts receivable
    $accounts_receivable = erp_acct_get_accounts_receivable_balance_with_people( $args );

    foreach ( $accounts_receivable as $key => $acc_receivable ) {
        erp_acct_insert_into_opening_balance(
            $next_f_year_id, null, $key, 'people', $acc_receivable, 0.00
        );
    }
}

/**
 * Insert closing balance data into opening balance
 *
 * @param int $f_year_id
 * @param int $chart_id
 * @param int $ledger_id
 * @param string $type
 * @param int $debit
 * @param int $credit
 *
 * @return void
 */
function erp_acct_insert_into_opening_balance($f_year_id, $chart_id, $ledger_id, $type, $debit, $credit) {
    global $wpdb;

    $wpdb->insert(
        "{$wpdb->prefix}erp_acct_opening_balances",
        [
            'financial_year_id' => $f_year_id,
            'chart_id'          => $chart_id,
            'ledger_id'         => $ledger_id,
            'type'              => $type,
            'debit'             => $debit,
            'credit'            => $credit,
            'created_at'        => date('Y-m-d H:i:s'),
            'created_by'        => get_current_user_id()
        ]
    );

}

/**
 * Get accounts receivable balance with people
 *
 * @return void
 */
function erp_acct_get_accounts_receivable_balance_with_people( $args ) {
    global $wpdb;

    // mainly customer_id and ( debit - credit )
    $sql = "SELECT invoice.customer_id, SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_invoice_account_details AS invoice_acd
        LEFT JOIN {$wpdb->prefix}erp_acct_invoices AS invoice ON invoice_acd.invoice_no = invoice.voucher_no
        WHERE invoice_acd.trn_date BETWEEN '%s' AND '%s' GROUP BY invoice_acd.invoice_no HAVING balance > 0";

    $data = $wpdb->get_results( $wpdb->prepare( $sql, $args['start_date'], $args['end_date'] ), ARRAY_A );

    return erp_acct_people_ar_calc_with_opening_balance( $args['start_date'], $data, $sql );
}

/**
 * Get people account_payable/account_receivable calculate with opening balance within financial year date range
 *
 * @param string $bs_start_date
 * @param float $data => account details data on balance sheet date range
 * @param string $sql
 * @param string $type
 *
 * @return float
 */
function erp_acct_people_ar_calc_with_opening_balance( $bs_start_date, $data, $sql ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $bs_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_people_ar_opening_balance_by_fn_year_id( $closest_fy_date['id'] );

    $temp = [];

    $merged = array_merge( $data, $opening_balance );

    foreach ( $merged as $entry ) {
        $id = $entry['customer_id'];

        if ( empty( $temp[$id] ) ) {
            $temp[$id] = 0;
        }

        $temp[ $id ] += $entry['balance'];
    }

    return $temp;

    // should we go further calculation, check the diff
    if ( ! erp_acct_has_date_diff($bs_start_date, $closest_fy_date['start_date']) ) {
        return $temp;
    } else {
        $prev_date_of_tb_start = date( 'Y-m-d', strtotime( '-1 day', strtotime($tb_start_date) ) );
    }

    // $start_date = $closest_fy_date['start_date'];
    // $end_date   = $prev_date_of_tb_start;

    // if ( 'payable' === $type ) {
    //     $balance += erp_acct_calculate_people_balance($sql1, $start_date, $end_date);
    //     $balance += erp_acct_calculate_people_balance($sql2, $start_date, $end_date);
    // } elseif ( 'receivable' === $type ) {
    //     $balance += erp_acct_calculate_people_balance($sql1, $start_date, $end_date);
    // }

    // return $balance;
}

/**
 * People accounts receivable from opening balance
 *
 * @param int $id
 *
 * @return void
 */
function erp_acct_people_ar_opening_balance_by_fn_year_id( $id ) {
    global $wpdb;

    $sql = "SELECT ledger_id AS customer_id, SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_opening_balances
        WHERE financial_year_id = %d AND type = 'people' GROUP BY ledger_id HAVING balance > 0";

    return $wpdb->get_results( $wpdb->prepare($sql, $id), ARRAY_A );
}
