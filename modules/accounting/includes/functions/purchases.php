<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all purchases
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_purchases() {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_purchase_account_details", ARRAY_A );

    return $rows;
}

/**
 * Get a purchase
 *
 * @param $purchase_no
 * @return mixed
 */
function erp_acct_get_purchase( $purchase_no ) {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_purchase_account_details WHERE voucher_no = {$purchase_no}", ARRAY_A );

    return $rows;
}

/**
 * Insert a purchase
 *
 * @param $data
 * @param $due
 * @return mixed
 */
function erp_acct_insert_purchase( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'       => 'sales_purchase',
            'created_at' => $data['created_at'],
            'created_by' => $created_by,
            'updated_at' => $data['updated_at'],
            'updated_by' => $data['updated_by'],
        ) );

        $voucher_no = $wpdb->insert_id;

        $purchase_data = erp_acct_get_formatted_purchase_data( $data, $voucher_no );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase', array(
            'voucher_no'      => $purchase_data['voucher_no'],
            'vendor_id'       => $purchase_data['vendor_id'],
            'vendor_name'     => $purchase_data['vendor_name'],
            'trn_date'        => $purchase_data['trn_date'],
            'due_date'        => $purchase_data['due_date'],
            'amount'          => $purchase_data['total'],
            'ref'             => $purchase_data['ref'],
            'status'          => $purchase_data['status'],
            'attachments'     => $purchase_data['attachments'],
            'remarks'         => $purchase_data['remarks'],
            'created_at'      => $purchase_data['created_at'],
            'created_by'      => $created_by,
            'updated_at'      => $purchase_data['updated_at'],
            'updated_by'      => $purchase_data['updated_by'],
        ) );

        $items = $data['line_items'];

        foreach( $items as $key => $item ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_details', array(
                'trn_no'        => $voucher_no,
                'product_id'    => $item['product_id'],
                'qty'           => $item['qty'],
                'amount'        => $item['amount'],
                'created_at'    => $purchase_data['created_at'],
                'created_by'    => $created_by,
                'updated_at'    => $purchase_data['updated_at'],
                'updated_by'    => $purchase_data['updated_by']
            ) );
        }

        $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
            'purchase_no'   => $voucher_no,
            'trn_no'        => $voucher_no,
            'remarks'       => $purchase_data['remarks'],
            'debit'         => 0,
            'credit'        => $purchase_data['amount'],
            'created_at'    => $purchase_data['created_at'],
            'created_by'    => $created_by,
            'updated_at'    => $purchase_data['updated_at'],
            'updated_by'    => $purchase_data['updated_by']
        ) );

        erp_acct_insert_purchase_data_into_ledger( $purchase_data );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'purchase-exception', $e->getMessage() );
    }

    return $voucher_no;

}

/**
 * Update a purchase
 *
 * @param $data
 * @param $purchase_id
 * @param $due
 * @return mixed
 */
function erp_acct_update_purchase( $data, $purchase_id ) {
    global $wpdb;

    $created_by = get_current_user_id();

    try {
        $wpdb->query( 'START TRANSACTION' );

        $purchase_data = erp_acct_get_formatted_purchase_data( $data, $purchase_id );

        $wpdb->update( $wpdb->prefix . 'erp_acct_purchase', array(
            'vendor_id'       => $purchase_data['vendor_id'],
            'vendor_name'     => $purchase_data['vendor_name'],
            'trn_date'        => $purchase_data['trn_date'],
            'due_date'        => $purchase_data['due_date'],
            'amount'          => $purchase_data['total'],
            'ref'             => $purchase_data['ref'],
            'status'          => $purchase_data['status'],
            'attachments'     => $purchase_data['attachments'],
            'remarks'         => $purchase_data['remarks'],
            'created_at'      => $purchase_data['created_at'],
            'created_by'      => $purchase_data['created_by'],
            'updated_at'      => $purchase_data['updated_at'],
            'updated_by'      => $purchase_data['updated_by'],
        ), array(
            'voucher_no'      => $purchase_id
        ) );

        $items = $data['line_items'];

        foreach( $items as $key => $item ) {
            $wpdb->update( $wpdb->prefix . 'erp_acct_purchase_details', array(
                'product_id'    => $item['product_id'],
                'qty'           => $item['qty'],
                'unit_price'    => $item['unit_price'],
                'amount'        => $item['amount'],
                'created_at'    => $purchase_data['created_at'],
                'created_by'    => $purchase_data['created_by'],
                'updated_at'    => $purchase_data['updated_at'],
                'updated_by'    => $purchase_data['updated_by']
            ), array(
                'trn_no'      => $purchase_id,
            ) );
        }

        $wpdb->update( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
            'purchase_no'   => $purchase_id,
            'trn_no'        => $purchase_id,
            'remarks'       => $purchase_data['remarks'],
            'debit'         => 0,
            'credit'        => $purchase_data['amount'],
            'created_at'    => $purchase_data['created_at'],
            'created_by'    => $created_by,
            'updated_at'    => $purchase_data['updated_at'],
            'updated_by'    => $purchase_data['updated_by']
        ), array(
            'trn_no'     => $purchase_id
        ) );

        erp_acct_insert_purchase_data_into_ledger( $purchase_data );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'purchase-exception', $e->getMessage() );
    }

    return $purchase_id;

}

/**
 * Delete a purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_purchase( $id ) {
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->delete( $wpdb->prefix . 'erp_acct_purchase_account_details', array( 'purchase_no' => $id ) );
}

/**
 * Void a purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_void_purchase( $id ) {
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_purchase',
        array(
            'status' => 'void',
        ),
        array( 'voucher_no' => $id )
    );
}

/**
 * Get formatted purchase data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_purchase_data( $data, $voucher_no ) {
    $user_info = get_userdata( $data['people_id'] );


    $purchase_data['voucher_no'] = isset( $data['voucher_no'] ) ? $data['voucher_no'] : 1;
    $purchase_data['vendor_id'] = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 1;
    $purchase_data['vendor_name'] = $user_info->first_name . ' ' . $user_info->last_name;
    $purchase_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $purchase_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $purchase_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $purchase_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $purchase_data['status'] = isset( $data['status'] ) ? $data['status'] : '';
    $purchase_data['ref'] = isset( $data['ref'] ) ? $data['ref'] : '';
    $purchase_data['remarks'] = isset( $data['remarks'] ) ? $data['remarks'] : '';
    $purchase_data['created_at'] = date("Y-m-d" );
    $purchase_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $purchase_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $purchase_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $purchase_data;
}

/**
 * Insert purchase/s data into ledger
 *
 * @param array $purchase_data
 *
 * @return mixed
 */
function erp_acct_insert_purchase_data_into_ledger( $purchase_data ) {
    global $wpdb;

    // Insert amount in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 405, // @TODO change later
        'trn_no'      => $purchase_data['trn_no'],
        'particulars' => $purchase_data['remarks'],
        'debit'       => 0,
        'credit'      => $purchase_data['amount'],
        'trn_date'    => $purchase_data['trn_date'],
        'created_at'  => $purchase_data['created_at'],
        'created_by'  => $purchase_data['created_by'],
        'updated_at'  => $purchase_data['updated_at'],
        'updated_by'  => $purchase_data['updated_by'],
    ) );

}

/**
 * Update purchase/s data into ledger
 *
 * @param array $purchase_data
 * @param array $purchase_no
 *
 * @return mixed
 */
function erp_acct_update_purchase_data_into_ledger( $purchase_data, $purchase_no ) {
    global $wpdb;

    // Update amount in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 405, // @TODO change later
        'particulars' => $purchase_data['remarks'],
        'debit'       => 0,
        'credit'      => $purchase_data['amount'],
        'trn_date'    => $purchase_data['trn_date'],
        'created_at'  => $purchase_data['created_at'],
        'created_by'  => $purchase_data['created_by'],
        'updated_at'  => $purchase_data['updated_at'],
        'updated_by'  => $purchase_data['updated_by'],
    ), array(
        'trn_no' => $purchase_no,
    ) );

}
