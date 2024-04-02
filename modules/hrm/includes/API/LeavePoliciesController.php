<?php

namespace WeDevs\ERP\HRM\API;

use WeDevs\ERP\API\REST_Controller;
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

        $items       = erp_hr_leave_get_policies( $args );
        $total_items = erp_hr_count_leave_policies();

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
     * Get a specific policy
     *
     * @param WP_REST_Request $request
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
        $item   = $this->prepare_item_for_database( $request );
        $policy = erp_hr_leave_insert_policy( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $policy, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

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

        $item = $this->prepare_item_for_database( $request );

        $policy_id = erp_hr_leave_insert_policy( $item );
        $policy    = erp_hr_leave_get_policy( $policy_id );

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
     * @param WP_REST_Request $request request object
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['name'] ) ) {
            $prepared_item['name'] = $request['name'];
        }

        if ( isset( $request['days'] ) ) {
            $prepared_item['value'] = absint( $request['days'] );
        }

        $prepared_item['color'] = isset( $request['color'] ) ? $request['color'] : '#fafafa';

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        if ( isset( $request['department'] ) ) {
            $prepared_item['department'] = absint( $request['department'] );
        }

        if ( isset( $request['designation'] ) ) {
            $prepared_item['designation'] = absint( $request['designation'] );
        }

        if ( isset( $request['gender'] ) ) {
            $prepared_item['gender'] = $request['gender'];
        }

        if ( isset( $request['marital'] ) ) {
            $prepared_item['marital'] = $request['marital'];
        }

        if ( isset( $request['activate'] ) ) {
            $activate_types            = array_flip( $this->activate_types );
            $prepared_item['activate'] = $activate_types[ $request['activate'] ];
        }

        if ( isset( $request['execute_day'] ) ) {
            $prepared_item['execute_day'] = absint( $request['execute_day'] );
        }

        if ( isset( $request['effective_date'] ) ) {
            $prepared_item['effective_date'] = gmdate( 'Y-m-d', strtotime( $request['effective_date'] ) );
        }

        if ( isset( $request['location'] ) ) {
            $prepared_item['location'] = absint( $request['location'] );
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['description'] = $request['description'];
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
            'name'           => $item->name,
            'days'           => (int) $item->value,
            'color'          => $item->color,
            'gender'         => ( $item->gender != -1 ) ? $item->gender : null,
            'marital'        => ( $item->marital != -1 ) ? $item->marital : null,
            'activate'       => $this->activate_types[ $item->activate ],
            'execute_day'    => (int) $item->execute_day,
            'effective_date' => gmdate( 'Y-m-d', strtotime( $item->effective_date ) ),
            'location'       => ( $item->location != -1 ) ? $item->location : null,
            'description'    => $item->description,
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'department', $include_params ) ) {
                $departments_controller = new DepartmentsController();

                $department_id      = (int) $item->department;
                $data['department'] = null;

                if ( $department_id ) {
                    $department         = $departments_controller->get_department( ['id' => $department_id ] );
                    $data['department'] = ! is_wp_error( $department ) ? $department->get_data() : null;
                }
            }

            if ( in_array( 'designation', $include_params ) ) {
                $designations_controller = new DesignationsController();

                $designation_id      = (int) $item->designation;
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
