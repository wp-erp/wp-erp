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
function erp_acct_update_ledger_category( $args ) {
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
 * @param $chart_id
 * @return array|object|null
 */
function erp_acct_get_ledgers_by_chart_id( $chart_id ) {
    global $wpdb;

    $charts = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}erp_acct_ledgers WHERE chart_id = {$chart_id} ", ARRAY_A);

    return $charts;
}

