<?php

namespace WeDevs\ERP\HRM\CLI;

use WP_CLI;
use WP_CLI_Command;

/**
 * HRM CLI class
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
