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
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_bills' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_expense' );
					},
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_bill' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_expenses_voucher' );
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
					'callback'            => [ $this, 'get_bill' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_expense' );
					},
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_bill' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_expenses_voucher' );
					},
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/due' . '/(?P<id>[\d]+)',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'due_bills' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_expenses_voucher' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/void',
            [
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'void_bill' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_publish_expenses_voucher' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/overview-payable',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_overview_payables' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );
    }

    /**
     * Get a collection of bills
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_bills( $request ) {
        $args = [
            'number' => isset( $request['per_page'] ) ? $request['per_page'] : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $bill_data   = erp_acct_get_bills( $args );
        $total_items = erp_acct_get_bills(
            [
				'count'  => true,
				'number' => -1,
			]
        );

        foreach ( $bill_data as $item ) {
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
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

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
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_bill_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $bill_data = erp_acct_get_bill( $id );
        // $bill_data['id'] = $id;

        // $bill_data['created_by'] = $this->get_user( $bill_data['created_by'] );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $data            = $this->prepare_item_for_response( $bill_data, $request, $additional_fields );
        $formatted_items = $this->prepare_response_for_collection( $data );
        $response        = rest_ensure_response( $formatted_items );

        $response->set_status( 200 );

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
        $bill_data = $this->prepare_item_for_database( $request );

        $item_total        = [];
        $additional_fields = [];

        $items = $request['bill_details'];

        foreach ( $items as $key => $item ) {
            $item_total[ $key ] = $item['amount'];
        }

        $bill_data['attachments']     = maybe_serialize( $bill_data['attachments'] );
        $bill_data['billing_address'] = isset( $bill_data['billing_address'] ) ? maybe_serialize( $bill_data['billing_address'] ) : '';
        $bill_data['amount']          = array_sum( $item_total );

        $bill = erp_acct_insert_bill( $bill_data );

        $this->add_log( $bill, 'add' );

        // $bill_data['voucher_no'] = $bill_id;
        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $bill_data = $this->prepare_item_for_response( $bill, $request, $additional_fields );

        $response = rest_ensure_response( $bill_data );
        $response->set_status( 201 );

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

        $can_edit = erp_acct_check_voucher_edit_state( $id );

        if ( ! $can_edit ) {
            return new WP_Error( 'rest_bill_invalid_edit', __( 'Invalid edit permission for update.' ), [ 'status' => 403 ] );
        }

        $bill_data = $this->prepare_item_for_database( $request );

        $item_total        = [];
        $additional_fields = [];

        $items = $request['bill_details'];

        foreach ( $items as $key => $item ) {
            $item_total[ $key ] = $item['amount'];
        }

        $bill_data['attachments']     = maybe_serialize( $bill_data['attachments'] );
        $bill_data['billing_address'] = isset( $bill_data['billing_address'] ) ? maybe_serialize( $bill_data['billing_address'] ) : '';
        $bill_data['amount']          = array_sum( $item_total );

        $bill = erp_acct_update_bill( $bill_data, $id );

        $this->add_log( $bill, 'update' );

        // $bill_data['voucher_no'] = $bill_id;
        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $bill_data = $this->prepare_item_for_response( $bill, $request, $additional_fields );

        $response = rest_ensure_response( $bill_data );
        $response->set_status( 200 );

        return $response;
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
     * Get a collection of bills with due of a people
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function due_bills( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_bill_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $args = [
            'number' => ! empty( $request['per_page'] ) ? $request['per_page'] : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $bill_data   = erp_acct_get_due_bills_by_people( [ 'people_id' => $id ] );
        $total_items = erp_acct_get_due_bills_by_people(
            [
				'people_id' => $id,
				'count'     => true,
				'number'    => -1,
			]
        );

        foreach ( $bill_data as $item ) {
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
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }


    /**
     * Get Dashboard Payable segments
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    function get_overview_payables( $request ) {
        $items    = erp_acct_get_payables_overview();
        $response = rest_ensure_response( $items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Log when bill is created
     *
     * @param $data
     * @param $action
     */
    public function add_log( $data, $action ) {
        erp_log()->add(
            [
				'component'     => 'Accounting',
				'sub_component' => __( 'Bill', 'erp' ),
				'old_value'     => '',
                'new_value'     => '',
                // translators: %1$s: amount, %2$s: id
				'message'       => sprintf( __( 'A bill of %1$s has been created for %2$s', 'erp' ), $data['amount'], erp_acct_get_people_name_by_people_id( $data['vendor_id'] ) ),
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

        if ( isset( $request['vendor_id'] ) ) {
            $prepared_item['vendor_id'] = $request['vendor_id'];
        }
        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['trn_date'] = $request['trn_date'];
        }
        if ( isset( $request['due_date'] ) ) {
            $prepared_item['due_date'] = $request['due_date'];
        }
        if ( isset( $request['amount'] ) ) {
            $prepared_item['total'] = (int) $request['amount'];
        }
        if ( isset( $request['due'] ) ) {
            $prepared_item['due'] = (int) $request['due'];
        }
        if ( isset( $request['trn_no'] ) ) {
            $prepared_item['trn_no'] = $request['trn_no'];
        }
        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
        }
        if ( isset( $request['bill_details'] ) ) {
            $prepared_item['bill_details'] = $request['bill_details'];
        }
        if ( isset( $request['status'] ) ) {
            $prepared_item['status'] = $request['status'];
        }
        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = $request['particulars'];
        }
        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = $request['attachments'];
        }
        if ( isset( $request['billing_address'] ) ) {
            $prepared_item['billing_address'] = maybe_serialize( $request['billing_address'] );
        }
        if ( isset( $request['ref'] ) ) {
            $prepared_item['ref'] = $request['ref'];
        }

        $prepared_item['request'] = $request;

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
            'editable'        => ! empty( $item->editable ) ? (int) $item->editable : 1,
            'voucher_no'      => (int) $item->voucher_no,
            'vendor_id'       => (int) $item->vendor_id,
            'vendor_name'     => $item->vendor_name,
            'trn_date'        => $item->trn_date,
            'due_date'        => $item->due_date,
            'billing_address' => ! empty( $item->billing_address ) ? $item->billing_address : erp_acct_get_people_address( $item->vendor_id ),
            'bill_details'    => ! empty( $item->bill_details ) ? $item->bill_details : [],
            'amount'          => (int) $item->amount,
            'due'             => ! empty( $item->due ) ? $item->due : $item->amount,
            'ref'             => ! empty( $item->ref ) ? $item->ref : '',
            'particulars'     => $item->particulars,
            'status'          => $item->status,
            'created_at'      => $item->created_at,
            'attachments'     => maybe_unserialize( $item->attachments ),
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
            'title'      => 'bill',
            'type'       => 'object',
            'properties' => [
                'id'           => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'voucher_no'   => [
                    'description' => __( 'Voucher no. for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ]
                ],
                'vendor_id'    => [
                    'description' => __( 'People id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'trn_date'     => [
                    'description' => __( 'Date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'due_date'     => [
                    'description' => __( 'Due date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'ref'     => [
                    'description' => __( 'Reference number for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ]
                ],
                'billing_address' =>  [
                    'description' => __( 'Billing address for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'bill_details' => [
                    'description' => __( 'List of line items data.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'ledger_id'   => [
                            'description' => __( 'Ledger id.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ]
                        ],
                        'description' => [
                            'description' => __( 'Item Particular.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                            'arg_options' => [
                                'sanitize_callback' => 'sanitize_text_field',
                            ]
                        ],
                        'amount'      => [
                            'description' => __( 'Bill Amount', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'view', 'edit' ],
                        ]
                    ],
                ],
                'status'       => [
                    'description' => __( 'Status for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true
                ],
                'type' => [
                    'description' => __( 'Item Type.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'particulars' => [
                    'description' => __( 'Bill Particular.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ]
            ],
        ];

        return $schema;
    }
}


