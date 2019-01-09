<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Transactions_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/transactions';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/sales', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_sales' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/expenses', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_expenses' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/purchases', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_purchases' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ]
        ] );

    }

    /**
     * Get sales transactions
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales( $request ) {
        $invoices = erp_acct_get_all_invoices();

        return $invoices;
    }

}
