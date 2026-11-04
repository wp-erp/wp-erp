<?php

namespace WeDevs\ERP\HRM\API;

use WeDevs\ERP\API\REST_Controller;
use WeDevs\ERP\HRM\Models\FinancialYear;
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
        $total_items = erp_hr_count_leave_policies();

        $formated_items = [];

        // erp_hr_leave_get_policies() returns display-formatted rows, load the raw policy
        foreach ( $policies['data'] as $policy ) {
            $item = erp_hr_leave_get_policy( (int) $policy->id );

            if ( empty( $item->id ) ) {
                continue;
            }

            $data             = $this->prepare_item_for_response( $item, $request );
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
            $policy_id->add_data( [ 'status' => 400 ] );

            return $policy_id;
        }

        $policy = erp_hr_leave_get_policy( (int) $policy_id );

        if ( empty( $policy->id ) ) {
            return new WP_Error( 'rest_policy_not_created', __( 'The leave policy could not be created.', 'erp' ), [ 'status' => 400 ] );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $policy, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $policy->id ) ) );

        return $response;
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

        $item = $this->prepare_item_for_database( $request, $policy );

        if ( is_wp_error( $item ) ) {
            return $item;
        }

        $policy_id = erp_hr_leave_insert_policy( $item );

        if ( is_wp_error( $policy_id ) ) {
            $policy_id->add_data( [ 'status' => 400 ] );

            return $policy_id;
        }

        $policy = erp_hr_leave_get_policy( (int) $policy_id );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $policy, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

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
     * Maps the REST fields to the arguments erp_hr_leave_insert_policy()
     * expects, the same way the admin policy form does.
     *
     * @since 1.17.12 Map to the leave_id/f_year based policy structure
     *
     * @param WP_REST_Request $request request object
     * @param object|null     $policy  existing policy when updating
     *
     * @return array|WP_Error $prepared_item
     */
    protected function prepare_item_for_database( $request, $policy = null ) {
        $prepared_item = [];

        if ( $policy ) {
            $prepared_item = [
                'id'                  => (int) $policy->id,
                'leave_id'            => $policy->leave_id,
                'employee_type'       => $policy->employee_type,
                'department_id'       => $policy->department_id,
                'designation_id'      => $policy->designation_id,
                'location_id'         => $policy->location_id,
                'gender'              => $policy->gender,
                'marital'             => $policy->marital,
                'f_year'              => $policy->f_year,
                'description'         => $policy->description,
                'days'                => $policy->days,
                'color'               => $policy->color,
                'applicable_from'     => $policy->applicable_from_days,
                'apply_for_new_users' => $policy->apply_for_new_users,
            ];
        }

        // Leave type (by id or name) and financial year can only be set on create
        if ( ! $policy ) {
            $leave = null;

            if ( ! empty( $request['leave_id'] ) ) {
                $leave = Leave::find( absint( $request['leave_id'] ) );
            } elseif ( ! empty( $request['name'] ) ) {
                $leave = Leave::where( 'name', sanitize_text_field( $request['name'] ) )->first();
            }

            if ( empty( $leave ) ) {
                return new WP_Error( 'rest_policy_invalid_leave', __( 'Invalid leave type. Pass an existing leave_id or leave type name.', 'erp' ), [ 'status' => 400 ] );
            }

            $prepared_item['leave_id'] = (int) $leave->id;

            // Financial year, defaults to the current one
            if ( ! empty( $request['f_year'] ) ) {
                $f_year = FinancialYear::find( absint( $request['f_year'] ) );
            } else {
                $f_year = erp_hr_get_financial_year_from_date();
            }

            if ( empty( $f_year ) ) {
                return new WP_Error( 'rest_policy_invalid_year', __( 'Invalid or missing financial year.', 'erp' ), [ 'status' => 400 ] );
            }

            $prepared_item['f_year'] = (int) $f_year->id;
        }

        if ( isset( $request['days'] ) ) {
            $prepared_item['days'] = absint( $request['days'] );
        }

        if ( isset( $request['color'] ) ) {
            $prepared_item['color'] = sanitize_text_field( $request['color'] );
        } elseif ( ! $policy ) {
            $prepared_item['color'] = '#fafafa';
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['description'] = sanitize_text_field( $request['description'] );
        }

        if ( isset( $request['applicable_from'] ) ) {
            $prepared_item['applicable_from'] = absint( $request['applicable_from'] );
        }

        if ( isset( $request['apply_for_new_users'] ) ) {
            $prepared_item['apply_for_new_users'] = rest_sanitize_boolean( $request['apply_for_new_users'] ) ? 1 : 0;
        }

        // Filters, -1 means "all" as in the admin form
        if ( ! $policy ) {
            $filters = [
                'employee_type'  => 'employee_type',
                'department'     => 'department_id',
                'designation'    => 'designation_id',
                'location'       => 'location_id',
                'gender'         => 'gender',
                'marital'        => 'marital',
            ];

            foreach ( $filters as $field => $column ) {
                $prepared_item[ $column ] = ! empty( $request[ $field ] ) ? sanitize_text_field( $request[ $field ] ) : '-1';
            }

            $prepared_item += [
                'description'         => '',
                'applicable_from'     => 0,
                'apply_for_new_users' => 0,
            ];
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
        $item  = (object) $item;
        $leave = Leave::find( $item->leave_id );

        $data = [
            'id'                  => (int) $item->id,
            'leave_id'            => (int) $item->leave_id,
            'name'                => $leave ? $leave->name : null,
            'days'                => (int) $item->days,
            'color'               => $item->color,
            'f_year'              => (int) $item->f_year,
            'employee_type'       => ( $item->employee_type != -1 ) ? $item->employee_type : null,
            'department'          => ( $item->department_id != -1 ) ? (int) $item->department_id : null,
            'designation'         => ( $item->designation_id != -1 ) ? (int) $item->designation_id : null,
            'location'            => ( $item->location_id != -1 ) ? (int) $item->location_id : null,
            'gender'              => ( $item->gender != -1 ) ? $item->gender : null,
            'marital'             => ( $item->marital != -1 ) ? $item->marital : null,
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

                if ( $department_id > 0 ) {
                    $department         = $departments_controller->get_department( ['id' => $department_id ] );
                    $data['department'] = ! is_wp_error( $department ) ? $department->get_data() : null;
                }
            }

            if ( in_array( 'designation', $include_params ) ) {
                $designations_controller = new DesignationsController();

                $designation_id      = (int) $item->designation_id;
                $data['designation'] = null;

                if ( $designation_id > 0 ) {
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
                'leave_id'    => [
                    'description' => __( 'Leave type id. Either leave_id or name is required on create.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                ],
                'name'        => [
                    'description' => __( 'Leave type name, used when leave_id is not given.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'f_year'      => [
                    'description' => __( 'Financial year id. Defaults to the current financial year.', 'erp' ),
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
            ],
        ];

        return $schema;
    }
}
