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

    $cache_key = 'erp-get-charts';
    $charts    = wp_cache_get( $cache_key, 'erp-accounting' );

    if ( false === $charts ) {
        $charts = $wpdb->get_results( "SELECT id, name AS label FROM {$wpdb->prefix}erp_acct_chart_of_accounts", ARRAY_A );

        wp_cache_set( $cache_key, $charts );
    }

    return $charts;
}

/**
 * Get Ledger name by id
 *
 * @param $ledger_id
 *
 * @return mixed
 */
function erp_acct_get_ledger_name_by_id( $ledger_id ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT id, name  FROM {$wpdb->prefix}erp_acct_ledgers WHERE id = %d", $ledger_id ) );

    return $row->name;
}

/**
 * Get ledger categories
 */
function erp_acct_get_ledger_categories( $chart_id ) {
    global $wpdb;

    $cache_key         = 'erp-get-ledger-categories';
    $ledger_categories = wp_cache_get( $cache_key, 'erp-accounting' );

    if ( false === $ledger_categories ) {
        $ledger_categories = $wpdb->get_results( $wpdb->prepare( "SELECT id, name AS label, chart_id, parent_id, system FROM {$wpdb->prefix}erp_acct_ledger_categories WHERE chart_id = %d", $chart_id ), ARRAY_A );

        wp_cache_set( $cache_key, $ledger_categories );
    }

    return $ledger_categories;
}

/**
 * Create ledger category
 */
function erp_acct_create_ledger_category( $args ) {
    global $wpdb;

    $exist = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_ledger_categories WHERE name = %s", $args['name'] ) );

    if ( ! $exist ) {
        $wpdb->insert(
            "{$wpdb->prefix}erp_acct_ledger_categories",
            [
                'name'      => $args['name'],
                'parent_id' => ! empty( $args['parent'] ) ? $args['parent'] : null,
            ]
        );

        return $wpdb->insert_id;
    }

    erp_acct_purge_cache( ['key' => 'erp-get-ledger-categories'] );

    return false;
}

/**
 * Update ledger category
 */
function erp_acct_update_ledger_category( $args ) {
    global $wpdb;

    $exist = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_ledger_categories WHERE name = %s AND id <> %d", $args['name'], $args['id'] ) );

    if ( ! $exist ) {
        return $wpdb->update(
            "{$wpdb->prefix}erp_acct_ledger_categories",
            [
                'name'      => $args['name'],
                'parent_id' => ! empty( $args['parent'] ) ? $args['parent'] : null,
            ],
            [ 'id' => $args['id'] ],
            [ '%s', '%d' ],
            [ '%d' ]
        );
    }

    erp_acct_purge_cache( ['key' => 'erp-get-ledger-categories'] );

    return false;
}

/**
 * Remove ledger category
 */
function erp_acct_delete_ledger_category( $id ) {
    global $wpdb;

    $parent_id = $wpdb->get_var( $wpdb->prepare( "SELECT parent_id FROM {$wpdb->prefix}erp_acct_ledger_categories WHERE id = %d", $id ) );

    $table = "{$wpdb->prefix}erp_acct_ledger_categories";

    $wpdb->update(
        $table,
        [ 'parent_id' => $parent_id ],
        [ 'parent_id' => $id ],
        [ '%s' ],
        [ '%d' ]
    );

    erp_acct_purge_cache( ['key' => 'erp-get-ledger-categories'] );

    return $wpdb->delete( $table, [ 'id' => $id ] );
}

/**
 * @param $chart_id
 *
 * @return array|object|null
 */
function erp_acct_get_ledgers_by_chart_id( $chart_id ) {
    global $wpdb;

    $ledgers = $wpdb->get_results( $wpdb->prepare( "SELECT id, name FROM {$wpdb->prefix}erp_acct_ledgers WHERE chart_id = %d AND unused IS NULL", $chart_id ), ARRAY_A );

    for ( $i = 0; $i < count( $ledgers ); $i++ ) {
        $ledgers[ $i ]['balance'] = erp_acct_get_ledger_balance( $ledgers[ $i ]['id'] );
    }

    return $ledgers;
}

/**
 * Get ledger transaction count
 *
 * @param $ledger_id
 *
 * @return mixed
 */
function erp_acct_get_ledger_trn_count( $ledger_id ) {
    global $wpdb;

    $ledger = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(*) as count FROM {$wpdb->prefix}erp_acct_ledger_details WHERE ledger_id = %d", $ledger_id ), ARRAY_A );

    return $ledger['count'];
}

/**
 * Get ledger balance
 *
 * @param $ledger_id
 *
 * @return mixed
 */
function erp_acct_get_ledger_balance( $ledger_id ) {
    global $wpdb;

    $ledger = $wpdb->get_row( $wpdb->prepare( "SELECT ledger.id, ledger.name, SUM(ld.debit - ld.credit) as balance FROM {$wpdb->prefix}erp_acct_ledgers AS ledger LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details as ld ON ledger.id = ld.ledger_id WHERE ledger.id = %d", $ledger_id ), ARRAY_A );

    return $ledger['balance'];
}

/**============
 * Ledger CRUD
 * ===============*/

/**
 * Get a ledger by id
 *
 * @param $id
 *
 * @return array|object|void|null
 */
function erp_acct_get_ledger( $id ) {
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE id = %d", $id ) );
}

/**
 * Insert a ledger
 *
 * @param $item
 *
 * @return array|object|void|null
 */
function erp_acct_insert_ledger( $item ) {
    global $wpdb;

    $wpdb->insert(
        "{$wpdb->prefix}erp_acct_ledgers",
        [
            'chart_id'    => $item['chart_id'],
            'category_id' => $item['category_id'],
            'name'        => $item['name'],
            'slug'        => slugify( $item['name'] ),
            'code'        => $item['code'],
        ]
    );

    return erp_acct_get_ledger( $wpdb->insert_id );
}

/**
 * Update a ledger
 *
 * @param $item
 * @param $id
 *
 * @return array|object|void|null
 */
function erp_acct_update_ledger( $item, $id ) {
    global $wpdb;

    $wpdb->update(
        "{$wpdb->prefix}erp_acct_ledgers",
        [
            'chart_id'    => $item['chart_id'],
            'category_id' => $item['category_id'],
            'name'        => $item['name'],
            'slug'        => slugify( $item['name'] ),
            'code'        => $item['code'],
        ],
        [ 'id' => $id ]
    );

    return erp_acct_get_ledger( $id );
}

/**
 * Get ledger opening balance data by financial year id
 *
 * @param int $id
 * @param int $chart_id ( optional )
 *
 * @return string
 */
function erp_acct_ledger_opening_balance_by_fn_year_id( $id ) {
    global $wpdb;

    return $wpdb->get_results( $wpdb->prepare( "SELECT ledger.id, ledger.name, SUM(opb.debit - opb.credit) AS balance
    FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
    LEFT JOIN {$wpdb->prefix}erp_acct_opening_balances AS opb ON ledger.id = opb.ledger_id
    WHERE opb.financial_year_id = %d AND opb.type = 'ledger' GROUP BY opb.ledger_id", $id ), ARRAY_A );
}

/**
 * =====================
 * =========================
 * =============================
 */
/**
 * Get ledger with balances
 *
 * @return array
 */
function erp_acct_get_ledgers_with_balances() {
    global $wpdb;

    $today = gmdate( 'Y-m-d' );

    $ledgers = $wpdb->get_results(
        "SELECT ledger.id, ledger.chart_id, ledger.category_id, ledger.name, ledger.slug, ledger.code, ledger.system, chart_of_account.name as account_name FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
        LEFT JOIN {$wpdb->prefix}erp_acct_chart_of_accounts AS chart_of_account ON ledger.chart_id = chart_of_account.id WHERE ledger.unused IS NULL",
        ARRAY_A
    );

    // get closest financial year id and start date
    $closest_fy_date = erp_acct_get_closest_fn_year_date( $today );

    // get opening balance data within that(^) financial year
    $opening_balance = erp_acct_ledger_opening_balance_by_fn_year_id( $closest_fy_date['id'] );

    $data = $wpdb->get_results( $wpdb->prepare( "SELECT ledger.id, ledger.name, SUM(ld.debit - ld.credit) as balance
    FROM {$wpdb->prefix}erp_acct_ledgers AS ledger
    LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details as ld ON ledger.id = ld.ledger_id
    AND ld.trn_date BETWEEN %s AND %s GROUP BY ld.ledger_id", $closest_fy_date['start_date'], $today ), ARRAY_A );

    return erp_acct_ledger_balance_with_opening_balance( $ledgers, $data, $opening_balance );
}

/**
 * Ledgers opening balance
 *
 * @param int $id
 *
 * @return void
 */
function erp_acct_ledgers_opening_balance_by_fn_year_id( $id ) {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ledger.id, ledger.name, SUM(opb.debit - opb.credit) AS balance FROM {$wpdb->prefix}erp_acct_ledgers AS ledger LEFT JOIN {$wpdb->prefix}erp_acct_opening_balances AS opb ON ledger.id = opb.ledger_id WHERE opb.financial_year_id = %d opb.type = 'ledger' GROUP BY opb.ledger_id",
            $id
        ),
        ARRAY_A
    );
}

/**
 * Get ledger balance with opening balance for chart of accounts
 *
 * @param array $ledgers
 * @param array $data
 * @param array $opening_balance
 *
 * @return array
 */
function erp_acct_ledger_balance_with_opening_balance( $ledgers, $data, $opening_balance ) {
    $temp_data         = [];
    $ledger_balance    = [];
    $transaction_count = [];

    /*
     * Generate ledger balance and transaction count according to ledger id
     */
    foreach ( $data as $row ) {
        if ( ! isset( $ledger_balance[ $row['id'] ] ) ) {
            $ledger_balance[ $row['id'] ]    = (float) $row['balance'];
            $transaction_count[ $row['id'] ] = 1;
        } else {
            $ledger_balance[ $row['id'] ]    += (float) $row['balance'];
            $transaction_count[ $row['id'] ] += 1;
        }
    }

    foreach ( $opening_balance as $op_balance ) {
        if ( ! isset( $ledger_balance[ $op_balance['id'] ] ) ) {
            $ledger_balance[ $op_balance['id'] ] = 0.00;
        }

        $ledger_balance[ $op_balance['id'] ] += (float) $op_balance['balance'];
    }

    foreach ( $ledgers as $ledger ) {
        $temp_data[] = [
            'id'          => $ledger['id'],
            'chart_id'    => $ledger['chart_id'],
            'category_id' => $ledger['category_id'],
            'name'        => $ledger['name'],
            'slug'        => $ledger['slug'],
            'code'        => $ledger['code'],
            'system'      => $ledger['system'],
            'trn_count'   => isset( $transaction_count[ $ledger['id'] ] ) ? $transaction_count[ $ledger['id'] ] : 0,
            'balance'     => isset( $ledger_balance[ $ledger['id'] ] ) ? $ledger_balance[ $ledger['id'] ] : 0.00,
        ];
    }

    return $temp_data;
}

/**
 * Get chart of account id by slug
 *
 * @param string $key
 *
 * @return int
 */
function erp_acct_get_chart_id_by_slug( $key ) {
    switch ( $key ) {
        case 'asset':
            $id = 1;
            break;

        case 'liability':
            $id = 2;
            break;

        case 'equity':
            $id = 3;
            break;

        case 'income':
            $id = 4;
            break;

        case 'expense':
            $id = 5;
            break;

        case 'asset_liability':
            $id = 6;
            break;

        case 'bank':
            $id = 7;
            break;
        default:
            $id = null;
    }

    return $id;
}

/**
 * Get ledgers
 *
 * @param $chart_id
 *
 * @return array|object|null
 */
function erp_acct_get_ledgers() {
    global $wpdb;

    $ledgers = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}erp_acct_ledgers WHERE unused IS NULL", ARRAY_A );

    return $ledgers;
}
