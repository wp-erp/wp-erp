<?php

namespace WeDevs\ERP\Onboarding\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class WorkDaysController extends WP_REST_Controller {

    public function __construct() {
        $this->namespace = 'erp/v1';
        $this->rest_base = 'onboarding/workdays';
    }

    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_workdays'],
                // 'permission_callback' => [$this, 'get_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_designation'],
                // 'permission_callback' => [$this, 'update_permissions_check'],
                'args'                => $this->get_designation_args(),
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_designation'],
                // 'permission_callback' => [$this, 'get_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_designation'],
                // 'permission_callback' => [$this, 'update_permissions_check'],
            ],
        ]);
    }

    public function get_permissions_check($request) {
        return current_user_can('erp_view_list');
    }

    public function update_permissions_check($request) {
        return current_user_can('erp_manage_designation');
    }

    public function get_workdays($request) {
        $working_days = erp_company_get_working_days();
        $options      = [
            '8' => __( 'Full Day', 'erp' ),
            '4' => __( 'Half Day', 'erp' ),
            '0' => __( 'Non-working Day', 'erp' ),
        ];
        $days = [
            'mon' => __( 'Monday', 'erp' ),
            'tue' => __( 'Tuesday', 'erp' ),
            'wed' => __( 'Wednesday', 'erp' ),
            'thu' => __( 'Thursday', 'erp' ),
            'fri' => __( 'Friday', 'erp' ),
            'sat' => __( 'Saturday', 'erp' ),
            'sun' => __( 'Sunday', 'erp' ),
        ];
        $response = [
            'working_days' => $working_days,
            'options'      => $options,
            'days'         => $days,
        ];
        return rest_ensure_response($response);
    }

    public function get_designation($request) {
        $designation = erp_hr_get_designation($request['id']);

        if (!$designation) {
            return new WP_Error('not_found', 'Designation not found', ['status' => 404]);
        }

        return rest_ensure_response($designation);
    }

    public function create_designation($request) {
        $designation = erp_hr_create_designation([
            'title' => sanitize_text_field($request['name'])
        ]);

        if (is_wp_error($designation)) {
            return $designation;
        }

        return rest_ensure_response($designation);
    }

    public function delete_designation($request) {

         // first check this Designation exist
         $designation = \WeDevs\ERP\HRM\Models\Designation::find( $request['id'] );
         if ( ! $designation ) {
             return new WP_Error('not_found', 'Designation not found', ['status' => 404]);
         }
         if ( $designation->num_of_employees() > 0 ) {
            return new WP_Error( 'not-empty', __( 'You can not delete this designation because it contains employees.', 'erp' ) );
        }
        $deleted = erp_hr_delete_designation($request['id']);

        if (!$deleted) {
            return new WP_Error('not_deleted', 'Designation could not be deleted', ['status' => 500]);
        }

        return rest_ensure_response(['deleted' => true]);
    }

    private function get_designation_args() {
        return [
            'name' => [
                'required'          => true,
                'type'             => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
}
