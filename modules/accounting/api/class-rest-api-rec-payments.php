<?php

namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Payments_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/payments';

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
					'callback'            => [ $this, 'get_payments' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sale' );
					},
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_payment' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_payment' );
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
					'callback'            => [ $this, 'get_payment' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_payment' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_payment' );
					},
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/void',
            [
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'void_payment' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_payment' );
					},
				],
			]
        );
    }

    /**
     * Get a collection of payments
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_payments( $request ) {
        $args = [
            'number' => isset( $request['per_page'] ) ? $request['per_page'] : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $payment_data  = erp_acct_get_payments( $args );
        $payment_count = erp_acct_get_payments(
            [
				'count'  => true,
				'number' => -1,
			]
        );

        foreach ( $payment_data as $item ) {
            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'created_by', $include_params, true ) ) {
                    $item['created_by'] = $this->get_user( $item['created_by'] );
                }
            }

            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $payment_count );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a payment
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_payment( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_payment( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Create a payment
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_payment( $request ) {
        $additional_fields = [];
        $payment_data      = $this->prepare_item_for_database( $request );

        $items      = $request['line_items'];
        $item_total = [];

        foreach ( $items as $key => $item ) {
            $item_total[ $key ] = $item['line_total'];
        }

        $payment_data['amount'] = array_sum( $item_total );

        $payment_data = erp_acct_insert_payment( $payment_data );

        $this->add_log( $payment_data, 'add' );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $payment_data = $this->prepare_item_for_response( $payment_data, $request, $additional_fields );

        $response = rest_ensure_response( $payment_data );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update a payment
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_payment( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $payment_data = $this->prepare_item_for_database( $request );

        $items      = $request['line_items'];
        $item_total = [];

        foreach ( $items as $key => $item ) {
            $item_total[ $key ] = $item['line_total'];
        }

        $payment_data['amount'] = array_sum( $item_total );

        $payment_data = erp_acct_update_payment( $payment_data, $id );

        $additional_fields              = [];
        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $payment_response = $this->prepare_item_for_response( $payment_data, $request, $additional_fields );

        $response = rest_ensure_response( $payment_response );

        $response->set_status( 200 );

        return $response;

    }

    /**
     * Void a payment
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function void_payment( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_void_payment( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Log when bill payment is created
     *
     * @param $data
     * @param $action
     */
    public function add_log( $data, $action ) {
        erp_log()->add(
            [
				'component'     => 'Accounting',
				'sub_component' => __( 'Receive Payment', 'erp' ),
				'old_value'     => '',
                'new_value'     => '',
                // translators: %1$s: amount, %2$s: id
				'message'       => sprintf( __( 'An invoice payment of %1$s has been created for %2$s', 'erp' ), $data['amount'], erp_acct_get_people_name_by_people_id( $data['customer_id'] ) ),
				'changetype'    => $action,
				'created_by'    => get_current_user_id(),

			]
        );
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

        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = $request['id'];
        }
        if ( isset( $request['customer_id'] ) ) {
            $prepared_item['customer_id'] = $request['customer_id'];
        }
        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['trn_date'] = $request['trn_date'];
        }
        if ( isset( $request['line_items'] ) ) {
            $prepared_item['line_items'] = $request['line_items'];
        }
        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = $request['particulars'];
        }
        if ( isset( $request['invoice_no'] ) ) {
            $prepared_item['invoice_no'] = $request['invoice_no'];
        }
        if ( isset( $request['amount'] ) ) {
            $prepared_item['amount'] = $request['amount'];
        }
        if ( isset( $request['ref'] ) ) {
            $prepared_item['ref'] = $request['ref'];
        }
        if ( isset( $request['due'] ) ) {
            $prepared_item['due'] = $request['due'];
        }
        if ( isset( $request['line_total'] ) ) {
            $prepared_item['line_total'] = $request['line_total'];
        }
        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
        }
        if ( isset( $request['type'] ) ) {
            $prepared_item['type'] = $request['type'];
        }
        if ( isset( $request['status'] ) ) {
            $prepared_item['status'] = $request['status'];
        }
        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = maybe_serialize( $request['attachments'] );
        }
        if ( isset( $request['billing_address'] ) ) {
            $prepared_item['billing_address'] = maybe_serialize( $request['billing_address'] );
        }
        if ( isset( $request['deposit_to'] ) ) {
            $prepared_item['deposit_to'] = $request['deposit_to'];
        }
        if ( isset( $request['name'] ) ) {
            $prepared_item['name'] = $request['name'];
        }
        if ( isset( $request['bank'] ) ) {
            $prepared_item['bank'] = $request['bank'];
        }
        if ( isset( $request['check_no'] ) ) {
            $prepared_item['check_no'] = $request['check_no'];
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
            'id'            => (int) $item->id,
            'voucher_no'    => (int) $item->voucher_no,
            'customer_id'   => (int) $item->customer_id,
            'customer_name' => $item->customer_name,
            'trn_date'      => $item->trn_date,
            'amount'        => $item->amount,
            'ref'           => $item->ref,
            'account'       => erp_acct_get_ledger_name_by_id( $item->trn_by_ledger_id ),
            'line_items'    => $item->line_items,
            'attachments'   => maybe_unserialize( $item->attachments ),
            'status'        => erp_acct_get_trn_status_by_id( $item->status ),
            'pdf_link'      => $item->pdf_link,
            'type'          => ! empty( $item->type ) ? $item->type : 'payment',
            'particulars'   => $item->particulars,
            'created_at'    => $item->created_at,
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
            'title'      => 'payment',
            'type'       => 'object',
            'properties' => [
                'id'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'voucher_no'      => [
                    'description' => __( 'Voucher no. for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'customer_id'     => [
                    'description' => __( 'Vendor id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'trn_date'        => [
                    'description' => __( 'Date for the resource.' ),
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
                        'city'        => [
                            'description' => __( 'City name.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'state'       => [
                            'description' => __( 'ISO code or name of the state, province or district.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'postal_code' => [
                            'description' => __( 'Postal code.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'country'     => [
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
                'line_items'      => [
                    'description' => __( 'List of line items data.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'invoice_no' => [
                            'description' => __( 'Invoice no.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'amount'     => [
                            'description' => __( 'Invoice amount.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'due'        => [
                            'description' => __( 'Invoice due.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'line_total' => [
                            'description' => __( 'Total.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                    ],
                ],
                'check_no'        => [
                    'description' => __( 'Check no for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'name'            => [
                    'description' => __( 'Check name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'type'            => [
                    'description' => __( 'Type for the resource.' ),
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
            ],
        ];

        return $schema;
    }
}
