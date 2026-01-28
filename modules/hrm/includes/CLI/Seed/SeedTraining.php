<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedTraining extends AbstractSeeder {

    /**
     * Generate training programs and assign employees (Pro feature).
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of training programs to create.
     * ---
     * default: 10
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:training
     *     wp hr seed:training --count=5
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        if ( ! post_type_exists( 'erp_hr_training' ) ) {
            WP_CLI::warning( 'Training module not active (post type erp_hr_training not found). Skipping.' );
            return;
        }

        $count        = (int) ( $assoc_args['count'] ?? 10 );
        $topics       = DataProvider::training_topics();
        $count        = min( $count, count( $topics ) );
        $employee_ids = $this->get_employee_user_ids();

        if ( empty( $employee_ids ) ) {
            WP_CLI::error( 'Employees must be created first.' );
        }

        $progress = $this->progress( 'Creating training programs', $count );
        $created  = 0;

        $trainers = [ 'Dr. Sarah Mitchell', 'Prof. James Wilson', 'Maria Garcia', 'Robert Chen', 'Lisa Thompson' ];
        $locations = [ 'Conference Room A', 'Training Center', 'Virtual - Zoom', 'Auditorium', 'Meeting Room 3' ];

        for ( $i = 0; $i < $count; $i++ ) {
            $topic = $topics[ $i ];

            // Spread training dates over 2 years.
            $start = DataProvider::random_date_between(
                date( 'Y-m-d', strtotime( '-2 years' ) ),
                date( 'Y-m-d', strtotime( '-1 month' ) )
            );

            $duration_days = mt_rand( 1, 5 );
            $end           = date( 'Y-m-d', strtotime( $start . " +{$duration_days} days" ) );

            $post_id = wp_insert_post( [
                'post_title'   => $topic['title'],
                'post_content' => $topic['description'],
                'post_type'    => 'erp_hr_training',
                'post_status'  => 'publish',
                'post_date'    => $start . ' 09:00:00',
            ] );

            if ( is_wp_error( $post_id ) ) {
                WP_CLI::warning( "Failed to create training '{$topic['title']}': " . $post_id->get_error_message() );
                $progress->tick();
                continue;
            }

            $trainer  = DataProvider::random_element( $trainers );
            $location = DataProvider::random_element( $locations );
            $cost     = mt_rand( 500, 5000 );
            $hours    = $duration_days * 8;

            // Add training meta.
            update_post_meta( $post_id, '_training_start_date', $start );
            update_post_meta( $post_id, '_training_end_date', $end );
            update_post_meta( $post_id, '_training_type', 'classroom' );
            update_post_meta( $post_id, '_training_status', ( strtotime( $end ) < time() ) ? 'completed' : 'scheduled' );

            // Assign 3-8 random employees.
            $assign_count      = mt_rand( 3, min( 8, count( $employee_ids ) ) );
            $assigned_keys     = array_rand( $employee_ids, $assign_count );

            if ( ! is_array( $assigned_keys ) ) {
                $assigned_keys = [ $assigned_keys ];
            }

            $completed_emps   = [];
            $incompleted_emps = [];

            foreach ( $assigned_keys as $key ) {
                $emp_id  = $employee_ids[ $key ];
                $is_past = ( strtotime( $end ) < time() );

                // 80% completion rate for past trainings.
                $completed = $is_past && ( mt_rand( 1, 100 ) <= 80 );

                $training_data = [
                    'erp_training_completed_date' => $completed ? $end : '',
                    'erp_training_trainer'        => $trainer,
                    'erp_trainer_phone'           => sprintf( '555%07d', mt_rand( 1000000, 9999999 ) ),
                    'erp_training_cost'           => $cost,
                    'erp_training_credit'         => mt_rand( 1, 4 ),
                    'erp_training_hours'          => $hours,
                    'erp_training_note'           => '',
                    'erp_training_rate'           => round( $cost / $hours, 2 ),
                    'completed'                   => $completed ? 'yes' : 'no',
                ];

                $existing = get_user_meta( $emp_id, 'erp_employee_training', true );

                if ( ! is_array( $existing ) ) {
                    $existing = [];
                }

                $existing[ $post_id ] = $training_data;
                update_user_meta( $emp_id, 'erp_employee_training', $existing );

                if ( $completed ) {
                    $completed_emps[] = $emp_id;
                } else {
                    $incompleted_emps[] = $emp_id;
                }
            }

            update_post_meta( $post_id, 'erp_training_completed_employee', $completed_emps );
            update_post_meta( $post_id, 'erp_training_incompleted_employee', $incompleted_emps );

            $created++;
            $progress->tick();
        }

        $progress->finish();

        WP_CLI::success( sprintf( 'Created %d training programs.', $created ) );
    }
}

WP_CLI::add_command( 'hr seed:training', __NAMESPACE__ . '\\SeedTraining' );
