<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all pay_purchases
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_pay_purchases() {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_pay_purchases", ARRAY_A );

    return $rows;
}

/**
 * Get a pay_purchase
 *
 * @param $purchase_no
 * @return mixed
 */
function erp_acct_get_pay_purchase( $purchase_no ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_pay_purchases WHERE purchase_no = {$purchase_no}", ARRAY_A );

    return $row;
}

/**
 * Insert a pay_purchase
 *
 * @param $data
 * @param $pay_purchase_id
 * @param $due
 * @return mixed
 */
function erp_acct_insert_pay_purchase( $data, $pay_purchase_id ) {
    global $wpdb; $pay_purchase_data = [];

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'            => $data['type'],
    ) );

    $voucher_no = $wpdb->insert_id ;

    $pay_purchase_data = erp_acct_get_formatted_pay_purchase_data( $data, $voucher_no );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_pay_purchase', array(
        'voucher_no'      => $voucher_no,
        'order_no'        => $pay_purchase_id,
        'trn_date'        => $pay_purchase_data['trn_date'],
        'amount'          => $pay_purchase_data['amount'],
        'trn_by'          => $pay_purchase_data['trn_by']
    ) );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_pay_purchase_account_details', array(
        'purchase_no'=> $voucher_no,
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $pay_purchase_data['amount']
    ) );

    return $voucher_no;

}

/**
 * Update a pay_purchase
 *
 * @param $data
 * @param $pay_purchase_id
 * @param $due
 * @return mixed
 */
function erp_acct_update_pay_purchase( $data, $pay_purchase_id ) {

    global $wpdb; $pay_purchase_data = [];

    $pay_purchase_data = erp_acct_get_formatted_pay_purchase_data( $data, $pay_purchase_id );

    $wpdb->update( $wpdb->prefix . 'erp_acct_pay_purchases', array(
        'order_no'        => $pay_purchase_data['order_no'],
        'vendor_name'     => $pay_purchase_data['vendor_name'],
        'trn_date'        => $pay_purchase_data['trn_date'],
        'amount'          => $pay_purchase_data['amount'],
        'trn_by'          => $pay_purchase_data['trn_by']
    ), array(
        'voucher_no'      => $pay_purchase_id
    ) );

    if ( $pay_purchase_data['type'] != 'pay_purchase' ) {
        return $pay_purchase_id;
    }

    $items = $data['line_items'];

    $wpdb->update( $wpdb->prefix . 'erp_acct_pay_purchase_account_details', array(
        'purchase_no'    => $pay_purchase_id,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $pay_purchase_data['amount']
    ), array(
        'trn_no'     => $pay_purchase_id
    ) );

    return $pay_purchase_id;

}

/**
 * Delete a pay_purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_pay_purchase( $id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_pay_purchases', array( 'voucher_no' => $id ) );
}

/**
 * Void a pay_purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_void_pay_purchase( $id ) {

}

/**
 * Get formatted pay_purchase data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_pay_purchase_data( $data, $voucher_no ) {
    $pay_purchase_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $pay_purchase_data['order_no'] = isset( $data['order_no'] ) ? $data['order_no'] : 1;
    $pay_purchase_data['purchase_details'] = isset( $data['purchase_details'] ) ? $data['purchase_details'] : '';
    $pay_purchase_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $pay_purchase_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $pay_purchase_data['trn_by'] = isset( $data['trn_by'] ) ? $data['trn_by'] : '';
    $pay_purchase_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';

    return $pay_purchase_data;
}
