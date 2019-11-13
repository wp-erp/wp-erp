<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all bills
 *
 * @param array $args
 * @return mixed
 */
function erp_acct_get_bills( $args = [] ) {
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

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql  = 'SELECT';
    $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
    $sql .= "FROM {$wpdb->prefix}erp_acct_bills WHERE `trn_by_ledger_id` IS NULL ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql );
    }

    $rows = $wpdb->get_results( $sql, ARRAY_A );

    return $rows;
}

/**
 * Get a single bill
 *
 * @param $bill_no
 * @return mixed
 */
function erp_acct_get_bill( $bill_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT

    voucher.editable,
    bill.id,
    bill.voucher_no,
    bill.vendor_id,
    bill.vendor_name,
    bill.address AS billing_address,
    bill.trn_date,
    bill.due_date,
    bill.amount,
    bill.ref,
    bill.particulars,
    bill.status,
    bill.created_at,
    bill.attachments

    FROM {$wpdb->prefix}erp_acct_bills AS bill
    LEFT JOIN {$wpdb->prefix}erp_acct_voucher_no as voucher ON bill.voucher_no = voucher.id
    LEFT JOIN {$wpdb->prefix}erp_acct_bill_account_details AS b_ac_detail ON bill.voucher_no = b_ac_detail.trn_no
    WHERE bill.voucher_no = %d",
        $bill_no
    );

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['bill_details'] = erp_acct_format_bill_line_items( $bill_no );

    return $row;
}

/**
 * Format bill line items
 * @param $voucher_no
 * @return array|object|null
 */
function erp_acct_format_bill_line_items( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
        b_detail.id,
        b_detail.trn_no,
        b_detail.ledger_id,
        b_detail.particulars,
        b_detail.amount,

        ledger.name AS ledger_name

        FROM {$wpdb->prefix}erp_acct_bills AS bill
        LEFT JOIN {$wpdb->prefix}erp_acct_bill_details AS b_detail ON bill.voucher_no = b_detail.trn_no
        LEFT JOIN {$wpdb->prefix}erp_acct_ledgers AS ledger ON ledger.id = b_detail.ledger_id
        WHERE bill.voucher_no = %d",
        $voucher_no
    );

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Insert a bill
 *
 * @param $data
 * @return mixed
 */
function erp_acct_insert_bill( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();
    $voucher_no = null;
    $draft      = 1;

    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $created_by;
    $currency           = erp_get_currency(true);

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            array(
				'type'       => 'bill',
				'currency'   => $currency,
				'editable'   => 1,
				'created_at' => $data['created_at'],
				'created_by' => $data['created_by'],
				'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
				'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : '',
            )
        );

        $voucher_no = $wpdb->insert_id;

        $bill_data           = erp_acct_get_formatted_bill_data( $data, $voucher_no );
        $bill_data['trn_no'] = $voucher_no;

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_bills',
            array(
				'voucher_no'  => $bill_data['voucher_no'],
				'vendor_id'   => $bill_data['vendor_id'],
				'vendor_name' => $bill_data['vendor_name'],
				'address'     => $bill_data['billing_address'],
				'trn_date'    => $bill_data['trn_date'],
				'due_date'    => $bill_data['due_date'],
				'amount'      => $bill_data['amount'],
				'ref'         => $bill_data['ref'],
				'particulars' => $bill_data['particulars'],
				'status'      => $bill_data['status'],
				'attachments' => $bill_data['attachments'],
				'created_at'  => $bill_data['created_at'],
				'created_by'  => $bill_data['created_by'],
            )
        );

        $items = $bill_data['bill_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_bill_details',
                [
					'trn_no'      => $voucher_no,
					'ledger_id'   => $item['ledger_id'],
					'particulars' => isset( $item['description'] ) ? $item['description'] : '',
					'amount'      => $item['amount'],
					'created_at'  => $bill_data['created_at'],
					'created_by'  => $bill_data['created_by'],
				]
            );

            erp_acct_insert_bill_data_into_ledger( $bill_data, $item );
        }

        if ( $draft === $bill_data['status'] ) {
            $wpdb->query( 'COMMIT' );
            return erp_acct_get_bill( $voucher_no );
        }

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_bill_account_details',
            array(
				'bill_no'     => $voucher_no,
				'trn_no'      => $voucher_no,
				'trn_date'    => $bill_data['trn_date'],
				'particulars' => $bill_data['particulars'],
				'debit'       => 0,
				'credit'      => $bill_data['amount'],
				'created_at'  => $bill_data['created_at'],
				'created_by'  => $bill_data['created_by'],
            )
        );

        $data['dr'] = 0;
        $data['cr'] = $bill_data['amount'];
        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        do_action( 'erp_acct_after_bill_create', $data, $voucher_no );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'bill-exception', $e->getMessage() );
    }

    $bill = erp_acct_get_bill( $voucher_no );

    $bill['email'] = erp_get_people_email( $bill_data['vendor_id'] );

    do_action( 'erp_acct_new_transaction_bill', $voucher_no, $bill );

    return $bill;
}

/**
 * Update a bill
 *
 * @param $data
 * @param $bill_id
 *
 * @return mixed
 */
function erp_acct_update_bill( $data, $bill_id ) {
    global $wpdb;

    $user_id    = get_current_user_id();
    $draft      = 1;
    $voucher_no = null;

    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $user_id;
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $user_id;
    $currency           = erp_get_currency(true);

    try {
        $wpdb->query( 'START TRANSACTION' );

        if ( $draft === $data['status'] ) {
            erp_acct_update_draft_bill( $data, $bill_id );
        } else {
            // disable editing on old bill
            $wpdb->update( $wpdb->prefix . 'erp_acct_voucher_no', [ 'editable' => 0 ], [ 'id' => $bill_id ] );

            // insert contra voucher
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_voucher_no',
                array(
					'type'       => 'bill',
					'currency'   => $currency,
					'editable'   => 0,
					'created_at' => $data['created_at'],
					'created_by' => $data['created_by'],
					'updated_at' => $data['updated_at'],
					'updated_by' => $data['updated_by'],
                )
            );

            $voucher_no = $wpdb->insert_id;

            $old_bill = erp_acct_get_bill( $bill_id );

            // insert contra `erp_acct_bills` (basically a duplication of row)
            $wpdb->query( $wpdb->prepare( "CREATE TEMPORARY TABLE acct_tmptable SELECT * FROM {$wpdb->prefix}erp_acct_bills WHERE voucher_no = %d", $bill_id ) );
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE acct_tmptable SET id = %d, voucher_no = %d, particulars = 'Contra entry for voucher no \#%d', created_at = '%s'",
                    0,
                    $voucher_no,
                    $bill_id,
                    $data['created_at']
                )
            );
            $wpdb->query( "INSERT INTO {$wpdb->prefix}erp_acct_bills SELECT * FROM acct_tmptable" );
            $wpdb->query( 'DROP TABLE acct_tmptable' );

            // change bill status and other things
            $status_closed = 7;
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}erp_acct_bills SET status = %d, updated_at ='%s', updated_by = %d WHERE voucher_no IN (%d, %d)",
                    $status_closed,
                    $data['updated_at'],
                    $user_id,
                    $bill_id,
                    $voucher_no
                )
            );

            $items = $old_bill['bill_details'];

            foreach ( $items as $key => $item ) {
                // insert contra `erp_acct_bill_details`
                $wpdb->insert(
                    $wpdb->prefix . 'erp_acct_bill_details',
                    [
						'trn_no'      => $voucher_no,
						'ledger_id'   => $item['ledger_id'],
						'particulars' => isset( $item['description'] ) ? $item['description'] : '',
						'amount'      => $item['amount'],
						'created_at'  => $data['created_at'],
						'created_by'  => $data['created_by'],
					]
                );

                // insert contra `erp_acct_ledger_details`
                erp_acct_update_bill_data_into_ledger( $old_bill, $voucher_no, $item );
            }

            // insert contra `erp_acct_bill_account_details`
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_bill_account_details',
                array(
					'bill_no'     => $bill_id,
					'trn_no'      => $voucher_no,
					'trn_date'    => $old_bill['trn_date'],
					'particulars' => $old_bill['particulars'],
					'debit'       => $old_bill['amount'],
					'updated_at'  => $data['updated_at'],
					'updated_by'  => $data['updated_by'],
                )
            );

            // insert new bill with edited data
            $new_bill = erp_acct_insert_bill( $data );

            do_action( 'erp_acct_after_bill_update', $data, $bill_id );

            $data['dr'] = 0;
            $data['cr'] = $data['amount'];
            erp_acct_update_data_into_people_trn_details( $data, $old_bill['voucher_no'] );
        }

        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'bill-exception', $e->getMessage() );
    }

    return erp_acct_get_bill( $new_bill['voucher_no'] );
}

/**
 * Make bill draft on update
 *
 * @param $data
 * @param $bill_id
 * @return void
 */
function erp_acct_update_draft_bill( $data, $bill_id ) {
    global $wpdb;

    $bill_data = erp_acct_get_formatted_bill_data( $data, $bill_id );

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_bills',
        array(
			'vendor_id'   => $bill_data['vendor_id'],
			'vendor_name' => $bill_data['vendor_name'],
			'address'     => $bill_data['billing_address'],
			'trn_date'    => $bill_data['trn_date'],
			'due_date'    => $bill_data['due_date'],
			'amount'      => $bill_data['amount'],
			'ref'         => $bill_data['ref'],
			'particulars' => $bill_data['particulars'],
			'attachments' => $bill_data['attachments'],
			'updated_at'  => $bill_data['updated_at'],
			'updated_by'  => $bill_data['updated_by'],
        ),
        array(
			'voucher_no' => $bill_id,
        )
    );

    /**
     *? We can't update `bill_details` directly
     *? suppose there were 5 detail rows previously
     *? but on update there may be 2 detail rows
     *? that's why we can't update because the foreach will iterate only 2 times, not 5 times
     *? so, remove previous rows and insert new rows
     */
    $prev_detail_ids = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_acct_bill_details WHERE trn_no = %d", $bill_id ), ARRAY_A );

    $prev_detail_ids = implode( ',', array_map( 'absint', $prev_detail_ids ) );

    $wpdb->delete( $wpdb->prefix . 'erp_acct_bill_details', [ 'trn_no' => $bill_id ] );

    $items = $bill_data['bill_details'];

    foreach ( $items as $item ) {
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_bill_details',
            [
				'trn_no'      => $bill_id,
				'ledger_id'   => $item['ledger_id'],
				'particulars' => isset( $item['description'] ) ? $item['description'] : '',
				'amount'      => $item['amount'],
				'created_at'  => $bill_data['created_at'],
				'created_by'  => $bill_data['created_by'],
			]
        );
    }
}

/**
 * Void a bill
 *
 * @param $id
 * @return void
 */
function erp_acct_void_bill( $id ) {
    global $wpdb;

    if ( ! $id ) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_bills',
        array(
            'status' => 8,
        ),
        array( 'voucher_no' => $id )
    );

    $wpdb->delete( $wpdb->prefix . 'erp_acct_ledger_details', array( 'trn_no' => $id ) );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_bill_account_details', array( 'bill_no' => $id ) );
}

/**
 * Get formatted bill data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_bill_data( $data, $voucher_no ) {
    $bill_data = [];

    $vendor = erp_get_people( $data['vendor_id'] );

    $bill_data['voucher_no']      = ! empty( $voucher_no ) ? $voucher_no : 0;
    $bill_data['vendor_id']       = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 1;
    $bill_data['vendor_name']     = isset( $vendor ) ? $vendor->first_name . ' ' . $vendor->last_name : '';
    $bill_data['billing_address'] = isset( $data['billing_address'] ) ? $data['billing_address'] : '';
    $bill_data['trn_date']        = isset( $data['trn_date'] ) ? $data['trn_date'] : date( 'Y-m-d' );
    $bill_data['due_date']        = isset( $data['due_date'] ) ? $data['due_date'] : date( 'Y-m-d' );
    $bill_data['created_at']      = date( 'Y-m-d' );
    $bill_data['amount']          = isset( $data['amount'] ) ? $data['amount'] : 0;
    $bill_data['ref']             = isset( $data['ref'] ) ? $data['ref'] : '';
    $bill_data['due']             = isset( $data['due'] ) ? $data['due'] : 0;
    $bill_data['attachments']     = isset( $data['attachments'] ) ? $data['attachments'] : '';
    // translators: %s: voucher_no
    $bill_data['particulars']      = ! empty( $data['particulars'] ) ? $data['particulars'] : sprintf( __( 'Bill created with voucher no %s', 'erp' ), $voucher_no );
    $bill_data['bill_details']     = isset( $data['bill_details'] ) ? $data['bill_details'] : '';
    $bill_data['status']           = isset( $data['status'] ) ? $data['status'] : 1;
    $bill_data['trn_by_ledger_id'] = isset( $data['trn_by'] ) ? $data['trn_by'] : null;
    $bill_data['created_at']       = date( 'Y-m-d' );
    $bill_data['created_by']       = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $bill_data['updated_at']       = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $bill_data['updated_by']       = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $bill_data;
}

/**
 * Insert bill/s data into ledger
 *
 * @param array $bill_data
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_insert_bill_data_into_ledger( $bill_data, $item_data ) {
    global $wpdb;

    $draft = 1;

    if ( $draft === $bill_data['status'] ) {
        return;
    }

    // Insert items amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $item_data['ledger_id'],
			'trn_no'      => $bill_data['voucher_no'],
			'particulars' => $bill_data['particulars'],
			'debit'       => $item_data['amount'],
			'credit'      => 0,
			'trn_date'    => $bill_data['trn_date'],
			'created_at'  => $bill_data['created_at'],
			'created_by'  => $bill_data['created_by'],
			'updated_at'  => $bill_data['updated_at'],
			'updated_by'  => $bill_data['updated_by'],
        )
    );

}


/**
 * Update bill/s data into ledger
 *
 * @param array $bill_data
 * @param array $bill_no
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_update_bill_data_into_ledger( $bill_data, $bill_no, $item_data ) {
    global $wpdb;

    $user_id = get_current_user_id();

    $bill_data['created_at'] = date( 'Y-m-d H:i:s' );
    $bill_data['created_by'] = $user_id;
    $bill_data['updated_at'] = date( 'Y-m-d H:i:s' );
    $bill_data['updated_by'] = $user_id;

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $item_data['ledger_id'],
			'trn_no'      => $bill_no,
			'particulars' => $bill_data['particulars'],
			'debit'       => 0,
			'credit'      => $item_data['amount'],
			'trn_date'    => $bill_data['trn_date'],
			'created_at'  => $bill_data['created_at'],
			'created_by'  => $bill_data['created_by'],
			'updated_at'  => $bill_data['updated_at'],
			'updated_by'  => $bill_data['updated_by'],
        )
    );
}

/**
 * Get Bill count
 *
 * @return int
 */
function erp_acct_get_bill_count() {
    global $wpdb;

    $row = $wpdb->get_row( 'SELECT COUNT(*) as count FROM ' . $wpdb->prefix . 'erp_acct_bills' );

    return $row->count;
}

/**
 * Get bills with due of a people
 *
 * @param array $args
 * @return mixed
 */

function erp_acct_get_due_bills_by_people( $args = [] ) {
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

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $bills            = "{$wpdb->prefix}erp_acct_bills";
    $bill_act_details = "{$wpdb->prefix}erp_acct_bill_account_details";
    $items            = $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';

    $query = $wpdb->prepare(
        "SELECT $items FROM $bills as bill INNER JOIN (
            SELECT bill_no, ABS(SUM( ba.debit - ba.credit)) as due
            FROM $bill_act_details as ba
            GROUP BY ba.bill_no HAVING due > 0 ) as bs
            ON bill.voucher_no = bs.bill_no
            WHERE bill.vendor_id = %d AND bill.status != 1
            ORDER BY %s %s $limit",
        $args['people_id'],
        $args['orderby'],
        $args['order']
    );

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
function erp_acct_get_bill_due( $bill_no ) {
    global $wpdb;

    $result = $wpdb->get_row( $wpdb->prepare( "SELECT bill_no, SUM( ba.debit - ba.credit) as due FROM {$wpdb->prefix}erp_acct_bill_account_details as ba WHERE ba.bill_no = %d GROUP BY ba.bill_no", $bill_no ), ARRAY_A );

    return $result['due'];
}

