<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all pay_purchases
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_pay_purchases() {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_pay_purchase", ARRAY_A );

    return $rows;
}

/**
 * Get a pay_purchase
 *
 * @param $purchase_no
 * @return mixed
 */
function erp_acct_get_pay_purchase( $purchase_no ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_pay_purchase WHERE purchase_no = {$purchase_no}", ARRAY_A );

    return $row;
}

/**
 * Insert a pay_purchase
 *
 * @param $data
 * @param $pay_purchase_id
 * @param $due
 * @return mixed
 */
function erp_acct_insert_pay_purchase( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'       => 'pay_purchase',
            'created_at' => $data['created_at'],
            'created_by' => $created_by,
            'updated_at' => $data['updated_at'],
            'updated_by' => $data['updated_by'],
        ) );

        $voucher_no = $wpdb->insert_id;

        $pay_purchase_data = erp_acct_get_formatted_pay_purchase_data( $data, $voucher_no );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_pay_purchase', array(
            'voucher_no'      => $voucher_no,
            'trn_date'        => $pay_purchase_data['trn_date'],
            'amount'          => $pay_purchase_data['amount'],
            'trn_by'          => $pay_purchase_data['trn_by'],
            'remarks'         => $pay_purchase_data['remarks'],
            'attachments'     => $pay_purchase_data['attachments'],
            'created_at'      => $pay_purchase_data['created_at'],
            'created_by'      => $created_by,
            'updated_at'      => $pay_purchase_data['updated_at'],
            'updated_by'      => $pay_purchase_data['updated_by'],
        ) );

        $items = $pay_purchase_data['purchase_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_pay_purchase_details', array(
                'voucher_no'  => $voucher_no,
                'purchase_no' => $item['id'],
                'amount'      => $item['amount'],
                'created_at'  => $pay_purchase_data['created_at'],
                'created_by'  => $created_by,
                'updated_at'  => $pay_purchase_data['updated_at'],
                'updated_by'  => $pay_purchase_data['updated_by'],
            ) );

            erp_acct_insert_pay_purchase_data_into_ledger( $pay_purchase_data, $item );
        }

        $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
            'purchase_no' => $voucher_no,
            'trn_no'      => $voucher_no,
            'remarks'     => $pay_purchase_data['remarks'],
            'debit'       => $pay_purchase_data['amount'],
            'credit'      => 0,
            'created_at'  => $pay_purchase_data['created_at'],
            'created_by'  => $created_by,
            'updated_at'  => $pay_purchase_data['updated_at'],
            'updated_by'  => $pay_purchase_data['updated_by'],
        ) );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'pay-purchase-exception', $e->getMessage() );
    }

    return $voucher_no;

}

/**
 * Update a pay_purchase
 *
 * @param $data
 * @param $pay_purchase_id
 * @param $due
 * @return mixed
 */
function erp_acct_update_pay_purchase( $data, $pay_purchase_id ) {
    global $wpdb;

    $created_by = get_current_user_id();

    try {
        $wpdb->query( 'START TRANSACTION' );

        $pay_purchase_data = erp_acct_get_formatted_pay_purchase_data( $data, $pay_purchase_id );

        $wpdb->update( $wpdb->prefix . 'erp_acct_pay_purchase', array(
            'trn_date'        => $pay_purchase_data['trn_date'],
            'amount'          => $pay_purchase_data['amount'],
            'trn_by'          => $pay_purchase_data['trn_by'],
            'remarks'         => $pay_purchase_data['particulars'],
            'attachments'     => $pay_purchase_data['attachments'],
            'created_at'      => $pay_purchase_data['created_at'],
            'created_by'      => $created_by,
            'updated_at'      => $pay_purchase_data['updated_at'],
            'updated_by'      => $pay_purchase_data['updated_by'],
        ), array (
            'voucher_no'      => $pay_purchase_id,
        ) );

        $items = $pay_purchase_data['purchase_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->update( $wpdb->prefix . 'erp_acct_pay_purchase_details', array(
                'purchase_no' => $item['id'],
                'amount'      => $item['amount'],
                'created_at'  => $pay_purchase_data['created_at'],
                'created_by'  => $created_by,
                'updated_at'  => $pay_purchase_data['updated_at'],
                'updated_by'  => $pay_purchase_data['updated_by'],
            ), array (
                'voucher_no'  => $pay_purchase_id,
            ) );

            erp_acct_update_pay_purchase_data_into_ledger( $pay_purchase_data, $pay_purchase_id, $item );
        }

        $wpdb->update( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
            'trn_no'      => $pay_purchase_id,
            'remarks'     => $pay_purchase_data['remarks'],
            'debit'       => $pay_purchase_data['amount'],
            'credit'      => 0,
            'created_at'  => $pay_purchase_data['created_at'],
            'created_by'  => $created_by,
            'updated_at'  => $pay_purchase_data['updated_at'],
            'updated_by'  => $pay_purchase_data['updated_by'],
        ), array (
            'purchase_no'      => $pay_purchase_id,
        ) );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'pay-purchase-exception', $e->getMessage() );
    }

    return $pay_purchase_id;

}

/**
 * Delete a pay_purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_delete_pay_purchase( $id ) {
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->delete( $wpdb->prefix . 'erp_acct_pay_purchase', array( 'voucher_no' => $id ) );
}

/**
 * Void a pay_purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_void_pay_purchase( $id ) {
    global $wpdb;

    if ( !$id ) {
        return;
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_pay_purchase',
        array(
            'status' => 'void',
        ),
        array( 'voucher_no' => $id )
    );
}

/**
 * Get formatted pay_purchase data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_pay_purchase_data( $data, $voucher_no ) {
    $pay_purchase_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $pay_purchase_data['order_no'] = isset( $data['order_no'] ) ? $data['order_no'] : 1;
    $pay_purchase_data['purchase_details'] = isset( $data['purchase_details'] ) ? $data['purchase_details'] : '';
    $pay_purchase_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $pay_purchase_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $pay_purchase_data['trn_by'] = isset( $data['trn_by'] ) ? $data['trn_by'] : '';
    $pay_purchase_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $pay_purchase_data['remarks'] = isset( $data['remarks'] ) ? $data['remarks'] : '';
    $pay_purchase_data['created_at'] = date("Y-m-d" );
    $pay_purchase_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $pay_purchase_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $pay_purchase_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $pay_purchase_data;
}

/**
 * Insert pay_purchase/s data into ledger
 *
 * @param array $pay_purchase_data
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_insert_pay_purchase_data_into_ledger( $pay_purchase_data, $item_data ) {
    global $wpdb;

    // Insert amount in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 605, //change later
        'trn_no'      => $pay_purchase_data['trn_no'],
        'remarks' => $pay_purchase_data['remarks'],
        'debit'       => $item_data['amount'],
        'credit'      => 0,
        'trn_date'    => $pay_purchase_data['trn_date'],
        'created_at'  => $pay_purchase_data['created_at'],
        'created_by'  => $pay_purchase_data['created_by'],
        'updated_at'  => $pay_purchase_data['updated_at'],
        'updated_by'  => $pay_purchase_data['updated_by'],
    ) );

}

/**
 * Update pay_purchase/s data into ledger
 *
 * @param array $pay_purchase_data
 * * @param array $pay_purchase_no
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_update_pay_purchase_data_into_ledger( $pay_purchase_data, $pay_purchase_no, $item_data ) {
    global $wpdb;

    // Update amount in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => 605, // change later
        'remarks'     => $pay_purchase_data['remarks'],
        'debit'       => $item_data['amount'],
        'credit'      => 0,
        'trn_date'    => $pay_purchase_data['trn_date'],
        'created_at'  => $pay_purchase_data['created_at'],
        'created_by'  => $pay_purchase_data['created_by'],
        'updated_at'  => $pay_purchase_data['updated_at'],
        'updated_by'  => $pay_purchase_data['updated_by'],
    ), array(
        'trn_no' => $pay_purchase_no,
    ) );

}
