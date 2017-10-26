<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Contacts_Controller extends REST_Controller {
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
    protected $rest_base = 'crm/contacts';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_contacts' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_list_contact' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_contact' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_add_contact' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/bulk', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_contacts' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_add_contact' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_contact' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_list_contact' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_contact' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_edit_contact', $request['id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_contact' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_delete_contact', $request['id'] );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of contacts
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_contacts( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $items       = erp_get_peoples( $args );
        $total_items = erp_get_peoples( [ 'count' => true ] );

        $formated_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            if ( isset( $request['include'] ) ) {
                $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

                if ( in_array( 'owner', $include_params ) ) {
                    $contact_owner_id = erp_crm_get_contact_owner( $item->id );

                    $item->owner = $this->get_user( $contact_owner_id );
                    $additional_fields = ['owner' => $item->owner];
                }
            }

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific contact
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_contact( $request ) {
        $id   = (int) $request['id'];
        $item = erp_get_people( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_contact_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $additional_fields = [];
        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'owner', $include_params ) ) {
                $contact_owner_id = erp_crm_get_contact_owner( $item->id );

                $item->owner = $this->get_user( $contact_owner_id );
                $additional_fields = ['owner' => $item->owner];
            }
        }

        $item  = $this->prepare_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create a contact
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_contact( $request ) {
        $item = $this->prepare_item_for_database( $request );
        $id   = erp_insert_people( $item );

        $contact = erp_get_people( $id );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $contact, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Create contacts
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_contacts( $request ) {
        $contacts = json_decode( $request->get_body(), true );

        foreach ( $contacts as $contact ) {
            $item = $this->prepare_item_for_database( $contact );
            $id   = erp_insert_people( $item );

            if ( is_wp_error( $id ) ) {
                return $id;
            }
        }

        return new WP_REST_Response( true, 201 );
    }

    /**
     * Update a contact
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_contact( $request ) {
        $id = (int) $request['id'];

        $item = erp_get_people( $id );
        if ( ! $item ) {
            return new WP_Error( 'rest_contact_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        if ( isset( $request['email'] ) ) {
            unset( $request['email'] );
        }

        if ( isset( $request['type'] ) ) {
            unset( $request['type'] );
        }

        $item = $this->prepare_item_for_database( $request );

        erp_insert_people( $item );

        $contact = erp_get_people( $id );

        $response = $this->prepare_item_for_response( $contact, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete a contact
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_contact( $request ) {
        $id = (int) $request['id'];

        $data = [
            'id'   => $id,
            'hard' => false,
            'type' => 'contact'
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
        if ( isset( $request['company'] ) ) {
            $prepared_item['company'] = $request['company'];
        }
        if ( isset( $request['phone'] ) ) {
            $prepared_item['phone'] = $request['phone'];
        }
        if ( isset( $request['mobile'] ) ) {
            $prepared_item['mobile'] = $request['mobile'];
        }
        if ( isset( $request['other'] ) ) {
            $prepared_item['other'] = $request['other'];
        }
        if ( isset( $request['website'] ) ) {
            $prepared_item['website'] = $request['website'];
        }
        if ( isset( $request['fax'] ) ) {
            $prepared_item['fax'] = $request['fax'];
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
        if ( isset( $request['currency'] ) ) {
            $prepared_item['currency'] = $request['currency'];
        }
        if ( isset( $request['type'] ) ) {
            $prepared_item['type'] = $request['type'];
        }
        if ( isset( $request['owner'] ) ) {
            $prepared_item['contact_owner'] = $request['owner'];
        }
        if ( isset( $request['life_stage'] ) ) {
            $prepared_item['life_stage'] = $request['life_stage'];
        }

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
        wp_send_json($item);
        $data = [
            'id'            => (int) $item->id,
            'first_name'    => $item->first_name,
            'last_name'     => $item->last_name,
            'email'         => $item->email,
            'company'       => $item->company,
            'phone'         => $item->phone,
            'mobile'        => $item->mobile,
            'other'         => $item->other,
            'website'       => $item->website,
            'fax'           => $item->fax,
            'notes'         => $item->notes,
            'street_1'      => $item->street_1,
            'street_2'      => $item->street_2,
            'city'          => $item->city,
            'state'         => $item->state,
            'postal_code'   => $item->postal_code,
            'country'       => $item->country,
            'currency'      => $item->currency,
            'types'         => $item->types,
            'user_id'       => (int) $item->user_id,
            'life_stage'    => $item->life_stage,
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
            'title'      => 'contact',
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
                'company'     => [
                    'description' => __( 'Company for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'phone'       => [
                    'description' => __( 'Phone for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'mobile'      => [
                    'description' => __( 'Mobile for the resource.' ),
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
                'fax'             => [
                    'description' => __( 'Fax of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'notes'           => [
                    'description' => __( 'Notes of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'street_1'        => [
                    'description' => __( 'Street 1 of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'street_2'        => [
                    'description' => __( 'Street 1 of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'city'            => [
                    'description' => __( 'City of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'state'           => [
                    'description' => __( 'State of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'postal_code'     => [
                    'description' => __( 'Postal Code of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'country'         => [
                    'description' => __( 'Country of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'currency'        => [
                    'description' => __( 'Currency of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'type'            => [
                    'description' => __( 'Type of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'owner'           => [
                    'description' => __( 'Owner of the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'required'    => true,
                ],
                'life_stage'      => [
                    'description' => __( 'Life stage of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
            ],
        ];

        return $schema;
    }
}
