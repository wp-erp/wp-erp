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

    $last_changed = erp_cache_get_last_changed( 'accounting', 'tax_cats', 'erp-accounting' );
    $cache_key    = 'erp-get-tax-cats-' . md5( serialize( $args ) ) . ": $last_changed";
    $tax_cats     = wp_cache_get( $cache_key, 'erp-accounting' );

    $cache_key_count = 'erp-get-tax-cats-count-' . md5( serialize( $args ) ) . ": $last_changed";
    $tax_cats_count  = wp_cache_get( $cache_key_count, 'erp-accounting' );

    if ( false === $tax_cats ) {
        $limit = '';

        if ( -1 !== $args['number'] ) {
            $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['number'], $args['offset'] );
        }

        $sql  = 'SELECT';
        $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
        $sql .= $wpdb->prepare( "FROM {$wpdb->prefix}erp_acct_tax_categories ORDER BY %s %s %s", $args['orderby'], $args['order'], $limit );

        if ( $args['count'] ) {
            $tax_cats_count = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key_count, $tax_cats_count, 'erp-accounting' );
        } else {
            $tax_cats = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key, $tax_cats, 'erp-accounting' );
        }
    }

    if ( $args['count'] ) {
        return $tax_cats_count;
    }

    return $tax_cats;
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
 *
 * @return int
 */
function erp_acct_insert_tax_cat( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_tax_categories',
        [
            'name'        => $tax_data['name'],
            'description' => $tax_data['description'],
            'created_at'  => $tax_data['created_at'],
            'created_by'  => $tax_data['created_by'],
            'updated_at'  => $tax_data['updated_at'],
            'updated_by'  => $tax_data['updated_by'],
        ]
    );

    $tax_id = $wpdb->insert_id;

    erp_acct_purge_cache( ['list' => 'tax_cats'] );

    return $tax_id;
}

/**
 * Update tax category
 *
 * @param $data
 *
 * @return int
 */
function erp_acct_update_tax_cat( $data, $id ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_tax_categories',
        [
            'name'        => $tax_data['name'],
            'description' => $tax_data['description'],
            'updated_at'  => $tax_data['updated_at'],
            'updated_by'  => $tax_data['updated_by'],
        ],
        [
            'id' => $id,
        ]
    );

    erp_acct_purge_cache( ['list' => 'tax_cats'] );

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

    $wpdb->delete( $wpdb->prefix . 'erp_acct_tax_categories', [ 'id' => $id ] );

    erp_acct_purge_cache( ['list' => 'tax_cats'] );

    return $id;
}
