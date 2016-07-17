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

function erp_ac_reporting_query() {
    global $wpdb;
    $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
    $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
    $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    $financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    $financial_end   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

    $sql = "SELECT led.id, led.code, led.name, led.type_id, types.name as type_name, types.class_id, class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit
    FROM $tbl_ledger as led
    LEFT JOIN $tbl_type as types ON types.id = led.type_id
    LEFT JOIN $tbl_class as class ON class.id = types.class_id
    LEFT JOIN $tbl_journals as jour ON jour.ledger_id = led.id
    LEFT JOIN $tbl_transaction as tran ON tran.id = jour.transaction_id
    WHERE ( tran.status IS NULL OR ( tran.status != 'draft' AND tran.status != 'void' AND tran.status != 'deleted' ) ) AND ( tran.issue_date >= '$financial_start' AND tran.issue_date <= '$financial_end' )
    GROUP BY led.id";

    $ledgers = $wpdb->get_results( $sql );

    return $ledgers;
}

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

    $args  = wp_parse_args( $args, $defaults );

    $cache_key  = 'erp-ac-tax-report' . md5( serialize( $args ) ) . md5( serialize( get_current_user_id() ) );
    $tax_report = wp_cache_get( $cache_key, 'erp' );

    if ( false === $tax_report ) {
        $tax_report = WeDevs\ERP\Accounting\Model\Transaction::with([ 'journals' => function( $q ) use( $args ) {
            return $q->with([ 'ledger' => function( $l ) use( $args ) {
                return $l->whereIn( 'tax', $args['tax_id'] );
            }]);
        }])->where( 'issue_date', '>=', $args['start'] )
        ->where( 'issue_date', '<=', $args['end'] )
        ->skip($args['offset'])->take($args['number'])->get()->toArray();

        wp_cache_set( $cache_key, $tax_report, 'erp' );
    }

    return $tax_report;
}

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

    $args  = wp_parse_args( $args, $defaults );

    $cache_key  = 'erp-ac-tax-report_count' . md5( serialize( $args ) ) . md5( serialize( get_current_user_id() ) );
    $tax_report = wp_cache_get( $cache_key, 'erp' );

    if ( false === $tax_report ) {
        $tax_report = WeDevs\ERP\Accounting\Model\Transaction::with([ 'journals' => function( $q ) use( $args ) {
            return $q->with([ 'ledger' => function( $l ) use( $args ) {
                return $l->whereIn( 'tax', $args['tax_id'] );
            }]);
        }])->where( 'issue_date', '>=', $args['start'] )
        ->where( 'issue_date', '<=', $args['end'] )
        ->count();

        wp_cache_set( $cache_key, $tax_report, 'erp' );
    }

    return $tax_report;
}

function erp_ac_normarlize_tax_from_transaction( $args = [] ) {
    $transactions = erp_ac_get_sales_tax_report( $args );

    $individual_info = [];
    $tax_info        = erp_ac_get_tax_info();

    foreach ( $tax_info as $tax_id => $tax_elemet ) {

        foreach ( $transactions as $key => $tax ) {

            foreach ( $tax['journals'] as $jour_key => $journal ) {

                if ( ! count( $journal['ledger'] ) ) {
                    unset( $tax['journals'][$jour_key] );
                } else if ( count( $journal['ledger'] ) && $tax_id != $journal['ledger']['tax'] ) {
                    unset( $tax['journals'][$jour_key] );
                }
            }

            $tax['tax_debit'] = array_sum( wp_list_pluck( $tax['journals'], 'debit' ) );
            $tax['tax_credit'] = array_sum( wp_list_pluck( $tax['journals'], 'credit' ) );

            foreach ( $tax['journals'] as $jour_key => $journal ) {

               $individual_info[$tax_id][$tax['type']][] = $tax;
            }
        }
    }

    $tax_unit_info = [];

    foreach ( $individual_info  as $tax_id => $tax_type ) {
        $sales = isset( $tax_type['sales'] ) ? $tax_type['sales'] : [];

        $tax_unit_info[$tax_id]['sales']['trns_subtotal'] = array_sum( wp_list_pluck( $sales, 'sub_total' ) );
        $tax_unit_info[$tax_id]['sales']['trns_total']    = array_sum( wp_list_pluck( $sales, 'trans_total' ) );
        $tax_unit_info[$tax_id]['sales']['trns_due']      = array_sum( wp_list_pluck( $sales, 'due' ) );
        $tax_unit_info[$tax_id]['sales']['total']         = array_sum( wp_list_pluck( $sales, 'total' ) );

        $tax_unit_info[$tax_id]['sales']['tax_id']        = $tax_info[$tax_id]['id'];
        $tax_unit_info[$tax_id]['sales']['tax_name']      = $tax_info[$tax_id]['name'];
        $tax_unit_info[$tax_id]['sales']['tax_number']    = $tax_info[$tax_id]['number'];
        $tax_unit_info[$tax_id]['sales']['rate']          = $tax_info[$tax_id]['rate'];

        $tax_unit_info[$tax_id]['sales']['tax_debit'] = array_sum( wp_list_pluck( $sales, 'tax_debit' ) );
        $tax_unit_info[$tax_id]['sales']['tax_credit'] = array_sum( wp_list_pluck( $sales, 'tax_credit' ) );



        $expense = isset( $tax_type['expense'] ) ? $tax_type['expense'] : [];

        $tax_unit_info[$tax_id]['expense']['trns_subtotal'] = array_sum( wp_list_pluck( $expense, 'sub_total' ) );
        $tax_unit_info[$tax_id]['expense']['trns_total']    = array_sum( wp_list_pluck( $expense, 'trans_total' ) );
        $tax_unit_info[$tax_id]['expense']['trns_due']      = array_sum( wp_list_pluck( $expense, 'due' ) );
        $tax_unit_info[$tax_id]['expense']['total']         = array_sum( wp_list_pluck( $expense, 'total' ) );

        $tax_unit_info[$tax_id]['expense']['tax_id']        = $tax_info[$tax_id]['id'];
        $tax_unit_info[$tax_id]['expense']['tax_name']      = $tax_info[$tax_id]['name'];
        $tax_unit_info[$tax_id]['expense']['tax_number']    = $tax_info[$tax_id]['number'];
        $tax_unit_info[$tax_id]['expense']['rate']          = $tax_info[$tax_id]['rate'];


        $tax_unit_info[$tax_id]['expense']['tax_debit'] = array_sum( wp_list_pluck( $expense, 'tax_debit' ) );
        $tax_unit_info[$tax_id]['expense']['tax_credit'] = array_sum( wp_list_pluck( $expense, 'tax_credit' ) );

    }

    return array( 'individuals' => $individual_info, 'units' => $tax_unit_info );

}

function erp_ac_get_sales_total_without_tax( $charts ) {

    $sales_journals  = isset( $charts[4] ) ? $charts[4] : [];
    $sales_total    = 0;
    
    foreach ( $sales_journals as $key => $ledger_jours ) {
        $sales_total  = $sales_total + array_sum( wp_list_pluck( $ledger_jours, 'credit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'debit' ) );
    }
    
    return $sales_total;
}

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

function erp_ac_get_good_sold_total_amount( $charts ) {
    $sales_journals = isset( $charts[3] ) ? $charts[3] : [];
    $goods_sold     = isset( $sales_journals[24] ) ? $sales_journals[24] : [];
    $sales_total    = 0;
    $sales_total    = array_sum( wp_list_pluck( $goods_sold, 'debit' ) ) - array_sum( wp_list_pluck( $goods_sold, 'credit' ) );
    
    return $sales_total;
}

function erp_ac_get_expense_total_without_tax( $charts ) {
    $expense_journals     = isset( $charts[3] ) ? $charts[3] : [];
    $receivable_tax       = erp_ac_get_tax_receivable_ledger();
    $receivable_tax       = wp_list_pluck( $receivable_tax, 'id' );
    $payable_tax_journals = [];
    $expense_total        = 0;
    
    foreach ( $expense_journals as $key => $ledger_jours ) {
        if ( in_array( $key, $receivable_tax ) ) {
            continue;
        }
        $expense_total  = $expense_total + array_sum( wp_list_pluck( $ledger_jours, 'debit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'credit' ) );
    }
    
    return $expense_total;
    
}

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






