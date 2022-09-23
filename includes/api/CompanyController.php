<?php

namespace WeDevs\ERP\API;

use WP_REST_Response;
use WP_REST_Server;

class CompanyController extends REST_Controller {

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
    protected $rest_base = 'hrm/company';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/company-locations', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_company_locations' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/employee-statuses', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee_statuses' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/employee-types', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee_types' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/pay-types', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee_pay_types' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/hiring-sources', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_source_of_hire' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get company locations
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_REST_Response
     */
    public function get_company_locations( $request ) {
        $locations = erp_company_get_locations();
        $response  = rest_ensure_response( $locations );
        $response  = $this->format_collection_response( $response, $request, count( $locations ) );

        return $response;
    }

    /**
     * Get employee statuses
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_REST_Response
     */
    public function get_employee_statuses( $request ) {
        $statuses = erp_hr_get_employee_statuses();
        $response = rest_ensure_response( $statuses );
        $response = $this->format_collection_response( $response, $request, count( $statuses ) );

        return $response;
    }

    /**
     * Get employee pay types
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_REST_Response
     */
    public function get_employee_pay_types( $request ) {
        $pay_types = erp_hr_get_pay_type();
        $response  = rest_ensure_response( $pay_types );
        $response  = $this->format_collection_response( $response, $request, count( $pay_types ) );

        return $response;
    }

    /**
     * Get employee types
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_REST_Response
     */
    public function get_employee_types( $request ) {
        $employee_types = erp_hr_get_employee_types();
        $response       = rest_ensure_response( $employee_types );
        $response       = $this->format_collection_response( $response, $request, count( $employee_types ) );

        return $response;
    }

    /**
     * Get hiring source
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_REST_Response
     */
    public function get_source_of_hire( $request ) {
        $sources  = erp_hr_get_employee_sources();
        $response = rest_ensure_response( $sources );
        $response = $this->format_collection_response( $response, $request, count( $sources ) );

        return $response;
    }
}
