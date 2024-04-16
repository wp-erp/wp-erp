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
function erp_acct_clsbl_get_closest_next_fn_year( $date ) {
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare( "SELECT id, start_date, end_date FROM {$wpdb->prefix}erp_acct_financial_years WHERE start_date > %s ORDER BY start_date ASC LIMIT 1", $date ) );
}

/**
 * Close balance sheet now
 *
 * @param array $args
 *
 * @return void
 */
function erp_acct_clsbl_close_balance_sheet_now( $args ) {
    $balance_sheet  = erp_acct_get_balance_sheet( $args );
    $assets         = $balance_sheet['rows1'];
    $liability      = $balance_sheet['rows2'];
    $equity         = $balance_sheet['rows3'];
    $next_f_year_id = $args['f_year_id'];

    global $wpdb;

    // remove next financial year data if exists
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}erp_acct_opening_balances
    WHERE financial_year_id = %d",
            $next_f_year_id
        )
    );

    $ledger_map = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();

    // ledgers
    $sql     = "SELECT id, chart_id, name, slug FROM {$wpdb->prefix}erp_acct_ledgers";
    $ledgers = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    foreach ( $ledgers as $ledger ) {
        // assets
        foreach ( $assets as $asset ) {
            if ( ! empty( $asset['id'] ) ) {
                if ( $asset['id'] === $ledger['id'] ) {
                    if ( 0 <= $asset['balance'] ) {
                        $debit  = abs( $asset['balance'] );
                        $credit = 0.00;
                    } else {
                        $debit  = 0.00;
                        $credit = abs( $asset['balance'] );
                    }

                    erp_acct_clsbl_insert_into_opening_balance(
                        $next_f_year_id,
                        $ledger['chart_id'],
                        $ledger['id'],
                        'ledger',
                        $debit,
                        $credit
                    );
                }
            }
        } // assets loop

        // liability
        foreach ( $liability as $liab ) {
            if ( ! empty( $liab['id'] ) ) {
                if ( $liab['id'] === $ledger['id'] ) {
                    if ( 0 <= $liab['balance'] ) {
                        $debit  = abs( $liab['balance'] );
                        $credit = 0.00;
                    } else {
                        $debit  = 0.00;
                        $credit = abs( $liab['balance'] );
                    }

                    erp_acct_clsbl_insert_into_opening_balance(
                        $next_f_year_id,
                        $ledger['chart_id'],
                        $ledger['id'],
                        'ledger',
                        $debit,
                        $credit
                    );
                }
            }
        } // liability loop

        // equity
        $owners_equity_id = $ledger_map->get_ledger_id_by_slug( 'owner_s_equity' );

        foreach ( $equity as $eqt ) {
            if ( ! empty( $eqt['id'] ) && $owners_equity_id !== $eqt['id'] ) {
                if ( $eqt['id'] === $ledger['id'] ) {
                    if ( 0 <= $eqt['balance'] ) {
                        $debit  = abs( $eqt['balance'] );
                        $credit = 0.00;
                    } else {
                        $debit  = 0.00;
                        $credit = abs( $eqt['balance'] );
                    }

                    erp_acct_clsbl_insert_into_opening_balance(
                        $next_f_year_id,
                        $ledger['chart_id'],
                        $ledger['id'],
                        'ledger',
                        $debit,
                        $credit
                    );
                }
            }
        } // liability loop
    } // ledger loop

    $chart_id_bank  = 7;
    $final_accounts = new \WeDevs\ERP\Accounting\Classes\FinalAccounts( $args );

    foreach ( $final_accounts->cash_at_bank_breakdowns as $cash_at_bank ) {
        erp_acct_clsbl_insert_into_opening_balance(
            $next_f_year_id,
            $chart_id_bank,
            $cash_at_bank['ledger_id'],
            'ledger',
            $cash_at_bank['balance'],
            0.00
        );
    }

    foreach ( $final_accounts->loan_at_bank_breakdowns as $loan_at_bank ) {
        erp_acct_clsbl_insert_into_opening_balance(
            $next_f_year_id,
            $chart_id_bank,
            $loan_at_bank['ledger_id'],
            'ledger',
            0.00,
            abs( $loan_at_bank['balance'] )
        );
    }

    // get accounts receivable
    $accounts_receivable = erp_acct_clsbl_people_ar_calc_with_opening_balance( $args['start_date'] ); //erp_acct_clsbl_get_accounts_receivable_balance_with_people( $args );

    foreach ( $accounts_receivable as $acc_receivable ) {
        erp_acct_clsbl_insert_into_opening_balance(
            $next_f_year_id,
            null,
            $acc_receivable['id'],
            'people',
            $acc_receivable['balance'],
            0.00
        );
    }

    // get accounts payable
    $accounts_payable = erp_acct_clsbl_vendor_ap_calc_with_opening_balance( $args['start_date'] ); //erp_acct_clsbl_get_accounts_payable_balance_with_people( $args );

    foreach ( $accounts_payable as $acc_payable ) {
        erp_acct_clsbl_insert_into_opening_balance(
            $next_f_year_id,
            null,
            $acc_payable['id'],
            'people',
            0.00,
            abs( $acc_payable['balance'] )
        );
    }

    // sales tax receivable
    $tax_receivable = erp_acct_clsbl_sales_tax_agency( $args, 'receivable' );

    foreach ( $tax_receivable as $receivable_agency ) {
        erp_acct_clsbl_insert_into_opening_balance(
            $next_f_year_id,
            null,
            $receivable_agency['id'],
            'tax_agency',
            $receivable_agency['balance'],
            0.00
        );
    }

    // sales tax payable
    $tax_payable = erp_acct_clsbl_sales_tax_agency( $args, 'payable' );

    foreach ( $tax_payable as $payable_agency ) {
        erp_acct_clsbl_insert_into_opening_balance(
            $next_f_year_id,
            null,
            $payable_agency['id'],
            'tax_agency',
            0.00,
            abs( $payable_agency['balance'] )
        );
    }

    $owners_equity_ledger = $ledger_map->get_ledger_id_by_slug( 'owner_s_equity' );
    $chart_equity_id      = 3;

    if ( 0 === $balance_sheet['owners_equity'] ) {
        return;
    }

    if ( $balance_sheet['owners_equity'] > 0 ) {
        erp_acct_clsbl_insert_into_opening_balance(
            $next_f_year_id,
            $chart_equity_id,
            $owners_equity_ledger,
            'ledger',
            0.00,
            abs( $balance_sheet['owners_equity'] )
        );
    } else {
        erp_acct_clsbl_insert_into_opening_balance(
            $next_f_year_id,
            $chart_equity_id,
            $owners_equity_ledger,
            'ledger',
            abs( $balance_sheet['owners_equity'] ),
            0.00
        );
    }
}

/**
 * Insert closing balance data into opening balance
 *
 * @param int    $f_year_id
 * @param int    $chart_id
 * @param int    $ledger_id
 * @param string $type
 * @param int    $debit
 * @param int    $credit
 *
 * @return void
 */
function erp_acct_clsbl_insert_into_opening_balance( $f_year_id, $chart_id, $ledger_id, $type, $debit, $credit ) {
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
            'created_at'        => gmdate( 'Y-m-d H:i:s' ),
            'created_by'        => get_current_user_id(),
        ]
    );
}

/**
 * Get accounts receivable balance with people
 *
 * @param array $args
 *
 * @return array
 */
function erp_acct_clsbl_get_accounts_receivable_balance_with_people( $args ) {
    global $wpdb;

    // mainly ( debit - credit )
    $sql = "SELECT invoice.customer_id AS id, SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_invoice_account_details AS invoice_acd
        LEFT JOIN {$wpdb->prefix}erp_acct_invoices AS invoice ON invoice_acd.invoice_no = invoice.voucher_no
        WHERE invoice_acd.trn_date BETWEEN '%s' AND '%s' GROUP BY invoice_acd.invoice_no HAVING balance > 0";

    $data = $wpdb->get_results( $wpdb->prepare( $sql, $args['start_date'], $args['end_date'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return erp_acct_clsbl_people_ar_calc_with_opening_balance( $args['start_date'], $data, $sql );
}

/**
 * Get accounts payable balance with people
 *
 * @param array $args
 *
 * @return array
 */
function erp_acct_clsbl_get_accounts_payable_balance_with_people( $args ) {
    global $wpdb;

    $bill_sql = "SELECT bill.vendor_id AS id, SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_bill_account_details AS bill_acd
        LEFT JOIN {$wpdb->prefix}erp_acct_bills AS bill ON bill_acd.bill_no = bill.voucher_no
        WHERE bill_acd.trn_date BETWEEN '%s' AND '%s' GROUP BY bill_acd.bill_no HAVING balance < 0";

    $purchase_sql = "SELECT purchase.vendor_id AS id, SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_purchase_account_details AS purchase_acd
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase AS purchase ON purchase_acd.purchase_no = purchase.voucher_no
        WHERE purchase_acd.trn_date BETWEEN '%s' AND '%s' GROUP BY purchase_acd.purchase_no HAVING balance < 0";

    $bill_data     = $wpdb->get_results( $wpdb->prepare( $bill_sql, $args['start_date'], $args['end_date'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $purchase_data = $wpdb->get_results( $wpdb->prepare( $purchase_sql, $args['start_date'], $args['end_date'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return erp_acct_clsbl_vendor_ap_calc_with_opening_balance(
        $args['start_date'],
        $bill_data,
        $purchase_data,
        $bill_sql,
        $purchase_sql
    );
}

/**
 * Get people account receivable calculate with opening balance within financial year date range
 *
 * @param string $bs_start_date
 * @param float  $data          => account details data on balance sheet date range
 * @param string $sql
 * @param string $type
 *
 * @return array
 */
function erp_acct_clsbl_people_ar_calc_with_opening_balance( $bs_start_date ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $bs_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_clsbl_customer_ar_opening_balance_by_fn_year_id( $closest_fy_date['id'] );

    // $merged = array_merge( $data, $opening_balance );
    return erp_acct_clsbl_get_formatted_people_balance( $opening_balance );

    // should we go further calculation, check the diff
    // if ( ! erp_acct_has_date_diff( $bs_start_date, $closest_fy_date['start_date'] ) ) {
    //     return $result;
    // } else {
    //     $prev_date_of_bs_start = gmdate( 'Y-m-d', strtotime( '-1 day', strtotime( $bs_start_date ) ) );
    // }

    // $query  = $wpdb->get_results( $wpdb->prepare( $sql, $closest_fy_date['start_date'], $prev_date_of_bs_start ), ARRAY_A );
    // $merged = array_merge( $result, $query );

    // return erp_acct_clsbl_get_formatted_people_balance( $merged );
}

/**
 * Get people account payable calculate with opening balance within financial year date range
 *
 * @param string $bs_start_date
 * @param float  $data          => account details data on balance sheet date range
 * @param string $sql
 * @param string $type
 *
 * @return array
 */
function erp_acct_clsbl_vendor_ap_calc_with_opening_balance( $bs_start_date ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $bs_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_clsbl_vendor_ap_opening_balance_by_fn_year_id( $closest_fy_date['id'] );

    // $merged = array_merge( $bill_data, $purchase_data, $opening_balance );
    return erp_acct_clsbl_get_formatted_people_balance( $opening_balance );
}

/**
 * People accounts receivable from opening balance
 *
 * @param int $id
 *
 * @return void
 */
function erp_acct_clsbl_customer_ar_opening_balance_by_fn_year_id( $id ) {
    global $wpdb;

    return $wpdb->get_results( $wpdb->prepare( "SELECT ledger_id AS id, SUM( debit - credit ) AS balance
    FROM {$wpdb->prefix}erp_acct_opening_balances
    WHERE financial_year_id = %d AND type = 'people' GROUP BY ledger_id HAVING balance > 0", $id ), ARRAY_A );
}

/**
 * People accounts payable from opening balance
 *
 * @param int $id
 *
 * @return void
 */
function erp_acct_clsbl_vendor_ap_opening_balance_by_fn_year_id( $id ) {
    global $wpdb;

    return $wpdb->get_results( $wpdb->prepare( "SELECT ledger_id AS id, SUM( debit - credit ) AS balance
    FROM {$wpdb->prefix}erp_acct_opening_balances
    WHERE financial_year_id = %d AND type = 'people' GROUP BY ledger_id HAVING balance < 0", $id ), ARRAY_A );
}

/**
 * Accounts receivable array merge
 *
 * @param array $arr1
 * @param array $arr2
 *
 * @return array
 */
function erp_acct_clsbl_get_formatted_people_balance( $arr ) {
    $temp = [];

    foreach ( $arr as $entry ) {
        // get index by id from a multidimensional array
        $index = array_search( $entry['id'], array_column( $arr, 'id' ), true );

        if ( ! empty( $temp[ $index ] ) ) {
            $temp[ $index ]['balance'] += $entry['balance'];
        } else {
            $temp[] = [
                'id'      => $entry['id'],
                'balance' => $entry['balance'],
            ];
        }
    }

    return $temp;
}

/**
 * Sales tax agency with closing balance
 *
 * @param array $args
 * @param array $type
 *
 * @return float
 */
function erp_acct_clsbl_sales_tax_agency( $args, $type ) {
    global $wpdb;

    if ( 'payable' === $type ) {
        $having = 'HAVING balance < 0';
    } elseif ( 'receivable' === $type ) {
        $having = 'HAVING balance > 0';
    }

    $sql = "SELECT agency_id AS id, SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_tax_agency_details
        WHERE trn_date BETWEEN '%s' AND '%s'
        GROUP BY agency_id {$having}";

    $data = $wpdb->get_results( $wpdb->prepare( $sql, $args['start_date'], $args['end_date'] ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return erp_acct_clsbl_sales_tax_agency_with_opening_balance( $args['start_date'], $data, $sql, $type );
}

/**
 * Get sales tax payable calculate with opening balance within financial year date range
 *
 * @param string $bs_start_date
 * @param float  $data          => agency details data on trial balance date range
 * @param string $sql
 * @param string $type
 *
 * @return float
 */
function erp_acct_clsbl_sales_tax_agency_with_opening_balance( $bs_start_date, $data, $sql, $type ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $bs_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_clsbl_sales_tax_agency_opening_balance_by_fn_year_id( $closest_fy_date['id'], $type );

    $merged = array_merge( $data, $opening_balance );
    $result = erp_acct_clsbl_get_formatted_people_balance( $merged );

    // should we go further calculation, check the diff
    if ( ! erp_acct_has_date_diff( $bs_start_date, $closest_fy_date['start_date'] ) ) {
        return $result;
    } else {
        $prev_date_of_tb_start = gmdate( 'Y-m-d', strtotime( '-1 day', strtotime( $bs_start_date ) ) );
    }

    // get agency details data between
    //     `financial year start date`
    // and
    //     `previous date from trial balance start date`
    $agency_details_balance = $wpdb->get_results( $wpdb->prepare( $sql, $closest_fy_date['start_date'], $prev_date_of_tb_start ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    $merged = array_merge( $result, $agency_details_balance );

    return erp_acct_clsbl_get_formatted_people_balance( $merged );
}

/**
 * @param int    $id
 * @param string $type
 *
 * @return void
 */
function erp_acct_clsbl_sales_tax_agency_opening_balance_by_fn_year_id( $id, $type ) {
    global $wpdb;

    if ( 'payable' === $type ) {
        $having = 'HAVING balance < 0';
    } elseif ( 'receivable' === $type ) {
        $having = 'HAVING balance > 0';
    }

    return $wpdb->get_results( "SELECT ledger_id AS id, SUM( debit - credit ) AS balance
    FROM {$wpdb->prefix}erp_acct_opening_balances
    WHERE type = 'tax_agency' GROUP BY ledger_id {$having}", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}
