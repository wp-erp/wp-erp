<?php
namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Customers_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/customers';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_customers' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_customer' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_customer' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_create_customer' );
                },
            ],
            'schema' => [ $this, 'get_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_customer' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_customer' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_customer' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_edit_customer' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_customer' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_delete_customer' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/transactions', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_transactions' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_ac_view_customer' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of customers
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_customers( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'type'   => 'customer'
        ];

        $items       = erp_get_peoples( $args );
        $total_items = erp_get_peoples( [ 'type' => 'customer', 'count' => true ] );

        $formatted_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'owner', $include_params ) ) {
                    $customer_owner_id = ( $item->user_id ) ? get_user_meta( $item->user_id, 'contact_owner', true ) : erp_people_get_meta( $item->id, 'contact_owner', true );

                    $item->owner = $this->get_user( $customer_owner_id );
                    $additional_fields = ['owner' => $item->owner];
                }
            }

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific customer
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_customer( $request ) {
        $id   = (int) $request['id'];
        $item = erp_get_people( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_customer_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $additional_fields = [];
        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'owner', $include_params ) ) {
                $customer_owner_id = ( $item->user_id ) ? get_user_meta( $item->user_id, 'contact_owner', true ) : erp_people_get_meta( $item->id, 'contact_owner', true );

                $item->owner = $this->get_user( $customer_owner_id );
                $additional_fields = ['owner' => $item->owner];
            }
        }

        $item  = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create a customer
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_customer( $request ) {

        if ( false != email_exists( $request['email'] ) ) {
            return new WP_Error( 'rest_customer_invalid_id', __( 'Email already exists!' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        $id   = erp_insert_people( $item );

        $customer = erp_get_people( $id );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $customer, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Update a customer
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_customer( $request ) {
        $id = (int) $request['id'];

        $item = erp_get_people( $id );
        if ( ! $item ) {
            return new WP_Error( 'rest_customer_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );

        erp_insert_people( $item );

        $customer = erp_get_people( $id );
        $response = $this->prepare_item_for_response( $customer, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete a customer
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_customer( $request ) {
        $id = (int) $request['id'];

        $data = [
            'id'   => $id,
            'hard' => false,
            'type' => 'customer'
        ];

        erp_delete_people( $data );

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

        // required arguments.
        if ( isset( $request['first_name'] ) ) {
            $prepared_item['first_name'] = $request['first_name'];
        }
        if ( isset( $request['last_name'] ) ) {
            $prepared_item['last_name'] = $request['last_name'];
        }
        if ( isset( $request['email'] ) ) {
            $prepared_item['email'] = $request['email'];
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }
        if ( isset( $request['phone'] ) ) {
            $prepared_item['phone'] = $request['phone'];
        }
        if ( isset( $request['website'] ) ) {
            $prepared_item['website'] = $request['website'];
        }
        if ( isset( $request['other'] ) ) {
            $prepared_item['other'] = $request['other'];
        }
        if ( isset( $request['notes'] ) ) {
            $prepared_item['notes'] = $request['notes'];
        }
        if ( isset( $request['street_1'] ) ) {
            $prepared_item['street_1'] = $request['street_1'];
        }
        if ( isset( $request['street_2'] ) ) {
            $prepared_item['street_2'] = $request['street_2'];
        }
        if ( isset( $request['city'] ) ) {
            $prepared_item['city'] = $request['city'];
        }
        if ( isset( $request['state'] ) ) {
            $prepared_item['state'] = $request['state'];
        }
        if ( isset( $request['postal_code'] ) ) {
            $prepared_item['postal_code'] = $request['postal_code'];
        }
        if ( isset( $request['country'] ) ) {
            $prepared_item['country'] = $request['country'];
        }

        $prepared_item['type'] = 'customer';

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
            'first_name'  => $item->first_name,
            'last_name'   => $item->last_name,
            'email'       => $item->email,
            'phone'       => $item->phone,
            'website'     => $item->website,
            'notes'       => $item->notes,
            'other'       => $item->other,
            'billing'     => [
                'first_name'    => $item->first_name,
                'last_name'     => $item->last_name,
                'street_1'      => $item->street_1,
                'street_2'      => $item->street_2,
                'city'          => $item->city,
                'state'         => $item->state,
                'postal_code'   => $item->postal_code,
                'country'       => $item->country,
                'email'         => $item->email,
                'phone'         => $item->phone
            ],
        ];

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
            'title'      => 'customer',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'first_name'  => [
                    'description' => __( 'First name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'last_name'   => [
                    'description' => __( 'Last name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'email'       => [
                    'description' => __( 'The email address for the resource.' ),
                    'type'        => 'string',
                    'format'      => 'email',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'phone'       => [
                    'description' => __( 'Phone for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'other'       => [
                    'description' => __( 'Other for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'website'         => [
                    'description' => __( 'Website of the resource.' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => [ 'embed', 'view', 'edit' ],
                ],
                'notes'           => [
                    'description' => __( 'Notes of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'billing'            => [
                    'description' => __( 'List of billing address data.', 'erp' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'first_name' => [
                            'description' => __( 'First name.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'last_name'  => [
                            'description' => __( 'Last name.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'street_1'  => [
                            'description' => __( 'Address line 1', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'street_2'  => [
                            'description' => __( 'Address line 2', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'city'       => [
                            'description' => __( 'City name.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'state'      => [
                            'description' => __( 'ISO code or name of the state, province or district.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'postal_code'   => [
                            'description' => __( 'Postal code.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'country'    => [
                            'description' => __( 'ISO code of the country.', 'erp' ),
                            'type'        => 'string',
                            'context'     => [ 'view', 'edit' ],
                        ],
                        'email'       => [
                            'description' => __( 'The email address for the resource.' ),
                            'type'        => 'string',
                            'format'      => 'email',
                            'context'     => [ 'edit' ],
                        ],
                        'phone'       => [
                            'description' => __( 'Phone for the resource.' ),
                            'type'        => 'string',
                            'context'     => [ 'edit' ],
                        ],
                    ],
                ],
            ],
        ];


        return $schema;
    }
}
