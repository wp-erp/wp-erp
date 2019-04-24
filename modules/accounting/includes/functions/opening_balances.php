<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all opening_balances
 *
 * @return mixed
 */

function erp_acct_get_all_opening_balances( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'count'      => false,
        's'          => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $where = '';
    $limit = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE opening_balance.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = "SELECT";

    if ( $args['count'] ) {
        $sql .= " COUNT( DISTINCT opening_balance.id ) as total_number";
    } else {
        $sql .= " *";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_opening_balances AS opening_balance LEFT JOIN {$wpdb->prefix}erp_acct_financial_years AS financial_year";
    $sql .= " ON opening_balance.financial_year_id = financial_year.id {$where} GROUP BY financial_year.name ORDER BY financial_year.{$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        $wpdb->get_results($sql);
        return $wpdb->num_rows;
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get opening_balances of a year
 *
 * @param $year_id
 *
 * @return mixed
 */

function erp_acct_get_opening_balance( $year_id ) {
    global $wpdb;

    $sql = "SELECT

    ob.id,
    ob.financial_year_id,
    ob.ledger_id,
    ledger.name,
    ob.chart_id,
    debit,
    credit

    FROM {$wpdb->prefix}erp_acct_opening_balances as ob
    LEFT JOIN {$wpdb->prefix}erp_acct_ledgers as ledger 
    ON ledger.id = ob.ledger_id
    WHERE financial_year_id = {$year_id} AND ob.type = 'ledger'";

    $rows = $wpdb->get_results( $sql, ARRAY_A );

    return $rows;
}

/**
 * Get virtual accounts of a year
 *
 * @param $year_id
 *
 * @return mixed
 */

function erp_acct_get_virtual_acct( $year_id ) {
    global $wpdb;

    $sql = "SELECT

    ob.id,
    ob.financial_year_id,
    ob.ledger_id,
    ob.type,
    debit,
    credit

    FROM {$wpdb->prefix}erp_acct_opening_balances as ob
    WHERE financial_year_id = {$year_id} AND ob.type <> 'ledger'";

    $rows = $wpdb->get_results( $sql, ARRAY_A );

    $rows = erp_acct_get_ob_virtual_acct( $year_id );

    return $rows;
}

/**
 * Insert opening_balance data
 *
 * @param $data
 * @return mixed
 */
function erp_acct_insert_opening_balance( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $opening_balance_data = erp_acct_get_formatted_opening_balance_data( $data );

        $items = $opening_balance_data['ledgers'];

        $ledgers = [];

        foreach ( $items as $item ) {
            $ledgers = array_merge( $ledgers, $item );
        }

        $year_id = $opening_balance_data['year'];

        $wpdb->query("DELETE FROM {$wpdb->prefix}erp_acct_opening_balances WHERE financial_year_id = {$year_id}" );

        foreach ( $ledgers as $ledger ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
                'financial_year_id' => $year_id,
                'ledger_id' => $ledger['id'],
                'chart_id' => $ledger['chart_id'],
                'type' => 'ledger',
                'debit' => isset( $ledger['debit'] ) ? $ledger['debit'] : 0,
                'credit' => isset( $ledger['credit'] ) ? $ledger['credit'] : 0,
                'created_at' => $opening_balance_data['created_at'],
                'created_by' => $opening_balance_data['created_by'],
                'updated_at' => $opening_balance_data['updated_at'],
                'updated_by' => $opening_balance_data['updated_by'],
            ] );
        }

        erp_acct_insert_ob_vir_accounts( $opening_balance_data, $year_id );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'opening_balance-exception', $e->getMessage() );
    }

    return erp_acct_get_opening_balance( $year_id );

}

/**
 *
 *
 * @param $data
 * @param $year_id
 */
function erp_acct_insert_ob_vir_accounts( $data, $year_id ) {
    global $wpdb;

    if ( !empty( $data['acct_rec'] ) ) {
        foreach ( $data['acct_rec'] as $acct_rec ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
                'financial_year_id' => $year_id,
                'ledger_id' => $acct_rec['people_id']['id'],
                'type' => 'people',
                'debit' => isset( $acct_rec['balance'] ) ?$acct_rec['balance'] : 0,
                'credit' => 0,
                'created_at' => $data['created_at'],
                'created_by' => $data['created_by'],
                'updated_at' => $data['updated_at'],
                'updated_by' => $data['updated_by'],
            ] );
        }
    }

    if ( !empty( $data['acct_pay'] ) ) {
        foreach ( $data['acct_pay'] as $acct_pay ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
                'financial_year_id' => $year_id,
                'ledger_id' => $acct_pay['people_id']['id'],
                'type' => 'people',
                'debit' => 0,
                'credit' => isset( $acct_pay['balance'] ) ? $acct_pay['balance'] : 0,
                'created_at' => $data['created_at'],
                'created_by' => $data['created_by'],
                'updated_at' => $data['updated_at'],
                'updated_by' => $data['updated_by'],
            ] );
        }
    }


    if ( !empty( $data['tax_pay'] ) ) {
        foreach ( $data['tax_pay'] as $tax_pay ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
                'financial_year_id' => $year_id,
                'ledger_id' => $tax_pay['agency']['id'],
                'type' => 'tax_agency',
                'debit' => 0,
                'credit' => isset( $tax_pay['amount'] ) ? $tax_pay['amount'] : 0,
                'created_at' => $data['created_at'],
                'created_by' => $data['created_by'],
                'updated_at' => $data['updated_at'],
                'updated_by' => $data['updated_by'],
            ] );
        }
    }
}


/**
 * Get formatted opening_balance data
 *
 * @param $data
 * @param $voucher_no
 * @return mixed
 */
function erp_acct_get_formatted_opening_balance_data( $data ) {
    $opening_balance_data = [];

    $opening_balance_data['year']         = isset($data['year']) ? $data['year'] : '';
    $opening_balance_data['ledgers']      = isset($data['ledgers']) ? $data['ledgers'] : [];
    $opening_balance_data['descriptions'] = isset($data['descriptions']) ? $data['descriptions'] : '';
    $opening_balance_data['amount']       = isset($data['amount']) ? $data['amount'] : '';
    $opening_balance_data['acct_pay']     = isset($data['acct_pay']) ? $data['acct_pay'] : [];
    $opening_balance_data['acct_rec']     = isset($data['acct_rec']) ? $data['acct_rec'] : [];
    $opening_balance_data['tax_pay']      = isset($data['tax_pay']) ? $data['tax_pay'] : [];
    $opening_balance_data['created_at']   = isset($data['created_at']) ? $data['created_at'] : '';
    $opening_balance_data['created_by']   = isset($data['created_by']) ? $data['created_by'] : '';
    $opening_balance_data['updated_at']   = isset($data['updated_at']) ? $data['updated_at'] : '';
    $opening_balance_data['updated_by']   = isset($data['updated_by']) ? $data['updated_by'] : '';

    return $opening_balance_data;
}

/**
 * Get opening balance names
 */
function erp_acct_get_opening_balance_names() {
    global $wpdb;

    $sql = "SELECT id, name FROM {$wpdb->prefix}erp_acct_financial_years";

    $rows = $wpdb->get_results( $sql, ARRAY_A );

    return $rows;
}

/**
 * Get opening balance date ranges
 *
 * @param $ob_name
 *
 * @return array
 */
function erp_acct_get_start_end_date( $year_id ) {
    $dates = [];
    global $wpdb;

    $sql = "SELECT start_date, end_date FROM {$wpdb->prefix}erp_acct_financial_years WHERE id = {$year_id}";

    $rows = $wpdb->get_row( $sql, ARRAY_A );

    $dates['start'] = $rows['start_date'];
    $dates['end'] = $rows['end_date'];

    return $dates;
}

/**
 * Get virtual accts summary for opening balance
 */
function erp_acct_get_ob_virtual_acct( $year_id ) {
    global $wpdb;


    $vir_ac['acct_receivable'] = $wpdb->get_results( "select ledger_id as people_id, debit, credit from {$wpdb->prefix}erp_acct_opening_balances where financial_year_id = {$year_id} and debit=0 and type='people'", ARRAY_A );
    $vir_ac['acct_payable']    = $wpdb->get_results( "select ledger_id as people_id, debit, credit from {$wpdb->prefix}erp_acct_opening_balances where financial_year_id = {$year_id} and credit=0 and type='people'", ARRAY_A );
    $vir_ac['tax_payable']     = $wpdb->get_results( "select ledger_id as agency_id, debit, credit from {$wpdb->prefix}erp_acct_opening_balances where financial_year_id = {$year_id} and credit=0 and type='tax_agency'", ARRAY_A );

    for( $i = 0; $i < count( $vir_ac['acct_payable'] ); $i++ ) {
        $vir_ac['acct_payable'][$i]['people']['id'] = $vir_ac['acct_payable'][$i]['people_id'] ;
        $vir_ac['acct_payable'][$i]['people']['name'] = erp_acct_get_people_name_by_people_id( $vir_ac['acct_payable'][$i]['people_id'] );
    }

    for( $i = 0; $i < count( $vir_ac['acct_receivable'] ); $i++ ) {
        $vir_ac['acct_receivable'][$i]['people']['id'] = $vir_ac['acct_receivable'][$i]['people_id'] ;
        $vir_ac['acct_receivable'][$i]['people']['name'] = erp_acct_get_people_name_by_people_id( $vir_ac['acct_receivable'][$i]['people_id'] );
    }

    for( $i = 0; $i < count( $vir_ac['tax_payable'] ); $i++ ) {
        $vir_ac['tax_payable'][$i]['agency']['id'] = $vir_ac['tax_payable'][$i]['agency_id'] ;
        $vir_ac['tax_payable'][$i]['agency']['name'] = erp_acct_get_tax_agency_name_by_id( $vir_ac['tax_payable'][$i]['agency_id'] );
    }

    return $vir_ac;

}
