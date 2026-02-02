<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedShifts extends AbstractSeeder {

    /**
     * Generate shifts and assign employees (Pro feature).
     *
     * ## EXAMPLES
     *
     *     wp hr seed:shifts
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        if ( ! $this->table_exists( 'erp_attendance_shifts' ) ) {
            WP_CLI::warning( 'Attendance module not active (table erp_attendance_shifts not found). Skipping shifts.' );
            return;
        }

        $employee_ids = $this->get_employee_user_ids();

        if ( empty( $employee_ids ) ) {
            WP_CLI::error( 'Employees must be created first.' );
        }

        $shifts     = DataProvider::shift_definitions();
        $progress   = $this->progress( 'Creating shifts', count( $shifts ) );
        $shift_ids  = [];

        global $wpdb;
        $table = $wpdb->prefix . 'erp_attendance_shifts';

        foreach ( $shifts as $shift ) {
            // Check if shift already exists.
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table} WHERE name = %s",
                    $shift['name']
                )
            );

            if ( $existing ) {
                $shift_ids[] = $existing;
                $progress->tick();
                continue;
            }

            if ( function_exists( 'erp_attendance_insert_shift' ) ) {
                $result = erp_attendance_insert_shift(
                    $shift['start'],
                    $shift['end'],
                    $shift['name'],
                    serialize( [ 'fri', 'sat' ] ),
                    1
                );

                if ( $result && ! is_wp_error( $result ) ) {
                    $shift_ids[] = $result;
                } else {
                    WP_CLI::warning( "Failed to create shift '{$shift['name']}'." );
                }
            } else {
                // Direct insert fallback.
                $start_ts = strtotime( "1970-01-01 {$shift['start']}" );
                $end_ts   = strtotime( "1970-01-01 {$shift['end']}" );
                $duration = $end_ts - $start_ts;

                $wpdb->insert(
                    $table,
                    [
                        'name'       => $shift['name'],
                        'start_time' => $shift['start'],
                        'end_time'   => $shift['end'],
                        'duration'   => $duration,
                        'holidays'   => serialize( [ 'fri', 'sat' ] ),
                        'status'     => 1,
                    ],
                    [ '%s', '%s', '%s', '%d', '%s', '%d' ]
                );

                $shift_ids[] = $wpdb->insert_id;
            }

            $progress->tick();
        }

        $progress->finish();

        // Assign employees to shifts.
        $shift_user_table = $wpdb->prefix . 'erp_attendance_shift_user';
        $emp_count        = count( $employee_ids );
        $progress         = $this->progress( 'Assigning employees to shifts', $emp_count );

        shuffle( $employee_ids );

        foreach ( $employee_ids as $index => $user_id ) {
            // Distribute: 40% morning, 40% day, 20% evening.
            if ( $index < $emp_count * 0.4 ) {
                $shift_id = $shift_ids[0];
            } elseif ( $index < $emp_count * 0.8 ) {
                $shift_id = $shift_ids[1];
            } else {
                $shift_id = $shift_ids[2] ?? $shift_ids[1];
            }

            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$shift_user_table} WHERE user_id = %d AND shift_id = %d",
                    $user_id,
                    $shift_id
                )
            );

            if ( ! $existing ) {
                // Always use direct insert for reliability
                $wpdb->insert(
                    $shift_user_table,
                    [
                        'shift_id' => $shift_id,
                        'user_id'  => $user_id,
                        'status'   => 1,
                    ],
                    [ '%d', '%d', '%d' ]
                );
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'shift_ids', $shift_ids );

        WP_CLI::success( sprintf( 'Created %d shifts and assigned %d employees.', count( $shift_ids ), $emp_count ) );
    }
}

WP_CLI::add_command( 'hr seed:shifts', __NAMESPACE__ . '\\SeedShifts' );
