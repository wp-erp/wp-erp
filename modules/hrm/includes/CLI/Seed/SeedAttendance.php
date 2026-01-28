<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedAttendance extends AbstractSeeder {

    /**
     * Generate attendance records (Pro feature).
     *
     * ## OPTIONS
     *
     * [--months=<months>]
     * : Number of past months to generate attendance for.
     * ---
     * default: 6
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:attendance
     *     wp hr seed:attendance --months=12
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        if ( ! $this->table_exists( 'erp_attendance_date_shift' ) ) {
            WP_CLI::warning( 'Attendance module not active (table erp_attendance_date_shift not found). Skipping.' );
            return;
        }

        global $wpdb;

        $months       = (int) ( $assoc_args['months'] ?? 6 );
        $employee_ids = $this->get_employee_user_ids();

        if ( empty( $employee_ids ) ) {
            WP_CLI::error( 'Employees must be created first.' );
        }

        $date_shift_table = $wpdb->prefix . 'erp_attendance_date_shift';
        $log_table        = $wpdb->prefix . 'erp_attendance_log';
        $shift_user_table = $wpdb->prefix . 'erp_attendance_shift_user';

        // Get holidays.
        $holidays_raw = $wpdb->get_results(
            "SELECT start, end FROM {$wpdb->prefix}erp_hr_holiday"
        );

        $holiday_dates = [];

        foreach ( $holidays_raw as $h ) {
            $current = strtotime( $h->start );
            $end     = strtotime( $h->end );

            while ( $current <= $end ) {
                $holiday_dates[ date( 'Y-m-d', $current ) ] = true;
                $current += 86400;
            }
        }

        $start_date = date( 'Y-m-d', strtotime( "-{$months} months" ) );
        $end_date   = date( 'Y-m-d', strtotime( '-1 day' ) );

        // Build date range (working days only).
        $dates = [];
        $current = strtotime( $start_date );
        $end_ts  = strtotime( $end_date );

        while ( $current <= $end_ts ) {
            $d   = date( 'Y-m-d', $current );
            $dow = date( 'N', $current );

            // Skip weekends (Fri=5, Sat=6 for some, or Sat=6, Sun=7).
            if ( $dow <= 5 && ! isset( $holiday_dates[ $d ] ) ) {
                $dates[] = $d;
            }

            $current += 86400;
        }

        $total = count( $employee_ids ) * count( $dates );

        WP_CLI::log( sprintf( 'Generating attendance for %d employees over %d working days (%d records).',
            count( $employee_ids ), count( $dates ), $total ) );

        $progress   = $this->progress( 'Creating attendance records', $total );
        $created    = 0;
        $batch_ds   = [];
        $batch_size = 500;

        foreach ( $employee_ids as $user_id ) {
            // Get user's shift.
            $shift = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT s.* FROM {$wpdb->prefix}erp_attendance_shifts s
                     JOIN {$shift_user_table} su ON s.id = su.shift_id
                     WHERE su.user_id = %d AND s.status = 1 LIMIT 1",
                    $user_id
                )
            );

            if ( ! $shift ) {
                // Default to 09:00-17:00 if no shift assigned.
                $shift_start = '09:00:00';
                $shift_end   = '17:00:00';
                $shift_id    = 0;
            } else {
                $shift_start = $shift->start_time;
                $shift_end   = $shift->end_time;
                $shift_id    = $shift->id;
            }

            foreach ( $dates as $date ) {
                // 90% attendance rate.
                $is_present = ( mt_rand( 1, 100 ) <= 90 );

                if ( ! $is_present ) {
                    // For absent days, use shift times (schema doesn't allow NULL)
                    $batch_ds[] = [
                        'date'       => $date,
                        'user_id'    => $user_id,
                        'shift_id'   => $shift_id,
                        'start_time' => $date . ' ' . $shift_start,
                        'end_time'   => $date . ' ' . $shift_end,
                        'present'    => 0,
                        'late'       => 0,
                        'early_left' => 0,
                        'day_type'   => 'working_day',
                        'created_at' => current_time( 'mysql' ),
                    ];

                    $created++;
                    $progress->tick();

                    if ( count( $batch_ds ) >= $batch_size ) {
                        $this->insert_batch( $date_shift_table, $log_table, $batch_ds );
                        $batch_ds = [];
                    }

                    continue;
                }

                // Randomize checkin: shift start +/- 15 min.
                $shift_start_ts = strtotime( $date . ' ' . $shift_start );
                $checkin_offset = mt_rand( -15, 15 ) * 60;
                $checkin_ts     = $shift_start_ts + $checkin_offset;

                // Randomize checkout: shift end +/- 30 min.
                $shift_end_ts    = strtotime( $date . ' ' . $shift_end );
                $checkout_offset = mt_rand( -30, 30 ) * 60;
                $checkout_ts     = $shift_end_ts + $checkout_offset;

                // Calculate late/early.
                $late       = max( 0, $checkin_ts - $shift_start_ts );
                $early_left = max( 0, $shift_end_ts - $checkout_ts );

                $checkin_str  = date( 'Y-m-d H:i:s', $checkin_ts );
                $checkout_str = date( 'Y-m-d H:i:s', $checkout_ts );
                $time_worked  = $checkout_ts - $checkin_ts;

                $batch_ds[] = [
                    'date'        => $date,
                    'user_id'     => $user_id,
                    'shift_id'    => $shift_id,
                    'start_time'  => $checkin_str,
                    'end_time'    => $checkout_str,
                    'present'     => 1,
                    'late'        => $late,
                    'early_left'  => $early_left,
                    'day_type'    => 'working_day',
                    'created_at'  => current_time( 'mysql' ),
                    '_checkin'    => $checkin_str,
                    '_checkout'   => $checkout_str,
                    '_time'       => $time_worked,
                ];

                $created++;
                $progress->tick();

                if ( count( $batch_ds ) >= $batch_size ) {
                    $this->insert_batch( $date_shift_table, $log_table, $batch_ds );
                    $batch_ds = [];
                }
            }
        }

        // Insert remaining batch.
        if ( ! empty( $batch_ds ) ) {
            $this->insert_batch( $date_shift_table, $log_table, $batch_ds );
        }

        $progress->finish();

        WP_CLI::success( sprintf( 'Created %d attendance records.', $created ) );
    }

    private function insert_batch( $date_shift_table, $log_table, $records ) {
        global $wpdb;

        foreach ( $records as $record ) {
            $checkin  = isset( $record['_checkin'] ) ? $record['_checkin'] : null;
            $checkout = isset( $record['_checkout'] ) ? $record['_checkout'] : null;
            $time     = isset( $record['_time'] ) ? $record['_time'] : 0;

            unset( $record['_checkin'], $record['_checkout'], $record['_time'] );

            // Check if record already exists.
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$date_shift_table} WHERE date = %s AND user_id = %d",
                    $record['date'],
                    $record['user_id']
                )
            );

            if ( $existing ) {
                continue;
            }

            $wpdb->insert( $date_shift_table, $record );
            $ds_id = $wpdb->insert_id;

            // Insert log record if present.
            if ( $record['present'] && $checkin && $ds_id ) {
                $wpdb->insert(
                    $log_table,
                    [
                        'user_id'       => $record['user_id'],
                        'date_shift_id' => $ds_id,
                        'checkin'       => $checkin,
                        'checkout'      => $checkout,
                        'time'          => $time,
                        'created_at'    => $record['created_at'],
                    ],
                    [ '%d', '%d', '%s', '%s', '%d', '%s' ]
                );
            }
        }
    }
}

WP_CLI::add_command( 'hr seed:attendance', __NAMESPACE__ . '\\SeedAttendance' );
