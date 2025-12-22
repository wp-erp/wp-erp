<?php

namespace WeDevs\ERP\HRM\API;

use WeDevs\ERP\API\REST_Controller;
use WeDevs\ERP\HRM\Employee;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class LeaveRequestsController extends REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'hrm/leaves/requests';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_leave_requests' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_leave_request' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_leave_manage' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_leave_request' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_leave_request'],
                'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
                'permission_callback' => function ($request) {
                    return current_user_can('erp_leave_manage');
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_leave_request'],
                'permission_callback' => function ($request) {
                    return current_user_can('erp_leave_manage');
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/approve', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'approve_leave_request'],
                'args'                => [
                    'comments' => [
                        'description'       => __('Comments for the approval.', 'erp'),
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
                'permission_callback' => function ($request) {
                    return current_user_can('erp_leave_manage');
                },
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/reject', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'reject_leave_request'],
                'args'                => [
                    'reason' => [
                        'description'       => __('Reason for the rejection.', 'erp'),
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
                'permission_callback' => function ($request) {
                    return current_user_can('erp_leave_manage');
                },
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/bulk-approve', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'bulk_approve_leave_requests'],
                'args'                => [
                    'ids' => [
                        'description' => __('Array of leave request IDs to approve.', 'erp'),
                        'type'        => 'array',
                        'required'    => true,
                        'items'       => [
                            'type' => 'integer',
                        ],
                    ],
                    'comments' => [
                        'description'       => __('Comments for the approval.', 'erp'),
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
                'permission_callback' => function ($request) {
                    return current_user_can('erp_leave_manage');
                },
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/bulk-reject', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'bulk_reject_leave_requests'],
                'args'                => [
                    'ids' => [
                        'description' => __('Array of leave request IDs to reject.', 'erp'),
                        'type'        => 'array',
                        'required'    => true,
                        'items'       => [
                            'type' => 'integer',
                        ],
                    ],
                    'reason' => [
                        'description'       => __('Reason for the rejection.', 'erp'),
                        'type'              => 'string',
                        'required'          => true,
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
                'permission_callback' => function ($request) {
                    return current_user_can('erp_leave_manage');
                },
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/bulk-delete', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'bulk_delete_leave_requests'],
                'args'                => [
                    'ids' => [
                        'description' => __('Array of leave request IDs to delete.', 'erp'),
                        'type'        => 'array',
                        'required'    => true,
                        'items'       => [
                            'type' => 'integer',
                        ],
                    ],
                ],
                'permission_callback' => function ($request) {
                    return current_user_can('erp_leave_manage');
                },
            ],
        ]);
    }

    /**
     * Get a collection of leave requests
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_leave_requests( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'type'   => $request['type'] == 'upcoming' ? 'upcoming' : '',
        ];

        $items           = [];
        $formatted_items = [];
        $total           = 0;

        if ( $args['type'] == 'upcoming' ) {
            $args['status']     = 1; // only approved leave request
            $args['start_date'] = erp_current_datetime()->setTime( 0, 0 )->getTimestamp(); //today
            $args['end_date']   = erp_current_datetime()->modify( 'last day of next month' )->setTime( 23, 59, 59 )->getTimestamp();
        }

        $args['status'] = $request['status'] ?? $args['status'] ?? '';
        $leave_requests = erp_hr_get_leave_requests( $args );
        $items          = $leave_requests['data'];
        $total          = $leave_requests['total'];

        $formatted_items = [];

        foreach ( $items as $item ) {
            $data              = $this->prepare_item_for_response( $item, $request );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total );

        return $response;
    }

    /**
     * Get a specific leave request
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_leave_request( $request ) {
        $id   = (int) $request['id'];
        $item = erp_hr_get_leave_request( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_leave_request_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create a leave request
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_leave_request( $request ) {
        $item = $this->prepare_item_for_database( $request->get_body_params() );
        $data = $request->get_body_params();

        $policies = erp_hr_get_assign_policy_from_entitlement( $data['employee_id'] );

        if ( ! $policies ) {
            return new WP_Error( 'rest_leave_request_required_entitlement', __( 'Set entitlement to the employee first.', 'erp' ), [ 'status' => 400 ] );
        }

        $id            = erp_hr_leave_insert_request( $item );
        $leave_request = erp_hr_get_leave_request( $id );

        $request->set_param( 'context', 'edit' );

        $response = $this->prepare_item_for_response( $leave_request, $data );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**     * Update a leave request
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_leave_request($request) {
        $id   = (int) $request['id'];
        $item = erp_hr_get_leave_request($id);

        if (empty($id) || empty($item->id)) {
            return new WP_Error('rest_leave_request_invalid_id', __('Invalid resource id.', 'erp'), ['status' => 404]);
        }

        // Check if leave request is already approved or rejected
        if (in_array($item->status, [1, 3])) {
            return new WP_Error('rest_leave_request_cannot_edit', __('Cannot edit approved or rejected leave requests.', 'erp'), ['status' => 400]);
        }

        $prepared_item = $this->prepare_item_for_database($request->get_body_params());
        $prepared_item['id'] = $id;

        // Validate employee entitlement if employee_id is being changed
        if (isset($prepared_item['user_id']) && $prepared_item['user_id'] != $item->user_id) {
            $policies = erp_hr_get_assign_policy_from_entitlement($prepared_item['user_id']);

            if (! $policies) {
                return new WP_Error('rest_leave_request_required_entitlement', __('Set entitlement to the employee first.', 'erp'), ['status' => 400]);
            }
        }

        $updated = erp_hr_leave_insert_request($prepared_item);

        if (is_wp_error($updated)) {
            return $updated;
        }

        $leave_request = erp_hr_get_leave_request($id);
        $response      = $this->prepare_item_for_response($leave_request, $request);

        return rest_ensure_response($response);
    }

    /**     * Approve a leave request
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function approve_leave_request($request) {
        $id   = (int) $request['id'];
        $item = erp_hr_get_leave_request($id);

        if (empty($id) || empty($item->id)) {
            return new WP_Error('rest_leave_request_invalid_id', __('Invalid resource id.', 'erp'), ['status' => 404]);
        }

        // Check if already approved
        if ($item->status == 1) {
            return new WP_Error('rest_leave_request_already_approved', __('Leave request is already approved.', 'erp'), ['status' => 400]);
        }

        $comments = isset($request['comments']) ? sanitize_textarea_field($request['comments']) : '';

         $updated = erp_hr_leave_request_update_status($id, 1, $comments);

        if (is_wp_error($updated)) {
            return $updated;
        }

        $leave_request = erp_hr_get_leave_request($id);
        $response      = $this->prepare_item_for_response($leave_request, $request);

        return rest_ensure_response($response);
    }

    /**
     * Reject a leave request
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function reject_leave_request($request) {
        $id   = (int) $request['id'];
        $item = erp_hr_get_leave_request($id);

        if (empty($id) || empty($item->id)) {
            return new WP_Error('rest_leave_request_invalid_id', __('Invalid resource id.', 'erp'), ['status' => 404]);
        }

        // Check if already rejected
        if ($item->status == 3) {
            return new WP_Error('rest_leave_request_already_rejected', __('Leave request is already rejected.', 'erp'), ['status' => 400]);
        }

        $reason = isset($request['reason']) ? sanitize_textarea_field($request['reason']) : '';

        if (empty($reason)) {
            return new WP_Error('rest_leave_request_missing_reason', __('Rejection reason is required.', 'erp'), ['status' => 400]);
        }


        // $updated = erp_hr_leave_request_update_status($id, $data);

        $updated = erp_hr_leave_request_update_status($id, 3, $reason);

        if (is_wp_error($updated)) {
            return $updated;
        }

        $leave_request = erp_hr_get_leave_request($id);
        $response      = $this->prepare_item_for_response($leave_request, $request);

        return rest_ensure_response($response);
    }

    /**
     * Delete a leave request
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function delete_leave_request($request) {
        $id   = (int) $request['id'];
        $item = erp_hr_get_leave_request($id);

        if (empty($id) || empty($item->id)) {
            return new WP_Error('rest_leave_request_invalid_id', __('Invalid resource id.', 'erp'), ['status' => 404]);
        }

        // Prepare response before deletion
        $previous = $this->prepare_item_for_response($item, $request);

        $deleted = erp_hr_delete_leave_request($id);

        if (! $deleted) {
            return new WP_Error('rest_cannot_delete', __('The leave request cannot be deleted.', 'erp'), ['status' => 500]);
        }

        $response = new WP_REST_Response();
        $response->set_data([
            'deleted'  => true,
            'previous' => $previous->get_data(),
        ]);

        return $response;
    }

    /**
     * Bulk approve leave requests
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function bulk_approve_leave_requests($request) {
        $ids = $request['ids'];

        if (empty($ids) || ! is_array($ids)) {
            return new WP_Error('rest_invalid_param', __('Invalid leave request IDs.', 'erp'), ['status' => 400]);
        }

        $comments = isset($request['comments']) ? sanitize_textarea_field($request['comments']) : '';

        $results = [
            'success' => [],
            'failed'  => [],
        ];

        foreach ($ids as $id) {
            $id   = absint($id);
            $item = erp_hr_get_leave_request($id);

            if (empty($item->id)) {
                $results['failed'][] = [
                    'id'      => $id,
                    'message' => __('Invalid leave request ID.', 'erp'),
                ];
                continue;
            }

            if ($item->status == 1) {
                $results['failed'][] = [
                    'id'      => $id,
                    'message' => __('Leave request is already approved.', 'erp'),
                ];
                continue;
            }



            $updated = erp_hr_leave_request_update_status($id, 1, $comments);

            if (is_wp_error($updated)) {
                $results['failed'][] = [
                    'id'      => $id,
                    'message' => $updated->get_error_message(),
                ];
            } else {
                $results['success'][] = $id;
            }
        }

        $response = new WP_REST_Response();
        $response->set_data([
            'success' => $results['success'],
            'failed'  => $results['failed'],
            'total'   => count($ids),
        ]);

        return $response;
    }

    /**
     * Bulk reject leave requests
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function bulk_reject_leave_requests($request) {
        $ids = $request['ids'];

        if (empty($ids) || ! is_array($ids)) {
            return new WP_Error('rest_invalid_param', __('Invalid leave request IDs.', 'erp'), ['status' => 400]);
        }

        $reason = isset($request['reason']) ? sanitize_textarea_field($request['reason']) : '';

        if (empty($reason)) {
            return new WP_Error('rest_leave_request_missing_reason', __('Rejection reason is required.', 'erp'), ['status' => 400]);
        }

        $results = [
            'success' => [],
            'failed'  => [],
        ];

        foreach ($ids as $id) {
            $id   = absint($id);
            $item = erp_hr_get_leave_request($id);

            if (empty($item->id)) {
                $results['failed'][] = [
                    'id'      => $id,
                    'message' => __('Invalid leave request ID.', 'erp'),
                ];
                continue;
            }

            if ($item->status == 3) {
                $results['failed'][] = [
                    'id'      => $id,
                    'message' => __('Leave request is already rejected.', 'erp'),
                ];
                continue;
            }


            $updated = erp_hr_leave_request_update_status($id, 3, $reason);

            if (is_wp_error($updated)) {
                $results['failed'][] = [
                    'id'      => $id,
                    'message' => $updated->get_error_message(),
                ];
            } else {
                $results['success'][] = $id;
            }
        }

        $response = new WP_REST_Response();
        $response->set_data([
            'success' => $results['success'],
            'failed'  => $results['failed'],
            'total'   => count($ids),
        ]);

        return $response;
    }

    /**
     * Bulk delete leave requests
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function bulk_delete_leave_requests($request) {
        $ids = $request['ids'];

        if (empty($ids) || ! is_array($ids)) {
            return new WP_Error('rest_invalid_param', __('Invalid leave request IDs.', 'erp'), ['status' => 400]);
        }

        $results = [
            'success' => [],
            'failed'  => [],
        ];

        foreach ($ids as $id) {
            $id   = absint($id);
            $item = erp_hr_get_leave_request($id);

            if (empty($item->id)) {
                $results['failed'][] = [
                    'id'      => $id,
                    'message' => __('Invalid leave request ID.', 'erp'),
                ];
                continue;
            }

            $deleted = erp_hr_delete_leave_request($id);

            if (! $deleted) {
                $results['failed'][] = [
                    'id'      => $id,
                    'message' => __('The leave request cannot be deleted.', 'erp'),
                ];
            } else {
                $results['success'][] = $id;
            }
        }

        $response = new WP_REST_Response();
        $response->set_data([
            'success' => $results['success'],
            'failed'  => $results['failed'],
            'total'   => count($ids),
        ]);

        return $response;
    }

    /**
     * Prepare a single item for create or update
     *
     * @param \WP_REST_Request $request request object
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['employee_id'] ) ) {
            $prepared_item['user_id'] = absint( $request['employee_id'] );
        }

        if ( isset( $request['start_date'] ) ) {
            $prepared_item['start_date'] = gmdate( 'Y-m-d', strtotime( $request['start_date'] ) );
        }

        if ( isset( $request['end_date'] ) ) {
            $prepared_item['end_date'] = gmdate( 'Y-m-d', strtotime( $request['end_date'] ) );
        }

        if ( isset( $request['policy'] ) ) {
            $prepared_item['leave_policy'] = absint( $request['policy'] );
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        if ( isset( $request['reason'] ) ) {
            $prepared_item['reason'] = $request['reason'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object          $item
     * @param \WP_REST_Request $request           request object
     * @param array           $additional_fields (optional)
     *
     * @return WP_REST_Response $response response data
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $employee = new Employee( $item->user_id );
error_log(print_r( [$item], true ));

        $data = [
            'id'            => (int) $item->id,
            'user_id'       => (int) $item->user_id,
            'employee_id'   => (int) $employee->employee_id,
            'employee_name' => $employee->display_name,
            'avatar_url'    => $employee->get_avatar_url( 80 ),
            'status'       => (int) $item->status,
            'start_date'    => erp_format_date( $item->start_date, 'Y-m-d' ),
            'end_date'      => erp_format_date( $item->end_date, 'Y-m-d' ),
            'reason'        => $item->reason,
            'comments'      => isset( $item->comments ) ? $item->comments : '',
            'applied_on'    => erp_format_date( $item->created_at, 'Y-m-d H:i:s' ),
            'policy_name'   => isset( $item->policy_name ) ? $item->policy_name : '',
            'applied_days'   => (float) $item->days,
            'available_days' => (float) $item->available,
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'policy', $include_params ) ) {
                $policies_controller = new LeavePoliciesController();

                $policy_id      = (int) $item->policy_id;


                if ( $policy_id ) {
                    $policy         = $policies_controller->get_policy( array( 'id' => $policy_id ) );
                    $data['policy'] = ! is_wp_error( $policy ) ? $policy->get_data() : null;
                }
            }
        }

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item );

        return $response;
    }

    /**
     * Get the User's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'request',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'employee_id' => [
                    'description' => __( 'Employee id for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'policy'      => [
                    'description' => __( 'Employee id for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'start_date'  => [
                    'description' => __( 'Start date for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'end_date'    => [
                    'description' => __( 'End date for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'reason'     => [
                    'description' => __( 'Reason for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ];

        return $schema;
    }
}
