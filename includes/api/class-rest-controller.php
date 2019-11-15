<?php

namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

abstract class REST_Controller {

    /**
     * The namespace of this controller's route.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The base of this controller's route.
     *
     * @var string
     */
    protected $rest_base;

    /**
     * Prepare a response for inserting into a collection.
     *
     * @param WP_REST_Response $response Response object.
     *
     * @return array Response data, ready for insertion into collection data.
     */
    public function prepare_response_for_collection( $response ) {
        if ( ! ( $response instanceof WP_REST_Response ) ) {
            return $response;
        }

        $data   = (array) $response->get_data();
        $server = rest_get_server();

        if ( method_exists( $server, 'get_compact_response_links' ) ) {
            $links = call_user_func( [ $server, 'get_compact_response_links' ], $response );
        } else {
            $links = call_user_func( [ $server, 'get_response_links' ], $response );
        }

        if ( ! empty( $links ) ) {
            $data['_links'] = $links;
        }

        return $data;
    }

    /**
     * Get the item's schema, conforming to JSON Schema.
     *
     * @return array
     */
    public function get_item_schema() {
        return [];
    }

    /**
     * Get the item's schema for display / public consumption purposes.
     *
     * @since 1.1.10
     * @since 1.2.1  Return empty array if no schema found
     *
     * @return array
     */
    public function get_public_item_schema() {
        $schema = $this->get_item_schema();

        if ( empty( $schema ) ) {
            return [];
        }

        foreach ( $schema['properties'] as &$property ) {
            if ( isset( $property['arg_options'] ) ) {
                unset( $property['arg_options'] );
            }
        }

        return $schema;
    }

    /**
     * Get the query params for collections.
     *
     * @return array
     */
    public function get_collection_params() {
        return [
            'context'  => $this->get_context_param(),
            'page'     => [
                'description'       => __( 'Current page of the collection.' ),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ],
            'per_page' => [
                'description'       => __( 'Maximum number of items to be returned in result set.' ),
                'type'              => 'integer',
                'default'           => 20,
                'minimum'           => 1,
                'maximum'           => 100,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'search'   => [
                'description'       => __( 'Limit results to those matching a string.' ),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }

    /**
     * Get the magical context param.
     *
     * Ensures consistent description between endpoints, and populates enum from schema.
     *
     * @param array $args
     *
     * @return array
     */
    public function get_context_param( $args = [] ) {
        $param_details = [
            'description'       => __( 'Scope under which the request is made; determines fields present in response.' ),
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_key',
            'validate_callback' => 'rest_validate_request_arg',
        ];
        $schema        = $this->get_item_schema();
        if ( empty( $schema['properties'] ) ) {
            return array_merge( $param_details, $args );
        }
        $contexts = [];
        foreach ( $schema['properties'] as $attributes ) {
            if ( ! empty( $attributes['context'] ) ) {
                $contexts = array_merge( $contexts, $attributes['context'] );
            }
        }
        if ( ! empty( $contexts ) ) {
            $param_details['enum'] = array_unique( $contexts );
            rsort( $param_details['enum'] );
        }

        return array_merge( $param_details, $args );
    }

    /**
     * Get the object type this controller is responsible for managing.
     *
     * @return string
     */
    protected function get_object_type() {
        $schema = $this->get_item_schema();

        if ( ! $schema || ! isset( $schema['title'] ) ) {
            return null;
        }

        return $schema['title'];
    }

    /**
     * Get an array of endpoint arguments from the item schema for the controller.
     *
     * @param string $method HTTP method of the request. The arguments
     *                       for `CREATABLE` requests are checked for required
     *                       values and may fall-back to a given default, this
     *                       is not done on `EDITABLE` requests. Default is
     *                       WP_REST_Server::CREATABLE.
     *
     * @return array $endpoint_args
     */
    public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {

        $schema            = $this->get_item_schema();
        $schema_properties = ! empty( $schema['properties'] ) ? $schema['properties'] : [];
        $endpoint_args     = [];

        foreach ( $schema_properties as $field_id => $params ) {

            // Arguments specified as `readonly` are not allowed to be set.
            if ( ! empty( $params['readonly'] ) ) {
                continue;
            }

            $endpoint_args[ $field_id ] = [
                'validate_callback' => 'rest_validate_request_arg',
                'sanitize_callback' => 'rest_sanitize_request_arg',
            ];

            if ( isset( $params['description'] ) ) {
                $endpoint_args[ $field_id ]['description'] = $params['description'];
            }

            if ( WP_REST_Server::CREATABLE === $method && isset( $params['default'] ) ) {
                $endpoint_args[ $field_id ]['default'] = $params['default'];
            }

            if ( WP_REST_Server::CREATABLE === $method && ! empty( $params['required'] ) ) {
                $endpoint_args[ $field_id ]['required'] = true;
            }

            foreach ( [ 'type', 'format', 'enum' ] as $schema_prop ) {
                if ( isset( $params[ $schema_prop ] ) ) {
                    $endpoint_args[ $field_id ][ $schema_prop ] = $params[ $schema_prop ];
                }
            }

            // Merge in any options provided by the schema property.
            if ( isset( $params['arg_options'] ) ) {

                // Only use required / default from arg_options on CREATABLE endpoints.
                if ( WP_REST_Server::CREATABLE !== $method ) {
                    $params['arg_options'] = array_diff_key( $params['arg_options'], [
                        'required' => '',
                        'default'  => ''
                    ] );
                }

                $endpoint_args[ $field_id ] = array_merge( $endpoint_args[ $field_id ], $params['arg_options'] );
            }
        }

        return $endpoint_args;
    }

    /**
     * Adds multiple links to the response.
     *
     * @param   object $response
     * @param   object $item
     * @param   array $additional_fields
     *
     * @return  object
     */
    protected function add_links( $response, $item, $additional_fields = array() ) {
        $response->data['_links'] = $this->prepare_links( $item, $additional_fields );

        return $response;
    }

    /**
     * Prepare links for the request.
     *
     * @param  object $item
     * @param  string $namespace
     * @param  string $rest_base
     *
     * @return array Links for the given user.
     */
    protected function prepare_links( $item, $additional_fields = array() ) {
        if ( empty( $additional_fields ) ) {
            $links = [
                'self' => [
                    'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $item->id ) ),
                ],
                'collection' => [
                    'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
                ]
            ];

            return $links;
        }

        $item = (array) $item;

        $namespace = $additional_fields['namespace'];
        $rest_base = $additional_fields['rest_base'];

        if ( empty( $item['id'] ) && isset( $additional_fields['id'] ) ) {
            $item['id'] = $additional_fields['id'];
        }

        if ( empty( $item['id'] ) && empty( $additional_fields['id'] ) ) {
            $item['id'] = '';
        }

        $links = [
            'self' => [
                'href' => rest_url( sprintf( '%s/%s/%d', $namespace, $rest_base, $item['id'] ) ),
            ],
            'collection' => [
                'href' => rest_url( sprintf( '%s/%s', $namespace, $rest_base ) ),
            ]
        ];

        return $links;
    }

    /**
     * Format item's collection for response
     *
     * @param  object $response
     * @param  object $request
     * @param  array $items
     * @param  int $total_items
     *
     * @return object
     */
    public function format_collection_response( $response, $request, $total_items ) {
        if ( $total_items === 0 ) {
            return $response;
        }

        // Store pagation values for headers then unset for count query.
        $per_page = (int) ( ! empty( $request['per_page'] ) ? $request['per_page'] : 20 );
        $page     = (int) ( ! empty( $request['page'] ) ? $request['page'] : 1 );

        $response->header( 'X-WP-Total', (int) $total_items );

        $max_pages = ceil( $total_items / $per_page );

        $response->header( 'X-WP-TotalPages', (int) $max_pages );
        $base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

        if ( $page > 1 ) {
            $prev_page = $page - 1;
            if ( $prev_page > $max_pages ) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg( 'page', $prev_page, $base );
            $response->link_header( 'prev', $prev_link );
        }
        if ( $max_pages > $page ) {

            $next_page = $page + 1;
            $next_link = add_query_arg( 'page', $next_page, $base );
            $response->link_header( 'next', $next_link );
        }

        return $response;
    }

    /**
     * Retrieve a wp user
     *
     * @param  integer $user_id
     *
     * @return array
     */
    public function get_user( $user_id ) {
        $user = get_user_by( 'ID', $user_id );

        if ( ! $user ) {
            return null;
        }

        $data = [
            'ID'            => $user->ID,
            'user_nicename' => $user->user_nicename,
            'user_email'    => $user->user_email,
            'user_url'      => $user->user_url,
            'display_name'  => $user->display_name,
            'avatar'        => get_avatar_url( $user->ID ),
            '_links'        => [
                'self' => [
                    'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $user->ID ) ),
                ],
                'collection' => [
                    'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
                ]
            ],
        ];

        return $data;
    }
}
