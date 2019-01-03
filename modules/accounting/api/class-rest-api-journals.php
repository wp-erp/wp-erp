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
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
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
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
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
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_journal' ],
                'args'                => [],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_journal' );
                },
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
        $args['number'] = !empty( $request['per_page'] ) ? $request['per_page'] : 20;
        $args['offset'] = 0;

        $formatted_items = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $items       = erp_acct_get_all_journals( $args );
        $total_items = erp_acct_get_all_journals( [ 'count' => true, 'number' => -1 ] );

        $formatted_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
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
        $id   = (int) $request['id']; $additional_fields = [];
        $item = erp_acct_get_journal( $id );

        if ( empty( $id ) ) {
            return new WP_Error( 'rest_journal_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

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

        $items = $request['line_items'];

        foreach ( $items as $key => $item ) {
            $vocher_amount_dr[$key] = $item['debit'];
            $vocher_amount_cr[$key] = $item['credit'];
        }

        $total_dr = array_sum( $vocher_amount_dr );
        $total_cr = array_sum( $vocher_amount_cr );

        if ( $total_dr != $total_cr ) {
            return new WP_Error( 'rest_journal_invalid_amount', __( 'Summation of debit and credit must be equal.' ), [ 'status' => 404 ] );
        }

        $trans_data['voucher_amount'] = $total_dr;

        $id = erp_acct_insert_journal( $trans_data );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;
        
        $response = $this->prepare_item_for_response( $trans_data, $request, $additional_fields );
        $response = rest_ensure_response( $response );
        
        $response->set_status( 201 );

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

        $prepared_item['type']       = 'journal';
        $prepared_item['trn_date'] = isset( $request['trn_date'] ) ? $request['trn_date'] : '';
        $prepared_item['particulars']        = isset( $request['particulars'] ) ? sanitize_text_field( $request['particulars'] ) : '';
        $prepared_item['line_items'] = isset( $request['line_items'] ) ? $request['line_items'] : [];

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
        $item = (object) $item;

        $data = [
            'particulars' => $item->particulars,
            'trn_date'    => $item->trn_date,
            'line_items'  => $item->line_items,
            'total'       => (float) $item->voucher_amount,
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
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'particulars'  => [
                    'description' => __( 'Particulars for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'trn_date'  => [
                    'description' => __( 'Issue date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'line_items'  => [
                    'description' => __( 'Items for the resource.' ),
                    'type'        => 'array',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
            ],
        ];

        return $schema;
    }
}
