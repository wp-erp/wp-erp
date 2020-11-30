<?php

namespace WeDevs\ERP\Accounting\API;

use WeDevs\ERP\Accounting\Includes\Classes\RequestHandler;
use WeDevs\ERP\API\REST_Controller;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Purchase_Return_Controller extends REST_Controller {

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
    protected $rest_base = 'accounting/v1/purchase-return';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/list',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purchases' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_expenses_voucher' );
                    },
                ],

            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/create',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_purchase_return' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_expenses_voucher' );
                    },
                ],

            ]
        );


        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'return_invoice' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],

            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/search-invoice' . '/(?P<id>[\d]+)',
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
     * search purchase voucher for return
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function search_voucher( $request ) {
        $voucherNo = $request['id'];

        $invoice_data = erp_acct_get_invoice_for_purchase_return( $voucherNo );


        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 200 );

        return $response;
    }


    /**
     * Get Purchase transactions
     *
     * @param WP_REST_Request $request
     *
     * @return object
     */
    public function get_purchases( $request ) {
        $args = [
            'number'     => (int) empty( $request['per_page'] ) ? 20 : (int) $request['per_page'],
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
            'status'     => empty( $request['status'] ) ? '' : $request['status'],
            'type'       => empty( $request['type'] ) ? '' : $request['type'],
            'vendor_id'  => empty( $request['vendor_id'] ) ? '' : $request['vendor_id'],
        ];


        $transactions = erp_acct_get_purchase_return_list( $args );
        $total_items  = erp_acct_get_purchase_transactions(
            [
                'count'  => true,
                'number' => - 1,
            ]
        );


        $response = rest_ensure_response( $transactions );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }


    /**
     * Get a collection of invoices
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function return_invoice( $request ) {
        $args = [
            'voucher_no' => $request['id'],
        ];

        $invoice_data = erp_acct_get_purchase_return_invoice( $args );


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
    public function create_purchase_return( $request ) {


        $invoice_data = $this->prepare_item_for_database( $request );

        $item_total          = 0;
        $item_discount_total = 0;
        $item_tax_total      = 0;
        $items               = $request['line_items'];

        foreach ( $items as $value ) {
            $sub_total = $value['qty'] * $value['price'];

            $item_total          += $sub_total;
            $item_tax_total      += $value['tax'] * $value['qty'];
            $item_discount_total += $value['discount'] * $value['qty'];
        }

        $invoice_data['discount']      = $item_discount_total;
        $invoice_data['discount_type'] = $request['discount_type'];
        $invoice_data['tax']           = $item_tax_total;
        $invoice_data['amount']        = $item_total;

        $invoice_id = erp_acct_insert_purchase_return( $invoice_data );

        $invoice_data['id'] = $invoice_id;

        $this->add_log( $invoice_data, 'add' );

        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 201 );

        return $response;
    }

    public function prepare_item_for_database( $request ) {
        $prepared_item = [];

        if ( isset( $request['vendor_id'] ) ) {
            $prepared_item['vendor_id'] = $request['vendor_id'];
        }

        if ( isset( $request['vendor_name'] ) ) {
            $prepared_item['vendor_name'] = $request['vendor_name'];
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

        if ( isset( $request['status'] ) ) {
            $prepared_item['status'] = $request['status'];
        }

        if ( isset( $request['return_reason'] ) ) {
            $prepared_item['return_reason'] = $request['return_reason'];
        }

        if ( isset( $request['voucher_no'] ) ) {
            $prepared_item['purchase_voucher_no'] = $request['voucher_no'];
        }

        $prepared_item['request'] = $request;

        return $prepared_item;
    }

    /**
     * Log when invoice is created
     *
     * @param $data
     * @param $action
     */
    public function add_log( $data, $action ) {
        erp_log()->add(
            [
                'component'     => 'Accounting',
                'sub_component' => __( 'Sales Return', 'erp' ),
                'old_value'     => '',
                'new_value'     => '',
                // translators: %1$s: amount, %2$s: id
                'message'       => sprintf( __( 'An invoice of %1$s has been return for %2$s', 'erp' ), $data['amount'], erp_acct_get_people_name_by_people_id( $data['customer_id'] ) ),
                'changetype'    => $action,
                'created_by'    => get_current_user_id(),
            ]
        );
    }

}
