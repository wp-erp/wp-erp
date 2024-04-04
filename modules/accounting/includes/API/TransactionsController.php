<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class TransactionsController extends \WeDevs\ERP\API\REST_Controller {

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
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/type/(?P<voucher_no>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_transaction_type' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/statuses',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_trn_statuses' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/sales',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/expenses',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_expenses' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/purchases',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purchases' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/sales/chart-status',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales_chart_status' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/sales/chart-payment',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_sales_chart_payment' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/income-expense-overview',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_income_expense_overview' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/expense/chart-expense',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_expense_chart_data' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/expense/chart-status',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_expense_chart_status' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/purchase/chart-purchase',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purchase_chart_data' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/purchase/chart-status',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_purchase_chart_status' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/payment-methods',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_payment_methods' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/send-pdf' . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'send_as_pdf' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/people-chart' . '/trn-amount' . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_people_trn_amount_data' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/people-chart' . '/trn-status' . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_people_trn_status_data' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_expense' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/voucher-type' . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_voucher_type' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
            ]
        );
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
     * Get transaction statuses
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_trn_statuses( $request ) {
        global $wpdb;

        $statuses = $wpdb->get_results( "SELECT id, type_name as name, slug FROM {$wpdb->prefix}erp_acct_trn_status_types", ARRAY_A );

        $response = rest_ensure_response( $statuses );

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
            'number'     => empty( $request['per_page'] ) ? 20 : (int) $request['per_page'],
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
            'status'     => empty( $request['status'] ) ? '' : $request['status'],
            'type'       => empty( $request['type'] ) ? '' : $request['type'],
            'customer_id'=> empty( $request['customer_id'] ) ? '' : $request['customer_id'],
        ];



        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $transactions = erp_acct_get_sales_transactions( $args );
        $total_items  = erp_acct_get_sales_transactions(
            [
                'count'       => true,
                'number'      => -1,
                'status'      => $args['status'],
                'type'        => $args['type'],
                'start_date'  => $args['start_date'],
                'end_date'    => $args['end_date'],
                'customer_id' => $args['customer_id'],
            ]
        );

        foreach ( $transactions as $transaction ) {
            $data              = $this->prepare_item_for_response( $transaction, $request, $additional_fields );
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
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
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
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_payment = erp_acct_get_sales_chart_payment( $args );

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
    public function get_income_expense_overview( $request ) {
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
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
        ];

        $bill_payment    = erp_acct_get_bill_chart_data( $args );
        $expense_payment = erp_acct_get_expense_chart_data( $args );

        $chart_payment['paid'] = $bill_payment['paid'] + $expense_payment['paid'];

        $chart_payment['payable'] = $bill_payment['payable'] + $expense_payment['payable'];

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
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_statuses = erp_acct_get_bill_chart_status( $args );
        $expense_status = erp_acct_get_expense_chart_status( $args );

        foreach ( $chart_statuses as $bill_status ) {
            if ( $bill_status['type_name'] === $expense_status['type_name'] ) {
                $bill_status['sub_total'] += $expense_status['sub_total'];
            } else {
                array_push( $chart_statuses, $expense_status );
                break;
            }
        }

        if ( ! count( $chart_statuses ) ) {
            array_push( $chart_statuses, $expense_status );
        }

        $response = rest_ensure_response( $chart_statuses );

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
            'number'     => empty( $request['per_page'] ) ? 20 : (int) $request['per_page'],
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
            'status'     => empty( $request['status'] ) ? '' : $request['status'],
            'type'       => empty( $request['type'] ) ? '' : $request['type'],
            'vendor_id'  => empty( $request['vendor_id'] ) ? '' : $request['vendor_id'],
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $transactions = erp_acct_get_expense_transactions( $args );
        $total_items  = erp_acct_get_expense_transactions(
            [
                'count'      => true,
                'number'     => -1,
                'status'     => $args['status'],
                'type'       => $args['type'],
                'start_date' => $args['start_date'],
                'end_date'   => $args['end_date'],
                'vendor_id'  => $args['vendor_id']
            ]
        );

        foreach ( $transactions as $transaction ) {
            $data              = $this->prepare_item_for_response( $transaction, $request, $additional_fields );
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
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_payment = erp_acct_get_purchase_chart_data( $args );

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
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
        ];

        $chart_status = erp_acct_get_purchase_chart_status( $args );

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
            'number'     => (int) empty( $request['per_page'] ) ? 20 : (int) $request['per_page'],
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
            'status'     => empty( $request['status'] ) ? '' : $request['status'],
            'type'       => empty( $request['type'] ) ? '' : $request['type'],
            'vendor_id'  => empty( $request['vendor_id'] ) ? '' : $request['vendor_id'],
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $transactions = erp_acct_get_purchase_transactions( $args );
        $total_items  = erp_acct_get_purchase_transactions(
            [
                'count'      => true,
                'number'     => -1,
                'status'     => $args['status'],
                'type'       => $args['type'],
                'start_date' => $args['start_date'],
                'end_date'   => $args['end_date'],
                'vendor_id'  => $args['vendor_id']
            ]
        );

        foreach ( $transactions as $transaction ) {
            $data              = $this->prepare_item_for_response( $transaction, $request, $additional_fields );
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

        $rows = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}erp_acct_payment_methods", ARRAY_A );

        return apply_filters( 'erp_acct_pay_methods', $rows );
    }

    public function get_voucher_type( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_voucher_type_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $response = erp_acct_get_transaction_type( $id );

        $response = rest_ensure_response( $response );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object|array    $item
     * @param WP_REST_Request $request           request object
     * @param array           $additional_fields (optional)
     * @return object $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $status = null;

        if ( ! empty( $item['inv_status'] ) ) {
            $status = $item['inv_status'];
        } else {
            if ( ! empty( $item['pay_status'] ) ) {
                $status = $item['pay_status'];
            } else {
                if ( ! empty( $item['bill_status'] ) ) {
                    $status = $item['bill_status'];
                } else {
                    if ( ! empty( $item['pay_bill_status'] ) ) {
                        $status = $item['pay_bill_status'];
                    } else {
                        if ( ! empty( $item['expense_status'] ) ) {
                            $status = $item['expense_status'];
                        } else {
                            if ( ! empty( $item['purchase_status'] ) ) {
                                $status = $item['purchase_status'];
                            } else {
                                if ( ! empty( $item['pay_purchase_status'] ) ) {
                                    $status = $item['pay_purchase_status'];
                                }
                            }
                        }
                    }
                }
            }
        }

        $item['status']      = erp_acct_get_trn_status_by_id( $status );
        $item['status_code'] = $status;

        $item['ref']         = isset( $item['ref'] )
                             ? $item['ref']
                             : ( isset( $item['pay_ref'] )
                             ? $item['pay_ref']
                             : ( isset( $item['exp_ref'] )
                             ? $item['exp_ref']
                             : '' ) );

        $data = array_merge( $item, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

    /**
     * Send mail with attachment
     *
     * @param $request
     *
     * @return array|mixed|object
     */
    public function send_as_pdf( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_trn_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $response = [
            'status'  => 304,
            'message' => 'There was an error sending mail!',
        ];

        $file_name   = erp_acct_get_pdf_filename( $request['trn_data']['voucher_no'] );
        $transaction = (object) $request['trn_data'];

        if ( erp_acct_send_email_with_pdf_attached( $request, $transaction, $file_name, 'F' ) ) {
            $response['status']  = 200;
            $response['message'] = 'mail sent successfully.';
        }

        return $response;
    }

    /**
     * Chart transaction data of a people
     */
    public function get_people_trn_amount_data( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
        ];

        $args['people_id'] = $request['id'];

        $bill_payment     = erp_acct_get_bill_chart_data( $args );
        $expense_payment  = erp_acct_get_expense_chart_data( $args );
        $sales_payment    = erp_acct_get_sales_chart_payment( $args );
        $purchase_payment = erp_acct_get_purchase_chart_data( $args );

        $chart_payment['paid']    = $bill_payment['paid'] + $expense_payment['paid'] + $sales_payment['received'] + $purchase_payment['paid'];
        $chart_payment['payable'] = $bill_payment['payable'] + $expense_payment['payable'] + $sales_payment['outstanding'] + $purchase_payment['payable'];

        $response = rest_ensure_response( $chart_payment );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Chart transaction status/s of a people
     */
    public function get_people_trn_status_data( $request ) {
        $args = [
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
        ];

        $args['people_id'] = $request['id'];

        $chart_statuses    = erp_acct_get_bill_chart_status( $args );
        $expense_status    = erp_acct_get_expense_chart_status( $args );
        $sales_statuses    = erp_acct_get_sales_chart_status( $args );
        $purchase_statuses = erp_acct_get_purchase_chart_status( $args );

        foreach ( $chart_statuses as $key => $item ) {
            $chart_statuses[ $key ]['sub_total'] = (int) $chart_statuses[ $key ]['sub_total'];
        }

        foreach ( $sales_statuses  as $key => $item) {
            $sales_statuses[ $key ]['sub_total'] = (int) $sales_statuses[ $key ]['sub_total'];
        }

        foreach (  $purchase_statuses as $key => $item) {
            $purchase_statuses[ $key ]['sub_total'] = (int) $purchase_statuses[ $key ]['sub_total'];
        }

        if ( ! empty( $expense_status ) ) {
            $expense_status['sub_total'] = (int) $expense_status['sub_total'];
            array_push( $chart_statuses, $expense_status );
        }
        $chart_statuses = array_merge( $chart_statuses, $sales_statuses, $purchase_statuses );

        $statuses = [];

        $len = count( $chart_statuses );

        for ( $i = 0; $i < $len; $i++ ) {
            $k = 0;

            if ( is_null( $chart_statuses[ $i ] ) ) {
                continue;
            }

            for ( $j = $i + 1; $j < $len; $j++ ) {
                if ( is_null( $chart_statuses[ $j ] ) ) {
                    continue;
                }

                if ( $chart_statuses[ $i ]['type_name'] === $chart_statuses[ $j ]['type_name'] ) {
                    $chart_statuses[ $i ]['sub_total'] += $chart_statuses[ $j ]['sub_total'];
                    $statuses[ $k ]['type_name']        = $chart_statuses[ $i ]['type_name'];
                    $statuses[ $k ]['sub_total']        = $chart_statuses[ $i ]['sub_total'];
                    $k++;
                    $chart_statuses[ $j ] = null;
                }
            }
            $statuses[ $k ]['type_name'] = $chart_statuses[ $i ]['type_name'];
            $statuses[ $k ]['sub_total'] = $chart_statuses[ $i ]['sub_total'];
            $k++;
        }

        $response = rest_ensure_response( $statuses );

        $response->set_status( 200 );

        return $response;
    }
}
