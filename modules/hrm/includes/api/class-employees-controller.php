<?php

namespace WeDevs\ERP\HRM\API;

use WeDevs\ERP\API\REST_Controller;
use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Dependents;
use WeDevs\ERP\HRM\Models\Education;
use WeDevs\ERP\HRM\Models\Performance;
use WeDevs\ERP\HRM\Models\Work_Experience;
use WeDevs\ERP\HRM\Models\Employee_Note;
use WP_Error;
use WP_REST_Response;
use WP_REST_Server;


class Employees_Controller extends REST_Controller {
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
    protected $rest_base = 'hrm/employees';

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employees' ],
                'args'                => $this->get_collection_params(),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_employee' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/bulk', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_employees' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_employee' ],
                'args'                => [
                    'context' => $this->get_context_param( [ 'default' => 'view' ] ),
                ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_employee' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_employee' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_delete_employee' );
                },
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );


    }


    /**
     * Get a collection of employees
     *
     * @param \WP_REST_Request $request
     *
     * @return mixed|object|\WP_REST_Response
     */
    public function get_employees( \WP_REST_Request $request ) {
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

        $formatted_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];
            $data              = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formatted_items[] = $this->prepare_response_for_collection( $data );
        }
        $response = rest_ensure_response( $formatted_items );
        $response = $this->format_collection_response( $response, $request, (int) $total_items );

        return $response;
    }

    /**
     * Get a specific employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_employee( \WP_REST_Request $request ) {
        $id   = (int) $request['id'];
        $item = new Employee( $id );

        if ( ! $item->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create employees
     *
     * @param $request
     *
     * @return $this|int|\WP_Error|\WP_REST_Response
     */
    public function create_employees( \WP_REST_Request $request ) {
        $employees = json_decode( $request->get_body(), true );

        foreach ( $employees as $employee ) {
            $item_data = $this->prepare_item_for_database( $employee );
            $item      = new Employee( null );
            $created   = $item->create_employee( $item_data );
            if ( is_wp_error( $created ) ) {
                return $created;
            }
        }

        return new WP_REST_Response( true, 201 );
    }

    /**
     * Create an employee
     *
     * @param \WP_REST_Request $request
     *
     * @return WP_Error|\WP_REST_Request
     */
    public function create_employee( $request ) {
        $item_data = $this->prepare_item_for_database( $request );

        $employee = new Employee( null );
        $created  = $employee->create_employee( $item_data );
        if ( is_wp_error( $created ) ) {
            return $created;
        }
        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $employee, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $employee->get_user_id() ) ) );

        return $response;
    }


    /**
     * Prepare a single user output for response
     *
     * @param \WeDevs\ERP\HRM\Employee $item
     * @param \WP_REST_Request|null    $request
     * @param array                    $additional_fields
     *
     * @return mixed|object|\WP_REST_Response
     */
    public function prepare_item_for_response( Employee $item, \WP_REST_Request $request = null, $additional_fields = [] ) {
        $default = [
            'user_id'         => '',
            'employee_id'     => '',
            'first_name'      => '',
            'middle_name'     => '',
            'last_name'       => '',
            'full_name'       => '',
//            'email'           => '',
            'location'        => '',
            'date_of_birth'   => '',
            'pay_rate'        => '',
            'pay_type'        => '',
            'hiring_source'   => '',
            'hiring_date'     => '',
            'type'            => '',
            'status'          => '',
            'other_email'     => '',
            'phone'           => '',
            'work_phone'      => '',
            'mobile'          => '',
            'address'         => '',
            'gender'          => '',
            'marital_status'  => '',
            'nationality'     => '',
            'driving_license' => '',
            'hobbies'         => '',
            'user_url'        => '',
            'description'     => '',
            'street_1'        => '',
            'street_2'        => '',
            'city'            => '',
            'country'         => '',
            'state'           => '',
            'postal_code'     => '',
        ];

        $data = wp_parse_args( $item->get_data( array(), true ), $default );

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'department', $include_params ) && ! empty( $item->get_department() ) ) {
                $data['department'] = Department::find( $item->get_department() );
            }

            if ( in_array( 'designation', $include_params ) && ! empty( $item->get_designation() ) ) {
                $data['designation'] = Department::find( $item->get_designation() );
            }

            if ( in_array( 'reporting_to', $include_params ) && $item->get_reporting_to() ) {
                $reporting_to = new Employee( $item->get_reporting_to() );
                if ( $reporting_to->is_employee() ) {
                    $data['reporting_to'] = $this->prepare_item_for_response( $reporting_to );
                }
            }

            if ( in_array( 'avatar', $include_params ) ) {
                $data['avatar_url'] = $item->get_avatar_url( 32 );
            }

            if ( in_array( 'roles', $include_params ) ) {
                $data['roles'] = $item->get_roles();
            }
        }

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item );

        return $response;
    }

    /**
     * Prepare a single item for create or update
     *
     * @param \WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_item = [];

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
