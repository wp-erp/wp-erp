<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Sales_Controller extends REST_Controller {
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
    protected $rest_base = 'accounting/sales';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_sales' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sale' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_sale' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => [ $this, 'create_update_permission_check' ],
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_sale' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sale' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_sale' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => [ $this, 'create_update_permission_check' ],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_sale' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sale' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of sales
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sales( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'type'   => 'sales',
            'join'   => ['items'],
        ];

        $items       = erp_ac_get_all_transaction( $args );
        $total_items = erp_ac_get_transaction_count( ['type' => 'sales'] );

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
     * Get a specific sale
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_sale( $request ) {
        $id   = (int) $request['id'];
        $item = \WeDevs\ERP\Accounting\Model\Transaction::find( $id );

        if ( empty( $id ) || empty( $item->id ) || $item->type != 'sales' ) {
            return new WP_Error( 'rest_sale_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item->items = $item->items->toArray();

        $additional_fields = [];

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create a sale
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_sale( $request ) {
        $trans_data = $this->prepare_item_for_database( $request );

        if ( empty( $request['form_type'] ) || ! in_array( $request['form_type'], ['payment', 'invoice'] ) ) {
            return new WP_Error( 'rest_sale_invalid_form_type', __( 'Invalid form type.' ), [ 'status' => 400 ] );
        }

        if ( empty( $request['customer'] ) ) {
            return new WP_Error( 'rest_sale_invalid_customer', __( 'Required customer.' ), [ 'status' => 400 ] );
        }

        if ( $request['form_type'] == 'invoice' && ( ! isset( $request['due_date'] ) || empty( $request['due_date'] ) ) ) {
            return new WP_Error( 'rest_sale_invalid_due_date', __( 'Required due_date field.' ), [ 'status' => 400 ] );
        }

        if ( $request['form_type'] == 'payment' && ( ! isset( $request['account_id'] ) || empty( $request['account_id'] ) ) ) {
            return new WP_Error( 'rest_sale_invalid_account_id', __( 'Required account_id field.' ), [ 'status' => 400 ] );
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
     * Update a sale
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_sale( $request ) {
        $id = (int) $request['id'];

        $item = (object) erp_ac_get_transaction( $id );
        if ( ! $item ) {
            return new WP_Error( 'rest_sale_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        if ( empty( $request['form_type'] ) || ! in_array( $request['form_type'], ['payment', 'invoice'] ) ) {
            return new WP_Error( 'rest_sale_invalid_form_type', __( 'Invalid form type.' ), [ 'status' => 400 ] );
        }

        if ( empty( $request['customer'] ) ) {
            return new WP_Error( 'rest_sale_invalid_customer', __( 'Required customer.' ), [ 'status' => 400 ] );
        }

        if ( $request['form_type'] == 'invoice' && ( ! isset( $request['due_date'] ) || empty( $request['due_date'] ) ) ) {
            return new WP_Error( 'rest_sale_invalid_due_date', __( 'Required due_date field.' ), [ 'status' => 400 ] );
        }

        if ( $request['form_type'] == 'payment' && ( ! isset( $request['account_id'] ) || empty( $request['account_id'] ) ) ) {
            return new WP_Error( 'rest_sale_invalid_account_id', __( 'Required account_id field.' ), [ 'status' => 400 ] );
        }

        $trans_data = $this->prepare_item_for_database( $request );

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

        $id = erp_ac_insert_transaction( $trans_data, $items );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        $transaction = \WeDevs\ERP\Accounting\Model\Transaction::find( $id );
        $transaction->items = $transaction->items->toArray();

        $response = $this->prepare_item_for_response( $transaction, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete a sale
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_sale( $request ) {
        $id = (int) $request['id'];

        $result = erp_ac_remove_transaction( $id );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( true, 204 );
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
            'id'                => (int) $item->id,
            'form_type'         => $item->form_type,
            'status'            => $item->status,
            'billing_address'   => $item->billing_address,
            'reference'         => $item->ref,
            'summary'           => $item->summary,
            'issue_date'        => $item->issue_date,
            'due_date'          => $item->due_date,
            'currency'          => $item->currency,
            'conversion_rate'   => (float) $item->conversion_rate,
            'items'             => $this->format_transaction_items( $item->items ),
            'sub_total'         => (float) $item->sub_total,
            'total'             => (float) $item->total,
            'due'               => (float) $item->due,
            'trans_total'       => (float) $item->trans_total,
            'invoice'           => erp_ac_get_invoice_number( $item->invoice_number, $item->invoice_format ),
            'parent'            => (int) $item->parent,
            'created_at'        => $item->created_at,
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'customer', $include_params ) ) {
                $customers_controller = new Customers_Controller();

                $customer_id  = (int) $item->user_id;
                $data['customer'] = null;

                if ( $customer_id ) {
                    $customer = $customers_controller->get_customer( ['id' => $customer_id ] );
                    $data['customer'] = ! is_wp_error( $customer ) ? $customer->get_data() : null;
                }
            }

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
     * Prepare transaction items for database
     *
     * @param  array $request
     *
     * @return array
     */
    protected function prepare_trans_items_for_database( $request ) {
        $taxes = erp_ac_get_tax_info();
        $taxes = array_pluck( $taxes, 'rate', 'id' );

        foreach ( $request['items'] as $item ) {
            $unit_price = (float) erp_ac_format_decimal( $item['unit_price'] );
            $qty        = intval( $item['qty'] );
            $discount   = (int) erp_ac_format_decimal( $item['discount'] );
            $tax        = isset( $item['tax'] ) ? $item['tax'] : 0;

            $items[] = [
                'item_id'     => isset( $item['id'] ) ? intval( $item['id'] ) : 0,
                'journal_id'  => isset( $item['journal_id'] ) ? intval( $item['journal_id'] ) : 0,
                'product_id'  => isset( $item['product_id'] ) ? intval( $item['product_id'] ) : 0,
                'account_id'  => isset( $item['account_id'] ) ? intval( $item['account_id'] ) : 0,
                'description' => sanitize_text_field( $item['description'] ),
                'qty'         => $qty,
                'unit_price'  => $unit_price,
                'discount'    => $discount,
                'line_total'  => ( ( $unit_price * $qty ) - $discount ),
                'tax'         => $tax,
                'tax_rate'    => isset( $taxes[ $tax ] ) ? $taxes[ $tax ] : 0,
                'tax_journal' => isset( $item['tax_journal'] ) ? $item['tax_journal'] : 0
            ];
        }

        return $items;
    }

    /**
     * Format the transaction's items
     *
     * @param  array $items
     *
     * @return array
     */
    protected function format_transaction_items( $items ) {
        return array_map( function( $item ) {
            return [
                'id'          => (int) $item['id'],
                'journal_id'  => (int) $item['journal_id'],
                'product_id'  => (int) $item['product_id'],
                'description' => $item['description'],
                'qty'         => (int) $item['qty'],
                'unit_price'  => (float) $item['unit_price'],
                'discount'    => (float) $item['discount'],
                'tax'         => (float) $item['tax'],
                'tax_rate'    => (float) $item['tax_rate'],
                'tax_journal' => (int) $item['tax_journal'],
                'line_total'  => (float) $item['line_total'],
                'order'       => (int) $item['order'],
            ];
        }, $items );
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
                'id'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'form_type'       => [
                    'description' => __( 'Form type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'account_id'      => [
                    'description' => __( 'Account id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
                'billing_address' => [
                    'description' => __( 'Billing address for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'reference'       => [
                    'description' => __( 'Reference for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'summary'         => [
                    'description' => __( 'Summary for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'issue_date'      => [
                    'description' => __( 'Issue date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'due_date'        => [
                    'description' => __( 'Due date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'currency'        => [
                    'description' => __( 'Currency for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'customer'        => [
                    'description' => __( 'Customer for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'items'           => [
                    'description' => __( 'Items for the resource.' ),
                    'type'        => 'array',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
            ],
        ];

        return $schema;
    }

    /**
     * Sale create/update permission check
     *
     * @param  [type] $request
     *
     * @return boolean
     */
    public function create_update_permission_check( $request ) {
        $form_type = isset( $request['form_type'] ) ? $request['form_type'] : '';

        switch ( $form_type ) {
            case 'invoice':
                return current_user_can( 'erp_ac_create_sales_invoice' );
                break;

            case 'payment':
                return current_user_can( 'erp_ac_create_sales_payment' );
                break;

            default:
                return false;
                break;
        }
    }
}