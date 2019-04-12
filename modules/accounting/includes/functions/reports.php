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
 * Trial balance helper
 *
 * @param array $args
 *
 * @return int
 */
function erp_acct_trial_balance_cash_at_bank( $args, $type ) {
    global $wpdb;

    if ( 'loan' === $type ) {
        $having = "HAVING balance < 0";
    } elseif ( 'balance' === $type ) {
        $having = "HAVING balance > 0";
    }

    $chart_bank = 7;

    $sql1 = $wpdb->prepare("SELECT group_concat(id) FROM {$wpdb->prefix}erp_acct_ledgers where chart_id = %d", $chart_bank);
    $ledger_ids = implode( ',', explode( ',', $wpdb->get_var($sql1) ) ); // e.g. 4, 5

    if ( $ledger_ids ) {
        $sql2 = $wpdb->prepare("SELECT SUM(ledger_details.balance) as balance from (SELECT SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_ledger_details
            WHERE ledger_id IN ({$ledger_ids}) AND trn_date BETWEEN '%s' AND '%s' GROUP BY ledger_id {$having}) AS ledger_details",
            $args['start_date'], $args['end_date']);

        return $wpdb->get_var($sql2);
    }

    return null;
}

/**
 * Trial balance helper
 *
 * @param array $args
 *
 * @return int
 */
function erp_acct_trial_balance_bank_balance( $args, $type ) {
    global $wpdb;

    if ( 'loan' === $type ) {
        $having = "HAVING balance < 0";
    } elseif ( 'balance' === $type ) {
        $having = "HAVING balance > 0";
    }

    $chart_bank = 7;

    $sql = $wpdb->prepare("SELECT ledger.id, ledger.name, SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id
        WHERE ledger.chart_id = %d AND trn_date BETWEEN '%s' AND '%s' GROUP BY ledger.id {$having}",
        $chart_bank, $args['start_date'], $args['end_date']);

    return $wpdb->get_results($sql);
}

/**
 * Trial balance helper
 *
 * @param array $args
 * @param string $type
 *
 * @return int
 */
function erp_acct_trial_balance_sales_tax_query( $args, $type = 'payable' ) {
    global $wpdb;

    if ( 'payable' === $type ) {
        $having = "HAVING balance < 0";
    } elseif ( 'receivable' === $type ) {
        $having = "HAVING balance > 0";
    }

    $sql = $wpdb->prepare("SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_tax_agency_details WHERE trn_date BETWEEN '%s' AND '%s'
            GROUP BY agency_id {$having} ) AS get_amount",
        $args['start_date'], $args['end_date']);

    return $wpdb->get_var($sql);
}

/**
 * Trial balance helper
 *
 * Get account receivable
 */
function erp_acct_get_account_receivable( $args ) {
    global $wpdb;

    // mainly ( debit - credit )
    $sql = $wpdb->prepare("SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance
            FROM {$wpdb->prefix}erp_acct_invoice_account_details WHERE trn_date BETWEEN '%s' AND '%s'
            GROUP BY invoice_no HAVING balance > 0 )
        AS get_amount", $args['start_date'], $args['end_date']);

    return $wpdb->get_var($sql);
}

/**
 * Trial balance helper
 *
 * Get account payble
 */
function erp_acct_get_account_payable( $args ) {
    global $wpdb;

    /**
     *? Why only bills, not expense?
     *? Expense is `direct expense`, and we don't include direct expense here
     */
    $bill_sql = $wpdb->prepare("SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_bill_account_details WHERE trn_date BETWEEN '%s' AND '%s'
            GROUP BY bill_no HAVING balance < 0 )
        AS get_amount", $args['start_date'], $args['end_date']);

    $purchase_sql = $wpdb->prepare("SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_purchase_account_details WHERE trn_date BETWEEN '%s' AND '%s'
            GROUP BY purchase_no HAVING balance < 0 )
        AS get_amount", $args['start_date'], $args['end_date']);

    $bill_amount = $wpdb->get_var($bill_sql);
    $purchase_amount = $wpdb->get_var($purchase_sql);

    return $bill_amount + $purchase_amount;
}


/**
 * Trial balance helper
 *
 * Get owners equity
 */
function erp_acct_get_owners_equity( $args, $type ) {
    global $wpdb;

    if ( 'capital' === $type ) {
        $having = "HAVING balance < 0";
    } elseif ( 'drawings' === $type ) {
        $having = "HAVING balance > 0";
    }

    $owners_equity = 30;

    $sql = $wpdb->prepare("SELECT SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id
        WHERE ledger.id = %d AND trn_date BETWEEN '%s' AND '%s' GROUP BY ledger.id {$having}",
        $owners_equity, $args['start_date'], $args['end_date']);

    return $wpdb->get_var($sql);
}

/**
 * Get trial balance
 *
 * @return mixed
 */
function erp_acct_get_trial_balance( $args ) {
    global $wpdb;

    $sql = "SELECT
        ledger.id, ledger.name, SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id
        WHERE ledger.chart_id <> 7 AND ledger.slug <> 'owner_s_equity' AND ledger_detail.trn_date BETWEEN '%s' AND '%s' GROUP BY ledger_detail.ledger_id";

    $data = $wpdb->get_results( $wpdb->prepare($sql, $args['start_date'], $args['end_date']), ARRAY_A );

    // All calculated DB results are inside `rows` key
    $results['rows'] = erp_acct_trial_balance_calculate_with_opening_balance( $args['start_date'], $data, $sql );

    /**
     * Let's create some virtual ledgers
     */

    $results['rows'][] = [
        'name'       => 'Cash at Bank',
        'balance'    => erp_acct_trial_balance_cash_at_bank( $args, 'balance' ),
        'additional' => erp_acct_trial_balance_bank_balance( $args, 'balance' )
    ];
    $results['rows'][] = [
        'name'       => 'Bank Loan',
        'balance'    => erp_acct_trial_balance_cash_at_bank( $args, 'loan' ),
        'additional' => erp_acct_trial_balance_bank_balance( $args, 'loan' )
    ];

    $results['rows'][] = [
        'name'    => 'Sales Tax Payable',
        'balance' => erp_acct_trial_balance_sales_tax_query( $args, 'payable' )
    ];
    $results['rows'][] = [
        'name'    => 'Sales Tax Receivable',
        'balance' => erp_acct_trial_balance_sales_tax_query( $args, 'receivable' )
    ];

    $results['rows'][] = [
        'name'    => 'Accounts Payable',
        'balance' => erp_acct_get_account_payable( $args )
    ];
    $results['rows'][] = [
        'name'    => 'Accounts Receivable',
        'balance' => erp_acct_get_account_receivable( $args )
    ];

    $results['rows'][] = [
        'name'    => 'Owner\'s Capital',
        'balance' => erp_acct_get_owners_equity( $args, 'capital' )
    ];
    $results['rows'][] = [
        'name'    => 'Owner\'s Drawings',
        'balance' => erp_acct_get_owners_equity( $args, 'drawings' )
    ];

    // Totals are inside the root `result` array
    $results['total_debit']  = 0;
    $results['total_credit'] = 0;

    // Add-up all debit and credit
    foreach ( $results['rows'] as $key => $result ) {
        if ( ! empty($result['balance']) ) {
            if ( $result['balance'] > 0 ) {
                $results['total_debit'] += $result['balance'];
            } else {
                $results['total_credit'] += $result['balance'];
            }
        } else {
            unset( $results['rows'][$key] );
        }
    }

    /**
     * `unset-converts-array-into-object`
     *
     * In JSON, arrays always start at index 0.
     * So if in PHP you remove element 0, the array starts at 1.
     * But this cannot be represented in array notation in JSON.
     * So it is represented as an object, which supports key/value pairs.
     * To make JSON represent the data as an array, you must ensure that the array starts at index 0 and has no gaps.
     *
     * Re-index object to make it array again
     */
    $results['rows'] = array_values( $results['rows'] );

    return $results;
}

/**
 * Get trial balance calculate with_opening balance within financial year date range
 *
 * @param string $tb_start_date
 * @param array $data => ledger details data on trial balance date range
 * @param string $sql
 *
 * @return array
 */
function erp_acct_trial_balance_calculate_with_opening_balance( $tb_start_date, $data, $sql ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $tb_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_opening_balance_by_fn_year_id( $closest_fy_date['id'] );

    $temp_data = [];
    $result    = [];

    $ledger_sql = "SELECT ledger.id, ledger.name FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        WHERE ledger.chart_id <> 7 AND ledger.slug <> 'owner_s_equity'";

    $ledgers = $wpdb->get_results( $ledger_sql, ARRAY_A );

    /**
     * Start writing a very `inefficient :(` foreach loop
     */
    foreach ( $ledgers as $ledger ) {
        $balance = 0;

        foreach ( $data as $row ) {
            if ( $row['id'] == $ledger['id'] ) {
                $balance += (float) $row['balance'];
            }
        }

        foreach ( $opening_balance as $op_balance ) {
            if ( $op_balance['id'] == $ledger['id'] ) {
                $balance += (float) $op_balance['balance'];
            }
        }

        $temp_data[] = [
            'id'      => $ledger['id'],
            'name'    => $ledger['name'],
            'balance' => $balance
        ];
    }

    // should we go further calculation, check the diff
    $date1    = date_create($tb_start_date);
    $date2    = date_create($closest_fy_date['start_date']);
    $interval = date_diff($date1, $date2);

    // if difference is `0` OR `1` day
    if ( '2' > $interval->format('%a') ) {
        return $temp_data;
    } else {
        // get previous date from trial balance start date
        $date_before_trial_balance_start = date( 'Y-m-d', strtotime( '-1 day', strtotime($tb_start_date) ) );
        $tb_date = $date_before_trial_balance_start;
    }

    // get ledger details data between
    //     `financial year start date`
    // and
    //     `previous date from trial balance start date`
    $ledger_details = $wpdb->get_results(
        $wpdb->prepare($sql, $closest_fy_date['start_date'], $tb_date), ARRAY_A
    );

    foreach ( $temp_data as $temp ) {
        $balance = $temp['balance'];

        foreach ( $ledger_details as $detail ) {
            if ( $temp['id'] == $detail['id'] ) {
                $balance += (float) $detail['balance'];
            }
        }

        $result[] = [
            'id'      => $temp['id'],
            'name'    => $temp['name'],
            'balance' => $balance
        ];
    }

    return $result;
}

/**
 * Get closest date from financial year
 *
 * @param string $date
 *
 * @return string
 */
function erp_acct_get_closest_fn_year_date( $date ) {
    global $wpdb;

    $sql = "SELECT id, start_date FROM {$wpdb->prefix}erp_acct_financial_years WHERE start_date <= '%s' ORDER BY start_date DESC LIMIT 1";

    return $wpdb->get_row( $wpdb->prepare($sql, $date), ARRAY_A );
}

/**
 * Get opening balance data by financial year id
 *
 * @param int $id
 *
 * @return string
 */
function erp_acct_opening_balance_by_fn_year_id( $id ) {
    global $wpdb;

    $sql = "SELECT ledger.id, ledger.name, SUM(opb.debit - opb.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_opening_balances AS opb ON ledger.id = opb.ledger_id
        WHERE opb.financial_year_id = %d GROUP BY opb.ledger_id";

    return $wpdb->get_results( $wpdb->prepare($sql, $id), ARRAY_A );
}

/**
 * Get diff date between financial year and trial balance
 *
 * @param string $fn_date
 * @param string $tb_date
 *
 * @return string
 */
// function erp_acct_get_diff_date_between_fn_year_and_trial_balance( $fn_date, $tb_date ) {

// }


/**
 * ===================================================
 * Ledger Report
 * ===================================================
 */

 /**
  * get ledger report
  *
  * @param int $ledger_id
  * @param string $start_date
  * @param string $end_date
  * @return mixed
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
                $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';

            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (-(float) $detail['credit']);

                // after calculation with credit
                if ( $opening_balance >= 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';
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
                $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';

            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (float) $detail['debit'];

                // after calculation with debit
                if ( $opening_balance >= 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';
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
        $balance = abs( $db_opening_balance ) . ' Cr';
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


/**
 * ===================================================
 * Sales Tax Report
 * ===================================================
 */

 /**
  * get sales tax report
  *
  * @param int $agency_id
  * @param string $start_date
  * @param string $end_date
  * @return mixed
  */
function erp_acct_get_sales_tax_report( $agency_id, $start_date, $end_date ) {
    global $wpdb;

    // opening balance
    $sql1 = $wpdb->prepare("SELECT SUM(debit - credit) AS opening_balance
        FROM {$wpdb->prefix}erp_acct_tax_agency_details
        WHERE agency_id = %d AND trn_date < '%s'",
        $agency_id, $start_date
    );

    $db_opening_balance = $wpdb->get_var( $sql1 );
    $opening_balance = (float) $db_opening_balance;

    // agency details
    $sql2 = $wpdb->prepare("SELECT
        trn_no, particulars, debit, credit, trn_date, created_at
        FROM {$wpdb->prefix}erp_acct_tax_agency_details
        WHERE agency_id = %d AND trn_date BETWEEN '%s' AND '%s'",
        $agency_id, $start_date, $end_date
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
                $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';

            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (-(float) $detail['credit']);

                // after calculation with credit
                if ( $opening_balance >= 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';
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
                $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';

            } elseif ( $opening_balance >= 0 ) {
                // opening balance is positive
                $opening_balance = $opening_balance + (float) $detail['debit'];

                // after calculation with debit
                if ( $opening_balance >= 0 ) {
                    $details[$key]['balance'] = $opening_balance . ' Dr';
                } elseif ( $opening_balance < 0 ) {
                    $details[$key]['balance'] = abs( $opening_balance ) . ' Cr';
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
        $balance = abs( $db_opening_balance ) . ' Cr';
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

    if ( empty( $args['start_date'] ) ) {
        $args['start_date'] = date('Y-m-d', strtotime('first day of this month') );
    }
    if ( empty( $args['end_date'] ) ) {
        $args['end_date'] = date('Y-m-d', strtotime('last day of this month') );
    }

    if ( empty( $args['start_date'] ) && empty( $args['end_date'] ) ) {
        $args['start_date'] = date('Y-m-d', strtotime('first day of this month') );
        $args['end_date'] = date('Y-m-d', strtotime('last day of this month') );
    }

    $sql1 = "SELECT
        ledger.name,
        SUM(ledger_detail.credit) as credit,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=4 AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
        GROUP BY ledger_detail.ledger_id";

    $sql2 = "SELECT
        ledger.name,
        SUM(ledger_detail.debit) as debit,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=5 AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
        GROUP BY ledger_detail.ledger_id";

    // All DB results are inside `rows` key
    $results['rows1'] = $wpdb->get_results($sql1, ARRAY_A);
    $results['rows2'] = $wpdb->get_results($sql2, ARRAY_A);

    // Totals are inside the root `result` array
    $results['total_debit'] = 0;
    $results['total_credit'] = 0;

    // Add-up all debit and credit
    foreach ($results['rows1'] as $result) {
        $results['total_credit'] += (float)$result['credit'];
    }
    foreach ($results['rows2'] as $result) {
        $results['total_debit']  += (float)$result['debit'];
    }

    $dr_cr_diff = $results['total_debit'] - $results['total_credit'];

    if ( abs( $results['total_debit'] ) <= abs( $results['total_credit'] ) ) {
        if ( $dr_cr_diff < 0 ) {
            $dr_cr_diff = - $dr_cr_diff;
        }
        $results['profit'] = $dr_cr_diff;
    } else {
        if ( $dr_cr_diff > 0 ) {
            $balance = - $dr_cr_diff;
        } else {
            $balance = - $dr_cr_diff;
        }
        $results['loss'] = $balance;
    }

    $results['balance'] = $dr_cr_diff;

    return $results;
}

/**
 * ===================================================
 * Balance Sheet
 * ===================================================
 */

/**
 * Get balance sheet
 */
function erp_acct_get_balance_sheet( $args ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) ) {
        $args['start_date'] = date('Y-m-d', strtotime('first day of this month') );
    }
    if ( empty( $args['end_date'] ) ) {
        $args['end_date'] = date('Y-m-d', strtotime('last day of this month') );
    }

    if ( empty( $args['start_date'] ) && empty( $args['end_date'] ) ) {
        $args['start_date'] = date('Y-m-d', strtotime('first day of this month') );
        $args['end_date'] = date('Y-m-d', strtotime('last day of this month') );
    }

    $sql1 = "SELECT
        ledger.name,
        ABS(SUM(ledger_detail.debit - ledger_detail.credit)) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=1 AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
        GROUP BY ledger_detail.ledger_id";

    $sql2 = "SELECT
        ledger.name,
        ABS(SUM(ledger_detail.debit - ledger_detail.credit)) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=2 AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
        GROUP BY ledger_detail.ledger_id";

    $sql3 = "SELECT
        ledger.name,
        ABS(SUM(ledger_detail.debit - ledger_detail.credit)) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=3 AND ledger.slug != 'owner_s_equity' AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
        GROUP BY ledger_detail.ledger_id";

    $results['rows1'] = $wpdb->get_results( $sql1, ARRAY_A );
    $results['rows2'] = $wpdb->get_results( $sql2, ARRAY_A );
    $results['rows3'] = $wpdb->get_results( $sql3, ARRAY_A );

    $results['rows1'][] = [
        'name' => 'Accounts Receivable',
        'balance' => erp_acct_get_account_receivable( $args )
    ];
    $results['rows1'][] = [
        'name'    => 'Cash at Bank',
        'balance' => erp_acct_trial_balance_cash_at_bank( $args, 'balance' )
    ];

    $results['rows2'][] = [
        'name' => 'Accounts Payable',
        'balance' => abs( erp_acct_get_account_payable( $args ) )
    ];
    $results['rows2'][] = [
        'name'    => 'Bank Loan',
        'balance' => abs( erp_acct_trial_balance_cash_at_bank( $args, 'loan' ) )
    ];

    $results['rows2'][] = [
        'name' => 'Sales Tax Payable',
        'slug' => 'sales_tax',
        'balance' => abs ( erp_acct_trial_balance_sales_tax_query( $args, 'payable' ) )
    ];

    $capital = erp_acct_get_owners_equity( $args, 'capital' );
    $drawings = erp_acct_get_owners_equity( $args, 'drawings' );

    $results['rows3'][] = [
        'name'    => 'Owner\'s Equity',
        'balance' => $capital - $drawings
    ];

    $profit_loss = erp_acct_get_profit_loss( $args );

    $dr_cr_diff = abs( $profit_loss['total_debit'] ) - abs( $profit_loss['total_credit'] );

    if ( abs( $profit_loss['total_debit'] ) <= abs( $profit_loss['total_credit'] ) ) {
        if ( $dr_cr_diff < 0 ) {
            $dr_cr_diff = - $dr_cr_diff;
        }
        $results['rows3'][] = [
            'name' => 'Profit',
            'slug' => 'profit',
            'balance' => $dr_cr_diff
        ];
    } else {
        if ( $dr_cr_diff > 0 ) {
            $balance = - $dr_cr_diff;
        } else {
            $dr_cr_diff = - $dr_cr_diff;
            $balance    = $dr_cr_diff;
        }
        $results['rows3'][] = [
            'name' => 'Loss',
            'slug' => 'loss',
            'balance' => $balance
        ];
    }

    $results['total_asset'] = 0;
    $results['total_equity'] = 0;
    $results['total_liability'] = 0;

    foreach ( $results['rows1'] as $result ) {
        if ( !is_numeric( $result['balance'] ) ) {
            continue;
        }

        if ( ! empty($result['balance']) ) {
            $results['total_asset'] += (float) $result['balance'];
        }
    }

    foreach ( $results['rows2'] as $result ) {
        if ( !is_numeric( $result['balance'] ) ) {
            continue;
        }

        if ( ! empty($result['balance']) ) {
            $results['total_liability'] += (float) $result['balance'];
        }
    }

    foreach ($results['rows3'] as $result) {
        if ( isset( $results['slug'] ) && $results['slug'] !== 'loss' ) {
            $result['balance'] = abs( $result['balance'] );
        }

        if ( ! empty($result['balance']) ) {
            if ( !is_numeric( (float) $result['balance'] ) ) {
                continue;
            }
            $results['total_equity'] += (float) $result['balance'];
        }
    }

    return $results;
}

/**
 * @param $results
 * @return array
 */
function erp_acct_get_profit_loss( $args ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) ) {
        $args['start_date'] = date('Y-m-d', strtotime('first day of this month') );
    }
    if ( empty( $args['end_date'] ) ) {
        $args['end_date'] = date('Y-m-d', strtotime('last day of this month') );
    }

    if ( empty( $args['start_date'] ) && empty( $args['end_date'] ) ) {
        $args['start_date'] = date('Y-m-d', strtotime('first day of this month') );
        $args['end_date'] = date('Y-m-d', strtotime('last day of this month') );
    }

    $sql = "SELECT
        ledger.name,
        SUM(ledger_detail.debit) as debit,
        SUM(ledger_detail.credit) as credit,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE (ledger.chart_id=4 OR ledger.chart_id=5) AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
        GROUP BY ledger_detail.ledger_id";

    $results['rows'] = $wpdb->get_results( $sql, ARRAY_A );

    $results['total_debit'] = 0;
    $results['total_credit'] = 0;

    foreach ( $results['rows'] as $result ) {
        $results['total_debit']  += (float)$result['debit'];
        $results['total_credit'] += (float)$result['credit'];
    }

    return $results;
}
