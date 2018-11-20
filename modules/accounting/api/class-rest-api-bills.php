<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Bills_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/bills';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_bills' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_bill' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_expenses_voucher' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_bill' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_bill' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_expenses_voucher' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_bill' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_expenses_voucher' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/void', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'void_bill' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_publish_expenses_voucher' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of bills
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_bills( $request ) {
        global $wpdb;

        $invoice_data = erp_acct_get_bills();

        $response = rest_ensure_response( $invoice_data );
        $response = $this->format_collection_response( $response, $request, count( $invoice_data ) );

        return $response;
    }

    /**
     * Get a bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_bill( $request ) {
        global $wpdb;

        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_bill_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $invoice_data =  erp_acct_get_bill( $id );

        $response = rest_ensure_response( $invoice_data );
        $response = $this->format_collection_response( $response, $request, 1 );

        return $response;
    }

    /**
     * Create a bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_bill( $request ) {
        $request->set_param( 'context', 'edit' );
        $bill_data = $this->prepare_item_for_database( $request );

        $items = $request['bill_details'];

        foreach ( $items as $key => $item ) {
            $total = 0; $due = 0;

            $bill_id[$key] = $item['voucher_no'];
            $total += $item['line_total'];

            $bill_data['amount'] = $total;

            erp_acct_insert_bill( $bill_data, $bill_id[$key] );
        }

        $response = rest_ensure_response( $bill_data );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Update a bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_bill( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_bill_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $bill_data = $this->prepare_item_for_database( $request );

        $items = $request['bill_details'];

        foreach ( $items as $key => $item ) {
            $total = 0; $due = 0;

            $invoice_id[$key] = $item['invoice_id'];
            $total += $item['line_total'];

            $bill_data['amount'] = $total;

            erp_acct_update_bill( $bill_data, $invoice_id[$key] );
        }

        $response = rest_ensure_response( $bill_data );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Delete a bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_bill( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_bill_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_delete_bill( $id );

        return new WP_REST_Response(true, 204 );
    }

    /**
     * Void a bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function void_bill( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_bill_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_void_bill( $id );

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
        if ( isset( $request['transaction_no'] ) ) {
            $prepared_item['transaction_no'] = $request['transaction_no'];
        }
        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = $request['attachments'];
        }
        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
        }
        if ( isset( $request['bill_details'] ) ) {
            $prepared_item['bill_details'] = $request['bill_details'];
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
            'address'         => $item->address,
            'bill_details'    => $item->bill_details,
            'subtotal'        => (int) $item->subtotal,
            'total'           => (int) $item->total,
            'discount'        => (int) $item->discount,
            'tax'             => (int) $item->tax,
            'ref'             => $item->ref,
            'remarks'         => $item->remarks,
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
            'title'      => 'bill',
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
                'address' => [
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
                'bill_details' => [
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
