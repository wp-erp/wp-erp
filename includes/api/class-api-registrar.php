<?php
namespace WeDevs\ERP\API;

/**
 * API_Registrar class
 */
class API_Registrar {
    /**
     * Constructor
     */
    public function __construct() {
        if ( ! class_exists( 'WP_REST_Server' ) ) {
            return;
        }

        // Init REST API routes.
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ], 10 );
    }

    /**
     * Register REST API routes.
     *
     * @since 1.2.0
     */
    public function register_rest_routes() {
        $controllers = [
            '\WeDevs\ERP\API\Utility_Controller'
        ];

        if ( erp_is_module_active( 'crm' ) ) {
            $controllers = array_merge( $controllers, [
                '\WeDevs\ERP\API\Contacts_Controller',
                '\WeDevs\ERP\API\Contacts_Groups_Controller',
                '\WeDevs\ERP\API\Activities_Controller',
                '\WeDevs\ERP\API\Schedules_Controller',
            ] );
        }

        $controllers = apply_filters( 'erp_rest_api_controllers', $controllers );

        foreach ( $controllers as $controller ) {
            $controller = new $controller();
            $controller->register_routes();
        }
    }
}
