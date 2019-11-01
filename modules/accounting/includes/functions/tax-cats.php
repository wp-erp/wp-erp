<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all tax categories
 *
 * @return mixed
 */
function erp_acct_get_all_tax_cats( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
        's'       => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    if ( -1 !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql  = 'SELECT';
    $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
    $sql .= "FROM {$wpdb->prefix}erp_acct_tax_categories ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql );
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get an single tax category
 *
 * @param $tax_no
 *
 * @return mixed
 */

function erp_acct_get_tax_cat( $tax_no ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_acct_tax_categories WHERE id = %d LIMIT 1", $tax_no ), ARRAY_A );

    return $row;
}

/**
 * Insert tax category data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_tax_cat( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_tax_categories',
        array(
			'name'        => $tax_data['name'],
			'description' => $tax_data['description'],
			'created_at'  => $tax_data['created_at'],
			'created_by'  => $tax_data['created_by'],
			'updated_at'  => $tax_data['updated_at'],
			'updated_by'  => $tax_data['updated_by'],
        )
    );

    $tax_id = $wpdb->insert_id;

    return $tax_id;

}

/**
 * Update tax category
 *
 * @param $data
 * @return int
 */
function erp_acct_update_tax_cat( $data, $id ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_tax_categories',
        array(
			'name'        => $tax_data['name'],
			'description' => $tax_data['description'],
			'created_at'  => $tax_data['created_at'],
			'created_by'  => $tax_data['created_by'],
			'updated_at'  => $tax_data['updated_at'],
			'updated_by'  => $tax_data['updated_by'],
        ),
        array(
			'id' => $id,
        )
    );

    return $id;

}

/**
 * Delete a tax category
 *
 * @param $tax_no
 *
 * @return int
 */
function erp_acct_delete_tax_cat( $id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_tax_categories', array( 'id' => $id ) );

    return $id;
}

