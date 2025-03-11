<?php

namespace WeDevs\ERP\Onboarding\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class DepartmentController extends WP_REST_Controller {
    
    public function __construct() {
        $this->namespace = 'erp/v1';
        $this->rest_base = 'onboarding/departments';
    }

    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_departments'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_department'],
                'permission_callback' => [$this, 'update_permissions_check'],
                'args'                => $this->get_department_args(),
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_department'],
                'permission_callback' => [$this, 'update_permissions_check'],
            ],
        ]);
    }

    public function get_permissions_check($request) {
        return current_user_can('erp_view_list');
    }

    public function update_permissions_check($request) {
        return current_user_can('erp_manage_department');
    }

    public function get_departments($request) {
        $departments = new \WeDevs\ERP\HRM\Models\Department();
        $departments = $departments->all(['id', 'title as name']);
        return rest_ensure_response($departments);
    }

    public function create_department($request) {
        $department = erp_hr_create_department([
            'title' => sanitize_text_field($request['name'])
        ]);

        if (is_wp_error($department)) {
            return $department;
        }

        $departments = new \WeDevs\ERP\HRM\Models\Department();
        $departments = $departments->all(['id', 'title as name']);
        return rest_ensure_response($departments);
    }

    public function delete_department($request) {
        // first check this department exist 
        $resp = \WeDevs\ERP\HRM\Models\Department::find( $request['id'] );
        if ( ! $resp ) {
            return new WP_Error('not_found', 'Department not found', ['status' => 404]);
        }


        $deleted = erp_hr_delete_department($request['id']);

        if (!$deleted) {
            return new WP_Error('not_deleted', 'Department could not be deleted', ['status' => 500]);
        }
        
        $departments = new \WeDevs\ERP\HRM\Models\Department();
        $departments = $departments->all(['id', 'title as name']);

        return rest_ensure_response($departments);
    }

    private function get_department_args() {
        return [
            'name' => [
                'required'          => true,
                'type'             => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
}