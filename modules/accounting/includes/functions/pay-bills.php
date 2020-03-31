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
function erp_acct_get_pay_bills( $args = [] ) {
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

    if ( '-1' === $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql  = 'SELECT';
    $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
    $sql .= "FROM {$wpdb->prefix}erp_acct_pay_bill ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql );
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get a pay_bill
 *
 * @param $bill_no
 * @return mixed
 */
function erp_acct_get_pay_bill( $bill_no ) {
    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT
            pay_bill.id,
            pay_bill.voucher_no,
            pay_bill.vendor_id,
            pay_bill.vendor_name,
            pay_bill.trn_date,
            pay_bill.amount,
            pay_bill.trn_by,
            pay_bill.particulars,
            pay_bill.created_at,
            pay_bill.attachments,
            pay_bill.status
            FROM {$wpdb->prefix}erp_acct_pay_bill AS pay_bill
            WHERE pay_bill.voucher_no = %d",
            $bill_no
        ),
        ARRAY_A
    );

    $row['bill_details'] = erp_acct_format_paybill_line_items( $bill_no );

    return $row;
}

/**
 * Format pay bill line items
 */
function erp_acct_format_paybill_line_items( $voucher_no ) {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT pay_bill_detail.id,
            pay_bill_detail.voucher_no,
            pay_bill_detail.bill_no,
            pay_bill_detail.amount
            FROM {$wpdb->prefix}erp_acct_pay_bill AS pay_bill
            LEFT JOIN {$wpdb->prefix}erp_acct_pay_bill_details as pay_bill_detail ON pay_bill.voucher_no = pay_bill_detail.voucher_no
            WHERE pay_bill.voucher_no = %d",
            $voucher_no
        ),
        ARRAY_A
    );
}

/**
 * Insert a pay_bill
 *
 * @param $data
 * @param $pay_bill_id
 * @param $due
 * @return mixed
 */
function erp_acct_insert_pay_bill( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $created_by;

    $voucher_no = null;
    $currency   = erp_get_currency(true);

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            array(
				'type'       => 'pay_bill',
				'currency'   => $currency,
				'created_at' => $data['created_at'],
				'created_by' => $created_by,
				'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
				'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : '',
            )
        );

        $voucher_no = $wpdb->insert_id;

        $pay_bill_data = erp_acct_get_formatted_pay_bill_data( $data, $voucher_no );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_pay_bill',
            array(
                'voucher_no'       => $voucher_no,
                'trn_date'         => $pay_bill_data['trn_date'],
                'vendor_id'        => $pay_bill_data['vendor_id'],
                'vendor_name'      => $pay_bill_data['people_name'],
                'amount'           => $pay_bill_data['amount'],
                'trn_by'           => $pay_bill_data['trn_by'],
                'trn_by_ledger_id' => $pay_bill_data['trn_by_ledger_id'],
                'particulars'      => $pay_bill_data['particulars'],
                'attachments'      => $pay_bill_data['attachments'],
                'status'           => $pay_bill_data['status'],
                'created_at'       => $pay_bill_data['created_at'],
                'created_by'       => $created_by,
                'updated_at'       => $pay_bill_data['updated_at'],
                'updated_by'       => $pay_bill_data['updated_by'],
            )
        );

        $items = $pay_bill_data['bill_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_pay_bill_details',
                array(
					'voucher_no' => $voucher_no,
					'bill_no'    => $item['voucher_no'],
					'amount'     => $item['amount'],
					'created_at' => $pay_bill_data['created_at'],
					'created_by' => $pay_bill_data['created_by'],
					'updated_at' => $pay_bill_data['updated_at'],
					'updated_by' => $pay_bill_data['updated_by'],
                )
            );

            if ( 1 === $pay_bill_data['status'] ) {
                $wpdb->query( 'COMMIT' );

                return erp_acct_get_pay_bill( $voucher_no );
            }
        }

        foreach ( $items as $key => $item ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_bill_account_details',
                array(
					'bill_no'     => $item['voucher_no'],
					'trn_no'      => $voucher_no,
					'trn_date'    => $pay_bill_data['trn_date'],
					'particulars' => $pay_bill_data['particulars'],
					'debit'       => $item['amount'],
					'credit'      => 0,
					'created_at'  => $pay_bill_data['created_at'],
					'created_by'  => $pay_bill_data['created_by'],
					'updated_at'  => $pay_bill_data['updated_at'],
					'updated_by'  => $pay_bill_data['updated_by'],
                )
            );
        }

        erp_acct_insert_pay_bill_data_into_ledger( $pay_bill_data );

        if ( isset( $pay_bill_data['trn_by'] ) && 3 === $pay_bill_data['trn_by'] ) {
            erp_acct_insert_check_data( $pay_bill_data );
        }

        $data['dr'] = $pay_bill_data['amount'];
        $data['cr'] = 0;
        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        do_action( 'erp_acct_after_pay_bill_create', $pay_bill_data, $voucher_no );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'pay-bill-exception', $e->getMessage() );
    }

    foreach ( $items as $item ) {
        erp_acct_change_bill_status( $item['voucher_no'] );
    }

    $pay_bill = erp_acct_get_pay_bill( $voucher_no );

    $pay_bill['email'] = erp_get_people_email( $data['vendor_id'] );

    do_action( 'erp_acct_new_transaction_pay_bill', $voucher_no, $pay_bill );

    return $pay_bill;

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

    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $pay_bill_data = erp_acct_get_formatted_pay_bill_data( $data, $pay_bill_id );

        $wpdb->update(
            $wpdb->prefix . 'erp_acct_pay_bill',
            array(
				'bill_no'     => $pay_bill_data['bill_no'],
				'trn_date'    => $pay_bill_data['trn_date'],
				'amount'      => $pay_bill_data['amount'],
				'type'        => $pay_bill_data['type'],
				'particulars' => $pay_bill_data['particulars'],
				'attachments' => $pay_bill_data['attachments'],
				'status'      => $pay_bill_data['status'],
				'created_at'  => $pay_bill_data['created_at'],
				'created_by'  => $pay_bill_data['created_by'],
				'updated_at'  => $pay_bill_data['updated_at'],
				'updated_by'  => $pay_bill_data['updated_by'],
            ),
            array(
				'voucher_no' => $pay_bill_id,
            )
        );

        $items = $pay_bill_data['bill_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->update(
                $wpdb->prefix . 'erp_acct_pay_bill_details',
                array(
					'bill_no'    => $item['voucher_no'],
					'amount'     => $item['amount'],
					'created_at' => $pay_bill_data['created_at'],
					'created_by' => $pay_bill_data['created_by'],
					'updated_at' => $pay_bill_data['updated_at'],
					'updated_by' => $pay_bill_data['updated_by'],
                ),
                array(
					'voucher_no' => $pay_bill_id,
                )
            );

            $wpdb->update(
                $wpdb->prefix . 'erp_acct_bill_account_details',
                array(
					'bill_no'     => $item['voucher_no'],
					'particulars' => $pay_bill_data['particulars'],
					'debit'       => 0,
					'credit'      => $item['amount'],
					'created_at'  => $pay_bill_data['created_at'],
					'created_by'  => $pay_bill_data['created_by'],
					'updated_at'  => $pay_bill_data['updated_at'],
					'updated_by'  => $pay_bill_data['updated_by'],
                ),
                array(
					'trn_no' => $pay_bill_id,
                )
            );

        }

        erp_acct_update_pay_bill_data_into_ledger( $pay_bill_data, $pay_bill_id );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'bill-exception', $e->getMessage() );
    }

    foreach ( $items as $item ) {
        erp_acct_change_bill_status( $item['voucher_no'] );
    }

    return erp_acct_get_pay_bill( $pay_bill_id );

}

/**
 * Void a pay_bill
 *
 * @param $id
 * @return void
 */
function erp_acct_void_pay_bill( $id ) {
    global $wpdb;

    if ( ! $id ) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_pay_bill',
        array(
            'status' => 8,
        ),
        array( 'voucher_no' => $id )
    );

    $wpdb->delete( $wpdb->prefix . 'erp_acct_ledger_details', array( 'trn_no' => $id ) );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_bill_account_details', array( 'trn_no' => $id ) );
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
    $pay_bill_data = [];

    $user_info = erp_get_people( $data['vendor_id'] );
    $company   = new \WeDevs\ERP\Company();

    $pay_bill_data['voucher_no']  = ! empty( $voucher_no ) ? $voucher_no : 0;
    $pay_bill_data['trn_no']      = ! empty( $voucher_no ) ? $voucher_no : 0;
    $pay_bill_data['vendor_id']   = isset( $data['vendor_id'] ) ? $data['vendor_id'] : null;
    $pay_bill_data['people_name'] = isset( $user_info ) ? $user_info->first_name . ' ' . $user_info->last_name : '';
    $pay_bill_data['trn_date']    = isset( $data['trn_date'] ) ? $data['trn_date'] : date( 'Y-m-d' );
    $pay_bill_data['amount']      = isset( $data['amount'] ) ? $data['amount'] : 0;
    $pay_bill_data['ref']         = isset( $data['ref'] ) ? $data['ref'] : 0;
    $pay_bill_data['trn_by']      = isset( $data['trn_by'] ) ? $data['trn_by'] : 0;
    // translators: %s: voucher_no
    $pay_bill_data['particulars']      = ! empty( $data['particulars'] ) ? $data['particulars'] : sprintf( __( 'Bill payment created with voucher no %s', 'erp' ), $voucher_no );
    $pay_bill_data['attachments']      = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $pay_bill_data['bill_details']     = isset( $data['bill_details'] ) ? $data['bill_details'] : '';
    $pay_bill_data['status']           = isset( $data['status'] ) ? $data['status'] : 4;
    $pay_bill_data['trn_by_ledger_id'] = isset( $data['deposit_to'] ) ? $data['deposit_to'] : null;
    $pay_bill_data['check_no']         = isset( $data['check_no'] ) ? $data['check_no'] : 0;
    $pay_bill_data['pay_to']           = isset( $user_info ) ? $user_info->first_name . ' ' . $user_info->last_name : '';
    $pay_bill_data['name']             = isset( $data['name'] ) ? $data['name'] : $company->name;
    $pay_bill_data['bank']             = isset( $data['bank'] ) ? $data['bank'] : '';
    $pay_bill_data['voucher_type']     = isset( $data['voucher_type'] ) ? $data['voucher_type'] : '';
    $pay_bill_data['created_at']       = date( 'Y-m-d' );
    $pay_bill_data['created_by']       = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $pay_bill_data['updated_at']       = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $pay_bill_data['updated_by']       = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $pay_bill_data;
}

/**
 * Insert pay_bill/s data into ledger
 *
 * @param array $pay_bill_data
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_insert_pay_bill_data_into_ledger( $pay_bill_data ) {
    global $wpdb;

    if ( 1 === $pay_bill_data['status'] || ( isset( $pay_bill_data['trn_by'] ) && 4 === $pay_bill_data['trn_by'] ) ) {
        return;
    }

    // Insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $pay_bill_data['trn_by_ledger_id'],
			'trn_no'      => $pay_bill_data['trn_no'],
			'particulars' => $pay_bill_data['particulars'],
			'debit'       => 0,
			'credit'      => $pay_bill_data['amount'],
			'trn_date'    => $pay_bill_data['trn_date'],
			'created_at'  => $pay_bill_data['created_at'],
			'created_by'  => $pay_bill_data['created_by'],
			'updated_at'  => $pay_bill_data['updated_at'],
			'updated_by'  => $pay_bill_data['updated_by'],
        )
    );

}

/**
 * Update pay_bill/s data into ledger
 *
 * @param array $pay_bill_data
 * * @param array $pay_bill_no
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_update_pay_bill_data_into_ledger( $pay_bill_data, $pay_bill_no ) {
    global $wpdb;

    if ( 1 === $pay_bill_data['status'] || ( isset( $pay_bill_data['trn_by'] ) && 4 === $pay_bill_data['trn_by'] ) ) {
        return;
    }

    // Update amount in ledger_details
    $wpdb->update(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $pay_bill_data['trn_by_ledger_id'],
			'particulars' => $pay_bill_data['particulars'],
			'debit'       => 0,
			'credit'      => $pay_bill_data['amount'],
			'trn_date'    => $pay_bill_data['trn_date'],
			'created_at'  => $pay_bill_data['created_at'],
			'created_by'  => $pay_bill_data['created_by'],
			'updated_at'  => $pay_bill_data['updated_at'],
			'updated_by'  => $pay_bill_data['updated_by'],
        ),
        array(
			'trn_no' => $pay_bill_no,
        )
    );

}

/**
 * Get Pay bills count
 *
 * @return int
 */
function erp_acct_get_pay_bill_count() {
    global $wpdb;

    $row = $wpdb->get_row( 'SELECT COUNT(*) as count FROM ' . $wpdb->prefix . 'erp_acct_pay_bill' );

    return $row->count;
}

/**
 * Update bill status after a payment
 *
 * @param $bill_no
 *
 * @return void
 */
function erp_acct_change_bill_status( $bill_no ) {
    global $wpdb;

    $due = erp_acct_get_bill_due( $bill_no );

    if ( 0 == $due ) {

        $wpdb->update(
            $wpdb->prefix . 'erp_acct_bills',
            array(
                'status' => 4,
            ),
            array( 'voucher_no' => $bill_no )
        );
    } else {
        $wpdb->update(
            $wpdb->prefix . 'erp_acct_bills',
            array(
                'status' => 5,
            ),
            array( 'voucher_no' => $bill_no )
        );
    }
}



