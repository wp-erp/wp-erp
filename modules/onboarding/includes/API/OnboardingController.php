<?php

namespace WeDevs\ERP\Onboarding\API;
use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class OnboardingController extends WP_REST_Controller {
    
    public function __construct() {
        $this->namespace = 'erp/v1';
        $this->rest_base = 'onboarding';
    }

    public function register_routes() {
        // Company Setup API
        register_rest_route($this->namespace, '/' . $this->rest_base . '/company', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_company_settings'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_company_settings'],
                'permission_callback' => [$this, 'update_permissions_check'],
                'args'                => $this->get_company_args(),
            ],
        ]);

        // Departments API
        register_rest_route($this->namespace, '/' . $this->rest_base . '/departments', [
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

        // Single Department API
        register_rest_route($this->namespace, '/' . $this->rest_base . '/departments/(?P<id>[\d]+)', [
            // [
            //     'methods'             => WP_REST_Server::READABLE,
            //     'callback'            => [$this, 'get_department'],
            //     'permission_callback' => [$this, 'get_permissions_check'],
            // ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_department'],
                'permission_callback' => [$this, 'update_permissions_check'],
            ],
        ]);

        // Designations API
        register_rest_route($this->namespace, '/' . $this->rest_base . '/designations', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_designations'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_designation'],
                'permission_callback' => [$this, 'update_permissions_check'],
                'args'                => $this->get_designation_args(),
            ],
        ]);

        // Single Designation API
        register_rest_route($this->namespace, '/' . $this->rest_base . '/designations/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_designation'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_designation'],
                'permission_callback' => [$this, 'update_permissions_check'],
            ],
        ]);
    }

    // Permission checks
    public function get_permissions_check($request) {
        return true;
    }

    public function update_permissions_check($request) {
        return true;
    }

    // Company Settings Methods
    public function get_company_settings($request) {
        
        $company_name = get_option('_erp_company', [])['name'];
        $erp_settings_general = get_option('erp_settings_general', []);
        
        $response_setting = [];
        $response_setting['company_name'] = $company_name;
        $response_setting['company_start'] = $erp_settings_general['gen_com_start'];
        $response_setting['company_financial_month'] = $erp_settings_general['gen_financial_month'];

        return rest_ensure_response($response_setting);
    }

    public function update_company_settings($request) {
        $settings = [
            'company_name'     => sanitize_text_field($request['company_name']),
            'gen_com_start'    => sanitize_text_field($request['company_start']),
            'gen_financial_month'   => sanitize_text_field($request['financial_year']),
        ];

        update_option('erp_company_settings', $settings);
        return rest_ensure_response($settings);
    }

    // Department Methods
    public function get_departments($request) {
        $departments = erp_hr_get_departments();
        return rest_ensure_response($departments);
    }

    public function get_department($request) {
        $department = erp_hr_get_department($request['id']);
        
        if (!$department) {
            return new WP_Error('not_found', 'Department not found', ['status' => 404]);
        }

        return rest_ensure_response($department);
    }

    public function create_department($request) {
        $department = erp_hr_create_department([
            'title' => sanitize_text_field($request['name'])
        ]);

        if (is_wp_error($department)) {
            return $department;
        }

        return rest_ensure_response($department);
    }

    public function delete_department($request) {
        $deleted = erp_hr_delete_department($request['id']);

        if (!$deleted) {
            return new WP_Error('not_deleted', 'Department could not be deleted', ['status' => 500]);
        }

        return rest_ensure_response(['deleted' => true]);
    }

    // Designation Methods
    public function get_designations($request) {
        $designations = erp_hr_get_designations();
        return rest_ensure_response($designations);
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
        $deleted = erp_hr_delete_designation($request['id']);

        if (!$deleted) {
            return new WP_Error('not_deleted', 'Designation could not be deleted', ['status' => 500]);
        }

        return rest_ensure_response(['deleted' => true]);
    }

    // Argument Definitions
    private function get_company_args() {
        return [
            'company_name' => [
                'required'          => true,
                'type'             => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'company_start' => [
                'required'          => true,
                'type'             => 'string',
                'format'           => 'date',
            ],
            'financial_year' => [
                'required'          => true,
                'type'             => 'string',
                'format'           => 'date',
            ],
        ];
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