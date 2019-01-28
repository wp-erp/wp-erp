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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/type/(?P<voucher_no>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_transaction_type' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ]
        ] );

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
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/sales/chart-status', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_sales_chart_status' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/sales/chart-payment', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_sales_chart_payment' ],
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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/income_expense_overview', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_income_expense_overview' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/expense/chart-expense', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_expense_chart_data' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/expense/chart-status', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_expense_chart_status' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/purchase/chart-purchase', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_purchase_chart_data' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/purchase/chart-status', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_purchase_chart_status' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sales_summary' );
                },
            ]
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/payment-methods', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_payment_methods' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_expense' );
                },
            ]
        ] );

    }

    /**
     * Get transactions type
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_transaction_type( $request ) {
        $voucher_no = ! empty( $request['voucher_no'] ) ? $request['voucher_no'] : 0;

        $voucher_type = erp_acct_get_transaction_type( $voucher_no );

        $response = rest_ensure_response( $voucher_type );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get sales transactions
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales( $request ) {
        $args = [
            'number' => empty( $request['per_page'] ) ? 20 : $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $formatted_items = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $transactions = erp_acct_get_sales_transactions( $args );
        $total_items = erp_acct_get_sales_transactions( [ 'count' => true, 'number' => -1 ] );

        foreach ( $transactions as $transaction ) {
            $data = $this->prepare_item_for_response( $transaction, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Chart status
     */
    public function get_sales_chart_status( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $chart_status = erp_acct_get_sales_chart_status($args);

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
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $chart_payment = erp_acct_get_sales_chart_payment($args);

        $response = rest_ensure_response( $chart_payment );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get Dashboard Income Expense Overview data
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function get_income_expense_overview( $request ){

        $data = erp_acct_get_income_expense_chart_data();

        $response = rest_ensure_response( $data );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get expense chart stauts data
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function get_expense_chart_data( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $chart_payment = erp_acct_get_expense_chart_data($args);

        $response = rest_ensure_response( $chart_payment );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get expense Chart status
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function get_expense_chart_status( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $chart_status = erp_acct_get_expense_chart_status($args);

        $response = rest_ensure_response( $chart_status );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get Expense transactions
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_expenses( $request ) {
        $args = [
            'number' => empty( $request['per_page'] ) ? 20 : $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $formatted_items = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $transactions = erp_acct_get_expense_transactions( $args );
        $total_items = erp_acct_get_expense_transactions( [ 'count' => true, 'number' => -1 ] );

        foreach ( $transactions as $transaction ) {
            $data = $this->prepare_item_for_response( $transaction, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get Purchase chart stauts data
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function get_purchase_chart_data( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $chart_payment = erp_acct_get_purchase_chart_data($args);

        $response = rest_ensure_response( $chart_payment );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get Purchase Chart status
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function get_purchase_chart_status( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $chart_status = erp_acct_get_purchase_chart_status($args);

        $response = rest_ensure_response( $chart_status );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get Purchase transactions
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_purchases( $request ) {
        $args = [
            'number' => empty( $request['per_page'] ) ? 20 : $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date' => empty( $request['end_date'] ) ? date('Y-m-d') : $request['end_date']
        ];

        $formatted_items = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $transactions = erp_acct_get_purchase_transactions( $args );
        $total_items = erp_acct_get_purchase_transactions( [ 'count' => true, 'number' => -1 ] );

        foreach ( $transactions as $transaction ) {
            $data = $this->prepare_item_for_response( $transaction, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }


    /**
     * Return available payment methods
     *
     * @return array
     */
    public function get_payment_methods() {
        global $wpdb;

        $sql = "SELECT id, name
            FROM {$wpdb->prefix}erp_acct_payment_methods";

        $row = $wpdb->get_results( $sql, ARRAY_A );

        return $row;
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
