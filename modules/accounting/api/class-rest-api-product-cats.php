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
    protected $rest_base = 'accounting/v1/product-cats';

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

        $product_cats = erp_acct_get_all_product_cats();
	    $response = rest_ensure_response( $product_cats );
	    $response = $this->format_collection_response( $response, $request, count( $product_cats ) );

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
	    $item = $this->prepare_item_for_database( $request );

        $term = erp_acct_insert_product_cat( $item );

        $response = $this->prepare_item_for_response( (object) $item, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
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

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_inventory_product_cat_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $term = erp_acct_get_product_cat( $id );

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

	    $item = $this->prepare_item_for_database( $request );

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_inventory_product_cat_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $term = erp_acct_update_product_cat( $item, $id );

        $item     = $this->prepare_item_for_response( (object) $item, $request );
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

        erp_acct_delete_product_cat( $term_id );

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

        if ( isset( $request['parent'] ) ) {
            $prepared_item['slug'] = $request['slug'];
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
	    $item = (object) $item;

        $data = [
            'name'           => $item->term_id,
            'parent'  => $item->term_taxonomy_id,
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
                'parent'            => [
                    'description' => __( 'Parent for the resource.' ),
                    'type'        => 'integer',
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
