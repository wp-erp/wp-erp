<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\ERP\API\REST_Controller;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class InvoicesController extends REST_Controller {

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
    protected $rest_base = 'accounting/v1/invoices';

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
                    'callback'            => [ $this, 'get_invoices' ],
                    'args'                => $this->get_collection_params(),
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sale' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_invoice' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_sales_invoice' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_invoice' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_sales_summary' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_invoice' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_sales_invoice' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/void',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'void_invoice' ],
                    'args'                => $this->get_collection_params(),
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_sales_invoice' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/due' . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'due_invoices' ],
                    'args'                => $this->get_collection_params(),
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_sales_invoice' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/attachments',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'upload_attachments' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_sales_invoice' );
                    },
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/overview-receivable',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_overview_receivables' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_sales_invoice' );
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
    public function get_invoices( $request ) {
        $args = [
            'number'     => $request['per_page'],
            'offset'     => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'start_date' => empty( $request['start_date'] ) ? '' : $request['start_date'],
            'end_date'   => empty( $request['end_date'] ) ? gmdate( 'Y-m-d' ) : $request['end_date'],
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $invoice_data = erp_acct_get_all_invoices( $args );
        $total_items  = erp_acct_get_all_invoices(
            [
                'count'  => true,
                'number' => -1,
            ]
        );

        foreach ( $invoice_data as $item ) {
            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'created_by', $include_params, true ) ) {
                    $item['created_by'] = $this->get_user( $item['created_by'] );
                }
            }

            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get an invoice
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_invoice( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_invoice_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_invoice( $id );

        $link_hash    = erp_acct_get_invoice_link_hash( $id, 'invoice' );
        $readonly_url = add_query_arg(
            [
                'query'    => 'readonly_invoice',
                'trans_id' => $id,
                'auth'     => $link_hash,
            ],
            site_url()
        );

        $item['readonly_url'] = $readonly_url;

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;
        $item                           = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response                       = rest_ensure_response( $item );

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
    public function create_invoice( $request ) {
        $invoice_data = $this->prepare_item_for_database( $request );

        $item_total          = 0;
        $item_discount_total = 0;
        $item_tax_total      = 0;
        $additional_fields   = [];

        $items = $request['line_items'];

        foreach ( $items as $value ) {
            $sub_total            = $value['qty'] * $value['unit_price'];
            $item_total          += $sub_total;
            $item_tax_total      += $value['tax'];
            $item_discount_total += $value['discount'];
        }

        $invoice_data['billing_address'] = maybe_serialize( $request['billing_address'] );
        $invoice_data['discount']        = $item_discount_total;
        $invoice_data['discount_type']   = $request['discount_type'];
        $invoice_data['tax_rate_id']     = $request['tax_rate_id'];
        $invoice_data['tax']             = $item_tax_total;
        $invoice_data['amount']          = $item_total;
        $invoice_data['attachments']     = maybe_serialize( $request['attachments'] );
        $additional_fields['namespace']  = $this->namespace;
        $additional_fields['rest_base']  = $this->rest_base;

        $invoice = erp_acct_insert_invoice( $invoice_data );

        if ( is_wp_error( $invoice ) ) {
            $response = rest_ensure_response( $invoice );
            $response->set_status( 507 );
        }

        $invoice_data['id'] = $invoice['id'];
        $this->add_log( $invoice_data, 'add' );
        $response = $this->prepare_item_for_response( $invoice_data, $request, $additional_fields );

        return $response;
    }

    /**
     * Update an invoice
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_invoice( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_invoice_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $can_edit = erp_acct_check_voucher_edit_state( $id );

        if ( ! $can_edit ) {
            return new WP_Error( 'rest_invoice_invalid_edit', __( 'Invalid edit permission for update.', 'erp' ), [ 'status' => 403 ] );
        }

        $invoice_data = $this->prepare_item_for_database( $request );

        $item_total          = 0;
        $item_discount_total = 0;
        $item_tax_total      = 0;
        $additional_fields   = [];

        $items = $request['line_items'];

        foreach ( $items as $value ) {
            $sub_total            = $value['qty'] * $value['unit_price'];
            $item_total          += $sub_total;
            $item_tax_total      += $value['tax'];
            $item_discount_total += $value['discount'];
        }

        $invoice_data['billing_address'] = maybe_serialize( $request['billing_address'] );
        $invoice_data['discount']        = $item_discount_total;
        $invoice_data['discount_type']   = $request['discount_type'];
        $invoice_data['tax_rate_id']     = $request['tax_rate_id'];
        $invoice_data['tax']             = $item_tax_total;
        $invoice_data['amount']          = $item_total;
        $invoice_data['attachments']     = maybe_serialize( $request['attachments'] );
        $additional_fields['namespace']  = $this->namespace;
        $additional_fields['rest_base']  = $this->rest_base;

        $old_data   = erp_acct_get_invoice( $id );
        $invoice_id = erp_acct_update_invoice( $invoice_data, $id );
        $new_data   = erp_acct_get_invoice( $id );

        $this->add_log( $new_data, 'edit', $old_data );

        $invoice_data['id'] = $invoice_id;

        $invoice_data = $this->prepare_item_for_response( $invoice_data, $request, $additional_fields );

        $response = rest_ensure_response( $invoice_data );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Void an invoice
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function void_invoice( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_invoice_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        erp_acct_void_invoice( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Get a collection of invoices with due of a customer
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function due_invoices( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_invoice_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $args = [
            'number' => isset( $request['per_page'] ) ? $request['per_page'] : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $invoice_data = erp_acct_receive_payments_from_customer( [ 'people_id' => $id ] );
        $total_items  = count( $invoice_data );

        foreach ( $invoice_data as $item ) {
            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'created_by', $include_params, true ) ) {
                    $item['created_by'] = $this->get_user( $item['created_by'] );
                }
            }

            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get Dashboard Recievables segments
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function get_overview_receivables( $request ) {
        $items    = erp_acct_get_recievables_overview();
        $response = rest_ensure_response( $items );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Upload attachment for invoice
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function upload_attachments( $request ) {
        $file_names     = isset( $_FILES['attachments']['name'] ) ? array_map( 'sanitize_file_name', (array) wp_unslash( $_FILES['attachments']['name'] ) ) : [];
        $file_tmp_names = isset( $_FILES['attachments']['tmp_name'] ) ? array_map( 'sanitize_url', (array) wp_unslash( $_FILES['attachments']['tmp_name'] ) ) : [];
        $file_types     = isset( $_FILES['attachments']['type'] ) ? array_map( 'sanitize_mime_type', (array) wp_unslash( $_FILES['attachments']['type'] ) ) : [];
        $file_errors    = isset( $_FILES['attachments']['error'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_FILES['attachments']['error'] ) ) : [];
        $file_sizes     = isset( $_FILES['attachments']['size'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_FILES['attachments']['size'] ) ) : [];
        $uploaded_files = [];

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        for ( $i = 0; $i < count( $file_names ); ++ $i ) {
            $upload_data = [
                'name'     => $file_names[ $i ],
                'tmp_name' => $file_tmp_names[ $i ],
                'type'     => $file_types[ $i ],
                'error'    => $file_errors[ $i ],
                'size'     => $file_sizes[ $i ],
            ];

            $uploaded_files[] = wp_handle_upload( $upload_data, [ 'test_form' => false ] );
        }

        $response = rest_ensure_response( $uploaded_files );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Log for invoice related actions
     *
     * @param array  $data
     * @param string $action
     * @param array  $old_data
     *
     * @return void
     */
    public function add_log( $data, $action, $old_data = [] ) {
        switch ( $action ) {
            case 'edit':
                $operation = 'updated';
                $changes   = ! empty( $old_data ) ? erp_get_array_diff( (array) $data, (array) $old_data ) : [];
                unset( $changes['pdf_link'], $changes['attachments'], $changes['line_items'] );
                break;
            case 'delete':
                $operation = 'deleted';
                break;
            default:
                $operation = 'created';
        }

        erp_log()->add(
            [
                'component'     => 'Accounting',
                'sub_component' => __( 'Invoice', 'erp' ),
                'old_value'     => isset( $changes['old_value'] ) ? $changes['old_value'] : '',
                'new_value'     => isset( $changes['new_value'] ) ? $changes['new_value'] : '',
                'message'       => sprintf( __( 'An invoice of %1$s has been %2$s for %3$s', 'erp' ), $data['amount'], $operation, erp_acct_get_people_name_by_people_id( $data['customer_id'] ) ),
                'changetype'    => $action,
                'created_by'    => get_current_user_id(),
            ]
        );
    }

    /**
     * Prepare a single item for create or update
     *
     * @param WP_REST_Request $request request object
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];

        if ( isset( $request['customer_id'] ) ) {
            $prepared_item['customer_id'] = $request['customer_id'];
        }

        if ( isset( $request['date'] ) ) {
            $prepared_item['date'] = $request['date'];
        }

        if ( isset( $request['due_date'] ) ) {
            $prepared_item['due_date'] = $request['due_date'];
        }

        if ( isset( $request['billing_address'] ) ) {
            $prepared_item['billing_address'] = sanitize_textarea_field( $request['billing_address'] );
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

        if ( isset( $request['estimate'] ) ) {
            $prepared_item['estimate'] = $request['estimate'];
        }

        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = maybe_serialize( $request['attachments'] );
        }

        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] =  sanitize_textarea_field( $request['particulars'] );
        }

        if ( isset( $request['additional_notes'] ) ) {
            $prepared_item['additional_notes'] = sanitize_textarea_field( $request['additional_notes'] );
        }

        if ( isset( $request['transaction_by'] ) ) {
            $prepared_item['transaction_by'] = $request['transaction_by'];
        }

        if ( isset( $request['convert'] ) ) {
            $prepared_item['convert'] = $request['convert'];
        }

        $prepared_item['request'] = $request;

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object|array    $item
     * @param WP_REST_Request $request           request object
     * @param array           $additional_fields (optional)
     *
     * @return WP_REST_Response $response response data
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $data = array_merge( $item, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

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
            'title'      => 'invoice',
            'type'       => 'object',
            'properties' => [
                'customer_id'     => [
                    'description' => __( 'Customer id for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'date'            => [
                    'description' => __( 'Date for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'due_date'        => [
                    'description' => __( 'Due date for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'billing_address' => [
                    'description' => __( 'List of billing address data.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'discount_type' => [
                    'description' => __( 'Discount type data.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'tax_rate_id' => [
                    'description' => __( 'Tax rate id.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                ],
                'line_items'      => [
                    'description' => __( 'List of line items data.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'product_id'   => [
                            'description' => __( 'Product id.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'product_type_name' => [
                            'description' => __( 'Product type.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                            'arg_options' => [
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                        'tax_cat_id' => [
                            'description' => __( 'Product type.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'qty'          => [
                            'description' => __( 'Product quantity.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'unit_price'   => [
                            'description' => __( 'Unit price.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'discount'     => [
                            'description' => __( 'Discount.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'tax'          => [
                            'description' => __( 'Tax.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'edit' ],
                        ],
                        'tax_rate'  => [
                            'description' => __( 'Tax percent.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'item_total'   => [
                            'description' => __( 'Item total.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'edit' ],
                        ],
                    ],
                ],
                'type'            => [
                    'description' => __( 'Type for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'status'          => [
                    'description' => __( 'Status for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                ],
                'particulars'          => [
                    'description' => __( 'Status for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'additional_notes'          => [
                    'description' => __( 'Custom text for invoice', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'estimate'        => [
                    'description' => __( 'Status for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
            ],
        ];

        return $schema;
    }
}
