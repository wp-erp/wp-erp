<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all journals
 *
 * @return mixed
 */

function erp_acct_get_all_journals( $args = [] ) {
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
        $where .= "WHERE journal.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = "SELECT";

    if ( $args['count'] ) {
        $sql .= " COUNT( DISTINCT journal.id ) as total_number";
    } else {
        $sql .= " *";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_journals AS journal LEFT JOIN {$wpdb->prefix}erp_acct_journal_details AS journal_detail";
    $sql .= " ON journal.voucher_no = journal_detail.trn_no {$where} GROUP BY journal.voucher_no ORDER BY journal.{$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var($sql);
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get an single journal
 *
 * @param $journal_no
 *
 * @return mixed
 */

function erp_acct_get_journal( $journal_no ) {
    global $wpdb;

    $sql = "SELECT

    journal.id,
    journal.voucher_no,
    journal.trn_date,
    journal.voucher_amount,
    journal.particulars,
    journal.created_at,
    journal.created_by,
    journal.updated_at, 
    journal.updated_by,
    
    journal_detail.trn_no,
    journal_detail.ledger_id,
    journal_detail.particulars,
    journal_detail.debit,
    journal_detail.credit
    
    FROM {$wpdb->prefix}erp_acct_journals as journal
    LEFT JOIN {$wpdb->prefix}erp_acct_journal_details as journal_detail ON journal.voucher_no = journal_detail.trn_no
    WHERE journal.id = {$journal_no} LIMIT 1";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
}

/**
 * Insert journal data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_journal( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'        => 'journal',
            'currency'    => '',
            'created_at'  => $data['created_at'],
            'created_by'  => $data['created_by'],
            'updated_at'  => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
            'updated_by'  => isset( $data['updated_by'] ) ? $data['updated_by'] : ''
        ) );

        $voucher_no = $wpdb->insert_id;

        $journal_data = erp_acct_get_formatted_journal_data( $data, $voucher_no );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_journals', array(
            'voucher_no'      => $voucher_no,
            'trn_date'        => $journal_data['trn_date'],
            'voucher_amount'  => $journal_data['voucher_amount'],
            'particulars'     => $journal_data['particulars'],
            'created_at'      => $journal_data['created_at'],
            'created_by'      => $journal_data['created_by'],
            'updated_at'      => $journal_data['updated_at'],
            'updated_by'      => $journal_data['updated_by'],
        ) );

        $items = $journal_data['line_items'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_journal_details', array(
                'trn_no'      => $voucher_no,
                'ledger_id'   => $item['ledger_id'],
                'particulars' => $item['particulars'],
                'debit'       => $item['debit'],
                'credit'      => $item['credit'],
                'created_at'  => $journal_data['created_at'],
                'created_by'  => $journal_data['created_by'],
                'updated_at'  => $journal_data['updated_at'],
                'updated_by'  => $journal_data['updated_by'],
            ) );
        }

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'journal-exception', $e->getMessage() );
    }

    return $voucher_no;

}

/**
 * Update journal data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_journal( $data, $journal_no ) {
    global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $journal_data = erp_acct_get_formatted_journal_data( $data, $journal_no );

        $wpdb->update( $wpdb->prefix . 'erp_acct_journals', array(
            'trn_date'        => $journal_data['trn_date'],
            'voucher_amount'  => $journal_data['voucher_amount'],
            'particulars'     => $journal_data['particulars'],
            'created_at'      => $journal_data['created_at'],
            'created_by'      => $journal_data['created_by'],
            'updated_at'      => $journal_data['updated_at'],
            'updated_by'      => $journal_data['updated_by'],
        ), array(
            'voucher_no' => $journal_no,
        ) );

        $items = $journal_data['line_items'];

        foreach ( $items as $key => $item ) {
            $wpdb->update( $wpdb->prefix . 'erp_acct_journal_details', array(
                'ledger_id'   => $item['ledger_id'],
                'particulars' => $item['particulars'],
                'debit'       => $item['debit'],
                'credit'      => $item['credit'],
                'created_at'  => $journal_data['created_at'],
                'created_by'  => $journal_data['created_by'],
                'updated_at'  => $journal_data['updated_at'],
                'updated_by'  => $journal_data['updated_by'],
            ), array(
                'trn_no' => $journal_no,
            ) );
        }

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'journal-exception', $e->getMessage() );
    }

    return $journal_no;

}

/**
 * Get formatted journal data
 *
 * @param $data
 * @param $voucher_no
 * @return mixed
 */
function erp_acct_get_formatted_journal_data( $data, $voucher_no ) {
    $journal_data = [];

    $journal_data['voucher_no'] = !empty($voucher_no) ? $voucher_no : 0;
    $journal_data['trn_date'] = isset($data['date']) ? $data['date'] : date("Y-m-d");
    $journal_data['voucher_amount'] = isset($data['voucher_amount']) ? $data['voucher_amount'] : 0;
    $journal_data['line_items'] = isset($data['line_items']) ? $data['line_items'] : array();
    $journal_data['particulars'] = isset($data['particulars']) ? $data['particulars'] : '';
    $journal_data['created_at'] = isset($data['created_at']) ? $data['created_at'] : '';
    $journal_data['created_by'] = isset($data['created_by']) ? $data['created_by'] : '';
    $journal_data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : '';
    $journal_data['updated_by'] = isset($data['updated_by']) ? $data['updated_by'] : '';

    return $journal_data;
}

