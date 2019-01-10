<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all payments
 *
 * @return mixed
 */

function erp_acct_get_payments( $args = [] ) {
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

    $sql = "SELECT";
    $sql .= $args['count'] ? " COUNT( id ) as total_number " : " * ";
    $sql .= "FROM {$wpdb->prefix}erp_acct_invoice_receipts ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var($sql);
    }

    return $wpdb->get_results( $sql, ARRAY_A );
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

    $sql = "SELECT

    pay_inv.id,
    pay_inv.voucher_no,
    pay_inv.trn_date,
    pay_inv.amount,
    pay_inv.trn_by,
    pay_inv.particulars,
    pay_inv.attachments,
    pay_inv.status,
    pay_inv.created_at,
    pay_inv.created_by,
    pay_inv.updated_at,
    pay_inv.updated_by,

    pay_inv_detail.invoice_no,
    pay_inv_detail.amount as pay_inv_detail_amount,
    
    ledger_detail.particulars,
    ledger_detail.debit,
    ledger_detail.credit

    from {$wpdb->prefix}erp_acct_invoice_receipts as pay_inv

    LEFT JOIN {$wpdb->prefix}erp_acct_invoice_receipts_details as pay_inv_detail ON pay_inv.voucher_no = pay_inv_detail.voucher_no
    LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details as ledger_detail ON pay_inv.voucher_no = ledger_detail.trn_no

    WHERE pay_inv.voucher_no = {$invoice_no}";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
}

/**
 * Insert payment info
 *
 * @param $payment_data
 *
 * @return mixed
 */

function erp_acct_insert_payment( $data ) {
	global $wpdb;

    $created_by = get_current_user_id();
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;

	try {
		$wpdb->query( 'START TRANSACTION' );

		$wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
			'type'       => 'payment',
			'created_at' => $data['created_at'],
			'created_by' => $data['created_by'],
            'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
            'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : ''
		) );

		$voucher_no = $wpdb->insert_id;

        $payment_data = erp_acct_get_formatted_payment_data( $data, $voucher_no );

	    $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_receipts', array(
            'voucher_no' => $voucher_no,
            'trn_date'   => date( 'Y-m-d' ),
            'particulars'=> $payment_data['particulars'],
            'amount'     => $payment_data['amount'],
            'trn_by'     => $payment_data['trn_by'],
            'created_at' => $payment_data['created_at'],
            'created_by' => $payment_data['created_by'],
            'updated_at' => $payment_data['updated_at'],
            'updated_by' => $payment_data['updated_by'],
	    ) );

        $items = $payment_data['line_items'];

	    foreach ( $items as $key => $item ) {
	        $total = 0; $due = 0;

	        $invoice_no[$key] = $payment_data['invoice_no'];
	        $total += $item['line_total'];

	        $payment_data['amount'] = $total;

	        erp_acct_insert_payment_line_items( $payment_data, $item, $voucher_no );
	    }

        erp_acct_insert_people_trn_data( $payment_data, $payment_data['customer_id'], 'credit' );

		erp_acct_change_invoice_status( $voucher_no );

		$wpdb->query( 'COMMIT' );

	} catch (Exception $e) {
		$wpdb->query( 'ROLLBACK' );
		return new WP_error( 'payment-exception', $e->getMessage() );
	}

	return $voucher_no;
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
function erp_acct_insert_payment_line_items( $data, $item, $voucher_no ) {
    global $wpdb;

    $payment_data = erp_acct_get_formatted_payment_data( $data, $voucher_no, $item['invoice_no'] );
    $created_by = get_current_user_id();
    $payment_data['created_at'] = date('Y-m-d H:i:s');
    $payment_data['created_by'] = $created_by;

    $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
        'invoice_no' => $item['invoice_no'],
        'trn_no'     => $voucher_no,
        'particulars'=> $payment_data['particulars'],
        'debit'      => 0,
        'credit'     => $item['line_total'],
        'created_at' => $payment_data['created_at'],
        'created_by' => $payment_data['created_by'],
        'updated_at' => $payment_data['updated_at'],
        'updated_by' => $payment_data['updated_by'],
    ) );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_receipts_details', array(
        'voucher_no' => $voucher_no,
        'invoice_no' => $item['invoice_no'],
        'amount'     => $item['line_total'],
        'created_at' => $payment_data['created_at'],
        'created_by' => $payment_data['created_by'],
        'updated_at' => $payment_data['updated_at'],
        'updated_by' => $payment_data['updated_by'],
    ) );

	erp_acct_insert_payment_data_into_ledger( $payment_data );

    return $voucher_no;

}

/**
 * Update payment data
 *
 * @param $data
 * @param $invoice_no
 * @return int
 */
function erp_acct_update_payment( $data, $voucher_no ) {
	global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

	try {
		$wpdb->query( 'START TRANSACTION' );

		$payment_data = erp_acct_get_formatted_payment_data( $data, $voucher_no );

	    $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_receipts', array(
            'trn_date'   => date( "Y-m-d" ),
            'particulars'=> $payment_data['particulars'],
            'amount'     => $payment_data['amount'],
            'trn_by'     => $payment_data['trn_by'],
            'created_at' => $payment_data['created_at'],
            'created_by' => $payment_data['created_by'],
            'updated_at' => $payment_data['updated_at'],
            'updated_by' => $payment_data['updated_by'],
	    ), array(
	        'voucher_no' => $voucher_no,
	    ) );

	    $items = $payment_data['line_items'];

	    foreach ( $items as $key => $item ) {
	        $total = 0;

	        $invoice_no[$key] = $item['invoice_id'];
	        $total += $item['line_total'];

	        $payment_data['amount'] = $total;

	        erp_acct_update_payment_line_items( $payment_data, $voucher_no, $invoice_no[$key] );
	    }

	    erp_acct_change_invoice_status( $voucher_no );

		$wpdb->query( 'COMMIT' );

	} catch (Exception $e) {
		$wpdb->query( 'ROLLBACK' );
		return new WP_error( 'payment-exception', $e->getMessage() );
	}

    return $voucher_no;

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

    $payment_data = erp_acct_get_formatted_payment_data( $data, $voucher_no, $invoice_no );

    $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
        'trn_no'     => $voucher_no,
        'particulars'=> $payment_data['particulars'],
        'debit'      => 0,
        'credit'     => $payment_data['amount'],
        'created_at' => $payment_data['created_at'],
        'created_by' => $payment_data['created_by'],
        'updated_at' => $payment_data['updated_at'],
        'updated_by' => $payment_data['updated_by'],
    ), array(
        'invoice_no' => $invoice_no,
    ) );

    $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_receipts_details', array(
        'voucher_no' => $voucher_no,
        'amount'     => $payment_data['amount'],
        'created_at' => $payment_data['created_at'],
        'created_by' => $payment_data['created_by'],
        'updated_at' => $payment_data['updated_at'],
        'updated_by' => $payment_data['updated_by'],
    ), array(
        'invoice_no' => $invoice_no,
    ) );

    erp_acct_change_invoice_status( $invoice_no );
	erp_acct_insert_payment_data_into_ledger( $payment_data );

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
function erp_acct_get_formatted_payment_data( $data, $voucher_no, $invoice_no = 0 ) {
    $payment_data = [];

    $payment_data['voucher_no']  = ! empty( $voucher_no ) ? $voucher_no : 0;
    $payment_data['invoice_no']  = ! empty( $invoice_no ) ? $invoice_no : 0;
    $payment_data['customer_id'] = isset( $data['customer_id'] ) ? $data['customer_id'] : 1;

    $payment_data['trn_date']    = isset( $data['date'] ) ? $data['date'] : date( "Y-m-d" );
    $payment_data['line_items']  = isset( $data['line_items'] ) ? $data['line_items'] : array();
    $payment_data['created_at']  = date( "Y-m-d" );
    $payment_data['amount']      = isset( $data['amount'] ) ? $data['amount'] : 0;
    $payment_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $payment_data['type']        = isset( $data['type'] ) ? $data['type'] : '';
    $payment_data['particulars'] = isset( $data['particulars'] ) ? $data['particulars'] : '';
    $payment_data['trn_by']      = isset( $data['trn_by'] ) ? $data['trn_by'] : '';
    $payment_data['created_at']  = isset( $data['created_at'] ) ? $data['created_at'] : '';
    $payment_data['created_by']  = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $payment_data['updated_at']  = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $payment_data['updated_by']  = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

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
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_invoice_receipts',
        array(
            'status' => 'void',
        ),
        array( 'voucher_no' => $id )
    );
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

    $wpdb->update($wpdb->prefix . 'erp_acct_invoices',
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

/**
 * Insert Payment/s data into ledger
 *
 * @param array $invoice_data
 *
 * @return mixed
 */
function erp_acct_insert_payment_data_into_ledger( $payment_data ) {
	global $wpdb;

	// Insert amount in ledger_details
	$wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
		'ledger_id'   => 305, // @TODO change later
		'trn_no'      => $payment_data['voucher_no'],
		'particulars' => $payment_data['particulars'],
		'debit'       => 0,
		'credit'      => $payment_data['amount'],
		'trn_date'    => $payment_data['trn_date'],
		'created_at'  => $payment_data['created_at'],
		'created_by'  => $payment_data['created_by'],
		'updated_at'  => $payment_data['updated_at'],
		'updated_by'  => $payment_data['updated_by'],
	) );
}

/**
 * Update Payment/s data into ledger
 *
 * @param array $invoice_data
 *
 * @return mixed
 */
function erp_acct_update_payment_data_in_ledger( $payment_data, $invoice_no ) {
	global $wpdb;

	// Update amount in ledger_details
	$wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
		'ledger_id'   => 305, // @TODO change later
		'particulars' => $payment_data['particulars'],
		'debit'       => 0,
		'credit'      => $payment_data['amount'],
		'trn_date'    => $payment_data['trn_date'],
		'created_at'  => $payment_data['created_at'],
		'created_by'  => $payment_data['created_by'],
		'updated_at'  => $payment_data['updated_at'],
		'updated_by'  => $payment_data['updated_by'],
	), array(
		'trn_no' => $invoice_no,
	) );
}

/**
 * Get Payment count
 *
 * @return int
 */
function erp_acct_get_payment_count() {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT COUNT(*) as count FROM " . $wpdb->prefix . "erp_acct_invoice_receipts" );

    return $row->count;
}
