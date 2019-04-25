<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Closing_Balance_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/closing-balance';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'close_balancesheet' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_expenses_voucher' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/next-fn-year', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_next_fn_year' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_expenses_voucher' );
                },
            ],
        ] );
    }

    /**
     * Close balancesheet
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function close_balancesheet( $request ) {
        if ( empty( $request['start_date'] ) ) {
            return new WP_Error( 'rest_invalid_date', __( 'Start date missing.' ), [ 'status' => 404 ] );
        }

        if ( empty( $request['end_date'] ) ) {
            return new WP_Error( 'rest_invalid_date', __( 'End date missing.' ), [ 'status' => 404 ] );
        }

        $args = [
            'f_year_id'  => (int) $request['f_year_id'],
            'start_date' => $request['start_date'],
            'end_date'   => $request['end_date']
        ];

        $data     = erp_acct_clsbl_close_balance_sheet_now( $args );
        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get next financial year
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_next_fn_year( $request ) {
        if ( empty( $request['date'] ) ) {
            return new WP_Error( 'rest_invalid_date', __( 'Invalid resource date.' ), [ 'status' => 404 ] );
        }

        $data     = erp_acct_clsbl_get_closest_next_fn_year( $request['date'] );
        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
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
        if ( isset( $request['trn_by_ledger_id'] ) ) {
            $prepared_item['trn_by_ledger_id'] = $request['trn_by_ledger_id'];
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
            'amount'          => $item->amount,
            'billing_address' => !empty( $item->billing_address ) ? $item->billing_address : erp_acct_get_people_address( $item->vendor_id ),
            'bill_details'    => !empty( $item->bill_details ) ? $item->bill_details : [],
            'type'            => !empty( $item->type ) ? $item->type : 'pay_bill',
            'status'          => $item->status,
            'particulars'     => $item->particulars,
            'attachments'     => maybe_unserialize( $item->attachments )
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
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
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
                'trn_by'  => [
                    'description' => __( 'Voucher no. for the resource.' ),
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
                        'ledger_id'       => [
                            'description' => __( 'Ledger id.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'particulars'      => [
                            'description' => __( 'Particulars.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'amount'   => [
                            'description' => __( 'Unit price.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'tax'       => [
                            'description' => __( 'Tax.' ),
                            'type'        => 'integer',
                            'context'     => [ 'edit' ],
                        ],
                        'line_total'       => [
                            'description' => __( 'Item total.' ),
                            'type'        => 'integer',
                            'context'     => [ 'edit' ],
                        ],
                    ],
                ],
                'check_no'   => [
                    'description' => __( 'Check no for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'name'   => [
                    'description' => __( 'Check name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
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
