<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class OpeningBalancesController extends \WeDevs\ERP\API\REST_Controller {

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
    protected $rest_base = 'accounting/v1/opening-balances';

    /**
     * Register the routes for the objects of the controller.
     */

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
                    'callback'            => [ $this, 'get_opening_balances' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_opening_balance' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_journal' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/names',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_opening_balance_names' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
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
                    'callback'            => [ $this, 'get_opening_balance' ],
                    'args'                => [
                        'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                    ],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/count',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_opening_balance_count_by_fy' ],
                    'args'                => [
                        'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                    ],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/virtual-accts' . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_virtual_accts_by_year' ],
                    'args'                => [
                        'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                    ],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/acc-payable-receivable',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_acc_payable_receivable' ],
                    'args'                => [
                        'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                    ],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
                    },
                ],
            ]
        );
    }

    /**
     * Get a collection of opening_balances
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_opening_balances( $request ) {
        $args['number'] = ! empty( $request['per_page'] ) ? $request['per_page'] : 20;
        $args['offset'] = ( $request['per_page'] * ( $request['page'] - 1 ) );

        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $items       = erp_acct_get_all_opening_balances( $args );
        $total_items = erp_acct_get_all_opening_balances(
            [
                'count'  => true,
                'number' => -1,
            ]
        );

        $formatted_items = [];

        foreach ( $items as $item ) {
            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get opening balances of a year
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_opening_balance( $request ) {
        global $wpdb;

        $id                = (int) $request['id'];
        $additional_fields = [];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_opening_balance_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $ledgers = erp_acct_get_opening_balance( $id );

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        foreach ( $ledgers as $ledger ) {
            if ( 7 === $ledger['chart_id'] ) {
                $ledger['bank']['id']   = $ledger['ledger_id'];
                $ledger['bank']['name'] = $ledger['name'];
            }
            $data              = $this->prepare_item_for_response( $ledger, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, count( $ledgers ) );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get number of entries of a financial year
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_opening_balance_count_by_fy( $request ) {
        global $wpdb;

        $id                = (int) $request['id'];
        $additional_fields = [];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_opening_balance_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $result = $wpdb->get_row( $wpdb->prepare( "select count(*) as num from {$wpdb->prefix}erp_acct_opening_balances where financial_year_id = %d", $id ) );

        $response = rest_ensure_response( $result->num );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a virtual accounts of a year
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_virtual_accts_by_year( $request ) {
        $id                = (int) $request['id'];
        $additional_fields = [];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_opening_balance_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_virtual_acct( $id );

        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a collection of opening_balance_names
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_opening_balance_names( $request ) {
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item = erp_acct_get_opening_balance_names();

        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Create a opening_balance
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_opening_balance( $request ) {
        $opening_balance_data = $this->prepare_item_for_database( $request );

        $items = $opening_balance_data['ledgers'];

        $ledgers  = [];
        $total_dr = 0;
        $total_cr = 0;

        $total_dr = ( isset( $request['total_dr'] ) ? $request['total_dr'] : 0 );
        $total_cr = ( isset( $request['total_dr'] ) ? $request['total_dr'] : 0 );

        if ( $total_dr !== $total_cr ) {
            return new WP_Error( 'rest_opening_balance_invalid_amount', __( 'Summation of debit and credit must be equal.', 'erp' ), [ 'status' => 400 ] );
        }

        $opening_balance_data['amount'] = $total_dr;

        $opening_balance = erp_acct_insert_opening_balance( $opening_balance_data );

        $this->add_log( $opening_balance_data, 'add' );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $response = $this->prepare_item_for_response( $opening_balance, $request, $additional_fields );
        $response = rest_ensure_response( $response );

        $response->set_status( 201 );

        return $response;
    }

    /**
     * Get account payable & receivable
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_acc_payable_receivable( $request ) {
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $acc_pay_rec = [];

        $acc_pay_rec['invoice_acc']       = erp_acct_get_opb_invoice_account_details( $request['start_date'] );
        $acc_pay_rec['bill_purchase_acc'] = erp_acct_get_opb_bill_purchase_account_details( $request['start_date'] );

        $response = rest_ensure_response( $acc_pay_rec );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Log when opening balance is created
     *
     * @param $data
     * @param $action
     */
    public function add_log( $data, $action ) {
        $data = (array) $data;

        erp_log()->add(
            [
                'component'     => 'Accounting',
                'sub_component' => __( 'Opening Balance', 'erp' ),
                'old_value'     => '',
                'new_value'     => '',
                // translators: %1$s: amount, %2$s: id
                'message'       => sprintf( __( 'A opening balance of %1$s has been created by %2$s', 'erp' ), $data['amount'], get_current_user_id() ),
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

        if ( isset( $request['year'] ) ) {
            $prepared_item['year'] = $request['year'];
        }

        if ( isset( $request['ledgers'] ) ) {
            $prepared_item['ledgers'] = $request['ledgers'];
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['description'] = $request['description'];
        }

        if ( isset( $request['acct_pay'] ) ) {
            $prepared_item['acct_pay'] = $request['acct_pay'];
        }

        if ( isset( $request['acct_rec'] ) ) {
            $prepared_item['acct_rec'] = $request['acct_rec'];
        }

        if ( isset( $request['tax_pay'] ) ) {
            $prepared_item['tax_pay'] = $request['tax_pay'];
        }

        return $prepared_item;
    }

    /**
     * Prepare output for response
     *
     * @param array|object    $item
     * @param WP_REST_Request $request           request object
     * @param array           $additional_fields (optional)
     *
     * @return WP_REST_Response $response response data
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $item = (array) $item;

        $data = array_merge( $item, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

    /**
     * Get currency's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'opening_balance',
            'type'       => 'object',
            'properties' => [
                'id'      => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'year'    => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'ledgers' => [
                    'description' => __( 'Ledger names for the resource.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'edit' ],
                ],
                'acct_pay' => [
                    'description' => __( 'Ledger names for the resource.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'edit' ],
                ],
                'acct_rec' => [
                    'description' => __( 'Ledger names for the resource.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'edit' ],
                ],
                'tax_pay' => [
                    'description' => __( 'Ledger names for the resource.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'edit' ],
                ],
                'total_dr'      => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'number',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'total_cr'      => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'number',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'description'      => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ];

        return $schema;
    }
}
