<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Get all products
 *
 * @return mixed
 */

function erp_acct_get_all_products( $args = [] ) {
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

    $last_changed = erp_cache_get_last_changed( 'accounting', 'products', 'erp-accounting' );
    $cache_key    = 'erp-get-products-' . md5( serialize( $args ) ) . ": $last_changed";
    $products     = wp_cache_get( $cache_key, 'erp-accounting' );

    $cache_key_count = 'erp-get-products-count-' . md5( serialize( $args ) ) . ": $last_changed";
    $products_count  = wp_cache_get( $cache_key_count, 'erp-accounting' );

    if ( false === $products ) {
        $limit = '';

        if ( -1 !== $args['number'] ) {
            $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['number'], $args['offset'] );
        }

        $sql = 'SELECT';

        if ( $args['count'] ) {
            $sql .= ' COUNT( product.id ) as total_number';
        } else {
            $sql .= " product.id,
                    product.name,
                    product.product_type_id,
                    product.cost_price,
                    product.sale_price,
                    product.tax_cat_id,
                    people.id AS vendor,
                    CONCAT(people.first_name, ' ',  people.last_name) AS vendor_name,
                    cat.id AS category_id,
                    cat.name AS cat_name,
                    product_type.name AS product_type_name";
        }

        $sql .= " FROM {$wpdb->prefix}erp_acct_products AS product
            LEFT JOIN {$wpdb->prefix}erp_peoples AS people ON product.vendor = people.id
            LEFT JOIN {$wpdb->prefix}erp_acct_product_categories AS cat ON product.category_id = cat.id
            LEFT JOIN {$wpdb->prefix}erp_acct_product_types AS product_type ON product.product_type_id = product_type.id
            WHERE product.product_type_id<>3";

        if ( ! empty( $args['s'] ) ) {
            $sql .= ' AND product.name LIKE %s';
            $search_str = '%' . $wpdb->esc_like( $args['s'] ) . '%';
            $sql = $wpdb->prepare( $sql, $search_str ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        // To use wpdb prepare, we need to add and %d=%d to achieve where 1=1
        $sql .= " ORDER BY product.{$args['orderby']} {$args['order']} {$limit}";

        erp_disable_mysql_strict_mode();

        if ( $args['count'] ) {
            $products_count = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key_count, $products_count, 'erp-accounting' );
        } else {
            $products = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key, $products, 'erp-accounting' );
        }
    }

    if ( $args['count'] ) {
        return $products_count;
    }

    return $products;
}

/**
 * Get an single product
 *
 * @param $product_no
 *
 * @return mixed
 */
function erp_acct_get_product( $product_id ) {
    global $wpdb;

    erp_disable_mysql_strict_mode();

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT
            product.id,
            product.name,
            product.product_type_id,
            product.cost_price,
            product.sale_price,
            product.tax_cat_id,
            people.id AS vendor,
            CONCAT(people.first_name, ' ',  people.last_name) AS vendor_name,
            cat.id AS category_id,
            cat.name AS cat_name,
            product_type.name AS product_type_name

		FROM {$wpdb->prefix}erp_acct_products AS product
		LEFT JOIN {$wpdb->prefix}erp_peoples AS people ON product.vendor = people.id
		LEFT JOIN {$wpdb->prefix}erp_acct_product_categories AS cat ON product.category_id = cat.id
        LEFT JOIN {$wpdb->prefix}erp_acct_product_types AS product_type ON product.product_type_id = product_type.id WHERE product.id = %d LIMIT %d",
            $product_id, 1
        ),
        ARRAY_A
    );

    return $row;
}

/**
 * Insert product data
 *
 * @param $data
 * @return WP_Error | integer
 */
function erp_acct_insert_product( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;
    $product_id         = null;

    try {
        $wpdb->query( 'START TRANSACTION' );
        $product_data = erp_acct_get_formatted_product_data( $data );

        $product_check =  $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}erp_acct_products where name = %s",
                $product_data['name']
            ),
            OBJECT
        );

        if ( $product_check ) {
           throw new \Exception( $product_data['name'] . ' ' . __( 'product already exists!', 'erp' ) ) ;
        }

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_products',
            [
                'name'            => $product_data['name'],
                'product_type_id' => $product_data['product_type_id'],
                'category_id'     => $product_data['category_id'],
                'tax_cat_id'      => $product_data['tax_cat_id'],
                'vendor'          => $product_data['vendor'],
                'cost_price'      => $product_data['cost_price'],
                'sale_price'      => $product_data['sale_price'],
                'created_at'      => $product_data['created_at'],
                'created_by'      => $product_data['created_by'],
                'updated_at'      => $product_data['updated_at'],
                'updated_by'      => $product_data['updated_by'],
            ]
        );

        $product_id = $wpdb->insert_id;

        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_Error( 'duplicate-product', $e->getMessage(), array( 'status' => 400 ) );
    }

    erp_acct_purge_cache( ['list' => 'products,products_vendor'] );

    do_action( 'erp_acct_after_change_product_list' );

    return erp_acct_get_product( $product_id );
}

/**
 * Update product data
 *
 * @param $data
 *
 * @return WP_Error | Object
 */
function erp_acct_update_product( $data, $id ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );
        $product_data = erp_acct_get_formatted_product_data( $data );

        $product_name_check =  $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}erp_acct_products where name = %s AND id NOT IN(%d)",
                $product_data['name'],
                $id
            ),
            OBJECT
        );

        if ( $product_name_check ) {
            throw new \Exception( $product_data['name'] . ' ' . __( "Product name already exists!" , "erp") ) ;
        }

        $wpdb->update(
            $wpdb->prefix . 'erp_acct_products',
            [
                'name'            => $product_data['name'],
                'product_type_id' => $product_data['product_type_id'],
                'category_id'     => $product_data['category_id'],
                'tax_cat_id'      => $product_data['tax_cat_id'],
                'vendor'          => $product_data['vendor'],
                'cost_price'      => $product_data['cost_price'],
                'sale_price'      => $product_data['sale_price'],
                'created_at'      => $product_data['updated_at'],
                'created_by'      => $product_data['updated_by'],
                'updated_at'      => $product_data['updated_at'],
                'updated_by'      => $product_data['updated_by'],
            ],
            [
                'id' => $id,
            ]
        );

        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );

        return new WP_Error( 'duplicate-product', $e->getMessage(), array( 'status' => 400 ) );
    }

    erp_acct_purge_cache( ['list' => 'products,products_vendor'] );

    do_action( 'erp_acct_after_change_product_list' );

    return erp_acct_get_product( $id );
}

/**
 * Get formatted product data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_product_data( $data ) {
    $product_data['name']            = ! empty( $data['name'] ) ? $data['name'] : 1;
    $product_data['product_type_id'] = ! empty( $data['product_type_id'] ) ? $data['product_type_id'] : 1;
    $product_data['category_id']     = ! empty( $data['category_id'] ) ? $data['category_id'] : 0;
    $product_data['tax_cat_id']      = ! empty( $data['tax_cat_id'] ) ? $data['tax_cat_id'] : 0;
    $product_data['vendor']          = ! empty( $data['vendor'] ) ? $data['vendor'] : '';
    $product_data['cost_price']      = ! empty( $data['cost_price'] ) ? $data['cost_price'] : '';
    $product_data['sale_price']      = ! empty( $data['sale_price'] ) ? $data['sale_price'] : '';
    $product_data['created_at']      = ! empty( $data['created_at'] ) ? $data['created_at'] : '';
    $product_data['created_by']      = ! empty( $data['created_by'] ) ? $data['created_by'] : '';
    $product_data['updated_at']      = ! empty( $data['updated_at'] ) ? $data['updated_at'] : '';
    $product_data['updated_by']      = ! empty( $data['updated_by'] ) ? $data['updated_by'] : '';

    return $product_data;
}

/**
 * Delete an product
 *
 * @param $product_no
 *
 * @return int
 */
function erp_acct_delete_product( $product_id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_products', [ 'id' => $product_id ] );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_product_details', [ 'product_id' => $product_id ] );

    erp_acct_purge_cache( ['list' => 'products,products_vendor'] );

    do_action( 'erp_acct_after_change_product_list' );

    return $product_id;
}

/**
 * Get product types
 *
 * @param $product_id
 *
 * @return int
 */
function erp_acct_get_product_types() {
    global $wpdb;

    $types = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_acct_product_types" );

    return apply_filters( 'erp_acct_product_types', $types );
}

/**
 * Get product type id by product id
 *
 * @param $product_id
 *
 * @return int
 */
function erp_acct_get_product_type_id_by_product_id( $product_id ) {
    global $wpdb;

    $type_id = $wpdb->get_var( $wpdb->prepare( "SELECT product_type_id FROM {$wpdb->prefix}erp_acct_products WHERE id = %d", $product_id ) );

    return $type_id;
}

/**
 * Get all products of a vendor
 *
 * @return mixed
 */
function erp_acct_get_vendor_products( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
        's'       => '',
        'vendor'  => 0,
    ];

    $args = wp_parse_args( $args, $defaults );

    $last_changed    = erp_cache_get_last_changed( 'accounting', 'products_vendor', 'erp-accounting' );
    $cache_key       = 'erp-get-products_vendor-' . md5( serialize( $args ) ) . ": $last_changed";
    $products_vendor = wp_cache_get( $cache_key, 'erp-accounting' );

    $cache_key_count       = 'erp-get-products_vendor-count-' . md5( serialize( $args ) ) . ": $last_changed";
    $products_vendor_count = wp_cache_get( $cache_key_count, 'erp-accounting' );

    if ( false === $products_vendor ) {
        $limit = '';

        if ( -1 !== $args['number'] ) {
            $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['number'], $args['offset'] );
        }

        $sql = 'SELECT';

        if ( $args['count'] ) {
            $sql .= ' COUNT( product.id ) as total_number';
        } else {
            $sql .= " product.id,
                product.name,
                product.product_type_id,
                product.cost_price,
                product.sale_price,
                product.tax_cat_id,
                product.vendor,
                CONCAT(people.first_name, ' ',  people.last_name) AS vendor_name,
                cat.id AS category_id,
                cat.name AS cat_name,
                product_type.name AS product_type_name";
        }

        // Build the FROM and JOIN parts of the query separately
        $sql .= $wpdb->prepare( " FROM {$wpdb->prefix}erp_acct_products AS product
            LEFT JOIN {$wpdb->prefix}erp_peoples AS people ON product.vendor = people.id
            LEFT JOIN {$wpdb->prefix}erp_acct_product_categories AS cat ON product.category_id = cat.id
            LEFT JOIN {$wpdb->prefix}erp_acct_product_types AS product_type ON product.product_type_id = product_type.id
            WHERE people.id = %d AND product.product_type_id <> %d", $args['vendor'], 3 );

        // Append the ORDER BY clause
        $sql .= " ORDER BY product.{$args['orderby']} {$args['order']}";

        // Append the LIMIT clause if needed
        if ( ! empty( $limit ) ) {
            $sql .= " $limit";
        }

        if ( $args['count'] ) {
            $products_vendor_count = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key_count, $products_vendor_count, 'erp-accounting' );
        } else {
            $products_vendor = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

            wp_cache_set( $cache_key, $products_vendor, 'erp-accounting' );
        }
    }

    if ( $args['count'] ) {
        return $products_vendor_count;
    }

    return $products_vendor;
}

/**
 * Validates csv data for importing
 *
 * @since 1.9.0
 *
 * @param array $data
 *
 * @return array|WP_Error
 */
function erp_acct_validate_csv_data( $data ) {
    $files = wp_check_filetype_and_ext( $data['csv_file']['tmp_name'], $data['csv_file']['name'] );

    if ( 'csv' !== $files['ext'] && 'text/csv' !== $files['type'] ) {
        return new WP_Error( 'invalid-file-type', __( 'The file is not a valid CSV file! Please provide a valid one.', 'erp' ) );
    }

    $csv = new \ParseCsv\Csv();
    $csv->encoding( null, 'UTF-8' );
    $csv->parse( $data['csv_file']['tmp_name'] );

    if ( empty( $csv->data ) ) {
        return new WP_Error( 'no-data', __( 'No data found to import!', 'erp' ) );
    }

    $csv_data   = [];
    $csv_data[] = array_keys( $csv->data[0] );

    foreach ( $csv->data as $data_item ) {
        $csv_data[] = array_values( $data_item );
    }

    if ( empty( $csv_data ) ) {
        return new WP_Error( 'no-data', __( 'No data found to import!', 'erp' ), [ 'status' => 400 ] );
    }

    $count           = 0;
    $errors          = [];
    $product_data    = [];
    $to_be_updated   = [];
    $processed_data  = '';
    $temp_type       = $data['type'];
    $update_existing = (int) $data['update_existing'] ? true : false;
    $curr_date       = erp_current_datetime()->format( 'Y-m-d' );
    $user            = get_current_user_id();

    if ( $update_existing ) {
        $temp_type = 'product_non_unique';
    }

    $errors = apply_filters( 'erp_validate_csv_data', $csv_data, $data['fields'], $temp_type );

    if ( ! empty( $errors ) ) {
        return new WP_Error( 'import-error', $errors );
    }

    unset( $csv_data[0] );

    foreach ( $csv_data as $index => $line ) {
        if ( empty( $line ) ) {
            continue;
        }

        if ( is_array( $data['fields'] ) && ! empty( $data['fields'] ) ) {
            $product_data[ $index ] = '';
            $product_exists_id      = '';
            $product_checked        = false;

            global $wpdb;

            foreach ( $data['fields'] as $key => $value ) {

                switch ( $key ) {

                    case 'category_id':

                        if ( ! empty( $line[ $value ] ) ) {
                            $valid_value = $wpdb->get_var(
                                "SELECT id
                                FROM {$wpdb->prefix}erp_acct_product_categories
                                WHERE id = {$line[ $value ]}"
                            );
                        }

                        break;

                    case 'product_type_id':

                        if ( ! empty( $line[ $value ] ) ) {
                            $valid_value = $wpdb->get_var(
                                "SELECT id
                                FROM {$wpdb->prefix}erp_acct_product_types
                                WHERE id = {$line[ $value ]}"
                            );
                        }

                        break;

                    case 'tax_cat_id':

                        if ( ! empty( $line[ $value ] ) ) {
                            $valid_value = $wpdb->get_var(
                                "SELECT id
                                FROM {$wpdb->prefix}erp_acct_tax_categories
                                WHERE id = {$line[ $value ]}"
                            );
                        }

                        break;

                    case 'vendor':

                        if ( ! empty( $line[ $value ] ) ) {
                            $valid_value = $wpdb->get_var(
                                "SELECT people.id
                                FROM {$wpdb->prefix}erp_peoples AS people
                                LEFT JOIN {$wpdb->prefix}erp_people_type_relations AS rel
                                ON people.id = rel.people_id
                                WHERE people.id = {$line[ $value ]}
                                AND rel.people_types_id = 4"
                            );
                        }

                        break;

                    default:
                        $valid_value = true;
                }

                $value = ! empty( $line[ $value ] ) &&
                         ! empty( $valid_value )
                         ? $line[ $value ]
                         : (
                            ! empty( $data[ $key ] )
                            ? $data[ $key ]
                            : ''
                         );

                if ( $update_existing && ! $product_checked && 'name' === $key ) {
                    $product_exists_id =  $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT id FROM {$wpdb->prefix}erp_acct_products where name = %s",
                            $value
                        )
                    );

                    $product_checked = true;
                }

                if ( empty( $product_exists_id ) ) {
                    $product_data[ $index ] .= "'{$value}',";
                } else {
                    $to_be_updated[ $product_exists_id ][ $key ] = $value;
                }
            }

            if ( empty( $product_exists_id ) ) {
                $product_data[ $index ] .= "'{$user}','{$curr_date}'";
            } else {
                unset( $product_data[ $index ] );
            }

            ++ $count;
        }
    }

    if ( ! empty( $product_data ) ) {
        $processed_data = '(' . implode( '),(', $product_data ) . ')';
    }

    return [
        'data'   => $processed_data,
        'update' => $to_be_updated,
        'total'  => $count
    ];
}

/**
 * Imports products from csv
 *
 * @since 1.9.0
 *
 * @param array $data
 *
 * @return int|WP_Error
 */
function erp_acct_import_products( $data ) {
    global $wpdb;

    if ( ! empty( $data['items'] ) ) {
        $inserted = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_products
                 (name, product_type_id, category_id, cost_price, sale_price, vendor, tax_cat_id, created_by, created_at)
                VALUES %s",
                $data['items']
            )
        );

        if ( is_wp_error( $inserted ) ) {
            return new WP_Error( 'import-db-error', __( 'Something went wrong', 'erp' ) );
        }
    }

    if ( ! empty( $data['update'] ) ) {
        $curr_date = erp_current_datetime()->format( 'Y-m-d' );
        $user      = get_current_user_id();

        foreach ( $data['update'] as $id => $field_data ) {
            $field_data['updated_at'] = $curr_date;
            $field_data['updated_by'] = $user;

            $wpdb->update( "{$wpdb->prefix}erp_acct_products", $field_data, [ 'id' => $id ] );
        }
    }

    if ( 0 >= (int) $data['total'] ) {
        return new WP_Error( 'import-error', __( 'No data imported', 'erp' ) );
    }

    return $data['total'];
}
