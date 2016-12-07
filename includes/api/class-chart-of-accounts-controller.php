<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Chart_Of_Accounts_Controller extends REST_Controller {
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
    protected $rest_base = 'accounting/chart-of-accounts';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/types', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_account_types' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_account_lists' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_accounts' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_account_lists' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_account' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_account' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_account' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_single_account' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_account' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_edit_account' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_account' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_delete_account' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of account types
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_account_types( $request ) {
        $types = erp_ac_get_all_chart_types();

        $types = array_map( function( $type ) {
            $type->id       = (int) $type->id;
            $type->class_id = (int) $type->class_id;

            return $type;
        }, $types );

        return new WP_REST_Response( $types );
    }

    /**
     * Get a collection of accounts
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_accounts( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $count_args = ['count' => true];

        if ( isset( $request['group'] ) ) {
            $group = ucwords( strtolower( $request['group'] ) );

            $all_groups             = erp_ac_get_chart_classes();
            $all_groups             = array_flip( $all_groups );
            $args['class_id']       = isset( $all_groups[ $group ] ) ? $all_groups[ $group ] : 0;
            $count_args['class_id'] = $args['class_id'];
        }

        $items       = erp_ac_get_all_chart( $args );
        $total_items = erp_ac_get_all_chart( $count_args );

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
     * Get a specific account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_account( $request ) {
        $id   = (int) $request['id'];
        $item = erp_ac_get_chart( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_chart_of_account_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $group_id   = (int) $item->charts->class_id;
        $all_groups = erp_ac_get_chart_classes();
        $additional_fields['group'] = isset( $all_groups[ $group_id ] ) ? $all_groups[ $group_id ] : '';

        $item     = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create an account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_account( $request ) {
        $item = $this->prepare_item_for_database( $request );

        if ( $item['type_id'] == 6 ) {
            $item['cash_account'] = 1;
            $item['reconcile']    = 1;
        }

        $id = erp_ac_insert_chart( $item );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        $account = erp_ac_get_chart( $id );

        if ( $item['type_id'] == 6 && isset( $request['bank_account'] ) && isset( $request['bank_name'] ) ) {
            $ledger = \WeDevs\ERP\Accounting\Model\Ledger::find( $id );
            $ledger->bank_details()->create([
                'account_number' => sanitize_text_field( $request['bank_account'] ),
                'bank_name'      => sanitize_text_field( $request['bank_name'] )
            ]);
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $account, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Update an account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_account( $request ) {
        $id = (int) $request['id'];

        $item = erp_ac_get_chart( $id );
        if ( ! $id || ! $item->id ) {
            return new WP_Error( 'rest_chart_of_account_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        if ( intval( $item->system ) ) {
            return new WP_Error( 'rest_chart_of_account_system_account', __( 'System account could not update.' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        if ( $item['type_id'] == 6 ) {
            $item['cash_account'] = 1;
            $item['reconcile']    = 1;
        }

        $result = erp_ac_insert_chart( $item );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        if ( $item['type_id'] == 6 && isset( $request['bank_account'] ) && isset( $request['bank_name'] ) ) {
            $ledger = \WeDevs\ERP\Accounting\Model\Ledger::find( $id );
            $ledger->bank_details()->update([
                'account_number' => sanitize_text_field( $request['bank_account'] ),
                'bank_name'      => sanitize_text_field( $request['bank_name'] )
            ]);
        }

        $account = erp_ac_get_chart( $id );

        $response = $this->prepare_item_for_response( $account, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_account( $request ) {
        $id = (int) $request['id'];

        $result = erp_ac_delete_chart( $id );

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

        $prepared_item['id']          = isset( $request['id'] ) ? intval( $request['id'] ) : 0;
        $prepared_item['type_id']     = isset( $request['type'] ) ? intval( $request['type'] ) : 0;
        $prepared_item['code']        = isset( $request['code'] ) ? intval( $request['code'] ) : 0;
        $prepared_item['name']        = isset( $request['name'] ) ? $request['name'] : '';
        $prepared_item['description'] = isset( $request['description'] ) ? $request['description'] : '';
        $prepared_item['active']      = isset( $request['active'] ) ? intval( $request['active'] ) : 1;

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
        if ( ! isset( $item->type_name ) ) {
            $type_id = intval( $item->type_id );
            $types   = erp_ac_get_all_chart_types();
            $type    = null;

            foreach ( $types as $type_key => $type_value ) {
                if ( $type_value->id == $type_id  ) {
                    $type = $type_value->name;
                    break;
                }
            }
        } else {
            $type = $item->type_name;
        }

        $groups   = erp_ac_get_chart_classes();
        $class_id = (int) $item->class_id;

        $data = [
            'id'          => (int) $item->id,
            'group'       => isset( $groups[ $class_id ] ) ? $groups[ $class_id ] : null,
            'type'        => $type,
            'code'        => (int) $item->code,
            'name'        => $item->name,
            'description' => $item->description,
            'parent'      => (int) $item->parent,
            'system'      => (int) $item->system,
            'entries'     => (int) $item->entries,
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
            'title'      => 'chart of account',
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
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'code'  => [
                    'description' => __( 'Code for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'name'  => [
                    'description' => __( 'Name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'description'  => [
                    'description' => __( 'Description for the resource.' ),
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