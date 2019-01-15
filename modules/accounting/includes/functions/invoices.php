<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all invoices
 *
 * @return mixed
 */

function erp_acct_get_all_invoices( $args = [] ) {
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

    $where = '';
    $limit = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = "SELECT";

    if ( $args['count'] ) {
        $sql .= " COUNT( DISTINCT invoice.id ) as total_number";
    } else {
        $sql .= " invoice.*, SUM(ledger_detail.credit) - SUM(ledger_detail.debit) as due";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_invoices AS invoice LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail"; 
    $sql .= " ON invoice.voucher_no = ledger_detail.trn_no {$where} GROUP BY invoice.voucher_no ORDER BY invoice.{$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var($sql);
    }

    return $wpdb->get_results( $sql, ARRAY_A );
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

    $sql = "Select

    invoice.id,
    invoice.voucher_no,
    invoice.customer_id,
    invoice.customer_name,
    invoice.trn_date,
    invoice.due_date,
    invoice.billing_address,
    invoice.amount,
    invoice.discount,
    invoice.tax,
    invoice.estimate,
    invoice.attachments,
    invoice.status, 
    invoice.particulars,
    invoice.created_at,
    invoice.created_by,
    invoice.updated_at, 
    invoice.updated_by,
    
    inv_detail.product_id,
    inv_detail.qty,
    inv_detail.unit_price,
    inv_detail.discount,
    inv_detail.tax,
    inv_detail.item_total, 
    inv_detail.tax_percent,
    
    inv_acc_detail.debit,
    inv_acc_detail.credit,
    
    product.name,
    product.product_type_id,
    product.category_id,
    product.vendor,
    product.cost_price,
    product.sale_price
    
    FROM {$wpdb->prefix}erp_acct_invoices as invoice
    LEFT JOIN {$wpdb->prefix}erp_acct_invoice_details as inv_detail ON invoice.voucher_no = inv_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details as inv_acc_detail ON invoice.voucher_no = inv_acc_detail.invoice_no
    LEFT JOIN {$wpdb->prefix}erp_acct_products as product ON inv_detail.product_id = product.id
    WHERE invoice.voucher_no = {$invoice_no}";

    $row = $wpdb->get_results( $sql, ARRAY_A );

    $row[0]['attachments'] = unserialize( $row[0]['attachments'] );

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
            'customer_id'     => $invoice_data['customer_id'],
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
                'tax_percent' => 0, // remove me please
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
            'customer_id'     => $invoice_data['customer_id'],
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
    $invoice_data['customer_id'] = isset( $data['customer_id'] ) ? $data['customer_id'] : 1;

    // $user_info = get_userdata( $invoice_data['people_id'] );
    $user_info = erp_get_people( $invoice_data['customer_id'] );

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
        'particulars' => $invoice_data['particulars'],
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

/**
 * Receive payments with due from a customer
 *
 * @return mixed
 */

function erp_acct_receive_payments_from_customer( $args = [] ) {
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

    $invoices = "{$wpdb->prefix}erp_acct_invoices";
    $invoice_act_details = "{$wpdb->prefix}erp_acct_invoice_account_details";
    $items = $args['count'] ? " COUNT( id ) as total_number " : " * ";

    $query = $wpdb->prepare( "SELECT $items FROM $invoices as invoice LEFT JOIN
                                (
                                    SELECT invoice_no, SUM( ia.debit - ia.credit) as due
                                    FROM $invoice_act_details as ia
                                    GROUP BY ia.invoice_no
                                    HAVING due > 0
                                ) as invs
                                ON invoice.voucher_no = invs.invoice_no
                                WHERE invoice.customer_id = %d
                                ORDER BY %s %s $limit", $args['people_id'],$args['orderby'],$args['order']  );

    if ( $args['count'] ) {
        return $wpdb->get_var( $query );
    }

    return $wpdb->get_results( $query, ARRAY_A );
}

/**
 * Get due of a bill
 *
 * @param $bill_no
 * @return int
 */
function erp_acct_get_due_payment( $invoice_no ) {
    global $wpdb;

    $result = $wpdb->get_row( "SELECT invoice_no, SUM( ia.debit - ia.credit) as due FROM {$wpdb->prefix}erp_acct_invoice_account_details as ia WHERE ia.invoice_no = {$invoice_no} GROUP BY ia.invoice_no", ARRAY_A );


    return $result['due'];
}

