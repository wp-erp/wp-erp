<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all taxes
 *
 * @param $data
 *
 * @return mixed
 */

function erp_acct_get_all_taxes( $data ) {
    global $wpdb;

    $row = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_tax", ARRAY_A );

    return $row;
}

/**
 * Get an single tax
 *
 * @param $tax_no
 *
 * @return mixed
 */

function erp_acct_get_tax( $tax_no ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_tax WHERE voucher_no = {$tax_no}", ARRAY_A );

    return $row;
}

/**
 * Insert tax data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_tax( $data ) {
    global $wpdb; $tax_data = [];

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'            => $data['type'],
    ) );

    $voucher_no = $wpdb->insert_id ;

    $tax_data = erp_acct_get_formatted_tax_data( $data, $voucher_no );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_tax', array(
        'voucher_no'      => $tax_data['voucher_no'],
        'people_id'       => $tax_data['people_id'],
        'trn_date'        => $tax_data['trn_date'],
        'due_date'        => $tax_data['due_date'],
        'created_at'      => $tax_data['created_at'],
        'billing_address' => $tax_data['billing_address'],
        'discount'        => $tax_data['discount'],
        'tax'             => $tax_data['tax'],
        'amount'          => $tax_data['total'],
        'type'            => $tax_data['type'],
        'attachments'     => $tax_data['attachments']
    ) );

    if ( $tax_data['type'] != 'tax' ) {
        return $voucher_no;
    }

    $items = $data['line_items'];

    foreach( $items as $key => $item ) {
        $wpdb->insert( $wpdb->prefix . 'erp_acct_tax_details', array(
            'trn_no'     => $voucher_no,
            'product_id' => $item['product_id'],
            'qty'        => $item['qty'],
            'unit_price' => $item['unit_price'],
            'discount'   => $item['discount'],
            'tax'        => $item['tax'],
            'item_total' => $item['item_total'],
        ) );
    }

    $wpdb->insert( $wpdb->prefix . 'erp_acct_tax_account_details', array(
        'tax_no' => $voucher_no,
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => $tax_data['amount'],
        'credit'     => 0
    ) );

    return $voucher_no;

}

/**
 * Update tax data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_tax( $data, $id ) {
    global $wpdb; $tax_data = [];

    $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
        'type'            => $data['type'],
    ) );

    $voucher_no = $wpdb->insert_id ;

    $tax_data = erp_acct_get_formatted_tax_data( $data, $voucher_no );

    $wpdb->update( $wpdb->prefix . 'erp_acct_tax', array(
        'voucher_no'      => $tax_data['voucher_no'],
        'people_id'       => $tax_data['people_id'],
        'trn_date'        => $tax_data['trn_date'],
        'due_date'        => $tax_data['due_date'],
        'created_at'      => $tax_data['created_at'],
        'billing_address' => $tax_data['billing_address'],
        'amount'          => $tax_data['amount'],
        'discount'        => $tax_data['discount'],
        'tax'             => $tax_data['tax'],
        'attachments'     => $tax_data['attachments'],
        'type'            => $tax_data['type']
    ), array(

    ) );

    if ( $tax_data['type'] != 'tax' ) {
        return $voucher_no;
    }

    $items = $data['line_items'];

    foreach( $items as $key => $item ) {
        $wpdb->update( $wpdb->prefix . 'erp_acct_tax_details', array(
            'trn_no'     => $voucher_no,
            'product_id' => $item['product_id'],
            'qty'        => $item['qty'],
            'unit_price' => $item['unit_price'],
            'discount'   => $item['discount'],
            'tax'        => $item['tax'],
            'item_total' => $item['item_total'],
        ), array(

        ) );
    }

    $wpdb->update( $wpdb->prefix . 'erp_acct_tax_account_details', array(
        'tax_no' => $voucher_no,
        'trn_no'     => $voucher_no,
        'particulars'=> '',
        'debit'      => $tax_data['amount'],
        'credit'     => 0
    ), array(

    ) );

    return $voucher_no;

}

/**
 * Get formatted tax data
 *
 * @param $data
 * @param $voucher_no
 * @return mixed
 */
function erp_acct_get_formatted_tax_data( $data, $voucher_no ) {

    $tax_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $tax_data['people_id'] = isset( $data['customer_id'] ) ? $data['customer_id'] : 1;

    $user_info = get_userdata($tax_data['people_id'] );

    $tax_data['customer_name'] = $user_info->first_name . ' ' . $user_info->last_name;
    $tax_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $tax_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $tax_data['created_at'] = date("Y-m-d" );
    $tax_data['billing_address'] = isset( $data['billing_address'] ) ? maybe_serialize( $data['billing_address'] ) : '';
    $tax_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $tax_data['discount'] = isset( $data['discount'] ) ? $data['discount'] : 0;
    $tax_data['tax'] = isset( $data['tax'] ) ? $data['tax'] : 0;
    $tax_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $tax_data['type'] = isset( $data['type'] ) ? $data['type'] : '';

    return $tax_data;
}

/**
 * Delete an tax
 *
 * @param $tax_no
 *
 * @return void
 */

function erp_acct_delete_tax( $tax_no ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_tax', array( 'tax' => $tax_no ) );
}

/**
 * Void an tax
 *
 * @param $tax_no
 *
 * @return void
 */

function erp_acct_void_tax( $tax_no ) {
    global $wpdb;

    if ( !$tax_no ) {
        return;
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_tax',
        array(
            'status' => 'void',
        ),
        array( 'voucher_no' => $tax_no )
    );
}




