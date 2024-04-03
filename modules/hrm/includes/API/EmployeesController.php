<?php

namespace WeDevs\ERP\HRM\API;

use Exception;
use WeDevs\ERP\API\REST_Controller;
use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class EmployeesController extends REST_Controller {

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
                    return current_user_can( 'erp_view_list' );
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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)', [
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
                    return current_user_can( 'erp_edit_employee', $request['user_id'] );
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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/experiences', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_experiences' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_experience', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_experience', $request['user_id'] );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/experiences' . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_experience', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_experience', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_experience' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_delete_experience', $request['user_id'] );
                },
            ],
        ] );

        //education
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/educations', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_educations' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_education', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_education', $request['user_id'] );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/educations' . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_education', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_education' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_delete_education', $request['user_id'] );
                },
            ],
        ] );

        //dependents
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/dependents', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_dependents' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_dependent', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_create_dependent', $request['user_id'] );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/dependents' . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_dependent', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_dependent' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_delete_dependent', $request['user_id'] );
                },
            ],
        ] );

        //job histories
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/job_histories', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_histories' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_jobinfo', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_history' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_jobinfo' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/job_histories' . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_history' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_jobinfo' );
                },
            ],
        ] );

        //performances

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/performances', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_performances' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_jobinfo', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_performance' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_jobinfo' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/performances' . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_performance' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_jobinfo' );
                },
            ],
        ] );

        //events
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/events', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_events' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee', $request['user_id'] );
                },
            ],
        ] );

        //terminate
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/terminate', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'create_terminate' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_can_terminate' );
                },
            ],
        ] );

        //announcements
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/announcements', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_announcements' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_announcement', $request['user_id'] );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/announcements', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_announcements' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_announcement', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_status' ],
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_view_announcement', $request['user_id'] );
                },
            ],
        ] );

        //policies
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/policies', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_policies' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee', $request['user_id'] );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/leaves', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_leaves' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee', $request['user_id'] );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_leave' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee', $request['user_id'] );
                },
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/notes', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_notes' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_review' );
                },
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_note' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_manage_review' );
                },
            ],
        ] );
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/notes' . '/(?P<note_id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_note' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( 'erp_edit_employee' );
                },
            ],
        ] );

        //roles
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<user_id>[\d]+)' . '/roles', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_roles' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( erp_hr_get_manager_role() );
                },
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_role' ],
                'permission_callback' => function ( $request ) {
                    return current_user_can( erp_hr_get_manager_role() );
                },
            ],
        ] );

        // Upload Photo
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/upload', [
            [
                'methods'               => WP_REST_Server::CREATABLE,
                'callback'              => [ $this, 'upload_photo' ],
                'permission_callback'   => function ( $request ) {
                    return current_user_can( 'erp_create_employee' );
                },
            ],
            [
                'methods'               => WP_REST_Server::EDITABLE,
                'callback'              => [ $this, 'update_photo' ],
                'permission_callback'   => function () {
                    return current_user_can( 'erp_create_employee' );
                },
            ],
        ] );
    }

    /**
     * Upload employee photo
     *
     * @return array
     */
    public function upload_photo( WP_REST_Request $request ) {
        $file = isset( $_FILES['image'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_FILES['image'] ) ) : [];

        if ( ! $file ) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id =  media_handle_upload( 'image', 0 );

        $response = [
            'photo_id'  => $attachment_id,
        ];

        return $response;
    }

    /**
     * Update Photo
     *
     * @return bool
     */
    public function update_photo( WP_REST_Request $request ) {
        $photo_id   = isset( $request['photo_id'] ) ? $request['photo_id'] : 0;
        $user_id    = isset( $request['user_id'] ) ? $request['user_id'] : 0;

        update_user_meta( $user_id, 'photo_id', $photo_id );
    }

    /**
     * Get a collection of employees
     *
     * @return mixed|object|WP_REST_Response
     */
    public function get_employees( WP_REST_Request $request ) {
        $args = [
            'number'      => $request['per_page'],
            'offset'      => ( $request['per_page'] * ( $request['page'] - 1 ) ),
            'status'      => ( $request['status'] ) ? $request['status'] : 'active',
            'department'  => ( $request['department'] ) ? $request['department'] : '-1',
            'designation' => ( $request['designation'] ) ? $request['designation'] : '-1',
            'location'    => ( $request['location'] ) ? $request['location'] : '-1',
            'type'        => ( $request['type'] ) ? $request['type'] : '-1',
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
     * @return WP_Error|WP_REST_Response
     */
    public function get_employee( WP_REST_Request $request ) {
        $user_id = (int) $request['user_id'];
        $item    = new Employee( $user_id );

        if ( ! $item->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
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
     * Create an employee
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_employee( $request ) {
        $item_data = $this->prepare_item_for_database( $request );

        $employee = new Employee( null );
        $created  = $employee->create_employee( $item_data );

        if ( is_wp_error( $created ) ) {
            return $created;
        }
        $request->set_param( 'context', 'edit' );
        $item     = new Employee( $created->user_id );

        // User Notification
        if ( isset( $request['user_notification'] ) && $request['user_notification'] == true ) {
            $emailer    = wperp()->emailer->get_email( 'NewEmployeeWelcome' );
            $send_login = isset( $request['login_info'] ) ? true : false;

            if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
                $emailer->trigger( $employee->get_user_id(), $send_login );
            }
        }

        $response = $this->prepare_item_for_response( $item, $request );
        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $employee->get_user_id() ) ) );

        return $response;
    }

    /**
     * Update an employee
     *
     * @return $this|mixed|object|WP_Error|WP_REST_Response
     */
    public function update_employee( WP_REST_Request $request ) {
        $id = (int) $request['user_id'];

        $employee = new Employee( $id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }

        $data    = $this->prepare_item_for_database( $request );
        $updated = $employee->update_employee( $data );

        if ( is_wp_error( $updated ) ) {
            return $updated;
        }

        $request->set_param( 'context', 'edit' );
        $updated_user = new Employee( $updated->user_id );
        $response     = $this->prepare_item_for_response( $updated_user, $request );

        $response = rest_ensure_response( $response );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $id ) ) );

        return $response;
    }

    /**
     * Delete an employee
     *
     * @param $request
     *
     * @return WP_REST_Response
     */
    public function delete_employee( WP_REST_Request $request ) {
        $id = (int) $request['user_id'];

        erp_employee_delete( $id );
        $response = rest_ensure_response( true );

        return new WP_REST_Response( $response, 204 );
    }

    /**
     * Get a collection of employee's experiences
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_experiences( WP_REST_Request $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }

        $items = $employee->get_experiences();

        $total_items = $employee->get_erp_user()->experiences()->count();
        $response    = rest_ensure_response( $items );
        $response    = $this->format_collection_response( $response, $request, $total_items );

        return $response;
    }

    /**
     * Create an experience
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_experience( WP_REST_Request $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }
        $experience = $employee->add_experience( $request->get_params() );

        if ( is_wp_error( $experience ) ) {
            return $experience;
        }

        $request->set_param( 'context', 'edit' );
        $response = rest_ensure_response( $experience );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $experience['id'] ) ) );

        return $response;
    }

    /**
     * Update an experience
     *
     * @return WP_Error|WP_REST_Request
     */
    public function update_experience( WP_REST_Request $request ) {
        $employee_id = (int) $request['user_id'];
        $exp_id      = (int) $request['id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }
        $args       = $request->get_params();
        $args['id'] = $exp_id;

        $experience = $employee->add_experience( $args );

        if ( is_wp_error( $experience ) ) {
            return $experience;
        }

        $request->set_param( 'context', 'edit' );
        $response = rest_ensure_response( $experience );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $experience['id'] ) ) );

        return $response;
    }

    /**
     * Delete an experience
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function delete_experience( $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }
        $result   = $employee->delete_experience( $request['id'] );
        $response = rest_ensure_response( $result );

        return $response;
    }

    /**
     * Get a collection of employee's educations
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_educations( $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 400 ] );
        }
        $items    = $employee->get_educations();
        $total    = $employee->get_erp_user()->educations()->count();
        $response = rest_ensure_response( $items );
        $response = $this->format_collection_response( $response, $request, $total );

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
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 400 ] );
        }
        $education = $employee->add_education( $request->get_params() );

        if ( is_wp_error( $education ) ) {
            return $education;
        }

        $request->set_param( 'context', 'edit' );

        $response = rest_ensure_response( $education );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $education['id'] ) ) );

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
        $employee_id = (int) $request['user_id'];
        $edu_id      = (int) $request['id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 400 ] );
        }

        $args['id'] = $edu_id;
        $education  = $employee->add_education( $request->get_params() );

        if ( is_wp_error( $education ) ) {
            return $education;
        }

        $request->set_param( 'context', 'edit' );

        $response = rest_ensure_response( $education );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $education['id'] ) ) );

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
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }
        $result   = $employee->delete_education( $request['id'] );
        $response = rest_ensure_response( $result );

        return $response;
    }

    /**
     * Get a collection of employee's dependents
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response|mixed
     */
    public function get_dependents( $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 400 ] );
        }
        $items    = $employee->get_dependents();
        $total    = $employee->get_erp_user()->dependents()->count();
        $response = rest_ensure_response( $items );
        $response = $this->format_collection_response( $response, $request, $total );

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
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 400 ] );
        }
        $dependent = $employee->add_dependent( $request->get_params() );

        if ( is_wp_error( $dependent ) ) {
            return $dependent;
        }

        $request->set_param( 'context', 'edit' );

        $response = rest_ensure_response( $dependent );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $dependent['id'] ) ) );

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
        $employee_id = (int) $request['user_id'];
        $depen_id    = (int) $request['id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 400 ] );
        }

        $args['id'] = $depen_id;
        $dependent  = $employee->add_dependent( $request->get_params() );

        if ( is_wp_error( $dependent ) ) {
            return $dependent;
        }

        $request->set_param( 'context', 'edit' );

        $response = rest_ensure_response( $dependent );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $dependent['id'] ) ) );

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
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }
        $result   = $employee->delete_dependent( $request['id'] );
        $response = rest_ensure_response( $result );

        return $response;
    }

    /**
     * Get employee histories
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_histories( $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 400 ] );
        }
        $module = ! empty( $request['module'] ) ? sanitize_key( $request['module'] ) : 'all';

        $histories = $employee->get_job_histories( $module );

        for ( $i = 0; $i < count( $histories['job'] ); $i ++ ) {
            $reports_to = new Employee( $histories['job'][ $i ]['reporting_to'] );

            if ( $employee->is_employee() ) {
                $histories['job'][ $i ]['reporting_to_full_name'] = $reports_to->display_name;
            }
        }

        $total    = $employee->get_erp_user()->histories()->count();
        $response = rest_ensure_response( $histories );
        $response = $this->format_collection_response( $response, $request, $total );

        return $response;
    }

    /**
     * Create employee history
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|WP_Error|WP_REST_Response
     */
    public function create_history( WP_REST_Request $request ) {
        $employee_id = (int) $request['user_id'];
        $module      = ! empty( $request['module'] ) ? sanitize_key( $request['module'] ) : 0;
        $employee    = new Employee( $employee_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }

        if ( empty( $module ) || ( ! in_array( $module, [ 'employment', 'compensation', 'job' ] ) ) ) {
            return new WP_Error( 'rest_no_module_type', __( 'Invalid/No module type', 'erp' ), [ 'status' => 404 ] );
        }

        $history = new WP_Error();

        if ( $request['module'] == 'employment' ) {
            $history = $employee->update_employment_status( $request->get_params() );
        }

        if ( $request['module'] == 'compensation' ) {
            $history = $employee->update_compensation( $request->get_params() );
        }

        if ( $request['module'] == 'job' ) {
            $history = $employee->update_job_info( $request->get_params() );
        }

        if ( is_wp_error( $history ) ) {
            return $history;
        }
        $response = rest_ensure_response( $history );

        return $response;
    }

    /**
     * Delete a history
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     *
     * @throws Exception
     */
    public function delete_history( $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }
        $result   = $employee->delete_job_history( $request['id'] );
        $response = rest_ensure_response( $result );

        return $response;
    }

    /**
     * Get all performance of a single employee
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_performances( WP_REST_Request $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 400 ] );
        }
        $items = $employee->get_performances( $request->get_params() );

        foreach ( $items as $item ) {
            foreach ( $item as $performance ) {
                $user_id = 0;

                if ( $performance['reporting_to'] ) {
                    $user_id = $performance['reporting_to'];
                } elseif ( $performance['reviewer'] ) {
                    $user_id = $performance['reviewer'];
                } elseif ( $performance['supervisor'] ) {
                    $user_id = $performance['supervisor'];
                }

                $associate_employee = new Employee( $user_id );

                if ( $associate_employee->is_employee() ) {
                    $performance->reporting_to_full_name = $associate_employee->display_name;
                    $performance->supervisor_full_name   = $associate_employee->display_name;
                    $performance->reviewer_full_name     = $associate_employee->display_name;
                }
            }
        }

        $total    = $employee->get_erp_user()->performances()->count();
        $response = rest_ensure_response( $items );
        $response = $this->format_collection_response( $response, $request, $total );

        return $response;
    }

    /**
     * Create a performance
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Request
     */
    public function create_performance( $request ) {
        $employee_id = (int) trim( $request['user_id'] );
        $employee    = new Employee( $employee_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }

        $performance = $employee->add_performance( $request->get_params() );

        if ( is_wp_error( $performance ) ) {
            return $performance;
        }
        $request->set_param( 'context', 'edit' );
        $response = rest_ensure_response( $performance );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $performance['id'] ) ) );

        return $response;
    }

    /**
     * Delete performance
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|WP_Error|WP_REST_Response
     */
    public function delete_performance( $request ) {
        $employee_id = (int) $request['user_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }
        $result   = $employee->delete_performance( $request['id'] );
        $response = rest_ensure_response( $result );

        return $response;
    }

    /**
     * Get all the events of a single user
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_events( $request ) {
        $user_id  = (int) $request['user_id'];
        $start    = ! empty( $request['start'] ) ? $request['start'] : gmdate( 'Y-01-01' );
        $end      = ! empty( $request['end'] ) ? $request['end'] : gmdate( 'Y-12-31' );

        $employee = new Employee( $user_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }
        $event_data = $employee->get_calender_events( ['start' => $start, 'end' => $end] );

        $response   = rest_ensure_response( $event_data );
        $response   = $this->format_collection_response( $response, $request, count( $event_data ) );

        return $response;
    }

    /**
     * Get Available leaves policies of a single employee
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_policies( $request ) {
        $user_id  = (int) $request['user_id'];
        $employee = new Employee( $user_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }
        $policies = $employee->get_leave_summary();

        foreach ( $policies as &$policy ) {
            if ( $policy->available == 0 && $policy->extra_leave > 0 ) {
                $policy->available = - $policy->extra_leave;
            }
        }
        $response = rest_ensure_response( $policies );
        $response = $this->format_collection_response( $response, $request, count( $policies ) );

        return $response;
    }

    /**
     * Get all leaves of a single employee
     *
     * @since 1.3.0
     *
     * @return array|WP_Error|object
     */
    public function get_leaves( WP_REST_Request $request ) {
        $user_id  = (int) $request['user_id'];
        $employee = new Employee( $user_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }

        $f_year = erp_hr_get_financial_year_from_date();

        if ( empty( $f_year ) ) {
            return new WP_Error( 'rest_invalid_financial_year', __( 'No financial year defined for current year.', 'erp' ), [ 'status' => 404 ] );
        }

        $args = [
            'user_id'   => $user_id,
            'f_year'    => $f_year->id,
            'status'    => 1,
            'orderby'   => 'created_at',
            'policy_id' => 0,
            'number'    => -1,
            'offset'    => 0,
        ];
        $leaves = erp_hr_get_leave_requests( $args );

        $response = rest_ensure_response( $leaves['data'] );
        $response = $this->format_collection_response( $response, $request, $leaves['total'] );

        return $response;
    }

    /**
     * Create leave request
     *
     * @since 1.3.0
     *
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error|object
     */
    public function create_leave( $request ) {
        $id       = (int) $request['user_id'];
        $employee = new Employee( $id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }

        $request_id = erp_hr_leave_insert_request(
            [
                'user_id'      => $request['user_id'],
                'leave_policy' => $request['policy_id'],
                'start_date'   => $request['start_date'],
                'end_date'     => $request['end_date'],
                'reason'       => $request['reason'],
                'status'       => 0,
            ]
        );

        if ( ! is_wp_error( $request_id ) ) {
            // notification email
            $emailer = wperp()->emailer->get_email( 'NewLeaveRequest' );

            if ( is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
                $emailer->trigger( $request_id );
            }
        }

        $response = rest_ensure_response( $request_id );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $request_id ) ) );

        return $response;
    }

    /**
     * Get all notes of a single employee
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_notes( $request ) {
        $args = [
            'total'  => isset( $request['perpage'] ) ? $request['perpage'] : 20,
            'offset' => isset( $request['offset'] ) ? $request['offset'] : 0,
        ];

        $id       = (int) $request['user_id'];
        $employee = new Employee( $id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }

        $notes = $employee->get_notes( $args['total'], $args['offset'] );

        foreach ( $notes as $note ) {
            $user                          = get_user_by( 'id', $note->comment_by );
            $note->comment_by_display_name = $user->display_name;
            $note->comment_by_avatar_url   = get_avatar_url( $note->comment_by );
        }

        $total    = $employee->get_erp_user()->notes()->count();
        $response = rest_ensure_response( $notes );
        $response = $this->format_collection_response( $response, $request, $total );

        return $response;
    }

    /**
     * Create a note for employee
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return array|mixed|WP_Error|WP_REST_Response
     */
    public function create_note( $request ) {
        $id       = (int) $request['user_id'];
        $employee = new Employee( $id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }
        $note                          = $employee->add_note( $request['note'], null, true );
        $note->comment_by_avatar_url   = get_avatar_url( $note->comment_by );
        $request->set_param( 'context', 'edit' );
        $response = rest_ensure_response( $note );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $note['id'] ) ) );

        return $response;
    }

    /**
     * Delete a note
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function delete_note( $request ) {
        $employee_id = (int) $request['user_id'];
        $note_id     = (int) $request['note_id'];
        $employee    = new Employee( $employee_id );

        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'rest_employee_invalid_id', __( 'Invalid resource id.', 'erp' ), [ 'status' => 400 ] );
        }
        $result   = $employee->delete_note( $note_id );
        $response = rest_ensure_response( $result );

        return $response;
    }

    /**
     * Get roles of an employee
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|WP_Error|WP_REST_Response
     */
    public function get_roles( $request ) {
        $employee_id = (int) trim( $request['user_id'] );
        $employee    = new Employee( $employee_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }
        $response = rest_ensure_response( $employee->get_roles() );

        return $response;
    }

    /**
     * Update employee roles
     * accepts associative array eg. ['erp_hr_manager' => true, 'erp_crm_manager' => false ]
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return array|mixed|WP_Error|WP_REST_Response
     */
    public function update_role( $request ) {
        $hr_manager_role = erp_hr_get_manager_role();

        if ( ! current_user_can( $hr_manager_role ) ) {
            return new WP_Error( 'rest_invalid_user_permission', __( 'User do not have permission for the action.', 'erp' ), [ 'status' => 404 ] );
        }
        $employee_id = (int) trim( $request['user_id'] );
        $employee    = new Employee( $employee_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }

        if ( ! is_array( $request['roles'] ) || empty( $request['roles'] ) ) {
            return new WP_Error( 'rest_performance_invalid_permission_type', __( 'Invalid role format', 'erp' ), [ 'status' => 400 ] );
        }

        $roles = $employee->update_role( $request['roles'] )->get_roles();
        $request->set_param( 'context', 'edit' );
        $response = rest_ensure_response( $roles );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $employee_id ) ) );

        return $response;
    }

    /**
     * Get announcement of an employee
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|object|WP_Error|WP_REST_Response
     */
    public function get_announcements( $request ) {
        $user_id  = (int) $request['user_id'];
        $employee = new Employee( $user_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }
        $announcements = $employee->get_announcements();

        foreach ( $announcements as $announcement ) {
            $user                        = get_user_by( 'id', $announcement['post_author'] );
            $announcement['post_author'] = $user->data->display_name;
        }

        $response      = rest_ensure_response( $announcements );
        $response      = $this->format_collection_response( $response, $request, count( $announcements ) );

        return $response;
    }

    /**
     * Terminate the employee
     *
     * @since 1.3.0
     *
     * @param $request
     *
     * @return mixed|WP_Error|WP_REST_Response
     */
    public function create_terminate( $request ) {
        $user_id  = (int) $request['user_id'];
        $employee = new Employee( $user_id );

        if ( ! $employee ) {
            return new WP_Error( 'rest_invalid_employee_id', __( 'Invalid Employee id.', 'erp' ), [ 'status' => 404 ] );
        }

        $employee_id         = isset( $request['user_id'] ) ? intval( $request['user_id'] ) : 0;
        $terminate_date      = ( empty( $request['terminate_date'] ) ) ? current_time( 'mysql' ) : $request['terminate_date'];
        $termination_type    = isset( $request['termination_type'] ) ? $request['termination_type'] : '';
        $termination_reason  = isset( $request['termination_reason'] ) ? $request['termination_reason'] : '';
        $eligible_for_rehire = isset( $request['eligible_for_rehire'] ) ? $request['eligible_for_rehire'] : '';

        $fields = [
            'employee_id'         => $employee_id,
            'terminate_date'      => $terminate_date,
            'termination_type'    => $termination_type,
            'termination_reason'  => $termination_reason,
            'eligible_for_rehire' => $eligible_for_rehire,
        ];
        $result = $employee->terminate( $fields );

        if ( is_wp_error( $result ) ) {
            return new WP_Error( 'rest_insufficient_data', $result->get_error_messages(), [ 'status' => 401 ] );
        }

        $request->set_param( 'context', 'edit' );
        $response = rest_ensure_response( true );
        $response->set_status( 201 );
        $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $user_id ) ) );

        return $response;
    }

    /**
     * Prepare a single user output for response
     *
     * @param array $additional_fields
     *
     * @return mixed|object|WP_REST_Response
     */
    public function prepare_item_for_response( Employee $item, WP_REST_Request $request = null, $additional_fields = [] ) {
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
     * @return array
     */
    public function get_collection_params() {
        return [
            'context'  => $this->get_context_param(),
            'page'     => [
                'description'       => __( 'Current page of the collection.', 'erp' ),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ],
            'per_page' => [
                'description'       => __( 'Maximum number of items to be returned in result set.', 'erp' ),
                'type'              => 'integer',
                'default'           => 20,
                'minimum'           => 1,
                'maximum'           => 100,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'search'   => [
                'description'       => __( 'Limit results to those matching a string.', 'erp' ),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }
}
