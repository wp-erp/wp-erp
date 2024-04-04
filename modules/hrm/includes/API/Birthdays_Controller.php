<?php

namespace WeDevs\ERP\HRM\API;

use WeDevs\ERP\API\REST_Controller;
use WeDevs\ERP\HRM\Employee;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;

class Birthdays_Controller extends REST_Controller {

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
    protected $rest_base = 'hrm/birthdays';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_birthdays' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of employee birthdays
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_birthdays( $request ) {
        $args = [
            'upcoming' => empty( $request['upcoming'] ) ? false : true,
            'number'   => $request['per_page'] ? $request['per_page'] : 20,
            'offset'   => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $from_date = \Carbon\Carbon::today()->format( 'm d' );

        if ( empty( $args['upcoming'] ) ) {
            $from_date = '01 01';
        }

        $db        = new \WeDevs\ORM\Eloquent\Database();
        $employees =  erp_array_to_object( \WeDevs\ERP\HRM\Models\Employee::select( '*' )
            ->where( $db->raw( "DATE_FORMAT( `date_of_birth`, '%m %d' )" ), '>=', $from_date )
            ->orderByRaw( 'MONTH(date_of_birth)' )
            ->orderByRaw( 'MONTH(date_of_birth)' )
            ->orderByRaw( 'DAYOFMONTH(date_of_birth)' )
            ->where( 'termination_date', '0000-00-00' )
            ->where( 'status', 'active' )
            ->where( $db->raw( "DATE_FORMAT( `date_of_birth`, '%m %d' )" ), '<=', \Carbon\Carbon::now()->addWeek()->format( 'm d' ) )
            ->limit( $args['number'] )
            ->offset( $args['offset'] )
            ->get()
            ->toArray() );

        $total_items    = count( $employees );
        $formated_items = [];

        foreach ( $employees as $employee ) {
            $item                  = [];
            $item['id']            = $employee->id;
            $item['user_id']       = $employee->user_id;
            $item['employee_id']   = $employee->employee_id;
            $item['date_of_birth'] = $employee->date_of_birth;
            $item['birthday']      = gmdate( 'dS, M', strtotime( $employee->date_of_birth ) );
            $item['designation']   = $employee->designation;
            $item['department']    = $employee->department;

            $employee_user            = new \WeDevs\ERP\HRM\Employee( intval( $employee->user_id ) );
            $item['name']             = $employee_user->get_full_name();
            $item['avatar']           = $employee_user->get_avatar( 80 );
            $item['job_title']        = $employee_user->get_job_title();
            $item['department_title'] = $employee_user->get_department_title();
            $item['url']              = $employee_user->get_details_url();

            $formated_items[] = $item;
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }
}
