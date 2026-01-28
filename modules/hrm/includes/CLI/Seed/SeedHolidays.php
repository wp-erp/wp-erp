<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedHolidays extends AbstractSeeder {

    /**
     * Generate holidays.
     *
     * ## OPTIONS
     *
     * [--years=<years>]
     * : Number of years to generate holidays for.
     * ---
     * default: 2
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:holidays
     *     wp hr seed:holidays --years=3
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        $years    = (int) ( $assoc_args['years'] ?? 2 );
        $holidays = DataProvider::holidays();

        $start_year = (int) date( 'Y' ) - $years + 1;
        $total      = count( $holidays ) * $years;
        $progress   = $this->progress( 'Creating holidays', $total );
        $created    = 0;

        for ( $y = 0; $y < $years; $y++ ) {
            $year = $start_year + $y;

            foreach ( $holidays as $holiday ) {
                $date = sprintf( '%d-%02d-%02d', $year, $holiday['month'], $holiday['day'] );

                $result = erp_hr_leave_insert_holiday( [
                    'title'       => $holiday['title'],
                    'start'       => $date,
                    'end'         => $date,
                    'description' => $holiday['title'] . ' ' . $year,
                ] );

                if ( ! is_wp_error( $result ) ) {
                    $created++;
                } else {
                    WP_CLI::warning( "Failed to create holiday '{$holiday['title']}' for {$year}: " . $result->get_error_message() );
                }

                $progress->tick();
            }
        }

        $progress->finish();

        WP_CLI::success( sprintf( 'Created %d holidays over %d years.', $created, $years ) );
    }
}

WP_CLI::add_command( 'hr seed:holidays', __NAMESPACE__ . '\\SeedHolidays' );
