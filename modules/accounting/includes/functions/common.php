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
