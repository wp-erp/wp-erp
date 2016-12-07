<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Bank_Accounts_Controller extends REST_Controller {
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
    protected $rest_base = 'accounting/bank-accounts';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_accounts' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_bank_accounts' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_account' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_bank_accounts' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/transfer', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'transfer_money' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_bank_transfer' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of accounts
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_accounts( $request ) {
        $items = erp_array_to_object( erp_ac_get_bank_account() );

        $formated_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, 0 );

        return $response;
    }

    /**
     * Get a specific account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_account( $request ) {
        $id   = (int) $request['id'];
        $item = \WeDevs\ERP\Accounting\Model\Ledger::bank()->with( ['bank_details'] )->find( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_bank_account_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request, [] );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Transfer money from one account to another
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function transfer_money( $request ) {
        $item = $this->prepare_item_for_database( $request );

        $debit_credit  = erp_ac_bank_credit_total_amount( $item['from_account_id'] );
        $ledger_amount = abs( $debit_credit['debit'] - $debit_credit['credit'] );

        if ( $ledger_amount < $item['amount'] ) {
            return new WP_Error( 'rest_bank_transfer_not_enough_money', __( 'No enough money from your transfer account.' ), [ 'status' => 400 ] );
        }

        $args = [
            'type'            => 'transfer',
            'form_type'       => 'bank',
            'status'          => 'closed',
            'account_id'      => $item['from_account_id'],
            'user_id'         => get_current_user_id(),
            'billing_address' => '',
            'ref'             => '',
            'issue_date'      => $item['date'],
            'summary'         => $item['memo'],
            'total'           => $item['amount'],
            'trans_total'     => $item['amount'],
            'currency'        => erp_ac_get_currency(),
            'created_by'      => get_current_user_id(),
            'created_at'      => current_time( 'mysql' )
        ];

        $items[] = [
            'account_id'  => $item['to_account_id'],
            'type'        => 'line_item',
            'line_total'  => $item['amount'],
            'description' => '',
            'qty'         => 1,
            'unit_price'  => $item['amount'],
            'discount'    => 0
        ];

        $id = erp_ac_insert_transaction( $args, $items );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        return new WP_REST_Response( true, 201 );
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

        $prepared_item['date']            = date( 'Y-m-d', strtotime( $request['date'] ) );
        $prepared_item['from_account_id'] = isset( $request['from_account_id'] ) ? intval( $request['from_account_id'] ) : 0;
        $prepared_item['to_account_id']   = isset( $request['to_account_id'] ) ?intval( $request['to_account_id'] ) : 0;
        $prepared_item['amount']          = isset( $request['amount'] ) ? floatval( $request['amount'] ) : 0;
        $prepared_item['memo']            = isset( $request['memo'] ) ? sanitize_text_field( $request['memo'] ) : '';

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
            'id'             => (int) $item->id,
            'code'           => (int) $item->code,
            'name'           => $item->name,
            'description'    => $item->description,
            'account_number' => isset( $item->bank_details['account_number'] ) ? $item->bank_details['account_number']: '',
            'balance'        => erp_ac_get_individual_bank_balance( intval( $item->id ), false ),
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'created_by', $include_params ) ) {
                $data['created_by'] = $this->get_user( intval( $item->created_by ) );
            }
        }

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

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
            'title'      => 'bank account',
            'type'       => 'object',
            'properties' => [
                'id'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'date'            => [
                    'description' => __( 'Date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'from_account_id' => [
                    'description' => __( 'From account id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'to_account_id'   => [
                    'description' => __( 'To account id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'amount'          => [
                    'description' => __( 'Amount for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'memo'            => [
                    'description' => __( 'Memo for the resource.' ),
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