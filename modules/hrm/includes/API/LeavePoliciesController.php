<?php

namespace WeDevs\ERP\HRM\API;

use WeDevs\ERP\API\REST_Controller;
use WeDevs\ERP\HRM\Models\Leave;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class LeavePoliciesController extends REST_Controller {

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
    protected $rest_base = 'hrm/leaves/policies';

    /**
     * Activate types of leave policy.
     *
     * @var array
     */
    protected $activate_types = [];

    /**
     * Class constructor.
     */
    public function __construct() {
        $this->activate_types = [
            1 => 'immediately',
            2 => 'after_days',
            3 => 'manually',
        ];
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_policies' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_leave_manage' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_policy' ],
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
                'callback'            => [ $this, 'get_policy' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_leave_manage' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_policy' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_leave_manage' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_policy' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_leave_manage' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of policies
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_policies( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $policies    = erp_hr_leave_get_policies( $args );
        $items       = ! empty( $policies['data'] ) ? $policies['data'] : [];
        $total_items = isset( $policies['total'] ) ? (int) $policies['total'] : erp_hr_count_leave_policies();

        $formated_items = [];

        foreach ( $items as $item ) {
            // erp_hr_leave_get_policies() returns display-formatted rows; reload the
            // record so the response always describes the same fields as the single
            // resource endpoints.
            $policy = erp_hr_leave_get_policy( $item->id );

            if ( empty( $policy ) ) {
                continue;
            }

            $data             = $this->prepare_item_for_response( $policy, $request );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific policy
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_policy( $request ) {
        $id   = (int) $request['id'];
        $item = erp_hr_leave_get_policy( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_policy_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create an policy
     *
     * @since 1.1.10
     * @since 1.2.0  erp_hr_leave_insert_policy is now returns Leave_Policies model
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_policy( $request ) {
        $item = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $item ) ) {
            return $item;
        }

        $policy_id = erp_hr_leave_insert_policy( $item );

        if ( is_wp_error( $policy_id ) ) {
            return $this->to_rest_error( $policy_id );
        }

        $policy = erp_hr_leave_get_policy( $policy_id );

        if ( empty( $policy ) ) {
            return new WP_Error( 'rest_policy_not_created', __( 'The leave policy could not be created.', 'erp' ), [ 'status' => 500 ] );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $policy, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $policy_id ) ) );

        return $response;
    }

    /**
     * Convert a WP_Error coming from the data layer into one carrying an HTTP status.
     *
     * @since 1.17.9
     *
     * @param WP_Error $error
     *
     * @return WP_Error
     */
    protected function to_rest_error( $error ) {
        $data = $error->get_error_data();

        if ( isset( $data['status'] ) ) {
            return $error;
        }

        $statuses = [
            'no-permission'     => 403,
            'exists'            => 409,
            'no-leave-id'       => 400,
            'no-financial-year' => 400,
            'not_exists'        => 404,
        ];

        $code   = $error->get_error_code();
        $status = isset( $statuses[ $code ] ) ? $statuses[ $code ] : 400;

        return new WP_Error( $code, $error->get_error_message(), [ 'status' => $status ] );
    }

    /**
     * Update an policy
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_policy( $request ) {
        $id = (int) $request['id'];

        $policy = erp_hr_leave_get_policy( $id );

        if ( empty( $id ) || empty( $policy->id ) ) {
            return new WP_Error( 'rest_policy_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $item ) ) {
            return $item;
        }

        $item['id'] = $id;

        $policy_id = erp_hr_leave_insert_policy( $item );

        if ( is_wp_error( $policy_id ) ) {
            return $this->to_rest_error( $policy_id );
        }

        $policy = erp_hr_leave_get_policy( $policy_id );

        if ( empty( $policy ) ) {
            return new WP_Error( 'rest_policy_not_updated', __( 'The leave policy could not be updated.', 'erp' ), [ 'status' => 500 ] );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $policy, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 200 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $policy_id ) ) );

        return $response;
    }

    /**
     * Delete an policy
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_policy( $request ) {
        $id = (int) $request['id'];

        erp_hr_leave_policy_delete( $id );

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

        $is_update = ! empty( $request['id'] );

        // Financial year — fall back to the one covering today when omitted.
        // Resolved before the leave type so a missing financial year cannot leave
        // a newly created leave type behind.
        $f_year = isset( $request['f_year'] ) ? absint( $request['f_year'] ) : 0;

        if ( ! $f_year && ! $is_update ) {
            $current_f_year = erp_hr_get_financial_year_from_date();

            if ( empty( $current_f_year ) ) {
                return new WP_Error(
                    'rest_policy_no_financial_year',
                    __( 'No financial year is configured for the current date. Pass a f_year or create a financial year first.', 'erp' ),
                    [ 'status' => 400 ]
                );
            }

            $f_year = (int) $current_f_year->id;
        }

        if ( $f_year ) {
            $prepared_item['f_year'] = $f_year;
        }

        // Resolve the leave type. `leave_id` wins over `name` when both are given.
        $leave_id = $this->resolve_leave_id( $request );

        if ( is_wp_error( $leave_id ) ) {
            return $leave_id;
        }

        if ( $leave_id ) {
            $prepared_item['leave_id'] = $leave_id;
        }

        if ( isset( $request['days'] ) ) {
            $prepared_item['days'] = absint( $request['days'] );
        }

        $prepared_item['color'] = isset( $request['color'] ) ? sanitize_text_field( $request['color'] ) : '#fafafa';

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        if ( isset( $request['department'] ) ) {
            $prepared_item['department_id'] = absint( $request['department'] );
        }

        if ( isset( $request['designation'] ) ) {
            $prepared_item['designation_id'] = absint( $request['designation'] );
        }

        if ( isset( $request['location'] ) ) {
            $prepared_item['location_id'] = absint( $request['location'] );
        }

        if ( isset( $request['employee_type'] ) ) {
            $prepared_item['employee_type'] = sanitize_text_field( $request['employee_type'] );
        }

        if ( isset( $request['gender'] ) ) {
            $prepared_item['gender'] = sanitize_text_field( $request['gender'] );
        }

        if ( isset( $request['marital'] ) ) {
            $prepared_item['marital'] = sanitize_text_field( $request['marital'] );
        }

        if ( isset( $request['applicable_from'] ) ) {
            $prepared_item['applicable_from'] = absint( $request['applicable_from'] );
        }

        if ( isset( $request['apply_for_new_users'] ) ) {
            $prepared_item['apply_for_new_users'] = ! empty( $request['apply_for_new_users'] ) ? 1 : 0;
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['description'] = sanitize_text_field( $request['description'] );
        }

        return $prepared_item;
    }

    /**
     * Resolve the leave type id a policy belongs to.
     *
     * Accepts an explicit `leave_id`, or a leave type `name` which is matched
     * against the existing types and created when it does not exist yet.
     *
     * @since 1.17.9
     *
     * @param WP_REST_Request $request
     *
     * @return int|WP_Error leave type id, 0 when neither field was sent
     */
    protected function resolve_leave_id( $request ) {
        if ( ! empty( $request['leave_id'] ) ) {
            $leave_id = absint( $request['leave_id'] );
            $leave    = Leave::find( $leave_id );

            if ( ! $leave ) {
                return new WP_Error( 'rest_policy_invalid_leave_id', __( 'Invalid leave type id.', 'erp' ), [ 'status' => 400 ] );
            }

            return $leave_id;
        }

        if ( ! isset( $request['name'] ) ) {
            return 0;
        }

        $name = sanitize_text_field( $request['name'] );

        if ( '' === $name ) {
            return new WP_Error( 'rest_policy_invalid_name', __( 'Name cannot be empty.', 'erp' ), [ 'status' => 400 ] );
        }

        $leave = Leave::where( 'name', $name )->first();

        if ( $leave ) {
            return (int) $leave->id;
        }

        $leave_id = erp_hr_insert_leave_policy_name( [
            'name'        => $name,
            'description' => isset( $request['description'] ) ? sanitize_text_field( $request['description'] ) : '',
        ] );

        if ( is_wp_error( $leave_id ) ) {
            return $this->to_rest_error( $leave_id );
        }

        return (int) $leave_id;
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
        $name = isset( $item->name ) ? $item->name : null;

        if ( null === $name && ! empty( $item->leave_id ) ) {
            $leave = Leave::find( $item->leave_id );
            $name  = $leave ? $leave->name : null;
        }

        $data = [
            'id'                  => (int) $item->id,
            'leave_id'            => (int) $item->leave_id,
            'name'                => $name,
            'days'                => (int) $item->days,
            'color'               => $item->color,
            'f_year'              => isset( $item->f_year ) ? (int) $item->f_year : null,
            'employee_type'       => ( '-1' != $item->employee_type ) ? $item->employee_type : null,
            'gender'              => ( '-1' != $item->gender ) ? $item->gender : null,
            'marital'             => ( '-1' != $item->marital ) ? $item->marital : null,
            'location'            => ( '-1' != $item->location_id ) ? (int) $item->location_id : null,
            'applicable_from'     => (int) $item->applicable_from_days,
            'apply_for_new_users' => (bool) $item->apply_for_new_users,
            'description'         => $item->description,
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'department', $include_params ) ) {
                $departments_controller = new DepartmentsController();

                $department_id      = (int) $item->department_id;
                $data['department'] = null;

                if ( $department_id ) {
                    $department         = $departments_controller->get_department( ['id' => $department_id ] );
                    $data['department'] = ! is_wp_error( $department ) ? $department->get_data() : null;
                }
            }

            if ( in_array( 'designation', $include_params ) ) {
                $designations_controller = new DesignationsController();

                $designation_id      = (int) $item->designation_id;
                $data['designation'] = null;

                if ( $designation_id ) {
                    $designation         = $designations_controller->get_designation( ['id' => $designation_id ] );
                    $data['designation'] = ! is_wp_error( $designation ) ? $designation->get_data() : null;
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
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'name'        => [
                    'description' => __( 'Name for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'leave_id'    => [
                    'description' => __( 'Leave type id the policy belongs to. Resolved from name when omitted.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                ],
                'days'        => [
                    'description' => __( 'Days for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'required'    => true,
                ],
                'color'       => [
                    'description' => __( 'Color for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'f_year'      => [
                    'description' => __( 'Financial year id. Defaults to the year covering the current date.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                ],
            ],
        ];

        return $schema;
    }
}
