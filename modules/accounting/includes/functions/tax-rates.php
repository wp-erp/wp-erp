<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all taxes
 *
 * @return mixed
 */

function erp_acct_get_all_tax_rates( $args = [] ) {
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
        $sql .= "FROM {$wpdb->prefix}erp_acct_taxes ORDER BY {$args['orderby']} {$args['order']} {$limit}";
    
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

function erp_acct_get_tax_rate( $tax_no ) {
    global $wpdb;

    $sql = "SELECT

    tax.tax_rate_name,
    tax.tax_number,
    tax.default,
    tax.tax_rate,
    tax.created_at,
    tax.created_by,
    tax.updated_at,
    tax.updated_by,
    
    tax_item.tax_id,
    tax_item.agency_id
    
    FROM {$wpdb->prefix}erp_acct_taxes AS tax
    LEFT JOIN {$wpdb->prefix}erp_acct_tax_items AS tax_item ON tax.id = tax_item.tax_id
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
function erp_acct_insert_tax_rate( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();
    $data['created_at'] = date("Y-m-d H:i:s");
    $data['created_by'] = $created_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->insert($wpdb->prefix . 'erp_acct_taxes', array(
        'tax_rate_name' => $tax_data['tax_rate_name'],
        'tax_number' => $tax_data['tax_number'],
        'default' => $tax_data['default'],
        'tax_rate' => $tax_data['tax_rate'],
        'created_at' => $tax_data['created_at'],
        'created_by' => $tax_data['created_by'],
        'updated_at' => $tax_data['updated_at'],
        'updated_by' => $tax_data['updated_by'],
    ));

    $tax_id = $wpdb->insert_id;

    $items = $data['tax_components'];

    foreach ($items as $key => $item) {
        $wpdb->insert($wpdb->prefix . 'erp_acct_tax_items', array(
            'tax_id'     => $tax_id,
            'agency_id'  => $item['agency_id'],
            'tax_rate'   => $item['tax_rate'],
            'created_at' => $tax_data['created_at'],
            'created_by' => $tax_data['created_by'],
            'updated_at' => $tax_data['updated_at'],
            'updated_by' => $tax_data['updated_by'],
        ));
    }

    $wpdb->insert($wpdb->prefix . 'erp_acct_tax_sales_tax_categories', array(
        'tax_id' => $tax_id,
        'sales_tax_category_id' => $tax_data['tax_category_id'],
        'created_at' => $tax_data['created_at'],
        'created_by' => $tax_data['created_by'],
        'updated_at' => $tax_data['updated_at'],
        'updated_by' => $tax_data['updated_by'],
    ));

    return $tax_id;

}

/**
 * Update tax data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_tax_rate( $data, $id ) {
    global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->update($wpdb->prefix . 'erp_acct_taxes', array(
        'tax_rate_name' => $tax_data['tax_rate_name'],
        'tax_number' => $tax_data['tax_number'],
        'default' => $tax_data['default'],
        'tax_rate' => $tax_data['tax_rate'],
        'created_at' => $tax_data['created_at'],
        'created_by' => $tax_data['created_by'],
        'updated_at' => $tax_data['updated_at'],
        'updated_by' => $tax_data['updated_by'],
    ), array(
        'id' => $id
    ));

    $items = $data['tax_components'];

    foreach ($items as $key => $item) {
        $wpdb->update($wpdb->prefix . 'erp_acct_tax_items', array(
            'agency_id'  => $item['agency_id'],
            'tax_rate'   => $item['tax_rate'],
            'created_at' => $tax_data['created_at'],
            'created_by' => $tax_data['created_by'],
            'updated_at' => $tax_data['updated_at'],
            'updated_by' => $tax_data['updated_by'],
        ), array(
            'tax_id' => $id
        ));
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_tax_sales_tax_categories', array(
        'sales_tax_category_id' => $tax_data['tax_category_id'],
        'created_at' => $tax_data['created_at'],
        'created_by' => $tax_data['created_by'],
        'updated_at' => $tax_data['updated_at'],
        'updated_by' => $tax_data['updated_by'],
    ), array(
        'tax_id' => $id
    ));

    return $id;

}

/**
 * Delete an tax
 *
 * @param $tax_no
 *
 * @return int
 */

function erp_acct_delete_tax_rate( $tax_no ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_taxes', array( 'tax_number' => $tax_no ) );

    return $tax_no;
}

/**
 * Get formatted tax data
 *
 * @param $data
 * @param $voucher_no
 * @return mixed
 */
function erp_acct_get_formatted_tax_data( $data ) {
    $tax_data = [];

    $tax_data['tax_rate_name'] = isset($data['tax_rate_name']) ? $data['tax_rate_name'] : '';
    $tax_data['tax_number'] = isset($data['tax_number']) ? $data['tax_number'] : '';
    $tax_data['default'] = isset($data['default']) ? $data['default'] : 0;
    $tax_data['tax_rate'] = isset($data['total_rate']) ? $data['total_rate'] : 0;
    $tax_data['tax_id'] = isset($data['tax_id']) ? $data['tax_id'] : 0;
    $tax_data['tax_category_id'] = isset($data['tax_category_id']) ? $data['tax_category_id'] : 0;
    $tax_data['agency_id'] = isset($data['agency_id']) ? $data['agency_id'] : '';
    $tax_data['components'] = isset($data['components']) ? $data['components'] : '';
    $tax_data['created_at'] = date("Y-m-d");
    $tax_data['created_by'] = isset($data['created_by']) ? $data['created_by'] : '';
    $tax_data['updated_at'] = isset($data['updated_at']) ? $data['updated_at'] : '';
    $tax_data['updated_by'] = isset($data['updated_by']) ? $data['updated_by'] : '';
    $tax_data['cat_name'] = isset($data['cat_name']) ? $data['cat_name'] : '';
    $tax_data['description'] = isset($data['description']) ? $data['description'] : '';

    return $tax_data;
}
