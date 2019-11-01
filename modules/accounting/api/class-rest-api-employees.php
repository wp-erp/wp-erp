<?php

namespace WeDevs\ERP\Accounting\API;

use WP_REST_Server;
use WP_REST_Response;
use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Employees_Controller extends \WeDevs\ERP\API\REST_Controller {
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
    protected $rest_base = 'accounting/v1/employees';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_employees' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_view_list' );
					},
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_employee' ],
					'args'                => [
						'context' => $this->get_context_param( [ 'default' => 'view' ] ),
					],
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_list_employee' );
					},
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/transactions',
            [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_transactions' ],
					'args'                => $this->get_collection_params(),
					'permission_callback' => function( $request ) {
						return current_user_can( 'erp_view_list' );
					},
				],
			]
        );

    }

    /**
     * Get a collection of employees
     *
     * @param $request
     *
     * @return mixed|object|\WP_REST_Response
     */
    public function get_employees( $request ) {
        $args = [
            'number'      => $request['per_page'],
            'offset'      => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'status'      => ( $request['status'] ) ? $request['status'] : 'active',
            'department'  => ( $request['department'] ) ? $request['department'] : '-1',
            'designation' => ( $request['designation'] ) ? $request['designation'] : '-1',
            'location'    => ( $request['location'] ) ? $request['location'] : '-1',
            's'           => ( $request['s'] ) ? $request['s'] : '',
        ];

        $items = erp_hr_get_employees( $args );

        $args['count'] = true;
        $total_items   = erp_hr_get_employees( $args );
        $total_items   = is_array( $total_items ) ? count( $total_items ) : $total_items;

        $formatted_items   = [];
        $additional_fields = [];

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        foreach ( $items as $item ) {
            $additional_fields['id'] = $item->user_id;

            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }
        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, $total_items );
        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a specific employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_employee( $request ) {
        $people_id = (int) $request['id'];
        $user_id   = erp_acct_get_user_id_by_people_id( $people_id );

        $employee = new \WeDevs\ERP\HRM\Employee( $user_id );
        $item     = (array) erp_get_people( $people_id );

        if ( empty( $item['id'] ) ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item['designation']  = $employee->get_designation( 'view' );
        $item['department']   = $employee->get_department( 'view' );
        $item['reporting_to'] = $employee->get_reporting_to( 'view' );
        $item['avatar']       = $employee->get_avatar();

        $additional_fields['namespace'] = $this->namespace;
        $additional_fields['rest_base'] = $this->rest_base;

        $item     = $this->prepare_employee_item_for_response( $item, $request, $additional_fields );
        $response = rest_ensure_response( $item );

        $response->set_status( 200 );

        return $response;
    }

    /**
     * Get a collection of transactions
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_transactions( $request ) {
        $args['people_id'] = (int) $request['id'];

        $transactions = erp_acct_get_people_transactions( $args );

        return new WP_REST_Response( $transactions, 200 );
    }

    /**
     * Prepare a single user output for response
     *
     * @param array|object $item
     * @param \WP_REST_Request|null $request
     * @param array $additional_fields
     *
     * @return mixed|object|\WP_REST_Response
     */
    public function prepare_item_for_response( $item, $request = null, $additional_fields = [] ) {
        $item     = $item->data;
        $employee = new \WeDevs\ERP\HRM\Employee( $item['user_id'] );

        $data                = array_merge( $item['work'], $item['personal'], $additional_fields );
        $data['user_id']     = $item['user_id'];
        $data['email']       = $item['user_email'];
        $data['people_id']   = erp_acct_get_people_id_by_user_id( $item['user_id'] );
        $data['department']  = $employee->get_department( 'view' );
        $data['designation'] = $employee->get_designation( 'view' );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

    /**
     * Prepare a single employee output for response
     *
     * @param array|object $item
     * @param \WP_REST_Request|null $request
     * @param array $additional_fields
     *
     * @return mixed|object|\WP_REST_Response
     */
    public function prepare_employee_item_for_response( $item, $request = null, $additional_fields = [] ) {
        // Wrap the data in a response object
        $response = rest_ensure_response( $item );

        $response = $this->add_links( $response, $item, $additional_fields );

        return $response;
    }

    /**
     * Prepare a single item for create or update
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    public function prepare_item_for_database( $request ) {
        $prepared_item = [];
        $company       = new \WeDevs\ERP\Company();

        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = $request['id'];
        }

        // required arguments.
        if ( isset( $request['first_name'] ) ) {
            $prepared_item['personal']['first_name'] = $request['first_name'];
        }

        if ( isset( $request['last_name'] ) ) {
            $prepared_item['personal']['last_name'] = $request['last_name'];
        }

        if ( isset( $request['employee_id'] ) ) {
            $prepared_item['work']['employee_id'] = $request['employee_id'];
        }

        if ( isset( $request['email'] ) ) {
            $prepared_item['user_email'] = $request['email'];
        }

        // optional arguments.
        if ( isset( $request['company'] ) ) {
            $prepared_item['company'] = isset( $request['company'] ) ? $request['company'] : $company->name;
        }

        if ( isset( $request['user_id'] ) ) {
            $prepared_item['user_id'] = absint( $request['user_id'] );
        }

        if ( isset( $request['middle_name'] ) ) {
            $prepared_item['personal']['middle_name'] = $request['middle_name'];
        }

        if ( isset( $request['designation'] ) ) {
            $prepared_item['work']['designation'] = $request['designation'];
        }

        if ( isset( $request['department'] ) ) {
            $prepared_item['work']['department'] = $request['department'];
        }

        if ( isset( $request['reporting_to'] ) ) {
            $prepared_item['work']['reporting_to'] = $request['reporting_to'];
        }

        if ( isset( $request['location'] ) ) {
            $prepared_item['work']['location'] = $request['location'];
        }

        if ( isset( $request['hiring_source'] ) ) {
            $prepared_item['work']['hiring_source'] = $request['hiring_source'];
        }

        if ( isset( $request['hiring_date'] ) ) {
            $prepared_item['work']['hiring_date'] = $request['hiring_date'];
        }

        if ( isset( $request['date_of_birth'] ) ) {
            $prepared_item['work']['date_of_birth'] = $request['date_of_birth'];
        }

        if ( isset( $request['pay_rate'] ) ) {
            $prepared_item['work']['pay_rate'] = $request['pay_rate'];
        }

        if ( isset( $request['pay_type'] ) ) {
            $prepared_item['work']['pay_type'] = $request['pay_type'];
        }

        if ( isset( $request['type'] ) ) {
            $prepared_item['work']['type'] = $request['type'];
        }

        if ( isset( $request['status'] ) ) {
            $prepared_item['work']['status'] = $request['status'];
        }

        if ( isset( $request['other_email'] ) ) {
            $prepared_item['personal']['other_email'] = $request['other_email'];
        }

        if ( isset( $request['phone'] ) ) {
            $prepared_item['personal']['phone'] = $request['phone'];
        }

        if ( isset( $request['work_phone'] ) ) {
            $prepared_item['personal']['work_phone'] = $request['work_phone'];
        }

        if ( isset( $request['mobile'] ) ) {
            $prepared_item['personal']['mobile'] = $request['mobile'];
        }

        if ( isset( $request['address'] ) ) {
            $prepared_item['personal']['address'] = $request['address'];
        }

        if ( isset( $request['gender'] ) ) {
            $prepared_item['personal']['gender'] = $request['gender'];
        }

        if ( isset( $request['marital_status'] ) ) {
            $prepared_item['personal']['marital_status'] = $request['marital_status'];
        }

        if ( isset( $request['nationality'] ) ) {
            $prepared_item['personal']['nationality'] = $request['nationality'];
        }

        if ( isset( $request['driving_license'] ) ) {
            $prepared_item['personal']['driving_license'] = $request['driving_license'];
        }

        if ( isset( $request['hobbies'] ) ) {
            $prepared_item['personal']['hobbies'] = $request['hobbies'];
        }

        if ( isset( $request['user_url'] ) ) {
            $prepared_item['personal']['user_url'] = $request['user_url'];
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['personal']['description'] = $request['description'];
        }

        if ( isset( $request['street_1'] ) ) {
            $prepared_item['personal']['street_1'] = $request['street_1'];
        }

        if ( isset( $request['street_2'] ) ) {
            $prepared_item['personal']['street_2'] = $request['street_2'];
        }

        if ( isset( $request['city'] ) ) {
            $prepared_item['personal']['city'] = $request['city'];
        }

        if ( isset( $request['country'] ) ) {
            $prepared_item['personal']['country'] = $request['country'];
        }

        if ( isset( $request['state'] ) ) {
            $prepared_item['personal']['state'] = $request['state'];
        }

        if ( isset( $request['postal_code'] ) ) {
            $prepared_item['personal']['postal_code'] = $request['postal_code'];
        }
        if ( isset( $request['photo_id'] ) ) {
            $prepared_item['personal']['photo_id'] = $request['photo_id'];
        }

        return $prepared_item;
    }


    /**
     * Get the query params for collections.
     *
     * @return array
     */
    public function get_collection_params() {
        return [
            'context'  => $this->get_context_param(),
            'page'     => [
                'description'       => __( 'Current page of the collection.' ),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ],
            'per_page' => [
                'description'       => __( 'Maximum number of items to be returned in result set.' ),
                'type'              => 'integer',
                'default'           => 20,
                'minimum'           => 1,
                'maximum'           => 100,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'search'   => [
                'description'       => __( 'Limit results to those matching a string.' ),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }

}
