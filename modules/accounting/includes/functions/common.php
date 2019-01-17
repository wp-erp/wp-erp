<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Upload attachments
 *
 * @return array
 */
function erp_acct_upload_attachments($files) {
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    $attachments = [];
    $movefiles = [];

    // Formatting request for upload
    for ( $i = 0; $i < count($files['name']); $i++ ) {
        $attachments[] = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
    }

    foreach ( $attachments as $attachment ) {
        $movefiles[] = wp_handle_upload( $attachment, [ 'test_form' => false ] );
    }

    return $movefiles;
}

/**
 * Change stock status of a product
 *
 * @param $product_id
 * @param $trn_no
 * @param $qty
 * @param $stock_in
 */
function erp_acct_change_inventory_status( $product_id, $trn_no, $qty, $stock_in ) {

}

/**
 * Get payable data for given month
 *
 * @param $from
 * @param $to
 *
 * @return array|null|object
 */
function erp_acct_get_payables( $from, $to ) {
    global $wpdb;

    $from_date = date( "Y-m-d", strtotime( $from ) );
    $to_date = date( "Y-m-d", strtotime( $to ) );

    $purchases = $wpdb->prefix . 'erp_acct_purchase';
    $purchase_acct_details = $wpdb->prefix . 'erp_acct_purchase_account_details';

    $purchase_query = $wpdb->prepare( "Select voucher_no, SUM(ad.debit - ad.credit) as due, due_date
                              FROM $purchases 
                              LEFT JOIN $purchase_acct_details as ad 
                              ON ad.purchase_no = voucher_no  where due_date 
                              BETWEEN %s and %s
                              Group BY voucher_no Having due < 0 ", $from_date, $to_date );

    $purchase_results = $wpdb->get_results( $purchase_query, ARRAY_A );

    $bills = $wpdb->prefix . 'erp_acct_bills';
    $bill_acct_details = $wpdb->prefix . 'erp_acct_bill_account_details';
    $bills_query = $wpdb->prepare( "Select voucher_no, SUM(ad.debit - ad.credit) as due, due_date
                              FROM $bills 
                              LEFT JOIN $bill_acct_details as ad 
                              ON ad.bill_no = voucher_no  where due_date 
                              BETWEEN %s and %s
                              Group BY voucher_no Having due < 0 ", $from_date, $to_date );

    $bill_results = $wpdb->get_results( $bills_query, ARRAY_A );

    if ( !empty( $purchase_results) && !empty( $bill_results ) ) {
        return array_merge( null, $purchase_results );
    }

    if ( empty( $bill_results ) ) {
        return $purchase_results;
    }

    if ( empty( $purchase_results ) ) {
        return $bill_results;
    }

}

/**
 * Get Payable overview data
 *
 * @return array
 */
function erp_acct_get_payables_overview() {
    // get dates till coming 90 days
    $from_date = date( "Y-m-d" );
    $to_date = date( "Y-m-d", strtotime( "+90 day", strtotime( $from_date ) ) );

    $data = [];
    $amount = [
        'first'  => 0,
        'second' => 0,
        'third'  => 0,
    ];

    $result = erp_acct_get_payables( $from_date, $to_date );

    if ( !empty( $result ) ) {
        $from_date = new DateTime( $from_date );

        foreach ( $result as $item_data ) {
            $item = (object)$item_data;
            $later = new DateTime( $item->due_date );
            $diff = $later->diff( $from_date )->format( "%a" );

            //segment by date difference
            switch ( $diff ) {
                case ( $diff <= 30 ):
                    $data['first'][] = $item_data;
                    $amount['first'] = $amount['first'] + abs( $item->due );
                    break;
                case ( $diff <= 60 ):
                    $data['second'][] = $item_data;
                    $amount['second'] = $amount['second'] + abs( $item->due );
                    break;
                case ( $diff <= 90 ):
                    $data['third'][] = $item_data;
                    $amount['third'] = $amount['third'] + abs( $item->due );
                    break;

                default:

            }
        }
    }

    return [ 'data' => $data, 'amount' => $amount ];
}
