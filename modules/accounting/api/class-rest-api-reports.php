<?php

namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Reports_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/reports';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/trial-balance',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_trial_balance' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/ledger-report',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_ledger_report' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/sales-tax-report',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_sales_tax_report' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/income-statement',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_income_statement' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/balance-sheet',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_balance_sheet' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/closest-fn-year',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_closest_fn_year' ],
					'args'                => [],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_ac_view_sales_summary' );
					},
				],
			]
        );

    }

    /**
     * Get trial balance
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_trial_balance( $request ) {
        $args = [
            'start_date' => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'   => ! empty( $request['end_date'] ) ? $request['end_date'] : null,
        ];

        $data = erp_acct_get_trial_balance( $args );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Chart status
     */
    public function get_sales_chart_status( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_status = erp_acct_get_sales_chart_status( $args );

        $response = rest_ensure_response( $chart_status );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Chart payment
     */
    public function get_sales_chart_payment( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_payment = erp_acct_get_sales_chart_payment( $args );

        $response = rest_ensure_response( $chart_payment );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get ledger report
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_ledger_report( $request ) {
        $ledger_id  = (int) $request['ledger_id'];
        $start_date = empty( $request['start_date'] ) ? date( 'Y-m-d' ) : $request['start_date'];
        $end_date   = empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'];

        $data = erp_acct_get_ledger_report( $ledger_id, $start_date, $end_date );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get sales tax report
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales_tax_report( $request ) {
        $agency_id  = (int) $request['agency_id'];
        $start_date = empty( $request['start_date'] ) ? null : $request['start_date'];
        $end_date   = empty( $request['end_date'] ) ? null : $request['end_date'];

        $data = erp_acct_get_sales_tax_report( $agency_id, $start_date, $end_date );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get income statement report
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_income_statement( $request ) {
        $start_date = $request['start_date'];
        $end_date   = $request['end_date'];
        $args       = [
            'start_date' => $start_date,
            'end_date'   => $end_date,
        ];

        $data = erp_acct_get_income_statement( $args );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get balance sheet report
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_balance_sheet( $request ) {
        $start_date = $request['start_date'];
        $end_date   = $request['end_date'];
        $args       = [
            'start_date' => $start_date,
            'end_date'   => $end_date,
        ];

        $data = erp_acct_get_balance_sheet( $args );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get closest financial year
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response $response Response data.
     */
    public function get_closest_fn_year( $request ) {
        $data = erp_acct_get_closest_fn_year_date( date( 'Y-m-d' ) );

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object|array $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {

        $data = array_merge( $item, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

}
