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
function erp_acct_get_purchases( $args = [] ) {
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
    $sql .= "FROM {$wpdb->prefix}erp_acct_purchase ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var($sql);
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get a purchase
 *
 * @param $purchase_no
 * @return mixed
 */
function erp_acct_get_purchase( $purchase_no ) {
    global $wpdb;

    $sql = "SELECT 

    purchase.voucher_no,
    purchase.vendor_id,
    purchase.trn_date,
    purchase.due_date,
    purchase.amount,
    purchase.vendor_name,
    purchase.ref,
    purchase.status,
    purchase.attachments,
    purchase.particulars,
    purchase.created_at,
    purchase.created_by,
    purchase.updated_at,
    purchase.updated_by,
    
    purchase_detail.product_id,
    purchase_detail.qty,
    purchase_detail.price,
    purchase_detail.amount,
    
    purchase_acc_detail.purchase_no,
    purchase_acc_detail.particulars,
    purchase_acc_detail.debit,
    purchase_acc_detail.credit,

    product.name,
    product.product_type_id,
    product.category_id,
    product.vendor,
    product.cost_price,
    product.sale_price
    
    FROM {$wpdb->prefix}erp_acct_purchase AS purchase
    LEFT JOIN {$wpdb->prefix}erp_acct_purchase_details AS purchase_detail ON purchase.voucher_no = purchase_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_purchase_account_details AS purchase_acc_detail ON purchase.voucher_no = purchase_acc_detail.trn_no
    LEFT JOIN {$wpdb->prefix}erp_acct_products AS product ON purchase_detail.product_id = product.id
    WHERE purchase.voucher_no = {$purchase_no} LIMIT 1";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
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
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;
    $voucher_no = 0;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'       => 'sales_purchase',
            'created_at' => $data['created_at'],
            'created_by' => $created_by,
            'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
            'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : ''
        ) );

        $voucher_no = $wpdb->insert_id;
        $purchase_no = $voucher_no;

        $purchase_data = erp_acct_get_formatted_purchase_data( $data, $voucher_no );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase', array(
            'voucher_no'      => $voucher_no,
            'vendor_id'       => $purchase_data['vendor_id'],
            'vendor_name'     => $purchase_data['vendor_name'],
            'trn_date'        => $purchase_data['trn_date'],
            'due_date'        => $purchase_data['due_date'],
            'amount'          => $purchase_data['amount'],
            'ref'             => $purchase_data['ref'],
            'status'          => $purchase_data['status'],
            'attachments'     => $purchase_data['attachments'],
            'particulars'     => $purchase_data['particulars'],
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
                'price'         => $item['unit_price'],
                'amount'        => $item['item_total'],
                'created_at'    => $purchase_data['created_at'],
                'created_by'    => $created_by,
                'updated_at'    => $purchase_data['updated_at'],
                'updated_by'    => $purchase_data['updated_by']
            ) );
        }

        $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
            'purchase_no'   => $purchase_no,
            'trn_no'        => $voucher_no,
            'particulars'   => $purchase_data['particulars'],
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

    return $purchase_no;

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

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

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
            'particulars'     => $purchase_data['particulars'],
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
            'particulars'   => $purchase_data['particulars'],
            'debit'         => 0,
            'credit'        => $purchase_data['amount'],
            'created_at'    => $purchase_data['created_at'],
            'created_by'    => $purchase_data['created_by'],
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
    $user_info = get_userdata( $data['vendor_id'] );

    $purchase_data['voucher_no']  = isset( $data['voucher_no'] ) ? $data['voucher_no'] : 1;
    $purchase_data['vendor_id']   = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 1;
    $purchase_data['vendor_name'] = $user_info->first_name . ' ' . $user_info->last_name;
    $purchase_data['trn_date']    = isset( $data['date'] ) ? $data['date'] : date( "Y-m-d" );
    $purchase_data['due_date']    = isset( $data['due_date'] ) ? $data['due_date'] : date( "Y-m-d" );
    $purchase_data['amount']      = isset( $data['amount'] ) ? $data['amount'] : 0;
    $purchase_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $purchase_data['status']      = isset( $data['status'] ) ? $data['status'] : '';
    $purchase_data['ref']         = isset( $data['ref'] ) ? $data['ref'] : '';
    $purchase_data['particulars'] = isset( $data['particulars'] ) ? $data['particulars'] : '';
    $purchase_data['created_at']  = date( "Y-m-d" );
    $purchase_data['created_by']  = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $purchase_data['updated_at']  = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $purchase_data['updated_by']  = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

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
        'trn_no'      => $purchase_data['voucher_no'],
        'particulars' => $purchase_data['particulars'],
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
        'particulars' => $purchase_data['particulars'],
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

/**
 * Get Purchases count
 *
 * @return int
 */
function erp_acct_get_purchase_count() {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT COUNT(*) as count FROM " . $wpdb->prefix . "erp_acct_purchase" );

    return $row->count;
}


/**
 * Get due purchases by vendor
 *
 * @return mixed
 */

function erp_acct_get_due_purchases_by_vendor( $args ) {
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

    $purchases= "{$wpdb->prefix}erp_acct_purchase";
    $purchase_act_details = "{$wpdb->prefix}erp_acct_purchase_account_details";
    $items = $args['count'] ? " COUNT( id ) as total_number " : " * ";

    $query = $wpdb->prepare( "SELECT $items FROM $purchases as purchase INNER JOIN 
                                (
                                    SELECT purchase_no, SUM( pa.credit - pa.debit) as due 
                                    FROM $purchase_act_details as pa
                                    GROUP BY pa.purchase_no
                                    HAVING due > 0
                                ) as ps
                                ON purchase.voucher_no = ps.purchase_no
                                WHERE purchase.vendor_id = %d
                                ORDER BY %s %s $limit", $args['vendor_id'],$args['orderby'],$args['order']  );

    if ( $args['count'] ) {
        return $wpdb->get_var( $query );
    }

    return $wpdb->get_results( $query, ARRAY_A );
}
