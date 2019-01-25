<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all expenses
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_expenses( $args = [] ) {
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
    $sql .= "FROM {$wpdb->prefix}erp_acct_bills WHERE `trn_by_ledger_id` IS NOT NULL ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var($sql);
    }

    $rows = $wpdb->get_results( $sql, ARRAY_A );

    return $rows;
}

/**
 * Get a single expense
 *
 * @param $bill_no
 * @return mixed
 */
function erp_acct_get_expense( $bill_no ) {
    global $wpdb;

    $sql = "SELECT

    bill.id,
    bill.voucher_no,
    bill.vendor_id,
    bill.vendor_name,
    bill.address,
    bill.trn_date,
    bill.due_date,
    bill.amount,
    bill.ref,
    bill.particulars,
    bill.status,
    bill.trn_by_ledger_id,
    bill.attachments,
    bill.created_at,
    bill.created_by,
    bill.updated_at,
    bill.updated_by,

    b_detail.amount,

    ledg_detail.debit,
    ledg_detail.credit,

    b_ac_detail.id,
    b_ac_detail.bill_no

    FROM {$wpdb->prefix}erp_acct_bills AS bill

    LEFT JOIN {$wpdb->prefix}erp_acct_bill_details AS b_detail ON bill.voucher_no = b_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_bill_account_details AS b_ac_detail ON bill.voucher_no = b_ac_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledg_detail ON bill.voucher_no = ledg_detail.trn_no

    WHERE bill.voucher_no = {$bill_no} AND bill.trn_by_ledger_id IS NOT NULL";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
}

/**
 * Insert a expense
 *
 * @param $data
 * @return mixed
 */
function erp_acct_insert_expense( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $created_by;
    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'       => 'expense',
            'created_at' => $data['created_at'],
            'created_by' => $data['created_by'],
            'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
            'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : ''
        ) );

        $voucher_no = $wpdb->insert_id;

        $bill_data = erp_acct_get_formatted_bill_data( $data, $voucher_no );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_bills', array(
            'voucher_no'      => $bill_data['voucher_no'],
            'vendor_id'       => $bill_data['vendor_id'],
            'vendor_name'     => $bill_data['vendor_name'],
            'address'         => $bill_data['billing_address'],
            'trn_date'        => $bill_data['trn_date'],
            'due_date'        => $bill_data['due_date'],
            'amount'          => $bill_data['amount'],
            'ref'             => $bill_data['ref'],
            'particulars'     => $bill_data['remarks'],
            'status'          => $bill_data['status'],
            'trn_by_ledger_id'=> $bill_data['trn_by_ledger_id'],
            'attachments'     => $bill_data['attachments'],
            'created_at'      => $bill_data['created_at'],
            'created_by'      => $bill_data['created_by'],
            'updated_at'      => $bill_data['updated_at'],
            'updated_by'      => $bill_data['updated_by'],
        ) );

        $items = $bill_data['bill_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_bill_details', array(
                'trn_no'      => $voucher_no,
                'ledger_id'   => $item['ledger_id'],
                'particulars' => $item['description'],
                'amount'      => $item['amount'],
                'created_at'  => $bill_data['created_at'],
                'created_by'  => $bill_data['created_by'],
                'updated_at'  => $bill_data['updated_at'],
                'updated_by'  => $bill_data['updated_by'],
            ) );

            erp_acct_insert_bill_data_into_ledger( $bill_data, $item );
        }

        //Insert into Ledger for source account
        erp_acct_insert_expense_data_into_ledger( $bill_data );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_bill_account_details', array(
            'bill_no'     => $voucher_no,
            'trn_no'      => $voucher_no,
            'trn_date'    => $bill_data['trn_date'],
            'particulars' => $bill_data['remarks'],
            'debit'       => 0,
            'credit'      => $bill_data['amount'],
            'created_at'  => $bill_data['created_at'],
            'created_by'  => $bill_data['created_by'],
            'updated_at'  => $bill_data['updated_at'],
            'updated_by'  => $bill_data['updated_by'],
        ) );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'expense-exception', $e->getMessage() );
    }

    return $voucher_no;

}

/**
 * Update a expense
 *
 * @param $data
 * @param $bill_id
 *
 * @return mixed
 */
function erp_acct_update_expense( $data, $bill_id ) {
    global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $bill_data = erp_acct_get_formatted_bill_data( $data, $bill_id );

        $wpdb->update( $wpdb->prefix . 'erp_acct_bills', array(
            'vendor_id'       => $bill_data['vendor_id'],
            'vendor_name'     => $bill_data['vendor_name'],
            'address'         => $bill_data['billing_address'],
            'trn_date'        => $bill_data['trn_date'],
            'due_date'        => $bill_data['due_date'],
            'amount'          => $bill_data['amount'],
            'ref'             => $bill_data['ref'],
            'particulars'     => $bill_data['remarks'],
            'status'          => $bill_data['status'],
            'trn_by_ledger_id'=> $bill_data['trn_by_ledger_id'],
            'attachments'     => $bill_data['attachments'],
            'created_at'      => $bill_data['created_at'],
            'created_by'      => $bill_data['created_by'],
            'updated_at'      => $bill_data['updated_at'],
            'updated_by'      => $bill_data['updated_by'],
        ), array(
            'voucher_no'      => $bill_id
        ) );

        $items = $bill_data['bill_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->update( $wpdb->prefix . 'erp_acct_bill_details', array(
                'ledger_id'   => $item['ledger_id'],
                'particulars' => $item['remarks'],
                'amount'      => $item['amount'],
                'created_at'  => $bill_data['created_at'],
                'created_by'  => $bill_data['created_by'],
                'updated_at'  => $bill_data['updated_at'],
                'updated_by'  => $bill_data['updated_by'],
            ), array(
                'trn_no'  => $bill_id
            ));

            erp_acct_update_bill_data_into_ledger( $bill_data, $bill_id, $item );
        }

        $wpdb->update( $wpdb->prefix . 'erp_acct_bill_account_details', array(
            'bill_no'     => $bill_id,
            'particulars' => $bill_data['remarks'],
            'debit'       => 0,
            'credit'      => $bill_data['total'],
            'created_at'  => $bill_data['created_at'],
            'created_by'  => $bill_data['created_by'],
            'updated_at'  => $bill_data['updated_at'],
            'updated_by'  => $bill_data['updated_by'],
        ), array(
            'trn_no'     => $bill_id
        ) );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'expense-exception', $e->getMessage() );
    }

    return $bill_id;

}

/**
 * Delete a expense
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_expense( $id ) {
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->delete( $wpdb->prefix . 'erp_acct_bills', array( 'voucher_no' => $id ) );
}

/**
 * Void a expense
 *
 * @param $id
 * @return void
 */
function erp_acct_void_expense( $id ) {
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_bills',
        array(
            'status' => 'void',
        ),
        array( 'voucher_no' => $id )
    );
}
