<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Checks_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/checks';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_checks' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_sale' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_check' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_payment' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_check' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_payment' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/approve', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'approve_check' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_payment' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/bounce', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'bounce_check' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_sales_payment' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of checks
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_checks( $request ) {
        $args = [
            'number' => isset( $request['per_page'] ) ? $request['per_page'] : 20,
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) )
        ];

        $formatted_items = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $check_data  = erp_acct_get_checks( $args );
        $check_count = erp_acct_get_checks( [ 'count' => true, 'number' => -1 ] );

        foreach ( $check_data as $item ) {
            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'created_by', $include_params ) ) {
                    $item['created_by'] = $this->get_user( $item['created_by'] );
                }
            }

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $check_count );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a check
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function approve_check( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_check_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_check( $id );

        erp_acct_perform_check_action( $item, [ 'action' => '6' ] );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Update a check
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function update_check( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_check_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $check_data = $this->prepare_item_for_database( $request );

        $items = $request['line_items']; $item_total = [];

        foreach ( $items as $key => $item ) {
            $item_total[$key] = $item['line_total'];
        }

        $check_data['amount'] = array_sum( $item_total );

        $check_id = erp_acct_perform_check_action( [ 'action' => '7' ] );

        $check_data['id'] = $check_id; $additional_fields = [];
        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $check_response = $this->prepare_item_for_response( $check_data, $request, $additional_fields );

        $response = rest_ensure_response( $check_response );

        $response->set_status( 200 );

        return $response;

    }

    /**
     * Delete a check
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_check( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_check_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        erp_acct_delete_check( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Void a check
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function bounce_check( $request ) {
        $id = (int) $request['id'];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_check_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_check( $id );

        erp_acct_perform_check_action( $item, [ 'action' => '7' ] );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

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

        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = $request['id'];
        }
        if ( isset( $request['trn_no'] ) ) {
            $prepared_item['trn_no'] = $request['trn_no'];
        }
        if ( isset( $request['check_trn_table_id'] ) ) {
            $prepared_item['check_trn_table_id'] = $request['check_trn_table_id'] ;
        }
        if ( isset( $request['people_name'] ) ) {
            $prepared_item['people_name'] = $request['people_name'];
        }
        if ( isset( $request['payee_name'] ) ) {
            $prepared_item['payee_name'] = $request['payee_name'];
        }
        if ( isset( $request['invoice_no'] ) ) {
            $prepared_item['invoice_no'] = $request['invoice_no'];
        }
        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['trn_date'] = $request['trn_date'];
        }
        if ( isset( $request['ledger_id'] ) ) {
            $prepared_item['ledger_id'] = $request['ledger_id'];
        }
        if ( isset( $request['amount'] ) ) {
            $prepared_item['amount'] = $request['amount'];
        }
        if ( isset( $request['debit'] ) ) {
            $prepared_item['debit'] = $request['debit'];
        }
        if ( isset( $request['credit'] ) ) {
            $prepared_item['credit'] = $request['credit'];
        }
        if ( isset( $request['status'] ) ) {
            $prepared_item['status'] = $request['status'];
        }
        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = maybe_serialize( $request['particulars'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param array|object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'trn_no'          => (int) $item->trn_no,
            'people_name'     => $item->people_name,
            'trn_date'        => $item->trn_date,
            'amount'          => $item->amount,
            'payee_name'      => $item->payee_name,
            'ledger_id'       => $item->ledger_id,
            'particulars'     => $item->particulars,
            'status'          => get_check_status_by_id( $item->trn_no ),
        ];

        $data = array_merge( $data, $additional_fields );

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
            'title'      => 'check',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'voucher_no'  => [
                    'description' => __( 'Voucher no. for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'amount'  => [
                    'description' => __( 'Voucher no. for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'people_name'   => [
                    'description' => __( 'Vendor id for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'trn_date'       => [
                    'description' => __( 'Date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'payee_name'       => [
                    'description' => __( 'Type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'status'       => [
                    'description' => __( 'Status for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'particulars'       => [
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
