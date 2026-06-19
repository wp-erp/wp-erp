<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedDesignations extends AbstractSeeder {

    /**
     * Generate designations.
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of designations to create.
     * ---
     * default: 15
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:designations
     *     wp hr seed:designations --count=10
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        $count        = (int) ( $assoc_args['count'] ?? 15 );
        $designations = DataProvider::designations();
        $count        = min( $count, count( $designations ) );

        $progress    = $this->progress( 'Creating designations', $count );
        $created_ids = [];

        for ( $i = 0; $i < $count; $i++ ) {
            $desig = $designations[ $i ];

            $result = erp_hr_create_designation( [
                'title'       => $desig['title'],
                'description' => $desig['description'],
                'status'      => 1,
            ] );

            if ( ! is_wp_error( $result ) ) {
                $created_ids[] = $result;
            } else {
                WP_CLI::warning( "Failed to create designation '{$desig['title']}': " . $result->get_error_message() );
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'designation_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d designations.', count( $created_ids ) ) );
    }
}

WP_CLI::add_command( 'hr seed:designations', __NAMESPACE__ . '\\SeedDesignations' );
