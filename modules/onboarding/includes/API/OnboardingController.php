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


    }

    // Permission checks
    public function get_permissions_check($request) {
        $nonce = $request->get_header('X-WP-Nonce');

        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'rest_forbidden',
                __('Invalid or missing nonce.', 'erp'),
                ['status' => 403]
            );
        }

        return current_user_can('manage_options');
    }

    public function update_permissions_check($request) {
        $nonce = $request->get_header('X-WP-Nonce');

        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'rest_forbidden',
                __('Invalid or missing nonce.', 'erp'),
                ['status' => 403]
            );
        }

        return current_user_can('manage_options');
    }

    // Company Settings Methods
    public function get_company_settings($request) {

        $company_name = get_option('_erp_company', [])['name'] ?? '';
        $erp_settings_general = get_option('erp_settings_general', []);

        $response_setting = [];
        $response_setting['name'] = $company_name;
        $response_setting['gen_com_start'] = $erp_settings_general['gen_com_start'] ?? '';
        $response_setting['gen_financial_month'] = $erp_settings_general['gen_financial_month'] ?? '';

        return rest_ensure_response($response_setting);
    }

    public function update_company_settings($request) {

        $response_setting = [];

        // getting the company serialized data
        $_erp_company = get_option('_erp_company', []);
        $erp_settings_general = get_option('erp_settings_general', []);


        $erp_settings_general['gen_com_start']    = sanitize_text_field($request['gen_com_start']);
        $erp_settings_general['gen_financial_month'] = sanitize_text_field($request['gen_financial_month']);


       $settings_general_updated =  update_option('erp_settings_general', $erp_settings_general);

       $_erp_company['name'] = sanitize_text_field($request['name']);
       $company_updated = update_option('_erp_company', $_erp_company);

        if (is_wp_error($settings_general_updated) || is_wp_error($company_updated)) {
            return new WP_Error('not_updated', 'Company settings could not be updated', ['status' => 500]);
        }
        $response_setting['name'] = $_erp_company['name'];
        $response_setting['gen_com_start'] = $erp_settings_general['gen_com_start'];
        $response_setting['gen_financial_month'] = $erp_settings_general['gen_financial_month'];
        $response_setting['updated'] = true;
        $response_setting['message'] = __('Company settings updated successfully', 'erp');
        return rest_ensure_response($response_setting);
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
            'name' => [
                'required'          => true,
                'type'             => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'gen_com_start' => [
                'required'          => true,
                'type'             => 'string',
                'format'           => 'date',
            ],
            'gen_financial_month' => [
                'required'          => true,
                'type'             => 'string',
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
