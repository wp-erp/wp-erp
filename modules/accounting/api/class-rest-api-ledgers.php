<?php

namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Ledgers_Accounts_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/ledgers';

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
					'callback'            => [ $this, 'get_all_ledger_accounts' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_account_lists' );
					},
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_ledger_account' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_account' );
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
					'callback'            => [ $this, 'get_ledger_account' ],
					'args'                => [
						'context' => $this->get_context_param( [ 'default' => 'view' ] ),
					],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_single_account' );
					},
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_ledger_account' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_edit_account' );
					},
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_ledger_account' ],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_delete_account' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<chart_id>[\d]+)' . '/accounts',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_ledger_accounts_by_chart' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_account_lists' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/accounts',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_chart_accounts' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_account_lists' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/bank-accounts',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_bank_accounts' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_account_lists' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/cash-accounts',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_cash_accounts' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_account_lists' );
					},
				],
			]
        );


        /*
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/categories/(?P<chart_id>[\d]+)',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_ledger_categories' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_account_lists' );
					},
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_ledger_category' ],
					// 'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_account' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/categories/(?P<id>[\d]+)',
            [
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_ledger_category' ],
					// 'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_edit_account' );
					},
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_ledger_category' ],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_delete_account' );
					},
				],
			]
        );
        */
    }

    /**
     * Get all the ledgers of a particular chart_id
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_all_ledger_accounts( $request ) {
        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $ledgers = erp_acct_get_ledgers_with_balances();

        foreach ( $ledgers as $ledger ) {
            $data              = $this->prepare_ledger_for_response( $ledger, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, count( $ledgers ) );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get all the ledgers of a particular chart_id
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_ledger_accounts_by_chart( $request ) {
        $id = $request['chart_id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_empty_chart_id', __( 'Chart ID is Empty.' ), [ 'status' => 400 ] );
        }

        $items = erp_acct_get_ledgers_by_chart_id( $id );

        $response = rest_ensure_response( $items );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a specific ledger
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_ledger_account( $request ) {
        global $wpdb;
        $items = array();

        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_ledger_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_ledger( $id );

        $result   = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create an ledger
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_ledger_account( $request ) {
        global $wpdb;

        $exist = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->prefix}erp_acct_ledgers WHERE name = %s", $request['name'] ) );

        if ( $exist ) {
            return new WP_Error( 'rest_ledger_name_already_exist', __( 'Name already exist.' ), [ 'status' => 404 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        $result = erp_acct_insert_ledger( $item );

        $this->add_log( $result, 'add' );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $result, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 200 );
        //$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $result['id'] ) ) );

        return $response;
    }

    /**
     * Update an ledger
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_ledger_account( $request ) {
        global $wpdb;

        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_ledger_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        $result = erp_acct_update_ledger( $item, $id );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $result, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_ledger_account( $request ) {
        global $wpdb;

        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_ledger_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $wpdb->delete( "{$wpdb->prefix}erp_acct_ledgers", [ 'id' => $id ] );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Get chart of accounts
     *
     * @param WP_REST_REQUEST $request
     *
     * @return WP_ERROR|WP_REST_REQUEST
     */
    public function get_chart_accounts( $request ) {
        $accounts = erp_acct_get_all_charts();

        $response = rest_ensure_response( $accounts );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a collection of bank accounts
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_bank_accounts( $request ) {
        $items = erp_acct_get_banks( true, false, false );

        if ( empty( $items ) ) {
            return new WP_Error( 'rest_empty_accounts', __( 'Bank accounts are empty.' ), [ 'status' => 204 ] );
        }

        $formatted_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            $data              = $this->prepare_bank_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, 0 );

        return $response;
    }

    /**
     * Get cash accounts
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_cash_accounts( $request ) {
        $args               = [];
        $args['start_date'] = date( 'Y-m-d' );

        $closest_fy_date    = erp_acct_get_closest_fn_year_date( $args['start_date'] );
        $args['start_date'] = $closest_fy_date['start_date'];
        $args['end_date']   = $closest_fy_date['end_date'];

        $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::get_instance();
        $ledger_id  = $ledger_map->get_ledger_id_by_slug( 'cash' );

        $c_balance = get_ledger_balance_with_opening_balance( $ledger_id, $args['start_date'], $args['end_date'] );

        $item[]            = [
            'id'           => $ledger_id,
            'name'         => 'Cash',
            'obalance'     => $c_balance['obalance'],
            'balance'      => $c_balance['balance'],
            'total_debit'  => $c_balance['total_debit'],
            'total_credit' => $c_balance['total_credit'],
        ];
        $additional_fields = [];
        $data              = $this->prepare_bank_item_for_response( $item, $request, $additional_fields );

        $response = rest_ensure_response( $data );
        $response = $this->format_collection_response( $response, $request, 0 );

        return $response;
    }

    /**
     * Get ledger categories
     *
     * @param WP_REST_REQUEST $request
     *
     * @return WP_ERROR|WP_REST_REQUEST
     */
    public function get_ledger_categories( $request ) {
        $chart_id = absint( $request['chart_id'] );

        $categories = erp_acct_get_ledger_categories( $chart_id );

        $response = rest_ensure_response( $categories );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Create ledger categories
     *
     * @param WP_REST_REQUEST $request
     *
     * @return WP_ERROR|WP_REST_REQUEST
     */
    public function create_ledger_category( $request ) {
        $category = erp_acct_create_ledger_category( $request );

        if ( ! $category ) {
            return new WP_Error( 'rest_ledger_already_exist', __( 'Category already exist.' ), [ 'status' => 404 ] );
        }

        $response = rest_ensure_response( $category );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Update ledger categories
     *
     * @param WP_REST_REQUEST $request
     *
     * @return WP_ERROR|WP_REST_REQUEST
     */
    public function update_ledger_category( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_ledger_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $category = erp_acct_update_ledger_category( $request );

        if ( ! $category ) {
            return new WP_Error( 'rest_ledger_already_exist', __( 'Category already exist.' ), [ 'status' => 404 ] );
        }

        $response = rest_ensure_response( $category );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Remove category
     */
    public function delete_ledger_category( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_payment_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_delete_ledger_category( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Log when ledger is created
     *
     * @param $data
     * @param $action
     */
    public function add_log( $data, $action ) {
        $data = (array) $data;
        erp_log()->add(
            [
				'component'     => 'Accounting',
				'sub_component' => __( 'Ledger Account', 'erp' ),
				'old_value'     => '',
                'new_value'     => '',
                // translators: %1$s: name, %2$s: id
				'message'       => sprintf( __( 'A ledger account named %1$s has been created for %2$s', 'erp' ), $data['name'], erp_acct_get_people_name_by_people_id( $data['people_id'] ) ),
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

        $prepared_item['chart_id']    = ! empty( $request['chart_id'] ) ? (int) $request['chart_id'] : '';
        $prepared_item['category_id'] = ! empty( $request['category_id'] ) ? (int) $request['category_id'] : null;
        $prepared_item['name']        = ! empty( $request['name'] ) ? $request['name'] : '';
        $prepared_item['code']        = ! empty( $request['code'] ) ? $request['code'] : null;

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
        $item = (object) $item;

        $data = [
            'id'          => $item->id,
            'chart_id'    => $item->chart_id,
            'category_id' => $item->category_id,
            'name'        => $item->name,
            'slug'        => $item->slug,
            'code'        => $item->code,
            'trn_count'   => erp_acct_get_ledger_trn_count( $item->id ),
            'system'      => $item->system,
            'balance'     => erp_acct_get_ledger_balance( $item->id ),
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
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
    public function prepare_ledger_for_response( $item, $request, $additional_fields = [] ) {
        $item = (array) $item;

        $data = array_merge( $item, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * @param $item
     * @param $request
     * @param $additional_fields
     * @return mixed|WP_REST_Response
     */
    public function prepare_bank_item_for_response( $item, $request, $additional_fields ) {
        $item = (array) $item;

        $data = array_merge( $item, $additional_fields );

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
            'title'      => 'chart of account',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'chart_id'    => [
                    'description' => __( 'Type for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'category_id' => [
                    'description' => __( 'Code for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ]
                ],
                'name'        => [
                    'description' => __( 'Name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'code'        => [
                    'description' => __( 'Description for the resource.' ),
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
