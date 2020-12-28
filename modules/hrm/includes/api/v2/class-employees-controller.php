<?php

namespace WeDevs\ERP\HRM\API\V2;

use Exception;
use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;
use WeDevs\ERP\Framework\Traits\Api;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_REST_Controller;

class Employees_Controller extends WP_REST_Controller {

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
                /*'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_list' );
                },*/
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_employee' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                /*'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_employee' );
                },*/
            ],
            'schema' => [ $this, 'get_public_item_schema' ],
        ] );
    }

    /**
     * Get a collection of employees
     *
     * @return mixed|object|WP_REST_Response
     */
    public function get_employees( WP_REST_Request $request ) {
        $args = [
            'number'            => $request['per_page'],
            'offset'            => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'status'            => ( $request['status'] ) ? $request['status'] : 'active',
            'department'        => ( $request['department'] ) ? $request['department'] : '-1',
            'designation'       => ( $request['designation'] ) ? $request['designation'] : '-1',
            'location'          => ( $request['location'] ) ? $request['location'] : '-1',
            'gender'            => ( $request['gender'] ) ? $request['gender'] : '-1',
            'marital_status'    => ( $request['marital_status'] ) ? $request['marital_status'] : '-1',
            'orderby'           => ( $request['orderby'] ) ? $request['orderby'] : 'hiring_date',
            'order'             => ( $request['order'] ) ? $request['order'] : 'DESC',
            'type'              => ( $request['type'] ) ? $request['type'] : '-1',
            's'                 => ( $request['s'] ) ? $request['s'] : '',
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

        $response->set_status( 200 );
        return $response;
    }

    /**
     * Create employees
     *
     * @param $request
     *
     * @return $this|int|WP_Error|WP_REST_Response
     */
    public function create_employees( WP_REST_Request $request ) {
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
     * Prepare a single user output for response
     *
     * @param array $additional_fields
     *
     * @return mixed|object|WP_REST_Response
     */
    public function prepare_item_for_response( $item, $request = null, $additional_fields = [] ) {
        $default = [
            'user_id'         => '',
            'employee_id'     => '',
            'first_name'      => '',
            'middle_name'     => '',
            'last_name'       => '',
            'full_name'       => '',
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
            'blood_group'     => '',
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

        $data = wp_parse_args( $item->get_data( [], true ), $default );

        if ( isset( $request['include'] ) ) {
            $include_params = explode( ',', str_replace( ' ', '', $request['include'] ) );

            if ( in_array( 'department', $include_params ) && ! empty( $item->get_department() ) ) {
                $data['department'] = Department::find( $item->get_department() );
            }

            if ( in_array( 'designation', $include_params ) && ! empty( $item->get_designation() ) ) {
                $data['designation'] = Designation::find( $item->get_designation() );
            }

            if ( in_array( 'reporting_to', $include_params ) && $item->get_reporting_to() ) {
                $reporting_to = new Employee( $item->get_reporting_to() );

                if ( $reporting_to->is_employee() ) {
                    $data['reporting_to'] = $this->prepare_item_for_response( $reporting_to );
                }
            }

            if ( in_array( 'avatar', $include_params ) ) {
                $data['avatar_url'] = $item->get_avatar_url( 80 );
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
     * @param WP_REST_Request $request request object
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

        if ( isset( $request['blood_group'] ) ) {
            $prepared_item['personal']['blood_group'] = $request['blood_group'];
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
     * @return array Query parameters for the collection.
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
            's'   => [
                'description'       => __( 'Limit results to those matching a string.' ),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'status'   => [
                'description'       => __( 'Items to be returned with specific status' ),
                'type'              => 'string',
                'default'           => 'active',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'department'   => [
                'description'       => __( 'Items to be returned with specific department' ),
                'type'              => 'integer',
                'default'           => -1,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'designation'   => [
                'description'       => __( 'Items to be returned with specific designation' ),
                'type'              => 'integer',
                'default'           => -1,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'location'   => [
                'description'       => __( 'Items to be returned with specific location' ),
                'type'              => 'integer',
                'default'           => -1,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'gender'   => [
                'description'       => __( 'Items to be returned with specific gender' ),
                'type'              => 'string',
                'default'           => '-1',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'marital_status'   => [
                'description'       => __( 'Items to be returned with specific marital status' ),
                'type'              => 'string',
                'default'           => '-1',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'orderby'   => [
                'description'       => __( 'Items to be returned with specific field name' ),
                'type'              => 'string',
                'default'           => 'hiring_date',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'order'   => [
                'description'       => __( 'Items to be returned either asc OR desc' ),
                'type'              => 'string',
                'default'           => 'DESC',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'type'   => [
                'description'       => __( 'Items to be returned with specific type' ),
                'type'              => 'string',
                'default'           => '-1',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }

    /**
     * Get the Employee schema, conforming to JSON Schema
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'contact',
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
                'middle_name'     => [
                    'description' => __( 'Company for the resource.' ),
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
                'phone'       => [
                    'description' => __( 'Phone for the resource.' ),
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
                'website'         => [
                    'description' => __( 'Website of the resource.' ),
                    'type'        => 'string',
                    'format'      => 'uri',
                    'context'     => [ 'embed', 'view', 'edit' ],
                ],
                'fax'             => [
                    'description' => __( 'Fax of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'notes'           => [
                    'description' => __( 'Notes of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
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
                'currency'        => [
                    'description' => __( 'Currency of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'type'            => [
                    'description' => __( 'Type of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'owner'           => [
                    'description' => __( 'Owner of the resource.' ),
                    'type'        => 'integer',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'required'    => true,
                ],
                'life_stage'      => [
                    'description' => __( 'Life stage of the resource.' ),
                    'type'        => 'string',
                    'context'     => [ 'embed', 'view', 'edit' ],
                    'arg_options' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'required'    => true,
                ],
            ],
        ];

        return $schema;
    }
}
