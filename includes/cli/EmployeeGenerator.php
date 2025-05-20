<?php

namespace WeDevs\ERP\CLI;

use WP_CLI;
use Faker\Factory;

/**
 * Employee Generator CLI Commands
 *
 * @since 1.0.0
 */
class EmployeeGenerator {

    /**
     * Generate random employees
     *
     * ## OPTIONS
     *
     * <count>
     * : Number of employees to generate
     *
     * ## EXAMPLES
     *
     *     wp erp generate-employees 10
     *
     * @param array $args Command arguments
     * @param array $assoc_args Command associative arguments
     */
    public function generate_employees($args, $assoc_args) {
        // Check if Faker is available
        if (!class_exists('Faker\Factory')) {
            WP_CLI::error('Faker library is not installed. Please run: composer require fakerphp/faker --dev');
            return;
        }

        $count = absint($args[0]);

        if ($count < 1) {
            WP_CLI::error('Please provide a valid number of employees to generate.');
            return;
        }

        WP_CLI::log(sprintf('Generating %d random employees...', $count));

        $faker = Factory::create();
        $success_count = 0;
        $error_count = 0;

        for ($i = 0; $i < $count; $i++) {
            try {
                $employee_data = [
                    'user_email'     => $faker->unique()->safeEmail(),
                    'user_login'     => $faker->unique()->userName(),
                    'user_pass'      => wp_generate_password(),
                    'first_name'     => $faker->firstName(),
                    'last_name'      => $faker->lastName(),
                    'display_name'   => $faker->name(),
                    'user_nicename'  => $faker->userName(),
                    'user_url'       => $faker->url(),
                    'user_registered' => current_time('mysql'),
                    'role'           => 'employee',
                    'status'         => 'active',

                    'joining_date'   => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'work_email'     => $faker->companyEmail(),
                    'phone'          => $faker->phoneNumber(),
                    'mobile'         => $faker->phoneNumber(),
                    'other_email'    => $faker->optional()->safeEmail(),
                    'address'        => [
                        'street_1'    => $faker->streetAddress(),
                        'street_2'    => $faker->optional()->secondaryAddress(),
                        'city'        => $faker->city(),
                        'state'       => $faker->state(),
                        'postal_code' => $faker->postcode(),
                        'country'     => $faker->countryCode(),
                    ],
                    'personal'       => [
                        'photo_id'    => 0,
                        'first_name' => $faker->firstName,
                        'last_name' => $faker->lastName,
                        'birth'       => $faker->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
                        'gender'      => $faker->randomElement(['male', 'female']),
                        'marital_status' => $faker->randomElement(array_keys(erp_hr_get_marital_statuses())),
                        'nationality' => $faker->country(),
                        'driving_license' => $faker->optional()->bothify('??###???'),
                    ],
                    'work'           => [
                        'type'           => 'permanent',
                        'employee_id' => $faker->unique()->numerify('EMP-####'),
                        'employment_type' => $faker->randomElement(['permanent', 'contract', 'temporary']),
                        'job_title'   => $faker->jobTitle(),
                        'department'  => $this->get_random_department(),
                        'reporting_to' => 0,
                        'designation'    => $this->get_random_designation(),
                        // 'location'    => $faker->city(),
                        'hiring_source' => $faker->randomElement(array_keys(erp_hr_get_employee_sources())),
                        'hiring_date' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                        'employment_status' => 'active',
                        'location' => '-1'
                    ],
                ];

                $employee = new \WeDevs\ERP\HRM\Employee();
                $result = $employee->create_employee($employee_data);

                if (is_wp_error($result)) {
                    throw new \Exception($result->get_error_message());
                }

                $success_count++;
                WP_CLI::log(sprintf('Created employee: %s %s', $employee_data['first_name'], $employee_data['last_name']));

            } catch (\Exception $e) {
                $error_count++;
                WP_CLI::warning(sprintf('Failed to create employee: %s', $e->getMessage()));
            }
        }

        WP_CLI::success(sprintf(
            'Employee generation completed. Successfully created %d employees. Failed: %d',
            $success_count,
            $error_count
        ));
    }

    /**
     * Get a random department ID
     *
     * @return int
     */
    private function get_random_department() {
        $departments = erp_hr_get_departments();

        if (empty($departments)) {
            // Create a default department if none exists
            $department_id = erp_hr_create_department([
                'title' => 'General',
                'description' => 'General Department',
                'status' => 1
            ]);
        } else {
            $department = $departments[array_rand($departments)];
            $department_id = $department->id;
        }

        return $department_id;
    }

    /**
     * Get a random designation ID
     *
     * @return int
     */
    private function get_random_designation() {
        $designations = erp_hr_get_designations();

        if (empty($designations)) {
            // Create a default designation if none exists
            $designation_id = erp_hr_create_designation([
                'title' => 'Staff',
                'description' => 'Staff Position',
                'status' => 1
            ]);
        } else {
            $designation = $designations[array_rand($designations)];
            $designation_id = $designation->id;
        }

        return $designation_id;
    }
}
