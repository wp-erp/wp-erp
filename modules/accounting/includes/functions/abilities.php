<?php

/**
 * Accounting Abilities
 *
 * Registers WP ERP Accounting operations as abilities via the WordPress
 * Abilities API, making them discoverable and callable by MCP (Model
 * Context Protocol) clients and AI assistants.
 *
 * Requires WordPress 6.9+ (Abilities API).
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Accounting ability category.
 */
add_action( 'wp_abilities_api_categories_init', 'erp_ac_register_ability_category' );

if ( ! function_exists( 'erp_ac_register_ability_category' ) ) {
    function erp_ac_register_ability_category() {
        if ( ! function_exists( 'wp_register_ability_category' ) ) {
            return;
        }

        wp_register_ability_category(
            'wp-erp-accounting',
            [
                'label'       => __( 'WP ERP — Accounting', 'erp' ),
                'description' => __( 'Abilities for managing customers, vendors, invoices, expenses, journal entries, and reports in WP ERP Accounting.', 'erp' ),
            ]
        );
    }
}

/**
 * Register all Accounting abilities.
 */
add_action( 'wp_abilities_api_init', 'erp_ac_register_abilities' );

if ( ! function_exists( 'erp_ac_register_abilities' ) ) {
    function erp_ac_register_abilities() {
        if ( ! function_exists( 'wp_register_ability' ) ) {
            return;
        }

        // ── Customers ────────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/ac-list-customers',
            [
                'label'        => __( 'List Customers', 'erp' ),
                'description'  => __( 'Retrieve a paginated list of accounting customers.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number'  => [ 'type' => 'integer', 'description' => __( 'Number per page.', 'erp' ) ],
                        'offset'  => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                        'orderby' => [ 'type' => 'string', 'description' => __( 'Order by field.', 'erp' ) ],
                        'order'   => [ 'type' => 'string', 'enum' => [ 'ASC', 'DESC' ], 'description' => __( 'Sort order.', 'erp' ) ],
                        'search'  => [ 'type' => 'string', 'description' => __( 'Search keyword.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'customers' => [ 'type' => 'array', 'description' => __( 'Array of customer objects.', 'erp' ) ],
                        'total'     => [ 'type' => 'integer' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_customer' );
                },
                'execute_callback' => function ( $input ) {
                    $args = wp_parse_args(
                        $input,
                        [
                            'number'  => 20,
                            'offset'  => 0,
                            'orderby' => 'first_name',
                            'order'   => 'ASC',
                            'type'    => 'customer',
                            'count'   => false,
                        ]
                    );
                    $customers     = erp_get_peoples( $args );
                    $args['count'] = true;
                    $total         = erp_get_peoples( $args );

                    return [
                        'customers' => is_array( $customers ) ? array_values( $customers ) : [],
                        'total'     => (int) $total,
                    ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-get-customer',
            [
                'label'        => __( 'Get Customer', 'erp' ),
                'description'  => __( 'Retrieve a single accounting customer by ID.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Customer people ID.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Customer object.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_single_customer' );
                },
                'execute_callback' => function ( $input ) {
                    $customer = erp_get_people( (int) $input['id'] );

                    if ( ! $customer ) {
                        return new \WP_Error( 'not_found', __( 'Customer not found.', 'erp' ), [ 'status' => 404 ] );
                    }

                    return is_object( $customer ) ? (array) $customer : $customer;
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-create-customer',
            [
                'label'        => __( 'Create Customer', 'erp' ),
                'description'  => __( 'Create a new accounting customer.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'first_name', 'email' ],
                    'properties' => [
                        'first_name'   => [ 'type' => 'string', 'description' => __( 'First name.', 'erp' ) ],
                        'last_name'    => [ 'type' => 'string', 'description' => __( 'Last name.', 'erp' ) ],
                        'email'        => [ 'type' => 'string', 'format' => 'email', 'description' => __( 'Email address.', 'erp' ) ],
                        'phone'        => [ 'type' => 'string', 'description' => __( 'Phone number.', 'erp' ) ],
                        'company'      => [ 'type' => 'string', 'description' => __( 'Company name.', 'erp' ) ],
                        'address_1'    => [ 'type' => 'string', 'description' => __( 'Street address.', 'erp' ) ],
                        'city'         => [ 'type' => 'string', 'description' => __( 'City.', 'erp' ) ],
                        'state'        => [ 'type' => 'string', 'description' => __( 'State/Province.', 'erp' ) ],
                        'postal_code'  => [ 'type' => 'string', 'description' => __( 'Postal/ZIP code.', 'erp' ) ],
                        'country'      => [ 'type' => 'string', 'description' => __( 'Country code.', 'erp' ) ],
                        'currency'     => [ 'type' => 'string', 'description' => __( 'Currency code.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'New customer ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_create_customer' );
                },
                'execute_callback' => function ( $input ) {
                    $input['type'] = 'customer';
                    $result        = erp_insert_people( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $result ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-update-customer',
            [
                'label'        => __( 'Update Customer', 'erp' ),
                'description'  => __( 'Update an existing accounting customer.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id'          => [ 'type' => 'integer', 'description' => __( 'Customer ID.', 'erp' ) ],
                        'first_name'  => [ 'type' => 'string', 'description' => __( 'First name.', 'erp' ) ],
                        'last_name'   => [ 'type' => 'string', 'description' => __( 'Last name.', 'erp' ) ],
                        'email'       => [ 'type' => 'string', 'format' => 'email', 'description' => __( 'Email address.', 'erp' ) ],
                        'phone'       => [ 'type' => 'string', 'description' => __( 'Phone number.', 'erp' ) ],
                        'company'     => [ 'type' => 'string', 'description' => __( 'Company name.', 'erp' ) ],
                        'address_1'   => [ 'type' => 'string', 'description' => __( 'Street address.', 'erp' ) ],
                        'city'        => [ 'type' => 'string', 'description' => __( 'City.', 'erp' ) ],
                        'country'     => [ 'type' => 'string', 'description' => __( 'Country code.', 'erp' ) ],
                        'currency'    => [ 'type' => 'string', 'description' => __( 'Currency code.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id'      => [ 'type' => 'integer' ],
                        'updated' => [ 'type' => 'boolean' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_edit_customer' );
                },
                'execute_callback' => function ( $input ) {
                    $input['type'] = 'customer';
                    $result        = erp_insert_people( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $input['id'], 'updated' => true ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-delete-customer',
            [
                'label'        => __( 'Delete Customer', 'erp' ),
                'description'  => __( 'Delete an accounting customer.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id'   => [ 'type' => 'integer', 'description' => __( 'Customer ID.', 'erp' ) ],
                        'hard' => [ 'type' => 'boolean', 'description' => __( 'Hard-delete permanently.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'deleted' => [ 'type' => 'boolean' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_delete_customer' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_delete_people(
                        [
                            'id'   => (int) $input['id'],
                            'hard' => ! empty( $input['hard'] ) ? 1 : 0,
                        ]
                    );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'deleted' => true ];
                },
            ]
        );

        // ── Vendors ──────────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/ac-list-vendors',
            [
                'label'        => __( 'List Vendors', 'erp' ),
                'description'  => __( 'Retrieve a paginated list of accounting vendors.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number'  => [ 'type' => 'integer', 'description' => __( 'Number per page.', 'erp' ) ],
                        'offset'  => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                        'orderby' => [ 'type' => 'string', 'description' => __( 'Order by field.', 'erp' ) ],
                        'order'   => [ 'type' => 'string', 'enum' => [ 'ASC', 'DESC' ], 'description' => __( 'Sort order.', 'erp' ) ],
                        'search'  => [ 'type' => 'string', 'description' => __( 'Search keyword.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'vendors' => [ 'type' => 'array', 'description' => __( 'Array of vendor objects.', 'erp' ) ],
                        'total'   => [ 'type' => 'integer' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_vendor' );
                },
                'execute_callback' => function ( $input ) {
                    $args = wp_parse_args(
                        $input,
                        [
                            'number'  => 20,
                            'offset'  => 0,
                            'orderby' => 'first_name',
                            'order'   => 'ASC',
                            'type'    => 'vendor',
                            'count'   => false,
                        ]
                    );
                    $vendors       = erp_get_peoples( $args );
                    $args['count'] = true;
                    $total         = erp_get_peoples( $args );

                    return [
                        'vendors' => is_array( $vendors ) ? array_values( $vendors ) : [],
                        'total'   => (int) $total,
                    ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-get-vendor',
            [
                'label'        => __( 'Get Vendor', 'erp' ),
                'description'  => __( 'Retrieve a single accounting vendor by ID.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Vendor people ID.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Vendor object.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_single_vendor' );
                },
                'execute_callback' => function ( $input ) {
                    $vendor = erp_get_people( (int) $input['id'] );

                    if ( ! $vendor ) {
                        return new \WP_Error( 'not_found', __( 'Vendor not found.', 'erp' ), [ 'status' => 404 ] );
                    }

                    return is_object( $vendor ) ? (array) $vendor : $vendor;
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-create-vendor',
            [
                'label'        => __( 'Create Vendor', 'erp' ),
                'description'  => __( 'Create a new accounting vendor.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'first_name', 'email' ],
                    'properties' => [
                        'first_name'  => [ 'type' => 'string', 'description' => __( 'First name.', 'erp' ) ],
                        'last_name'   => [ 'type' => 'string', 'description' => __( 'Last name.', 'erp' ) ],
                        'email'       => [ 'type' => 'string', 'format' => 'email', 'description' => __( 'Email address.', 'erp' ) ],
                        'phone'       => [ 'type' => 'string', 'description' => __( 'Phone number.', 'erp' ) ],
                        'company'     => [ 'type' => 'string', 'description' => __( 'Company name.', 'erp' ) ],
                        'address_1'   => [ 'type' => 'string', 'description' => __( 'Street address.', 'erp' ) ],
                        'city'        => [ 'type' => 'string', 'description' => __( 'City.', 'erp' ) ],
                        'country'     => [ 'type' => 'string', 'description' => __( 'Country code.', 'erp' ) ],
                        'currency'    => [ 'type' => 'string', 'description' => __( 'Currency code.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'New vendor ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_create_vendor' );
                },
                'execute_callback' => function ( $input ) {
                    $input['type'] = 'vendor';
                    $result        = erp_insert_people( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $result ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-update-vendor',
            [
                'label'        => __( 'Update Vendor', 'erp' ),
                'description'  => __( 'Update an existing accounting vendor.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id'         => [ 'type' => 'integer', 'description' => __( 'Vendor ID.', 'erp' ) ],
                        'first_name' => [ 'type' => 'string', 'description' => __( 'First name.', 'erp' ) ],
                        'last_name'  => [ 'type' => 'string', 'description' => __( 'Last name.', 'erp' ) ],
                        'email'      => [ 'type' => 'string', 'format' => 'email', 'description' => __( 'Email address.', 'erp' ) ],
                        'phone'      => [ 'type' => 'string', 'description' => __( 'Phone number.', 'erp' ) ],
                        'company'    => [ 'type' => 'string', 'description' => __( 'Company name.', 'erp' ) ],
                        'address_1'  => [ 'type' => 'string', 'description' => __( 'Street address.', 'erp' ) ],
                        'city'       => [ 'type' => 'string', 'description' => __( 'City.', 'erp' ) ],
                        'country'    => [ 'type' => 'string', 'description' => __( 'Country code.', 'erp' ) ],
                        'currency'   => [ 'type' => 'string', 'description' => __( 'Currency code.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id'      => [ 'type' => 'integer' ],
                        'updated' => [ 'type' => 'boolean' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_edit_vendor' );
                },
                'execute_callback' => function ( $input ) {
                    $input['type'] = 'vendor';
                    $result        = erp_insert_people( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $input['id'], 'updated' => true ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-delete-vendor',
            [
                'label'        => __( 'Delete Vendor', 'erp' ),
                'description'  => __( 'Delete an accounting vendor.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id'   => [ 'type' => 'integer', 'description' => __( 'Vendor ID.', 'erp' ) ],
                        'hard' => [ 'type' => 'boolean', 'description' => __( 'Hard-delete permanently.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'deleted' => [ 'type' => 'boolean' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_delete_vendor' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_delete_people(
                        [
                            'id'   => (int) $input['id'],
                            'hard' => ! empty( $input['hard'] ) ? 1 : 0,
                        ]
                    );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'deleted' => true ];
                },
            ]
        );

        // ── Chart of Accounts ────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/ac-list-accounts',
            [
                'label'        => __( 'List Ledger Accounts', 'erp' ),
                'description'  => __( 'Retrieve chart of accounts (ledger accounts).', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number'    => [ 'type' => 'integer', 'description' => __( 'Number per page.', 'erp' ) ],
                        'offset'    => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                        'type_id'   => [ 'type' => 'integer', 'description' => __( 'Account type ID filter.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'accounts' => [ 'type' => 'array', 'description' => __( 'Array of ledger account objects.', 'erp' ) ],
                        'total'    => [ 'type' => 'integer' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_account_lists' );
                },
                'execute_callback' => function ( $input ) {
                    global $wpdb;

                    $number  = isset( $input['number'] ) ? (int) $input['number'] : 20;
                    $offset  = isset( $input['offset'] ) ? (int) $input['offset'] : 0;
                    $table   = esc_sql( $wpdb->prefix . 'erp_acct_ledger' );
                    $where   = '';

                    if ( ! empty( $input['type_id'] ) ) {
                        $where = $wpdb->prepare( ' WHERE type_id = %d', (int) $input['type_id'] );
                    }

                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $accounts = $wpdb->get_results(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        $wpdb->prepare( "SELECT * FROM {$table}{$where} ORDER BY id ASC LIMIT %d OFFSET %d", $number, $offset ),
                        ARRAY_A
                    );
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}{$where}" );

                    return [
                        'accounts' => is_array( $accounts ) ? $accounts : [],
                        'total'    => $total,
                    ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/ac-create-account',
            [
                'label'        => __( 'Create Ledger Account', 'erp' ),
                'description'  => __( 'Create a new chart-of-accounts entry.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'name', 'type_id' ],
                    'properties' => [
                        'name'        => [ 'type' => 'string', 'description' => __( 'Account name.', 'erp' ) ],
                        'type_id'     => [ 'type' => 'integer', 'description' => __( 'Account type ID.', 'erp' ) ],
                        'code'        => [ 'type' => 'string', 'description' => __( 'Account code.', 'erp' ) ],
                        'description' => [ 'type' => 'string', 'description' => __( 'Account description.', 'erp' ) ],
                        'currency'    => [ 'type' => 'string', 'description' => __( 'Currency code.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'New ledger account ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_create_account' );
                },
                'execute_callback' => function ( $input ) {
                    global $wpdb;

                    $data = [
                        'name'        => sanitize_text_field( $input['name'] ),
                        'type_id'     => (int) $input['type_id'],
                        'code'        => isset( $input['code'] ) ? sanitize_text_field( $input['code'] ) : '',
                        'description' => isset( $input['description'] ) ? sanitize_textarea_field( $input['description'] ) : '',
                        'currency'    => isset( $input['currency'] ) ? sanitize_text_field( $input['currency'] ) : '',
                        'created_at'  => current_time( 'mysql' ),
                    ];

                    $table = $wpdb->prefix . 'erp_acct_ledger';
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    $wpdb->insert( $table, $data );

                    return [ 'id' => (int) $wpdb->insert_id ];
                },
            ]
        );

        // ── Journals ─────────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/ac-list-journals',
            [
                'label'        => __( 'List Journal Entries', 'erp' ),
                'description'  => __( 'Retrieve accounting journal entries.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number'     => [ 'type' => 'integer', 'description' => __( 'Number per page.', 'erp' ) ],
                        'offset'     => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                        'start_date' => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Filter from date (YYYY-MM-DD).', 'erp' ) ],
                        'end_date'   => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Filter to date (YYYY-MM-DD).', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'journals' => [ 'type' => 'array', 'description' => __( 'Array of journal objects.', 'erp' ) ],
                        'total'    => [ 'type' => 'integer' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_journal' );
                },
                'execute_callback' => function ( $input ) {
                    global $wpdb;

                    $number = isset( $input['number'] ) ? (int) $input['number'] : 20;
                    $offset = isset( $input['offset'] ) ? (int) $input['offset'] : 0;
                    $table  = esc_sql( $wpdb->prefix . 'erp_acct_journals' );

                    $where_clauses = [];
                    $params        = [];

                    if ( ! empty( $input['start_date'] ) ) {
                        $where_clauses[] = 'trn_date >= %s';
                        $params[]        = sanitize_text_field( $input['start_date'] );
                    }

                    if ( ! empty( $input['end_date'] ) ) {
                        $where_clauses[] = 'trn_date <= %s';
                        $params[]        = sanitize_text_field( $input['end_date'] );
                    }

                    $where = ! empty( $where_clauses ) ? ' WHERE ' . implode( ' AND ', $where_clauses ) : '';
                    $params[] = $number;
                    $params[] = $offset;

                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
                    $journals = $wpdb->get_results(
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        $wpdb->prepare( "SELECT * FROM {$table}{$where} ORDER BY id DESC LIMIT %d OFFSET %d", ...$params ),
                        ARRAY_A
                    );
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $count_sql = "SELECT COUNT(*) FROM {$table}{$where}";
                    $count_params = array_slice( $params, 0, -2 );
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                    $total = empty( $count_params )
                        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" )
                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                        : (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$count_params ) );

                    return [
                        'journals' => is_array( $journals ) ? $journals : [],
                        'total'    => $total,
                    ];
                },
            ]
        );

        // ── Bank Accounts ─────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/ac-list-bank-accounts',
            [
                'label'        => __( 'List Bank Accounts', 'erp' ),
                'description'  => __( 'Retrieve accounting bank accounts.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number' => [ 'type' => 'integer', 'description' => __( 'Number per page.', 'erp' ) ],
                        'offset' => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'bank_accounts' => [ 'type' => 'array', 'description' => __( 'Array of bank account objects.', 'erp' ) ],
                        'total'         => [ 'type' => 'integer' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_bank_accounts' );
                },
                'execute_callback' => function ( $input ) {
                    global $wpdb;

                    $number = isset( $input['number'] ) ? (int) $input['number'] : 20;
                    $offset = isset( $input['offset'] ) ? (int) $input['offset'] : 0;
                    $table  = esc_sql( $wpdb->prefix . 'erp_acct_bank_accounts' );

                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    $accounts = $wpdb->get_results(
                        $wpdb->prepare( "SELECT * FROM {$table} ORDER BY id ASC LIMIT %d OFFSET %d", $number, $offset ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                        ARRAY_A
                    );
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
                    $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

                    return [
                        'bank_accounts' => is_array( $accounts ) ? $accounts : [],
                        'total'         => $total,
                    ];
                },
            ]
        );

        // ── Sales (Invoices & Payments) ───────────────────────────────────────

        wp_register_ability(
            'wp-erp/ac-view-sales-summary',
            [
                'label'        => __( 'View Sales Summary', 'erp' ),
                'description'  => __( 'Retrieve a summary of accounting sales transactions.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'start_date' => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Period start date.', 'erp' ) ],
                        'end_date'   => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Period end date.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Sales summary data.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
                'execute_callback' => function ( $input ) {
                    global $wpdb;

                    $start_date = ! empty( $input['start_date'] ) ? sanitize_text_field( $input['start_date'] ) : wp_date( 'Y-01-01' );
                    $end_date   = ! empty( $input['end_date'] ) ? sanitize_text_field( $input['end_date'] ) : wp_date( 'Y-m-d' );
                    $table      = esc_sql( $wpdb->prefix . 'erp_acct_invoices' );

                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    $summary = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT COUNT(*) AS count, SUM(total) AS total, SUM(amount_due) AS outstanding FROM {$table} WHERE trn_date BETWEEN %s AND %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                            $start_date,
                            $end_date
                        ),
                        ARRAY_A
                    );

                    return is_array( $summary ) ? $summary : [];
                },
            ]
        );

        // ── Expenses ─────────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/ac-view-expenses-summary',
            [
                'label'        => __( 'View Expenses Summary', 'erp' ),
                'description'  => __( 'Retrieve a summary of accounting expenses.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'start_date' => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Period start date.', 'erp' ) ],
                        'end_date'   => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Period end date.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Expenses summary data.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_expenses_summary' );
                },
                'execute_callback' => function ( $input ) {
                    global $wpdb;

                    $start_date = ! empty( $input['start_date'] ) ? sanitize_text_field( $input['start_date'] ) : wp_date( 'Y-01-01' );
                    $end_date   = ! empty( $input['end_date'] ) ? sanitize_text_field( $input['end_date'] ) : wp_date( 'Y-m-d' );
                    $table      = esc_sql( $wpdb->prefix . 'erp_acct_expense_checks' );

                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    $summary = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT COUNT(*) AS count, SUM(amount) AS total FROM {$table} WHERE trn_date BETWEEN %s AND %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                            $start_date,
                            $end_date
                        ),
                        ARRAY_A
                    );

                    return is_array( $summary ) ? $summary : [];
                },
            ]
        );

        // ── Reports ───────────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/ac-view-reports',
            [
                'label'        => __( 'View Accounting Reports', 'erp' ),
                'description'  => __( 'Retrieve available accounting report types and their meta information.', 'erp' ),
                'category'     => 'wp-erp-accounting',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [],
                ],
                'output_schema' => [
                    'type'        => 'array',
                    'description' => __( 'Array of available report type identifiers.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_ac_view_reports' );
                },
                'execute_callback' => function ( $input ) {
                    return apply_filters(
                        'erp_ac_abilities_report_types',
                        [
                            [ 'id' => 'balance_sheet',     'label' => __( 'Balance Sheet', 'erp' ) ],
                            [ 'id' => 'trial_balance',     'label' => __( 'Trial Balance', 'erp' ) ],
                            [ 'id' => 'income_statement',  'label' => __( 'Income Statement', 'erp' ) ],
                            [ 'id' => 'cash_flow',         'label' => __( 'Cash Flow Statement', 'erp' ) ],
                            [ 'id' => 'sales_tax',         'label' => __( 'Sales Tax Report', 'erp' ) ],
                            [ 'id' => 'journal',           'label' => __( 'Journal Report', 'erp' ) ],
                        ]
                    );
                },
            ]
        );
    }
}
