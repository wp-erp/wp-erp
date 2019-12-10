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
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
        's'       => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    if ( '-1' !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql  = 'SELECT';
    $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
    $sql .= "FROM {$wpdb->prefix}erp_acct_invoice_receipts ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql );
    }

    $payment_data = $wpdb->get_results( $sql, ARRAY_A );

    return $payment_data;
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
    pay_inv.customer_id,
    pay_inv.customer_name,
    pay_inv.trn_date,
    pay_inv.amount,
    pay_inv.trn_by,
    pay_inv.trn_by_ledger_id,
    pay_inv.particulars,
    pay_inv.attachments,
    pay_inv.status,
    pay_inv.created_at,

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

    $row['line_items'] = erp_acct_format_payment_line_items( $invoice_no );
    $row['pdf_link']   = erp_acct_pdf_abs_path_to_url( $invoice_no );

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

    $created_by         = get_current_user_id();
    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;
    $voucher_no         = null;
    $currency           = erp_get_currency(true);

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            array(
				'type'       => 'payment',
				'currency'   => $currency,
				'created_at' => $data['created_at'],
				'created_by' => $data['created_by'],
				'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
				'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : '',
            )
        );

        $voucher_no = $wpdb->insert_id;

        $payment_data = erp_acct_get_formatted_payment_data( $data, $voucher_no );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_invoice_receipts',
            array(
				'voucher_no'       => $voucher_no,
				'customer_id'      => $payment_data['customer_id'],
				'customer_name'    => $payment_data['customer_name'],
				'trn_date'         => $payment_data['trn_date'],
				'particulars'      => $payment_data['particulars'],
				'amount'           => $payment_data['amount'],
				'ref'              => $payment_data['ref'],
				'trn_by'           => $payment_data['trn_by'],
				'attachments'      => $payment_data['attachments'],
				'status'           => $payment_data['status'],
				'trn_by_ledger_id' => $payment_data['deposit_to'],
				'created_at'       => $payment_data['created_at'],
				'created_by'       => $payment_data['created_by'],
				'updated_at'       => $payment_data['updated_at'],
				'updated_by'       => $payment_data['updated_by'],
            )
        );

        $items = $payment_data['line_items'];

        foreach ( $items as $key => $item ) {
            $total = 0;

            $invoice_no[ $key ] = $payment_data['invoice_no'];
            $total             += $item['line_total'];

            $payment_data['amount'] = $total;

            erp_acct_insert_payment_line_items( $payment_data, $item, $voucher_no );
        }

        if ( isset( $payment_data['trn_by'] ) && 3 === $payment_data['trn_by'] ) {
            erp_acct_insert_check_data( $payment_data );
        }

        $data['dr'] = 0;
        $data['cr'] = $payment_data['amount'];
        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        do_action( 'erp_acct_after_payment_create', $payment_data, $voucher_no );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'payment-exception', $e->getMessage() );
    }

    foreach ( $items as $key => $item ) {
        erp_acct_change_invoice_status( $item['invoice_no'] );
    }

    $payment = erp_acct_get_payment( $voucher_no );

    $payment['email'] = erp_get_people_email( $data['customer_id'] );

    do_action( 'erp_acct_new_transaction_payment', $voucher_no, $payment );

    return $payment;
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

    $payment_data               = erp_acct_get_formatted_payment_data( $data, $voucher_no, $item['invoice_no'] );
    $created_by                 = get_current_user_id();
    $payment_data['created_at'] = date( 'Y-m-d H:i:s' );
    $payment_data['created_by'] = $created_by;

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_invoice_receipts_details',
        array(
			'voucher_no' => $voucher_no,
			'invoice_no' => $item['invoice_no'],
			'amount'     => $item['line_total'],
			'created_at' => $payment_data['created_at'],
			'created_by' => $payment_data['created_by'],
			'updated_at' => $payment_data['updated_at'],
			'updated_by' => $payment_data['updated_by'],
        )
    );

    if ( 1 === $payment_data['status'] ) {
        return;
    }

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_invoice_account_details',
        array(
			'invoice_no'  => $item['invoice_no'],
			'trn_no'      => $voucher_no,
			'trn_date'    => $payment_data['trn_date'],
			'particulars' => $payment_data['particulars'],
			'debit'       => 0,
			'credit'      => $item['line_total'],
			'created_at'  => $payment_data['created_at'],
			'created_by'  => $payment_data['created_by'],
			'updated_at'  => $payment_data['updated_at'],
			'updated_by'  => $payment_data['updated_by'],
        )
    );

    erp_acct_insert_payment_data_into_ledger( $payment_data );

    return $voucher_no;

}

/**
 * Update payment data
 *
 * @param $data
 * @param $invoice_no
 * @return mixed
 */
function erp_acct_update_payment( $data, $voucher_no ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $payment_data = erp_acct_get_formatted_payment_data( $data, $voucher_no );

        $wpdb->update(
            $wpdb->prefix . 'erp_acct_invoice_receipts',
            array(
				'trn_date'         => $payment_data['trn_date'],
				'particulars'      => $payment_data['particulars'],
				'amount'           => $payment_data['amount'],
				'trn_by'           => $payment_data['trn_by'],
				'trn_by_ledger_id' => $payment_data['deposit_to'],
				'created_at'       => $payment_data['created_at'],
				'created_by'       => $payment_data['created_by'],
				'updated_at'       => $payment_data['updated_at'],
				'updated_by'       => $payment_data['updated_by'],
            ),
            array(
				'voucher_no' => $voucher_no,
            )
        );

        $items = $payment_data['line_items'];

        foreach ( $items as $key => $item ) {
            $total = 0;

            $invoice_no[ $key ] = $item['invoice_id'];
            $total             += $item['line_total'];

            $payment_data['amount'] = $total;

            erp_acct_update_payment_line_items( $payment_data, $voucher_no, $invoice_no[ $key ] );
        }

        if ( isset( $payment_data['trn_by'] ) && 3 === $payment_data['trn_by'] ) {
            erp_acct_insert_check_data( $payment_data );
        }

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'payment-exception', $e->getMessage() );
    }

    foreach ( $items as $key => $item ) {
        erp_acct_change_invoice_status( $item['invoice_no'] );
    }

    return erp_acct_get_payment( $voucher_no );

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

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_invoice_receipts_details',
        array(
			'voucher_no' => $voucher_no,
			'amount'     => $payment_data['amount'],
			'created_at' => $payment_data['created_at'],
			'created_by' => $payment_data['created_by'],
			'updated_at' => $payment_data['updated_at'],
			'updated_by' => $payment_data['updated_by'],
        ),
        array(
			'invoice_no' => $invoice_no,
        )
    );

    if ( 1 === $payment_data['status'] ) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_invoice_account_details',
        array(
			'trn_no'      => $voucher_no,
			'particulars' => $payment_data['particulars'],
			'trn_date'    => $payment_data['trn_date'],
			'debit'       => 0,
			'credit'      => $payment_data['amount'],
			'created_at'  => $payment_data['created_at'],
			'created_by'  => $payment_data['created_by'],
			'updated_at'  => $payment_data['updated_at'],
			'updated_by'  => $payment_data['updated_by'],
        ),
        array(
			'invoice_no' => $invoice_no,
        )
    );

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

    // We can pass the name from view... to reduce query load
    $user_info = erp_get_people( $data['customer_id'] );
    $company   = new \WeDevs\ERP\Company();

    $payment_data['voucher_no']    = ! empty( $voucher_no ) ? $voucher_no : 0;
    $payment_data['invoice_no']    = ! empty( $invoice_no ) ? $invoice_no : 0;
    $payment_data['customer_id']   = isset( $data['customer_id'] ) ? $data['customer_id'] : null;
    $payment_data['customer_name'] = isset( $user_info ) ? $user_info->first_name . ' ' . $user_info->last_name : '';
    $payment_data['trn_date']      = isset( $data['trn_date'] ) ? $data['trn_date'] : date( 'Y-m-d' );
    $payment_data['line_items']    = isset( $data['line_items'] ) ? $data['line_items'] : array();
    $payment_data['created_at']    = date( 'Y-m-d' );
    $payment_data['amount']        = isset( $data['amount'] ) ? $data['amount'] : 0;
    $payment_data['ref']           = isset( $data['ref'] ) ? $data['ref'] : null;
    $payment_data['attachments']   = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $payment_data['voucher_type']  = isset( $data['type'] ) ? $data['type'] : '';
    // translators: %s: voucher_no
    $payment_data['particulars']  = ! empty( $data['particulars'] ) ? $data['particulars'] : sprintf( __( 'Invoice receipt created with voucher no %s', 'erp' ), $voucher_no );
    $payment_data['trn_by']       = isset( $data['trn_by'] ) ? $data['trn_by'] : '';
    $payment_data['deposit_to']   = isset( $data['deposit_to'] ) ? $data['deposit_to'] : null;
    $payment_data['status']       = isset( $data['status'] ) ? $data['status'] : null;
    $payment_data['check_no']     = isset( $data['check_no'] ) ? $data['check_no'] : 0;
    $payment_data['pay_to']       = isset( $user_info ) ? $user_info->first_name . ' ' . $user_info->last_name : '';
    $payment_data['name']         = isset( $data['name'] ) ? $data['name'] : $company->name;
    $payment_data['bank']         = isset( $data['bank'] ) ? $data['bank'] : '';
    $payment_data['voucher_type'] = isset( $data['type'] ) ? $data['type'] : '';
    $payment_data['created_at']   = isset( $data['created_at'] ) ? $data['created_at'] : null;
    $payment_data['created_by']   = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $payment_data['updated_at']   = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
    $payment_data['updated_by']   = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

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

    if ( ! $id ) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_invoice_receipts',
        array(
            'status' => 8,
        ),
        array( 'voucher_no' => $id )
    );

    $wpdb->delete( $wpdb->prefix . 'erp_acct_ledger_details', array( 'trn_no' => $id ) );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_invoice_account_details', array( 'trn_no' => $id ) );

}

/**
 * Update invoice status after a payment
 *
 * @param $invoice_no
 *
 * @return void
 */
function erp_acct_change_invoice_status( $invoice_no ) {
    global $wpdb;

    $due = (float) erp_acct_get_invoice_due( $invoice_no );

    if ( 0.00 === $due ) {
        $wpdb->update(
            $wpdb->prefix . 'erp_acct_invoices',
            array(
                'status' => 4,
            ),
            array( 'voucher_no' => $invoice_no )
        );
    } else {
        $wpdb->update(
            $wpdb->prefix . 'erp_acct_invoices',
            array(
                'status' => 5,
            ),
            array( 'voucher_no' => $invoice_no )
        );
    }
}

/**
 * Insert Payment/s data into ledger
 *
 * @param array $payment_data
 *
 * @return mixed
 */
function erp_acct_insert_payment_data_into_ledger( $payment_data ) {
    global $wpdb;

    if ( 1 === $payment_data['status'] || ( isset( $payment_data['trn_by'] ) && 4 === $payment_data['trn_by'] ) ) {
        return;
    }

    // Insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $payment_data['deposit_to'],
			'trn_no'      => $payment_data['voucher_no'],
			'particulars' => $payment_data['particulars'],
			'debit'       => $payment_data['amount'],
			'credit'      => 0,
			'trn_date'    => $payment_data['trn_date'],
			'created_at'  => $payment_data['created_at'],
			'created_by'  => $payment_data['created_by'],
			'updated_at'  => $payment_data['updated_at'],
			'updated_by'  => $payment_data['updated_by'],
        )
    );
}

/**
 * Update Payment/s data into ledger
 *
 * @param array $payment_data
 * @param int $invoice_no
 *
 * @return mixed
 */
function erp_acct_update_payment_data_in_ledger( $payment_data, $invoice_no ) {
    global $wpdb;

    if ( 1 === $payment_data['status'] || ( isset( $payment_data['trn_by'] ) && 4 === $payment_data['trn_by'] ) ) {
        return;
    }

    // Update amount in ledger_details
    $wpdb->update(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $payment_data['deposit_to'],
			'particulars' => $payment_data['particulars'],
			'debit'       => $payment_data['amount'],
			'credit'      => 0,
			'trn_date'    => $payment_data['trn_date'],
			'created_at'  => $payment_data['created_at'],
			'created_by'  => $payment_data['created_by'],
			'updated_at'  => $payment_data['updated_at'],
			'updated_by'  => $payment_data['updated_by'],
        ),
        array(
			'trn_no' => $invoice_no,
        )
    );
}

/**
 * Get Payment count
 *
 * @return int
 */
function erp_acct_get_payment_count() {
    global $wpdb;

    $row = $wpdb->get_row( 'SELECT COUNT(*) as count FROM ' . $wpdb->prefix . 'erp_acct_invoice_receipts' );

    return $row->count;
}

/**
 * Format payment line items
 *
 * @param string $invoice
 *
 * @return array
 */
function erp_acct_format_payment_line_items( $invoice = 'all' ) {
    global $wpdb;

    $sql = 'SELECT id, voucher_no, invoice_no, amount ';

    if ( 'all' === $invoice ) {
        $invoice_sql = '';
    } else {
        $invoice_sql = 'WHERE voucher_no = ' . $invoice;
    }
    $sql .= "FROM {$wpdb->prefix}erp_acct_invoice_receipts_details {$invoice_sql}";

    return $wpdb->get_results( $sql, ARRAY_A );
}

