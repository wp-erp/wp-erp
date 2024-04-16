<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once ERP_ACCOUNTING_INCLUDES . '/functions/reports/trial-balance.php';

/**
 * ===================================================
 * Ledger Report
 * ===================================================
 */

/**
 * get ledger report
 *
 * @param int    $ledger_id
 * @param string $start_date
 * @param string $end_date
 *
 * @return mixed
 */
function erp_acct_get_ledger_report( $ledger_id, $start_date, $end_date ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = (float) erp_acct_ledger_report_opening_balance_by_fn_year_id( $closest_fy_date['id'], $ledger_id );

    // should we go further calculation, check the diff
    if ( erp_acct_has_date_diff( $start_date, $closest_fy_date['start_date'] ) ) {
        $prev_date_of_start = gmdate( 'Y-m-d', strtotime( '-1 day', strtotime( $start_date ) ) );

        $prev_ledger_details = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(debit - credit) AS balance
            FROM {$wpdb->prefix}erp_acct_ledger_details
            WHERE ledger_id = %d AND trn_date BETWEEN %s AND %s ORDER BY trn_date ASC",
            $ledger_id,
            $closest_fy_date['start_date'],
            $prev_date_of_start
        ) );
        $opening_balance += (float) $prev_ledger_details;
    }

    $raw_opening_balance = $opening_balance;

    // ledger details
    $wpdb->query( "SET SESSION sql_mode='';" );

    $details = $wpdb->get_results( $wpdb->prepare(
        "SELECT
        trn_no, particulars, debit, credit, trn_date, created_at
        FROM {$wpdb->prefix}erp_acct_ledger_details
        WHERE ledger_id = %d AND trn_date BETWEEN %s AND %s ORDER BY trn_date ASC",
        $ledger_id,
        $start_date,
        $end_date
    ), ARRAY_A );

    $total_debit  = 0;
    $total_credit = 0;

    foreach ( $details as $key => $detail ) {
        $total_debit += (float) $detail['debit'];
        $total_credit += (float) $detail['credit'];

        if ( '0.00' === $detail['debit'] ) {
            // so we're working with credit
            $opening_balance = $opening_balance + ( - (float) $detail['credit'] );

            // after calculation with credit
            if ( $opening_balance >= 0 ) {
                // opening balance is positive
                $details[ $key ]['balance'] = $opening_balance . ' Dr';
            } elseif ( $opening_balance < 0 ) {
                // opening balance is negative
                $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
            }
        }

        if ( '0.00' === $detail['credit'] ) {
            // so we're working with debit
            $opening_balance = $opening_balance + (float) $detail['debit'];

            // after calculation with debit
            if ( $opening_balance >= 0 ) {
                // opening balance is positive
                $details[ $key ]['balance'] = $opening_balance . ' Dr';
            } elseif ( $opening_balance < 0 ) {
                // opening balance is negative
                $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
            }
        }
    }

    // Assign opening balance as first row
    if ( (float) $raw_opening_balance > 0 ) {
        $balance = $raw_opening_balance . ' Dr';
    } elseif ( (float) $raw_opening_balance < 0 ) {
        $balance = abs( $raw_opening_balance ) . ' Cr';
    } else {
        $balance = '0 Dr';
    }

    array_unshift(
        $details,
        [
            'trn_no'      => null,
            'particulars' => 'Opening Balance =',
            'debit'       => null,
            'credit'      => null,
            'trn_date'    => $start_date,
            'balance'     => $balance,
            'created_at'  => null,
        ]
    );

    return [
        'details' => $details,
        'extra'   => [
            'total_debit'  => $total_debit,
            'total_credit' => $total_credit,
        ],
    ];
}

/**
 * Ledger report opening balance helper
 *
 * @param $id
 * @param $ledger_id
 *
 * @return string|null
 */
function erp_acct_ledger_report_opening_balance_by_fn_year_id( $id, $ledger_id ) {
    global $wpdb;

    return $wpdb->get_var( $wpdb->prepare( "SELECT SUM(debit - credit) AS balance FROM {$wpdb->prefix}erp_acct_opening_balances
    WHERE financial_year_id = %d AND ledger_id = %d AND type = 'ledger' GROUP BY ledger_id", $id, $ledger_id ) );
}

/**
 * ===================================================
 * Sales Tax Report
 * ===================================================
 */

/**
 * get sales tax report
 *
 * @param int    $agency_id
 * @param string $start_date
 * @param string $end_date
 *
 * @return mixed
 */
function erp_acct_get_sales_tax_report( $agency_id, $start_date, $end_date ) {
    global $wpdb;

    // opening balance
    $db_opening_balance = $wpdb->get_var( $wpdb->prepare(
        "SELECT SUM(debit - credit) AS opening_balance
        FROM {$wpdb->prefix}erp_acct_tax_agency_details
        WHERE agency_id = %d AND trn_date < %s",
        $agency_id,
        $start_date
    ) );
    $opening_balance    = (float) $db_opening_balance;

    // agency details
    $details = $wpdb->get_results( $wpdb->prepare( "SELECT trn_no, particulars, debit, credit, trn_date, created_at FROM {$wpdb->prefix}erp_acct_tax_agency_details WHERE agency_id = %d AND trn_date BETWEEN %s AND %s", $agency_id, $start_date, $end_date ), ARRAY_A );

    $total_debit  = 0;
    $total_credit = 0;

    // Please refactor me
    foreach ( $details as $key => $detail ) {
        $total_debit += (float) $detail['debit'];
        $total_credit += (float) $detail['credit'];

        if ( '0.00' === $detail['debit'] ) {
            // so we're working with credit
            if ( $opening_balance < 0 ) {
                // opening balance is negative
                $opening_balance            = $opening_balance + ( - (float) $detail['credit'] );
                $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + ( - (float) $detail['credit'] );

                // after calculation with credit
                if ( $opening_balance >= 0 ) {
                    $details[ $key ]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
                }
            } else {
                // opening balance is 0
                $details[ $key ]['balance'] = '0 Dr';
            }
        }

        if ( '0.00' === $detail['credit'] ) {
            // so we're working with debit

            if ( $opening_balance < 0 ) {
                // opening balance is negative
                $opening_balance            = $opening_balance + (float) $detail['debit'];
                $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (float) $detail['debit'];

                // after calculation with debit
                if ( $opening_balance >= 0 ) {
                    $details[ $key ]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[ $key ]['balance'] = abs( $opening_balance ) . ' Cr';
                }
            } else {
                // opening balance is 0
                $details[ $key ]['balance'] = '0 Dr';
            }
        }
    }

    // Assign opening balance as first row
    if ( (float) $db_opening_balance > 0 ) {
        $balance = $db_opening_balance . ' Dr';
    } elseif ( (float) $db_opening_balance < 0 ) {
        $balance = abs( $db_opening_balance ) . ' Cr';
    } else {
        $balance = '0 Dr';
    }

    array_unshift(
        $details,
        [
            'trn_no'      => null,
            'particulars' => 'Opening Balance =',
            'debit'       => null,
            'credit'      => null,
            'trn_date'    => $start_date,
            'balance'     => $balance,
            'created_at'  => null,
        ]
    );

    return [
        'details' => $details,
        'extra'   => [
            'total_debit'  => $total_debit,
            'total_credit' => $total_credit,
        ],
    ];
}

/**
 * Generates filter wise sales tax report
 *
 * @since 1.10.0
 *
 * @param array $args
 *
 * @return array
 */
function erp_acct_get_filtered_sales_tax_report( $args ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) || empty( $args['end_date'] ) ) {
        return [];
    }

    $sql['from']  = "{$wpdb->prefix}erp_acct_invoices AS inv";
    $sql['where'] = "inv.trn_date BETWEEN '%s' AND '%s'";
    $sql['extra'] = '';
    $values       = [ $args['start_date'], $args['end_date'] ];

    if ( ! empty( $args['customer_id'] ) ) {

        $sql['select'] = 'inv.trn_date, inv.voucher_no, inv.tax AS tax_amount, inv.customer_id, inv.customer_name';
        $sql['where'] .= " AND inv.tax > 0 AND inv.customer_id = %d";
        $values[]      = $args['customer_id'];

    } else if ( ! empty( $args['category_id'] ) ) {

        $sql['select'] = 'inv.trn_date, details.trn_no AS voucher_no, sum(details.tax) AS tax_amount, details.tax_cat_id';
        $sql['from']  .= " RIGHT JOIN {$wpdb->prefix}erp_acct_invoice_details AS details ON inv.voucher_no = details.trn_no";
        $sql['where'] .= " AND details.tax > 0 AND details.tax_cat_id = %d";
        $sql['extra'] .= "GROUP BY details.trn_no";
        $values[]      = $args['category_id'];

    } else {

        $sql['select'] = 'inv.trn_date, inv.voucher_no, inv.tax AS tax_amount';
        $sql['where'] .= " AND inv.tax > 0";

    }

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT {$sql['select']} FROM {$sql['from']} WHERE {$sql['where']} {$sql['extra']}",
            $values
        ),
        ARRAY_A
    );
}

/**
 * ===================================================
 * Income Statement
 * ===================================================
 */

/**
 * Get income statement
 */
function erp_acct_get_income_statement( $args ) {
    global $wpdb;

    $results = erp_acct_get_profit_loss( $args );

    if ( $results['income'] >= abs( $results['expense'] ) ) {
        $results['profit']      = $results['income'] - $results['expense'];
        $results['raw_balance'] = $results['profit'];
    } else {
        $results['loss']        = $results['income'] - $results['expense'];
        $results['raw_balance'] = $results['loss'];
    }

    $results['balance'] = isset( $results['profit'] ) ? $results['profit'] : $results['loss'];

    return $results;
}

/**
 * Income statement with opening balance helper
 *
 * @param $bs_start_date
 * @param $data
 * @param $sql
 * @param $chart_id
 *
 * @return array
 */
function erp_acct_income_statement_calculate_with_opening_balance( $is_start_date, $data, $sql, $chart_id ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $is_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_is_opening_balance_by_fn_year_id( $closest_fy_date['id'], $chart_id );

    $ledgers   = $wpdb->get_results( $wpdb->prepare( "SELECT ledger.id, ledger.name FROM {$wpdb->prefix}erp_acct_ledgers AS ledger WHERE ledger.chart_id = %d", $chart_id ), ARRAY_A );
    $temp_data = erp_acct_get_is_balance_with_opening_balance( $ledgers, $data, $opening_balance );
    $result    = [];

    if ( ! erp_acct_has_date_diff( $is_start_date, $closest_fy_date['start_date'] ) ) {
        return $temp_data;
    } else {
        $prev_date_of_tb_start = gmdate( 'Y-m-d', strtotime( '-1 day', strtotime( $is_start_date ) ) );
    }

    // should we go further calculation, check the diff
    $date1    = date_create( $is_start_date );
    $date2    = date_create( $closest_fy_date['start_date'] );
    $interval = date_diff( $date1, $date2 );

    // if difference is `0` OR `1` day
    if ( '2' > $interval->format( '%a' ) ) {
        return $temp_data;
    } else {
        // get previous date from balance sheet start date
        $date_before_balance_sheet_start = gmdate( 'Y-m-d', strtotime( '-1 day', strtotime( $is_start_date ) ) );
        $is_date                         = $date_before_balance_sheet_start;
    }

    // get ledger details data between `financial year start date` and `previous date from balance sheet start date`
    $ledger_details = $wpdb->get_results( $wpdb->prepare( $sql, $closest_fy_date['start_date'], $is_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    foreach ( $temp_data as $temp ) {
        $balance = $temp['balance'];

        foreach ( $ledger_details as $detail ) {
            if ( $temp['id'] === $detail['id'] ) {
                $balance += (float) $detail['balance'];
            }
        }

        $result[] = [
            'id'      => $temp['id'],
            'name'    => $temp['name'],
            'balance' => $balance,
        ];
    }

    return $result;
}

/**
 * Get income statement ledger balance with opening balance
 *
 * @param array $ledgers
 * @param array $data
 * @param array $opening_balance
 *
 * @return array
 */
function erp_acct_get_is_balance_with_opening_balance( $ledgers, $data, $opening_balance ) {
    $temp_data = [];

    foreach ( $ledgers as $ledger ) {
        $balance = 0;

        foreach ( $data as $row ) {
            if ( $row['balance'] && $row['id'] === $ledger['id'] ) {
                $balance += (float) abs( $row['balance'] );
            }
        }

        foreach ( $opening_balance as $op_balance ) {
            if ( $op_balance['id'] === $ledger['id'] ) {
                $balance += (float) abs( $op_balance['balance'] );
            }
        }

        if ( $balance ) {
            $temp_data[] = [
                'id'      => $ledger['id'],
                'name'    => $ledger['name'],
                'balance' => $balance,
            ];
        }
    }

    return $temp_data;
}

/**
 * Get income statement opening balance data by financial year id
 *
 * @param int $id
 * @param int $chart_id ( optional )
 *
 * @return array
 */
function erp_acct_is_opening_balance_by_fn_year_id( $id, $chart_id ) {
    global $wpdb;

    $where = '';

    if ( $chart_id ) {
        $where = $wpdb->prepare( 'AND ledger.chart_id = %d', $chart_id );
    }

    return $wpdb->get_results( $wpdb->prepare( "SELECT ledger.id, ledger.name, SUM(opb.debit - opb.credit) AS balance
    FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
    LEFT JOIN {$wpdb->prefix}erp_acct_opening_balances AS opb ON ledger.id = opb.ledger_id
    WHERE opb.financial_year_id = %d {$where} AND opb.type = 'ledger' AND ledger.slug <> 'owner_s_equity'
    GROUP BY opb.ledger_id", $id ), ARRAY_A );
}

/**
 * ===================================================
 * Balance Sheet
 * ===================================================
 */

/**
 * Get balance sheet
 *
 * @param $args
 *
 * @return mixed
 */
function erp_acct_get_balance_sheet( $args ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) ) {
        $args['start_date'] = gmdate( 'Y-m-d', strtotime( 'first day of this month' ) );
    }

    if ( empty( $args['end_date'] ) ) {
        $args['end_date'] = gmdate( 'Y-m-d', strtotime( 'last day of this month' ) );
    }

    if ( empty( $args['start_date'] ) && empty( $args['end_date'] ) ) {
        $args['start_date'] = gmdate( 'Y-m-d', strtotime( 'first day of this month' ) );
        $args['end_date']   = gmdate( 'Y-m-d', strtotime( 'last day of this month' ) );
    }

    $sql1 = "SELECT
        ledger.id,
        ledger.name,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=1 AND ledger_detail.trn_date BETWEEN '%s' AND '%s'
        GROUP BY ledger_detail.ledger_id";

    $sql2 = "SELECT
        ledger.id,
        ledger.name,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=2 AND ledger_detail.trn_date BETWEEN '%s' AND '%s'
        GROUP BY ledger_detail.ledger_id";

    $sql3 = "SELECT
        ledger.id,
        ledger.name,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=3 AND ledger.slug <> 'owner_s_equity' AND ledger_detail.trn_date BETWEEN '%s' AND '%s'
        GROUP BY ledger_detail.ledger_id";

    // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
    $data1 = $wpdb->get_results( $wpdb->prepare( $sql1, $args['start_date'], $args['end_date'] ), ARRAY_A );
    $data2 = $wpdb->get_results( $wpdb->prepare( $sql2, $args['start_date'], $args['end_date'] ), ARRAY_A );
    $data3 = $wpdb->get_results( $wpdb->prepare( $sql3, $args['start_date'], $args['end_date'] ), ARRAY_A );
    // phpcs:enable

    $results['rows1'] = erp_acct_balance_sheet_calculate_with_opening_balance( $args['start_date'], $data1, $sql1, 1 );
    $results['rows2'] = erp_acct_balance_sheet_calculate_with_opening_balance( $args['start_date'], $data2, $sql2, 2 );
    $results['rows3'] = erp_acct_balance_sheet_calculate_with_opening_balance( $args['start_date'], $data3, $sql3, 3 );

    $final_accounts   = new \WeDevs\ERP\Accounting\Classes\FinalAccounts( $args );

    $results['rows1'][] = [
        'name'    => 'Accounts Receivable',
        'balance' => erp_acct_get_account_receivable( $args ),
    ];

    $results['rows1'][] = [
        'name'    => 'Sales Tax Receivable',
        'slug'    => 'sales_tax',
        'balance' => erp_acct_sales_tax_query( $args, 'receivable' ),
    ];

    $results['rows1'][] = [
        'name'       => 'Cash at Bank',
        'balance'    => $final_accounts->cash_at_bank,
        'additional' => $final_accounts->cash_at_bank_breakdowns,
    ];

    $results['rows2'][] = [
        'name'    => 'Accounts Payable',
        'balance' => erp_acct_get_account_payable( $args ),
    ];

    $results['rows2'][] = [
        'name'       => 'Bank Loan',
        'balance'    => $final_accounts->loan_at_Bank,
        'additional' => $final_accounts->loan_at_bank_breakdowns,
    ];

    $results['rows2'][] = [
        'name'    => 'Sales Tax Payable',
        'slug'    => 'sales_tax',
        'balance' => erp_acct_sales_tax_query( $args, 'payable' ),
    ];

    $ledger_map        = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();
    $owner_s_equity_id = $ledger_map->get_ledger_id_by_slug( 'owner_s_equity' );

    $capital     = erp_acct_get_owners_equity( $args, 'capital' );
    $drawings    = erp_acct_get_owners_equity( $args, 'drawings' );
    $new_capital = $capital + $drawings;

    $closest_fy_date       = erp_acct_get_closest_fn_year_date( $args['start_date'] );
    $prev_date_of_tb_start = gmdate( 'Y-m-d', strtotime( '-1 day', strtotime( $args['start_date'] ) ) );

    // Owner's Equity calculation with income statement profit/loss
    $inc_statmnt_range = [
        'start_date' => $closest_fy_date['start_date'],
        'end_date'   => $prev_date_of_tb_start,
    ];

    $income_statement_balance = erp_acct_get_income_statement( $inc_statmnt_range );

    $new_capital = $new_capital - $income_statement_balance['raw_balance'];

    if ( 0 < $new_capital ) {
        $results['rows3'][] = [
            'id'      => $owner_s_equity_id,
            'name'    => 'Owner\'s Drawings',
            'balance' => $new_capital,
        ];
    } else {
        $results['rows3'][] = [
            'id'      => $owner_s_equity_id,
            'name'    => 'Owner\'s Capital',
            'balance' => $new_capital,
        ];
    }

    $profit_loss = erp_acct_get_income_statement( $args );

    if ( ! empty( $profit_loss['profit'] ) ) {
        $results['rows3'][] = [
            'name'    => 'Profit',
            'slug'    => 'profit',
            'balance' => -$profit_loss['profit'],
        ];
    }

    if ( ! empty( $profit_loss['loss'] ) ) {
        $results['rows3'][] = [
            'name'    => 'Loss',
            'slug'    => 'loss',
            'balance' => -$profit_loss['loss'],
        ];
    }

    $results['total_asset']     = 0;
    $results['total_equity']    = 0;
    $results['total_liability'] = 0;

    foreach ( $results['rows1'] as $result ) {
        if ( ! is_numeric( $result['balance'] ) ) {
            continue;
        }

        if ( ! empty( $result['balance'] ) ) {
            $results['total_asset'] += (float) $result['balance'];
        }
    }

    foreach ( $results['rows2'] as $result ) {
        if ( ! is_numeric( $result['balance'] ) ) {
            continue;
        }

        if ( ! empty( $result['balance'] ) ) {
            $results['total_liability'] += (float) $result['balance'];
        }
    }

    foreach ( $results['rows3'] as $result ) {
        if ( isset( $results['slug'] ) && 'loss' !== $results['slug'] ) {
            $result['balance'] = abs( $result['balance'] );
        }

        if ( ! empty( $result['balance'] ) ) {
            if ( ! is_numeric( (float) $result['balance'] ) ) {
                continue;
            }
            $results['total_equity'] += (float) $result['balance'];
        }
    }

    $profit = 0;
    $loss   = 0;

    if ( ! empty( $profit_loss['profit'] ) ) {
        $profit = $profit_loss['profit'];
    } elseif ( ! empty( $profit_loss['loss'] ) ) {
        $loss = $profit_loss['loss'];
    }

    $results['owners_equity'] = abs( $capital ) - abs( $drawings ) + abs( $profit ) - abs( $loss );

    return $results;
}

/**
 * Balance sheet with opening balance helper
 *
 * @param $bs_start_date
 * @param $data
 * @param $sql
 * @param $chart_id
 *
 * @return array
 */
function erp_acct_balance_sheet_calculate_with_opening_balance( $bs_start_date, $data, $sql, $chart_id ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $bs_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_bs_opening_balance_by_fn_year_id( $closest_fy_date['id'], $chart_id );

    $ledgers   = $wpdb->get_results( $wpdb->prepare( "SELECT
    ledger.id, ledger.name
    FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
    WHERE ledger.chart_id=%d AND ledger.slug <> 'owner_s_equity'", $chart_id ), ARRAY_A );
    $temp_data = erp_acct_get_bs_balance_with_opening_balance( $ledgers, $data, $opening_balance );
    $result    = [];

    if ( ! erp_acct_has_date_diff( $bs_start_date, $closest_fy_date['start_date'] ) ) {
        return $temp_data;
    } else {
        $prev_date_of_tb_start = gmdate( 'Y-m-d', strtotime( '-1 day', strtotime( $bs_start_date ) ) );
    }

    // should we go further calculation, check the diff
    $date1    = date_create( $bs_start_date );
    $date2    = date_create( $closest_fy_date['start_date'] );
    $interval = date_diff( $date1, $date2 );

    // if difference is `0` OR `1` day
    if ( '2' > $interval->format( '%a' ) ) {
        return $temp_data;
    } else {
        // get previous date from balance sheet start date
        $date_before_balance_sheet_start = gmdate( 'Y-m-d', strtotime( '-1 day', strtotime( $bs_start_date ) ) );
        $bs_date                         = $date_before_balance_sheet_start;
    }

    // get ledger details data between `financial year start date` and `previous date from balance sheet start date`
    $ledger_details = $wpdb->get_results( $wpdb->prepare( $sql, $closest_fy_date['start_date'], $bs_date ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    foreach ( $temp_data as $temp ) {
        $balance = $temp['balance'];

        foreach ( $ledger_details as $detail ) {
            if ( $temp['id'] === $detail['id'] ) {
                $balance += (float) $detail['balance'];
            }
        }

        $result[] = [
            'id'      => $temp['id'],
            'name'    => $temp['name'],
            'balance' => $balance,
        ];
    }

    return $result;
}

/**
 * Get ledger balance with opening balance
 *
 * @param array $ledgers
 * @param array $data
 * @param array $opening_balance
 *
 * @return array
 */
function erp_acct_get_bs_balance_with_opening_balance( $ledgers, $data, $opening_balance ) {
    $temp_data = [];

    foreach ( $ledgers as $ledger ) {
        $balance = 0;

        foreach ( $data as $row ) {
            if ( $row['balance'] && $row['id'] === $ledger['id'] ) {
                $balance += (float) $row['balance'];
            }
        }

        foreach ( $opening_balance as $op_balance ) {
            if ( $op_balance['id'] === $ledger['id'] ) {
                $balance += (float) $op_balance['balance'];
            }
        }

        if ( $balance ) {
            $temp_data[] = [
                'id'      => $ledger['id'],
                'name'    => $ledger['name'],
                'balance' => $balance,
            ];
        }
    }

    return $temp_data;
}

/**
 * Get opening balance data by financial year id
 *
 * @param int $id
 * @param int $chart_id ( optional )
 *
 * @return array
 */
function erp_acct_bs_opening_balance_by_fn_year_id( $id, $chart_id ) {
    global $wpdb;

    $where = '';

    if ( $chart_id ) {
        $where = $wpdb->prepare( 'AND ledger.chart_id = %d', $chart_id );
    }

    return $wpdb->get_results( $wpdb->prepare( "SELECT ledger.id, ledger.name, SUM(opb.debit - opb.credit) AS balance
    FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
    LEFT JOIN {$wpdb->prefix}erp_acct_opening_balances AS opb ON ledger.id = opb.ledger_id
    WHERE opb.financial_year_id = %d {$where} AND opb.type = 'ledger' AND ledger.slug <> 'owner_s_equity'
    GROUP BY opb.ledger_id", $id ), ARRAY_A );
}

/**
 * Get profit-loss
 *
 * @param $args
 *
 * @return array
 */
function erp_acct_get_profit_loss( $args ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) ) {
        $args['start_date'] = gmdate( 'Y-m-d', strtotime( 'first day of january' ) );
    } else {
        $closest_fy_date    = erp_acct_get_closest_fn_year_date( $args['start_date'] );
        $args['start_date'] = $closest_fy_date['start_date'];
    }

    if ( empty( $args['end_date'] ) ) {
        $args['end_date'] = gmdate( 'Y-m-d', strtotime( 'last day of this month' ) );
    }

    if ( empty( $args['start_date'] ) && empty( $args['end_date'] ) ) {
        $args['start_date'] = gmdate( 'Y-m-d', strtotime( 'first day of january' ) );
        $args['end_date']   = gmdate( 'Y-m-d', strtotime( 'last day of this month' ) );
    }

    $sql1 = "SELECT
        ledger.id,
        ledger.name,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=4 AND ledger_detail.trn_date BETWEEN '%s' AND '%s'
        GROUP BY ledger_detail.ledger_id";

    $sql2 = "SELECT
        ledger.id,
        ledger.name,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=5 AND ledger_detail.trn_date BETWEEN '%s' AND '%s'
        GROUP BY ledger_detail.ledger_id";

    // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
    $data1 = $wpdb->get_results( $wpdb->prepare( $sql1, $args['start_date'], $args['end_date'] ), ARRAY_A );
    $data2 = $wpdb->get_results( $wpdb->prepare( $sql2, $args['start_date'], $args['end_date'] ), ARRAY_A );
    // phpcs:enable

    $results['rows1'] = erp_acct_income_statement_calculate_with_opening_balance( $args['start_date'], $data1, $sql1, 4 );
    $results['rows2'] = erp_acct_income_statement_calculate_with_opening_balance( $args['start_date'], $data2, $sql2, 5 );

    $results['income']  = 0;
    $results['expense'] = 0;

    foreach ( $results['rows1'] as $result ) {
        $results['income'] += (float) $result['balance'];
    }

    foreach ( $results['rows2'] as $result ) {
        $results['expense'] += (float) $result['balance'];
    }

    return $results;
}
