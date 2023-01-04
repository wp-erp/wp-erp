<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class ExpensesController extends \WeDevs\ERP\API\REST_Controller {

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
    protected $rest_base = 'accounting/v1/expenses';

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
                    'callback'            => [ $this, 'get_expenses' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_expense' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
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
                    'callback'            => [ $this, 'get_expense' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_expense' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_expenses_voucher' );
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
                    'callback'            => [ $this, 'void_expense' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_publish_expenses_voucher' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/checks' . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_check' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );
    }

    /**
     * Get a collection of expenses
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_expenses( $request ) {
        $args = [
            'number' => isset( $request['per_page'] ) ? $request['per_page'] : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $expense_data = erp_acct_get_expenses( $args );
        $total_items  = erp_acct_get_expenses(
            [
                'count'  => true,
                'number' => -1,
            ]
        );

        foreach ( $expense_data as $item ) {
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
     * Get a expense
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_expense( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_expense_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $expense_data       = erp_acct_get_expense( $id );
        $expense_data['id'] = $id;

        $expense_data['created_by'] = $this->get_user( $expense_data['created_by'] );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $data     = $this->prepare_item_for_response( $expense_data, $request, $additional_fields );
        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a check
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_check( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_check_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $expense_data       = erp_acct_get_check( $id );
        $expense_data['id'] = $id;

        $expense_data['created_by'] = $this->get_user( $expense_data['created_by'] );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $data     = $this->prepare_item_for_response( $expense_data, $request, $additional_fields );
        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Create a expense
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_expense( $request ) {
        $expense_data = $this->prepare_item_for_database( $request );

        $item_amount       = [];
        $item_total        = [];
        $additional_fields = [];

        $items = $request['bill_details'];

        foreach ( $items as $key => $item ) {
            $item_amount[ $key ] = $item['amount'];
            $item_total[ $key ]  = $item['amount'];
        }
        $expense_data['attachments'] = maybe_serialize( $request['attachments'] );
        $expense_data['amount']      = array_sum( $item_total );

        $expense = erp_acct_insert_expense( $expense_data );
        $this->add_log( (array) $expense, 'add' );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $expense_data = $this->prepare_item_for_response( $expense, $request, $additional_fields );

        $response = rest_ensure_response( $expense_data );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update a expense
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_expense( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_expense_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $expense_data = $this->prepare_item_for_database( $request );

        $item_amount       = [];
        $item_total        = [];
        $additional_fields = [];

        $items = $request['bill_details'];

        foreach ( $items as $key => $item ) {
            $item_amount[ $key ] = $item['amount'];
            $item_total[ $key ]  = $item['amount'];
        }
        $expense_data['attachments'] = maybe_serialize( $request['attachments'] );
        $expense_data['amount']      = array_sum( $item_total );

        $old_data   = erp_acct_get_expense( $id );
        $expense_id = erp_acct_update_expense( $expense_data, $id );

        $this->add_log( $expense_data, 'edit', $old_data );

        $expense_data['id']             = $expense_id;
        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $expense_data = $this->prepare_item_for_response( $expense_data, $request, $additional_fields );

        $response = rest_ensure_response( $expense_data );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Void a expense
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function void_expense( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_expense_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        erp_acct_void_expense( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Log for expense related actions
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
                'sub_component' => __( 'Expense', 'erp' ),
                'old_value'     => isset( $changes['old_value'] ) ? $changes['old_value'] : '',
                'new_value'     => isset( $changes['new_value'] ) ? $changes['new_value'] : '',
                'message'       => sprintf( __( 'An expense of %1$s has been %2$s for %3$s', 'erp' ), $data['amount'], $operation, erp_acct_get_people_name_by_people_id( $data['people_id'] ) ),
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

        if ( isset( $request['people_id'] ) ) {
            $prepared_item['people_id'] = $request['people_id'];
        }

        if ( isset( $request['ref'] ) ) {
            $prepared_item['ref'] = $request['ref'];
        }

        if ( isset( $request['bank'] ) ) {
            $prepared_item['bank'] = $request['bank'];
        }

        if ( isset( $request['check_no'] ) ) {
            $prepared_item['check_no'] = $request['check_no'];
        }

        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['trn_date'] = $request['trn_date'];
        }

        if ( isset( $request['due_date'] ) ) {
            $prepared_item['due_date'] = $request['due_date'];
        }

        if ( isset( $request['amount'] ) ) {
            $prepared_item['total'] = $request['amount'];
        }

        if ( isset( $request['trn_no'] ) ) {
            $prepared_item['trn_no'] = $request['trn_no'];
        }

        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = $request['attachments'];
        }

        if ( isset( $request['deposit_to'] ) ) {
            $prepared_item['trn_by_ledger_id'] = $request['deposit_to'];
        }

        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
        }

        if ( isset( $request['bank_trn_charge'] ) ) {
            $prepared_item['bank_trn_charge'] = $request['bank_trn_charge'];
        }

        if ( isset( $request['bill_details'] ) ) {
            $prepared_item['bill_details'] = $request['bill_details'];
        }

        if ( isset( $request['billing_address'] ) ) {
            $prepared_item['billing_address'] = $request['billing_address'];
        }

        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = $request['particulars'];
        }

        if ( isset( $request['status'] ) ) {
            $prepared_item['status'] = $request['status'];
        }

        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = $request['attachments'];
        }

        if ( isset( $request['name'] ) ) {
            $prepared_item['name'] = $request['name'];
        }

        if ( isset( $request['pay_to'] ) ) {
            $prepared_item['pay_to'] = $request['pay_to'];
        }

        if ( isset( $request['type'] ) ) {
            $prepared_item['voucher_type'] = $request['type'];
        }

        if ( isset( $request['convert'] ) ) {
            $prepared_item['convert'] = $request['convert'];
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
            'id'                 => (int) $item->id,
            'voucher_no'         => (int) $item->voucher_no,
            'people_id'          => (int) $item->people_id,
            'people_name'        => $item->people_name,
            'date'               => $item->trn_date,
            'address'            => $item->address,
            'bill_details'       => $item->bill_details,
            'pdf_link'           => $item->pdf_link,
            'total'              => (float) $item->amount,
            'ref'                => ! empty( $item->ref ) ? $item->ref : '',
            'check_no'           => $item->check_no,
            'particulars'        => $item->particulars,
            'status'             => $item->status,
            'attachments'        => maybe_unserialize( $item->attachments ),
            'trn_by'             => $item->trn_by,
            'transaction_charge' => $item->transaction_charge,
            'created_at'         => $item->created_at,
            'deposit_to'         => $item->trn_by_ledger_id,
            'check_data'         => ! empty( $item->check_data ) ? $item->check_data : [],
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
            'title'      => 'expense',
            'type'       => 'object',
            'properties' => [
                'id'              => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'voucher_no'      => [
                    'description' => __( 'Voucher no. for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
                'people_id'       => [
                    'description' => __( 'People id for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'ref'       => [
                    'description' => __( 'Reference for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'trn_date'        => [
                    'description' => __( 'Date for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'trn_by'        => [
                    'description' => __( 'Trans by for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
                'billing_address' => [
                    'description' => __( 'Billing address for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'bill_details' => [
                    'description' => __( 'List of line items data.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'ledger_id'   => [
                            'description' => __( 'Ledger id.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                            'required'    => true,
                        ],
                        'particulars' => [
                            'description' => __( 'Bill Particulars.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                            'arg_options' => [
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                        'amount'      => [
                            'description' => __( 'Bill Amount', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'view', 'edit' ],
                            'required'    => true,
                        ],
                    ],
                ],
                'particulars'     => [
                    'description' => __( 'Particulars for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'status'          => [
                    'description' => __( 'Status for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
                'type'            => [
                    'description' => __( 'Item Type.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'check_no'        => [
                    'description' => __( 'Payment method for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'deposit_to'      => [
                    'description' => __( 'Account for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
            ],
        ];

        return $schema;
    }
}
