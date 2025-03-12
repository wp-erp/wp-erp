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

        $table_name = $wpdb->prefix . 'erp_hr_financial_years';
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

        // multiple entries from request
        $years = $request['year'];
        $start_dates = $request['start_date'];
        $end_dates = $request['end_date'];

        // Validate input arrays have same length
        if (!is_array($years) || !is_array($start_dates) || !is_array($end_dates) ||
            count($years) !== count($start_dates) || count($years) !== count($end_dates)) {
            return new WP_Error(
                'invalid_input',
                'Invalid input: Years, start dates, and end dates must be arrays of equal length',
                ['status' => 400]
            );
        }

        // Validate date formats and ranges
        foreach ($years as $i => $year) {
            $start = strtotime($start_dates[$i]);
            $end = strtotime($end_dates[$i]);
            
            if (!$start || !$end || $start >= $end) {
                return new WP_Error(
                    'invalid_dates',
                    'Invalid date range: Start date must be before end date',
                    ['status' => 400]
                );
            }
        }

        // Prepare data and check for duplicates
        $data = [];
        $duplicate_years = [];
        $financial_year_ids = [];


        foreach ($years as $i => $year) {
            $year = sanitize_text_field($year);
            $start_date = sanitize_text_field($start_dates[$i]);
            $end_date = sanitize_text_field($end_dates[$i]);

            // Check if entry already exists for this year
            $existing = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE fy_name = %s",
                    $year
                )
            );

            if ($existing) {
                $duplicate_years[] = $year;
                continue; // Skip this entry
            }

            $data[] = [
                'fy_name'      => sanitize_text_field( wp_unslash( $year ) ),
                'start_date' => strtotime( sanitize_text_field( wp_unslash( $start_date ) ) ),
                'end_date'   => strtotime( sanitize_text_field( wp_unslash( $end_date ) ) ),
                'created_at' => gmdate( 'Y-m-d' ),
                'created_by' => get_current_user_id(),
            ];
        }


        if (empty($data)) {
            return new WP_Error(
                'no_valid_entries',
                'No valid leave year entries could be created. All years may already exist.',
                [
                    'status' => 400,
                    'duplicate_years' => $duplicate_years
                ]
            );
        }

        $format = ['%s', '%s', '%s', '%s', '%d'];
        foreach ($data as $row) {
            $inserted = $wpdb->insert($table_name, $row, $format );
            if (!$inserted) {
                return new WP_Error('insert_error', 'Could not create some leave years', ['status' => 500]);
            }
            $financial_year_ids[] = $wpdb->insert_id;
        }

        if($request['generate_default_leave_policies'] == 'yes') {
            $this->generate_prefault_leave_policies( $financial_year_ids );
        }


        $response = [
            'created_entries' => $data,
            'skipped_years' => $duplicate_years
        ];

        return rest_ensure_response($response);
    }

    function generate_prefault_leave_policies( $financial_year_ids ) {
       $casual_leave_id = erp_hr_insert_leave_policy_name( ['name' => 'Casual Leave']);
       $sick_leave_id = erp_hr_insert_leave_policy_name( ['name' => 'Sick Leave']);

       foreach ( $financial_year_ids as $f_year ) {

            $data = array(
                    'leave_id'            => $casual_leave_id,
                    'employee_type'       => '-1',
                    'description'         => 'Casual Leave',
                    'days'                => 14,
                    'color'               => '#009682',
                    'department_id'       => '-1',
                    'designation_id'      => '-1',
                    'location_id'         => '-1',
                    'gender'              => '-1',
                    'marital'             => '-1',
                    'f_year'              => $f_year,
                    'applicable_from'     => 0,
                    'apply_for_new_users' => 0,
                );
            erp_hr_leave_insert_policy( $data );
         }

        foreach ( $financial_year_ids as $f_year ) {
            $data = array(
                'leave_id'            => $sick_leave_id,
                'employee_type'       => '-1',
                'description'         => 'Sick Leave',
                'days'                => 14,
                'color'               => '#c90000',
                'department_id'       => '-1',
                'designation_id'      => '-1',
                'location_id'         => '-1',
                'gender'              => '-1',
                'marital'             => '-1',
                'f_year'              => $f_year,
                'applicable_from'     => 0,
                'apply_for_new_users' => 0,
            );
            erp_hr_leave_insert_policy( $data );
        }

    }


    public function update_leave_year($request) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'erp_hr_financial_years';
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

        $table_name = $wpdb->prefix . 'erp_hr_financial_years';
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
            // 'year' => [
            //     'required'          => true,
            //     'type'             => 'string',
            //     'sanitize_callback' => 'sanitize_text_field',
            // ],
            // 'start_date' => [
            //     'required'          => true,
            //     'type'             => 'string',
            // ],
            // 'end_date' => [
            //     'required'          => true,
            //     'type'             => 'string',
            // ],
        ];
    }
}
