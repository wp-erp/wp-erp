<?php
namespace WeDevs\ERP\HRM\API\V2;

use Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Controller;
use WeDevs\ERP\Framework\Traits\Api;
use WeDevs\ERP\HRM\Models\Designation as Designation_Model;
use WeDevs\ERP\HRM\Designation;

class Designations_Controller extends WP_REST_Controller {

    use Api;

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'erp/v2';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'hrm/designations';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_designations' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_designation' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_designation' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_designation' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_designation' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_designation' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_designation' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_designation' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of designations
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_designations( WP_REST_Request $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            's'      => $request['s'] ? $request['s'] : '',
        ];

        $items       = erp_hr_get_designations( $args );
        $total_items = erp_hr_count_designation();

        $formated_items = [];

        foreach ( $items as $item ) {
            $data             = $this->prepare_item_for_response( $item, $request );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific designation
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_designation( WP_REST_Request $request ) {
        $id   = (int) $request['id'];
        $item = Designation_Model::find( $id );

        if ( empty( $id ) || empty( $item ) ) {
            return new WP_Error( 'rest_designation_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = new Designation( $id );
        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create a designation
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_designation( WP_REST_Request $request ) {
        $item = $this->prepare_item_for_database( $request );
        $id   = erp_hr_create_designation( $item );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        $designation = new Designation( $id );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $designation, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Update a designation
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_designation( WP_REST_Request $request ) {
        $id   = (int) $request['id'];
        $item = Designation_Model::find( $id );

        if ( empty( $id ) || empty( $item ) ) {
            return new WP_Error( 'rest_designation_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        $designation = new Designation( $id );
        $item        = $this->prepare_item_for_database( $request );
        $id          = erp_hr_update_designation( $item );

        if ( is_wp_error( $id ) ) {
            return $id;
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $designation, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete a designation
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_designation( WP_REST_Request $request ) {
        $id = (int) $request['id'];

        erp_hr_delete_designation( $id );

        return new WP_REST_Response( true, 204 );
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

        // required arguments.
        if ( isset( $request['title'] ) ) {
            $prepared_item['title'] = $request['title'];
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['description'] = $request['description'];
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
        $total_employees = \WeDevs\ERP\HRM\Models\Employee::where( [ 'status' => 'active', 'designation' => $item->id ] )->count();

        $data = [
            'id'              => (int) $item->id,
            'title'           => $item->title,
            'description'     => $item->description,
            'total_employees' => $total_employees,
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
            'title'      => 'designation',
            'type'       => 'object',
            'properties' => [
                'id'            => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'title'         => [
                    'description' => __( 'Title for the resource.' ),
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

    /**
	 * Retrieves the query params for the collections.
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_collection_params() {
		return [
			'context'    => $this->get_context_param(),
			'page'       => [
				'description'       => __( 'Current page of the collection.' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
            ],
			'per_page'    => [
				'description'       => __( 'Maximum number of items to be returned in result set.' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
            ],
			's'           => [
				'description'       => __( 'Limit results to those matching a string.' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
	}
}
