<?php

namespace WeDevs\ERP\Accounting\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class JournalsController extends \WeDevs\ERP\API\REST_Controller {

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
    protected $rest_base = 'accounting/v1/journals';

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
                    'callback'            => [ $this, 'get_journals' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
                    },
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_journal' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_create_journal' );
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
                    'callback'            => [ $this, 'get_journal' ],
                    'args'                => [
                        'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                    ],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/next',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_next_journal_id' ],
                    'args'                => [],
                    'permission_callback' => function ( $request ) {
                        return current_user_can( 'erp_ac_view_journal' );
                    },
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );
    }

    /**
     * Get a collection of journals
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_journals( $request ) {
        $args['number'] = ! empty( $request['per_page'] ) ? $request['per_page'] : 20;
        $args['offset'] = ( $request['per_page'] * ( $request['page'] - 1 ) );

        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $items       = erp_acct_get_all_journals( $args );
        $total_items = erp_acct_get_all_journals(
            [
                'count'  => true,
                'number' => -1,
            ]
        );

        $formatted_items = [];

        foreach ( $items as $item ) {
            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        $response->set_status( 200 );

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
        $id                = (int) $request['id'];
        $additional_fields = [];

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_journal_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 404 ] );
        }

        $item = erp_acct_get_journal( $id );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item = $this->prepare_item_for_response( $item, $request, $additional_fields );

        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Get a next journal id
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_next_journal_id( $request ) {
        global $wpdb;

        $count      = $wpdb->get_row( 'SELECT count(*) FROM ' . $wpdb->prefix . 'erp_acct_journals', ARRAY_N );
        $item['id'] = $count['0'] + 1;

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

        $items            = $trans_data['line_items'];
        $vocher_amount_dr = [];
        $vocher_amount_cr = [];

        foreach ( $items as $key => $item ) {
            $vocher_amount_dr[ $key ] = (float) $item['debit'];
            $vocher_amount_cr[ $key ] = (float) $item['credit'];
        }

        $total_dr = array_sum( $vocher_amount_dr );
        $total_cr = array_sum( $vocher_amount_cr );

        $trans_data['voucher_amount'] = $total_dr;

        $journal = erp_acct_insert_journal( $trans_data );

        $this->add_log( $journal, 'add' );

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $response = $this->prepare_item_for_response( $journal, $request, $additional_fields );
        $response = rest_ensure_response( $response );

        $response->set_status( 201 );

        return $response;
    }

    /**
     * Log when journal is created
     *
     * @param $data
     * @param $action
     */
    public function add_log( $data, $action ) {
        erp_log()->add(
            [
                'component'     => 'Accounting',
                'sub_component' => __( 'Journal', 'erp' ),
                'old_value'     => '',
                'new_value'     => '',
                // translators: %s: amount
                'message'       => sprintf( __( 'An journal of %s has been created', 'erp' ), $data['voucher_amount'] ),
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

        if ( isset( $request['type'] ) ) {
            $prepared_item['type'] = $request['type'];
        }

        if ( isset( $request['trn_date'] ) ) {
            $prepared_item['date'] = $request['trn_date'];
        }

        if ( isset( $request['attachments'] ) ) {
            $prepared_item['attachments'] = maybe_serialize( $request['attachments'] );
        }

        if ( isset( $request['particulars'] ) ) {
            $prepared_item['particulars'] = $request['particulars'];
        }

        if ( isset( $request['line_items'] ) ) {
            $prepared_item['line_items'] = $request['line_items'];
        }

        if ( isset( $request['ref'] ) ) {
            $prepared_item['ref'] = $request['ref'];
        }

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object          $item
     * @param WP_REST_Request $request           request object
     * @param array           $additional_fields (optional)
     *
     * @return WP_REST_Response $response response data
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $item = (object) $item;

        $data = [
            'id'          => $item->id,
            'voucher_no'  => $item->voucher_no,
            'particulars' => $item->particulars,
            'ref'         => $item->ref,
            'trn_date'    => $item->trn_date,
            'line_items'  => ! empty( $item->line_items ) ? $item->line_items : [],
            'attachments' => maybe_unserialize( $item->attachments ),
            'total'       => (float) $item->voucher_amount,
            'created_at'  => $item->created_at,
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'created_by', $include_params, true ) ) {
                $data['created_by'] = $this->get_user( intval( $item->created_by ) );
            }
        }

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
            'title'      => 'journal',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.', 'erp' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'trn_date' => [
                    'description' => __( 'Transaction date for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'ref' => [
                    'description' => __( 'Reference for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'type' => [
                    'description' => __( 'Type for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'line_items'      => [
                    'description' => __( 'List of line items data.', 'erp' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'ledger_id'   => [
                            'description' => __( 'Ledger id.', 'erp' ),
                            'type'        => 'integer',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'particulars' => [
                            'description' => __( 'Line particulars.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                            'arg_options' => [
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                        'debit' => [
                            'description' => __( 'Debit balance.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'credit'          => [
                            'description' => __( 'Credit balance.', 'erp' ),
                            'type'        => 'number',
                            'context'     => [ 'view', 'edit' ],
                        ],
                    ],
                ],
                'particulars' => [
                    'description' => __( 'Particulars for the resource.', 'erp' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'attachments' => [
                    'description' => __( 'Attachments for the resource.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'edit' ],
                ],
            ],
        ];

        return $schema;
    }
}
