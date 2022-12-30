<?php

namespace WeDevs\ERP\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class SchedulesController extends REST_Controller {

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
    protected $rest_base = 'crm/schedules';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_schedules' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_schedules' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/crm/todays-schedules', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_todays_schedules' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_schedules' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/crm/upcoming-schedules', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_upcoming_schedules' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_crm_manage_schedules' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of user's schedules
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_schedules( $request ) {
        $args = [
            'limit'  => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'type'   => 'log_activity',
        ];

        $items       = erp_crm_get_feed_activity( $args );
        $items       = erp_array_to_object( $items );
        $total_items = erp_crm_get_feed_activity( ['count' => true] );

        $formated_items = [];

        foreach ( $items as $item ) {
            $additional_fields = [];

            $data             = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a collection of today's schedules
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_todays_schedules( $request ) {
        $items       = erp_crm_get_todays_schedules_activity( 1 );
        $items       = erp_array_to_object( $items );
        $total_items = null;

        $formated_items = [];

        foreach ( $items as $item ) {
            $additional_fields = [];

            $data             = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a collection of upcoming schedules
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_upcoming_schedules( $request ) {
        $items = erp_crm_get_next_seven_day_schedules_activities( 1 );

        $items       = erp_array_to_object( $items );
        $total_items = null;

        $formated_items = [];

        foreach ( $items as $item ) {
            $additional_fields = [];

            $data             = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
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
        // Convert to a standard type
        $item->type = 'log';

        $common_fields = [
            'id'         => (int) $item->id,
            'type'       => $item->type,
            'contact_id' => (int) $item->user_id,
            'created_by' => $item->created_by,
        ];

        $fields = [
            'date_time' => $item->start_date,
            'log_type'  => $item->log_type,
            'content'   => $item->message,
        ];

        if ( $item->log_type == 'meeting' ) {
            $fields['employee_ids'] = wp_list_pluck( $item->extra['invited_user'], 'id' );
        }

        if ( $item->log_type == 'email' ) {
            $fields['subject'] = $item->email_subject;
        }

        $fields = array_merge( $common_fields, $fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $fields );

        $response = $this->add_links( $response, $item );

        return $response;
    }
}
