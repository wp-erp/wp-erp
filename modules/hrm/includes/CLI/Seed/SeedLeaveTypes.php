<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;
use WeDevs\ERP\HRM\Models\Leave;

class SeedLeaveTypes extends AbstractSeeder {

    /**
     * Generate leave types.
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of leave types to create.
     * ---
     * default: 5
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:leave-types
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        $count       = (int) ( $assoc_args['count'] ?? 5 );
        $leave_types = DataProvider::leave_types();
        $count       = min( $count, count( $leave_types ) );

        $progress    = $this->progress( 'Creating leave types', $count );
        $created_ids = [];

        for ( $i = 0; $i < $count; $i++ ) {
            $lt = $leave_types[ $i ];

            $existing = Leave::where( 'name', $lt['name'] )->first();

            if ( $existing ) {
                $created_ids[] = $existing->id;
                $progress->tick();
                continue;
            }

            $leave = Leave::create( [
                'name'        => $lt['name'],
                'description' => $lt['description'],
            ] );

            $created_ids[] = $leave->id;
            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'leave_type_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d leave types.', count( $created_ids ) ) );
    }
}

WP_CLI::add_command( 'hr seed:leave-types', __NAMESPACE__ . '\\SeedLeaveTypes' );
