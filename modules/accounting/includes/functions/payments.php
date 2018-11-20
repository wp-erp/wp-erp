<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all payments
 *
 * @return mixed
 */

function erp_acct_get_payments() {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_invoice_receipts", ARRAY_A );

    return $rows;
}

/**
 * Get a single payment
 *
 * @param $invoice_no
 *
 * @return mixed
 */

function erp_acct_get_payment( $invoice_no ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_invoice_receipts WHERE voucher_no = {$invoice_no}", ARRAY_A );

    return $row;
}

/**
 * Insert payment info
 *
 * @param $payment_data
 *
 * @return mixed
 */

function erp_acct_insert_payment( $payment_data ) {
    global $wpdb;

    $items = $payment_data['line_items'];

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'            => $payment_data['type'],
    ) );

    $voucher_no = $wpdb->insert_id ;

    $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_receipts', array(
        'voucher_no' => $voucher_no,
        'trn_date'   => date("Y-m-d" ),
        'particulars'=> 'received',
        'amount'     => $payment_data['amount'],
        'trn_by'     => $payment_data['trn_by']
    ) );

    foreach ( $items as $key => $item ) {
        $total = 0; $due = 0;

        $payment_id[$key] = $item['payment_id'];
        $total += $item['line_total'];

        $payment_data['amount'] = $total;

        erp_acct_insert_payment_line_items( $payment_data, $payment_id[$key], $voucher_no );
    }
}

/**
 * Insert payment line items
 *
 * @param $data
 * @param $invoice_no
 * @param $voucher_no
 * @param $due
 * @return int
 */
function erp_acct_insert_payment_line_items( $data, $invoice_no, $voucher_no ) {
    global $wpdb;

    $payment_data = erp_acct_get_formatted_payment_data( $data, $voucher_no, $invoice_no );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
        'invoice_no' => $invoice_no,
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $payment_data['amount']
    ) );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_receipts_details', array(
        'voucher_no' => $voucher_no,
        'invoice_no' => $invoice_no,
        'amount'     => $payment_data['amount'],
    ) );

    erp_acct_change_invoice_status( $invoice_no );

    return $voucher_no;

}

/**
 * Update payment data
 *
 * @param $data
 * @param $invoice_no
 * @return int
 */
function erp_acct_update_payment( $data, $invoice_no ) {
    global $wpdb; $payment_data = [];

    $payment_data = erp_acct_get_formatted_payment_data( $data, $invoice_no );

    $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_receipts', array(
        'trn_date'   => date("Y-m-d" ),
        'particulars'=> 'received',
        'amount'     => $payment_data['amount'],
        'trn_by'     => $payment_data['trn_by']
    ), array(
        'voucher_no' => $invoice_no,
    ) );

    $items = $payment_data['line_items'];

    foreach ( $items as $key => $item ) {
        $total = 0;

        $payment_id[$key] = $item['invoice_id'];
        $total += $item['line_total'];

        $payment_data['amount'] = $total;

        erp_acct_update_payment_line_items( $payment_data, $payment_id[$key], $invoice_no );
    }

    erp_acct_change_invoice_status( $invoice_no );

    return $invoice_no;

}

/**
 * Insert payment line items
 *
 * @param $data
 * @param $invoice_no
 * @param $voucher_no
 * @param $due
 * @return int
 */
function erp_acct_update_payment_line_items( $data, $invoice_no, $voucher_no ) {
    global $wpdb;

    $payment_data = erp_acct_get_formatted_payment_data( $data, $invoice_no );

    $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => 0,
        'credit'     => $payment_data['amount']
    ), array(
        'invoice_no' => $invoice_no,
    ) );

    $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_receipts_details', array(
        'voucher_no' => $voucher_no,
        'amount'     => $payment_data['amount'],
    ), array(
        'invoice_no' => $invoice_no,
    ) );

    erp_acct_change_invoice_status( $invoice_no );

    return $voucher_no;

}

/**
 * Get formatted payment data
 *
 * @param $data
 * @param $voucher_no
 * @param $invoice_no
 * @return mixed
 */
function erp_acct_get_formatted_payment_data( $data, $voucher_no ) {
    $payment_data = [];

    $payment_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $payment_data['customer_id'] = isset( $data['customer_id'] ) ? $data['customer_id'] : 1;

    $payment_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $payment_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $payment_data['created_at'] = date("Y-m-d" );
    $payment_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $payment_data['discount'] = isset( $data['discount'] ) ? $data['discount'] : 0;
    $payment_data['tax'] = isset( $data['tax'] ) ? $data['tax'] : 0;
    $payment_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $payment_data['type'] = isset( $data['type'] ) ? $data['type'] : '';
    $payment_data['trn_by'] = isset( $data['trn_by'] ) ? $data['trn_by'] : '';

    return $payment_data;
}

/**
 * Delete a payment
 *
 * @param $id
 *
 * @return void
 */

function erp_acct_delete_payment( $id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_invoice_receipts', array( 'voucher_no' => $id ) );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_invoice_receipts_details', array( 'voucher_no' => $id ) );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_invoice_account_details', array( 'invoice_no' => $id ) );
}

/**
 * Void a payment
 *
 * @param $id
 *
 * @return void
 */

function erp_acct_void_payment( $id ) {

}

/**
 * Update payment status after a transaction
 *
 * @param $invoice_no
 * @param $due
 * @return int
 */
function erp_acct_change_invoice_status( $invoice_no ) {
    global $wpdb;

    $due = erp_acct_get_invoice_due( $invoice_no );

    if ( $due > 0 || !$invoice_no ) {
        return;
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_invoice',
        array(
            'status' => 'paid',
        ),
        array( 'voucher_no' => $invoice_no )
    );
}

/**
 * Get due of an invoice
 *
 * @param $invoice_no
 * @return int
 */
function erp_acct_get_invoice_due( $invoice_no ) {

}







