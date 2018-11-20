<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all pay_bills
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_pay_bills() {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_invoice", ARRAY_A );

    return $rows;
}

/**
 * Get a pay_bill
 *
 * @param $bill_no
 * @return mixed
 */
function erp_acct_get_pay_bill( $bill_no ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_invoice WHERE voucher_no = {$bill_no}", ARRAY_A );

    return $row;
}

/**
 * Insert a pay_bill
 *
 * @param $data
 * @param $pay_bill_id
 * @param $due
 * @return mixed
 */
function erp_acct_insert_pay_bill( $data, $pay_bill_id ) {
    global $wpdb;

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'            => $data['type'],
    ) );

    $voucher_no = $wpdb->insert_id ;

    $pay_bill_data = erp_acct_get_formatted_pay_bill_data( $data, $voucher_no );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_pay_bill', array(
        'voucher_no'      => $voucher_no,
        'bill_no'         => $pay_bill_id,
        'trn_date'        => $pay_bill_data['trn_date'],
        'amount'          => $pay_bill_data['amount'],
        'remarks'         => $pay_bill_data['particulars'],
        'attachments'     => $pay_bill_data['attachments']
    ) );


    $wpdb->insert( $wpdb->prefix . 'erp_acct_bill_account_details', array(
        'bill_no'    => $voucher_no,
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => $pay_bill_data['amount'],
        'credit'     => 0
    ) );

    return $voucher_no;

}

/**
 * Update a pay_bill
 *
 * @param $data
 * @param $pay_bill_id
 * @param $due
 * @return mixed
 */
function erp_acct_update_pay_bill( $data, $pay_bill_id ) {

    global $wpdb; $pay_bill_data = [];

    $pay_bill_data = erp_acct_get_formatted_pay_bill_data( $data, $pay_bill_id );

    $wpdb->update( $wpdb->prefix . 'erp_acct_pay_bill', array(
        'bill_no'         => $pay_bill_data['bill_no'],
        'trn_date'        => $pay_bill_data['trn_date'],
        'amount'          => $pay_bill_data['total'],
        'type'            => $pay_bill_data['type'],
        'particulars'     => $pay_bill_data['particulars'],
        'attachments'     => $pay_bill_data['attachments']
    ), array(
        'voucher_no'      => $pay_bill_id
    ) );

    $items = $data['line_items'];

    $wpdb->update( $wpdb->prefix . 'erp_acct_bill_account_details', array(
        'bill_no'    => $pay_bill_id,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $pay_bill_data['amount']
    ), array(
        'trn_no'     => $pay_bill_id
    ) );

    return $pay_bill_id;

}

/**
 * Delete a pay_bill
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_pay_bill( $id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_pay_bill', array( 'ID' => $id ) );
}

/**
 * Void a pay_bill
 *
 * @param $id
 * @return void
 */
function erp_acct_void_pay_bill( $id ) {

}

/**
 * Get formatted pay_bill data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_pay_bill_data( $data, $voucher_no ) {
    $pay_bill_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $pay_bill_data['vendor_id'] = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 1;
    $pay_bill_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $pay_bill_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $pay_bill_data['ref'] = isset( $data['ref'] ) ? $data['ref'] : 0;
    $pay_bill_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $pay_bill_data['bill_details'] = isset( $data['bill_details'] ) ? $data['bill_details'] : '';

    return $pay_bill_data;
}
