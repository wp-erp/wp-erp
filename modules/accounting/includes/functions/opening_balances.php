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

    error_log( print_r( $sql, true ) );

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get an single opening_balance
 *
 * @param $year_id
 *
 * @return mixed
 */

function erp_acct_get_opening_balance( $year_id ) {
    global $wpdb;

    $sql = "SELECT

    opening_balance.id,
    opening_balance.financial_year_id,
    opening_balance.ledger_id,
    opening_balance.debit,
    opening_balance.credit,
    financial_year.name,
    financial_year.description,
    financial_year.created_at,
    financial_year.created_by,
    financial_year.updated_at,
    financial_year.updated_by

    FROM {$wpdb->prefix}erp_acct_opening_balances as opening_balance
    LEFT JOIN {$wpdb->prefix}erp_acct_financial_years as financial_year ON opening_balance.financial_year_id = financial_year.id
    WHERE financial_year.name = {$year_id}";

    error_log( print_r( $sql, true ) );

    $rows = $wpdb->get_results( $sql, ARRAY_A );

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

        $wpdb->insert( $wpdb->prefix . 'erp_acct_financial_years', array(
            'name' => $opening_balance_data['year'],
            'description' => $opening_balance_data['description'],
            'created_at' => $opening_balance_data['created_at'],
            'created_by' => $opening_balance_data['created_by'],
            'updated_at' => $opening_balance_data['updated_at'],
            'updated_by' => $opening_balance_data['updated_by'],
        ) );

        $year_id = $wpdb->insert_id;

        $items = $opening_balance_data['ledgers'];

        $ledgers = [];

        foreach ( $items as $item ) {
            $ledgers = array_merge( $ledgers, $item );
        }

        foreach ( $ledgers as $ledger ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
                'financial_year_id' => $year_id,
                'ledger_id' => $ledger['id'],
                'debit' => isset( $ledger['debit'] ) ? $ledger['debit'] : 0,
                'credit' => isset( $ledger['credit'] ) ? $ledger['credit'] : 0,
                'created_at' => $opening_balance_data['created_at'],
                'created_by' => $opening_balance_data['created_by'],
                'updated_at' => $opening_balance_data['updated_at'],
                'updated_by' => $opening_balance_data['updated_by'],
            ] );
        }

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'opening_balance-exception', $e->getMessage() );
    }

    return erp_acct_get_opening_balance( $year_id );

}

/**
 * Update opening_balance data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_opening_balance( $data, $opening_balance_no ) {
    global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $opening_balance_data = erp_acct_get_formatted_opening_balance_data( $data );

        $wpdb->update( $wpdb->prefix . 'erp_acct_financial_years', array(
            'description' => $opening_balance_data['description'],
            'created_at' => $opening_balance_data['created_at'],
            'created_by' => $opening_balance_data['created_by'],
            'updated_at' => $opening_balance_data['updated_at'],
            'updated_by' => $opening_balance_data['updated_by'],
        ), array(
            'name' => $opening_balance_data['year'],
        ) );

        $items = $opening_balance_data['ledgers'];

        $ledgers = [];

        foreach ( $items as $item ) {
            $ledgers = array_merge( $ledgers, $item );
        }

        foreach ( $ledgers as $ledger ) {
            $wpdb->update( $wpdb->prefix . 'erp_acct_opening_balances', array(
                'ledger_id' => $ledger['id'],
                'debit' => isset( $ledger['debit'] ) ? $ledger['debit'] : 0,
                'credit' => isset( $ledger['credit'] ) ? $ledger['credit'] : 0,
                'created_at' => $opening_balance_data['created_at'],
                'created_by' => $opening_balance_data['created_by'],
                'updated_at' => $opening_balance_data['updated_at'],
                'updated_by' => $opening_balance_data['updated_by'],
            ), array(
                'financial_year_id' => $opening_balance_no,
            ) );
        }

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'opening_balance-exception', $e->getMessage() );
    }

    return erp_acct_get_opening_balance( $opening_balance_no );

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

    $opening_balance_data['year'] = isset($data['year']) ? $data['year'] : '';
    $opening_balance_data['ledgers'] = isset($data['ledgers']) ? $data['ledgers'] : [];
    $opening_balance_data['descriptions'] = isset($data['descriptions']) ? $data['descriptions'] : '';
    $opening_balance_data['amount'] = isset($data['amount']) ? $data['amount'] : '';
    $opening_balance_data['created_at'] = isset($data['created_at']) ? $data['created_at'] : '';
    $opening_balance_data['created_by'] = isset($data['created_by']) ? $data['created_by'] : '';
    $opening_balance_data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : '';
    $opening_balance_data['updated_by'] = isset($data['updated_by']) ? $data['updated_by'] : '';

    return $opening_balance_data;
}

