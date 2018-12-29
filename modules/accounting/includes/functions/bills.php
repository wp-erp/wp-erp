<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all bills
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_bills( $args = [] ) {
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
    $sql .= "FROM {$wpdb->prefix}erp_acct_bills ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var($sql);
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
    bill.attachments,
    bill.created_at,
    bill.created_by,
    bill.updated_at,
    bill.updated_by,
    
    b_detail.amount,
    
    ledg.id,
    ledg.chart_id,
    ledg.category_id,
    ledg.name,
    ledg.code,
    ledg.system,
                  
    ledg_detail.debit,
    ledg_detail.credit,
    
    b_ac_detail.id,
    b_ac_detail.bill_no
    
    FROM {$wpdb->prefix}erp_acct_bills AS bill
    
    LEFT JOIN {$wpdb->prefix}erp_acct_bill_details AS b_detail ON bill.voucher_no = b_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_bill_account_details AS b_ac_detail ON bill.voucher_no = b_ac_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledg_detail ON bill.voucher_no = ledg_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_ledgers AS ledg ON ledg.id = ledg_detail.ledger_id
    
    WHERE bill.voucher_no = {$bill_no}";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
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
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'       => 'bill',
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
            'attachments'     => $bill_data['attachments'],
            'ref'             => $bill_data['ref'],
            'particulars'     => $bill_data['remarks'],
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

        $wpdb->insert( $wpdb->prefix . 'erp_acct_bill_account_details', array(
            'bill_no'    => $voucher_no,
            'trn_no'     => $voucher_no,
            'particulars'=> '',
            'debit'      => 0,
            'credit'     => $bill_data['total'],
            'created_at'  => $bill_data['created_at'],
            'created_by'  => $bill_data['created_by'],
            'updated_at'  => $bill_data['updated_at'],
            'updated_by'  => $bill_data['updated_by'],
        ) );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'bill-exception', $e->getMessage() );
    }

    return $voucher_no;

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

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $bill_data = erp_acct_get_formatted_bill_data( $data, $bill_id );

        $wpdb->update( $wpdb->prefix . 'erp_acct_bills', array(
            'vendor_id'       => $bill_data['vendor_id'],
            'vendor_name'     => $bill_data['vendor_name'],
            'trn_date'        => $bill_data['trn_date'],
            'due_date'        => $bill_data['due_date'],
            'address'         => $bill_data['billing_address'],
            'amount'          => $bill_data['total'],
            'type'            => $bill_data['type'],
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
            'bill_no'    => $bill_id,
            'particulars'=> '',
            'debit'      => 0,
            'credit'     => $bill_data['amount'],
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
        return new WP_error( 'bill-exception', $e->getMessage() );
    }

    return $bill_id;

}

/**
 * Delete a bill
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_bill( $id ) {
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->delete( $wpdb->prefix . 'erp_acct_bill', array( 'voucher_no' => $id ) );
}

/**
 * Void a bill
 *
 * @param $id
 * @return void
 */
function erp_acct_void_bill( $id ) {
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

    $bill_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $bill_data['vendor_id'] = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 1;
    $bill_data['vendor_name'] = isset( $data['vendor_name'] ) ? $data['vendor_name'] : '';
    $bill_data['billing_address'] = isset( $data['billing_address'] ) ? $data['billing_address'] : '';
    $bill_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $bill_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $bill_data['created_at'] = date("Y-m-d" );
    $bill_data['address'] = isset( $data['address'] ) ? maybe_serialize( $data['address'] ) : '';
    $bill_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $bill_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $bill_data['ref'] = isset( $data['ref'] ) ? $data['ref'] : '';
    $bill_data['remarks'] = isset( $data['remarks'] ) ? $data['remarks'] : '';
    $bill_data['bill_details'] = isset( $data['bill_details'] ) ? $data['bill_details'] : '';
    $bill_data['status'] = isset( $data['status'] ) ? $data['status'] : 1;
    $bill_data['created_at'] = date("Y-m-d" );
    $bill_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $bill_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $bill_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

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

    // Insert amount in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => $item_data['ledger_id'],
        'trn_no'      => $bill_data['trn_no'],
        'particulars' => $bill_data['remarks'],
        'debit'       => $item_data['amount'],
        'credit'      => 0,
        'trn_date'    => $bill_data['trn_date'],
        'created_at'  => $bill_data['created_at'],
        'created_by'  => $bill_data['created_by'],
        'updated_at'  => $bill_data['updated_at'],
        'updated_by'  => $bill_data['updated_by'],
    ) );

}

/**
 * Update bill/s data into ledger
 *
 * @param array $bill_data
 * * @param array $bill_no
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_update_bill_data_into_ledger( $bill_data, $bill_no, $item_data ) {
    global $wpdb;

    // Update amount in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => $item_data['ledger_id'],
        'particulars' => $bill_data['remarks'],
        'debit'       => $item_data['amount'],
        'credit'      => 0,
        'trn_date'    => $bill_data['trn_date'],
        'created_at'  => $bill_data['created_at'],
        'created_by'  => $bill_data['created_by'],
        'updated_at'  => $bill_data['updated_at'],
        'updated_by'  => $bill_data['updated_by'],
    ), array(
        'trn_no' => $bill_no,
    ) );

}

/**
 * Get Bill count
 *
 * @return int
 */
function erp_acct_get_bill_count() {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT COUNT(*) as count FROM " . $wpdb->prefix . "erp_acct_bills" );

    return $row->count;
}
