<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all invoices
 *
 * @return mixed
 */

function erp_acct_get_all_invoices() {
    global $wpdb;

    $row = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_invoice", ARRAY_A );

    return $row;
}

/**
 * Get an single invoice
 *
 * @param $invoice_no
 *
 * @return mixed
 */

function erp_acct_get_invoice( $invoice_no ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_invoice WHERE voucher_no = {$invoice_no}", ARRAY_A );

    return $row;
}

/**
 * Insert invoice data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_invoice( $data ) {
    global $wpdb; $invoice_data = [];

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'            => $data['type'],
    ) );

    $voucher_no = $wpdb->insert_id ;

    $invoice_data = erp_acct_get_formatted_invoice_data( $data, $voucher_no );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice', array(
        'voucher_no'      => $invoice_data['voucher_no'],
        'people_id'       => $invoice_data['people_id'],
        'trn_date'        => $invoice_data['trn_date'],
        'due_date'        => $invoice_data['due_date'],
        'created_at'      => $invoice_data['created_at'],
        'billing_address' => $invoice_data['billing_address'],
        'discount'        => $invoice_data['discount'],
        'tax'             => $invoice_data['tax'],
        'amount'          => $invoice_data['amount'],
        'type'            => $invoice_data['type'],
        'attachments'     => $invoice_data['attachments']
    ) );

    if ( $invoice_data['type'] != 'invoice' ) {
        return $voucher_no;
    }

    $items = $data['line_items'];

    foreach( $items as $key => $item ) {
        $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_details', array(
            'trn_no'     => $voucher_no,
            'product_id' => $item['product_id'],
            'qty'        => $item['qty'],
            'unit_price' => $item['unit_price'],
            'discount'   => $item['discount'],
            'tax'        => $item['tax'],
            'item_total' => $item['item_total'],
        ) );
    }

    $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
        'invoice_no' => $voucher_no,
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => $invoice_data['amount'],
        'credit'     => 0
    ) );

//    erp_acct_insert_into_ledger();

    return $voucher_no;

}

/**
 * Update invoice data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_invoice( $data, $invoice_no ) {
    global $wpdb; $invoice_data = [];

    $invoice_data = erp_acct_get_formatted_invoice_data( $data, $invoice_no );

    $wpdb->update( $wpdb->prefix . 'erp_acct_invoice', array(
        'people_id'       => $invoice_data['people_id'],
        'trn_date'        => $invoice_data['trn_date'],
        'due_date'        => $invoice_data['due_date'],
        'created_at'      => $invoice_data['created_at'],
        'billing_address' => $invoice_data['billing_address'],
        'amount'          => $invoice_data['amount'],
        'discount'        => $invoice_data['discount'],
        'tax'             => $invoice_data['tax'],
        'attachments'     => $invoice_data['attachments'],
        'type'            => $invoice_data['type']
    ), array(
        'voucher_no'      => $invoice_no,
    ) );

    if ( $invoice_data['type'] != 'invoice' ) {
        return $invoice_no;
    }

    $items = $data['line_items'];

    foreach( $items as $key => $item ) {
        $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_details', array(
            'product_id' => $item['product_id'],
            'qty'        => $item['qty'],
            'unit_price' => $item['unit_price'],
            'discount'   => $item['discount'],
            'tax'        => $item['tax'],
            'item_total' => $item['item_total'],
        ), array(
            'trn_no'      => $invoice_no,
        ) );
    }

    $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
        'trn_no'     => $invoice_no,
        'particulars'=> '',
        'debit'      => $invoice_data['amount'],
        'credit'     => 0
    ), array(
        'invoice_no' => $invoice_no,
    ) );

    return $invoice_no;

}

/**
 * Get formatted invoice data
 *
 * @param $data
 * @param $voucher_no
 * @return mixed
 */
function erp_acct_get_formatted_invoice_data( $data, $voucher_no ) {

    $invoice_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $invoice_data['people_id'] = isset( $data['customer_id'] ) ? $data['customer_id'] : 1;

    $user_info = get_userdata($invoice_data['people_id'] );

    $invoice_data['customer_name'] = $user_info->first_name . ' ' . $user_info->last_name;
    $invoice_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $invoice_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $invoice_data['created_at'] = date("Y-m-d" );
    $invoice_data['billing_address'] = isset( $data['billing_address'] ) ? maybe_serialize( $data['billing_address'] ) : '';
    $invoice_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $invoice_data['discount'] = isset( $data['discount'] ) ? $data['discount'] : 0;
    $invoice_data['tax'] = isset( $data['tax'] ) ? $data['tax'] : 0;
    $invoice_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $invoice_data['type'] = isset( $data['type'] ) ? $data['type'] : '';

    return $invoice_data;
}

/**
 * Delete an invoice
 *
 * @param $invoice_no
 *
 * @return void
 */

function erp_acct_delete_invoice( $invoice_no ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_invoice', array( 'voucher_no' => $invoice_no ) );
}

/**
 * Void an invoice
 *
 * @param $invoice_no
 *
 * @return void
 */

function erp_acct_void_invoice( $invoice_no ) {
    global $wpdb;

    if ( !$invoice_no ) {
        return;
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_invoice',
        array(
            'status' => 'void',
        ),
        array( 'voucher_no' => $invoice_no )
    );
}




