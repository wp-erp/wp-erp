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
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'tax.id',
        'order'   => 'ASC',
        'count'   => false,
        's'       => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $last_changed = erp_cache_get_last_changed( 'accounting', 'tax_rates', 'erp-accounting' );
    $cache_key    = 'erp-get-tax-rates-' . md5( serialize( $args ) ) . ": $last_changed";
    $tax_rates    = wp_cache_get( $cache_key, 'erp-accounting' );

    $cache_key_count = 'erp-get-tax-rates-count-' . md5( serialize( $args ) ) . " : $last_changed";
    $tax_rates_count  = wp_cache_get( $cache_key_count, 'erp-accounting' );

    if ( false === $tax_rates ) {
        $limit = '';

        if ( -1 !== $args['number'] ) {
            $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['number'], $args['offset'] );
        }

        $sql  = 'SELECT';
        $sql .= $args['count'] ? ' COUNT( DISTINCT tax.id ) as total_number ' : ' DISTINCT tax.id, tax.tax_rate_name, tax.tax_number, tax.default ';
        $sql .= $wpdb->prepare( "FROM {$wpdb->prefix}erp_acct_taxes AS tax INNER JOIN {$wpdb->prefix}erp_acct_tax_cat_agency as cat_agency on tax.id = cat_agency.tax_id ORDER BY %s %s %s", $args['orderby'], $args['order'], $limit );

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
 * Get an single tax
 *
 * @param $tax_no
 *
 * @return mixed
 */
function erp_acct_get_tax_rate( $tax_no ) {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT

                tax.id,
                tax.tax_rate_name,
                tax.tax_number,
                tax.default,
                tax.created_at,
                tax.created_by,
                tax.updated_at,
                tax.updated_by,

                tax_item.tax_id,
                tax_item.component_name,
                tax_item.agency_id,
                tax_item.tax_cat_id

        FROM {$wpdb->prefix}erp_acct_taxes AS tax
        LEFT JOIN {$wpdb->prefix}erp_acct_tax_cat_agency AS tax_item ON tax.id = tax_item.tax_id

        WHERE tax.id = %d LIMIT 1", $tax_no );

    erp_disable_mysql_strict_mode();

    $row = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    $row['tax_components'] = erp_acct_format_tax_line_items( $tax_no );

    for ( $i = 0; $i < count( $row['tax_components'] ); $i++ ) {
        $row['tax_components'][ $i ]['agency']   = null; // we'll fill that later from VUE
        $row['tax_components'][ $i ]['category'] = null; // we'll fill that later from VUE

        $row['tax_components'][ $i ]['agency_name']  = erp_acct_get_tax_agency_by_id( $row['tax_components'][ $i ]['agency_id'] );
        $row['tax_components'][ $i ]['tax_cat_name'] = erp_acct_get_tax_category_by_id( $row['tax_components'][ $i ]['tax_cat_id'] );
    }

    return $row;
}

/**
 * Insert tax data
 *
 * @param $data
 *
 * @return array
 */
function erp_acct_insert_tax_rate( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );
    $items    = $data['tax_components'];
    $tax_id   = (int) $data['tax_rate_name'];
    $inserted = [];

    foreach ( $items as $item ) {
        $result = $wpdb->insert(
            $wpdb->prefix . 'erp_acct_tax_cat_agency',
            [
                'tax_id'         => $tax_id,
                'component_name' => $item['component_name'],
                'tax_cat_id'     => $item['tax_category_id'],
                'agency_id'      => $item['agency_id'],
                'tax_rate'       => $item['tax_rate'],
                'created_at'     => $tax_data['created_at'],
                'created_by'     => $tax_data['created_by'],
                'updated_at'     => $tax_data['updated_at'],
                'updated_by'     => $tax_data['updated_by'],
            ]
        );

        if ( ! is_wp_error( $result ) ) {
            $inserted[] = $wpdb->insert_id;
        }
    }

    erp_acct_purge_cache( [ 'list' => 'tax_rates' ] );

    return $inserted;
}

/**
 * Update tax data
 *
 * @param $data
 *
 * @return int
 */
function erp_acct_update_tax_rate( $data, $id ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_taxes',
        [
            'tax_rate_id' => $tax_data['tax_rate_id'],
            'tax_number'  => $tax_data['tax_number'],
            'default'     => $tax_data['default'],
            'updated_at'  => $tax_data['updated_at'],
            'updated_by'  => $tax_data['updated_by'],
        ],
        [
            'id' => $id,
        ]
    );

    if ( ! empty( $tax_data['default'] ) && $tax_data['default'] ) {
        $results = $wpdb->get_results( 'UPDATE ' . $wpdb->prefix . 'erp_acct_taxes' . ' SET `default`=0' );
    }

    $items = $data['tax_components'];

    foreach ( $items as $key => $item ) {
        $wpdb->update(
            $wpdb->prefix . 'erp_acct_tax_cat_agency',
            [
                'component_name' => $item['component_name'],
                'tax_cat_id'     => $item['tax_cat_id'],
                'agency_id'      => $item['agency_id'],
                'tax_rate'       => $item['tax_rate'],
                'updated_at'     => $tax_data['updated_at'],
                'updated_by'     => $tax_data['updated_by'],
            ],
            [
                'tax_id' => $id,
            ]
        );
    }

    erp_acct_purge_cache( ['list' => 'tax_rates'] );

    return $id;
}

/**
 * Update tax data
 *
 * @param $data
 *
 * @return int
 */
function erp_acct_quick_edit_tax_rate( $data, $id ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    if ( ! empty( $tax_data['default'] ) && 1 === $tax_data['default'] ) {
        $results = $wpdb->get_results( 'UPDATE ' . $wpdb->prefix . 'erp_acct_taxes' . ' SET `default`=0' );
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_taxes',
        [
            'tax_number' => $tax_data['tax_number'],
            'default'    => $tax_data['default'],
            'updated_at' => $tax_data['updated_at'],
            'updated_by' => $tax_data['updated_by'],
        ],
        [
            'id' => $id,
        ]
    );

    erp_acct_purge_cache( ['list' => 'tax_rates'] );

    return $id;
}

/**
 * Add line item of a tax rate
 *
 * @param $data
 *
 * @return int
 */
function erp_acct_add_tax_rate_line( $data ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    $tax_data = erp_acct_get_formatted_tax_line_data( $data );

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_tax_cat_agency',
        [
            'tax_id'         => $tax_data['tax_id'],
            'component_name' => $tax_data['component_name'],
            'tax_cat_id'     => $tax_data['tax_cat_id'],
            'agency_id'      => $tax_data['agency_id'],
            'tax_rate'       => $tax_data['tax_rate'],
            'created_at'     => $tax_data['created_at'],
            'created_by'     => $tax_data['created_by'],
            'updated_at'     => $tax_data['updated_at'],
            'updated_by'     => $tax_data['updated_by'],
        ]
    );

    erp_acct_purge_cache( ['list' => 'tax_rates'] );

    return $tax_data['tax_id'];
}

/**
 * Update line item of a tax rate
 *
 * @param $data
 *
 * @return int
 */
function erp_acct_edit_tax_rate_line( $data ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    $tax_data = erp_acct_get_formatted_tax_line_data( $data );

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_tax_cat_agency',
        [
            'component_name' => $tax_data['component_name'],
            'tax_cat_id'     => $tax_data['tax_cat_id'],
            'agency_id'      => $tax_data['agency_id'],
            'tax_rate'       => $tax_data['tax_rate'],
            'updated_at'     => $tax_data['updated_at'],
            'updated_by'     => $tax_data['updated_by'],
        ],
        [
            'id' => $tax_data['db_id'],
        ]
    );

    erp_acct_purge_cache( ['list' => 'tax_rates'] );

    return $tax_data['db_id'];
}

/**
 * Delete an tax rate line
 *
 * @param $line_no
 *
 * @return int
 */
function erp_acct_delete_tax_rate_line( $line_no ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_tax_cat_agency', [ 'id' => $line_no ] );

    erp_acct_purge_cache( ['list' => 'tax_rates'] );

    return $line_no;
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

    $wpdb->delete( $wpdb->prefix . 'erp_acct_taxes', [ 'id' => $tax_no ] );

    erp_acct_purge_cache( ['list' => 'tax_rates'] );

    return $tax_no;
}

/**
 * Get all tax payments
 *
 * @return mixed
 */
function erp_acct_get_tax_pay_records( $args = [] ) {
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

    $last_changed = erp_cache_get_last_changed( 'accounting', 'tax_pay', 'erp-accounting' );
    $cache_key    = 'erp-get-tax-pay-' . md5( serialize( $args ) ) . ": $last_changed";
    $tax_pay      = wp_cache_get( $cache_key, 'erp-accounting' );

    $cache_key_count = 'erp-get-tax-pay-count-' . md5( serialize( $args ) ) . " : $last_changed";
    $tax_pay_count   = wp_cache_get( $cache_key_count, 'erp-accounting' );

    if ( false === $tax_pay ) {

        $limit = '';

        if ( -1 !== $args['number'] ) {
            $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['number'], $args['offset'] );
        }

        $sql  = 'SELECT';
        $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
        $sql .= $wpdb->prepare( "FROM {$wpdb->prefix}erp_acct_tax_pay ORDER BY %s %s %s", $args['orderby'], $args['order'], $limit );

        if ( $args['count'] ) {
            $tax_pay_count = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key_count, $tax_pay_count, 'erp-accounting' );
        } else {
            $tax_pay = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key, $tax_pay, 'erp-accounting' );
        }
    }

    if ( $args['count'] ) {
        return $tax_pay_count;
    }

    return $tax_pay;
}

/**
 * Get a single tax payment
 *
 * @return mixed
 */
function erp_acct_get_tax_pay_record( $voucher_no ) {
    global $wpdb;

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT
            tax.id,
            tax.voucher_no,
            tax.particulars,
            tax.amount,
            tax.trn_date,
            tax.voucher_type,
            tax.trn_by,
            tax.agency_id,
            tax.ledger_id,
            tax.created_at
            FROM {$wpdb->prefix}erp_acct_tax_pay AS tax
            WHERE tax.voucher_no = %d LIMIT 1",
            $voucher_no
        ),
        ARRAY_A
    );

    return $row;
}

/**
 * Make a tax payment
 *
 * @param $data
 *
 * @return array
 */
function erp_acct_pay_tax( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;
    $currency           = erp_get_currency( true );

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_voucher_no',
        [
            'type'       => 'tax_payment',
            'currency'   => $currency,
            'created_at' => $data['created_at'],
            'created_by' => $data['created_by'],
            'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : null,
            'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : null,
        ]
    );

    $voucher_no = $wpdb->insert_id;

    $tax_data = erp_acct_get_formatted_tax_data( $data );

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_tax_pay',
        [
            'voucher_no'   => $voucher_no,
            'trn_date'     => $tax_data['trn_date'],
            // translators: %s: voucher_no
            'particulars'  => ! empty( $tax_data['particulars'] ) ? $tax_data['particulars'] : sprintf( __( 'Invoice created with voucher no %s', 'erp' ), $voucher_no ),
            'amount'       => $tax_data['amount'],
            'voucher_type' => $tax_data['voucher_type'],
            'trn_by'       => $tax_data['trn_by'],
            'agency_id'    => $tax_data['agency_id'],
            'ledger_id'    => $tax_data['ledger_id'],
            'created_at'   => $tax_data['created_at'],
            'created_by'   => $tax_data['created_by'],
            'updated_at'   => $tax_data['updated_at'],
            'updated_by'   => $tax_data['updated_by'],
        ]
    );

    if ( 'debit' === $tax_data['voucher_type'] ) {
        $debit  = $tax_data['amount'];
        $credit = 0;
    } else {
        $debit  = 0;
        $credit = $tax_data['amount'];
    }

    // insert data into {$wpdb->prefix}erp_acct_tax_agency_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_tax_agency_details',
        [
            'agency_id'   => $tax_data['agency_id'],
            'trn_no'      => $voucher_no,
            'trn_date'    => $tax_data['trn_date'],
            // translators: %s: voucher_no
            'particulars' => ! empty( $tax_data['particulars'] ) ? $tax_data['particulars'] : sprintf( __( 'Invoice created with voucher no %s', 'erp' ), $voucher_no ),
            'debit'       => $debit,
            'credit'      => $credit,
            'created_at'  => $tax_data['created_at'],
            'created_by'  => $tax_data['created_by'],
        ]
    );

    $tax_data['voucher_no'] = $voucher_no;

    erp_acct_insert_tax_pay_data_into_ledger( $tax_data );

    $tax_pay = erp_acct_get_tax_pay_record( $voucher_no );

    erp_acct_purge_cache( ['list' => 'tax_pay'] );

    return $tax_pay;
}

/**
 * Insert Tax pay data into ledger
 *
 * @param array $invoice_data
 *
 * @return mixed
 */
function erp_acct_insert_tax_pay_data_into_ledger( $tax_data ) {
    global $wpdb;

    if ( 'debit' === $tax_data['voucher_type'] ) {
        $debit  = 0;
        $credit = $tax_data['amount'];
    } else {
        $debit  = $tax_data['amount'];
        $credit = 0;
    }

    // Insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        [
            'ledger_id'   => $tax_data['ledger_id'],
            'trn_no'      => $tax_data['voucher_no'],
            'particulars' => $tax_data['particulars'],
            'debit'       => $debit,
            'credit'      => $credit,
            'trn_date'    => $tax_data['trn_date'],
            'created_at'  => $tax_data['created_at'],
            'created_by'  => $tax_data['created_by'],
            'updated_at'  => $tax_data['updated_at'],
            'updated_by'  => $tax_data['updated_by'],
        ]
    );

    erp_acct_purge_cache( ['list' => 'tax_pay'] );
}

/**
 * Format payment line items
 *
 * @param string $tax
 *
 * @return array
 */
function erp_acct_format_tax_line_items( $tax = 'all' ) {
    global $wpdb;

    $sql = 'SELECT id as db_id, tax_id, component_name, agency_id, tax_cat_id, tax_rate';

    if ( 'all' === $tax ) {
        $tax_sql = '';
    } else {
        $tax_sql = $wpdb->prepare( 'WHERE tax_id = %d', $tax );
    }
    $sql .= " FROM {$wpdb->prefix}erp_acct_tax_cat_agency {$tax_sql} ORDER BY tax_id";

    $results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return $results;
}

/**
 * Get formatted tax data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_tax_data( $data ) {
    $tax_data = [];

    $tax_data['tax_rate_id']     = isset( $data['tax_rate_name'] ) ? $data['tax_rate_name'] : '';
    $tax_data['tax_rate']        = isset( $data['tax_rate'] ) ? $data['tax_rate'] : 0;
    $tax_data['tax_id']          = isset( $data['tax_id'] ) ? $data['tax_id'] : 0;
    $tax_data['trn_by']          = isset( $data['trn_by'] ) ? $data['trn_by'] : '';
    $tax_data['tax_category_id'] = isset( $data['tax_category_id'] ) ? $data['tax_category_id'] : 0;
    $tax_data['agency_id']       = isset( $data['agency_id'] ) ? $data['agency_id'] : 0;
    $tax_data['agency_name']     = isset( $data['agency_name'] ) ? $data['agency_name'] : '';
    $tax_data['tax_cat_name']    = isset( $data['tax_cat_name'] ) ? $data['tax_cat_name'] : '';
    $tax_data['tax_components']  = isset( $data['tax_components'] ) ? $data['tax_components'] : [];
    $tax_data['created_at']      = gmdate( 'Y-m-d' );
    $tax_data['created_by']      = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $tax_data['updated_at']      = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
    $tax_data['updated_by']      = isset( $data['updated_by'] ) ? $data['updated_by'] : '';
    $tax_data['name']            = isset( $data['name'] ) ? $data['name'] : '';
    $tax_data['description']     = isset( $data['description'] ) ? $data['description'] : '';
    $tax_data['voucher_no']      = isset( $data['voucher_no'] ) ? $data['voucher_no'] : '';
    $tax_data['trn_date']        = isset( $data['trn_date'] ) ? $data['trn_date'] : gmdate( 'Y-m-d' );
    $tax_data['tax_period']      = isset( $data['tax_period'] ) ? $data['tax_period'] : '';
    $tax_data['particulars']     = isset( $data['particulars'] ) ? $data['particulars'] : '';
    $tax_data['amount']          = isset( $data['amount'] ) ? $data['amount'] : '';
    $tax_data['ledger_id']       = isset( $data['ledger_id'] ) ? $data['ledger_id'] : '';
    $tax_data['voucher_type']    = isset( $data['voucher_type'] ) ? $data['voucher_type'] : '';

    return $tax_data;
}

/**
 * Get formatted tax data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_tax_line_data( $data ) {
    $tax_data = [];

    $tax_data['tax_id']         = isset( $data['tax_id'] ) ? $data['tax_id'] : '';
    $tax_data['db_id']          = isset( $data['db_id'] ) ? $data['db_id'] : '';
    $tax_data['rate_id']        = isset( $data['rate_id'] ) ? $data['rate_id'] : '';
    $tax_data['component_name'] = isset( $data['component_name'] ) ? $data['component_name'] : '';
    $tax_data['agency_id']      = isset( $data['agency_id'] ) ? $data['agency_id'] : 0;
    $tax_data['tax_cat_id']     = isset( $data['tax_cat_id'] ) ? $data['tax_cat_id'] : 0;
    $tax_data['tax_rate']       = isset( $data['tax_rate'] ) ? $data['tax_rate'] : 0;
    $tax_data['created_at']     = isset( $data['created_at'] ) ? $data['created_at'] : '';
    $tax_data['created_by']     = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $tax_data['updated_at']     = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
    $tax_data['updated_by']     = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $tax_data;
}

/**
 * Tax summary
 */
function erp_acct_tax_summary() {
    global $wpdb;

    erp_disable_mysql_strict_mode();

    return $wpdb->get_results(
        "SELECT
        tax.id AS tax_rate_id,
        tax.tax_rate_name,
        tax.default,
        tca.tax_cat_id,
        sum(tca.tax_rate) AS tax_rate
        FROM {$wpdb->prefix}erp_acct_tax_cat_agency AS tca
        INNER JOIN {$wpdb->prefix}erp_acct_taxes AS tax ON tax.id = tca.tax_id
        GROUP BY tca.tax_cat_id, tax.id order by tax_cat_id",
        ARRAY_A
    );
}

/**
 * Get default tax rate name id
 */
function erp_acct_get_default_tax_rate_name_id() {
    global $wpdb;

    return $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}erp_acct_taxes WHERE `default` = 1" );
}

/**
 * Inserts synced tax data
 *
 * @since 1.10.0
 *
 * @param array $args
 *
 * @return int|string|\WP_Error
 */
function erp_acct_insert_synced_tax( $args = [] ) {
    global $wpdb;

    $defaults = [
        'system_id'   => null,
        'sync_type'   => '',
        'sync_source' => '',
        'sync_id'     => null,
        'sync_slug'   => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    if ( empty( $args['system_id'] ) || ( empty( $args['sync_slug'] ) && empty( $args['sync_id'] ) ) ) {
        return new \WP_Error( 'inconsistent-data', __( 'Inconsistent data provided', 'erp' ) );
    }

    $inserted = $wpdb->insert( "{$wpdb->prefix}erp_acct_synced_taxes", $args, [ '%d', '%s', '%s', '%d', '%s' ] );

    return $inserted;
}

/**
 * Retrieves system id of synced tax data
 *
 * @since 1.10.0
 *
 * @param string $sync_type
 * @param string $sync_source
 * @param int|string $sync_id
 * @param string $sync_slug
 *
 * @return int|null
 */
function erp_acct_get_synced_tax_system_id( $sync_type, $sync_source, $sync_id = false, $sync_slug = false ) {
    global $wpdb;

    $sql  = "SELECT system_id
            FROM {$wpdb->prefix}erp_acct_synced_taxes
            WHERE sync_type = %s
            AND sync_source = %s";

    $args = [ $sync_type, $sync_source ];

    if ( false !== $sync_id ) {
        $sql   .= " AND sync_id = %d";
        $args[] = $sync_id;
    }

    if ( false !== $sync_slug ) {
        $sql   .= " AND sync_slug = %s";
        $args[] = $sync_slug;
    }

    $system_id = $wpdb->get_var( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    return ! is_wp_error( $system_id ) ? (int) $system_id : null;
}
