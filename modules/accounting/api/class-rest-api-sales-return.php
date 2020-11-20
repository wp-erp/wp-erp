<?php

namespace WeDevs\ERP\Accounting\API;

use WeDevs\ERP\Accounting\Includes\Classes\RequestHandler;
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
            '/' . $this->rest_base .'/create',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_sales_return' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],

            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/search-invoice'.'/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'search_voucher' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],

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
            'voucher_no'     => $request['id']
        ];

        $invoice_data = erp_acct_get_invoice_for_return( $args );


        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 200 );

        return $response;
    }


    /**
     * Create an invoice
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_sales_return( $request ) {

        $requests = new RequestHandler($request) ;

        return wp_send_json($requests->all());

        $invoice_data = $this->prepare_item_for_database( $request );

        $item_total          = 0;
        $item_discount_total = 0;
        $item_tax_total      = 0;
        $additional_fields   = [];

        $items = $request['line_items'];

        foreach ( $items as $value ) {
            $sub_total = $value['qty'] * $value['unit_price'];

            $item_total += $sub_total;
            $item_tax_total += $value['tax'] * $value['qty'];
            $item_discount_total += $value['discount'] * $value['qty'];
        }

        $invoice_data['discount']        = $item_discount_total;
        $invoice_data['discount_type']   = $request['discount_type'];
        $invoice_data['tax_rate_id']     = $request['tax_rate_id'];
        $invoice_data['tax']             = $item_tax_total;
        $invoice_data['amount']          = $item_total;

        $invoice_id = erp_acct_insert_sales_return( $invoice_data );

        $invoice_data['id'] = $invoice_id;

        $this->add_log( $invoice_data, 'add' );

        $invoice_data = $this->prepare_item_for_response( $invoice_data, $request, $additional_fields );

        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 201 );

        return $response;
    }


    public function prepare_item_for_database($request){
        $prepared_item = [];

        if ( isset( $request['customer_id'] ) ) {
            $prepared_item['customer_id'] = $request['customer_id'];
        }

        if ( isset( $request['return_date'] ) ) {
            $prepared_item['return_date'] = $request['return_date'];
        }

        if ( isset( $request['line_items'] ) ) {
            $prepared_item['line_items'] = $request['line_items'];
        }

        if ( isset( $request['discount_type'] ) ) {
            $prepared_item['discount_type'] = $request['discount_type'];
        }

        if ( isset( $request['tax_rate_id'] ) ) {
            $prepared_item['tax_rate_id'] = $request['tax_rate_id'];
        }

        if ( isset( $request['status'] ) ) {
            $prepared_item['status'] = $request['status'];
        }

        if ( isset( $request['return_reason'] ) ) {
            $prepared_item['return_reason'] = $request['return_reason'];
        }

        if ( isset( $request['sales_voucher_no'] ) ) {
            $prepared_item['sales_voucher_no'] = $request['voucher_no'];
        }

        $prepared_item['request'] = $request;

        return $prepared_item;
    }

}
