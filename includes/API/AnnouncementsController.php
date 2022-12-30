<?php

namespace WeDevs\ERP\API;

use DateTime;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class AnnouncementsController extends REST_Controller {

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
    protected $rest_base = 'hrm/announcements';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_announcements' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_announcement' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_announcement' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_announcement' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_announcement' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_announcement' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_announcement' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_announcement' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_announcement' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_announcement' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_announcement' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_announcement' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of announcements
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_announcements( $request ) {
        $args = [
            'posts_per_page' => $request['per_page'],
            'offset'         => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $args['post_type'] = 'erp_hr_announcement';

        $items = get_posts( $args );

        $count_items = wp_count_posts( $args['post_type'] );
        $total_items = (int) $count_items->publish;

        $formated_items = [];

        foreach ( $items as $item ) {
            $item->id         = $item->ID;
            $data             = $this->prepare_item_for_response( $item, $request );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific announcement
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_announcement( $request ) {
        $id       = (int) $request['id'];
        $item     = get_post( $id );
        $item->id = $item->ID;

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_announcement_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create an announcement
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_announcement( $request ) {
        $item = $this->prepare_item_for_database( $request );

        $id   = wp_insert_post( $item );

        $type = ( $request['recipient_type'] == 'all_employee' ) ? 'all_employee' : 'selected_employee';

        $employees = [];

        if ( $type == 'selected_employee' ) {
            $employees = explode( ',', str_replace( ' ', '', $request['employees'] ) );
        }

        erp_hr_assign_announcements_to_employees( $id, $type, $employees );

        $announcement     = get_post( $id );
        $announcement->id = $announcement->ID;

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $announcement, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Update an announcement
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_announcement( $request ) {
        $id = (int) $request['id'];

        $announcement = get_post( $id );

        if ( empty( $id ) || empty( $announcement->ID ) ) {
            return new WP_Error( 'rest_announcement_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        $id           = wp_insert_post( $item );
        $announcement = get_post( $id );

        $type = ( $request['recipient_type'] == 'all_employees' ) ? 'all_employee' : 'selected_employee';

        $employees = [];

        if ( $type == 'selected_employee' ) {
            $employees = explode( ',', str_replace( ' ', '', $request['employees'] ) );
        }

        erp_hr_assign_announcements_to_employees( $id, $type, $employees );

        $announcement->id = $announcement->ID;

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $announcement, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an announcement
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_announcement( $request ) {
        $id = (int) $request['id'];

        $force_delete = true;
        wp_delete_post( $id );

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
        if ( isset( $request['title'] ) ) {
            $prepared_item['post_title'] = $request['title'];
        }

        if ( isset( $request['body'] ) ) {
            $prepared_item['post_content'] = $request['body'];
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['ID'] = absint( $request['id'] );
        }

        if ( isset( $request['status'] ) ) {
            $prepared_item['post_status'] = $request['status'];
        }

        $prepared_item['post_type'] = 'erp_hr_announcement';

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
        $post_date   = new DateTime( $item->post_date );
        $post_author = get_user_by( 'id', $item->post_author );

        $data = [
            'id'     => (int) $item->id,
            'title'  => $item->post_title,
            'body'   => $item->post_content,
            'status' => $item->post_status,
            'date'   => $post_date->format( 'Y-m-d' ),
            'author' => $post_author->user_login,
        ];

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
            'title'      => 'announcement',
            'type'       => 'object',
            'properties' => [
                'id'              => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'title'           => [
                    'description' => __( 'Title for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'body'            => [
                    'description' => __( 'Body for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'status'          => [
                    'description' => __( 'Status for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'recipient_type'  => [
                    'description' => __( 'Recipient type for the resource.', 'erp' ),
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
