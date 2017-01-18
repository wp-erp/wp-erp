<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Accounting_Reports_Controller extends REST_Controller {
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
    protected $rest_base = 'accounting/reports';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/trial-balances', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_trial_balances' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_reports' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/income-statements', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_income_statements' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_reports' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/sales-taxes', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_sales_taxes' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_reports' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/balance-sheets', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_balance_sheets' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_reports' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of trial balances
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_trial_balances( $request ) {
        $ledgers    = erp_ac_reporting_query();
        $charts     = [];
        $new_charts = [];

        $debit_total  = 0;
        $credit_total = 0;

        if ( ! empty( $ledgers ) ) {
            $x = 0;
            foreach ( $ledgers as $ledger ) {
                $class_key = strtolower( $ledger->class_name );

                if ( $ledger->id == 1 ) {
                    $debit  = floatval( $ledger->debit ) - floatval( $ledger->credit );
                    $credit = 0;
                } else {
                    $debit  = floatval( $ledger->debit );
                    $credit = floatval( $ledger->credit );
                }

                $new_balance = $debit - $credit;

                if ( $new_balance >= 0 ) {
                    $debit  = $new_balance;
                    $credit = 0;
                } else {
                    $credit = abs( $new_balance );
                    $debit  = 0;
                }

                $debit_total  += $debit;
                $credit_total += $credit;

                $charts[ $class_key ][] = null;
                if ( floatval( $ledger->debit ) || floatval( $ledger->credit ) ) {
                    $charts[ $class_key ][] = [
                        'id'     => (int) $ledger->id,
                        'code'   => (int) $ledger->code,
                        'name'   => $ledger->name,
                        'debit'  => (float) $ledger->debit,
                        'credit' => (float) $ledger->credit,
                    ];
                }
            }

            $new_charts = [];
            foreach ( $charts as $key => $value ) {
                $new_charts[ $key ] = array_filter( $value, function( $item ) {
                    return ! is_null( $item );
                } );
            }

            $new_charts['debit_total']  = $debit_total;
            $new_charts['credit_total'] = $credit_total;
        }

        $response = rest_ensure_response( $new_charts );
        $response = $this->format_collection_response( $response, $request, 0 );

        return $response;
    }

    /**
     * Get a collection of income statements
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_income_statements( $request ) {
        $ledgers = erp_ac_reporting_query();

        foreach ($ledgers as $ledger) {
            $charts[$ledger->class_id][$ledger->id][] = $ledger;
        }

        $sales_total   = erp_ac_get_sales_total_without_tax( $charts ) + erp_ac_get_sales_tax_total( $charts );
        $goods_sold    = erp_ac_get_good_sold_total_amount( $charts );
        $expense_total = erp_ac_get_expense_total_without_tax( $charts );
        $expense_total = $expense_total - $goods_sold;
        $tax_total     = erp_ac_get_sales_tax_total( $charts ) + erp_ac_get_expense_tax_total( $charts );

        $gross         = $sales_total - $goods_sold;
        $operating     = $gross - $expense_total;


        $data['revenue']            = (float) erp_ac_get_price( $sales_total, ['symbol' => false] );
        $data['cost_of_goods_sold'] = (float) erp_ac_get_price( $goods_sold, ['symbol' => false] );
        $data['gross_income']       = (float) erp_ac_get_price( $gross, ['symbol' => false] );
        $data['overhead']           = (float) erp_ac_get_price( $expense_total, ['symbol' => false] );
        $data['operating_income']   = (float) erp_ac_get_price( $operating, ['symbol' => false] );
        $data['tax']                = (float) erp_ac_get_price( $tax_total, ['symbol' => false] );
        $data['net_income']         = (float) erp_ac_get_price( ( $operating - $tax_total ), ['symbol' => false] );

        $response = rest_ensure_response( $data );
        $response = $this->format_collection_response( $response, $request, 0 );

        return $response;
    }

    /**
     * Get a collection of sales taxes
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales_taxes( $request ) {
        $taxs  = erp_ac_normarlize_tax_from_transaction();
        $taxes = $taxs['units'];

        $data = [];
        foreach ( $taxes as $tax_id => $tax ) {
            $data[] = [
                'tax' => $tax['sales']['tax_name'],
                'tax_payable' => [
                    'transaction_sub_total' => erp_ac_get_price( $tax['sales']['trns_subtotal'], ['symbol' => false] ),
                    'tax_amount'            => erp_ac_get_price( $tax['sales']['tax_credit'], ['symbol' => false] ),
                ],
                'tax_receivable' => [
                    'transaction_sub_total' => erp_ac_get_price( $tax['expense']['trns_subtotal'], ['symbol' => false] ),
                    'tax_amount'            => erp_ac_get_price( $tax['expense']['tax_debit'], ['symbol' => false] ),
                ],
                'net_tax' => erp_ac_get_price( ( $tax['sales']['tax_credit'] - $tax['expense']['tax_debit'] ), ['symbol' => false] ),
            ];
        }

        $response = rest_ensure_response( $data );
        $response = $this->format_collection_response( $response, $request, 0 );

        return $response;
    }

    /**
     * Get a collection of balance sheets
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_balance_sheets( $request ) {
        $ledgers = erp_ac_reporting_query();

        foreach ( $ledgers as $ledger ) {
            $charts[ $ledger->class_id ][ $ledger->id ][] = $ledger;
        }

        $assets      = ! empty( $charts[1] ) ? $charts[1] : [];
        $liabilities = ! empty( $charts[2] ) ? $charts[2] : [];
        $equities    = ! empty( $charts[5] ) ? $charts[5] : [];

        $sales_total   = erp_ac_get_sales_total_without_tax( $charts ) + erp_ac_get_sales_tax_total( $charts );
        $goods_sold    = erp_ac_get_good_sold_total_amount( $charts );
        $expense_total = erp_ac_get_expense_total_without_tax( $charts );
        $expense_total = $expense_total - $goods_sold;
        $tax_total     = erp_ac_get_sales_tax_total( $charts ) + erp_ac_get_expense_tax_total( $charts );
        $gross         = $sales_total - $goods_sold;
        $operating     = $gross - $expense_total;
        $net_income    = $operating - $tax_total;

        $data = [
            'assets'      => $this->process_individual_balance_sheet( $assets ),
            'liabilities' => $this->process_individual_balance_sheet( $liabilities ),
        ];

        $equities_data             = $this->process_individual_balance_sheet( $equities, $net_income );
        $equities_data['accounts'] = ! empty( $equities_data['accounts'] ) ? $equities_data['accounts'] : [];
        $equities_data['accounts'] = array_merge( $equities_data['accounts'], [
            'net_income' => erp_ac_get_price( $net_income, [ 'symbol' => false ] ),
        ] );

        $data['equities'] = $equities_data;

        $response = rest_ensure_response( $data );
        $response = $this->format_collection_response( $response, $request, 0 );

        return $response;
    }

    /**
     * Process the individual balance sheet
     *
     * @param  array   $items
     * @param  integer $total_balance
     *
     * @return array
     */
    protected function process_individual_balance_sheet( $items, $total_balance = 0 ) {
        $data = [];
        foreach ( $items as $key => $item ) {
            $account = reset( $item );
            $debit   = array_sum( wp_list_pluck( $item, 'debit' ) );
            $credit  = array_sum( wp_list_pluck( $item, 'credit' ) );
            $balance = $debit - $credit;
            $amount  = erp_ac_get_price( $balance, [ 'symbol' => false ] );

            if ( $balance == 0 ) {
                continue;
            }

            $total_balance = $total_balance + $balance;

            $data['accounts'][] = [
                'account' => $account->name,
                'amount'  => $amount,
            ];
        }

        $data['total'] = erp_ac_get_price( $total_balance, [ 'symbol' => false ] );

        return $data;
    }
}