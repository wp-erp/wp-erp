<?php

namespace WeDevs\ERP\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class ContactsGroupsController extends REST_Controller {

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
    protected $rest_base = 'crm/contacts/groups';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_groups' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_groups' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_group' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_create_groups' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_group' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_groups' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_group' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_create_groups' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_group' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_delete_groups' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/subscribes', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_subscribed_contacts' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_groups' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'subscribe_contact' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_groups' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<group_id>[\d]+)' . '/subscribes' . '/(?P<contact_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_subscribed_contact' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_groups' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of groups
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_groups( $request ) {
        $args = [
            'number'  => $request['per_page'],
            'offset'  => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'orderby' => $request['orderby'],
            'order'   => $request['order']
        ];

        $items       = erp_crm_get_contact_groups( $args );
        $total_items = erp_crm_get_contact_groups( [ 'count' => true ] );

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
     * Get a specific group
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_group( $request ) {
        $id    = (int) $request['id'];
        $item  = (object) erp_crm_get_contact_group_by_id( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_group_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create a group
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_group( $request ) {
        $data = [
            'name'        => $request['name'],
            'description' => $request['description'],
        ];

        $group = erp_crm_save_contact_group( $data );

        return new WP_REST_Response( $group, 201 );
    }

    /**
     * Update a group
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_group( $request ) {
        $data = [
            'id'          => intval( $request['id'] ),
            'name'        => $request['name'],
            'description' => $request['description'],
        ];

        $group = erp_crm_save_contact_group( $data );

        return new WP_REST_Response( $group_id, 201 );
    }

    /**
     * Delete a group
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_group( $request ) {
        $group_id = (int) $request['id'];

        erp_crm_contact_group_delete( $group_id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Get a collection of subscribed contacts
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_subscribed_contacts( $request ) {
        $args = [
            'group_id' => intval( $request['id'] ),
            'number'   => $request['per_page'],
            'offset'   => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $subscribers   = erp_crm_get_subscriber_contact( $args );
        $args['count'] = true;
        $total_items   = erp_crm_get_subscriber_contact( $args );

        $item_ids = [];

        foreach ( $subscribers as $subscriber ) {
            $item_ids[] = $subscriber->user_id;
        }

        $items = [];

        if ( ! empty( $item_ids ) ) {
            $items = erp_get_people_by( 'id', $item_ids );
        }

        $contacts_controller = new ContactsController();

        $formated_items = [];

        foreach ( $items as $item ) {
            $data             = $contacts_controller->prepare_item_for_response( $item, $request );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Subscribe a contact to a group
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function subscribe_contact( $request ) {
        if ( ! isset( $request['contact_ids'] ) || empty( $request['contact_ids'] ) ) {
            return new WP_Error( 'rest_group_invalid_contact_ids', __( 'Required contact ids.', 'erp' ), [ 'status' => 400 ] );
        }

        $contact_ids = explode( ',', str_replace( ' ', '', $request['contact_ids'] ) );

        foreach ( $contact_ids as $contact_id ) {
            $data = [
                'group_id' => intval( $request['id'] ),
                'user_id'  => intval( $contact_id ),
            ];

            $result = erp_crm_create_new_contact_subscriber( $data );
        }

        return new WP_REST_Response( true, 201 );
    }

    /**
     * Unsubscribe a contact from a group
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_subscribed_contact( $request ) {
        $contact_id = intval( $request['contact_id'] );
        $group_id   = intval( $request['group_id'] );

        erp_crm_contact_subscriber_delete( $contact_id, $group_id );

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

        if ( isset( $request['description'] ) ) {
            $prepared_item['description'] = $request['description'];
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object          $item
     * @param WP_REST_Request $request request object
     *
     * @return WP_REST_Response $response response data
     */
    public function prepare_item_for_response( $item, $request ) {
        $data = [
            'id'          => (int) $item->id,
            'name'        => $item->name,
            'description' => $item->description,
        ];

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
            'title'      => 'contact group',
            'type'       => 'object',
            'properties' => [
                'id'     => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'name'  => [
                    'description' => __( 'Name for the resource.', 'erp' ),
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
