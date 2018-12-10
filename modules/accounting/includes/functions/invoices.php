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

    $row = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_invoices", ARRAY_A );

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

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_invoices WHERE voucher_no = {$invoice_no}", ARRAY_A );

    return $row;
}

/**
 * Insert invoice data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_invoice( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'        => 'sales_invoice',
            'currency'    => '',
            'created_at'  => $data['created_at'],
            'created_by'  => $data['created_by'],
            'updated_at'  => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
            'updated_by'  => isset( $data['updated_by'] ) ? $data['updated_by'] : ''
        ) );

        $voucher_no = $wpdb->insert_id;

        $invoice_data = erp_acct_get_formatted_invoice_data( $data, $voucher_no );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_invoices', array(
            'voucher_no'      => $invoice_data['voucher_no'],
            'people_id'       => $invoice_data['people_id'],
            'customer_name'   => $invoice_data['customer_name'],
            'trn_date'        => $invoice_data['trn_date'],
            'due_date'        => $invoice_data['due_date'],
            'billing_address' => $invoice_data['billing_address'],
            'amount'          => $invoice_data['amount'],
            'discount'        => $invoice_data['discount'],
            'tax'             => $invoice_data['tax'],
            'estimate'        => $invoice_data['estimate'],
            'attachments'     => $invoice_data['attachments'],
            'status'          => $invoice_data['status'],
            'particulars'     => $invoice_data['particulars'],
            'created_at'      => $invoice_data['created_at'],
            'created_by'      => $invoice_data['created_by'],
            'updated_at'      => $invoice_data['updated_at'],
            'updated_by'      => $invoice_data['updated_by'],
        ) );

        $items = $invoice_data['line_items'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_details', array(
                'trn_no'      => $voucher_no,
                'product_id'  => $item['product_id'],
                'qty'         => $item['qty'],
                'unit_price'  => $item['unit_price'],
                'discount'    => $item['discount'],
                'tax'         => $item['tax'],
                'tax_percent' => $item['tax_percent'],
                'item_total'  => $item['item_total'],
                'created_at'  => $invoice_data['created_at'],
                'created_by'  => $invoice_data['created_by'],
                'updated_at'  => $invoice_data['updated_at'],
                'updated_by'  => $invoice_data['updated_by'],
            ) );
        }

        $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
            'invoice_no'  => $voucher_no,
            'trn_no'      => $voucher_no,
            'particulars' => '',
            'debit'       => $invoice_data['amount'],
            'credit'      => 0,
            'created_at'  => $invoice_data['created_at'],
            'created_by'  => $invoice_data['created_by'],
            'updated_at'  => $invoice_data['updated_at'],
            'updated_by'  => $invoice_data['updated_by'],
        ) );

        erp_acct_insert_invoice_data_into_ledger( $invoice_data );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'invoice-exception', $e->getMessage() );
    }

    return $voucher_no;

}

/**
 * Update invoice data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_invoice( $data, $invoice_no ) {
    global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $invoice_data = erp_acct_get_formatted_invoice_data( $data, $invoice_no );

        $wpdb->update( $wpdb->prefix . 'erp_acct_invoices', array(
            'people_id'       => $invoice_data['people_id'],
            'customer_name'   => $invoice_data['customer_name'],
            'trn_date'        => $invoice_data['trn_date'],
            'due_date'        => $invoice_data['due_date'],
            'billing_address' => $invoice_data['billing_address'],
            'amount'          => $invoice_data['amount'],
            'discount'        => $invoice_data['discount'],
            'tax'             => $invoice_data['tax'],
            'estimate'        => $invoice_data['estimate'],
            'attachments'     => $invoice_data['attachments'],
            'status'          => $invoice_data['status'],
            'particulars'     => $invoice_data['particulars'],
            'created_at'      => $invoice_data['created_at'],
            'created_by'      => $invoice_data['created_by'],
            'updated_at'      => $invoice_data['updated_at'],
            'updated_by'      => $invoice_data['updated_by'],
        ), array(
            'voucher_no' => $invoice_no,
        ) );

        $items = $invoice_data['line_items'];

        foreach ( $items as $key => $item ) {
            $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_details', array(
                'product_id'  => $item['product_id'],
                'qty'         => $item['qty'],
                'unit_price'  => $item['unit_price'],
                'discount'    => $item['discount'],
                'tax'         => $item['tax'],
                'tax_percent' => $item['tax_percent'],
                'item_total'  => $item['item_total'],
                'created_at'  => $invoice_data['created_at'],
                'created_by'  => $invoice_data['created_by'],
                'updated_at'  => $invoice_data['updated_at'],
                'updated_by'  => $invoice_data['updated_by'],
            ), array(
                'trn_no' => $invoice_no,
            ) );
        }

        $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
            'trn_no'      => $invoice_no,
            'particulars' => $invoice_data['particulars'],
            'debit'       => $invoice_data['amount'],
            'credit'      => 0,
            'created_at'  => $invoice_data['created_at'],
            'created_by'  => $invoice_data['created_by'],
            'updated_at'  => $invoice_data['updated_at'],
            'updated_by'  => $invoice_data['updated_by'],
        ), array(
            'invoice_no' => $invoice_no,
        ) );

        erp_acct_update_invoice_data_in_ledger( $invoice_data, $invoice_no );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'invoice-exception', $e->getMessage() );
    }

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
    $invoice_data = [];

    $invoice_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $invoice_data['people_id'] = isset( $data['customer_id'] ) ? $data['customer_id'] : 1;

    $user_info = get_userdata( $invoice_data['people_id'] );

    $invoice_data['customer_name'] = $user_info->first_name . ' ' . $user_info->last_name;
    $invoice_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $invoice_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $invoice_data['billing_address'] = isset( $data['billing_address'] ) ? maybe_serialize( $data['billing_address'] ) : '';
    $invoice_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $invoice_data['discount'] = isset( $data['discount'] ) ? $data['discount'] : 0;
    $invoice_data['line_items'] = isset( $data['line_items'] ) ? $data['line_items'] : array();
    $invoice_data['trn_by'] = isset( $data['trn_by'] ) ? $data['trn_by'] : '';
    $invoice_data['tax'] = isset( $data['tax'] ) ? $data['tax'] : 0;
    $invoice_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $invoice_data['status'] = isset( $data['status'] ) ? $data['status'] : 1;
    $invoice_data['particulars'] = isset( $data['particulars'] ) ? $data['particulars'] : '';
    $invoice_data['estimate'] = isset( $data['estimate'] ) ? $data['estimate'] : 1;
    $invoice_data['created_at'] = isset( $data['created_at'] ) ? $data['created_at'] : '';
    $invoice_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $invoice_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $invoice_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

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

    if ( !$invoice_no ) {
        return;
    }

    $wpdb->delete( $wpdb->prefix . 'erp_acct_invoices', array( 'voucher_no' => $invoice_no ) );
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

    $wpdb->update($wpdb->prefix . 'erp_acct_invoices',
        array(
            'status' => 'void',
        ),
        array( 'voucher_no' => $invoice_no )
    );
}


/**
 * Insert invoice/s data into ledger
 *
 * @param array $invoice_data
 *
 * @return mixed
 */
function erp_acct_insert_invoice_data_into_ledger( $invoice_data ) {
    global $wpdb;

    // Insert amount in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 305, // @TODO change later
        'trn_no'      => $invoice_data['voucher_no'],
        'particulars' => $invoice_data['particulars'],
        'debit'       => 0,
        'credit'      => $invoice_data['amount'],
        'trn_date'    => $invoice_data['trn_date'],
        'created_at'  => $invoice_data['created_at'],
        'created_by'  => $invoice_data['created_by'],
    ) );

    // Insert tax in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 306, // @TODO change later
        'trn_no'      => $invoice_data['voucher_no'],
        'particulars' => $invoice_data['particulars'],
        'debit'       => 0,
        'credit'      => $invoice_data['tax'],
        'trn_date'    => $invoice_data['trn_date'],
        'created_at'  => $invoice_data['created_at'],
        'created_by'  => $invoice_data['created_by'],
    ) );

    // Insert discount in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 307, // @TODO change later
        'trn_no'      => $invoice_data['voucher_no'],
        'particulars' => $invoice_data['particulars'],
        'debit'       => $invoice_data['tax'],
        'credit'      => 0,
        'trn_date'    => $invoice_data['trn_date'],
        'created_at'  => $invoice_data['created_at'],
        'created_by'  => $invoice_data['created_by'],
    ) );

}

/**
 * Update invoice/s data into ledger
 *
 * @param array $invoice_data
 *
 * @return mixed
 */
function erp_acct_update_invoice_data_in_ledger( $invoice_data, $invoice_no ) {
    global $wpdb;

    // Update amount in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 305, // @TODO change later
        'particulars'     => $invoice_data['particulars'],
        'debit'       => 0,
        'credit'      => $invoice_data['amount'],
        'trn_date'    => $invoice_data['trn_date'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by'],
    ), array(
        'trn_no' => $invoice_no,
    ) );

    // Update tax in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 306, // @TODO change later
        'trn_no'      => $invoice_data['voucher_no'],
        'particulars'     => $invoice_data['particulars'],
        'debit'       => 0,
        'credit'      => $invoice_data['tax'],
        'trn_date'    => $invoice_data['trn_date'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by'],
    ), array(
        'trn_no' => $invoice_no,
    ) );

    // Update discount in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 307, // @TODO change later
        'trn_no'      => $invoice_data['voucher_no'],
        'particulars'     => $invoice_data['particulars'],
        'debit'       => $invoice_data['tax'],
        'credit'      => 0,
        'trn_date'    => $invoice_data['trn_date'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by'],
    ), array(
        'trn_no' => $invoice_no,
    ) );

}

/**
 * Get Invoice count
 *
 * @return int
 */
function erp_acct_get_invoice_count() {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT COUNT(*) as count FROM " . $wpdb->prefix . "erp_acct_invoices" );

    return $row->count;
}


