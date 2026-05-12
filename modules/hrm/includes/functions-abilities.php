<?php

/**
 * HR Abilities
 *
 * Registers WP ERP Human Resource Management operations as abilities
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
 * Register the HRM ability category.
 */
add_action( 'wp_abilities_api_categories_init', 'erp_hrm_register_ability_category' );

if ( ! function_exists( 'erp_hrm_register_ability_category' ) ) {
    function erp_hrm_register_ability_category() {
        if ( ! function_exists( 'wp_register_ability_category' ) ) {
            return;
        }

        wp_register_ability_category(
            'wp-erp-hrm',
            [
                'label'       => __( 'WP ERP — Human Resources', 'erp' ),
                'description' => __( 'Abilities for managing employees, departments, designations, leave, and announcements in WP ERP HRM.', 'erp' ),
            ]
        );
    }
}

/**
 * Register all HRM abilities.
 */
add_action( 'wp_abilities_api_init', 'erp_hrm_register_abilities' );

if ( ! function_exists( 'erp_hrm_register_abilities' ) ) {
    function erp_hrm_register_abilities() {
        if ( ! function_exists( 'wp_register_ability' ) ) {
            return;
        }

        // ── Employees ──────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/hrm-list-employees',
            [
                'label'        => __( 'List Employees', 'erp' ),
                'description'  => __( 'Retrieve a paginated list of employees.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number'     => [ 'type' => 'integer', 'description' => __( 'Number of employees per page.', 'erp' ) ],
                        'offset'     => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                        'orderby'    => [ 'type' => 'string', 'description' => __( 'Field to order by.', 'erp' ) ],
                        'order'      => [ 'type' => 'string', 'enum' => [ 'ASC', 'DESC' ], 'description' => __( 'Sort order.', 'erp' ) ],
                        'status'     => [ 'type' => 'string', 'description' => __( 'Filter by employee status (active, inactive, terminated).', 'erp' ) ],
                        'department' => [ 'type' => 'integer', 'description' => __( 'Filter by department ID.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Employee list result.', 'erp' ),
                    'properties'  => [
                        'employees' => [ 'type' => 'array', 'description' => __( 'Array of employee objects.', 'erp' ) ],
                        'total'     => [ 'type' => 'integer', 'description' => __( 'Total number of matching employees.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_list_employee' );
                },
                'execute_callback' => function ( $input ) {
                    $args      = wp_parse_args(
                        $input,
                        [
                            'number'  => 20,
                            'offset'  => 0,
                            'orderby' => 'user_login',
                            'order'   => 'ASC',
                            'status'  => 'active',
                            'count'   => false,
                        ]
                    );
                    $employees = erp_hr_get_employees( $args );
                    $args['count'] = true;
                    $total     = erp_hr_get_employees( $args );

                    return [
                        'employees' => is_array( $employees ) ? array_values( $employees ) : [],
                        'total'     => (int) $total,
                    ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-get-employee',
            [
                'label'        => __( 'Get Employee', 'erp' ),
                'description'  => __( 'Retrieve details of a single employee by user ID.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'user_id' ],
                    'properties' => [
                        'user_id' => [ 'type' => 'integer', 'description' => __( 'WordPress user ID of the employee.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Employee object.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_view_employee' );
                },
                'execute_callback' => function ( $input ) {
                    $employee = new \WeDevs\ERP\HRM\Employee( (int) $input['user_id'] );

                    if ( ! $employee->is_employee() ) {
                        return new \WP_Error( 'not_found', __( 'Employee not found.', 'erp' ), [ 'status' => 404 ] );
                    }

                    return $employee->to_array();
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-create-employee',
            [
                'label'        => __( 'Create Employee', 'erp' ),
                'description'  => __( 'Create a new employee record.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'first_name', 'last_name', 'email' ],
                    'properties' => [
                        'first_name'   => [ 'type' => 'string', 'description' => __( 'First name.', 'erp' ) ],
                        'last_name'    => [ 'type' => 'string', 'description' => __( 'Last name.', 'erp' ) ],
                        'email'        => [ 'type' => 'string', 'format' => 'email', 'description' => __( 'Email address.', 'erp' ) ],
                        'designation'  => [ 'type' => 'integer', 'description' => __( 'Designation ID.', 'erp' ) ],
                        'department'   => [ 'type' => 'integer', 'description' => __( 'Department ID.', 'erp' ) ],
                        'date_of_birth' => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Date of birth (YYYY-MM-DD).', 'erp' ) ],
                        'hiring_date'  => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Hiring date (YYYY-MM-DD).', 'erp' ) ],
                        'status'       => [ 'type' => 'string', 'description' => __( 'Employee status (active, inactive).', 'erp' ) ],
                        'type'         => [ 'type' => 'string', 'description' => __( 'Employee type.', 'erp' ) ],
                        'location'     => [ 'type' => 'integer', 'description' => __( 'Office location ID.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Created employee object.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_create_employee' );
                },
                'execute_callback' => function ( $input ) {
                    $args = [
                        'user_email' => isset( $input['email'] ) ? $input['email'] : '',
                        'personal'   => [
                            'first_name'    => isset( $input['first_name'] ) ? $input['first_name'] : '',
                            'last_name'     => isset( $input['last_name'] ) ? $input['last_name'] : '',
                            'date_of_birth' => isset( $input['date_of_birth'] ) ? $input['date_of_birth'] : '',
                        ],
                        'work'       => [
                            'designation' => isset( $input['designation'] ) ? (int) $input['designation'] : 0,
                            'department'  => isset( $input['department'] ) ? (int) $input['department'] : 0,
                            'location'    => isset( $input['location'] ) ? (int) $input['location'] : 0,
                            'hiring_date' => isset( $input['hiring_date'] ) ? $input['hiring_date'] : '',
                            'status'      => isset( $input['status'] ) ? $input['status'] : 'active',
                            'type'        => isset( $input['type'] ) ? $input['type'] : '',
                        ],
                    ];

                    $result = erp_hr_employee_create( $args );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    if ( is_string( $result ) ) {
                        return new \WP_Error( 'create_failed', $result );
                    }

                    $employee = new \WeDevs\ERP\HRM\Employee( (int) $result );

                    return $employee->to_array();
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-update-employee',
            [
                'label'        => __( 'Update Employee', 'erp' ),
                'description'  => __( 'Update an existing employee record.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'user_id' ],
                    'properties' => [
                        'user_id'      => [ 'type' => 'integer', 'description' => __( 'WordPress user ID of the employee.', 'erp' ) ],
                        'first_name'   => [ 'type' => 'string', 'description' => __( 'First name.', 'erp' ) ],
                        'last_name'    => [ 'type' => 'string', 'description' => __( 'Last name.', 'erp' ) ],
                        'designation'  => [ 'type' => 'integer', 'description' => __( 'Designation ID.', 'erp' ) ],
                        'department'   => [ 'type' => 'integer', 'description' => __( 'Department ID.', 'erp' ) ],
                        'status'       => [ 'type' => 'string', 'description' => __( 'Employee status.', 'erp' ) ],
                        'type'         => [ 'type' => 'string', 'description' => __( 'Employee type.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'description' => __( 'Updated employee object.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_edit_employee' );
                },
                'execute_callback' => function ( $input ) {
                    $user_id  = (int) $input['user_id'];
                    $employee = new \WeDevs\ERP\HRM\Employee( $user_id );

                    if ( ! $employee->is_employee() ) {
                        return new \WP_Error( 'not_found', __( 'Employee not found.', 'erp' ), [ 'status' => 404 ] );
                    }

                    unset( $input['user_id'] );
                    $employee->update_employee( $input );

                    return ( new \WeDevs\ERP\HRM\Employee( $user_id ) )->to_array();
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-delete-employee',
            [
                'label'        => __( 'Delete Employee', 'erp' ),
                'description'  => __( 'Delete an employee record.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'user_id' ],
                    'properties' => [
                        'user_id' => [ 'type' => 'integer', 'description' => __( 'WordPress user ID of the employee.', 'erp' ) ],
                        'hard'    => [ 'type' => 'boolean', 'description' => __( 'Whether to hard-delete (true) or soft-delete (false).', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'deleted' => [ 'type' => 'boolean' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_delete_employee' );
                },
                'execute_callback' => function ( $input ) {
                    $user_id = (int) $input['user_id'];
                    $hard    = ! empty( $input['hard'] ) ? 1 : 0;

                    erp_hr_employee_on_delete( $user_id, $hard );

                    return [ 'deleted' => true ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-terminate-employee',
            [
                'label'        => __( 'Terminate Employee', 'erp' ),
                'description'  => __( 'Terminate an active employee.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'user_id', 'terminated_date' ],
                    'properties' => [
                        'user_id'         => [ 'type' => 'integer', 'description' => __( 'WordPress user ID.', 'erp' ) ],
                        'terminated_date' => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Date of termination (YYYY-MM-DD).', 'erp' ) ],
                        'reason'          => [ 'type' => 'string', 'description' => __( 'Reason for termination.', 'erp' ) ],
                        'type'            => [ 'type' => 'string', 'description' => __( 'Type of termination.', 'erp' ) ],
                        'rehire'          => [ 'type' => 'string', 'description' => __( 'Rehire eligibility.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'terminated' => [ 'type' => 'boolean' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_can_terminate' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_hr_employee_terminate( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'terminated' => true ];
                },
            ]
        );

        // ── Departments ─────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/hrm-list-departments',
            [
                'label'        => __( 'List Departments', 'erp' ),
                'description'  => __( 'Retrieve all departments.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number' => [ 'type' => 'integer', 'description' => __( 'Number of results.', 'erp' ) ],
                        'offset' => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'array',
                    'description' => __( 'Array of department objects.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_view_list' );
                },
                'execute_callback' => function ( $input ) {
                    return erp_hr_get_departments( wp_parse_args( $input, [] ) );
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-manage-department',
            [
                'label'        => __( 'Manage Department', 'erp' ),
                'description'  => __( 'Create or update a department.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'title' ],
                    'properties' => [
                        'id'          => [ 'type' => 'integer', 'description' => __( 'Department ID (omit to create new).', 'erp' ) ],
                        'title'       => [ 'type' => 'string', 'description' => __( 'Department title.', 'erp' ) ],
                        'description' => [ 'type' => 'string', 'description' => __( 'Description.', 'erp' ) ],
                        'lead'        => [ 'type' => 'integer', 'description' => __( 'Department lead user ID.', 'erp' ) ],
                        'parent'      => [ 'type' => 'integer', 'description' => __( 'Parent department ID.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Department ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_manage_department' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_hr_create_department( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $result ];
                },
            ]
        );

        // ── Designations ────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/hrm-list-designations',
            [
                'label'        => __( 'List Designations', 'erp' ),
                'description'  => __( 'Retrieve all job designations.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number' => [ 'type' => 'integer', 'description' => __( 'Number of results.', 'erp' ) ],
                        'offset' => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'array',
                    'description' => __( 'Array of designation objects.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_view_list' );
                },
                'execute_callback' => function ( $input ) {
                    return erp_hr_get_designations( wp_parse_args( $input, [] ) );
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-manage-designation',
            [
                'label'        => __( 'Manage Designation', 'erp' ),
                'description'  => __( 'Create or update a job designation.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'title' ],
                    'properties' => [
                        'id'          => [ 'type' => 'integer', 'description' => __( 'Designation ID (omit to create new).', 'erp' ) ],
                        'title'       => [ 'type' => 'string', 'description' => __( 'Designation title.', 'erp' ) ],
                        'description' => [ 'type' => 'string', 'description' => __( 'Description.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Designation ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_manage_designation' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_hr_create_designation( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $result ];
                },
            ]
        );

        // ── Leave ───────────────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/hrm-create-leave-request',
            [
                'label'        => __( 'Create Leave Request', 'erp' ),
                'description'  => __( 'Submit a new leave request.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'user_id', 'leave_id', 'start_date', 'end_date' ],
                    'properties' => [
                        'user_id'    => [ 'type' => 'integer', 'description' => __( 'Employee user ID.', 'erp' ) ],
                        'leave_id'   => [ 'type' => 'integer', 'description' => __( 'Leave policy ID.', 'erp' ) ],
                        'start_date' => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Leave start date (YYYY-MM-DD).', 'erp' ) ],
                        'end_date'   => [ 'type' => 'string', 'format' => 'date', 'description' => __( 'Leave end date (YYYY-MM-DD).', 'erp' ) ],
                        'reason'     => [ 'type' => 'string', 'description' => __( 'Reason for leave.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Leave request ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_leave_create_request' );
                },
                'execute_callback' => function ( $input ) {
                    $result = erp_hr_leave_insert_request( $input );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'id' => (int) $result ];
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-manage-leave',
            [
                'label'        => __( 'Manage Leave Request', 'erp' ),
                'description'  => __( 'Approve or reject a leave request.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'request_id', 'status' ],
                    'properties' => [
                        'request_id' => [ 'type' => 'integer', 'description' => __( 'Leave request ID.', 'erp' ) ],
                        'status'     => [ 'type' => 'integer', 'enum' => [ 1, 2, 3 ], 'description' => __( 'Status: 1=Pending, 2=Approved, 3=Rejected.', 'erp' ) ],
                        'reason'     => [ 'type' => 'string', 'description' => __( 'Optional reason/note.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'updated' => [ 'type' => 'boolean' ],
                    ],
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_leave_manage' );
                },
                'execute_callback' => function ( $input ) {
                    $request_id = (int) $input['request_id'];

                    if ( ! \WeDevs\ERP\HRM\Models\LeaveRequest::find( $request_id ) ) {
                        return new WP_Error( 'no-request-found', __( 'Invalid leave request', 'erp' ) );
                    }

                    $result = erp_hr_leave_request_update_status(
                        $request_id,
                        (int) $input['status'],
                        ! empty( $input['reason'] ) ? $input['reason'] : ''
                    );

                    if ( is_wp_error( $result ) ) {
                        return $result;
                    }

                    return [ 'updated' => true ];
                },
            ]
        );

        // ── Announcements ───────────────────────────────────────────────────

        wp_register_ability(
            'wp-erp/hrm-list-announcements',
            [
                'label'        => __( 'List Announcements', 'erp' ),
                'description'  => __( 'Retrieve HR announcements.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'number' => [ 'type' => 'integer', 'description' => __( 'Number of results.', 'erp' ) ],
                        'offset' => [ 'type' => 'integer', 'description' => __( 'Pagination offset.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'array',
                    'description' => __( 'Array of announcement objects.', 'erp' ),
                ],
                'permission_callback' => function () {
                    return current_user_can( 'erp_view_announcement' );
                },
                'execute_callback' => function ( $input ) {
                    return erp_hr_get_announcements( wp_parse_args( $input, [] ) );
                },
            ]
        );

        wp_register_ability(
            'wp-erp/hrm-create-announcement',
            [
                'label'        => __( 'Create Announcement', 'erp' ),
                'description'  => __( 'Publish a new HR announcement.', 'erp' ),
                'category'     => 'wp-erp-hrm',
                'meta'         => [ 'mcp' => [ 'public' => true, 'type' => 'tool' ] ],
                'input_schema' => [
                    'type'       => 'object',
                    'required'   => [ 'title', 'message' ],
                    'properties' => [
                        'title'        => [ 'type' => 'string', 'description' => __( 'Announcement title.', 'erp' ) ],
                        'message'      => [ 'type' => 'string', 'description' => __( 'Announcement body.', 'erp' ) ],
                        'departments'  => [
                            'type'        => 'array',
                            'items'       => [ 'type' => 'integer' ],
                            'description' => __( 'Target department IDs (empty for all).', 'erp' ),
                        ],
                        'send_to_all'  => [ 'type' => 'boolean', 'description' => __( 'Send to all employees.', 'erp' ) ],
                    ],
                ],
                'output_schema' => [
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [ 'type' => 'integer', 'description' => __( 'Announcement post ID.', 'erp' ) ],
                    ],
                ],
                'permission_callback' => function () {
                    // Note: 'erp_crate_announcement' is the canonical capability name as defined
                    // in functions-capabilities.php; the typo is preserved intentionally.
                    return current_user_can( 'erp_crate_announcement' );
                },
                'execute_callback' => function ( $input ) {
                    $post_id = wp_insert_post(
                        [
                            'post_title'   => sanitize_text_field( $input['title'] ),
                            'post_content' => wp_kses_post( $input['message'] ),
                            'post_status'  => 'publish',
                            'post_type'    => 'erp_hr_announcement',
                            'post_author'  => get_current_user_id(),
                        ],
                        true
                    );

                    if ( is_wp_error( $post_id ) ) {
                        return $post_id;
                    }

                    if ( ! empty( $input['send_to_all'] ) ) {
                        $type     = 'all_employee';
                        $selected = [];
                    } elseif ( ! empty( $input['departments'] ) ) {
                        $type     = 'by_department';
                        $selected = array_map( 'intval', (array) $input['departments'] );
                    } else {
                        $type     = 'all_employee';
                        $selected = [];
                    }

                    if ( function_exists( 'erp_hr_assign_announcements_to_employees' ) ) {
                        erp_hr_assign_announcements_to_employees( $post_id, $type, $selected );
                    }

                    return [ 'id' => (int) $post_id ];
                },
            ]
        );
    }
}
