<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedLeaveEntitlements extends AbstractSeeder {

    /**
     * Generate leave entitlements for all employees.
     *
     * ## EXAMPLES
     *
     *     wp hr seed:leave-entitlements
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        global $wpdb;

        $employee_ids = $this->get_employee_user_ids();
        $leave_types  = DataProvider::leave_types();

        if ( empty( $employee_ids ) ) {
            WP_CLI::error( 'Employees must be created first. Run seed:employees.' );
        }

        $policies = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}erp_hr_leave_policies ORDER BY f_year ASC, leave_id ASC"
        );

        if ( empty( $policies ) ) {
            WP_CLI::error( 'Leave policies must be created first. Run seed:leave-policies.' );
        }

        $financial_years = $this->get_financial_years();
        $fy_map = [];

        foreach ( $financial_years as $fy ) {
            $fy_map[ $fy->id ] = $fy;
        }

        // Get employee details to check gender for gender-specific policies.
        $emp_genders = [];

        foreach ( $employee_ids as $uid ) {
            $gender = get_user_meta( $uid, 'gender', true );

            if ( empty( $gender ) ) {
                $gender = 'male';
            }

            $emp_genders[ $uid ] = $gender;
        }

        // Get employee hiring dates.
        $emp_hiring = [];

        foreach ( $employee_ids as $uid ) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT hiring_date FROM {$wpdb->prefix}erp_hr_employees WHERE user_id = %d",
                    $uid
                )
            );

            $emp_hiring[ $uid ] = $row ? $row->hiring_date : date( 'Y-m-d', strtotime( '-2 years' ) );
        }

        $total = count( $employee_ids ) * count( $policies );
        $progress = $this->progress( 'Creating leave entitlements', $total );
        $created_ids = [];
        $skip_count  = 0;

        foreach ( $employee_ids as $uid ) {
            foreach ( $policies as $policy ) {
                // Check gender match.
                $leave_type_index = null;

                foreach ( $leave_types as $idx => $lt ) {
                    $lt_row = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT name FROM {$wpdb->prefix}erp_hr_leaves WHERE id = %d",
                            $policy->leave_id
                        )
                    );

                    if ( $lt_row && $lt_row->name === $lt['name'] ) {
                        $leave_type_index = $idx;
                        break;
                    }
                }

                // Skip gender-specific policies.
                if ( $policy->gender !== '-1' && ! empty( $policy->gender ) ) {
                    if ( $emp_genders[ $uid ] !== $policy->gender ) {
                        $skip_count++;
                        $progress->tick();
                        continue;
                    }
                }

                // Skip if employee was not yet hired in this FY.
                if ( isset( $fy_map[ $policy->f_year ] ) ) {
                    $fy = $fy_map[ $policy->f_year ];
                    $hiring_ts = strtotime( $emp_hiring[ $uid ] );

                    if ( $hiring_ts > $fy->end_date ) {
                        $skip_count++;
                        $progress->tick();
                        continue;
                    }
                }

                // Check if entitlement already exists.
                $existing = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements
                         WHERE user_id = %d AND leave_id = %d AND f_year = %d AND trn_id = %d AND trn_type = 'leave_policies'",
                        $uid,
                        $policy->leave_id,
                        $policy->f_year,
                        $policy->id
                    )
                );

                if ( $existing ) {
                    $created_ids[] = $existing;
                    $progress->tick();
                    continue;
                }

                $result = erp_hr_leave_insert_entitlement( [
                    'user_id'     => $uid,
                    'leave_id'    => $policy->leave_id,
                    'created_by'  => get_current_user_id(),
                    'trn_id'      => $policy->id,
                    'trn_type'    => 'leave_policies',
                    'day_in'      => $policy->days,
                    'day_out'     => 0,
                    'description' => 'Seeded entitlement',
                    'f_year'      => $policy->f_year,
                ] );

                if ( is_wp_error( $result ) ) {
                    $skip_count++;
                } else {
                    $created_ids[] = $result;
                }

                $progress->tick();
            }
        }

        $progress->finish();

        $this->store_ids( 'entitlement_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d leave entitlements (%d skipped).', count( $created_ids ), $skip_count ) );
    }
}

WP_CLI::add_command( 'hr seed:leave-entitlements', __NAMESPACE__ . '\\SeedLeaveEntitlements' );
