<?php

namespace WeDevs\ERP\HRM\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use WeDevs\ERP\API\REST_Controller;

class Departments_Controller extends REST_Controller {
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
    protected $rest_base = 'hrm/departments';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_departments' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_department' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_department' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_department' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_department' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_department' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_department' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_department' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of departments
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_departments( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            's'      => $request['s'] ? $request['s'] : '',
        ];

        $items       = erp_hr_get_departments( $args );
        $total_items = erp_hr_count_departments();

        $formated_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            $data             = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific department
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_department( $request ) {
        $id   = (int) $request['id'];
        $item = new \WeDevs\ERP\HRM\Department( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_department_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create a department
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_department( $request ) {
        $item       = $this->prepare_item_for_database( $request );
        $id         = erp_hr_create_department( $item );
        $department = new \WeDevs\ERP\HRM\Department( $id );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $department, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Update a department
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_department( $request ) {
        $id = (int) $request['id'];

        $department = new \WeDevs\ERP\HRM\Department( $id );
        if ( ! $department ) {
            return new WP_Error( 'rest_department_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );
        $id   = erp_hr_create_department( $item );
        if( is_wp_error( $id ) ){
            return $id;
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $department, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete a department
     *
     * @since 1.0.0
     *
     * @param $request
     *
     * @return WP_REST_Response
     */
    public function delete_department( $request ) {
        $id = (int) $request['id'];

        erp_hr_delete_department( $id );
        $response = rest_ensure_response(true);
        return new WP_REST_Response( $response, 204 );
    }

    /**
     * Prepare a single item for create or update
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( ! empty( $request['title'] ) ) {
            $prepared_item['title'] = $request['title'];
        }

        // optional arguments.
        if ( ! empty( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        if ( ! empty( $request['description'] ) ) {
            $prepared_item['description'] = $request['description'];
        }

        if ( ! empty( $request['parent'] ) ) {
            $prepared_item['parent'] = absint( $request['parent'] );
        }

        if ( ! empty( $request['head'] ) ) {
            $prepared_item['lead'] = absint( $request['head'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object          $item
     * @param WP_REST_Request $request Request object.
     * @param array           $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'              => (int) $item->id,
            'title'           => $item->title,
            'lead'            => $item->lead,
            'parent'          => $item->parent,
            'description'     => $item->description,
            'total_employees' => $item->num_of_employees()
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );
            if ( in_array( 'parent', $include_params ) ) {
                $data['parent'] = $this->get_parent_department( $item );
            }

            if ( in_array( 'head', $include_params ) ) {
                $data['head'] = $this->get_user( intval( $item->lead ) );
            }
        }

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item );

        return $response;
    }

    /**
     * Get the parent of a department
     *
     * @param  object $item
     *
     * @return array
     */
    public function get_parent_department( $item ) {
        $parent_id = (int) $item->get_parent_id( $item->id );

        if ( ! $parent_id ) {
            return null;
        }

        $parent = new \WeDevs\ERP\HRM\Department( $parent_id );

        return [
            'id'     => $parent->id,
            'title'  => $parent->title,
            '_links' => $this->prepare_links( $parent ),
        ];
    }

    /**
     * Get the User's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'department',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'title'       => [
                    'description' => __( 'Title for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'description' => [
                    'description' => __( 'Description for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'parent'      => [
                    'description' => __( 'Parent for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
                'head'        => [
                    'description' => __( 'Head for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
            ],
        ];

        return $schema;
    }
}
