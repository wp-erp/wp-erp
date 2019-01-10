<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all chart of accounts
 *
 * @return array
 */
function erp_acct_get_all_charts() {
    global $wpdb;

    $charts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}erp_acct_chart_of_accounts", ARRAY_A);

    return $charts;
}



/**
 * Fetch all chart from database
 *
 * @return array
 */
function erp_acct_get_chart_count() {
    global $wpdb;

    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'erp_acct_ledger' );
}

/**
 * Fetch a single chart from database
 *
 * @param int $id
 *
 * @return array
 */
function erp_acct_get_chart( $id = 0 ) {

}

/**
 * Insert a new chart
 *
 * @param array $args
 */
function erp_acct_insert_chart( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'          => null,
        'name'        => '',
        'description' => '',
        'type_id'     => '',
        'active'      => 1,
        'parent'      => 0,
        'system'      => 0,
        'created_by'  => get_current_user_id()
    );

    $args       = wp_parse_args( $args, $defaults );

    $table_name = $wpdb->prefix . 'erp_acct_ledger';

    // some basic validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No Name provided.', 'erp' ) );
    }

    // remove row id to determine if new or update
    $row_id = (int) $args['id'];
    unset( $args['id'] );

    if ( ! $row_id ) {
        if ( ! erp_acct_create_account() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }

        $ledger = WeDevs\ERP\Accounting\INCLUDES\Model\Ledger::create( $args );

        if ( $ledger->id ) {
            return $ledger->id;
        }

    } else {

        if ( ! erp_acct_edit_account() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }

        // don't allow to change account type
        unset( $args['type_id'] );

        // do update method here
        if ( $wpdb->update( $table_name, $args, array( 'id' => $row_id ) ) ) {

            return $row_id;
        }
    }

    return false;
}



/**
 * Get all chart types by class id
 *
 * @param int $class_id
 *
 * @return array
 */
function erp_acct_get_chart_types_by_class_id( $class_id ) {
    global $wpdb;

    $cache_key = 'erp-ac-chart-type-by-class-id';
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'erp_acct_chart_types WHERE class_id = ' . $class_id . ' ORDER BY class_id ASC' );

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}





/**
 * Account code generator if its empty
 *
 * @since  1.1.5
 *
 * @return int
 */
function erp_acct_accounting_code_generator() {
    $ledger = WeDevs\ERP\Accounting\INCLUDES\Model\Ledger::select( 'code' )->get()->toArray();
    $ledger = wp_list_pluck( $ledger, 'code' );
    $code   = random_int( 0, 999 );

    if ( in_array( $code, $ledger ) ) {
        erp_acct_accounting_code_generator();
    }

    return $code;
}
