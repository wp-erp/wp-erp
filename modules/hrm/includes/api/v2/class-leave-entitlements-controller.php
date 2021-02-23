<?php

namespace WeDevs\ERP\HRM\API\V2;

use WP_REST_Controller;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\ERP\Framework\Traits\Api;

class Leave_Entitlements_Controller extends WP_REST_Controller {

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
    protected $rest_base = 'hrm/leaves/entitlements';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_entitlements' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_entitlement' ],
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
                'callback'            => [ $this, 'get_entitlement' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_entitlement' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_leave_manage' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of entitlements
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_entitlements( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $items       = erp_hr_leave_get_entitlements( $args );
        $total_items = erp_hr_leave_count_entitlements();

        $formated_items = [];

        foreach ( $items as $item ) {
            $data             = $this->prepare_item_for_response( $item, $request );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific entitlement
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_entitlement( $request ) {
        $id   = (int) $request['id'];
        $item = \WeDevs\ERP\HRM\Models\Leave_Entitlement::find( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_policy_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create an entitlement
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_entitlement( \WP_REST_Request $request ) {

        $department_id      = $request->get_param( 'department_id' );
        $designation_id     = $request->get_param( 'designation_id' );
        $location_id        = $request->get_param( 'location_id' );
        $gender             = $request->get_param( 'gender' );
        $marital            = $request->get_param( 'marital' );
        $f_year             = $request->get_param( 'f_year' );
        $leave_policy       = $request->get_param( 'leave_policy' );
        $assignment_to      = $request->get_param( 'assignment_to' );
        $single_employee    = $request->get_param( 'single_employee' );
        $comment            = $request->get_param( 'comment' );


        $department_id      = isset( $department_id ) ? intval( $department_id ) : '-1';
        $designation_id     = isset( $designation_id ) ? intval( $designation_id ) : '-1';
        $location_id        = isset( $location_id ) ? intval( $location_id ) : '-1';
        $gender             = isset( $gender ) ? intval( $gender ) : '-1';
        $marital            = isset( $marital ) ? intval( $marital ) : '-1';
        $f_year             = isset( $f_year ) ? intval( $f_year ) : '';
        $leave_policy       = isset( $leave_policy ) ? intval( $leave_policy ) : '';
        $is_single          = ! isset( $assignment_to );
        $single_employee    = isset( $single_employee ) ? intval( $single_employee ) : '-1';
        $comment            = isset( $comment ) ? $comment : '-1';


        $policy = Leave_Policy::find( $leave_policy );

        // fetch employees if not single
        $employees = array();

        if ( ! $is_single ) {
            $employees = erp_hr_get_employees( array(
                'department'    => $policy->department_id,
                'location'      => $policy->location_id,
                'designation'   => $policy->designation_id,
                'gender'        => $policy->gender,
                'marital_status'    => $policy->marital,
                'number'            => '-1',
                'no_object'         => true,
            ) );
        } else {
            $user              = get_user_by( 'id', $single_employee );
            $emp               = new \stdClass();
            $emp->user_id      = $user->ID;
            $emp->display_name = $user->display_name;

            $employees[] = $emp;
        }

        $affected = 0;
        foreach ( $employees as $employee ) {
            // get required data and send it to insert_entitlement function
            $data = array(
                'user_id'       => $employee->user_id,
                'leave_id'      => $policy->leave_id,
                'created_by'    => get_current_user_id(),
                'trn_id'        => $policy->id,
                'trn_type'      => 'leave_policies',
                'day_in'        => $policy->days,
                'day_out'       => 0,
                'description'   => $comment,
                'f_year'        => $policy->f_year,
            );

            $inserted = erp_hr_leave_insert_entitlement( $data );

            if ( ! is_wp_error( $inserted ) ) {
                $affected += 1;
            }
            else {
                //
            }
        }

        $response = rest_ensure_response( $affected );
        return $response;
    }

    /**
     * Delete an entitlement
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_entitlement( $request ) {
        $id = (int) $request['id'];

        $item        = \WeDevs\ERP\HRM\Models\Leave_Entitlement::find( $id );
        $employee_id = (int) $item->user_id;
        $policy_id   = (int) $item->policy_id;

        erp_hr_delete_entitlement( $id, $employee_id, $policy_id );

        return new WP_REST_Response( true, 204 );
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

        if ( isset( $request['policy'] ) ) {
            $prepared_item['policy_id'] = absint( $request['policy'] );
        }

        if ( isset( $request['days'] ) ) {
            $prepared_item['days'] = absint( $request['days'] );
        }

        if ( isset( $request['start_date'] ) ) {
            $prepared_item['from_date'] = date( 'Y-m-d', strtotime( $request['start_date'] ) );
        }

        if ( isset( $request['end_date'] ) ) {
            $prepared_item['to_date'] = date( 'Y-m-d', strtotime( $request['end_date'] ) );
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
        $data = [
            'id'             => (int) $item->id,
            'employee_id'    => (int) $item->user_id,
            'employee_name'  => $item->employee_name,
            'days'           => (int) $item->days,
            'start_date'     => date( 'Y-m-d', strtotime( $item->from_date ) ),
            'end_date'       => date( 'Y-m-d', strtotime( $item->to_date ) ),
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
            'title'      => 'policy',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'employee_id'          => [
                    'description' => __( 'Employee id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'required'    => true,
                ],
                'policy'          => [
                    'description' => __( 'Policy for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'required'    => true,
                ],
                'days'            => [
                    'description' => __( 'Days for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'required'    => true,
                ],
                'start_date'      => [
                    'description' => __( 'Start date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'end_date'        => [
                    'description' => __( 'End date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
            ],
        ];

        return $schema;
    }
}
