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

    $charts = $wpdb->get_results("SELECT id, name AS label FROM {$wpdb->prefix}erp_acct_chart_of_accounts", ARRAY_A);

    return $charts;
}



/**
 * Ledger count
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
 * Get ledger categories
 */

function erp_acct_get_ledger_categories() {
    global $wpdb;

    return $wpdb->get_results("SELECT id, name AS label, parent_id, system FROM {$wpdb->prefix}erp_acct_ledger_categories");
}

/**
 * Create ledger category
 */
function erp_acct_create_ledger_category( $args ) {
    global $wpdb;

    $exist = $wpdb->get_var("SELECT name FROM {$wpdb->prefix}erp_acct_ledger_categories WHERE name = '{$args['name']}'");

    if ( ! $exist ) {
        $wpdb->insert("{$wpdb->prefix}erp_acct_ledger_categories", [
            'name'      => $args['name'],
            'parent_id' => ! empty($args['parent']) ? $args['parent'] : null
        ]);

        return $wpdb->insert_id;
    }

    return false;
}


/**
 * Update ledger category
 */
function erp_acct_update_ledger_category($args) {
    global $wpdb;

    $exist = $wpdb->get_var("SELECT name FROM {$wpdb->prefix}erp_acct_ledger_categories WHERE name = '{$args['name']}' AND id <> {$args['id']}");

    if ( ! $exist ) {
        return $wpdb->update( 
            "{$wpdb->prefix}erp_acct_ledger_categories",
            [
                'name' => $args['name'],
                'parent_id' => ! empty($args['parent']) ? $args['parent'] : null
            ],
            [ 'id' => $args['id'] ],
            [ '%s', '%d'], 
            [ '%d' ]
        );
    }

    return false;
}

/**
 * Remove ledger category
 */
function erp_acct_delete_ledger_category( $id ) {
    global $wpdb;

    $table = "{$wpdb->prefix}erp_acct_ledger_categories";

    $parent_id = $wpdb->get_var("SELECT parent_id FROM {$table} WHERE id = {$id}");

    $wpdb->update( 
        $table, 
        [ 'parent_id' => $parent_id ],
        [ 'parent_id' => $id ],
        [ '%s' ],
        [ '%d']
    );

    return $wpdb->delete( $table, ['id' => $id] );

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
