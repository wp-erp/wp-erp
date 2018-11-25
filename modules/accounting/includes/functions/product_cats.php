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

	$row = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_product_categories", ARRAY_A );

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

	$row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_product_categories WHERE id = {$product_cat_id} GROUP BY parent", ARRAY_A );

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

	$created_by = get_current_user_id();

	try {
		$wpdb->query( 'START TRANSACTION' );
		$product_cat_data = erp_acct_get_formatted_product_cat_data( $data );

		$wpdb->insert( $wpdb->prefix . 'erp_acct_product_categories', array(
			'name'            => $product_cat_data['name'],
			'parent'          => $product_cat_data['parent'],
			'created_at'      => '',
			'created_by'      => $created_by,
			'updated_at'      => '',
			'updated_by'      => '',
		) );

		$product_cat_id = $wpdb->insert_id;

		$wpdb->query( 'COMMIT' );

	} catch (Exception $e) {
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

	$created_by = get_current_user_id();

	try {
		$wpdb->query( 'START TRANSACTION' );
		$product_cat_data = erp_acct_get_formatted_product_cat_data( $data );

		$wpdb->insert( $wpdb->prefix . 'erp_acct_product_categories', array(
			'name'            => $product_cat_data['name'],
			'parent'          => $product_cat_data['parent'],
			'created_at'      => '',
			'created_by'      => $created_by,
			'updated_at'      => '',
			'updated_by'      => '',
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
function erp_acct_get_formatted_product_cat_data( $data ) {

	$product_cat_data['name'] = isset( $data['name'] ) ? $data['name'] : 1;
	$product_cat_data['parent'] = isset( $data['parent'] ) ? $data['parent'] : 1;

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





