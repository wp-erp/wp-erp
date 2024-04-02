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
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of leave requests
     *
     * @param WP_REST_Request $request
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
     * @param WP_REST_Request $request
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
     * @param WP_REST_Request $request
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

    /**
     * Prepare a single item for create or update
     *
     * @param WP_REST_Request $request request object
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
     * @param WP_REST_Request $request           request object
     * @param array           $additional_fields (optional)
     *
     * @return WP_REST_Response $response response data
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $employee = new Employee( $item->user_id );

        $data = [
            'id'            => (int) $item->id,
            'user_id'       => (int) $item->user_id,
            'employee_id'   => (int) $employee->employee_id,
            'employee_name' => $employee->display_name,
            'avatar_url'    => $employee->get_avatar_url( 80 ),
            'start_date'    => erp_format_date( $item->start_date, 'Y-m-d' ),
            'end_date'      => erp_format_date( $item->end_date, 'Y-m-d' ),
            'reason'        => $item->reason,
            'comments'      => isset( $item->comments ) ? $item->comments : '',
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'policy', $include_params ) ) {
                $policies_controller = new LeavePoliciesController();

                $policy_id      = (int) $item->policy_id;
                $data['policy'] = null;

                if ( $policy_id ) {
                    $policy         = $policies_controller->get_policy( ['id' => $policy_id ] );
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
