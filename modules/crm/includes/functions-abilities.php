<?php

/**
 * CRM Abilities
 *
 * Registers WP ERP Customer Relationship Management operations as abilities
 * via the WordPress Abilities API, making them discoverable and callable
 * by MCP (Model Context Protocol) clients and AI assistants.
 *
 * Requires WordPress 6.9+ (Abilities API).
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the CRM ability category.
 */
add_action( 'wp_abilities_api_categories_init', 'erp_crm_register_ability_category' );

if ( ! function_exists( 'erp_crm_register_ability_category' ) ) {
    function erp_crm_register_ability_category() {
        if ( ! function_exists( 'wp_register_ability_category' ) ) {
            return;
        }

        wp_register_ability_category(
            'wp-erp-crm',
            [
                'label'       => __( 'WP ERP — CRM', 'erp' ),
                'description' => __( 'Abilities for managing contacts, groups, activities, and schedules in WP ERP CRM.', 'erp' ),
            ]
        );
    }
}

/**
 * Register all CRM abilities.
 */
add_action( 'wp_abilities_api_init', 'erp_crm_register_abilities' );

if ( ! function_exists( 'erp_crm_register_abilities' ) ) {
    function erp_crm_register_abilities() {
        if ( ! function_exists( 'wp_register_ability' ) ) {
            return;
        }

        // ── Contacts ────────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/crm-list-contacts',
            [
                'label'        => __( 'List CRM Contacts', 'erp' ),
                'description'  => __( 'Retrieve a paginated list of CRM contacts.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number'     => [ 'type' => 'integer', 'description' => __( 'Number of contacts per page.', 'erp' ) ],
                        'offset'     => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                        'type'       => [ 'type' => 'string', 'enum' => [ 'contact', 'company' ], 'description' => __( 'Contact type filter.', 'erp' ) ],
                        'life_stage' => [ 'type' => 'string', 'description' => __( 'Life stage filter.', 'erp' ) ],
                        'search'     => [ 'type' => 'string', 'description' => __( 'Search keyword.', 'erp' ) ],
                        'orderby'    => [ 'type' => 'string', 'description' => __( 'Field to order by.', 'erp' ) ],
                        'order'      => [ 'type' => 'string', 'enum' => [ 'ASC', 'DESC' ], 'description' => __( 'Sort order.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'contacts' => [ 'type' => 'array', 'description' => __( 'Array of contact objects.', 'erp' ) ],
                        'total'    => [ 'type' => 'integer', 'description' => __( 'Total matching contacts.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_crm_list_contact' );
                },
                'execute_callback' => function ( $input ) {
                    $args = wp_parse_args(
                        $input,
                        [
                            'number'  => 20,
                            'offset'  => 0,
                            'type'    => 'contact',
                            'orderby' => 'first_name',
                            'order'   => 'ASC',
                            'count'   => false,
                        ]
                    );
                    $contacts         = erp_get_peoples( $args );
                    $args['count']    = true;
                    $total            = erp_get_peoples( $args );

                    return [
                        'contacts' => is_array( $contacts ) ? array_values( $contacts ) : [],
                        'total'    => (int) $total,
                    ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/crm-get-contact',
            [
                'label'        => __( 'Get CRM Contact', 'erp' ),
                'description'  => __( 'Retrieve a single CRM contact by ID.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Contact (people) ID.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Contact object.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_crm_list_contact' );
                },
                'execute_callback' => function ( $input ) {
                    $contact = erp_get_people( (int) $input['id'] );

                    if ( ! $contact ) {
                        return new \WP_Error( 'not_found', __( 'Contact not found.', 'erp' ), [ 'status' => 404 ] );
                    }

                    return is_object( $contact ) ? (array) $contact : $contact;
                },
            ]
        );

        wp_register_ability(
            'wp-erp/crm-create-contact',
            [
                'label'        => __( 'Create CRM Contact', 'erp' ),
                'description'  => __( 'Create a new CRM contact or company.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'first_name', 'email' ],
                    'properties' => [
                        'first_name'  => [ 'type' => 'string', 'description' => __( 'First name.', 'erp' ) ],
                        'last_name'   => [ 'type' => 'string', 'description' => __( 'Last name.', 'erp' ) ],
                        'email'       => [ 'type' => 'string', 'format' => 'email', 'description' => __( 'Email address.', 'erp' ) ],
                        'phone'       => [ 'type' => 'string', 'description' => __( 'Phone number.', 'erp' ) ],
                        'company'     => [ 'type' => 'string', 'description' => __( 'Company name.', 'erp' ) ],
                        'type'        => [ 'type' => 'string', 'enum' => [ 'contact', 'company' ], 'description' => __( 'Contact type.', 'erp' ) ],
                        'life_stage'  => [ 'type' => 'string', 'description' => __( 'CRM life stage.', 'erp' ) ],
                        'address_1'   => [ 'type' => 'string', 'description' => __( 'Street address.', 'erp' ) ],
                        'city'        => [ 'type' => 'string', 'description' => __( 'City.', 'erp' ) ],
                        'country'     => [ 'type' => 'string', 'description' => __( 'Country code.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'New contact ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_crm_add_contact' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_insert_people( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $result ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/crm-update-contact',
            [
                'label'        => __( 'Update CRM Contact', 'erp' ),
                'description'  => __( 'Update an existing CRM contact.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id'         => [ 'type' => 'integer', 'description' => __( 'Contact ID.', 'erp' ) ],
                        'first_name' => [ 'type' => 'string', 'description' => __( 'First name.', 'erp' ) ],
                        'last_name'  => [ 'type' => 'string', 'description' => __( 'Last name.', 'erp' ) ],
                        'email'      => [ 'type' => 'string', 'format' => 'email', 'description' => __( 'Email address.', 'erp' ) ],
                        'phone'      => [ 'type' => 'string', 'description' => __( 'Phone number.', 'erp' ) ],
                        'life_stage' => [ 'type' => 'string', 'description' => __( 'CRM life stage.', 'erp' ) ],
                        'address_1'  => [ 'type' => 'string', 'description' => __( 'Street address.', 'erp' ) ],
                        'city'       => [ 'type' => 'string', 'description' => __( 'City.', 'erp' ) ],
                        'country'    => [ 'type' => 'string', 'description' => __( 'Country code.', 'erp' ) ],
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
                    return current_user_can( 'erp_crm_edit_contact' );
                },
                'execute_callback' => function ( $input ) {
                    $id = (int) $input['id'];
                    unset( $input['id'] );
                    $input['id'] = $id;

                    $result = erp_insert_people( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => $id, 'updated' => true ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/crm-delete-contact',
            [
                'label'        => __( 'Delete CRM Contact', 'erp' ),
                'description'  => __( 'Delete a CRM contact.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id'   => [ 'type' => 'integer', 'description' => __( 'Contact ID.', 'erp' ) ],
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
                    return current_user_can( 'erp_crm_delete_contact' );
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

        // ── Contact Groups ──────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/crm-list-groups',
            [
                'label'        => __( 'List CRM Contact Groups', 'erp' ),
                'description'  => __( 'Retrieve all CRM contact groups.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number' => [ 'type' => 'integer', 'description' => __( 'Number of results.', 'erp' ) ],
                        'offset' => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'array',
                    'description' => __( 'Array of contact group objects.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_crm_manage_groups' );
                },
                'execute_callback' => function ( $input ) {
                    return erp_crm_get_contact_groups( wp_parse_args( $input, [] ) );
                },
            ]
        );

        wp_register_ability(
            'wp-erp/crm-create-group',
            [
                'label'        => __( 'Create CRM Contact Group', 'erp' ),
                'description'  => __( 'Create a new CRM contact group.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'name' ],
                    'properties' => [
                        'name'        => [ 'type' => 'string', 'description' => __( 'Group name.', 'erp' ) ],
                        'description' => [ 'type' => 'string', 'description' => __( 'Group description.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Created group ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_crm_create_groups' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_crm_save_contact_group( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $result ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/crm-delete-group',
            [
                'label'        => __( 'Delete CRM Contact Group', 'erp' ),
                'description'  => __( 'Delete a CRM contact group.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'id' ],
                    'properties' => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Group ID to delete.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'deleted' => [ 'type' => 'boolean' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_crm_delete_groups' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_crm_contact_group_delete( (int) $input['id'] );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'deleted' => true ];
                },
            ]
        );

        // ── Activities ──────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/crm-list-activities',
            [
                'label'        => __( 'List CRM Activities', 'erp' ),
                'description'  => __( 'Retrieve activity feed for a CRM contact.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'contact_id' ],
                    'properties' => [
                        'contact_id' => [ 'type' => 'integer', 'description' => __( 'Contact ID to retrieve activity for.', 'erp' ) ],
                        'type'       => [ 'type' => 'string', 'description' => __( 'Activity type filter.', 'erp' ) ],
                        'number'     => [ 'type' => 'integer', 'description' => __( 'Number of results.', 'erp' ) ],
                        'offset'     => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'array',
                    'description' => __( 'Array of activity objects.', 'erp' ),
                ],
                'permission_callback' => function () {
                    // Note: 'erp_crm_manage_activites' is the canonical capability name as defined
                    // in functions-capabilities.php; the typo is preserved intentionally.
                    return current_user_can( 'erp_crm_manage_activites' );
                },
                'execute_callback' => function ( $input ) {
                    return erp_crm_get_feed_activity( wp_parse_args( $input, [] ) );
                },
            ]
        );

        // ── Schedules ───────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/crm-list-schedules',
            [
                'label'        => __( 'List CRM Schedules', 'erp' ),
                'description'  => __( 'Retrieve scheduled activities for a CRM contact.', 'erp' ),
                'category'     => 'wp-erp-crm',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'tab'    => [ 'type' => 'string', 'description' => __( 'Schedule tab (today, upcoming, all).', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'array',
                    'description' => __( 'Array of scheduled activity objects.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_crm_manage_schedules' );
                },
                'execute_callback' => function ( $input ) {
                    $tab = isset( $input['tab'] ) ? sanitize_text_field( $input['tab'] ) : 'today';

                    return erp_crm_get_schedule_data( $tab );
                },
            ]
        );
    }
}
