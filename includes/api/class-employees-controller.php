<?php
namespace WeDevs\ERP\API;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Work_Experience;
use WeDevs\ERP\HRM\Models\Education;
use WeDevs\ERP\HRM\Models\Dependents;

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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/experiences', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_experiences' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/experiences' . '/(?P<exp_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/educations', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_educations' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/educations' . '/(?P<edu_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/dependents', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_dependents' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/dependents' . '/(?P<dep_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_list_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );
    }

    /**
     * Get a collection of employees
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_employees( $request ) {
        $args = [
            'number' => $request['per_page'],
            'offset' => ( $request['per_page'] * ( $request['page'] - 1 ) ),
        ];

        $items         = erp_hr_get_employees( $args );

        $args['count'] = true;
        $total_items   = erp_hr_get_employees( $args );

        $formated_items = [];
        foreach ( $items as $item ) {
            $additional_fields = [];

            $data = $this->prepare_item_for_response( $item, $request, $additional_fields );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific employee
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_employee( $request ) {
        $id   = (int) $request['id'];
        $item = new Employee( $id );

        if ( empty( $id ) || empty( $item->id ) ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 404 ] );
        }

        $item     = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create an employee
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_employee( $request ) {
        $item = $this->prepare_item_for_database( $request );

        $id       = erp_hr_employee_create( $item );
        $employee = new Employee( $id );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $employee, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Create employees
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_employees( $request ) {
        $employees = json_decode( $request->get_body(), true );

        foreach ( $employees as $employee ) {
            $item = $this->prepare_item_for_database( $employee );
            $id   = erp_hr_employee_create( $item );

            if ( is_wp_error( $id ) ) {
                return $id;
            }
        }

        return new WP_REST_Response( true, 201 );
    }

    /**
     * Update an employee
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_employee( $request ) {
        $id = (int) $request['id'];

        $employee = new Employee( $id );
        if ( ! $employee ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.' ), [ 'status' => 400 ] );
        }

        $item = $this->prepare_item_for_database( $request );
        $id   = erp_hr_employee_create( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_item_for_response( $employee, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an employee
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_employee( $request ) {
        $id = (int) $request['id'];

        erp_employee_delete( $id );

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Prepare a single item for create or update
     *
     * @param WP_REST_Request $request Request object.
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

        if ( isset( $request['email'] ) ) {
            $prepared_item['user_email'] = $request['email'];
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
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

        return $prepared_item;
    }

    /**
     * Prepare a single user output for response
     *
     * @param object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'              => (int) $item->id,
            'first_name'      => $item->first_name,
            'middle_name'     => $item->middle_name,
            'last_name'       => $item->last_name,
            'email'           => $item->user_email,
            'location'        => $item->location,
            'hiring_source'   => $item->hiring_source,
            'hiring_date'     => $item->hiring_date,
            'date_of_birth'   => $item->date_of_birth,
            'pay_rate'        => (int) $item->pay_rate,
            'pay_type'        => $item->pay_type,
            'type'            => $item->type,
            'status'          => $item->status,
            'other_email'     => $item->other_email,
            'phone'           => $item->phone,
            'work_phone'      => $item->work_phone,
            'mobile'          => $item->mobile,
            'address'         => $item->address,
            'gender'          => $item->gender,
            'marital_status'  => $item->marital_status,
            'nationality'     => $item->nationality,
            'driving_license' => $item->driving_license,
            'hobbies'         => $item->hobbies,
            'user_url'        => $item->user_url,
            'description'     => $item->description,
            'street_1'        => $item->street_1,
            'street_2'        => $item->street_2,
            'city'            => $item->city,
            'country'         => $item->country,
            'state'           => $item->state,
            'postal_code'     => $item->postal_code,
        ];

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'department', $include_params ) ) {
                $departments_controller = new Departments_Controller();

                $department_id      = (int) $item->department;
                $data['department'] = null;

                if ( $department_id ) {
                    $department = $departments_controller->get_department( ['id' => $department_id ] );
                    $data['department'] = ! is_wp_error( $department ) ? $department->get_data() : null;
                }
            }

            if ( in_array( 'designation', $include_params ) ) {
                $designations_controller = new Designations_Controller();

                $designation_id      = (int) $item->designation;
                $data['designation'] = null;

                if ( $designation_id ) {
                    $designation = $designations_controller->get_designation( ['id' => $designation_id ] );
                    $data['designation'] = ! is_wp_error( $designation ) ? $designation->get_data() : null;
                }
            }

            if ( in_array( 'reporting_to', $include_params ) ) {
                $reporting_to_id      = (int) $item->reporting_to;
                $data['reporting_to'] = null;

                if ( $reporting_to_id ) {
                    $reporting_to = $this->get_employee( ['id' => $reporting_to_id ] );
                    $data['reporting_to'] = ! is_wp_error( $reporting_to ) ? $reporting_to->get_data() : null;
                }
            }
        }

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        $response = $this->add_links( $response, $item );

        return $response;
    }

    /**
     * Get a collection of employee's experiences
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_experiences( $request ) {
        $employee_id = (int) $request['id'];
        $employee    = new Employee( $employee_id );
        $items       = $employee->get_experiences();

        $formated_items = [];
        foreach ( $items as $item ) {
            $data = $this->prepare_experience_for_response( $item, $request );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific experience of an employee
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_experience( $request ) {
        $exp_id      = (int) $request['exp_id'];
        $employee_id = (int) $request['id'];

        $experience = Work_Experience::where( ['id' => $exp_id, 'employee_id' => $employee_id] )->first();

        if ( ! $experience ) {
            return new WP_Error( 'rest_invalid_experience', __( 'Invalid experience id.' ), array( 'status' => 404 ) );
        }

        $response   = $this->prepare_experience_for_response( $experience, $request );

        return $response;
    }

    /**
     * Create an experience
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_experience( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );

        if ( ! isset( $request['company_name'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required company_name.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['job_title'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required job_title.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['from'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required from.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['to'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required to.' ), array( 'status' => 400 ) );
        }

        $item       = $this->prepare_experience_for_database( $request );
        $experience = Work_Experience::create( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_experience_for_response( $experience, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Update an experience
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_experience( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );
        $request['id'] = (int) $request['exp_id'];

        $item       = $this->prepare_experience_for_database( $request );
        $experience = Work_Experience::find( $request['id'] );
        if ( $experience ) {
            $is_updated = $experience->update( $item );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_experience_for_response( $experience, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an experience
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_experience( $request ) {
        $id = (int) $request['exp_id'];

        Work_Experience::find( $id )->delete();

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Prepare a single experience for create or update
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_experience_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['company_name'] ) ) {
            $prepared_item['company_name'] = $request['company_name'];
        }

        if ( isset( $request['job_title'] ) ) {
            $prepared_item['job_title'] = $request['job_title'];
        }

        if ( isset( $request['description'] ) ) {
            $prepared_item['description'] = $request['description'];
        }

        if ( isset( $request['from'] ) ) {
            $prepared_item['from'] = $request['from'];
        }

        if ( isset( $request['to'] ) ) {
            $prepared_item['to'] = $request['to'];
        }

        if ( isset( $request['employee_id'] ) ) {
            $prepared_item['employee_id'] = absint( $request['employee_id'] );
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single experience output for response
     *
     * @param object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_experience_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'           => (int) $item->id,
            'company_name' => $item->company_name,
            'job_title'    => $item->job_title,
            'description'  => $item->description,
            'from'         => $item->from,
            'to'           => $item->to,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Get a collection of employee's educations
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_educations( $request ) {
        $employee_id = (int) $request['id'];
        $employee    = new Employee( $employee_id );
        $items       = $employee->get_educations();

        $formated_items = [];
        foreach ( $items as $item ) {
            $data = $this->prepare_education_for_response( $item, $request );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific education of an employee
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_education( $request ) {
        $edu_id      = (int) $request['edu_id'];
        $employee_id = (int) $request['id'];

        $education = Education::where( ['id' => $edu_id, 'employee_id' => $employee_id] )->first();

        if ( ! $education ) {
            return new WP_Error( 'rest_invalid_education', __( 'Invalid education id.' ), array( 'status' => 404 ) );
        }

        $response  = $this->prepare_education_for_response( $education, $request );

        return $response;
    }

    /**
     * Create an education
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_education( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );

        if ( ! isset( $request['school'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required school.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['degree'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required degree.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['field'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required field.' ), array( 'status' => 400 ) );
        }

        $item      = $this->prepare_education_for_database( $request );
        $education = Education::create( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_education_for_response( $education, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Update an education
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_education( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );
        $request['id'] = (int) $request['edu_id'];

        $item      = $this->prepare_education_for_database( $request );
        $education = Education::find( $request['id'] );
        if ( $education ) {
            $is_updated = $education->update( $item );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_education_for_response( $education, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an education
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_education( $request ) {
        $id = (int) $request['edu_id'];

        Education::find( $id )->delete();

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Prepare a single education for create or update
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_education_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['school'] ) ) {
            $prepared_item['school'] = $request['school'];
        }

        if ( isset( $request['degree'] ) ) {
            $prepared_item['degree'] = $request['degree'];
        }

        if ( isset( $request['field'] ) ) {
            $prepared_item['field'] = $request['field'];
        }

        if ( isset( $request['finished'] ) ) {
            $prepared_item['finished'] = $request['finished'];
        }

        if ( isset( $request['notes'] ) ) {
            $prepared_item['notes'] = $request['notes'];
        }

        if ( isset( $request['interest'] ) ) {
            $prepared_item['interest'] = $request['interest'];
        }

        if ( isset( $request['employee_id'] ) ) {
            $prepared_item['employee_id'] = absint( $request['employee_id'] );
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single education output for response
     *
     * @param object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_education_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'       => (int) $item->id,
            'school'   => $item->school,
            'degree'   => $item->degree,
            'field'    => $item->field,
            'finished' => $item->finished,
            'notes'    => $item->notes,
            'interest' => $item->interest,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Get a collection of employee's dependents
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_dependents( $request ) {
        $employee_id = (int) $request['id'];
        $employee    = new Employee( $employee_id );
        $items       = $employee->get_dependents();

        $formated_items = [];
        foreach ( $items as $item ) {
            $data = $this->prepare_dependent_for_response( $item, $request );
            $formated_items[] = $this->prepare_response_for_collection( $data );
        }

        $response = rest_ensure_response( $formated_items );
        $response = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Get a specific dependent of an employee
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_dependent( $request ) {
        $dep_id      = (int) $request['dep_id'];
        $employee_id = (int) $request['id'];

        $dependent = Dependents::where( ['id' => $dep_id, 'employee_id' => $employee_id] )->first();

        if ( ! $dependent ) {
            return new WP_Error( 'rest_invalid_dependent', __( 'Invalid dependent id.' ), array( 'status' => 404 ) );
        }

        $response  = $this->prepare_dependent_for_response( $dependent, $request );

        return $response;
    }

    /**
     * Create a dependent
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_dependent( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );

        if ( ! isset( $request['name'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required name.' ), array( 'status' => 400 ) );
        }

        if ( ! isset( $request['relation'] ) ) {
            return new WP_Error( 'rest_experience_required_fields', __( 'Required relation.' ), array( 'status' => 400 ) );
        }

        $item      = $this->prepare_dependent_for_database( $request );
        $dependent = Dependents::create( $item );

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_dependent_for_response( $dependent, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Update a dependent
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_dependent( $request ) {
        $request['employee_id'] = $request['id'];
        unset( $request['id'] );
        $request['id'] = (int) $request['dep_id'];

        $item      = $this->prepare_dependent_for_database( $request );
        $dependent = Dependents::find( $request['id'] );
        if ( $dependent ) {
            $is_updated = $dependent->update( $item );
        }

        $request->set_param( 'context', 'edit' );
        $response = $this->prepare_dependent_for_response( $dependent, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete a dependent
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function delete_dependent( $request ) {
        $id = (int) $request['dep_id'];

        Dependents::find( $id )->delete();

        return new WP_REST_Response( true, 204 );
    }

    /**
     * Prepare a single dependent for create or update
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return array $prepared_item
     */
    protected function prepare_dependent_for_database( $request ) {
        $prepared_item = [];

        // required arguments.
        if ( isset( $request['name'] ) ) {
            $prepared_item['name'] = $request['name'];
        }

        if ( isset( $request['relation'] ) ) {
            $prepared_item['relation'] = $request['relation'];
        }

        if ( isset( $request['date_of_birth'] ) ) {
            $prepared_item['dob'] = $request['date_of_birth'];
        }

        if ( isset( $request['employee_id'] ) ) {
            $prepared_item['employee_id'] = absint( $request['employee_id'] );
        }

        // optional arguments.
        if ( isset( $request['id'] ) ) {
            $prepared_item['id'] = absint( $request['id'] );
        }

        return $prepared_item;
    }

    /**
     * Prepare a single dependent output for response
     *
     * @param object $item
     * @param WP_REST_Request $request Request object.
     * @param array $additional_fields (optional)
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_dependent_for_response( $item, $request, $additional_fields = [] ) {
        $data = [
            'id'            => (int) $item->id,
            'name'          => $item->name,
            'relation'      => $item->relation,
            'date_of_birth' => $item->dob,
        ];

        $data = array_merge( $data, $additional_fields );

        // Wrap the data in a response object
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Get the User's schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'employee',
            'type'       => 'object',
            'properties' => [
                'id'          => [
                    'description' => __( 'Unique identifier for the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'first_name'  => [
                    'description' => __( 'First name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'middle_name'  => [
                    'description' => __( 'Middle name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'last_name'   => [
                    'description' => __( 'Last name for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'email'       => [
                    'description' => __( 'The email address for the resource.' ),
                    'type'        => 'string',
                    'format'      => 'email',
                    'context'     => [ 'edit' ],
                    'required'    => true,
                ],
                'location'    => [
                    'description' => __( 'Location for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'hiring_source' => [
                    'description' => __( 'Hiring source for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'hiring_date'   => [
                    'description' => __( 'Hiring date for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'date_of_birth' => [
                    'description' => __( 'Date of birth for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'pay_rate' => [
                    'description' => __( 'Pay rate for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'pay_type' => [
                    'description' => __( 'Pay type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'type'        => [
                    'description' => __( 'Type for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'status'      => [
                    'description' => __( 'Status for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
                'phone'       => [
                    'description' => __( 'Phone for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'work_phone'   => [
                    'description' => __( 'Work phone for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'mobile'      => [
                    'description' => __( 'Mobile for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'other'       => [
                    'description' => __( 'Other for the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'user_url'    => [
                    'description' => __( 'Website of the resource.' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => [ 'embed', 'view', 'edit' ],
                ],
                'street_1'        => [
                    'description' => __( 'Street 1 of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'street_2'        => [
                    'description' => __( 'Street 1 of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'city'            => [
                    'description' => __( 'City of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'state'           => [
                    'description' => __( 'State of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'postal_code'     => [
                    'description' => __( 'Postal Code of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'country'         => [
                    'description' => __( 'Country of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ];

        return $schema;
    }
}