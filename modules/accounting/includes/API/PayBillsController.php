<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PayBillsController extends \WeDevs\ERP\API\REST_Controller {

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
    protected $rest_base = 'accounting/v1/pay-bills';

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
                    'callback'            => [ $this, 'get_pay_bills' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_pay_bill' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_expenses_voucher' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_pay_bill' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_pay_bill' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_expenses_voucher' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/void',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'void_pay_bill' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_publish_expenses_voucher' );
                    },
                ],
            ]
        );
    }

    /**
     * Get a collection of pay_bills
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_pay_bills( $request ) {
        $args = [
            'number' => isset( $request['per_page'] ) ? $request['per_page'] : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $pay_bill_data = erp_acct_get_pay_bills( $args );
        $total_items   = erp_acct_get_pay_bills(
            [
                'count'  => true,
                'number' => -1,
            ]
        );

        foreach ( $pay_bill_data as $item ) {
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
     * Get a pay_bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_pay_bill( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_pay_bill_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_pay_bill( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;
        $item                           = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response                       = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Create a pay_bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_pay_bill( $request ) {
        $additional_fields = [];
        $pay_bill_data     = $this->prepare_item_for_database( $request );

        $items      = $request['bill_details'];
        $item_total = [];

        foreach ( $items as $key => $item ) {
            $item_total[ $key ] = $item['amount'];
        }

        $pay_bill_data['amount'] = array_sum( $item_total );

        $pay_bill_data = erp_acct_insert_pay_bill( $pay_bill_data );

        $this->add_log( $pay_bill_data, 'add' );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $pay_bill_data = $this->prepare_item_for_response( $pay_bill_data, $request, $additional_fields );

        $response = rest_ensure_response( $pay_bill_data );

        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update a pay_bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_pay_bill( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_pay_bill_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $pay_bill_data = $this->prepare_item_for_database( $request );

        $items      = $request['bill_details'];
        $item_total = [];

        foreach ( $items as $key => $item ) {
            $item_total[ $key ] = $item['total'];
        }

        $pay_bill_data['amount'] = array_sum( $item_total );

        $old_data = erp_acct_get_pay_bill( $id );

        $pay_bill_id = erp_acct_update_pay_bill( $pay_bill_data, $id );

        $this->add_log( $pay_bill_data, 'edit', $old_data );

        $pay_bill_data['id']            = $pay_bill_id;
        $additional_fields              = [];
        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $pay_bill_response = $this->prepare_item_for_response( $pay_bill_data, $request, $additional_fields );

        $response = rest_ensure_response( $pay_bill_response );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Void a pay_bill
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function void_pay_bill( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_pay_bill_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $old_data = erp_acct_get_pay_bill( $id );

        erp_acct_void_pay_bill( $id );

        $this->add_log( $old_data, 'delete' );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Log for pay bill related actions
     *
     * @param array $data
     * @param string $action
     * @param array $old_data
     *
     * @return void
     */
    public function add_log( $data, $action, $old_data = [] ) {
        switch ( $action ) {
            case 'edit':
                $operation = 'updated';
                $changes   = ! empty( $old_data ) ? erp_get_array_diff( $data, $old_data ) : [];
                break;
            case 'delete':
                $operation = 'deleted';
                break;
            default:
                $operation = 'created';
        }

        erp_log()->add(
            [
                'component'     => 'Accounting',
                'sub_component' => __( 'Pay Bill', 'erp' ),
                'old_value'     => isset( $changes['old_value'] ) ? $changes['old_value'] : '',
                'new_value'     => isset( $changes['new_value'] ) ? $changes['new_value'] : '',
                'message'       => sprintf( __( 'A bill payment of %1$s has been %2$s for %3$s', 'erp' ), $data['amount'], $operation, erp_acct_get_people_name_by_people_id( $data['vendor_id'] ) ),
                'changetype'    => $action,
                'created_by'    => get_current_user_id(),
            ]
        );
    }

    /**
     * Prepare a single item for create or update
     *
     * @param WP_REST_Request $request request object
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

        if ( isset( $request['billing_address'] ) ) {
            $prepared_item['billing_address'] = maybe_serialize( $request['billing_address'] );
        }

        if ( isset( $request['bill_details'] ) ) {
            $prepared_item['bill_details'] = $request['bill_details'];
        }

        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = $request['particulars'];
        }

        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = maybe_serialize( $request['attachments'] );
        }

        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
        }

        if ( isset( $request['type'] ) ) {
            $prepared_item['voucher_type'] = $request['type'];
        }

        if ( isset( $request['amount'] ) ) {
            $prepared_item['amount'] = $request['amount'];
        }

        if ( isset( $request['due'] ) ) {
            $prepared_item['due'] = $request['due'];
        }

        if ( isset( $request['status'] ) ) {
            $prepared_item['status'] = $request['status'];
        }

        if ( isset( $request['check_no'] ) ) {
            $prepared_item['check_no'] = $request['check_no'];
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

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param array|object    $item
     * @param WP_REST_Request $request           request object
     * @param array           $additional_fields (optional)
     *
     * @return WP_REST_Response $response response data
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'id'              => (int) $item->id,
            'voucher_no'      => (int) $item->voucher_no,
            'vendor_id'       => (int) $item->vendor_id,
            'trn_date'        => $item->trn_date,
            'amount'          => $item->amount,
            'billing_address' => ! empty( $item->billing_address ) ? $item->billing_address : erp_acct_get_people_address( $item->vendor_id ),
            'pdf_link'        => $item->pdf_link ,
            'bill_details'    => ! empty( $item->bill_details ) ? $item->bill_details : [],
            'type'            => ! empty( $item->type ) ? $item->type : 'pay_bill',
            'status'          => $item->status,
            'particulars'     => $item->particulars,
            'ref'             => $item->ref,
            'trn_by'          => erp_acct_get_payment_method_by_id( $item->trn_by )->name,
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
            'title'      => 'pay_bill',
            'type'       => 'object',
            'properties' => [
                'id'           => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'vendor_id'    => [
                    'description' => __( 'Vendor id for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'ref'     => [
                    'description' => __( 'Reference for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'trn_date'     => [
                    'description' => __( 'Trans Date for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'trn_by'       => [
                    'description' => __( 'Voucher no. for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
                'bill_details' => [
                    'description' => __( 'List of line items data.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'id'   => [
                            'description' => __( 'Unique identifier for the line item.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'voucher_no' => [
                            'description' => __( 'Voucher no for line item.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'due_date'      => [
                            'description' => __( 'Due date for the resource.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                            'arg_options' => [
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                        'amount'         => [
                            'description' => __( 'Amount for the resource.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'edit' ],
                        ],
                        'due'  => [
                            'description' => __( 'Item due.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'edit' ],
                        ],
                    ],
                ],
                'check_no'     => [
                    'description' => __( 'Check no for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'name'         => [
                    'description' => __( 'Check name for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'type'         => [
                    'description' => __( 'Type for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'status'       => [
                    'description' => __( 'Status for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
                'particulars'       => [
                    'description' => __( 'Particulars for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'deposit_to'      => [
                    'description' => __( 'Deposit to for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
            ],
        ];

        return $schema;
    }
}
