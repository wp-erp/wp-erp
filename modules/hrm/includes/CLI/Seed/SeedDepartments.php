<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedDepartments extends AbstractSeeder {

    /**
     * Generate departments.
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of departments to create.
     * ---
     * default: 8
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:departments
     *     wp hr seed:departments --count=5
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        $count       = (int) ( $assoc_args['count'] ?? 8 );
        $departments = DataProvider::departments();
        $count       = min( $count, count( $departments ) );

        $progress    = $this->progress( 'Creating departments', $count );
        $created_ids = [];

        for ( $i = 0; $i < $count; $i++ ) {
            $dept = $departments[ $i ];

            $result = erp_hr_create_department( [
                'title'       => $dept['title'],
                'description' => $dept['description'],
                'lead'        => 0,
                'parent'      => 0,
                'status'      => 1,
            ] );

            if ( ! is_wp_error( $result ) ) {
                $created_ids[] = $result;
            } else {
                WP_CLI::warning( "Failed to create department '{$dept['title']}': " . $result->get_error_message() );
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'department_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d departments.', count( $created_ids ) ) );
    }
}

WP_CLI::add_command( 'hr seed:departments', __NAMESPACE__ . '\\SeedDepartments' );
