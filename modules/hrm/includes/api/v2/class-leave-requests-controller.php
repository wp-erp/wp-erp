<?php

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Controller;
use WeDevs\ERP\HRM\Employee;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\ERP\Framework\Traits\Api;

class Leave_Requests_Controller extends WP_REST_Controller {

    use Api;

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp/v2';

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
    public function get_leave_requests( \WP_REST_Request $request ) {

        $per_page       = $request->get_param( 'per_page' );
        $page           = $request->get_param( 'page' );
        $status         = $request->get_param( 'status' );
        $filter_year    = $request->get_param( 'filter_year' );
        $orderby        = $request->get_param( 'orderby' );
        $order          = $request->get_param( 'order' );
        $search         = $request->get_param( 's' );

        // get current year as default f_year
        $f_year = erp_hr_get_financial_year_from_date();
        $f_year = ! empty( $f_year ) ? $f_year->id : '';

        $args = array(
            'offset'  => ( $per_page * ( $page - 1 ) ),
            'number'  => $per_page,
            'status'  => $status,
            'f_year'  => isset( $filter_year ) ? $filter_year : $f_year,
            'orderby' => isset( $orderby ) ? $orderby : 'created_at',
            'order'   => isset( $order ) ? $order : 'DESC',
            's'       => isset( $search ) ? $search : ''
        );

        if ( erp_hr_is_current_user_dept_lead() && ! current_user_can( 'erp_leave_manage' ) ) {
            $args['lead'] = get_current_user_id();
        }

        $leave_requests = erp_hr_get_leave_requests( $args );
        $items          = $leave_requests['data'];
        $total          = $leave_requests['total'];

        $formatted_items = [];
        foreach( $items as $item ) {
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
            return new WP_Error( 'rest_leave_request_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
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
            return new WP_Error( 'rest_leave_request_required_entitlement', __( 'Set entitlement to the employee first.' ), [ 'status' => 400 ] );
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
            $prepared_item['start_date'] = date( 'Y-m-d', strtotime( $request['start_date'] ) );
        }

        if ( isset( $request['end_date'] ) ) {
            $prepared_item['end_date'] = date( 'Y-m-d', strtotime( $request['end_date'] ) );
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
            'id'                   => (int)$item->id,
            'user_id'              => (int)$item->user_id,
            'employee_id'          => (int)$employee->employee_id,
            'employee_name'        => $employee->display_name,
            'display_name'         => $item->display_name,
            'employee_designation' => $employee->get_designation('view'),
            'policy_name'          => $item->policy_name,
            'avatar_url'           => $employee->get_avatar_url(80),
            'start_date'           => erp_format_date($item->start_date, 'Y-m-d'),
            'end_date'             => erp_format_date($item->end_date, 'Y-m-d'),
            'reason'               => $item->reason,
            'message'              => $item->message,
            'leave_id'             => $item->leave_id,
            'days'                 => $item->days,
            'available'            => $item->available,
            'spent'                => $item->spent
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'policy', $include_params ) ) {
                $policies_controller = new Leave_Policies_Controller();

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
        $response = rest_ensure_response( apply_filters( 'filter_leave_request', $data, $request ) );

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
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'employee_id' => [
                    'description' => __( 'Employee id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'policy'      => [
                    'description' => __( 'Employee id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'start_date'  => [
                    'description' => __( 'Start date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'end_date'    => [
                    'description' => __( 'End date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'reason'     => [
                    'description' => __( 'Reason for the resource.' ),
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
