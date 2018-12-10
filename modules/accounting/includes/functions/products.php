<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get all products
 *
 * @return mixed
 */

function erp_acct_get_all_products() {
	global $wpdb;

	$row = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_products", ARRAY_A );

	return $row;
}

/**
 * Get an single product
 *
 * @param $product_no
 *
 * @return mixed
 */

function erp_acct_get_product( $product_id ) {
	global $wpdb;

	$row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_products WHERE id = {$product_id} GROUP BY category_id", ARRAY_A );

	return $row;
}

/**
 * Insert product data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_product( $data ) {
	global $wpdb;

    $created_by = get_current_user_id();
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;

	try {
		$wpdb->query( 'START TRANSACTION' );
		$product_data = erp_acct_get_formatted_product_data( $data );

		$wpdb->insert( $wpdb->prefix . 'erp_acct_products', array(
			'name'            => $product_data['name'],
			'product_type_id' => $product_data['product_type_id'],
			'category_id'     => $product_data['category_id'],
			'vendor'          => $product_data['vendor'],
			'cost_price'      => $product_data['cost_price'],
			'sale_price'      => $product_data['sale_price'],
			'created_at'      => $product_data['created_at'],
			'created_by'      => $product_data['created_by'],
			'updated_at'      => $product_data['updated_at'],
			'updated_by'      => $product_data['updated_by'],
		) );

		$product_id = $wpdb->insert_id;

		$wpdb->query( 'COMMIT' );

	} catch (Exception $e) {
		$wpdb->query( 'ROLLBACK' );
		return new WP_error( 'product-exception', $e->getMessage() );
	}

	return $product_id;

}

/**
 * Update product data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_product( $data, $id ) {
	global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

	try {
		$wpdb->query( 'START TRANSACTION' );
		$product_data = erp_acct_get_formatted_product_data( $data );

		$wpdb->insert( $wpdb->prefix . 'erp_acct_products', array(
			'name'            => $product_data['name'],
			'product_type_id' => $product_data['product_type_id'],
			'category_id'     => $product_data['category_id'],
			'vendor'          => $product_data['vendor'],
			'cost_price'      => $product_data['cost_price'],
			'sale_price'      => $product_data['sale_price'],
			'created_at'      => $product_data['updated_at'],
			'created_by'      => $product_data['updated_by'],
			'updated_at'      => $product_data['updated_at'],
			'updated_by'      => $product_data['updated_by'],
		), array(
			'id' => $id,
		) );

		$wpdb->query( 'COMMIT' );

	} catch (Exception $e) {
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
function erp_acct_get_formatted_product_data( $data ) {

	$product_data['name'] = isset( $data['name'] ) ? $data['name'] : 1;
	$product_data['product_type_id'] = isset( $data['product_type_id'] ) ? $data['product_type_id'] : 1;
	$product_data['category_id'] = isset( $data['category_id'] ) ? $data['category_id'] : 0;
	$product_data['vendor']   = isset( $data['vendor'] ) ? $data['vendor'] : '';
	$product_data['cost_price']   = isset( $data['cost_price'] ) ? $data['cost_price'] : '';
	$product_data['sale_price']   = isset( $data['sale_price'] ) ? $data['sale_price'] : '';
    $product_data['created_at'] = isset( $data['created_at'] ) ? $data['created_at'] : '';
    $product_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $product_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $product_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

	return $product_data;
}

/**
 * Delete an product
 *
 * @param $product_no
 *
 * @return int
 */

function erp_acct_delete_product( $product_id ) {
	global $wpdb;

	$wpdb->delete( $wpdb->prefix . 'erp_acct_products', array( 'id' => $product_id ) );

    return $product_id;
}





