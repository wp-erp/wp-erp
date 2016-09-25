<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Contacts {
    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp';

    /**
     * Route base.
     *
     * @var string
     */
    protected $base = 'crm/contacts';

    /**
     * Constructor funtion.
     */
    public function __construct() {
        $this->register_routes();
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_contacts' ],
                'args'     => [],
            ],
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'create_contact' ],
                'args'     => [],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_contact' ],
            ],
            [
                'methods'  => WP_REST_Server::EDITABLE,
                'callback' => [ $this, 'update_contact' ],
            ],
            [
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => [ $this, 'delete_contact' ],
            ],
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
        $contacts = erp_get_peoples();

        return new WP_REST_Response( $contacts, 200 );
    }

    /**
     * Get a specific contact
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_contact( $request ) {
        $contact_id = (int) $request['id'];
        $contact    = erp_get_people( $contact_id );

        return new WP_REST_Response( $contact, 200 );
    }

    /**
     * Create a contact
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_contact( $request ) {
        $schema = $this->get_item_schema();

        $item = [];
        foreach ( $schema as $key ) {
            $item[ $key ] = $request[ $key ];
        }

        $contact_id = erp_insert_people( $item );

        return new WP_REST_Response( $contact_id, 200 );
    }

    /**
     * Update a contact
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_contact( $request ) {
        $schema     = $this->get_item_schema();
        $contact_id = intval( $request['id'] );
        $item       = (array) erp_get_people( $contact_id );

        foreach ( $schema as $key ) {
            $item[ $key ] = $request[ $key ];
        }

        $item['id'] = $contact_id;

        $contact_id = erp_insert_people( $item );

        return new WP_REST_Response( $contact_id, 200 );
    }

    /**
     * Delete a contact
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_contact( $request ) {
        $contact_id = (int) $request['id'];

        $data = [
            'id'   => $contact_id,
            'hard' => false,
            'type' => 'contact'
        ];

        erp_delete_people( $data );

        return new WP_REST_Response( true, 200 );
    }

    /**
     * Get the Post's schema, conforming to JSON Schema.
     *
     * @return array
     */
    public function get_item_schema() {
        // $schema = [
        //     '$schema' => 'http://json-schema.org/draft-04/schema#',
        //     'title'   => 'contact',
        //     'type'    => 'object',
        //     /*
        //      * Base properties for every Post.
        //      */
        //     'properties' => [
        //         'first_name'      => [
        //             'description' => __( "The date the object was published, in the site's timezone." ),
        //             'type'        => 'string',
        //             'context'     => [ 'view', 'edit', 'embed' ],
        //         ],
        //     ]
        // ];


        $schema = [
            'id',
            'first_name',
            'last_name',
            'email',
            'company',
            'phone',
            'mobile',
            'other',
            'website',
            'fax',
            'notes',
            'street_1',
            'street_2',
            'city',
            'state',
            'postal_code',
            'country',
            'currency',
            'type',
            'user_id',
            'created_by',
        ];

        return $schema;
    }
}