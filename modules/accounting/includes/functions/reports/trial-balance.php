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
function erp_acct_cash_at_bank( $args, $type ) {
    global $wpdb;

    $balance = null;

    if ( 'loan' === $type ) {
        $having = "HAVING balance < 0";
    } elseif ( 'balance' === $type ) {
        $having = "HAVING balance > 0";
    }

    $chart_bank = 7;

    $sql1 = $wpdb->prepare("SELECT group_concat(id) FROM {$wpdb->prefix}erp_acct_ledgers where chart_id = %d", $chart_bank);
    $ledger_ids = implode( ',', explode( ',', $wpdb->get_var($sql1) ) ); // e.g. 4, 5

    if ( $ledger_ids ) {
        $sql2 = "SELECT SUM(ledger_details.balance) as balance from (SELECT SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_ledger_details WHERE ledger_id IN ({$ledger_ids}) AND trn_date BETWEEN '%s' AND '%s'
        GROUP BY ledger_id {$having}) AS ledger_details";

        $data = $wpdb->get_var( $wpdb->prepare( $sql2, $args['start_date'], $args['end_date'] ) );

        $balance = erp_acct_bank_cash_calc_with_opening_balance( $args['start_date'], $data, $sql2, $type );
    }

    return $balance;
}

/**
 * Trial balance helper
 *
 * @param array $args
 *
 * @return mixed
 */
function erp_acct_bank_balance( $args, $type ) {
    global $wpdb;

    $balance = null;

    if ( 'loan' === $type ) {
        $having = "HAVING balance < 0";
    } elseif ( 'balance' === $type ) {
        $having = "HAVING balance > 0";
    }

    $chart_bank = 7;

    $sql = "SELECT ledger.id, ledger.name, SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id
        WHERE ledger.chart_id = %d AND trn_date BETWEEN '%s' AND '%s' GROUP BY ledger.id {$having}";

    $data = $wpdb->get_results( $wpdb->prepare( $sql, $chart_bank, $args['start_date'], $args['end_date'] ), ARRAY_A );

    $balance = erp_acct_bank_balance_calc_with_opening_balance( $args['start_date'], $data, $sql, $type );

    return $balance;
}

/**
 * Trial balance helper
 *
 * @param array $args
 * @param string $type
 *
 * @return int
 */
function erp_acct_sales_tax_query( $args, $type ) {
    global $wpdb;

    if ( 'payable' === $type ) {
        $having = 'HAVING balance < 0';
    } elseif ( 'receivable' === $type ) {
        $having = 'HAVING balance > 0';
    }

    $sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_tax_agency_details
        WHERE trn_date BETWEEN '%s' AND '%s' GROUP BY agency_id {$having} ) AS get_amount";

    $data = $wpdb->get_var( $wpdb->prepare( $sql, $args['start_date'], $args['end_date'] ) );

    return erp_acct_sales_tax_calc_with_opening_balance( $args['start_date'], $data, $sql, $type );
}

/**
 * Trial balance helper
 *
 * Get account receivable
 */
function erp_acct_get_account_receivable( $args ) {
    global $wpdb;

    // mainly ( debit - credit )
    $sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance
            FROM {$wpdb->prefix}erp_acct_invoice_account_details WHERE trn_date BETWEEN '%s' AND '%s'
            GROUP BY invoice_no HAVING balance > 0 )
        AS get_amount";

    $data = $wpdb->get_var($wpdb->prepare( $sql, $args['start_date'], $args['end_date'] ) );

    return erp_acct_people_calc_with_opening_balance( $args['start_date'], $data, 'receivable', $sql );
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
    $bill_sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_bill_account_details WHERE trn_date BETWEEN '%s' AND '%s'
            GROUP BY bill_no HAVING balance < 0 )
        AS get_amount";

    $purchase_sql = "SELECT SUM(balance) AS amount
        FROM ( SELECT SUM( debit - credit ) AS balance FROM {$wpdb->prefix}erp_acct_purchase_account_details WHERE trn_date BETWEEN '%s' AND '%s'
            GROUP BY purchase_no HAVING balance < 0 )
        AS get_amount";

    $bill_amount = $wpdb->get_var( $wpdb->prepare( $bill_sql, $args['start_date'], $args['end_date'] ) );
    $purchase_amount = $wpdb->get_var( $wpdb->prepare( $purchase_sql, $args['start_date'], $args['end_date'] ) );

    $data = (float) $bill_amount + (float) $purchase_amount;

    return erp_acct_people_calc_with_opening_balance( $args['start_date'], $data, 'payable', $bill_sql, $purchase_sql );
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

    $sql = "SELECT SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger.id = ledger_detail.ledger_id
        WHERE ledger.slug = 'owner_s_equity' AND trn_date BETWEEN '%s' AND '%s' GROUP BY ledger.id {$having}";

    $data = $wpdb->get_var( $wpdb->prepare( $sql, $args['start_date'], $args['end_date'] ) );

    return erp_acct_owners_equity_calc_with_opening_balance( $args['start_date'], $data, $sql, $type );
}

/**
 * Check if date has min 2 days difference ( Trial balance helper )
 *
 * @param string $date1
 * @param string $date2
 *
 * @return boolean
 */
function erp_acct_has_date_diff($date1, $date2) {
    $interval = date_diff( date_create($date1), date_create($date2) );

    // if difference is `0` OR `1` day
    if ( '2' > $interval->format('%a') ) {
        return false;
    }

    return true;
}

/**
 * Calculate extra account receivable/payable
 *
 * @param string $sql
 * @param string $start_date
 * @param string $end_date
 *
 * @return float
 */
function erp_acct_calculate_people_balance($sql, $start_date, $end_date) {
    global $wpdb;

    $balance = 0;
    $query   = $wpdb->get_var( $wpdb->prepare($sql, $start_date, $end_date) );

    if ( $query ) {
        $balance += (float) $query;
    }

    return $balance;
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
function erp_acct_get_balance_with_opening_balance($ledgers, $data, $opening_balance) {
    $temp_data = [];

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

        if ( $balance ) {
            $temp_data[] = [
                'id'      => $ledger['id'],
                'name'    => $ledger['name'],
                'balance' => $balance
            ];
        }
    }

    return $temp_data;
}

/**
 * get ledger details data between
 * `financial year start date`
 * and
 * `previous date from trial balance start date`
 *
 * @return array
 */
function erp_acct_get_balance_within_ledger_details_and_trial_balance($sql, $temp_data) {
    global $wpdb;

    $result = [];

    $ledger_details = $wpdb->get_results($sql, ARRAY_A);

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
 * Get trial balance calculate with opening balance within financial year date range
 *
 * @param string $tb_start_date
 * @param array $data => ledger details data on trial balance date range
 * @param string $sql
 *
 * @return array
 */
function erp_acct_calc_with_opening_balance( $tb_start_date, $data, $sql ) {
    global $wpdb;

    $result = [];

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $tb_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_opening_balance_by_fn_year_id( $closest_fy_date['id'] );

    $ledgers = $wpdb->get_results( "SELECT ledger.id, ledger.name FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
                WHERE ledger.chart_id <> 7 AND ledger.slug <> 'owner_s_equity'", ARRAY_A );

    $temp_data = erp_acct_get_balance_with_opening_balance($ledgers, $data, $opening_balance);

    // should we go further calculation, check the diff
    if ( ! erp_acct_has_date_diff($tb_start_date, $closest_fy_date['start_date']) ) {
        return $temp_data;
    } else {
        $prev_date_of_tb_start = date( 'Y-m-d', strtotime( '-1 day', strtotime($tb_start_date) ) );
    }

    $sql = $wpdb->prepare($sql, $closest_fy_date['start_date'], $prev_date_of_tb_start);

    $result = erp_acct_get_balance_within_ledger_details_and_trial_balance($sql, $temp_data);

    return $result;
}

/**
 * Get trial balance cash at bank calculate with opening balance within financial year date range
 *
 * @param string $tb_start_date
 * @param array $data => ledger details data on trial balance date range
 * @param string $sql
 * @param string $type
 *
 * @return float
 */
function erp_acct_bank_cash_calc_with_opening_balance( $tb_start_date, $data, $sql, $type ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $tb_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_bank_cash_opening_balance_by_fn_year_id( $closest_fy_date['id'], $type );

    $balance = (float) $data;

    foreach ( $opening_balance as $op_balance ) {
        $balance += (float) $op_balance['balance'];
    }

    // should we go further calculation, check the diff
    if ( ! erp_acct_has_date_diff($tb_start_date, $closest_fy_date['start_date']) ) {
        return $balance;
    } else {
        $prev_date_of_tb_start = date( 'Y-m-d', strtotime( '-1 day', strtotime($tb_start_date) ) );
    }

    // get ledger details data between
    //     `financial year start date`
    // and
    //     `previous date from trial balance start date`
    $ledger_details_balance = $wpdb->get_var( $wpdb->prepare($sql, $closest_fy_date['start_date'], $prev_date_of_tb_start) );

    if ( $ledger_details_balance ) {
        $balance += (float) $ledger_details_balance;
    }

    return $balance;
}

/**
 * Get trial balance bank balance calculate with opening balance within financial year date range
 *
 * @param string $tb_start_date
 * @param array $data => ledger details data on trial balance date range
 * @param string $sql
 * @param string $type
 *
 * @return array
 */
function erp_acct_bank_balance_calc_with_opening_balance( $tb_start_date, $data, $sql, $type ) {
    global $wpdb;

    $chart_bank = 7;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $tb_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_bank_balance_opening_balance_by_fn_year_id( $closest_fy_date['id'], $type );

    $ledgers = $wpdb->get_results( "SELECT ledger.id, ledger.name FROM {$wpdb->prefix}erp_acct_ledgers AS ledger WHERE ledger.chart_id = 7", ARRAY_A );

    $temp_data = erp_acct_get_balance_with_opening_balance($ledgers, $data, $opening_balance);

    // should we go further calculation, check the diff
    if ( ! erp_acct_has_date_diff($tb_start_date, $closest_fy_date['start_date']) ) {
        return $temp_data;
    } else {
        $prev_date_of_tb_start = date( 'Y-m-d', strtotime( '-1 day', strtotime($tb_start_date) ) );
    }

    $sql = $wpdb->prepare($sql, $chart_bank, $closest_fy_date['start_date'], $prev_date_of_tb_start);

    $result = erp_acct_get_balance_within_ledger_details_and_trial_balance($sql, $temp_data);

    return $result;
}

/**
 * Get trial balance sales tax calculate with opening balance within financial year date range
 *
 * @param string $tb_start_date
 * @param float $data => ledger details data on trial balance date range
 * @param string $sql
 * @param string $type
 *
 * @return float
 */
function erp_acct_sales_tax_calc_with_opening_balance( $tb_start_date, $data, $sql, $type ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $tb_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_sales_tax_opening_balance_by_fn_year_id( $closest_fy_date['id'], $type );

    $balance = (float) $data;

    foreach ( $opening_balance as $op_balance ) {
        $balance += (float) $op_balance['balance'];
    }

    // should we go further calculation, check the diff
    if ( ! erp_acct_has_date_diff($tb_start_date, $closest_fy_date['start_date']) ) {
        return $balance;
    } else {
        $prev_date_of_tb_start = date( 'Y-m-d', strtotime( '-1 day', strtotime($tb_start_date) ) );
    }

    // get agency details data between
    //     `financial year start date`
    // and
    //     `previous date from trial balance start date`
    $ledger_details_balance = $wpdb->get_var( $wpdb->prepare($sql, $closest_fy_date['start_date'], $prev_date_of_tb_start) );

    if ( $ledger_details_balance ) {
        $balance += (float) $ledger_details_balance;
    }

    return $balance;
}

/**
 * Get trial balance people account_payable/account_receivable calculate with opening balance within financial year date range
 *
 * @param string $tb_start_date
 * @param float $data => ledger details data on trial balance date range
 * @param string $sql
 * @param string $type
 *
 * @return float
 */
function erp_acct_people_calc_with_opening_balance( $tb_start_date, $data, $type, $sql1, $sql2 = null ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $tb_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_people_opening_balance_by_fn_year_id( $closest_fy_date['id'], $type );

    $balance = (float) $data;

    if ( ! empty( $opening_balance ) ) {
        $balance += (float) $opening_balance;
    }

    // should we go further calculation, check the diff
    if ( ! erp_acct_has_date_diff($tb_start_date, $closest_fy_date['start_date']) ) {
        return $balance;
    } else {
        $prev_date_of_tb_start = date( 'Y-m-d', strtotime( '-1 day', strtotime($tb_start_date) ) );
    }

    $start_date = $closest_fy_date['start_date'];
    $end_date   = $prev_date_of_tb_start;

    if ( 'payable' === $type ) {
        $balance += erp_acct_calculate_people_balance($sql1, $start_date, $end_date);
        $balance += erp_acct_calculate_people_balance($sql2, $start_date, $end_date);
    } elseif ( 'receivable' === $type ) {
        $balance += erp_acct_calculate_people_balance($sql1, $start_date, $end_date);
    }

    return $balance;
}

/**
 * Get trial balance owners equity calculate with opening balance within financial year date range
 *
 * @param string $tb_start_date
 * @param float $data => ledger details data on trial balance date range
 * @param string $sql
 * @param string $type
 *
 * @return float
 */
function erp_acct_owners_equity_calc_with_opening_balance( $tb_start_date, $data, $sql, $type ) {
    global $wpdb;

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $tb_start_date );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_owners_equity_opening_balance_by_fn_year_id( $closest_fy_date['id'], $type );

    $balance = (float) $data;

    if ( ! empty( $opening_balance ) ) {
        $balance += (float) $opening_balance;
    }

    // should we go further calculation, check the diff
    if ( ! erp_acct_has_date_diff($tb_start_date, $closest_fy_date['start_date']) ) {
        return $balance;
    } else {
        $prev_date_of_tb_start = date( 'Y-m-d', strtotime( '-1 day', strtotime($tb_start_date) ) );
    }

    // get ledger details data between
    //     `financial year start date`
    // and
    //     `previous date from trial balance start date`
    $ledger_details_balance = $wpdb->get_var( $wpdb->prepare($sql, $closest_fy_date['start_date'], $prev_date_of_tb_start) );

    if ( $ledger_details_balance ) {
        $balance += (float) $ledger_details_balance;
    }

    return $balance;
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

    $sql = "SELECT id, name, start_date, end_date FROM {$wpdb->prefix}erp_acct_financial_years WHERE start_date <= '%s' ORDER BY start_date DESC LIMIT 1";

    return $wpdb->get_row( $wpdb->prepare($sql, $date), ARRAY_A );
}

/**
 * Get opening balance data by financial year id
 *
 * @param int $id
 * @param int $chart_id ( optional )
 *
 * @return string
 */
function erp_acct_opening_balance_by_fn_year_id( $id, $chart_id = null ) {
    global $wpdb;

    $where = '';

    if ( $chart_id ) {
        $where = $wpdb->prepare('AND ledger.chart_id = %d', $chart_id);
    }

    $sql = "SELECT ledger.id, ledger.name, SUM(opb.debit - opb.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_opening_balances AS opb ON ledger.id = opb.ledger_id
        WHERE opb.financial_year_id = %d {$where} AND opb.type = 'ledger' AND ledger.slug <> 'owner_s_equity'
        GROUP BY opb.ledger_id";

    return $wpdb->get_results( $wpdb->prepare($sql, $id), ARRAY_A );
}

/**
 * Get bank opening balance data by financial year id
 *
 * @param int $id
 * @param string $type
 *
 * @return array
 */
function erp_acct_bank_cash_opening_balance_by_fn_year_id( $id, $type ) {
    global $wpdb;

    if ( 'loan' === $type ) {
        $having = 'HAVING balance < 0';
    } elseif ( 'balance' === $type ) {
        $having = 'HAVING balance > 0';
    }

    $sql = "SELECT SUM(opb.balance) AS balance FROM (SELECT SUM( debit - credit ) AS balance
            FROM {$wpdb->prefix}erp_acct_opening_balances WHERE financial_year_id = %d AND chart_id = 7
            GROUP BY ledger_id {$having}) AS opb";

    return $wpdb->get_results( $wpdb->prepare($sql, $id), ARRAY_A );
}

/**
 * Get bank opening balance data by financial year id
 *
 * @param int $id
 * @param string $type
 *
 * @return array
 */
function erp_acct_sales_tax_opening_balance_by_fn_year_id( $id, $type ) {
    global $wpdb;

    if ( 'payable' === $type ) {
        $having = 'HAVING balance < 0';
    } elseif ( 'receivable' === $type ) {
        $having = 'HAVING balance > 0';
    }

    $sql = "SELECT SUM(opb.balance) AS balance FROM ( SELECT SUM( debit - credit ) AS balance
            FROM {$wpdb->prefix}erp_acct_opening_balances
            WHERE financial_year_id = %d AND type = 'tax_agency' GROUP BY ledger_id {$having} ) AS opb";

    return $wpdb->get_results( $wpdb->prepare($sql, $id), ARRAY_A );
}

/**
 * Get bank balance opening balance data by financial year id
 *
 * @param int $id
 * @param string $type
 *
 * @return array
 */
function erp_acct_bank_balance_opening_balance_by_fn_year_id( $id, $type ) {
    global $wpdb;

    if ( 'loan' === $type ) {
        $having = 'HAVING balance < 0';
    } elseif ( 'balance' === $type ) {
        $having = 'HAVING balance > 0';
    }

    $sql = "SELECT ledger.id, ledger.name, SUM(opb.debit - opb.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_opening_balances AS opb ON ledger.id = opb.ledger_id
        WHERE opb.financial_year_id = %d AND ledger.chart_id = 7 GROUP BY opb.ledger_id {$having}";

    return $wpdb->get_results( $wpdb->prepare($sql, $id), ARRAY_A );
}

/**
 * Get bank balance opening balance data by financial year id
 *
 * @param int $id
 * @param string $type
 *
 * @return mixed
 */
function erp_acct_owners_equity_opening_balance_by_fn_year_id( $id, $type ) {
    global $wpdb;

    if ( 'capital' === $type ) {
        $having = 'HAVING balance < 0';
    } elseif ( 'drawings' === $type ) {
        $having = 'HAVING balance > 0';
    }

    $sql = "SELECT SUM(opb.debit - opb.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_opening_balances AS opb ON ledger.id = opb.ledger_id
        WHERE opb.financial_year_id = %d AND opb.type = 'ledger' AND ledger.slug = 'owner_s_equity' {$having}";

    return $wpdb->get_var( $wpdb->prepare($sql, $id) );
}

function erp_acct_people_opening_balance_by_fn_year_id( $id, $type ) {
    global $wpdb;

    if ( 'payable' === $type ) {
        $having = 'HAVING balance < 0';
    } elseif ( 'receivable' === $type ) {
        $having = 'HAVING balance > 0';
    }

    $sql = "SELECT SUM(opb.balance) AS balance FROM ( SELECT SUM( debit - credit ) AS balance
        FROM {$wpdb->prefix}erp_acct_opening_balances
        WHERE financial_year_id = %d AND type = 'people' GROUP BY ledger_id {$having} ) AS opb";

    return $wpdb->get_var( $wpdb->prepare($sql, $id) );
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
    $results['rows'] = erp_acct_calc_with_opening_balance( $args['start_date'], $data, $sql );

    /**
     * Let's create some virtual ledgers
     */

    $results['rows'][] = [
        'name'       => 'Cash at Bank',
        'balance'    => erp_acct_cash_at_bank( $args, 'balance' ),
        'additional' => erp_acct_bank_balance( $args, 'balance' )
    ];
    $results['rows'][] = [
        'name'       => 'Bank Loan',
        'balance'    => erp_acct_cash_at_bank( $args, 'loan' ),
        'additional' => erp_acct_bank_balance( $args, 'loan' )
    ];

    $results['rows'][] = [
        'name'    => 'Sales Tax Payable',
        'balance' => erp_acct_sales_tax_query( $args, 'payable' )
    ];
    $results['rows'][] = [
        'name'    => 'Sales Tax Receivable',
        'balance' => erp_acct_sales_tax_query( $args, 'receivable' )
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
