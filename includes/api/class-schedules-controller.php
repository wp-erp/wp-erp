<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class Schedules_Controller extends REST_Controller {
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
    protected $base = 'crm/schedules';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_schedules' ],
                'args'     => $this->get_collection_params(),
            ],
        ] );

        register_rest_route( $this->namespace, '/crm/todays-schedules', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_todays_schedules' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/crm/upcoming-schedules', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_upcoming_schedules' ],
            ],
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
            'number'     => -1,
            'type'       => 'log_activity',
            'created_by' => 1, // user_id
        ];

        $schedules = erp_crm_get_feed_activity( $args );

        return new WP_REST_Response( $schedules, 200 );
    }

    /**
     * Get a collection of today's schedules
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_todays_schedules( $request ) {
        $schedules = erp_crm_get_todays_schedules_activity( 1 );

        return new WP_REST_Response( $schedules, 200 );
    }

    /**
     * Get a collection of upcoming schedules
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_upcoming_schedules( $request ) {
        $schedules = erp_crm_get_next_seven_day_schedules_activities( 1 );

        return new WP_REST_Response( $schedules, 200 );
    }
}