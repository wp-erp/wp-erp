<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all product_cats
 *
 * @return mixed
 */
function erp_acct_get_all_product_cats() {
    global $wpdb;

    $row = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'erp_acct_product_categories', ARRAY_A );

    return $row;
}

/**
 * Get an single product
 *
 * @param $product_cat_no
 *
 * @return mixed
 */

function erp_acct_get_product_cat( $product_cat_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_acct_product_categories WHERE id = %d GROUP BY parent", $product_cat_id ), ARRAY_A );

    return $row;
}

/**
 * Insert product data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_product_cat( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;

    try {
        $wpdb->query( 'START TRANSACTION' );
        $product_cat_data = erp_acct_get_formatted_product_cat_data( $data );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_product_categories',
            array(
				'name'       => $product_cat_data['name'],
				'parent'     => $product_cat_data['parent'],
				'created_at' => $product_cat_data['created_at'],
				'created_by' => $product_cat_data['created_by'],
				'updated_at' => $product_cat_data['updated_at'],
				'updated_by' => $product_cat_data['updated_by'],
            )
        );

        $product_cat_id = $wpdb->insert_id;

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'product-exception', $e->getMessage() );
    }

    return $product_cat_id;

}

/**
 * Update product data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_product_cat( $data, $id ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );
        $product_cat_data = erp_acct_get_formatted_product_cat_data( $data );

        $wpdb->update(
            $wpdb->prefix . 'erp_acct_product_categories',
            array(
				'name'       => $product_cat_data['name'],
				'parent'     => $product_cat_data['parent'],
				'created_at' => $product_cat_data['created_at'],
				'created_by' => $product_cat_data['created_by'],
				'updated_at' => $product_cat_data['updated_at'],
				'updated_by' => $product_cat_data['updated_by'],
            ),
            array(
				'id' => $id,
            )
        );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'product-exception', $e->getMessage() );
    }

    return $id;

}

/**
 * Get formatted product data
 *
 * @param $data
 * @param $voucher_no
 * @return mixed
 */
function erp_acct_get_formatted_product_cat_data( $data ) {

    $product_cat_data['name']       = isset( $data['name'] ) ? $data['name'] : '';
    $product_cat_data['parent']     = isset( $data['parent'] ) ? $data['parent'] : 0;
    $product_cat_data['created_at'] = isset( $data['created_at'] ) ? $data['created_at'] : '';
    $product_cat_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $product_cat_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $product_cat_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $product_cat_data;
}

/**
 * Delete an product
 *
 * @param $product_cat_no
 *
 * @return void
 */
function erp_acct_delete_product_cat( $product_cat_id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_product_categories', array( 'id' => $product_cat_id ) );
}

