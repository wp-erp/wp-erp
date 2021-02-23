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
    
}
