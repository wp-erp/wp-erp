<?php

namespace WeDevs\ERP\HRM\API\V2;

use Exception;
use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;
use WeDevs\ERP\Framework\Traits\Api;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Controller;

class Settings_Controller extends WP_REST_Controller {

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
    protected $rest_base = 'hrm/settings';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employees' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_employee' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of employees
     *
     * @return mixed|object|WP_REST_Response
     */
    public function get_employees( WP_REST_Request $request ) {
        $args = [
            'number'            => $request['per_page'],
            'offset'            => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'status'            => ( $request['status'] ) ? $request['status'] : 'active',
            'department'        => ( $request['department'] ) ? $request['department'] : '-1',
            'designation'       => ( $request['designation'] ) ? $request['designation'] : '-1',
            'location'          => ( $request['location'] ) ? $request['location'] : '-1',
            'gender'            => ( $request['gender'] ) ? $request['gender'] : '-1',
            'marital_status'    => ( $request['marital_status'] ) ? $request['marital_status'] : '-1',
            'orderby'           => ( $request['orderby'] ) ? $request['orderby'] : 'hiring_date',
            'order'             => ( $request['order'] ) ? $request['order'] : 'DESC',
            'type'              => ( $request['type'] ) ? $request['type'] : '-1',
            's'                 => ( $request['s'] ) ? $request['s'] : '',
        ];

        $items = erp_hr_get_employees( $args );

        $args['count'] = true;
        $total_items   = erp_hr_get_employees( $args );

        $formatted_items = [];

        foreach ( $items as $item ) {
            $additional_fields = [];
            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }
        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, (int) $total_items );

        $response->set_status( 200 );
        return $response;
    }
}
