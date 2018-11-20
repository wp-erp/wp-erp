<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all purchases
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_purchases() {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_purchase_account_details", ARRAY_A );

    return $rows;
}

/**
 * Get a purchase
 *
 * @param $purchase_no
 * @return mixed
 */
function erp_acct_get_purchase( $purchase_no ) {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_purchase_account_details WHERE voucher_no = {$purchase_no}", ARRAY_A );

    return $rows;
}

/**
 * Insert a purchase
 *
 * @var ClassName $wpdb
 *
 * @param $data
 * @param $purchase_id
 * @param $due
 * @return mixed
 */
function erp_acct_insert_purchase( $data, $purchase_id ) {
    global $wpdb; $purchase_data = [];

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'            => $data['type'],
    ) );

    $voucher_no = $wpdb->insert_id ;

    $purchase_data = erp_acct_get_formatted_purchase_data( $data, $voucher_no );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase', array(
        'voucher_no'      => $purchase_data['voucher_no'],
        'vendor_id'       => $purchase_data['vendor_id'],
        'trn_date'        => $purchase_data['trn_date'],
        'due_date'        => $purchase_data['due_date'],
        'amount'          => $purchase_data['total'],
        'ref'             => $purchase_data['ref'],
        'status'          => $purchase_data['status'],
        'attachments'     => $purchase_data['attachments']
    ) );

    $items = $data['line_items'];

    foreach( $items as $key => $item ) {
        $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_details', array(
            'trn_no'     => $voucher_no,
            'product_id' => $item['product_id'],
            'qty'        => $item['qty'],
            'unit_price' => $item['unit_price'],
            'amount'     => $item['amount']
        ) );
    }

    $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
        'purchase_no'    => $voucher_no,
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $purchase_data['amount']
    ) );

    return $voucher_no;

}

/**
 * Update a purchase
 *
 * @param $data
 * @param $purchase_id
 * @param $due
 * @return mixed
 */
function erp_acct_update_purchase( $data, $purchase_id ) {

    global $wpdb; $purchase_data = [];

    $purchase_data = erp_acct_get_formatted_purchase_data( $data, $purchase_id );

    $wpdb->update( $wpdb->prefix . 'erp_acct_purchases', array(
        'vendor_id'       => $purchase_data['vendor_id'],
        'trn_date'        => $purchase_data['trn_date'],
        'due_date'        => $purchase_data['due_date'],
        'amount'          => $purchase_data['total'],
        'ref'             => $purchase_data['ref'],
        'status'          => $purchase_data['status'],
        'attachments'     => $purchase_data['attachments']
    ), array(
        'voucher_no'      => $purchase_id
    ) );

    $items = $data['line_items'];

    foreach( $items as $key => $item ) {
        $wpdb->update( $wpdb->prefix . 'erp_acct_purchase_details', array(
            'ledger_id'   => $item['ledger_id'],
            'particulars' => $item['particulars'],
            'amount'      => $item['amount']
        ), array(
            'trn_no'      => $purchase_id,
        ) );
    }

    $wpdb->update( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
        'purchase_no'    => $purchase_id,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $purchase_data['amount']
    ), array(
        'trn_no'     => $purchase_id
    ) );

    return $purchase_id;

}

/**
 * Delete a purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_purchase( $id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_purchase_account_details', array( 'purchase_no' => $id ) );
}

/**
 * Void a purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_void_purchase( $id ) {

}

/**
 * Get formatted purchase data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_purchase_data( $data, $voucher_no ) {
    $purchase_data['vendor_id'] = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 1;
    $purchase_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $purchase_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $purchase_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $purchase_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $purchase_data['status'] = isset( $data['status'] ) ? $data['status'] : '';

    return $purchase_data;
}
