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
        $controllers = [];

        if ( erp_is_module_active( 'crm' ) ) {
            $controllers = array_merge( $controllers, [
                '\WeDevs\ERP\API\Contacts_Controller',
                '\WeDevs\ERP\API\Contacts_Groups_Controller',
                '\WeDevs\ERP\API\Activities_Controller',
                '\WeDevs\ERP\API\Schedules_Controller',
            ] );
        }

        if ( erp_is_module_active( 'hrm' ) ) {
            $controllers = array_merge( $controllers, [
                '\WeDevs\ERP\API\Departments_Controller',
                '\WeDevs\ERP\API\Designations_Controller',
                '\WeDevs\ERP\API\Employees_Controller',
                '\WeDevs\ERP\API\Announcements_Controller',
                '\WeDevs\ERP\API\Leave_Policies_Controller',
                '\WeDevs\ERP\API\Leave_Entitlements_Controller',
                '\WeDevs\ERP\API\Leave_Holidays_Controller',
                '\WeDevs\ERP\API\Leave_Requests_Controller',
                '\WeDevs\ERP\API\HRM_Reports_Controller',
            ] );
        }

        if ( erp_is_module_active( 'accounting' ) ) {
            $controllers = array_merge( $controllers, [
                '\WeDevs\ERP\API\Customers_Controller',
                '\WeDevs\ERP\API\Vendors_Controller',
                '\WeDevs\ERP\API\Sales_Controller',
                '\WeDevs\ERP\API\Expenses_Controller',
                '\WeDevs\ERP\API\Chart_Of_Accounts_Controller',
                '\WeDevs\ERP\API\Bank_Accounts_Controller',
                '\WeDevs\ERP\API\Journals_Controller',
                '\WeDevs\ERP\API\Accounting_Reports_Controller',
            ] );
        }

        $controllers = apply_filters( 'erp_rest_api_controllers', $controllers );

        foreach ( $controllers as $controller ) {
            $controller = new $controller();
            $controller->register_routes();
        }
    }
}