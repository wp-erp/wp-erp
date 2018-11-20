<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all bills
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_bills() {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_bill", ARRAY_A );

    return $rows;
}

/**
 * Get a single bill
 *
 * @param $bill_no
 * @return mixed
 */
function erp_acct_get_bill( $bill_no ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_bill WHERE voucher_no = {$bill_no}", ARRAY_A );

    return $row;
}

/**
 * Insert a bill
 *
 * @param $data
 * @param $bill_id
 * @return mixed
 */
function erp_acct_insert_bill( $data, $bill_id ) {
    global $wpdb; $bill_data = [];

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'            => $data['type'],
    ) );

    $voucher_no = $wpdb->insert_id ;

    $bill_data = erp_acct_get_formatted_bill_data( $data, $voucher_no );

    error_log( print_r( $bill_data, true ) );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_bill', array(
        'voucher_no'      => $bill_data['voucher_no'],
        'vendor_id'       => $bill_data['people_id'],
        'vendor_name'     => $bill_data['vendor_name'],
        'address'         => $bill_data['billing_address'],
        'trn_date'        => $bill_data['trn_date'],
        'due_date'        => $bill_data['due_date'],
        'amount'          => $bill_data['total'],
        'attachments'     => $bill_data['attachments'],
        'ref'             => $bill_data['ref'],
        'remarks'         => $bill_data['remarks'],
        'created_at'      => $bill_data['created_at'],
        'created_by'      => $bill_data['created_by'],
    ) );



    $wpdb->insert( $wpdb->prefix . 'erp_acct_bill_account_details', array(
        'bill_no'    => $voucher_no,
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $bill_data['amount']
    ) );

    return $voucher_no;

}

/**
 * Update a bill
 *
 * @param $data
 * @param $bill_id
 * @param $due
 * @return mixed
 */
function erp_acct_update_bill( $data, $bill_id ) {

    global $wpdb;
    $bill_data = erp_acct_get_formatted_bill_data( $data, $bill_id );

    $wpdb->update( $wpdb->prefix . 'erp_acct_bills', array(
        'vendor_id'       => $bill_data['people_id'],
        'vendor_name'     => $bill_data['vendor_name'],
        'trn_date'        => $bill_data['trn_date'],
        'due_date'        => $bill_data['due_date'],
        'created_at'      => $bill_data['created_at'],
        'address'         => $bill_data['billing_address'],
        'amount'          => $bill_data['total'],
        'type'            => $bill_data['type'],
        'attachments'     => $bill_data['attachments']
        ), array(
        'voucher_no'      => $bill_id
    ) );


    $wpdb->update( $wpdb->prefix . 'erp_acct_bill_account_details', array(
        'bill_no'    => $bill_id,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $bill_data['amount']
    ), array(
        'trn_no'     => $bill_id
    ) );

    return $bill_id;

}

/**
 * Delete a bill
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_bill( $id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_bill', array( 'voucher_no' => $id ) );
}

/**
 * Void a bill
 *
 * @param $id
 * @return void
 */
function erp_acct_void_bill( $id ) {

}

/**
 * Get formatted bill data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_bill_data( $data, $voucher_no ) {
    $bill_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $bill_data['vendor_id'] = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 1;
    $bill_data['vendor_name'] = isset( $data['vendor_name'] ) ? $data['vendor_name'] : '';
    $bill_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $bill_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $bill_data['created_at'] = date("Y-m-d" );
    $bill_data['address'] = isset( $data['address'] ) ? maybe_serialize( $data['address'] ) : '';
    $bill_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $bill_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $bill_data['ref'] = isset( $data['ref'] ) ? $data['ref'] : '';
    $bill_data['remarks'] = isset( $data['remarks'] ) ? $data['remarks'] : '';
    $bill_data['bill_details'] = isset( $data['bill_details'] ) ? $data['bill_details'] : '';
    $bill_data['created_by'] = isset( $bill_data['created_by'] ) ? $bill_data['created_by'] : '';

    return $bill_data;
}
