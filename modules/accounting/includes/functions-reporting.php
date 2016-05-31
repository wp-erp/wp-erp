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
            'description' => __( 'See the sales tax summary.', 'erp' )
        ],
        'income-statement' => [
             'title'       => __( 'Income Statement', 'erp' ),
             'description' => __( 'A summary of a management\'s performance as reflecte the profitability of an organization during the time interval.', 'erp' )
        ],
        'balance-sheet' => [
            'title'       => '',
            'description' => ''
        ],
        // 'profit-loss' => [
        //     'title'       => __( 'Profit and Loss', 'accounting' ),
        //     'description' => __( '', 'accounting' )
        // ],
        // 'ar-aging-summary' => [
        //     'title'       => __( 'A/R Aging Summary', 'accounting' ),
        //     'description' => __( '', 'accounting' )
        // ],
        // 'company-snapshot' => [
        //     'title'       => __( 'Company Snapshot', 'accounting' ),
        //     'description' => __( '', 'accounting' )
        // ],

        // 'ap-aging-summary' => [
        //     'title'       => __( 'A/P Aging Summary', 'accounting' ),
        //     'description' => __( '', 'accounting' )
        // ],
        // 'cash-flow' => [
        //     'title'       => __( 'Statement of Cash Flows', 'accounting' ),
        //     'description' => __( '', 'accounting' )
        // ],
        // 'vendor-balance-summary' => [
        //     'title'       => __( 'Vendor Balance Summary', 'accounting' ),
        //     'description' => __( '', 'accounting' )
        // ],
    ];

    return apply_filters( 'erp_hr_reports', $reports );
}

function erp_ac_transaction_report( $transaction_id ) {
    $args = [
        'id'          => $transaction_id,
        'join'        => ['journals'],
        'with_ledger' => true,
        'output_by'   => 'array'
    ];

    $transaction = erp_ac_get_all_transaction( $args );
    return erp_ac_toltip_per_transaction_ledgers( reset( $transaction ) );
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
    $tax_report = wp_cache_get( $cache_key, 'accounting' );

    if ( false === $tax_report ) {
        $tax_report = WeDevs\ERP\Accounting\Model\Transaction::with([ 'journals' => function( $q ) use( $args ) {
            return $q->with([ 'ledger' => function( $l ) use( $args ) {
                return $l->whereIn( 'tax', $args['tax_id'] );
            }]);
        }])->where( 'issue_date', '>=', $args['start'] )
        ->where( 'issue_date', '<=', $args['end'] )
        ->skip($args['offset'])->take($args['number'])->get()->toArray();

        wp_cache_set( $cache_key, $tax_report, 'accounting' );
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
    $tax_report = wp_cache_get( $cache_key, 'accounting' );

    if ( false === $tax_report ) {
        $tax_report = WeDevs\ERP\Accounting\Model\Transaction::with([ 'journals' => function( $q ) use( $args ) {
            return $q->with([ 'ledger' => function( $l ) use( $args ) {
                return $l->whereIn( 'tax', $args['tax_id'] );
            }]);
        }])->where( 'issue_date', '>=', $args['start'] )
        ->where( 'issue_date', '<=', $args['end'] )
        ->count();

        wp_cache_set( $cache_key, $tax_report, 'accounting' );
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

function erp_ac_get_sales_total() {
    $sales_transaction = erp_ac_get_transaction_for_sales();
    $journals          = array_filter( wp_list_pluck( $sales_transaction, 'journals' ) );
    $sales_total       = 0;

    foreach ( $journals as $key => $journal ) {
        $sales_total = $sales_total + array_sum( wp_list_pluck( $journal, 'credit' ) );
    }
    
    return $sales_total;
}

function erp_ac_get_good_sold_total_amount() {
    $sales_transaction = erp_ac_get_transaction_by_journal_id( 24 );
    $journals          = array_filter( wp_list_pluck( $sales_transaction, 'journals' ) );
    $sales_total       = 0;

    foreach ( $journals as $key => $journal ) {
        $sales_total = $sales_total + array_sum( wp_list_pluck( $journal, 'debit' ) );
    }
    
    return $sales_total;
}

function erp_ac_get_expense_total() {
    $expense_transaction = erp_ac_get_expnese_transaction_without_tax();
    $journals            = array_filter( wp_list_pluck( $expense_transaction, 'journals' ) );
    $expense_total       = 0;
 
    foreach ( $journals as $key => $journal ) {
        $expense_total = $expense_total + array_sum( wp_list_pluck( $journal, 'debit' ) );
    }
    
    return $expense_total;
}

function erp_ac_get_tax_total() {
    $tax_transaction = erp_ac_get_transaction_for_tax();

    $journals  = array_filter( wp_list_pluck( $tax_transaction, 'journals' ) );
    $tax_total = 0;

    foreach ( $journals as $key => $journal ) {
        $tax_total = $tax_total + array_sum( wp_list_pluck( $journal, 'debit' ) );
    }
    
    return $tax_total;
}






