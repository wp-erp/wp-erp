<?php

namespace WeDevs\ERP\HRM\CLI;

use WP_CLI;
use WP_CLI_Command;

/**
 * HRM CLI class
 */

/**
 * HRM CLI Commands
 * # Master command - seeds everything
 *    wp hr clean
 *    wp hr seed

 *   # With options
 *   wp hr seed --employees=100 --clean-first
 *   wp hr seed --skip=shifts,attendance,training,assets,payroll
 *   wp hr seed --only=financial-years,departments,designations,employees
 *
 *  # Individual seeders
 *   wp hr seed:financial-years
 *   wp hr seed:departments
 *   wp hr seed:designations
 *   wp hr seed:employees
 *   wp hr seed:leave-types
 *   wp hr seed:leave-policies
 *   wp hr seed:leave-entitlements
 *   wp hr seed:leave-requests
 *   wp hr seed:leave-approvals
 *   wp hr seed:holidays
 *   wp hr seed:shifts
 *   wp hr seed:attendance
 *   wp hr seed:training
 *   wp hr seed:assets
 *   wp hr seed:payroll
 *   wp hr seed:announcements

 *  # Clean data
 *  wp hr clean
 */
class Commands extends WP_CLI_Command {

    /**
     * Clean HRM tables
     *
     * @since 1.2.0
     *
     * @return void
     */
    public function clean() {
        global $wpdb;

        $tables = [
            'erp_hr_financial_years',
            'erp_hr_depts',
            'erp_hr_designations',
            'erp_hr_employees',
            'erp_hr_employee_history',
            'erp_hr_employee_notes',
            'erp_hr_leave_policies',
            'erp_hr_holiday',
            'erp_hr_leave_entitlements',
            'erp_hr_leaves',
            'erp_hr_leave_requests',
            'erp_hr_work_exp',
            'erp_hr_education',
            'erp_hr_dependents',
            'erp_hr_employee_performance',
            'erp_hr_announcement',
        ];

        foreach ( $tables as $table ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        WP_CLI::success( 'Table deleted successfully!' );
    }
}

WP_CLI::add_command( 'hr', 'WeDevs\ERP\HRM\CLI\Commands' );

// Auto-load seed files
$seed_dir = __DIR__ . '/Seed';
if (is_dir($seed_dir)) {
    require_once $seed_dir . '/DataProvider.php';
    require_once $seed_dir . '/AbstractSeeder.php';

    // Load master seed command first
    if (file_exists($seed_dir . '/SeedCommand.php')) {
        require_once $seed_dir . '/SeedCommand.php';
    }

    // Load all other seed files
    foreach (glob($seed_dir . '/Seed*.php') as $file) {
        if (basename($file) !== 'SeedCommand.php') {
            require_once $file;
        }
    }
}
