<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedLeaveApprovals extends AbstractSeeder {

    /**
     * Approve and reject leave requests.
     *
     * ## OPTIONS
     *
     * [--approve-ratio=<ratio>]
     * : Percentage of requests to approve (0-100).
     * ---
     * default: 80
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:leave-approvals
     *     wp hr seed:leave-approvals --approve-ratio=90
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();
        $this->suppress_emails();

        global $wpdb;

        $approve_ratio = (int) ( $assoc_args['approve-ratio'] ?? 80 );

        // Get all pending leave requests.
        $requests = $wpdb->get_results(
            "SELECT id FROM {$wpdb->prefix}erp_hr_leave_requests WHERE last_status = 2 ORDER BY id ASC"
        );

        if ( empty( $requests ) ) {
            WP_CLI::warning( 'No pending leave requests found.' );
            return;
        }

        $total    = count( $requests );
        $progress = $this->progress( 'Processing leave approvals', $total );

        $approved = 0;
        $rejected = 0;
        $pending  = 0;
        $errors   = 0;

        $reject_comments = [
            'Insufficient team coverage during requested period.',
            'Request conflicts with project deadline.',
            'Please reschedule to a different week.',
            'Team already has too many members on leave.',
            'Critical deliverables pending, please postpone.',
        ];

        foreach ( $requests as $index => $request ) {
            $rand = mt_rand( 1, 100 );

            if ( $rand <= $approve_ratio ) {
                // Approve.
                $result = erp_hr_leave_request_update_status( $request->id, 1 );

                if ( is_wp_error( $result ) ) {
                    $errors++;
                } else {
                    $approved++;
                }
            } elseif ( $rand <= $approve_ratio + 15 ) {
                // Reject.
                $comment = DataProvider::random_element( $reject_comments );
                $result  = erp_hr_leave_request_update_status( $request->id, 3, $comment );

                if ( is_wp_error( $result ) ) {
                    $errors++;
                } else {
                    $rejected++;
                }
            } else {
                // Leave as pending.
                $pending++;
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::success(
            sprintf(
                'Processed %d leave requests: %d approved, %d rejected, %d left pending, %d errors.',
                $total, $approved, $rejected, $pending, $errors
            )
        );
    }
}

WP_CLI::add_command( 'hr seed:leave-approvals', __NAMESPACE__ . '\\SeedLeaveApprovals' );
