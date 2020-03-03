<?php

namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Inventory_Products_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/products';

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
					'callback'            => [ $this, 'get_inventory_products' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_manager' );
					},
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_inventory_product' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_manager' );
					},
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_inventory_product' ],
					'args'                => [
						'context' => $this->get_context_param( [ 'default' => 'view' ] ),
					],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_manager' );
					},
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_inventory_product' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_manager' );
					},
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_inventory_product' ],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_manager' );
					},
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/delete/(?P<ids>[\d,?]+)',
            [
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'bulk_delete' ],
					'args'                => [
						'ids' => [ 'required' => true ],
					],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_manager' );
					},
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/types',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_product_types' ],
					'args'                => [
						'context' => $this->get_context_param( [ 'default' => 'view' ] ),
					],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_manager' );
					},
				],
			]
        );
    }

    /**
     * Get a collection of inventory_products
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_inventory_products( $request ) {
        $args = [
            'number' => ! empty( $request['number'] ) ? (int) $request['number'] : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) )
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $product_data = erp_acct_get_all_products( $args );
        $total_items  = erp_acct_get_all_products(
            [
				'count'  => true,
				'number' => -1,
			]
        );

        foreach ( $product_data as $item ) {
            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a specific inventory product
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_inventory_product( $request ) {
        $id   = (int) $request['id'];
        $item = erp_acct_get_product( $id );

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_inventory_product_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

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
    public function create_inventory_product( $request ) {
        $item = $this->prepare_item_for_database( $request );

        $id         = erp_acct_insert_product( $item );
        $item['id'] = $id;

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $response = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update an inventory product
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_inventory_product( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        $id         = erp_acct_update_product( $item, $id );
        $item['id'] = $id;

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $response = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $response );
        $response->set_status( 200 );

        return $response;
    }


    /**
     * Delete an inventory product
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_inventory_product( $request ) {
        $id = (int) $request['id'];

        erp_acct_delete_product( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Prepare a single item for create or update
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];
        // required arguments.
        if ( isset( $request['name'] ) ) {
            $prepared_item['name'] = $request['name'];
        }

        if ( isset( $request['product_type_id'] ) ) {
            $prepared_item['product_type_id'] = $request['product_type_id']['id'];
        }

        if ( isset( $request['category_id'] ) ) {
            $prepared_item['category_id'] = $request['category_id']['id'];
        }

        if ( isset( $request['tax_cat_id'] ) ) {
            $prepared_item['tax_cat_id'] = $request['tax_cat_id']['id'];
        }

        if ( isset( $request['vendor'] ) ) {
            $prepared_item['vendor'] = $request['vendor']['id'];
        }

        if ( isset( $request['cost_price'] ) ) {
            $prepared_item['cost_price'] = $request['cost_price'];
        }

        if ( isset( $request['sale_price'] ) ) {
            $prepared_item['sale_price'] = $request['sale_price'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param array|object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'id'                => $item->id,
            'name'              => $item->name,
            'product_type_id'   => $item->product_type_id,
            'product_type_name' => $item->product_type_name,
            'category_id'       => $item->category_id,
            'tax_cat_id'        => $item->tax_cat_id,
            'vendor'            => $item->vendor,
            'cost_price'        => $item->cost_price,
            'sale_price'        => $item->sale_price,
            'vendor_name'       => $item->vendor_name,
            'cat_name'          => $item->cat_name,
            'tax_cat_name'      => erp_acct_get_tax_category_by_id( $item->tax_cat_id ),
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
            'title'      => 'erp_inv_product',
            'type'       => 'object',
            'properties' => [
                'id'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'name'            => [
                    'description' => __( 'Name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'product_type_id'    => [
                    'description' => __( 'State for the resource.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'id'   => [
                            'description' => __( 'Unique identifier for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                            'required'    => true,
                        ],
                        'name' => [
                            'description' => __( 'Type name for the resource.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                            'arg_options' => [
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ]
                    ],
                ],
                'category_id'    => [
                    'description' => __( 'Category id for the resource.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'id'   => [
                            'description' => __( 'Unique identifier for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'name' => [
                            'description' => __( 'Type name for the resource.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'parent' => [
                            'description' => __( 'Parent category for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ]
                    ],
                ],
                'tax_cat_id'    => [
                    'description' => __( 'Tax category id for the resource.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'id'   => [
                            'description' => __( 'Unique identifier for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'name' => [
                            'description' => __( 'Tax category name for the resource.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'description' => [
                            'description' => __( 'Description for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ]
                    ],
                ],
                'vendor'    => [
                    'description' => __( 'Vendor for the resource.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'id'   => [
                            'description' => __( 'Unique identifier for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                            'required'    => false
                        ],
                        'name' => [
                            'description' => __( 'Name for the resource.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                            'arg_options' => [
                                'sanitize_callback' => 'sanitize_text_field',
                            ]
                        ]
                    ],
                ],
                'cost_price'      => [
                    'description' => __( 'Cost price for the resource.' ),
                    'type'        => 'number',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'sale_price'      => [
                    'description' => __( 'Sale price for the resource.' ),
                    'type'        => 'number',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ]
            ],
        ];

        return $schema;
    }

    /**
     * Get product type
     *
     * @return object
     */
    public function get_product_types() {
        $types    = erp_acct_get_product_types();
        $response = rest_ensure_response( $types );
        return $response;
    }

    /**
     * Bulk delete action
     *
     * @param object $request
     *
     * @return object
     */
    public function bulk_delete( $request ) {
        $ids = $request['ids'];
        $ids = explode( ',', $ids );

        if ( ! $ids ) {
            return;
        }
        foreach ( $ids as $id ) {
            erp_acct_delete_product( $id );
        }

        return new WP_REST_Response( true, 204 );
    }
}
