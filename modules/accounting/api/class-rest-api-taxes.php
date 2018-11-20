<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Taxes_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/taxes';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_taxes' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sale' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_tax' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_tax' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_tax' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_tax' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_tax' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_tax' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_tax' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/void', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'void_tax' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_tax' );
                },
            ],
        ] );

    }

    /**
     * Get a collection of taxes
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_taxes( $request ) {
        global $wpdb;

        $tax_data = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_tax GROUP BY voucher_no" );
        $tax_count = $wpdb->get_row( "SELECT COUNT(*) FROM " . $wpdb->prefix . "erp_acct_tax" );

        $response = rest_ensure_response( $tax_data );
        $response = $this->format_collection_response( $response, $request, $tax_count );

        return $response;
    }


    /**
     * Get an tax
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_tax( $request ) {
        global $wpdb;
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $tax_data = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "erp_acct_tax WHERE voucher_no = {$id}" );

        $response = rest_ensure_response( $tax_data );
        $response = $this->format_collection_response( $response, $request, 1 );

        return $response;
    }

    /**
     * Create an tax
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_tax( $request ) {

        $tax_data = $this->prepare_item_for_database( $request );

        $item_total = []; $item_subtotal = []; $item_tax_total = []; $item_discount_total = []; $formatted_items = [];

        $items = $request['line_items'];

        foreach ( $items as $key => $item ) {
            $item_subtotal[$key] = $item['qty'] * $item['unit_price'];
            $item_tax_total[$key] = $item_subtotal[$key] * ($item['tax_percent'] / 100);
            $item_discount_total[$key] = $item['discount'] * $item['qty'];
            $item_total[$key] = $item_subtotal[$key] + $item_tax_total[$key] - $item_discount_total[$key];

        }

        $tax_data['billing_address'] = maybe_serialize( $request['billing_address'] );
        $tax_data['subtotal'] = array_sum( $item_subtotal );
        $tax_data['discount'] = array_sum( $item_tax_total );
        $tax_data['tax'] = array_sum( $item_discount_total );
        $tax_data['amount'] = array_sum( $item_total );
        $tax_data['attachments'] = maybe_serialize( $request['attachments'] );

        $id = erp_acct_insert_tax( $tax_data );

        $response = rest_ensure_response( $tax_data );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Update an tax
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_tax( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $tax_data = $this->prepare_item_for_database( $request );

        $item_total = []; $item_subtotal = []; $item_tax_total = []; $item_discount_total = []; $formatted_items = [];

        $items = $request['line_items'];

        foreach ( $items as $key=>$item ) {
            $item_subtotal[$key] = $item['qty'] * $item['unit_price'];
            $item_tax_total[$key] = $item_subtotal[$key] * ($item['tax_percent'] / 100);
            $item_discount_total[$key] = $item['discount'] * $item['qty'];
            $item_total[$key] = $item_subtotal[$key] + $item_tax_total[$key] - $item_discount_total[$key];

        }

        $tax_data['subtotal'] = array_sum( $item_subtotal );
        $tax_data['discount'] = array_sum( $item_tax_total );
        $tax_data['tax'] = array_sum( $item_discount_total );
        $tax_data['total'] = array_sum( $item_total );

        $id = erp_acct_update_tax( $tax_data );

        $response = rest_ensure_response( $tax_data );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }


    /**
     * Delete an tax
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_tax( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_delete_tax( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Void an tax
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function void_tax( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_void_tax( $id );

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

        if ( isset( $request['customer_id'] ) ) {
            $prepared_item['customer_id'] = $request['customer_id'];
        }
        if ( isset( $request['date'] ) ) {
            $prepared_item['date'] = $request['date'];
        }
        if ( isset( $request['due_date'] ) ) {
            $prepared_item['due_date'] = $request['due_date'];
        }
        if ( isset( $request['billing_address'] ) ) {
            $prepared_item['billing_address'] = absint( $request['billing_address'] );
        }
        if ( isset( $request['line_items'] ) ) {
            $prepared_item['line_items'] = $request['line_items'];
        }
        if ( isset( $request['subtotal'] ) ) {
            $prepared_item['subtotal'] = $request['subtotal'];
        }
        if ( isset( $request['total'] ) ) {
            $prepared_item['total'] = $request['total'];
        }
        if ( isset( $request['type'] ) ) {
            $prepared_item['type'] = $request['type'];
        }
        if ( isset( $request['status'] ) ) {
            $prepared_item['status'] = $request['status'];
        }
        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = $request['attachments'];
        }
        if ( isset( $request['transaction_by'] ) ) {
            $prepared_item['transaction_by'] = $request['transaction_by'];
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
            'customer_id'     => (int) $item->customer_id,
            'date'            => $item->date,
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
            'title'      => 'tax',
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
                'customer_id'   => [
                    'description' => __( 'Customer id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'date'       => [
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
                'billing_address' => [
                    'description' => __( 'List of billing address data.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'city'       => [
                            'description' => __( 'City name.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'state'      => [
                            'description' => __( 'ISO code or name of the state, province or district.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'postal_code'   => [
                            'description' => __( 'Postal code.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'country'    => [
                            'description' => __( 'ISO code of the country.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'phone'       => [
                            'description' => __( 'Phone for the resource.' ),
                            'type'        => 'string',
                            'context'     => [ 'edit' ],
                        ],
                    ],
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
