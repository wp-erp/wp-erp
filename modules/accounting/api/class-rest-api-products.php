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
    protected $rest_base = 'accounting/v1/inv_products';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_inventory_products' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_inventory_product' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_inventory_product' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_inventory_product' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_inventory_product' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
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
            'posts_per_page' => $request['per_page'],
            'offset'         => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $args['post_type'] = 'erp_inv_product';

        $items = get_posts( $args );

        $count_items = wp_count_posts( $args['post_type'] );
        $total_items = (int) $count_items->publish;

        $formatted_items = [];
        foreach ( $items as $item ) {
            $item->id         = $item->ID;
            $data             = $this->prepare_item_for_response( $item, $request );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

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
        $id       = (int) $request['id'];
        $item     = get_post( $id );
        $item->id = $item->ID;

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_inventory_product_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

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
        $metas = [];
        $item = $this->prepare_item_for_database( $request );

        $id   = wp_insert_post( $item );

        $inv_product     = get_post( $id );
        $inv_product->id = $inv_product->ID;

        if ( isset( $inv_product->id ) ) {
            $this->update_product_metas( $inv_product->id, $request );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $inv_product, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

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
        $inv_product = get_post( $id );

        if ( empty( $id ) || empty( $inv_product->ID ) ) {
            return new WP_Error( 'rest_inventory_product_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $item ) ) {
            return $item;
        }

        $post_id = wp_update_post( wp_slash( (array) $item ), true );

        $this->update_product_metas( $post_id, $request );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $inv_product, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    public function update_product_metas( $post_id, $request ) {
        if ( !empty( $request['_cost_price'] ) ) {
            update_post_meta( $post_id, '_cost_price', $request['_cost_price'] );
        }

        if ( !empty( $request['_sale_price'] ) ) {
            update_post_meta( $post_id, '_sale_price', $request['_sale_price'] );
        }

        if ( !empty( $request['_sku'] ) ) {
            update_post_meta( $post_id, '_sku', $request['_sku'] );
        }

        if ( !empty( $request['_sales_account'] ) ) {
            update_post_meta( $post_id, '_sales_account', $request['_sales_account'] );
        }

        if ( !empty( $request['_purchase_account'] ) ) {
            update_post_meta( $post_id, '_purchase_account', $request['_purchase_account'] );
        }

        if ( !empty( $request['_inventory_asset_account'] ) ) {
            update_post_meta( $post_id, '_inventory_asset_account', $request['_inventory_asset_account'] );
        }
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

        wp_delete_post( $id );

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

        $prepared_item['post_type'] = 'erp_inv_product';

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $post_date   = new \DateTime( $item->post_date );
        $post_author = get_user_by('id', $item->post_author);

        $data = [
            'id'     => (int) $item->id,
            'title'  => $item->post_title,
            'body'   => $item->post_content,
            'status' => $item->post_status,
            'date'   => $post_date->format('Y-m-d'),
            'author' => $post_author->user_login
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
            'title'      => 'erp_inv_product',
            'type'       => 'object',
            'properties' => [
                'id'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'title'           => [
                    'description' => __( 'Title for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'body'            => [
                    'description' => __( 'Body for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'status'          => [
                    'description' => __( 'Status for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                '_sku'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                '_sale_price'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                '_cost_price'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                '_sales_account'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                '_purchase_account'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                '_inventory_asset_account'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
            ],
        ];

        return $schema;
    }
}
