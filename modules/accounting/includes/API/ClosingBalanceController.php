<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class ClosingBalanceController extends \WeDevs\ERP\API\REST_Controller {

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
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'close_balancesheet' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_expenses_voucher' );
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
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/next-fn-year',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_next_fn_year' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );
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
            return new WP_Error( 'rest_invalid_date', __( 'Start date missing.', 'erp' ), [ 'status' => 404 ] );
        }

        if ( empty( $request['end_date'] ) ) {
            return new WP_Error( 'rest_invalid_date', __( 'End date missing.', 'erp' ), [ 'status' => 404 ] );
        }

        $args = [
            'f_year_id'  => (int) $request['f_year_id'],
            'start_date' => $request['start_date'],
            'end_date'   => $request['end_date'],
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
            return new WP_Error( 'rest_invalid_date', __( 'Invalid resource date.', 'erp' ), [ 'status' => 404 ] );
        }

        $data     = erp_acct_clsbl_get_closest_next_fn_year( $request['date'] );
        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get current financial year
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_closest_fn_year( $request ) {
        $data     = erp_acct_get_closest_fn_year_date( erp_current_datetime()->format( 'Y-m-d' ) );
        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }
}
