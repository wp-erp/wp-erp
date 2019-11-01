<?php

namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Tax_Rates_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/taxes';

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
					'callback'            => [ $this, 'get_tax_rates' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sale' );
					},
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_tax_rate' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_invoice' );
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
					'callback'            => [ $this, 'get_tax_rate' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sale' );
					},
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_tax_rate' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_invoice' );
					},
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_tax_rate' ],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_invoice' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/delete/(?P<ids>[\d,?]+)',
            [
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'bulk_delete' ],
					'args'                => [
						'ids' => [ 'required' => true ],
					],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_invoice' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/quick-edit',
            [

				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'quick_edit_tax_rate' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_invoice' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/line-add',
            [

				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'line_add_tax_rate' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_invoice' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/line-edit',
            [

				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'line_edit_tax_rate' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_invoice' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/line-delete' . '/(?P<db_id>[\d]+)',
            [

				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'line_delete_tax_rate' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_invoice' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/tax-records',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_tax_records' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sale' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/tax-records' . '/(?P<id>[\d]+)',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_tax_pay_record' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sale' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/pay-tax',
            [
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'pay_tax' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_create_sales_payment' );
					},
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/summary',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_tax_summary' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sale' );
					},
				],
			]
        );

    }

    /**
     * Get a collection of taxes
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_tax_rates( $request ) {
        $args = [
            'number'     => ! empty( $request['per_page'] ) ? (int) $request['per_page'] : 20,
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $tax_data    = erp_acct_get_all_tax_rates( $args );
        $total_items = erp_acct_get_all_tax_rates(
            [
				'count'  => true,
				'number' => -1,
			]
        );

        foreach ( $tax_data as $item ) {
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
     * Get an tax
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_tax_rate( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_tax_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_tax_rate( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Create an tax
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_tax_rate( $request ) {
        $item_rates = [];

        $tax_data = $this->prepare_item_for_database( $request );

        $items = $request['tax_components'];

        foreach ( $items as $key => $item ) {
            $item_rates[ $key ] = $item['tax_rate'];
        }

        $tax_data['tax_rate'] = array_sum( $item_rates );

        $tax_id = erp_acct_insert_tax_rate( $tax_data );

        $tax_data['id'] = $tax_id;

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $tax_data = $this->prepare_item_for_response( $tax_data, $request, $additional_fields );

        $response = rest_ensure_response( $tax_data );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update a tax rate
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_tax_rate( $request ) {
        $id         = (int) $request['id'];
        $item_rates = [];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_tax_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $tax_data = $this->prepare_item_for_database( $request );

        $items = $tax_data['tax_components'];

        foreach ( $items as $key => $item ) {
            $item_rates[ $key ] = $item['tax_rate'];
        }

        $tax_data['tax_rate'] = array_sum( $item_rates );

        $tax_id = erp_acct_update_tax_rate( $tax_data, $id );

        $tax_data['id'] = $tax_id;

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $tax_data = $this->prepare_item_for_response( $tax_data, $request, $additional_fields );

        $response = rest_ensure_response( $tax_data );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Quick Edit a tax rate
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function quick_edit_tax_rate( $request ) {
        $id         = (int) $request['id'];
        $item_rates = [];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_tax_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $tax_data = $this->prepare_item_for_database( $request );

        $tax_data['tax_rate'] = array_sum( $item_rates );

        $tax_id = erp_acct_quick_edit_tax_rate( $tax_data, $id );

        $tax_data['id'] = $tax_id;

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $tax_data = $this->prepare_item_for_response( $tax_data, $request, $additional_fields );

        $response = rest_ensure_response( $tax_data );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Add component of a tax rate
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function line_add_tax_rate( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_tax_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $tax_data = $this->prepare_line_item_for_database( $request );

        $line_id = erp_acct_add_tax_rate_line( $tax_data );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $tax_data = $this->prepare_tax_line_for_response( $tax_data, $request, $additional_fields );

        $response = rest_ensure_response( $tax_data );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Update component of a tax rate
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function line_edit_tax_rate( $request ) {
        $id         = (int) $request['id'];
        $item_rates = [];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_tax_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $tax_data = $this->prepare_line_item_for_database( $request );

        $line_id = erp_acct_edit_tax_rate_line( $tax_data );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $tax_data = $this->prepare_tax_line_for_response( $tax_data, $request, $additional_fields );

        $response = rest_ensure_response( $tax_data );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Update component of a tax rate
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function line_delete_tax_rate( $request ) {
        $id = (int) $request['db_id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_tax_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_delete_tax_rate_line( $id );

        return new WP_REST_Response( true, 204 );
    }


    /**
     * Delete an tax
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_tax_rate( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_tax_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_delete_tax_rate( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Get all tax payment records
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function get_tax_records( $request ) {
        $args = [
            'number'     => ! empty( $request['per_page'] ) ? (int) $request['per_page'] : 20,
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $tax_data    = erp_acct_get_tax_pay_records( $args );
        $total_items = erp_acct_get_tax_pay_records(
            [
				'count'  => true,
				'number' => -1,
			]
        );

        foreach ( $tax_data as $item ) {
            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'created_by', $include_params, true ) ) {
                    $item['created_by'] = $this->get_user( $item['created_by'] );
                }
            }

            $data              = $this->prepare_tax_pay_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a tax payment
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_tax_pay_record( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_tax_pay_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_tax_pay_record( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_tax_pay_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Make a tax payment
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function pay_tax( $request ) {
        $tax_data = $this->prepare_item_for_database( $request );

        $tax_id = erp_acct_pay_tax( $tax_data );

        $tax_data['id'] = $tax_id;

        $tax_data['voucher_no'] = $tax_id; // do we need it?

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $tax_data = $this->prepare_tax_pay_response( $tax_data, $request, $additional_fields );

        $response = rest_ensure_response( $tax_data );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Tax summary
     */
    public function get_tax_summary( $request ) {
        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $summary = erp_acct_tax_summary();

        foreach ( $summary as $item ) {
            $data              = $this->prepare_tax_summary_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Bulk delete action
     *
     * @param object $request
     *
     * @return object
     */
    public function bulk_delete( $request ) {
        $ids = $request['ids'];
        $ids = explode( ',', $ids );

        if ( ! $ids ) {
            return;
        }
        foreach ( $ids as $id ) {
            erp_acct_delete_tax_rate( $id );
        }

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Log when tax payment is created
     *
     * @param $data
     * @param $action
     */
    public function add_log( $data, $action ) {
        erp_log()->add(
            [
				'component'     => 'Accounting',
				'sub_component' => __( 'Tax Payment', 'erp' ),
				'old_value'     => '',
                'new_value'     => '',
                // translators: %1$s: amount, %2$s: id
				'message'       => sprintf( __( 'A tax payment of %1$s has been created for %2$s', 'erp' ), $data['amount'], erp_acct_get_people_name_by_people_id( $data['people_id'] ) ),
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

        if ( isset( $request['tax_rate_name'] ) ) {
            $prepared_item['tax_rate_name'] = $request['tax_rate_name'];
        }
        if ( isset( $request['is_compound'] ) ) {
            $prepared_item['is_compound'] = $request['is_compound'];
        }
        if ( isset( $request['tax_components'] ) ) {
            $prepared_item['tax_components'] = $request['tax_components'];
        }
        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['trn_date'] = $request['trn_date'];
        }
        if ( isset( $request['trn_by'] ) ) {
            $prepared_item['trn_by'] = $request['trn_by'];
        }
        if ( isset( $request['tax_category_id'] ) ) {
            $prepared_item['tax_category_id'] = $request['tax_category_id'];
        }
        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = $request['particulars'];
        }
        if ( isset( $request['amount'] ) ) {
            $prepared_item['amount'] = $request['amount'];
        }
        if ( isset( $request['ledger_id'] ) ) {
            $prepared_item['ledger_id'] = $request['ledger_id'];
        }
        if ( isset( $request['agency_id'] ) ) {
            $prepared_item['agency_id'] = $request['agency_id'];
        }
        if ( isset( $request['voucher_type'] ) ) {
            $prepared_item['voucher_type'] = $request['voucher_type'];
        }
        if ( isset( $request['tax_rate'] ) ) {
            $prepared_item['tax_rate'] = $request['tax_rate'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a line item of a single tax rate create or update
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_line_item_for_database( $request ) {
        $prepared_item = [];

        if ( isset( $request['tax_id'] ) ) {
            $prepared_item['tax_id'] = $request['tax_id'];
        }
        if ( isset( $request['db_id'] ) ) {
            $prepared_item['db_id'] = $request['db_id'];
        }
        if ( isset( $request['row_id'] ) ) {
            $prepared_item['row_id'] = $request['row_id'];
        }
        if ( isset( $request['component_name'] ) ) {
            $prepared_item['component_name'] = $request['component_name'];
        }
        if ( isset( $request['agency_id'] ) ) {
            $prepared_item['agency_id'] = $request['agency_id'];
        }
        if ( isset( $request['tax_cat_id'] ) ) {
            $prepared_item['tax_cat_id'] = $request['tax_cat_id'];
        }
        if ( isset( $request['tax_rate'] ) ) {
            $prepared_item['tax_rate'] = $request['tax_rate'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a single tax rate output for response
     *
     * @param array $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'id'             => (int) $item->id,
            'tax_rate_name'  => $item->tax_rate_name,
            'tax_rate'       => ! empty( $item->tax_rate ) ? $item->tax_rate : '',
            'tax_components' => ! empty( $item->tax_components ) ? $item->tax_components : '',
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

    /**
     * Prepare a tax rate line item output for response
     *
     * @param array $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_tax_line_for_response( $item, $request, $additional_fields = [] ) {

        $data = array_merge( $item, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

    /**
     * Prepare a single tax payment output for response
     *
     * @param array $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_tax_pay_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'id'           => (int) $item->id,
            'voucher_no'   => $item->voucher_no,
            'agency_id'    => erp_acct_get_tax_agency_name_by_id( $item->agency_id ),
            'trn_date'     => $item->trn_date,
            'particulars'  => $item->particulars,
            'amount'       => $item->amount,
            'trn_by'       => $item->trn_by,
            'ledger_id'    => erp_acct_get_ledger_name_by_id( $item->ledger_id ),
            'voucher_type' => $item->voucher_type,
            'created_at'   => $item->created_at,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

    /**
     * Prepare tax summary output for response
     *
     * @param array $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_tax_summary_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'tax_rate_id'           => (int) $item->tax_rate_id,
            'tax_rate_name'         => $item->tax_rate_name,
            'default'               => (int) $item->default,
            'sales_tax_category_id' => $item->tax_cat_id,
            'tax_rate'              => ! empty( $item->tax_rate ) ? $item->tax_rate : null,
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
            'title'      => 'tax',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'tax_rate_name'       => [
                    'description' => __( 'Tax rate name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'is_compound' => [
                    'description' => __( 'Tax type for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ]
                ],
                'tax_components'    => [
                    'description' => __( 'Components for the resource.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'component_name'   => [
                            'description' => __( 'Component name for the resource.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'agency_id' => [
                            'description' => __( 'Agency id for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'tax_category_id' => [
                            'description' => __( 'Tax category id for the resource.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'tax_rate' => [
                            'description' => __( 'Tax rate for the resource.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'view', 'edit' ],
                        ]
                    ],
                ]
            ],
        ];

        return $schema;
    }
}
