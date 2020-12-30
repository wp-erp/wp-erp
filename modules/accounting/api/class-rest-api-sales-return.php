<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Sales_Return_Controller extends \WeDevs\ERP\API\REST_Controller {

    /**
     * this class for sales return invoice create, view, list
     * from version of 1.7.0
     */


    /**
     * Endpoint namespace.
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
     * Register the routes for creating sales return invoice.
     */
    public function register_routes() {

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/create',
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

        /**
         *  Register the routes for sales return list.
         */
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/list',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales_return_list' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],

            ]
        );


        /**
         *  Register the routes for single sales return invoice.
         */
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales_return' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],

            ]
        );


        /**
         *  Register the routes for searching sales invoice for return
         */
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
     * Get sales return list
     *
     * @param WP_REST_Request $request
     *
     * @return object
     */
    public function get_sales_return_list( $request ) {
        $args = [
            'number'     => empty( $request['per_page'] ) ? 20 : (int) $request['per_page'],
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? date( 'Y-m-d' ) : $request['end_date'],
            'status'     => empty( $request['status'] ) ? '' : $request['status'],
        ];

        // get paginated
        $transactions = erp_acct_get_sales_return_transactions( $args );

        // get total item number for  pagination
        $total_items = erp_acct_get_sales_return_transactions(
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
     * Get sales return single invoice
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function get_sales_return( $request ) {
        $args = [
            'voucher_no' => $request['id'],
        ];

        $invoice_data = erp_acct_get_sales_return_invoice( $args );


        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * get sales invoice for return
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function search_voucher( $request ) {
        $args = [
            'voucher_no' => $request['id'],
        ];

        $invoice_data = erp_acct_get_invoice_for_return( $args );


        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 200 );

        return $response;
    }


    /**
     * Create sales return invoice
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function create_sales_return( $request ) {

        $invoice_data        = $this->prepare_item_for_database( $request );
        $item_total          = 0;
        $item_discount_total = 0;
        $item_tax_total      = 0;

        $items = $request['line_items'];

        foreach ( $items as $value ) {
            $sub_total = $value['qty'] * $value['unit_price'];

            $item_total          += $sub_total;
            $item_tax_total      += $value['tax'] * $value['qty'];
            $item_discount_total += $value['discount'] * $value['qty'];
        }

        $invoice_data['discount']      = $item_discount_total;
        $invoice_data['discount_type'] = $request['discount_type'];
        $invoice_data['tax_rate_id']   = $request['tax_rate_id'];
        $invoice_data['tax']           = $item_tax_total;
        $invoice_data['amount']        = $item_total;

        $invoice_id = erp_acct_insert_sales_return( $invoice_data );

        // if insert failed
        if ( is_wp_error( $invoice_id ) ) {
            return $invoice_id;
        }

        $invoice_data['id'] = $invoice_id;

        $this->add_log( $invoice_data, 'add' );

        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 201 );

        return $response;
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


    public function prepare_item_for_database( $request ) {
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

        if ( isset( $request['voucher_no'] ) ) {
            $prepared_item['sales_voucher_no'] = $request['voucher_no'];
        }

        $prepared_item['request'] = $request;

        return $prepared_item;
    }

}
