<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedLeaveRequests extends AbstractSeeder {

    /**
     * Generate leave requests.
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of leave requests to create.
     * ---
     * default: 100
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:leave-requests
     *     wp hr seed:leave-requests --count=200
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        global $wpdb;

        $count        = (int) ( $assoc_args['count'] ?? 100 );
        $employee_ids = $this->get_employee_user_ids();
        $reasons      = DataProvider::leave_reasons();

        if ( empty( $employee_ids ) ) {
            WP_CLI::error( 'Employees must be created first.' );
        }

        $financial_years = $this->get_financial_years();

        if ( empty( $financial_years ) ) {
            WP_CLI::error( 'Financial years must be created first.' );
        }

        $progress     = $this->progress( 'Creating leave requests', $count );
        $created_ids  = [];
        $fail_count   = 0;

        // Track used dates per employee to avoid overlaps.
        $used_dates = [];

        for ( $i = 0; $i < $count; $i++ ) {
            $user_id = DataProvider::random_element( $employee_ids );

            // Get entitlements for this employee.
            $entitlements = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT e.*, fy.start_date as fy_start, fy.end_date as fy_end
                     FROM {$wpdb->prefix}erp_hr_leave_entitlements e
                     JOIN {$wpdb->prefix}erp_hr_financial_years fy ON e.f_year = fy.id
                     WHERE e.user_id = %d AND e.trn_type = 'leave_policies'
                     ORDER BY RAND() LIMIT 1",
                    $user_id
                )
            );

            if ( empty( $entitlements ) ) {
                $fail_count++;
                $progress->tick();
                continue;
            }

            $ent = $entitlements[0];

            // Generate a random leave duration (1-5 days).
            $duration = mt_rand( 1, 5 );

            // Find a random start date within the financial year (convert timestamps to dates).
            $fy_start = date( 'Y-m-d', $ent->fy_start );
            $fy_end   = date( 'Y-m-d', $ent->fy_end );

            // Ensure end date doesn't exceed FY.
            $latest_start = date( 'Y-m-d', strtotime( $fy_end . " -{$duration} days" ) );

            if ( $latest_start < $fy_start ) {
                $fail_count++;
                $progress->tick();
                continue;
            }

            $start_date = DataProvider::random_working_date_between( $fy_start, $latest_start );
            $end_date   = date( 'Y-m-d', strtotime( $start_date . " +{$duration} days" ) );

            // Skip weekends for end date.
            $dow = date( 'N', strtotime( $end_date ) );

            if ( $dow == 6 ) {
                $end_date = date( 'Y-m-d', strtotime( $end_date . ' +2 days' ) );
            } elseif ( $dow == 7 ) {
                $end_date = date( 'Y-m-d', strtotime( $end_date . ' +1 day' ) );
            }

            if ( $end_date > $fy_end ) {
                $end_date = $fy_end;
            }

            // Check overlap with previous requests for this user.
            $key = $user_id . '_' . $start_date;

            if ( isset( $used_dates[ $key ] ) ) {
                $fail_count++;
                $progress->tick();
                continue;
            }

            $used_dates[ $key ] = true;

            $reason = DataProvider::random_element( $reasons );

            $result = erp_hr_leave_insert_request( [
                'user_id'      => $user_id,
                'leave_policy' => $ent->id,
                'start_date'   => $start_date,
                'end_date'     => $end_date,
                'reason'       => $reason,
                'status'       => 2,
            ] );

            if ( is_wp_error( $result ) ) {
                $fail_count++;
            } else {
                $created_ids[] = $result;
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'leave_request_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d leave requests (%d failed/skipped).', count( $created_ids ), $fail_count ) );
    }
}

WP_CLI::add_command( 'hr seed:leave-requests', __NAMESPACE__ . '\\SeedLeaveRequests' );
