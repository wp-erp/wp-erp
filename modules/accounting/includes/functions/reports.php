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

    $sql2 = $wpdb->prepare("SELECT SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_ledger_details
        WHERE ledger_id IN ({$ledger_ids}) AND trn_date BETWEEN '%s' AND '%s' GROUP BY ledger_id {$having}",
        $args['start_date'], $args['end_date']);

    return $wpdb->get_var($sql2);
}

/**
 * Trial balance helper
 *
 * @param array $args
 * @param array $type
 *
 * @return int
 */
function erp_acct_trial_balance_sales_tax_query( $args, $type ) {
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
 * Get trial balance
 *
 * @return mixed
 */
function erp_acct_get_trial_balance( $args ) {
    global $wpdb;

    if ( empty( $args['start_date'] ) ) {
        $args['start_date'] = date('Y-m-d', strtotime('first day of this month') );
    }

    if ( empty( $args['end_date'] ) ) {
        $args['end_date'] = date('Y-m-d', strtotime('last day of this month') );
    }

    $sql = $wpdb->prepare("SELECT
        ledger.name,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id
        WHERE ledger_detail.trn_date BETWEEN '%s' AND '%s' GROUP BY ledger_detail.ledger_id",
        $args['start_date'], $args['end_date']
    );

    // All DB results are inside `rows` key
    $results['rows'] = $wpdb->get_results($sql, ARRAY_A);

    /**
     * Let's create some virtual ledgers
     */

    $results['rows'][] = [
        'name'    => 'Cash at Bank',
        'balance' => erp_acct_trial_balance_cash_at_bank( $args, 'balance' )
    ];
    $results['rows'][] = [
        'name'    => 'Bank Loan',
        'balance' => erp_acct_trial_balance_cash_at_bank( $args, 'loan' )
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

    $sql = "SELECT
        ledger.name,
        SUM(ledger_detail.debit) as debit,
        SUM(ledger_detail.credit) as credit,
        SUM(ledger_detail.debit - ledger_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE (ledger.chart_id=4 OR ledger.chart_id=5) AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
        GROUP BY ledger_detail.ledger_id";

    // All DB results are inside `rows` key
    $results['rows'] = $wpdb->get_results($sql, ARRAY_A);

    // Totals are inside the root `result` array
    $results['total_debit'] = 0;
    $results['total_credit'] = 0;

    // Add-up all debit and credit
    foreach ($results['rows'] as $result) {
        $results['total_debit']  += (float)$result['debit'];
        $results['total_credit'] += (float)$result['credit'];
    }

    $dr_cr_diff = abs( $results['total_debit'] ) - abs( $results['total_credit'] );

    if ( abs( $results['total_debit'] ) <= abs( $results['total_credit'] ) ) {
        if ( $dr_cr_diff < 0 ) {
            $dr_cr_diff = - $dr_cr_diff;
        }
        $results['rows'][] = [
            'name' => 'Profit',
            'debit' => $dr_cr_diff,
            'credit' => 0,
            'balance' => $dr_cr_diff
        ];
    } else {
        if ( $dr_cr_diff > 0 ) {
            $balance = - $dr_cr_diff;
        } else {
            $dr_cr_diff = - $dr_cr_diff;
            $balance    = $dr_cr_diff;
        }
        $results['rows'][] = [
            'name' => 'Loss',
            'debit' => 0,
            'credit' => $dr_cr_diff,
            'balance' => $balance
        ];
    }

    $results['total_debit'] = 0;
    $results['total_credit'] = 0;
    foreach ($results['rows'] as $result) {
        $results['total_debit']  += (float)$result['debit'];
        $results['total_credit'] += (float)$result['credit'];
    }

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
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE (ledger.chart_id=1 OR ledger.chart_id=7) AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
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
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id WHERE ledger.chart_id=3 AND ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'
        GROUP BY ledger_detail.ledger_id";

    // All DB results are inside `rows` key
    $results['rows1'] = $wpdb->get_results($sql1, ARRAY_A);
    $results['rows2'] = $wpdb->get_results($sql2, ARRAY_A);
    $results['rows3'] = $wpdb->get_results($sql3, ARRAY_A);

    array_unshift( $results['rows1'], [
        'name' => 'Assets',
        'balance' => ''
    ] );

    array_unshift( $results['rows2'], [
        'name' => 'Liability',
        'balance' => ''
    ] );

    array_unshift( $results['rows3'], [
        'name' => 'Equity',
        'balance' => ''
    ] );

    $results['rows1'][] = [
        'name' => 'Accounts Receivable',
        'balance' => erp_acct_get_account_receivable( $args )
    ];

    $results['rows2'][] = [
        'name' => 'Accounts Payable',
        'balance' => abs( erp_acct_get_account_payable( $args ) )
    ];

    $results['rows2'][] = [
        'name' => 'Sales Tax Payable',
        'slug' => 'sales_tax',
        'balance' => abs ( erp_acct_trial_balance_sales_tax_query( $args, 'payable' ) )
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

    $results['rows2'] = array_merge( $results['rows2'], $results['rows3'] );

    unset( $results['rows3'] );

    $results['total_left'] = 0;
    $results['total_right'] = 0;

    foreach ($results['rows1'] as $result) {
        if ( ! empty($result['balance']) ) {
            $results['total_left'] += $result['balance'];
        }
    }

    foreach ($results['rows2'] as $result) {
        if ( isset( $results['slug'] ) && $results['slug'] !== 'loss' ) {
            $result['balance'] = abs( $result['balance'] );
        }
        if ( ! empty($result['balance']) ) {
            $results['total_right'] += $result['balance'];
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

    // All DB results are inside `rows` key
    $results['rows'] = $wpdb->get_results($sql, ARRAY_A);

    // Totals are inside the root `result` array
    $results['total_debit'] = 0;
    $results['total_credit'] = 0;

    // Add-up all debit and credit
    foreach ($results['rows'] as $result) {
        $results['total_debit']  += (float)$result['debit'];
        $results['total_credit'] += (float)$result['credit'];
    }

    return $results;
}
