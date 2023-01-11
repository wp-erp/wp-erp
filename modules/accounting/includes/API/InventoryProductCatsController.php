<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class InventoryProductCatsController extends \WeDevs\ERP\API\REST_Controller {

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
    protected $rest_base = 'accounting/v1/product-cats';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_all_inventory_product_cats' ],
                    'args'                => $this->get_collection_params(),
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_manager' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_inventory_product_cat' ],
                    'args'                => $this->get_collection_params(),
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_manager' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_inventory_product_cat' ],
                    'args'                => [
                        'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                    ],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_manager' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_inventory_product_cat' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_manager' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_inventory_product_cat' ],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_manager' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/delete/(?P<ids>[\d,?]+)',
            [
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'bulk_delete_cat' ],
                    'args'                => [
                        'ids' => [ 'required' => true ],
                    ],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_manager' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );
    }

    /**
     * Get a collection of inventory product categories
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_all_inventory_product_cats( $request ) {
        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $product_cats = erp_acct_get_all_product_cats();

        $total_items = is_array( $product_cats ) ? count( $product_cats ) : 1;

        foreach ( $product_cats as $item ) {
            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a specific inventory product category
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_inventory_product_cat( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_inventory_product_cat_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_product_cat( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;
        $item                           = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response                       = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Create an inventory product
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_inventory_product_cat( $request ) {
        $item       = $this->prepare_item_for_database( $request );
        $id         = erp_acct_insert_product_cat( $item );
        $item['id'] = $id;

        $this->add_log( $item, 'add' );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $response = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update an inventory product category
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_inventory_product_cat( $request ) {
        $id = (int) $request['id'];

        $item = $this->prepare_item_for_database( $request );

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_inventory_product_cat_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $old_data = erp_acct_get_product_cat( $id );
        $id       = erp_acct_update_product_cat( $item, $id );

        $this->add_log( $item, 'edit', $old_data );

        $item['id'] = $id;

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Delete an inventory product category
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_inventory_product_cat( $request ) {
        $term_id = (int) $request['id'];

        $item = erp_acct_get_product_cat( $term_id );

        erp_acct_delete_product_cat( $term_id );

        $this->add_log( $item, 'delete' );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Bulk delete action
     *
     * @param object $request
     *
     * @return object
     */
    public function bulk_delete_cat( $request ) {
        $ids = $request['ids'];
        $ids = explode( ',', $ids );

        if ( ! $ids ) {
            return;
        }

        foreach ( $ids as $id ) {
            $item = erp_acct_get_product_cat( $id );
            erp_acct_delete_product_cat( $id );
            $this->add_log( $item, 'delete' );
        }

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Log for product category related actions
     *
     * @param array $data
     * @param string $action
     * @param array $old_data
     *
     * @return void
     */
    public function add_log( $data, $action, $old_data = [] ) {
        switch ( $action ) {
            case 'edit':
                $operation = 'updated';
                $changes   = ! empty( $old_data ) ? erp_get_array_diff( $data, $old_data ) : [];
                break;
            case 'delete':
                $operation = 'deleted';
                break;
            default:
                $operation = 'created';
        }

        erp_log()->add(
            [
                'component'     => 'Accounting',
                'sub_component' => __( 'Product Category', 'erp' ),
                'old_value'     => isset( $changes['old_value'] ) ? $changes['old_value'] : '',
                'new_value'     => isset( $changes['new_value'] ) ? $changes['new_value'] : '',
                'message'       => sprintf( __( '<strong>%1$s</strong> product category has been %2$s', 'erp' ), $data['name'], $operation ),
                'changetype'    => $action,
                'created_by'    => get_current_user_id(),
            ]
        );
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

        if ( isset( $request['name'] ) ) {
            $prepared_item['name'] = $request['name'];
        }

        if ( isset( $request['parent'] ) ) {
            $prepared_item['parent'] = $request['parent'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param array|object    $item
     * @param WP_REST_Request $request           request object
     * @param array           $additional_fields (optional)
     *
     * @return WP_REST_Response $response response data
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'id'     => $item->id,
            'name'   => $item->name,
            'parent' => $item->parent,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

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
            'title'      => 'erp_inv_product_cat',
            'type'       => 'object',
            'properties' => [
                'id'     => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'name'   => [
                    'description' => __( 'Name for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'parent'    => [
                    'description' => __( 'Parent for the resource.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'id'   => [
                            'description' => __( 'Unique identifier for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'name' => [
                            'description' => __( 'Name for the resource.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                    ],
                ],
            ],
        ];

        return $schema;
    }
}
