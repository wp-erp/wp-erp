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

function erp_acct_get_ledger_categories($chart_id) {
    global $wpdb;

    $sql = "SELECT id, name AS label, chart_id, parent_id, system FROM {$wpdb->prefix}erp_acct_ledger_categories WHERE chart_id = {$chart_id}";

    return $wpdb->get_results($sql, ARRAY_A);
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

/**
 * Get ledger transaction count
 *
 * @param $ledger_id
 * @return mixed
 */
function erp_acct_get_ledger_trn_count( $ledger_id ) {
    global $wpdb;

    $sql = "SELECT
        COUNT(*) as count
        FROM {$wpdb->prefix}erp_acct_ledger_details 
        WHERE ledger_id = {$ledger_id}";

    $ledger = $wpdb->get_row( $sql, ARRAY_A );

    return $ledger['count'];
}

/**
 * Get ledger balance
 *
 * @param $ledger_id
 * @return mixed
 */
function erp_acct_get_ledger_balance( $ledger_id ) {
    global $wpdb;

    $sql = "SELECT
        ledger.id,
        ledger.name,
        SUM(ld.debit - ld.credit) as balance

        FROM {$wpdb->prefix}erp_acct_ledgers AS ledger 
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details as ld ON ledger.id = ld.ledger_id 
        WHERE ledger.id = {$ledger_id}";

    $ledger = $wpdb->get_row($sql, ARRAY_A);

    return $ledger['balance'];
}


/**============
 * Ledger CRUD
 ===============*/

function erp_acct_get_ledger( $id ) {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE id = %d", $id);

    return $wpdb->get_row( $sql );
}

function erp_acct_insert_ledger( $item ) {
    global $wpdb;

    $wpdb->insert( "{$wpdb->prefix}erp_acct_ledgers", [
        'chart_id'    => $item['chart_id'],
        'category_id' => $item['category_id'],
        'name'        => $item['name'],
        'slug'        => slugify($item['name']),
        'code'        => $item['code']
     ] );

     return erp_acct_get_ledger( $wpdb->insert_id );
}

function erp_acct_update_ledger( $item, $id ) {
    global $wpdb;

    $wpdb->update( "{$wpdb->prefix}erp_acct_ledgers", [
        'chart_id'      => $item['chart_id'],
        'category_id'   => $item['category_id'],
        'name'          => $item['name'],
        'slug'        => slugify($item['name']),
        'code'          => $item['code']
        ], [ 'id' => $id ]
    );

    return erp_acct_get_ledger( $id );
}
