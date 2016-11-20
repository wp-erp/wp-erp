<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Journals_Controller extends REST_Controller {
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'accounting/journals';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_journals' ],
                'args'     => $this->get_collection_params(),
            ],
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'create_journal' ],
                'args'     => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_journal' ],
                'args'     => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of journals
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_journals( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'type'   => 'journal',
        ];

        $items          = erp_ac_get_ledger_transactions( $args );
        $args['number'] = -1;
        $total_items    = count( erp_ac_get_ledger_transactions( $args ) );

        // dd( $items );

        $formated_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific journal
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_journal( $request ) {
        $id   = (int) $request['id'];
        $item = \WeDevs\ERP\Accounting\Model\Transaction::find( $id );

        if ( empty( $id ) || empty( $item->id ) || $item->type != 'journal' ) {
            return new WP_Error( 'rest_journal_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $transaction_items = $item->items->toArray();
        $items             = [];
        foreach ( $transaction_items as $transaction_item ) {
            $items[] = [
                'journal_id' => (int) $transaction_item['journal_id'],
                'unit_price' => (float) $transaction_item['unit_price'],
                'total'      => (float) $transaction_item['line_total'],
            ];
        }

        dd($transaction_items);

        $additional_fields = ['items' => $items];

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create a journal
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_journal( $request ) {
        $trans_data = $this->prepare_item_for_database( $request );

        if ( empty( $request['form_type'] ) || ! in_array( $request['form_type'], ['payment', 'invoice'] ) ) {
            return new WP_Error( 'rest_sale_invalid_form_type', __( 'Invalid form type.' ), [ 'status' => 404 ] );
        }

        if ( empty( $request['customer'] ) ) {
            return new WP_Error( 'rest_sale_invalid_customer', __( 'Required customer.' ), [ 'status' => 404 ] );
        }

        if ( $trans_data['form_type'] == 'payment' ) {
            $due_transactions = erp_ac_get_all_transaction([
                'status'      => ['in' => ['awaiting_payment', 'partial']],
                'user_id'     => $request['customer'],
                'parent'      => 0,
                'type'        => 'sales',
                'join'        => ['journals'],
                'with_ledger' => true,
                'output_by'   => 'array',
            ]);

            if ( ! empty( $due_transactions ) && empty( $request['partial_id'] ) ) {
                $due_items = [];

                foreach ( $due_transactions as $due_transaction ) {
                    $due_items[] = [
                        'transaction_id' => (int) $due_transaction['id'],
                        'account_id'     => 1,
                        'due'            => (float) $due_transaction['due'],
                    ];
                }

                $error_response = rest_ensure_response( [
                    'code'    => 'rest_sale_due_invoices',
                    'message' => __( 'You\'ve some due invoices.', 'erp' ),
                    'data'    => $due_items
                ] );
                $error_response->set_status( 404 );

                return $error_response;
            }

            if ( ! empty( $request['partial_id'] ) ) {
                $trans_data['status'] = 'closed';
            }
        }

        $items = $this->prepare_trans_items_for_database( $request );

        $tax_total = array_reduce( $items, function( $total, $value ) {
            return $total + $value['tax_rate'];
        } );

        $trans_data['sub_total'] = array_reduce( $items, function( $total, $value ) {
            return $total + $value['line_total'];
        } );

        $trans_data['trans_total'] = $trans_data['sub_total'] + $tax_total;
        $trans_data['total']       = $trans_data['trans_total'];
        $trans_data['line_total']  = array_pluck( $items, 'line_total' );

        if ( $trans_data['form_type'] == 'invoice' ) {
            $trans_data['due'] = $trans_data['total'];
        }

        $id = erp_ac_insert_transaction( $trans_data, $items );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        $transaction = \WeDevs\ERP\Accounting\Model\Transaction::find( $id );
        $transaction->items = $transaction->items->toArray();

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $transaction, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Prepare a single item for create or update
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];

        $prepared_item['id']              = isset( $request['id'] ) ? intval( $request['id'] ) : 0;
        $prepared_item['type']            = 'sales';
        $prepared_item['form_type']       = isset( $request['form_type'] ) ? sanitize_text_field( $request['form_type'] ) : '';
        $prepared_item['account_id']      = isset( $request['account_id'] ) ? intval( $request['account_id'] ) : 0;

        if ( $request['form_type'] == 'payment' ) {
            $prepared_item['partial_id']  = ! empty( $request['partial_id'] ) ? explode( ",", str_replace( " ", "", $request['partial_id'] ) ) : [];
        }

        if ( $request['form_type'] == 'invoice' ) {
            $prepared_item['account_id'] = 1;
        }

        $prepared_item['status']          = ( $prepared_item['form_type'] == 'payment' ) ? 'closed' : 'draft';
        $prepared_item['user_id']         = isset( $request['customer'] ) ? intval( $request['customer'] ) : 0;
        $prepared_item['billing_address'] = isset( $request['billing_address'] ) ? wp_kses_post( $request['billing_address'] ) : '';
        $prepared_item['ref']             = isset( $request['reference'] ) ? sanitize_text_field( $request['reference'] ) : '';
        $prepared_item['issue_date']      = isset( $request['issue_date'] ) ? sanitize_text_field( $request['issue_date'] ) : '';
        $prepared_item['due_date']        = isset( $request['due_date'] ) ? sanitize_text_field( $request['due_date'] ) : '';
        $prepared_item['summary']         = isset( $request['summary'] ) ? wp_kses_post( $request['summary'] ) : '';
        $prepared_item['currency']        = isset( $request['currency'] ) ? sanitize_text_field( $request['currency'] ) : 'USD';

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'         => (int) $item->id,
            'reference'  => $item->ref,
            'issue_date' => $item->issue_date,
            'summary'    => $item->summary,
            'debit'      => (float) $item->debit,
            'credit'     => (float) $item->credit,
            'total'      => (float) $item->total,
            'created_at' => $item->created_at,
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'created_by', $include_params ) ) {
                $data['created_by'] = $this->get_user( intval( $item->created_by ) );
            }
        }

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item );

        return $response;
    }

    /**
     * Get the User's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'sale',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'type'  => [
                    'description' => __( 'Type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'status'  => [
                    'description' => __( 'Status for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ];

        return $schema;
    }
}