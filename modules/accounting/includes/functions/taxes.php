<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all taxes
 *
 * @return mixed
 */

function erp_acct_get_all_taxes( $args = [] ) {
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
        $sql .= "FROM {$wpdb->prefix}erp_acct_tax ORDER BY {$args['orderby']} {$args['order']} {$limit}";
    
        if ( $args['count'] ) {
            return $wpdb->get_var($sql);
        }
    
        return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get an single tax
 *
 * @param $tax_no
 *
 * @return mixed
 */

function erp_acct_get_tax( $tax_no ) {
    global $wpdb;

    $sql = "Select

    tax.name,
    tax.tax_number,
    tax.created_at,
    tax.created_by,
    tax.updated_at,
    tax.updated_by,
    
    tax_item.tax_id,
    tax_item.component_name,
    tax_item.agency_name
    
    from {$wpdb->prefix}erp_acct_tax AS tax
    Left JOIN {$wpdb->prefix}erp_acct_tax_items AS tax_item ON tax.id = tax_item.tax_id
    WHERE tax.id = {$tax_no} LIMIT 1";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
}

/**
 * Insert tax data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_tax( $data ) {
    global $wpdb;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->insert( $wpdb->prefix . 'erp_acct_tax', array(
        'name'            => $tax_data['name'],
        'tax_number'      => $tax_data['tax_number'],
    ) );

    $tax_id = $wpdb->insert_id;

    $items = $data['components'];

    foreach( $items as $key => $item ) {
        $wpdb->insert( $wpdb->prefix . 'erp_acct_tax_items', array(
            'tax_id'         => $tax_id,
            'component_name' => $item['component_name'],
            'agency_name'    => $item['agency_name'],
            'tax_percent'    => $item['tax_percent']
        ) );
    }

    return $tax_id;

}

/**
 * Update tax data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_tax( $data, $id ) {
    global $wpdb;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->update( $wpdb->prefix . 'erp_acct_tax', array(
        'name'            => $tax_data['name'],
    ), array(
        'tax_number'      => $tax_data['tax_number']
    ) );

    $items = $data['components'];

    foreach( $items as $key => $item ) {
        $wpdb->update( $wpdb->prefix . 'erp_acct_tax_items', array(
            'component_name' => $item['component_name'],
            'agency_name'    => $item['agency_name'],
            'tax_percent'    => $item['tax_percent']
        ), array(
            'tax_id'         => $id
        ) );
    }

    return $id;

}

/**
 * Get formatted tax data
 *
 * @param $data
 * @param $voucher_no
 * @return mixed
 */
function erp_acct_get_formatted_tax_data( $data ) {

    $tax_data['name'] = isset( $data['name'] ) ? $data['name'] : 1;
    $tax_data['tax_number'] = isset( $data['customer_id'] ) ? $data['customer_id'] : 1;
    $tax_data['tax_id'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $tax_data['component_name']   = isset( $data['component_name'] ) ? $data['component_name'] : '';
    $tax_data['agency_name']   = isset( $data['agency_name'] ) ? $data['agency_name'] : '';
    $tax_data['components']   = isset( $data['components'] ) ? $data['components'] : '';

    return $tax_data;
}

/**
 * Delete an tax
 *
 * @param $tax_no
 *
 * @return void
 */

function erp_acct_delete_tax( $tax_no ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_tax', array( 'tax_number' => $tax_no ) );
}





