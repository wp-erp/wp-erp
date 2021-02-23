<?php

namespace WeDevs\ERP\HRM\API\V2;

use WeDevs\ERP\HRM\Models\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;
use DateTime;
use WP_REST_Controller;
use WP_REST_Request;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\ERP\Framework\Traits\Api;

class Overview_Controller extends WP_REST_Controller {

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
    protected $rest_base = 'hrm/overview';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_hrm_overview_all_counts' ],
                'args'                => $this->get_collection_params(),
                /*'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_announcement' );
                },*/
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/about-to-end', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_hrm_overview_about_to_end' ],
                'args'                => $this->get_collection_params(),
                /*'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_announcement' );
                },*/
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of overview page counts
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_hrm_overview_all_counts( WP_REST_Request $request ) {

        $employees    = Employee::where( 'status', 'active' )->count();
        $departments  = Department::count();
        $designations = Designation::count();

        $items = array(
            'employees'    => $employees,
            'departments'  => $departments,
            'designations' => $designations,
        );

        $response = rest_ensure_response( $items );

        return $response;

    }

    /**
     * Get a collection of overview page about to end
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_hrm_overview_about_to_end( WP_REST_Request $request ) {

        $c_t_employees  = erp_hr_get_contractual_employee();
        $current_date   =  current_time( 'Y-m-d' );
        $trainee        = [];
        $contract       = [];

        foreach ( $c_t_employees as $key => $user ) {
            $date1          = date_create( $current_date );
            $end_date       = get_user_meta( $user->user_id, 'end_date', true );
            $date2          = date_create( $end_date );
            $diff           = date_diff( $date1, $date2 );

            if ( $diff->days > 0 && $diff->days < 21 ) {
                $user->end_date = $end_date;

                if ( $user->type == 'contract' ) {
                    $contract[] = $user;
                }

                if ( $user->type == 'trainee' ) {
                    $trainee[] = $user;
                }
            }
        }
        usort( $contract, function ( $a, $b ) {
            return $a->end_date > $b->end_date;
        } );
        usort( $trainee, function ( $a, $b ) {
            return $a->end_date > $b->end_date;
        } );

        $items = array(
            'contract' => $contract,
            'trainee'  => $trainee
        );

        $response = rest_ensure_response( $items );

        return $response;
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
            $prepared_item['post_title'] = $request['title'];
        }

        if ( isset( $request['body'] ) ) {
            $prepared_item['post_content'] = $request['body'];
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['ID'] = absint( $request['id'] );
        }

        if ( isset( $request['status'] ) ) {
            $prepared_item['post_status'] = $request['status'];
        }

        $prepared_item['post_type'] = 'erp_hr_announcement';

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
        $post_date   = new DateTime( $item->post_date );
        $post_author = get_user_by( 'id', $item->post_author );

        $data = [
            'id'     => (int) $item->id,
            'title'  => $item->post_title,
            'body'   => $item->post_content,
            'status' => $item->post_status,
            'date'   => $post_date->format( 'Y-m-d' ),
            'author' => $post_author->user_login,
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
            'title'      => 'announcement',
            'type'       => 'object',
            'properties' => [
                'id'              => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'title'           => [
                    'description' => __( 'Title for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'body'            => [
                    'description' => __( 'Body for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'status'          => [
                    'description' => __( 'Status for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'recipient_type'  => [
                    'description' => __( 'Recipient type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
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
