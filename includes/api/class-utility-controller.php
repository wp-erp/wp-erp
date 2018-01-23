<?php
namespace WeDevs\ERP\API;

use WP_Error;
use WP_REST_Response;
use WP_REST_Server;
class Utility_Controller extends REST_Controller {
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
    protected $rest_base = 'utility';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base .'/get-active-plugins', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_active_plugins' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can('erp_view_list' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get the list of active plugins
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|WP_REST_Response
     */
    public function get_active_plugins( $request ){
        $active_plugins = get_option('active_plugins');
        $response = rest_ensure_response( $active_plugins );

        return $response;
    }
}
