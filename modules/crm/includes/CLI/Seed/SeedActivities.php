<?php

namespace WeDevs\ERP\CRM\CLI\Seed;

use WP_CLI;

class SeedActivities extends AbstractCrmSeeder {

    /**
     * Generate CRM activities (notes, calls, meetings, tasks, emails).
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of activities to create.
     * ---
     * default: 100
     * ---
     *
     * ## EXAMPLES
     *
     *     wp crm seed:activities
     *     wp crm seed:activities --count=200
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        $count       = (int) ( $assoc_args['count'] ?? 100 );
        $contact_ids = $this->get_contact_ids();
        $company_ids = $this->get_company_ids();

        if ( empty( $contact_ids ) && empty( $company_ids ) ) {
            WP_CLI::error( 'No contacts or companies found. Run seed:contacts or seed:companies first.' );
        }

        // Merge contacts and companies for activity assignment.
        $all_people_ids = array_merge( $contact_ids, $company_ids );

        $notes           = CrmDataProvider::notes();
        $meeting_subjects = CrmDataProvider::meeting_subjects();
        $call_subjects   = CrmDataProvider::call_subjects();
        $task_titles     = CrmDataProvider::task_titles();
        $email_subjects  = CrmDataProvider::email_subjects();

        $progress    = $this->progress( 'Creating activities', $count );
        $created     = [
            'notes'    => 0,
            'calls'    => 0,
            'meetings' => 0,
            'tasks'    => 0,
            'emails'   => 0,
        ];
        $fail_count  = 0;

        // Activity type distribution.
        $activity_types = [
            'note'    => 25,
            'call'    => 20,
            'meeting' => 20,
            'task'    => 20,
            'email'   => 15,
        ];

        for ( $i = 0; $i < $count; $i++ ) {
            $people_id = $this->random_element( $all_people_ids );
            $type      = $this->weighted_random( $activity_types );

            $result = false;

            switch ( $type ) {
                case 'note':
                    $result = $this->create_note( $people_id, $notes );
                    if ( $result ) {
                        $created['notes']++;
                    }
                    break;

                case 'call':
                    $result = $this->create_call( $people_id, $call_subjects );
                    if ( $result ) {
                        $created['calls']++;
                    }
                    break;

                case 'meeting':
                    $result = $this->create_meeting( $people_id, $meeting_subjects );
                    if ( $result ) {
                        $created['meetings']++;
                    }
                    break;

                case 'task':
                    $result = $this->create_task( $people_id, $task_titles );
                    if ( $result ) {
                        $created['tasks']++;
                    }
                    break;

                case 'email':
                    $result = $this->create_email_activity( $people_id, $email_subjects );
                    if ( $result ) {
                        $created['emails']++;
                    }
                    break;
            }

            if ( ! $result ) {
                $fail_count++;
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::success( sprintf(
            'Created %d notes, %d calls, %d meetings, %d tasks, %d emails (%d failed).',
            $created['notes'],
            $created['calls'],
            $created['meetings'],
            $created['tasks'],
            $created['emails'],
            $fail_count
        ) );
    }

    /**
     * Create a note activity.
     *
     * @param int   $people_id
     * @param array $notes
     * @return bool
     */
    private function create_note( $people_id, $notes ) {
        $created_at = $this->random_datetime_between(
            date( 'Y-m-d', strtotime( '-2 years' ) ),
            date( 'Y-m-d' )
        );

        $data = [
            'user_id'    => $people_id,
            'created_by' => 1,
            'type'       => 'new_note',
            'message'    => $this->random_element( $notes ),
            'created_at' => $created_at,
        ];

        $result = erp_crm_save_customer_feed_data( $data );

        return ! empty( $result['id'] );
    }

    /**
     * Create a call activity.
     *
     * @param int   $people_id
     * @param array $subjects
     * @return bool
     */
    private function create_call( $people_id, $subjects ) {
        $start_date = $this->random_datetime_between(
            date( 'Y-m-d', strtotime( '-2 years' ) ),
            date( 'Y-m-d' )
        );

        $duration = mt_rand( 5, 60 );
        $end_date = date( 'Y-m-d H:i:s', strtotime( $start_date . " +{$duration} minutes" ) );

        $extra = base64_encode( wp_json_encode( [
            'schedule_title'     => $this->random_element( $subjects ),
            'all_day'            => 'false',
            'allow_notification' => 'false',
            'invite_contact'     => [],
        ] ) );

        $data = [
            'user_id'    => $people_id,
            'created_by' => 1,
            'type'       => 'log_activity',
            'log_type'   => 'call',
            'message'    => 'Call regarding ' . strtolower( $this->random_element( $subjects ) ),
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'extra'      => $extra,
            'created_at' => $start_date,
        ];

        $result = erp_crm_save_customer_feed_data( $data );

        return ! empty( $result['id'] );
    }

    /**
     * Create a meeting activity.
     *
     * @param int   $people_id
     * @param array $subjects
     * @return bool
     */
    private function create_meeting( $people_id, $subjects ) {
        $is_future  = mt_rand( 0, 1 );
        $start_date = $is_future
            ? $this->random_datetime_between( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( '+3 months' ) ) )
            : $this->random_datetime_between( date( 'Y-m-d', strtotime( '-2 years' ) ), date( 'Y-m-d' ) );

        $duration = mt_rand( 30, 120 );
        $end_date = date( 'Y-m-d H:i:s', strtotime( $start_date . " +{$duration} minutes" ) );

        $subject = $this->random_element( $subjects );

        $extra = base64_encode( wp_json_encode( [
            'schedule_title'            => $subject,
            'all_day'                   => 'false',
            'allow_notification'        => mt_rand( 0, 1 ) ? 'true' : 'false',
            'invite_contact'            => [],
            'notification_via'          => 'email',
            'notification_time'         => 15,
            'notification_time_interval'=> 'minutes',
        ] ) );

        $data = [
            'user_id'    => $people_id,
            'created_by' => 1,
            'type'       => 'log_activity',
            'log_type'   => 'meeting',
            'message'    => $subject,
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'extra'      => $extra,
            'created_at' => $is_future ? current_time( 'mysql' ) : $start_date,
        ];

        $result = erp_crm_save_customer_feed_data( $data );

        return ! empty( $result['id'] );
    }

    /**
     * Create a task activity.
     *
     * @param int   $people_id
     * @param array $titles
     * @return bool
     */
    private function create_task( $people_id, $titles ) {
        global $wpdb;

        $is_future  = mt_rand( 0, 1 );
        $start_date = $is_future
            ? $this->random_datetime_between( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( '+1 month' ) ) )
            : $this->random_datetime_between( date( 'Y-m-d', strtotime( '-2 years' ) ), date( 'Y-m-d' ) );

        $title   = $this->random_element( $titles );
        $is_done = ! $is_future && mt_rand( 0, 1 );

        $extra = base64_encode( wp_json_encode( [
            'task_title'         => $title,
            'all_day'            => 'false',
            'allow_notification' => mt_rand( 0, 1 ) ? 'true' : 'false',
            'invite_contact'     => [],
        ] ) );

        $data = [
            'user_id'    => $people_id,
            'created_by' => 1,
            'type'       => 'tasks',
            'message'    => $title,
            'start_date' => $start_date,
            'extra'      => $extra,
            'created_at' => $is_future ? current_time( 'mysql' ) : $start_date,
        ];

        $result = erp_crm_save_customer_feed_data( $data );

        if ( ! empty( $result['id'] ) ) {
            // Assign task to admin user.
            $wpdb->insert(
                $wpdb->prefix . 'erp_crm_activities_task',
                [
                    'activity_id' => $result['id'],
                    'user_id'     => 1,
                ],
                [ '%d', '%d' ]
            );

            // Mark as done if it's a past task and randomly selected.
            if ( $is_done ) {
                $wpdb->update(
                    $wpdb->prefix . 'erp_crm_customer_activities',
                    [ 'done_at' => date( 'Y-m-d H:i:s', strtotime( $start_date . ' +1 day' ) ) ],
                    [ 'id' => $result['id'] ],
                    [ '%s' ],
                    [ '%d' ]
                );
            }

            return true;
        }

        return false;
    }

    /**
     * Create an email activity (logged email, not actually sent).
     *
     * @param int   $people_id
     * @param array $subjects
     * @return bool
     */
    private function create_email_activity( $people_id, $subjects ) {
        $created_at = $this->random_datetime_between(
            date( 'Y-m-d', strtotime( '-2 years' ) ),
            date( 'Y-m-d' )
        );

        $subject = $this->random_element( $subjects );

        $extra = base64_encode( wp_json_encode( [
            'replied' => 1,
        ] ) );

        $data = [
            'user_id'       => $people_id,
            'created_by'    => 1,
            'type'          => 'email',
            'email_subject' => $subject,
            'message'       => "Dear Customer,\n\nThank you for your interest in our services.\n\nBest regards,\nThe Team",
            'extra'         => $extra,
            'created_at'    => $created_at,
        ];

        $result = erp_crm_save_customer_feed_data( $data );

        return ! empty( $result['id'] );
    }

    /**
     * Get a weighted random selection.
     *
     * @param array $weights Associative array of item => weight
     * @return string
     */
    private function weighted_random( $weights ) {
        $total   = array_sum( $weights );
        $random  = mt_rand( 1, $total );
        $current = 0;

        foreach ( $weights as $item => $weight ) {
            $current += $weight;
            if ( $random <= $current ) {
                return $item;
            }
        }

        return array_key_first( $weights );
    }
}

WP_CLI::add_command( 'crm seed:activities', __NAMESPACE__ . '\\SeedActivities' );
