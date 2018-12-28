<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Pay_Purchases_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/pay-purchases';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_pay_purchases' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_pay_purchase' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_expenses_voucher' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_pay_purchase' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_pay_purchase' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_expenses_voucher' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_pay_purchase' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_expenses_voucher' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/void', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'void_pay_purchase' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_publish_expenses_voucher' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of pay_purchases
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_pay_purchases( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) )
        ];

        $formatted_items = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $pay_purchase_data  = erp_acct_get_pay_purchases( $args );
        $total_items = erp_acct_get_pay_purchases( [ 'count' => true, 'number' => -1 ] );

        foreach ( $pay_purchase_data as $item ) {
            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'created_by', $include_params ) ) {
                    $item['created_by'] = $this->get_user( $item['created_by'] );
                }
            }

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a pay_purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_pay_purchase( $request ) {
        global $wpdb;

        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_pay_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_pay_purchase( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;
        $item  = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );
    }

    /**
     * Create a pay_purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_pay_purchase( $request ) {
        $additional_fields = [];
        $pay_purchase_data = $this->prepare_item_for_database( $request );

        $items = $request['purchase_details']; $item_total = [];

        foreach ( $items as $key => $item ) {
            $item_total[$key] = $item['line_total'];
        }

        $pay_purchase_data['amount'] = array_sum( $item_total );

        $pay_purchase_id = erp_acct_insert_pay_purchase( $pay_purchase_data );

        $pay_purchase_data['id'] = $pay_purchase_id;
        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $pay_bill_data = $this->prepare_item_for_response( $pay_purchase_data, $request, $additional_fields );

        $response = rest_ensure_response( $pay_purchase_data );

        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update a pay_purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_pay_purchase( $request ) {

        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_pay_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $pay_purchase_data = $this->prepare_item_for_database( $request );

        $items = $request['purchase_details']; $item_total = [];

        foreach ( $items as $key => $item ) {
            $item_total[$key] = $item['line_total'];
        }

        $pay_purchase_data['amount'] = array_sum( $item_total );

        $pay_purchase_id = erp_acct_update_pay_purchase( $pay_purchase_data, $id );

        $response = rest_ensure_response( $pay_purchase_data );
        $response = $this->format_collection_response( $response, $request, 1 );

        return $response;
    }

    /**
     * Delete a pay_purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_pay_purchase( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_pay_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_delete_pay_purchase( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Void a pay_purchase
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function void_pay_purchase( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_pay_purchase_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_void_pay_purchase( $id );

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
            $prepared_item['trn_date'] = $request['trn_date'];
        }
        if ( isset( $request['purchase_details'] ) ) {
            $prepared_item['purchase_details'] = $request['purchase_details'];
        }
        if ( isset( $request['amount'] ) ) {
            $prepared_item['amount'] = $request['amount'];
        }
        if ( isset( $request['ref'] ) ) {
            $prepared_item['ref'] = $request['ref'];
        }
        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = maybe_serialize( $request['attachments'] );
        }
        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
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
            'id'              => (int) $item->id,
            'voucher_no'      => (int) $item->voucher_no,
            'vendor_id'       => (int) $item->vendor_id,
            'trn_date'        => $item->trn_date,
            'trn_by'          => $item->trn_by,
            'purchase_details'=> $item->purchase_details,
            'amount'          => (int) $item->amount,
            'ref'             => (int) $item->ref,
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
            'title'      => 'pay_purchase',
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
                    'description' => __( 'Vendor id for the resource.' ),
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
                'line_items' => [
                    'description' => __( 'List of line items data.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'id'       => [
                            'description' => __( 'Product id.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'voucher_no'      => [
                            'description' => __( 'Product type.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'due'   => [
                            'description' => __( 'Unit price.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'total'    => [
                            'description' => __( 'Discount.', 'erp' ),
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
