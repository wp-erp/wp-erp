<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all tax rate names
 *
 * @return mixed
 */
function erp_acct_get_all_tax_rate_names( $args = [] ) {
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

    $last_changed = erp_cache_get_last_changed( 'accounting', 'tax_rates_names', 'erp-accounting' );
    $cache_key    = 'erp-get-tax-rates-name-' . md5( serialize( $args ) ) . ": $last_changed";
    $tax_rates    = wp_cache_get( $cache_key, 'erp-accounting' );

    $cache_key_count = 'erp-get-tax-rates-name-count-' . md5( serialize( $args ) ) . " : $last_changed";
    $tax_rates_count  = wp_cache_get( $cache_key_count, 'erp-accounting' );

    if ( false === $tax_rates ) {
        $limit = '';

        if ( -1 !== $args['number'] ) {
            $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['number'], $args['offset'] );
        }

        $sql  = 'SELECT';
        $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
        $sql .= $wpdb->prepare( "FROM {$wpdb->prefix}erp_acct_taxes ORDER BY %s %s %s", $args['orderby'], $args['order'], $limit);

        if ( $args['count'] ) {
            $tax_rates_count = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key_count, $tax_rates_count, 'erp-accounting' );
        } else {
            $tax_rates = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key, $tax_rates, 'erp-accounting' );
        }
    }

    if ( $args['count'] ) {
        return $tax_rates_count;
    }

    return $tax_rates;
}

/**
 * Get an single tax rate name
 *
 * @param $tax_no
 *
 * @return mixed
 */
function erp_acct_get_tax_rate_name( $tax_no ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_acct_taxes WHERE id = %d LIMIT 1", $tax_no ), ARRAY_A );

    return $row;
}

/**
 * Insert tax rate name
 *
 * @param $data
 *
 * @return int
 */
function erp_acct_insert_tax_rate_name( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;

    if ( ! empty( $data['default'] ) ) {
        $wpdb->query( "UPDATE {$wpdb->prefix}erp_acct_taxes SET `default` = 0" );
    }

    $tax_data = erp_acct_get_formatted_tax_rate_name_data( $data );

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_taxes',
        [
            'tax_rate_name' => $tax_data['tax_rate_name'],
            'tax_number'    => $tax_data['tax_number'],
            'default'       => $tax_data['default'],
            'created_at'    => $tax_data['created_at'],
            'created_by'    => $tax_data['created_by'],
            'updated_at'    => $tax_data['updated_at'],
            'updated_by'    => $tax_data['updated_by'],
        ]
    );

    erp_acct_purge_cache( ['list' => 'tax_rates_names'] );

    return $wpdb->insert_id;
}

/**
 * Update tax rate name
 *
 * @param $data
 *
 * @return int
 */
function erp_acct_update_tax_rate_name( $data, $id ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    $tax_data = erp_acct_get_formatted_tax_rate_name_data( $data );

    if ( ! empty( $tax_data['default'] ) ) {
        $wpdb->query( "UPDATE {$wpdb->prefix}erp_acct_taxes SET `default` = 0" );
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_taxes',
        [
            'tax_rate_name' => $tax_data['tax_rate_name'],
            'tax_number'    => $tax_data['tax_number'],
            'default'       => $tax_data['default'],
            'updated_at'    => $tax_data['updated_at'],
            'updated_by'    => $tax_data['updated_by'],
        ],
        [
            'id' => $id,
        ]
    );

    erp_acct_purge_cache( ['list' => 'tax_rates_names'] );

    return $id;
}

/**
 * Delete an tax rate name
 *
 * @param $tax_no
 *
 * @return int
 */
function erp_acct_delete_tax_rate_name( $id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_taxes', [ 'id' => $id ] );

    erp_acct_purge_cache( ['list' => 'tax_rates_names'] );

    return $id;
}

/**
 * Get formatted tax rate name data
 *
 * @param $data
 *
 * @return mixed
 */
function erp_acct_get_formatted_tax_rate_name_data( $data ) {
    $tax_data = [];

    $tax_data['tax_rate_name'] = isset( $data['tax_rate_name'] ) ? $data['tax_rate_name'] : '';
    $tax_data['tax_number']    = isset( $data['tax_number'] ) ? $data['tax_number'] : '';
    $tax_data['default']       = isset( $data['default'] ) ? $data['default'] : '';
    $tax_data['created_at']    = gmdate( 'Y-m-d' );
    $tax_data['created_by']    = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $tax_data['updated_at']    = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
    $tax_data['updated_by']    = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $tax_data;
}
