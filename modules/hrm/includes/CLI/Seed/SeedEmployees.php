<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;
use WeDevs\ERP\HRM\Employee;

class SeedEmployees extends AbstractSeeder {

    /**
     * Generate employees.
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of employees to create.
     * ---
     * default: 50
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:employees
     *     wp hr seed:employees --count=100
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        $count           = (int) ( $assoc_args['count'] ?? 50 );
        $department_ids  = $this->get_department_ids();
        $designation_ids = $this->get_designation_ids();

        if ( empty( $department_ids ) || empty( $designation_ids ) ) {
            WP_CLI::error( 'Departments and designations must be created first. Run seed:departments and seed:designations.' );
        }

        $male_names   = DataProvider::first_names_male();
        $female_names = DataProvider::first_names_female();
        $last_names   = DataProvider::last_names();

        $employee_types = [ 'permanent', 'permanent', 'permanent', 'permanent', 'permanent', 'permanent', 'permanent', 'parttime', 'parttime', 'contract' ];
        $genders        = [ 'male', 'male', 'male', 'female', 'female' ];
        $marital        = [ 'single', 'married', 'married', 'single' ];
        $countries      = [ 'US', 'US', 'US', 'GB', 'CA', 'AU' ];

        $progress     = $this->progress( 'Creating employees', $count );
        $created_ids  = [];
        $used_emails  = [];

        for ( $i = 0; $i < $count; $i++ ) {
            $gender     = DataProvider::random_element( $genders );
            $first_name = ( $gender === 'male' )
                ? DataProvider::random_element( $male_names )
                : DataProvider::random_element( $female_names );
            $last_name  = DataProvider::random_element( $last_names );

            $email_base = strtolower( $first_name . '.' . $last_name );
            $email      = $email_base . '@example.com';
            $suffix     = 1;

            while ( in_array( $email, $used_emails, true ) || email_exists( $email ) ) {
                $email = $email_base . $suffix . '@example.com';
                $suffix++;
            }

            $used_emails[] = $email;

            $is_terminated = ( $i >= $count * 0.9 );
            $status        = $is_terminated ? 'terminated' : 'active';

            $hire_start = date( 'Y-m-d', strtotime( '-2 years' ) );
            $hire_end   = date( 'Y-m-d', strtotime( '-1 month' ) );
            $hiring_date = DataProvider::random_date_between( $hire_start, $hire_end );

            $dob_start = date( 'Y-m-d', strtotime( '-55 years' ) );
            $dob_end   = date( 'Y-m-d', strtotime( '-22 years' ) );
            $dob       = DataProvider::random_date_between( $dob_start, $dob_end );

            $dept_id  = DataProvider::random_element( $department_ids );
            $desig_id = DataProvider::random_element( $designation_ids );
            $emp_type = DataProvider::random_element( $employee_types );
            $country  = DataProvider::random_element( $countries );
            $mar_status = DataProvider::random_element( $marital );

            $pay_rate = mt_rand( 3000, 15000 );

            $employee_data = [
                'user_email' => $email,
                'personal'   => [
                    'first_name'     => $first_name,
                    'last_name'      => $last_name,
                    'gender'         => $gender,
                    'marital_status' => $mar_status,
                    'nationality'    => $country,
                    'date_of_birth'  => $dob,
                    'phone'          => sprintf( '555%07d', mt_rand( 1000000, 9999999 ) ),
                    'mobile'         => sprintf( '555%07d', mt_rand( 1000000, 9999999 ) ),
                    'address'        => mt_rand( 100, 9999 ) . ' Main Street',
                    'city'           => 'New York',
                    'state'          => 'NY',
                    'country'        => $country,
                    'postal_code'    => sprintf( '%05d', mt_rand( 10000, 99999 ) ),
                ],
                'work' => [
                    'designation'  => $desig_id,
                    'department'   => $dept_id,
                    'hiring_source' => 'direct',
                    'hiring_date'  => $hiring_date,
                    'date_of_birth' => $dob,
                    'pay_rate'     => $pay_rate,
                    'pay_type'     => 'monthly',
                    'type'         => $emp_type,
                    'status'       => $status,
                ],
            ];

            if ( $is_terminated ) {
                $term_date = DataProvider::random_date_between( $hiring_date, date( 'Y-m-d' ) );
                $employee_data['work']['termination_date'] = $term_date;
            }

            $employee = new Employee( null );
            $result   = $employee->create_employee( $employee_data );

            if ( is_wp_error( $result ) ) {
                WP_CLI::warning( "Failed to create employee '{$first_name} {$last_name}': " . $result->get_error_message() );
            } else {
                $created_ids[] = is_object( $result ) ? $result->get_user_id() : $result;
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'employee_user_ids', $created_ids );

        // Update department leads with first employee in each department.
        $this->update_department_leads( $department_ids );

        WP_CLI::success( sprintf( 'Created %d employees.', count( $created_ids ) ) );
    }

    private function update_department_leads( $department_ids ) {
        global $wpdb;

        foreach ( $department_ids as $dept_id ) {
            $lead = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE department = %d AND status = 'active' ORDER BY user_id ASC LIMIT 1",
                    $dept_id
                )
            );

            if ( $lead ) {
                $wpdb->update(
                    $wpdb->prefix . 'erp_hr_depts',
                    [ 'lead' => $lead ],
                    [ 'id' => $dept_id ],
                    [ '%d' ],
                    [ '%d' ]
                );
            }
        }
    }
}

WP_CLI::add_command( 'hr seed:employees', __NAMESPACE__ . '\\SeedEmployees' );
