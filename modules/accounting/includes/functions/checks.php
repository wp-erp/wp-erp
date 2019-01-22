<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
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
    $check_data['check_trn_table_id'] = isset( $data['check_trn_table_id'] ) ? $data['check_trn_table_id'] : 1;
    $check_data['people_name'] = isset( $data['people_name'] ) ? $data['people_name'] : '';
    $check_data['pay_to'] = isset( $data['billing_address'] ) ? $data['billing_address'] : '';
    $check_data['trn_date']   = isset( $data['trn_date'] ) ? $data['trn_date'] : date("Y-m-d" );
    $check_data['ledger_id']   = isset( $data['ledger_id'] ) ? $data['ledger_id'] : 0;
    $check_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $check_data['status'] = isset( $data['status'] ) ? $data['status'] : 1;
    $check_data['particulars'] = isset( $data['particulars'] ) ? $data['particulars'] : '';
    $check_data['created_at'] = date("Y-m-d" );
    $check_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $check_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $check_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $check_data;
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
    
    $wpdb->insert( $wpdb->prefix . 'erp_acct_checks', array(
        'trn_no' => $trn_data['trn_no'],
        'check_trn_table_id' => $trn_data['check_trn_table_id'],
        'people_name' => $trn_data['people_name'],
        'pay_to' => $trn_data['pay_to'],
        'debit' => $trn_data['debit'],
        'credit' => $trn_data['credit'],
        'trn_date' => $trn_data['trn_date'],
        'ledger_id' => $trn_data['ledger_id'],
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
    }

    erp_acct_insert_check_data_into_ledger( $trn_data, $item );
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
        'ledger_id'   => $item_data['ledger_id'],
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
        'ledger_id'   => $item_data['ledger_id'],
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
