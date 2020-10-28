<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Sales_Return_Controller extends \WeDevs\ERP\API\REST_Controller {

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
    protected $rest_base = 'accounting/v1/sales-return';

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
                    'callback'            => [ $this, 'search_voucher' ],
                    'args'                => $this->get_collection_params(),
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sale' );
                    },
                ]
            ]
        );

    }

    /**
     * Get a collection of invoices
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function search_voucher( $request ) {
        $args = [
            'voucher_no'     => $request['voucher_no']
        ];


        $invoice_data = erp_acct_get_sales_voucher( $args );


        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 200 );

        return $response;
    }


}
