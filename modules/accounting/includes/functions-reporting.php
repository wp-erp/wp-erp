<?php
/**
 * Get all accounting reports
 *
 * @return array
 */
function erp_ac_get_reports() {

    $reports = [
        'trial-balance' => [
            'title'       => __( 'Trial Balance', 'erp' ),
            'description' => __( 'Trial balance is the bookkeeping or accounting report that lists the balances in each of general ledger accounts', 'erp' )
        ],
        'sales-tax' => [
            'title'       => __( 'Sales Tax', 'erp' ),
            'description' => __( 'It generates report based on the sales tax charged or paid for the current financial cycle/year.', 'erp' )
        ],
        'income-statement' => [
             'title'       => __( 'Income Statement', 'erp' ),
             'description' => __( 'A summary of a management\'s performance as reflecte the profitability of an organization during the time interval.', 'erp' )
        ],
        'balance-sheet' => [
            'title'       => __( 'Balance Sheet', 'erp' ),
            'description' => __( 'This is a report gives you an immediate status of your accounts at a specified date. You can call it a "Snapshot" view of the current position (day) of the financial year.', 'erp' )
        ],
        // 'profit-loss' => [
        //     'title'       => __( 'Profit and Loss', 'erp' ),
        //     'description' => __( '', 'erp' )
        // ],
        // 'ar-aging-summary' => [
        //     'title'       => __( 'A/R Aging Summary', 'erp' ),
        //     'description' => __( '', 'erp' )
        // ],
        // 'company-snapshot' => [
        //     'title'       => __( 'Company Snapshot', 'erp' ),
        //     'description' => __( '', 'erp' )
        // ],

        // 'ap-aging-summary' => [
        //     'title'       => __( 'A/P Aging Summary', 'erp' ),
        //     'description' => __( '', 'erp' )
        // ],
        // 'cash-flow' => [
        //     'title'       => __( 'Statement of Cash Flows', 'erp' ),
        //     'description' => __( '', 'erp' )
        // ],
        // 'vendor-balance-summary' => [
        //     'title'       => __( 'Vendor Balance Summary', 'erp' ),
        //     'description' => __( '', 'erp' )
        // ],
    ];

    return apply_filters( 'erp_ac_reports', $reports );
}

/**
 * Get closing Balnace
 *
 * @since  1.1.9
 *
 * @return array
 */
function erp_ac_get_asset_liability_equity_balance( $financial_end = false ) {
    global $wpdb;

    $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
    $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
    $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    if ( $financial_end ) {
        $financial_end = date( 'Y-m-d', strtotime( $financial_end ) );

    } else {
        $financial_end = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );
    }

    $sql = $wpdb->prepare(
        "SELECT ledger.id, ledger.code, ledger.name, ledger.type_id, type.name as type_name, type.class_id, class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit
        FROM $tbl_class as class
        LEFT JOIN $tbl_type as type ON type.class_id = class.id
        LEFT JOIN $tbl_ledger as ledger ON ledger.type_id = type.id
        LEFT JOIN $tbl_journals as jour ON jour.ledger_id = ledger.id
        LEFT JOIN $tbl_transaction as trans ON trans.id = jour.transaction_id
        WHERE class.id IN ( 1, 2, 5 )
        AND ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
        AND ( trans.issue_date <= '%s' )
        GROUP BY ledger.id", $financial_end
    );

    return $wpdb->get_results( $sql );
}

/**
 * Get closing debit and credit
 *
 * @since  1.1.9
 *
 * @return array
 */
function erp_ac_get_opening_income_expense( $financial_end = false ) {
    global $wpdb;

    $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
    $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
    $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    if ( $financial_end ) {
        $financial_end = date( 'Y-m-d', strtotime( $financial_end ) );

    } else {
        $financial_end = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );
    }

    $financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );

    $sql = $wpdb->prepare(
        "SELECT ledger.id, ledger.code, ledger.name, ledger.type_id, type.name as type_name, type.class_id, class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit
        FROM $tbl_class as class
        LEFT JOIN $tbl_type as type ON type.class_id = class.id
        LEFT JOIN $tbl_ledger as ledger ON ledger.type_id = type.id
        LEFT JOIN $tbl_journals as jour ON jour.ledger_id = ledger.id
        LEFT JOIN $tbl_transaction as trans ON trans.id = jour.transaction_id
        WHERE class.id IN ( 3, 4 )
        AND ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
        AND ( trans.issue_date <= '%s' )
        GROUP BY ledger.id", $financial_end
    );

    return $wpdb->get_results( $sql );
}

/**
 * Get closing debit and credit
 *
 * @since  1.1.9
 *
 * @return array
 */
function erp_ac_get_closing_income_expense( $financial_end = false ) {
    global $wpdb;

    $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
    $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
    $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    $financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    if ( $financial_end ) {
        $financial_end = date( 'Y-m-d', strtotime( $financial_end ) );

        if ( $financial_end >= $financial_start ) {
            $financial_end = $financial_start;
        }
    } else {
        $financial_end = $financial_start;
    }

    $sql = $wpdb->prepare(
        "SELECT sum(jour.debit) as debit, sum(jour.credit) as credit
        FROM $tbl_class as class
        LEFT JOIN $tbl_type as type ON type.class_id = class.id
        LEFT JOIN $tbl_ledger as ledger ON ledger.type_id = type.id
        LEFT JOIN $tbl_journals as jour ON jour.ledger_id = ledger.id
        LEFT JOIN $tbl_transaction as trans ON trans.id = jour.transaction_id
        WHERE class.id IN ( 3, 4 )
        AND ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
        AND ( trans.issue_date < '%s' )", $financial_end
    );

    $balance = $wpdb->get_results( $sql );
    $balance = reset( $balance );

    if ( $balance->credit > $balance->debit ) {
        $balance->credit = abs( $balance->credit - $balance->debit );
        $balance->debit  = abs( 0 );

    } else if ( $balance->credit < $balance->debit ) {
        $balance->debit  = abs( $balance->debit - $balance->credit );
        $balance->credit = abs( 0 );

    } else {
        $balance->debit  = abs( 0 );
        $balance->credit = abs( 0 );
    }

    return  $balance;
}

/**
 * Transaction report query
 *
 * @param  string $financial_end
 *
 * @since  1.0
 *
 * @return array
 */
function erp_ac_reporting_query( $financial_end = false ) {
    $financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );

    if ( $financial_end ) {
        $financial_end = date( 'Y-m-d', strtotime( $financial_end ) );
    } else {
        $financial_end = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );
    }

    $unit_balance         = erp_ac_get_asset_liability_equity_balance( $financial_end );
    $ope_in_ex_balance    = erp_ac_get_opening_income_expense( $financial_end );
    $report               = array_merge( $unit_balance, $ope_in_ex_balance );
    return $report;


    if ( $financial_start > $financial_end ) {
        return [];
    }

    global $wpdb;
    $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
    $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
    $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';
    $query           = [];

    $query[] = "tran.issue_date >= '$financial_start'";
    $query[] = "tran.issue_date <= '$financial_end'";

    $query = $query ? ' AND ' . implode( ' AND ', $query ) : '';
    $where = "( tran.status IS NULL OR tran.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) ) AND ( 1=1 $query )";
    $join  = '';
    $where = apply_filters( 'erp_ac_trial_balance_where', $where );
    $join  = apply_filters( 'erp_ac_trial_balance_join', $join );

    $sql = "SELECT led.id, led.code, led.name, led.type_id, types.name as type_name, types.class_id, class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit
    FROM $tbl_ledger as led
    LEFT JOIN $tbl_type as types ON types.id = led.type_id
    LEFT JOIN $tbl_class as class ON class.id = types.class_id
    LEFT JOIN $tbl_journals as jour ON jour.ledger_id = led.id
    LEFT JOIN $tbl_transaction as tran ON tran.id = jour.transaction_id
    $join
    WHERE
    $where
    GROUP BY led.id";

    return $wpdb->get_results( $sql );

}

/**
 * Get transaction by class id
 *
 * @param  array  $class_id
 * @param  date   $financial_start
 * @param  date   $financial_end
 *
 * @since  1.1.9
 *
 * @return array
 */
function erp_ac_get_transaction_by_calss_id( $class_id = [], $financial_start = false, $financial_end = false ) {
    global $wpdb;

    $cache_key       = 'erp-ac-transaction-by-calss-id-' . md5( serialize( $class_id ) ) . $financial_start . $financial_end;
    $items           = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
        $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
        $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
        $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
        $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

        $financial_start = $financial_start ? date( 'Y-m-d', strtotime( $financial_start ) ) : date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
        $financial_end   = $financial_end ? date( 'Y-m-d', strtotime( $financial_end ) ) : date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

        if ( count( $class_id ) ) {
            $class_id = implode( "','", $class_id );
            $where    = " AND class.id IN ( '$class_id' ) ";
        } else {
            $where = '';
        }

        $sql = $wpdb->prepare(
            "SELECT trans.id as transaction_id, trans.issue_date, trans.status as trans_status, trans.type as trans_type, jour.debit, jour.credit, ledger.id as ledger_id,
            ledger.code, ledger.name as ledger_name, ledger.type_id, type.name as type_name, type.class_id,
            class.name as class_name
            FROM $tbl_class as class
            LEFT JOIN $tbl_type as type ON type.class_id = class.id
            LEFT JOIN $tbl_ledger as ledger ON ledger.type_id = type.id
            LEFT JOIN $tbl_journals as jour ON jour.ledger_id = ledger.id
            LEFT JOIN $tbl_transaction as trans ON trans.id = jour.transaction_id
            WHERE ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
            AND ( trans.issue_date >= '%s' AND trans.issue_date <= '%s' )
            $where", $financial_start, $financial_end
        );

        $items = $wpdb->get_results( $sql );
        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

/**
 * Get transaction group by class id
 *
 * @param  array  $class_id
 * @param  date   $financial_start
 * @param  date   $financial_end
 *
 * @since  1.1.9
 *
 * @return array
 */
function erp_ac_get_transaction_group_by_calss_id( $class_id = [], $financial_start = false, $financial_end = false ) {
    $trans = erp_ac_get_transaction_by_calss_id( $class_id, $financial_start = false, $financial_end = false );
    $group = [];

    foreach ( $trans as $tran ) {
        $group[$tran->class_id][] = $tran;
    }

    return $group;
}

/**
 * Get transaction group by month from class id
 *
 * @param  array  $class_id
 * @param  date   $financial_start
 * @param  date   $financial_end
 *
 * @since  1.1.9
 *
 * @return array
 */
function erp_ac_get_transaction_group_by_month_from_calss_id( $class_id = [], $financial_start = false, $financial_end = false ) {
    $trans = erp_ac_get_transaction_by_calss_id( $class_id, $financial_start = false, $financial_end = false );
    $group = [];

    foreach ( $trans as $tran ) {
        $date = date( 'm', strtotime( $tran->issue_date ) );
        $group[$tran->class_id][$date][] = $tran;
    }

    return $group;
}

/**
 * Tax report query
 *
 * @since  1.1
 *
 * @param  array $args
 *
 * @return array
 */
function erp_ac_get_sales_tax_report( $args ) {
    $all_tax_id = array_keys( erp_ac_get_tax_dropdown() );

    if ( isset( $args['tax_id'] ) && is_array( $args['tax_id'] ) ) {
        $all_tax_id = $args['tax_id'];
    }

    $defaults = array(
        'number' => 20,
        'offset' => 0,
        'start'  => date( 'Y-m-d', strtotime( erp_financial_start_date() ) ),
        'end'    => date( 'Y-m-d', strtotime( erp_financial_end_date() ) ),
        'tax_id' => $all_tax_id
    );

    $args            = wp_parse_args( $args, $defaults );
    //$args['start'] = ( $args['start'] && ! empty( $args['start'] ) ) ? $args['start'] : date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    $args['end']     = ( $args['end'] && ! empty( $args['end'] ) ) ? $args['end'] : date( 'Y-m-d', strtotime( erp_financial_end_date() ) );
    $cache_key       = 'erp-ac-tax-report' . md5( serialize( $args ) ) . md5( serialize( get_current_user_id() ) );
    $tax_report      = wp_cache_get( $cache_key, 'erp' );

    if ( false === $tax_report ) {
        $tax_report = WeDevs\ERP\Accounting\Model\Transaction::with([ 'journals' => function( $q ) use( $args ) {
            return $q->with([ 'ledger' => function( $l ) use( $args ) {
                return $l->whereIn( 'tax', $args['tax_id'] );
            }]);
        }])//->where( 'issue_date', '>=', $args['start'] )
        ->where( 'issue_date', '<=', $args['end'] )
        ->where( function($q) {
            $q->whereNull( 'status' )->orWhereNotIn( 'status', ['draft', 'void', 'awaiting_approval'] );
        } )
        ->skip($args['offset'])
        ->take($args['number'])
        ->get()
        ->toArray();

        wp_cache_set( $cache_key, $tax_report, 'erp' );
    }

    return $tax_report;
}

/**
 * Tax report count query
 *
 * @param  array  $args
 *
 * @since  1.1
 *
 * @return int
 */
function erp_ac_get_sales_tax_report_count( $args = [] ) {
    $all_tax_id = array_keys( erp_ac_get_tax_dropdown() );

    if ( isset( $args['tax_id'] ) && is_array( $args['tax_id'] ) ) {
        $all_tax_id = $args['tax_id'];
    }

    $defaults = array(
        'start'  => date( 'Y-m-d', strtotime( erp_financial_start_date() ) ),
        'end'    => date( 'Y-m-d', strtotime( erp_financial_end_date() ) ),
        'tax_id' => $all_tax_id
    );

    $args       = wp_parse_args( $args, $defaults );
    $cache_key  = 'erp-ac-tax-report_count' . md5( serialize( $args ) ) . md5( serialize( get_current_user_id() ) );
    $tax_report = wp_cache_get( $cache_key, 'erp' );

    if ( false === $tax_report ) {
        $tax_report = WeDevs\ERP\Accounting\Model\Transaction::with([ 'journals' => function( $q ) use( $args ) {
            return $q->with([ 'ledger' => function( $l ) use( $args ) {
                return $l->whereIn( 'tax', $args['tax_id'] );
            }]);
        }])//->where( 'issue_date', '>=', $args['start'] )
        ->where( 'issue_date', '<=', $args['end'] )
        ->where( function($q) {
            $q->whereNull( 'status' )->orWhereNotIn( 'status', ['draft', 'void', 'awaiting_approval'] );
        })
        ->count();

        wp_cache_set( $cache_key, $tax_report, 'erp' );
    }

    return $tax_report;
}

/**
 * Formating tax report query result for individual tax
 *
 * @param  array  $args [description]
 *
 * @since  1.1.9
 *
 * @return array
 */
function erp_ac_normarlize_individual_tax( $args = [] ) {
    $tax_id          = is_array( $args['tax_id'] ) && count( $args['tax_id'] ) ? reset( $args['tax_id'] ) : false;
    $transactions    = erp_ac_get_sales_tax_report( $args );
    $tax_receivable  = erp_ac_get_tax_account_from_tax_id( $tax_id, 'expense' );
    $tax_payable     = erp_ac_get_tax_account_from_tax_id( $tax_id, 'sales' );
    $tax_unit_info   = [];

    foreach ( $transactions as $trans ) {
        foreach ( $trans['journals'] as $jour ) {
            $tax_ledger_id = isset( $jour['ledger']['id'] ) ? $jour['ledger']['id'] : false;
            $transaction_id = $trans['id'];

            if ( $tax_ledger_id == $tax_receivable ) {
                if ( isset( $tax_unit_info[$transaction_id]['receivable'] ) ) {
                    $tax_unit_info[$transaction_id]['receivable'] = $tax_unit_info[$transaction_id]['receivable'] + ( $jour['debit'] - $jour['credit'] );
                } else {
                    $tax_unit_info[$transaction_id]['receivable'] = $jour['debit'] - $jour['credit'];
                }

                $tax_unit_info[$transaction_id]['receivable'] = $tax_unit_info[$transaction_id]['receivable'];
                $tax_unit_info[$transaction_id]['issue_date']   = $trans['issue_date'];
                $tax_unit_info[$transaction_id]['transaction_id']   = $transaction_id;
                $tax_unit_info[$transaction_id]['type']   = 'expense';
            }

            if ( $tax_ledger_id == $tax_payable ) {
                if ( isset( $tax_unit_info[$transaction_id]['payable'] ) ) {
                    $tax_unit_info[$transaction_id]['payable'] = $tax_unit_info[$transaction_id]['payable'] + ( $jour['credit'] - $jour['debit'] );
                } else {
                    $tax_unit_info[$transaction_id]['payable'] = $jour['credit'] - $jour['debit'];
                }

                $tax_unit_info[$transaction_id]['payable'] = $tax_unit_info[$transaction_id]['payable'];
                $tax_unit_info[$transaction_id]['issue_date']   = $trans['issue_date'];
                $tax_unit_info[$transaction_id]['transaction_id']   = $transaction_id;
                $tax_unit_info[$transaction_id]['type']   = 'sales';
            }
        }
    }

    return $tax_unit_info;
}

/**
 * Formating tax report query result for tax summery
 *
 * @param  array  $args
 *
 * @since  1.1.9
 *
 * @return array
 */
function erp_ac_normarlize_tax_from_transaction( $args = [] ) {
    $transactions    = erp_ac_get_sales_tax_report( $args );
    $tax_receivable  = wp_list_pluck( erp_ac_get_tax_receivable_ledger(), 'id' );
    $tax_payable     = wp_list_pluck( erp_ac_get_tax_payable_ledger(), 'id' );
    $tax_info        = erp_ac_get_tax_info();
    $tax_unit_info   = [];


    foreach ( $transactions as $trans ) {
        foreach ( $trans['journals'] as $jour ) {
            $tax_ledger_id = isset( $jour['ledger']['id'] ) ? $jour['ledger']['id'] : false;
            $tax_id = isset( $jour['ledger']['tax'] ) ? $jour['ledger']['tax'] : false;

            if ( in_array( $tax_ledger_id, $tax_receivable ) ) {
                if ( isset( $tax_unit_info[$tax_id]['expense']['amount'] ) ) {
                    $tax_unit_info[$tax_id]['expense']['amount'] = $tax_unit_info[$tax_id]['expense']['amount'] + ( $jour['debit'] - $jour['credit'] );
                } else {
                    $tax_unit_info[$tax_id]['expense']['amount'] = ( $jour['debit'] - $jour['credit'] );
                }

                $tax_unit_info[$tax_id]['expense']['tax_id']     = $tax_info[$tax_id]['id'];
                $tax_unit_info[$tax_id]['expense']['tax_name']   = $tax_info[$tax_id]['name'];
                $tax_unit_info[$tax_id]['expense']['tax_number'] = $tax_info[$tax_id]['number'];
                $tax_unit_info[$tax_id]['expense']['rate']       = $tax_info[$tax_id]['rate'];
            }

            if ( in_array( $tax_ledger_id, $tax_payable ) ) {
                if ( isset( $tax_unit_info[$tax_id]['sales']['amount'] ) ) {
                    $tax_unit_info[$tax_id]['sales']['amount'] = $tax_unit_info[$tax_id]['sales']['amount'] + ( $jour['credit'] - $jour['debit'] );
                } else {
                    $tax_unit_info[$tax_id]['sales']['amount'] = ( $jour['credit'] - $jour['debit'] );
                }

                $tax_unit_info[$tax_id]['sales']['tax_id']     = $tax_info[$tax_id]['id'];
                $tax_unit_info[$tax_id]['sales']['tax_name']   = $tax_info[$tax_id]['name'];
                $tax_unit_info[$tax_id]['sales']['tax_number'] = $tax_info[$tax_id]['number'];
                $tax_unit_info[$tax_id]['sales']['rate']       = $tax_info[$tax_id]['rate'];
            }
        }
    }

    return $tax_unit_info;
}

/**
 * Get total sales amount without tax
 *
 * @param  array $charts
 *
 * @since  1.1
 *
 * @return int
 */
function erp_ac_get_sales_total_without_tax( $charts ) {

    $sales_journals  = isset( $charts[4] ) ? $charts[4] : [];
    $sales_total    = 0;

    foreach ( $sales_journals as $key => $ledger_jours ) {
        $sales_total  = $sales_total + array_sum( wp_list_pluck( $ledger_jours, 'credit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'debit' ) );
    }

    return $sales_total;
}

/**
 * Get total sales total amount
 *
 * @param  array $charts
 *
 * @since  1.1
 *
 * @return int
 */
function erp_ac_get_sales_tax_total( $charts ) {
    $payable_tax          = erp_ac_get_tax_payable_ledger();
    $payable_tax          = wp_list_pluck( $payable_tax, 'id' );
    $payable_tax_journals = [];
    $tax_total            = 0;
    $libility_payable_tax_journals = isset( $charts[2] ) ? $charts[2] : [];

    foreach ( $libility_payable_tax_journals as $key => $libility_journal ) {
        if ( in_array( $key , $payable_tax ) ) {
            $payable_tax_journals[$key] = $libility_journal;
        }
    }

    foreach ( $payable_tax_journals as $key => $ledger_jours ) {
        $tax_total  = $tax_total + array_sum( wp_list_pluck( $ledger_jours, 'credit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'debit' ) );
    }

    return $tax_total;
}

/**
 * Get cost of good sold amount
 *
 * @param  string $charts
 *
 * @since  1.1
 *
 * @return int
 */
function erp_ac_get_good_sold_total_amount( $financial_end = false ) {

    if ( $financial_end ) {
        $financial_end = date( 'Y-m-d', strtotime( $financial_end ) );
    } else {
        $financial_end = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );
    }

    global $wpdb;

    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    $sql = $wpdb->prepare(
                "SELECT trans.id as transaction_id
                FROM $tbl_transaction as trans
                LEFT JOIN $tbl_journals as jour ON jour.transaction_id = trans.id
                WHERE jour.ledger_id = '%d'
                AND ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
                AND ( trans.issue_date < '%s' )", 24, $financial_end
            );

    $results   = $wpdb->get_results($sql);
    $trans_ids = implode( "','", wp_list_pluck(  $results, 'transaction_id' ) );

    $sql = "SELECT sum( jour.debit ) as debit FROM $tbl_journals as jour WHERE jour.transaction_id IN ( '$trans_ids' )";
    $results   = $wpdb->get_var($sql);

    return $results;
}

/**
 * Get total expense amount without tax
 *
 * @param  array $charts
 *
 * @since  1.1
 *
 * @return int
 */
function erp_ac_get_expense_total_with_tax( $charts ) {
    $expense_journals     = isset( $charts[3] ) ? $charts[3] : [];
    $expense_total        = 0;

    foreach ( $expense_journals as $key => $ledger_jours ) {
        $expense_total  = $expense_total + array_sum( wp_list_pluck( $ledger_jours, 'debit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'credit' ) );
    }
    return $expense_total;

}

/**
 * Get total expense tax total
 *
 * @param  array $charts
 *
 * @since  1.1
 *
 * @return int
 */
function erp_ac_get_expense_tax_total( $charts ) {
    $expense_journals     = isset( $charts[3] ) ? $charts[3] : [];
    $receivable_tax       = erp_ac_get_tax_receivable_ledger();
    $receivable_tax       = wp_list_pluck( $receivable_tax, 'id' );
    $payable_tax_journals = [];
    $expense_tax_total    = 0;

    foreach ( $expense_journals as $key => $ledger_jours ) {
        if ( in_array( $key, $receivable_tax ) ) {
            $expense_tax_total  = $expense_tax_total + array_sum( wp_list_pluck( $ledger_jours, 'debit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'credit' ) );
        }

    }

    return $expense_tax_total;
}
