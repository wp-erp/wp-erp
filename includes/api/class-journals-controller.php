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
    protected $rest_base = 'accounting/journals';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_journals' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_journal' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_journal' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
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
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
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
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'type'   => 'journal',
        ];

        $items       = erp_ac_get_all_transaction( ['type' => 'journal', 'join' => ['journals'], 'with_ledger' => true] );
        $total_items = erp_ac_get_transaction_count( ['type' => 'journal'] );

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
        $item = erp_ac_get_transaction( $id, ['type' => 'journal', 'join' => ['journals'], 'with_ledger' => true, 'output_by' => 'object'] );

        if ( empty( $id ) || empty( $item->id ) || $item->type != 'journal' ) {
            return new WP_Error( 'rest_journal_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $additional_fields = [];

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
        $items      = $request['items'];

        $id = erp_ac_new_journal( $trans_data, $items );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        $transaction = erp_ac_get_transaction( $id, ['type' => 'journal', 'join' => ['journals'], 'with_ledger' => true, 'output_by' => 'object'] );

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

        $prepared_item['type']       = 'journal';
        $prepared_item['ref']        = isset( $request['reference'] ) ? sanitize_text_field( $request['reference'] ) : '';
        $prepared_item['issue_date'] = isset( $request['issue_date'] ) ? sanitize_text_field( $request['issue_date'] ) : '';
        $prepared_item['summary']    = isset( $request['summary'] ) ? wp_kses_post( $request['summary'] ) : '';

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
            'id'          => (int) $item->id,
            'reference'   => $item->ref,
            'summary'     => $item->summary,
            'issue_date'  => $item->issue_date,
            'items'       => $this->format_transaction_items( $item->journals ),
            'total'       => (float) $item->total,
            'trans_total' => (float) $item->trans_total,
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
                'debit'       => (float) $item['debit'],
                'credit'      => (float) $item['credit'],
                'ledger_id'   => (int) $item['ledger']['id'],
                'ledger_name' => isset( $item['ledger']['name'] ) ? $item['ledger']['name'] : '',
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
            'title'      => 'journal',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'reference'  => [
                    'description' => __( 'Reference for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'summary'  => [
                    'description' => __( 'Summary for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'issue_date'  => [
                    'description' => __( 'Issue date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'items'  => [
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