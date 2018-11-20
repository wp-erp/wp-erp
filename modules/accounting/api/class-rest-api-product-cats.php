<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Inventory_Product_Cats_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/inv_product_cat';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_all_inventory_product_cats' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_inventory_product_cat' ],
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
                'callback'            => [ $this, 'get_inventory_product_cat' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_inventory_product_cat' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_inventory_product_cat' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_hr_manager' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of inventory product categories
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_all_inventory_product_cats( $request ) {
        $result = array();
        $product_cat_args = array( 'hide_empty' => false, 'order_by' => 'name', 'order' => 'ASC' );

        $product_cats = get_terms('product_category', $product_cat_args );
        foreach( $product_cats as $product_cat ){
            $result['term_id'][] = $product_cat->term_id;
            $result['name'][] = $product_cat->name;
        }
        $response = rest_ensure_response( $result );
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
        $term = wp_insert_term( $request['name'], 'product_category', array( 'name' =>$request['name'], 'slug' => $request['slug'] ) );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( (object) $term, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $term['term_id'] ) ) );

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
        $id       = (int) $request['id'];
        $term     = get_term( $id );

        if ( empty( $id ) || empty( $term->term_id ) ) {
            return new WP_Error( 'rest_inventory_product_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( (object) $term, $request );
        $response = rest_ensure_response( $item );

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
        $id       = (int) $request['id'];
        $term = wp_update_term( $id, 'product_category', array(
            'name' => $request['name'],
            'slug' => $request['slug']
        ));

        if ( empty( $id ) || !isset( $term['term_id'] ) ) {
            return new WP_Error( 'rest_inventory_product_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( (object) $term, $request );
        $response = rest_ensure_response( $item );

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

        wp_delete_term( $term_id, 'product_category' );

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

        if ( isset( $request['name'] ) ) {
            $prepared_item['name'] = $request['name'];
        }

        if ( isset( $request['slug'] ) ) {
            $prepared_item['slug'] = $request['slug'];
        }

        if ( isset( $request['id'] ) ) {
            $prepared_item['term_id'] = absint( $request['term_id'] );
        }


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
        $data = [
            'term_id'           => $item->term_id,
            'term_taxonomy_id'  => $item->term_taxonomy_id,
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
                'name'           => [
                    'description' => __( 'Title for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'slug'            => [
                    'description' => __( 'Slug for the resource.' ),
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
