<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all checks
 *
 * @return mixed
 */

function erp_acct_get_checks( $args = [] ) {
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

    $limit = '';

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = "SELECT";
    $sql .= $args['count'] ? " COUNT( id ) as total_number " : " trn_no, people_name, payee_name, trn_date, (debit - credit) as amount ";
    $sql .= "FROM {$wpdb->prefix}erp_acct_checks ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var($sql);
    }

    $check_data = $wpdb->get_results( $sql, ARRAY_A );

    return $check_data;
}

/**
 * Get a single check
 *
 * @param $check_no
 *
 * @return mixed
 */

function erp_acct_get_check( $check_no ) {
    global $wpdb;

    $sql = "SELECT * from {$wpdb->prefix}erp_acct_checks as check_tbl WHERE check_tbl.trn_no = {$check_no}";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
}

/**
 * Update a check
 *
 * @param $data
 * @param $check_id
 *
 * @return mixed
 */
function erp_acct_update_check( $data, $check_id ) {
    global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

    $wpdb->update( $wpdb->prefix . 'erp_acct_checks', array(
        'check_trn_table_id' => $data['check_trn_table_id'],
        'people_name'        => $data['people_name'],
        'pay_to'             => $data['pay_to'],
        'trn_date'           => $data['trn_date'],
        'ledger_id'          => $data['ledger_id'],
        'particulars'        => $data['particulars'],
        'debit'              => $data['debit'],
        'credit'             => $data['credit'],
        'created_at'         => $data['created_at'],
        'created_by'         => $data['created_by'],
        'updated_at'         => $data['updated_at'],
        'updated_by'         => $data['updated_by'],
    ), array(
        'trn_no'      => $check_id
    ) );

    return $check_id;

}

/**
 * Delete a check
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_check( $id ) {
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->delete( $wpdb->prefix . 'erp_acct_checks', array( 'trn_no' => $id ) );
}


/**
 * Record check data
 *
 * @param $trn_data
 * @param $trn_no
 * @param $items
 *
 * return boolean
 */
function erp_acct_record_check_data( $trn_data, $trn_no, $items ) {
    global $wpdb;

    $company = new \WeDevs\ERP\Company();
    
    $wpdb->insert( $wpdb->prefix . 'erp_acct_checks', array(
        'trn_no' => $trn_no,
        'check_trn_table_id' => $trn_data['check_trn_table_id'],
        'people_name' => $company->name,
        'pay_to' => $trn_data['pay_to'],
        'debit' => $trn_data['amount'],
        'credit' => 0,
        'trn_date' => $trn_data['trn_date'],
        'ledger_id' => 999,
        'particulars' => $trn_data['particulars'],
        'created_at' => $trn_data['created_at'],
        'created_by' => $trn_data['created_by'],
        'updated_at' => $trn_data['updated_at'],
        'updated_by' => $trn_data['updated_by'],
    ) );

    $check_no = $wpdb->insert_id;

    foreach ( $items as $key => $item ) {
        $wpdb->insert( $wpdb->prefix . 'erp_acct_check_details', array(
            'invoice_no'  => $trn_no,
            'check_id'   => $check_no,
            'amount'      => $item['amount'],
            'created_at'  => $trn_data['created_at'],
            'created_by'  => $trn_data['created_by'],
            'updated_at'  => $trn_data['updated_at'],
            'updated_by'  => $trn_data['updated_by'],
        ) );

        erp_acct_insert_check_data_into_ledger( $trn_data, $item );
    }
}


/**
 * Insert check/s data into ledger
 *
 * @param array $check_data
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_insert_check_data_into_ledger( $check_data, $item_data ) {
    global $wpdb;

    // Insert amount in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 999,
        'trn_no'      => $check_data['trn_no'],
        'particulars' => $check_data['particulars'],
        'debit'       => $item_data['debit'],
        'credit'      => $item_data['credit'],
        'trn_date'    => $check_data['trn_date'],
        'created_at'  => $check_data['created_at'],
        'created_by'  => $check_data['created_by'],
        'updated_at'  => $check_data['updated_at'],
        'updated_by'  => $check_data['updated_by'],
    ) );

}

/**
 * Update check/s data into ledger
 *
 * @param array $check_data
 * * @param array $check_no
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_update_check_data_into_ledger( $check_data, $check_no, $item_data ) {
    global $wpdb;

    // Update amount in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 999,
        'particulars' => $check_data['particulars'],
        'debit'       => $item_data['debit'],
        'credit'      => $item_data['credit'],
        'trn_date'    => $check_data['trn_date'],
        'created_at'  => $check_data['created_at'],
        'created_by'  => $check_data['created_by'],
        'updated_at'  => $check_data['updated_at'],
        'updated_by'  => $check_data['updated_by'],
    ), array(
        'trn_no' => $check_no,
    ) );

}

/**
 * Set check status
 *
 * @param array $args
 *
 * @return boolean
 */
function erp_acct_perform_check_action( $check_data, $args = [] ) {
    global $wpdb;

    $created_by = get_current_user_id();
    $check_data['created_at'] = date("Y-m-d H:i:s");
    $check_data['created_by'] = $created_by;

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'       => 'check',
        'created_at' => $check_data['created_at'],
        'created_by' => $check_data['created_by'],
        'updated_at' => isset( $check_data['updated_at'] ) ? $check_data['updated_at'] : '',
        'updated_by' => isset( $check_data['updated_by'] ) ? $check_data['updated_by'] : ''
    ) );

    $voucher_no = $wpdb->insert_id;

    $wpdb->insert( $wpdb->prefix . 'erp_acct_check_transactions', array(
        'voucher_no'  => $voucher_no,
        'trn_no'      => $check_data['trn_no'],
        'status'      => $check_data['status'],
        'trn_date'    => $check_data['trn_date'],
        'created_at'  => $check_data['created_at'],
        'created_by'  => $check_data['created_by'],
        'updated_at'  => $check_data['updated_at'],
        'updated_by'  => $check_data['updated_by'],
    ) );
}

/**
 * Get check status
 *
 * @param $id
 * @return array|string
 */
function get_check_status_by_id( $id ) {
    global $wpdb;

    $check_statuses = $wpdb->get_results("SELECT trn_no, status FROM {$wpdb->prefix}erp_acct_check_transactions WHERE trn_no = {$id}", ARRAY_A );

    $rowcount = count( $check_statuses );

    if( $rowcount ) {
        return erp_acct_get_trn_status_by_id( $check_statuses['status'] );
    }

    return 'awaiting_approval';
}

/**
 * Get formatted check data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_check_data( $data, $voucher_no ) {
    $check_data = [];

    $check_data['trn_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $check_data['invoice_no'] = isset( $data['invoice_no'] ) ? $data['invoice_no'] : 0;
    $check_data['check_trn_table_id'] = isset( $data['check_trn_table_id'] ) ? $data['check_trn_table_id'] : 1;
    $check_data['people_name'] = isset( $data['people_name'] ) ? $data['people_name'] : '';
    $check_data['pay_to'] = isset( $data['customer_id'] ) ? $data['customer_id'] : '';
    $check_data['trn_date']   = isset( $data['trn_date'] ) ? $data['trn_date'] : date("Y-m-d" );
    $check_data['ledger_id']   = isset( $data['ledger_id'] ) ? $data['ledger_id'] : 0;
    $check_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $check_data['debit'] = isset( $data['debit'] ) ? $data['debit'] : 0;
    $check_data['credit'] = isset( $data['credit'] ) ? $data['credit'] : 0;
    $check_data['status'] = isset( $data['status'] ) ? $data['status'] : 7;
    $check_data['particulars'] = isset( $data['particulars'] ) ? $data['particulars'] : '';
    $check_data['created_at'] = date("Y-m-d" );
    $check_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $check_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $check_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $check_data;
}
