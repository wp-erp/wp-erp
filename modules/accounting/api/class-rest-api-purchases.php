<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Purchases_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/purchases';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_purchases' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_purchase' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_purchase' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_purchase' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_purchase' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_purchase' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_purchase' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_purchase' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_purchase' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/void', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'void_purchase' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_purchase' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of purchases
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_purchases( $request ) {
        global $wpdb;

        $purchase_data = erp_acct_get_purchases();

        $response = rest_ensure_response( $purchase_data );
        $response = $this->format_collection_response( $response, $request, count( $purchase_data ) );

        return $response;
    }

    /**
     * Get a purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_purchase( $request ) {
        global $wpdb;

        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $purchase_data = erp_acct_get_purchase( $id );
        $response = rest_ensure_response( $purchase_data );
        $response = $this->format_collection_response( $response, $request, 1 );

        return $response;
    }

    /**
     * Create a purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_purchase( $request ) {
        $purchase_data = $this->prepare_item_for_database( $request );

        $items = $request['line_items'];

        foreach ( $items as $key => $item ) {
            $total = 0;

            $purchase_id[$key] = $item['purchase_id'];
            $total += $item['line_total'];

            $purchase_data['amount'] = $total;

            erp_acct_insert_purchase( $purchase_data, $purchase_id[$key] );
        }

        $response = rest_ensure_response( $purchase_data );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Update a purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_purchase( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $purchase_data = $this->prepare_item_for_database( $request );

        $items = $request['line_items'];

        foreach ( $items as $key => $item ) {
            $total = 0; $due = 0;

            $purchase_id[$key] = $item['purchase_id'];
            $total += $item['line_total'];

            $purchase_data['amount'] = $total;

            erp_acct_update_purchase( $purchase_data, $purchase_id[$key] );
        }

        $response = rest_ensure_response( $purchase_data );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Delete a purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_purchase( $request ) {
        $id = (int) $request['id'];
        
        if ( empty( $id ) ) {
            return new WP_Error( 'rest_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_delete_purchase( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Void a purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function void_purchase( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_void_purchase( $id );

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

        if ( isset( $request['vendor_id'] ) ) {
            $prepared_item['vendor_id'] = $request['vendor_id'];
        }
        if ( isset( $request['ref'] ) ) {
            $prepared_item['ref'] = $request['ref'];
        }
        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['trn_date'] = absint( $request['trn_date'] );
        }
        if ( isset( $request['due_date'] ) ) {
            $prepared_item['due_date'] = absint( $request['due_date'] );
        }
        if ( isset( $request['amount'] ) ) {
            $prepared_item['amount'] = $request['amount'];
        }
        if ( isset( $request['memo'] ) ) {
            $prepared_item['memo'] = $request['memo'];
        }
        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = $request['attachments'];
        }
        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
        }
        if ( isset( $request['type'] ) ) {
            $prepared_item['type'] = $request['type'];
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
            'id'              => (int) $item->id,
            'voucher_no'      => (int) $item->voucher_no,
            'vendor_id'       => (int) $item->vendor_id,
            'date'            => $item->trn_date,
            'due_date'        => $item->due_date,
            'billing_address' => $item->billing_address,
            'line_items'      => $item->line_items,
            'subtotal'        => (int) $item->subtotal,
            'total'           => (int) $item->total,
            'discount'        => (int) $item->discount,
            'tax'             => (int) $item->tax,
            'type'            => $item->type,
            'status'          => $item->status,
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
            'title'      => 'purchase',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'voucher_no'  => [
                    'description' => __( 'Voucher no. for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'vendor_id'   => [
                    'description' => __( 'Customer id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'trn_date'       => [
                    'description' => __( 'Date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'due_date'       => [
                    'description' => __( 'Due date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'line_items' => [
                    'description' => __( 'List of line items data.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'product_id'       => [
                            'description' => __( 'Product id.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'product_type'      => [
                            'description' => __( 'Product type.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'qty'   => [
                            'description' => __( 'Product quantity.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'unit_price'   => [
                            'description' => __( 'Unit price.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'discount'    => [
                            'description' => __( 'Discount.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'tax'       => [
                            'description' => __( 'Tax.' ),
                            'type'        => 'integer',
                            'context'     => [ 'edit' ],
                        ],
                        'tax_percent'    => [
                            'description' => __( 'Tax percent.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'item_total'       => [
                            'description' => __( 'Item total.' ),
                            'type'        => 'integer',
                            'context'     => [ 'edit' ],
                        ],
                    ],
                ],
                'type'       => [
                    'description' => __( 'Type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'status'       => [
                    'description' => __( 'Status for the resource.' ),
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
