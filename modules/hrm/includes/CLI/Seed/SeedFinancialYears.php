<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedFinancialYears extends AbstractSeeder {

    /**
     * Generate financial years.
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of financial years to create.
     * ---
     * default: 3
     * ---
     *
     * [--start-year=<year>]
     * : Starting year.
     * ---
     * default: 0
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:financial-years
     *     wp hr seed:financial-years --count=3 --start-year=2024
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        $count      = (int) ( $assoc_args['count'] ?? 3 );
        $start_year = (int) ( $assoc_args['start-year'] ?? 0 );

        if ( $start_year === 0 ) {
            $start_year = (int) date( 'Y' ) - 2;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'erp_hr_financial_years';

        $progress   = $this->progress( 'Creating financial years', $count );
        $created_ids = [];

        for ( $i = 0; $i < $count; $i++ ) {
            $year       = $start_year + $i;
            $fy_name    = "FY {$year}";
            $start_date = strtotime( "{$year}-01-01" );
            $end_date   = strtotime( "{$year}-12-31 23:59:59" );

            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$table} WHERE fy_name = %s",
                    $fy_name
                )
            );

            if ( $existing ) {
                $created_ids[] = $existing;
                $progress->tick();
                continue;
            }

            $wpdb->insert(
                $table,
                [
                    'fy_name'     => $fy_name,
                    'start_date'  => $start_date,
                    'end_date'    => $end_date,
                    'description' => "Financial Year {$year}",
                    'created_by'  => get_current_user_id(),
                    'updated_by'  => get_current_user_id(),
                ],
                [ '%s', '%d', '%d', '%s', '%d', '%d' ]
            );

            $created_ids[] = $wpdb->insert_id;
            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'financial_year_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d financial years.', count( $created_ids ) ) );
    }
}

WP_CLI::add_command( 'hr seed:financial-years', __NAMESPACE__ . '\\SeedFinancialYears' );
