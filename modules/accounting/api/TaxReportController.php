<?php

namespace WeDevs\ERP\Accounting\API;

use WeDevs\ERP\API\REST_Controller;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class TaxReportController extends REST_Controller {


    /*
     * Added to Version: 1.7.1
     * This class is compilable for composer 2 and also psr4 auto-loading
     * This class for tax related report of Sales and Purchase
     * all sql  query executed inside this class
     * no need to call other class
     *
     * Added Report :
     * Sales:
     * Sales Tax Report: transaction based
     * Sales Tax Report: Customer Based
     * Sales Tax Report: Category Based
     * Sales Tax Report: Agency Based
     *
     * Purchase:
     *Purchase Tax Report: Transaction Based.
     * Purchase Tax Report: Vendor Based
     * Purchase Tax Report: Category Based
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
    protected $rest_base = 'accounting/v1/tax-reports';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/sales',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales_tax' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/customer-wise-sales',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales_tax_customer_wise' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/category-wise-sales',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales_tax_category_wise' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );


        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/purchase',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purchase_tax' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/vendor-wise-purchase',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_vendor_wise_purchase' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/category-wise-purchase',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purchase_tax_category_wise' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

    }

    /**
     * get sales tax depending on sales transaction
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales_tax( $request ) {
        global $wpdb;
        $args = [
            'start_date' => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'   => ! empty( $request['end_date'] ) ? $request['end_date'] : null,
        ];

        $sql = "SELECT trn_date,voucher_no, tax as tax_amount
        FROM {$wpdb->prefix}erp_acct_invoices 
        WHERE tax > 0  AND trn_date BETWEEN '%s' AND '%s'";

        $taxData = $wpdb->get_results( $wpdb->prepare( $sql, $args['start_date'], $args['end_date'] ), ARRAY_A );

        return wp_send_json( $taxData );
    }


    /**
     * get sales tax : Customer Based
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales_tax_customer_wise( $request ) {
        global $wpdb;
        $args = [
            'start_date'  => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'    => ! empty( $request['end_date'] ) ? $request['end_date'] : null,
            'customer_id' => ! empty( $request['customer_id'] ) ? $request['customer_id'] : null,
        ];

        $sql = "SELECT trn_date,voucher_no, tax as tax_amount,customer_id, customer_name
        FROM {$wpdb->prefix}erp_acct_invoices 
        WHERE tax > 0 AND customer_id = %d AND trn_date BETWEEN '%s' AND '%s'";

        $taxData = $wpdb->get_results( $wpdb->prepare( $sql, $args['customer_id'], $args['start_date'], $args['end_date'] ), ARRAY_A );

        return wp_send_json( $taxData );
    }


    /**
     *  get sales tax : Category Based
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales_tax_category_wise( $request ) {
        global $wpdb;
        $args = [
            'start_date'  => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'    => ! empty( $request['end_date'] ) ? $request['end_date'] : null,
            'category_id' => ! empty( $request['category_id'] ) ? $request['category_id'] : null,
        ];

        $sql = "SELECT created_at as trn_date,trn_no as voucher_no, sum(tax) as tax_amount,tax_cat_id
        FROM {$wpdb->prefix}erp_acct_invoice_details 
        WHERE tax > 0 AND tax_cat_id = %d AND created_at BETWEEN '%s' AND '%s' GROUP BY trn_no";

        $taxData = $wpdb->get_results( $wpdb->prepare( $sql, $args['category_id'], $args['start_date'], $args['end_date'] ), ARRAY_A );

        return wp_send_json( $taxData );
    }


    /**
     *   get Purchase vat : Transaction  Based
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_purchase_tax( $request ) {
        global $wpdb;
        $args = [
            'start_date' => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'   => ! empty( $request['end_date'] ) ? $request['end_date'] : null,
        ];

        $sql = "SELECT trn_date,voucher_no, tax as tax_amount
        FROM {$wpdb->prefix}erp_acct_purchase 
        WHERE tax > 0  AND trn_date BETWEEN '%s' AND '%s'";

        $taxData = $wpdb->get_results( $wpdb->prepare( $sql, $args['start_date'], $args['end_date'] ), ARRAY_A );

        return wp_send_json( $taxData );
    }


    /**
     * get Purchase vat : Vendor  Based
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_vendor_wise_purchase( $request ) {
        global $wpdb;
        $args = [
            'start_date' => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'   => ! empty( $request['end_date'] ) ? $request['end_date'] : null,
            'vendor_id'  => ! empty( $request['vendor_id'] ) ? $request['vendor_id'] : null,
        ];

        $sql = "SELECT trn_date,voucher_no, tax as tax_amount,vendor_id, vendor_name
        FROM {$wpdb->prefix}erp_acct_purchase 
        WHERE tax > 0 AND vendor_id = %d AND trn_date BETWEEN '%s' AND '%s'";

        $taxData = $wpdb->get_results( $wpdb->prepare( $sql, $args['vendor_id'], $args['start_date'], $args['end_date'] ), ARRAY_A );

        return wp_send_json( $taxData );
    }


    /**
     * get Purchase vat : Category  Based
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_purchase_tax_category_wise( $request ) {
        global $wpdb;
        $args = [
            'start_date'  => ! empty( $request['start_date'] ) ? $request['start_date'] : null,
            'end_date'    => ! empty( $request['end_date'] ) ? $request['end_date'] : null,
            'category_id' => ! empty( $request['category_id'] ) ? $request['category_id'] : null,
        ];

        $sql = "SELECT created_at as trn_date,trn_no as voucher_no, sum(tax) as tax_amount,tax_cat_id
        FROM {$wpdb->prefix}erp_acct_purchase_details 
        WHERE tax > 0 AND tax_cat_id = %d AND created_at BETWEEN '%s' AND '%s' GROUP BY trn_no";

        $taxData = $wpdb->get_results( $wpdb->prepare( $sql, $args['category_id'], $args['start_date'], $args['end_date'] ), ARRAY_A );

        return wp_send_json( $taxData );
    }


}
