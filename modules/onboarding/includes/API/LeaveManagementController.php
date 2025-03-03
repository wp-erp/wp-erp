<?php

namespace WeDevs\ERP\Onboarding\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

class LeaveManagementController extends WP_REST_Controller {
    
    public function __construct() {
        $this->namespace = 'erp/v1';
        $this->rest_base = 'onboarding/leave-years';        
    }

    public function register_routes() {
        // Get all leave years
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_leave_years'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_leave_year'],
                'permission_callback' => [$this, 'update_permissions_check'],
                'args'                => $this->get_leave_year_args(),
            ],
        ]);

        // Single leave year operations
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_leave_year'],
                'permission_callback' => [$this, 'get_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_leave_year'],
                'permission_callback' => [$this, 'update_permissions_check'],
                'args'                => $this->get_leave_year_args(),
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_leave_year'],
                'permission_callback' => [$this, 'update_permissions_check'],
            ],
        ]);
    }

    public function get_permissions_check($request) {
       return current_user_can('manage_options');
    }

    public function update_permissions_check($request) {
        return current_user_can('manage_options');
    }

    public function get_leave_years($request) {
        $results = erp_get_hr_financial_years();
        return rest_ensure_response($results);
    }

    public function get_leave_year($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'erp_hr_leave_years';
        $id = (int) $request['id'];
        
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id),
            ARRAY_A
        );

        if (!$result) {
            return new WP_Error('not_found', 'Leave year not found', ['status' => 404]);
        }

        return rest_ensure_response($result);
    }

    public function create_leave_year($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'erp_hr_financial_years';
        
        $data = [
            'year'       => sanitize_text_field($request['year']),
            'start_date' => sanitize_text_field($request['start_date']),
            'end_date'   => sanitize_text_field($request['end_date']),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'created_by' =>  get_current_user_id(),
            'updated_by' =>  get_current_user_id(),
        ];

        $format = ['%s', '%s', '%s', '%s', '%s'];
        
        $inserted = $wpdb->insert($table_name, $data, $format);

        if (!$inserted) {
            return new WP_Error('insert_error', 'Could not create leave year', ['status' => 500]);
        }

        $data['id'] = $wpdb->insert_id;
        
        return rest_ensure_response($data);
    }

    public function update_leave_year($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'erp_hr_leave_years';
        $id = (int) $request['id'];
        
        $data = [
            'year'       => sanitize_text_field($request['year']),
            'start_date' => sanitize_text_field($request['start_date']),
            'end_date'   => sanitize_text_field($request['end_date']),
            'updated_at' => current_time('mysql'),
        ];

        $format = ['%s', '%s', '%s', '%s'];
        $where = ['id' => $id];
        $where_format = ['%d'];
        
        $updated = $wpdb->update($table_name, $data, $where, $format, $where_format);

        if ($updated === false) {
            return new WP_Error('update_error', 'Could not update leave year', ['status' => 500]);
        }

        $data['id'] = $id;
        
        return rest_ensure_response($data);
    }

    public function delete_leave_year($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'erp_hr_leave_years';
        $id = (int) $request['id'];
        
        $deleted = $wpdb->delete(
            $table_name,
            ['id' => $id],
            ['%d']
        );

        if (!$deleted) {
            return new WP_Error('delete_error', 'Could not delete leave year', ['status' => 500]);
        }

        return rest_ensure_response(['deleted' => true]);
    }

    private function get_leave_year_args() {
        return [
            'year' => [
                'required'          => true,
                'type'             => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'start_date' => [
                'required'          => true,
                'type'             => 'string',
                'format'           => 'date',
            ],
            'end_date' => [
                'required'          => true,
                'type'             => 'string',
                'format'           => 'date',
            ],
        ];
    }
}