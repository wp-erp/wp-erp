<?php

namespace WeDevs\ERP\Onboarding\API;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;
use WeDevs\ERP\HRM\Models\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Designation;

class ImportEmployeeController extends WP_REST_Controller {

    public function __construct() {
        $this->namespace = 'erp/v1';
        $this->rest_base = 'onboarding/import-employees';
    }

    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_employee_fields'],
                'permission_callback' => [$this, 'get_permissions_check'],
                'args'                => [],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'import_employees'],
                'permission_callback' => [$this, 'update_permissions_check'],
                'args'                => [],
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/sample-csv', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'download_sample_csv'],
                'permission_callback' => [$this, 'get_permissions_check'],
                'args'                => [],
            ],
        ]);
    }

    public function get_permissions_check($request) {
        $nonce = $request->get_header('X-WP-Nonce');

        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'rest_forbidden',
                __('Invalid or missing nonce.', 'erp'),
                ['status' => 403]
            );
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'erp'),
                ['status' => 401]
            );
        }

        // Check for proper capabilities
        if (!current_user_can('manage_option') ) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have sufficient permissions to access this endpoint.', 'erp'),
                ['status' => 403]
            );
        }

        return true;
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

        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be logged in to access this endpoint.', 'erp'),
                ['status' => 401]
            );
        }

        // Check for proper capabilities
        if (!current_user_can('manage_option')) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have sufficient permissions to access this endpoint.', 'erp'),
                ['status' => 403]
            );
        }

        return true;
    }

    public function get_employee_fields($request) {
        $employee_fields = [
            'work'     => [
                'designation'    => __('Designation', 'erp'),
                'department'     => __('Department', 'erp'),
                'location'       => __('Location', 'erp'),
                'hiring_source'  => __('Hiring Source', 'erp'),
                'hiring_date'    => __('Hiring Date', 'erp'),
                'date_of_birth'  => __('Date of Birth', 'erp'),
                'reporting_to'   => __('Reporting To', 'erp'),
                'pay_rate'       => __('Pay Rate', 'erp'),
                'pay_type'       => __('Pay Type', 'erp'),
                'type'           => __('Type', 'erp'),
                'status'         => __('Status', 'erp'),
            ],
            'personal' => [
                'employee_id'    => __('Employee ID', 'erp'),
                'first_name'     => __('First Name', 'erp'),
                'middle_name'    => __('Middle Name', 'erp'),
                'last_name'      => __('Last Name', 'erp'),
                'user_email'     => __('Email', 'erp'),
                'phone'          => __('Phone', 'erp'),
                'work_phone'     => __('Work Phone', 'erp'),
                'mobile'         => __('Mobile', 'erp'),
                'address'        => __('Address', 'erp'),
                'gender'         => __('Gender', 'erp'),
                'marital_status' => __('Marital Status', 'erp'),
                'nationality'    => __('Nationality', 'erp'),
                'driving_license'=> __('Driving License', 'erp'),
                'hobbies'        => __('Hobbies', 'erp'),
                'user_url'       => __('Website', 'erp'),
                'description'    => __('Description', 'erp'),
                'street_1'       => __('Street 1', 'erp'),
                'street_2'       => __('Street 2', 'erp'),
                'city'           => __('City', 'erp'),
                'country'        => __('Country', 'erp'),
                'state'          => __('State', 'erp'),
                'postal_code'    => __('Postal Code', 'erp'),
            ],
        ];

        return rest_ensure_response($employee_fields);
    }

    public function import_employees($request) {
        if (!isset($_FILES['file'])) {
            return new WP_Error('no_file', __('No file uploaded.', 'erp'), ['status' => 400]);
        }

        $file = $_FILES['file'];
        $mapping = json_decode($request->get_param('mapping'), true);

        if (!$mapping) {
            return new WP_Error('invalid_mapping', __('Invalid mapping data.', 'erp'), ['status' => 400]);
        }

        // Process CSV file
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            return new WP_Error('file_error', __('Could not open file.', 'erp'), ['status' => 400]);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            return new WP_Error('invalid_csv', __('Invalid CSV file.', 'erp'), ['status' => 400]);
        }

        $imported = 0;
        $errors = [];

        while (($data = fgetcsv($handle)) !== false) {
            $employee_data = [];
            foreach ($mapping as $field => $column) {
                if ($column && isset($data[array_search($column, $headers)])) {
                    $employee_data[$field] = sanitize_text_field($data[array_search($column, $headers)]);
                }
            }

            // Create employee
            $result = $this->create_employee($employee_data);
            if (is_wp_error($result)) {
                $errors[] = $result->get_error_message();
            } else {
                $imported++;
            }
        }

        fclose($handle);

        return rest_ensure_response([
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    private function create_employee($data) {
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'user_email'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', sprintf(__('Missing required field: %s', 'erp'), $field));
            }
        }

        // Check if email already exists
        if (email_exists($data['user_email'])) {
            return new WP_Error('email_exists', sprintf(__('Email already exists: %s', 'erp'), $data['user_email']));
        }

        // Create WordPress user
        $userdata = [
            'user_login'   => $data['user_email'],
            'user_email'   => $data['user_email'],
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'display_name' => $data['first_name'] . ' ' . $data['last_name'],
            'role'         => 'employee',
        ];

        $user_id = wp_insert_user($userdata);

        if (is_wp_error($user_id)) {
            return $user_id;
        }

        // Create employee record
        $employee_data = [
            'user_id'      => $user_id,
            'designation'  => isset($data['designation']) ? $data['designation'] : 0,
            'department'   => isset($data['department']) ? $data['department'] : 0,
            'location'     => isset($data['location']) ? $data['location'] : '',
            'hiring_date'  => isset($data['hiring_date']) ? $data['hiring_date'] : current_time('mysql'),
            'status'       => isset($data['status']) ? $data['status'] : 'active',
        ];

        $employee = new Employee();
        $employee->create($employee_data);

        return true;
    }

    public function download_sample_csv($request) {
        $filename = 'employee-import-sample.csv';
        $filepath = WP_CONTENT_DIR . '/uploads/' . $filename;

        // Create sample CSV content
        $headers = [
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Department',
            'Designation',
            'Hiring Date',
            'Status'
        ];

        $sample_data = [
            ['John', 'Doe', 'john@example.com', '1234567890', 'IT', 'Developer', '2024-01-01', 'active'],
            ['Jane', 'Smith', 'jane@example.com', '0987654321', 'HR', 'Manager', '2024-01-01', 'active']
        ];

        // Create CSV file
        $fp = fopen($filepath, 'w');
        fputcsv($fp, $headers);
        foreach ($sample_data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output file content
        readfile($filepath);
        unlink($filepath);
        exit;
    }
}
