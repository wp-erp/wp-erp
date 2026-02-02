<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

/**
 * Master seed command orchestrator
 */
class SeedCommand extends AbstractSeeder {

    /**
     * Seed all HRM data
     *
     * ## OPTIONS
     *
     * [--skip=<steps>]
     * : Comma-separated list of steps to skip
     *
     * [--only=<steps>]
     * : Comma-separated list of steps to run (skips all others)
     *
     * [--employees=<number>]
     * : Number of employees to create
     * ---
     * default: 50
     * ---
     *
     * [--clean-first]
     * : Run clean command before seeding
     *
     * ## EXAMPLES
     *
     *     # Seed all data
     *     wp hr seed
     *
     *     # Seed with 100 employees
     *     wp hr seed --employees=100
     *
     *     # Skip pro features
     *     wp hr seed --skip=shifts,attendance,training,assets,payroll
     *
     *     # Only seed basic structure
     *     wp hr seed --only=financial-years,departments,designations,employees
     *
     *     # Clean and seed
     *     wp hr seed --clean-first
     *
     * @param array $args       Positional arguments
     * @param array $assoc_args Named arguments
     *
     * @return void
     */
    public function __invoke( $args, $assoc_args ) {
        $skip       = isset( $assoc_args['skip'] ) ? explode( ',', $assoc_args['skip'] ) : [];
        $only       = isset( $assoc_args['only'] ) ? explode( ',', $assoc_args['only'] ) : [];
        $employees  = isset( $assoc_args['employees'] ) ? absint( $assoc_args['employees'] ) : 20;
        $clean_first = isset( $assoc_args['clean-first'] );

        $this->ensure_admin();
        $this->suppress_emails();

        // Clean first if requested
        if ( $clean_first ) {
            WP_CLI::line( '' );
            WP_CLI::line( WP_CLI::colorize( '%G=== Cleaning existing data ===%n' ) );
            WP_CLI::runcommand( 'hr clean', [ 'launch' => false ] );
        }

        // Define all seed steps in execution order
        $steps = [
            'financial-years' => [
                'title'       => 'Financial Years',
                'command'     => 'hr seed:financial-years',
                'args'        => '--count=3 --start-year=2024',
                'description' => 'Creates 3 financial years (2024-2026)',
            ],
            'departments' => [
                'title'       => 'Departments',
                'command'     => 'hr seed:departments',
                'args'        => '--count=8',
                'description' => 'Creates 8 departments',
            ],
            'designations' => [
                'title'       => 'Designations',
                'command'     => 'hr seed:designations',
                'args'        => '--count=15',
                'description' => 'Creates 15 designations',
            ],
            'employees' => [
                'title'       => 'Employees',
                'command'     => 'hr seed:employees',
                'args'        => "--count={$employees}",
                'description' => "Creates {$employees} employees",
            ],
            'leave-types' => [
                'title'       => 'Leave Types',
                'command'     => 'hr seed:leave-types',
                'args'        => '--count=5',
                'description' => 'Creates 5 leave types',
            ],
            'leave-policies' => [
                'title'       => 'Leave Policies',
                'command'     => 'hr seed:leave-policies',
                'args'        => '',
                'description' => 'Creates ~15 leave policies',
            ],
            'leave-entitlements' => [
                'title'       => 'Leave Entitlements',
                'command'     => 'hr seed:leave-entitlements',
                'args'        => '',
                'description' => "Creates ~{$this->calculate_entitlements($employees)} leave entitlements",
            ],
            'leave-requests' => [
                'title'       => 'Leave Requests',
                'command'     => 'hr seed:leave-requests',
                'args'        => '--count=100',
                'description' => 'Creates 100 leave requests',
            ],
            'leave-approvals' => [
                'title'       => 'Leave Approvals',
                'command'     => 'hr seed:leave-approvals',
                'args'        => '--approve-ratio=80',
                'description' => 'Approves ~80% of leave requests',
            ],
            'holidays' => [
                'title'       => 'Holidays',
                'command'     => 'hr seed:holidays',
                'args'        => '--years=2',
                'description' => 'Creates ~30 holidays over 2 years',
            ],
            'shifts' => [
                'title'       => 'Shifts (Pro)',
                'command'     => 'hr seed:shifts',
                'args'        => '',
                'description' => 'Creates 3 shifts + assignments',
                'pro'         => true,
            ],
            'attendance' => [
                'title'       => 'Attendance (Pro)',
                'command'     => 'hr seed:attendance',
                'args'        => '--months=6',
                'description' => 'Creates ~' . ( 6 * $employees * 22 ) . ' attendance records',
                'pro'         => true,
            ],
            'training' => [
                'title'       => 'Training (Pro)',
                'command'     => 'hr seed:training',
                'args'        => '--count=10',
                'description' => 'Creates 10 training programs',
                'pro'         => true,
            ],
            'assets' => [
                'title'       => 'Assets (Pro)',
                'command'     => 'hr seed:assets',
                'args'        => '--count=30',
                'description' => 'Creates 5 categories + 30 assets',
                'pro'         => true,
            ],
            'payroll' => [
                'title'       => 'Payroll (Pro)',
                'command'     => 'hr seed:payroll',
                'args'        => '--months=6',
                'description' => 'Creates 1 pay calendar + 6 monthly payruns',
                'pro'         => true,
            ],
            'announcements' => [
                'title'       => 'Announcements',
                'command'     => 'hr seed:announcements',
                'args'        => '--count=12',
                'description' => 'Creates 12 announcements',
            ],
        ];

        // Filter steps based on --only or --skip
        if ( ! empty( $only ) ) {
            $steps = array_filter( $steps, function( $key ) use ( $only ) {
                return in_array( $key, $only, true );
            }, ARRAY_FILTER_USE_KEY );
        } elseif ( ! empty( $skip ) ) {
            $steps = array_filter( $steps, function( $key ) use ( $skip ) {
                return ! in_array( $key, $skip, true );
            }, ARRAY_FILTER_USE_KEY );
        }

        // Display execution plan
        WP_CLI::line( '' );
        WP_CLI::line( WP_CLI::colorize( '%G=== Seed Execution Plan ===%n' ) );
        WP_CLI::line( '' );

        foreach ( $steps as $key => $step ) {
            $icon = isset( $step['pro'] ) && $step['pro'] ? '💎' : '✓';
            WP_CLI::line( sprintf( '  %s %s: %s', $icon, $step['title'], $step['description'] ) );
        }

        WP_CLI::line( '' );
        WP_CLI::confirm( 'Proceed with seeding?' );

        // Execute steps
        $start_time = time();
        $completed  = 0;
        $failed     = 0;

        WP_CLI::line( '' );
        WP_CLI::line( WP_CLI::colorize( '%G=== Starting Seed Process ===%n' ) );

        foreach ( $steps as $key => $step ) {
            WP_CLI::line( '' );
            WP_CLI::line( WP_CLI::colorize( "%Y[{$step['title']}]%n" ) );

            $command = trim( "{$step['command']} {$step['args']}" );

            try {
                WP_CLI::runcommand( $command, [ 'launch' => false, 'exit_error' => false ] );
                $completed++;
            } catch ( \Exception $e ) {
                WP_CLI::warning( "Failed to execute: {$step['title']}" );
                WP_CLI::line( $e->getMessage() );
                $failed++;
            }
        }

        // Summary
        $duration = time() - $start_time;

        WP_CLI::line( '' );
        WP_CLI::line( WP_CLI::colorize( '%G=== Seed Summary ===%n' ) );
        WP_CLI::line( '' );
        WP_CLI::line( sprintf( '  Completed: %d', $completed ) );
        if ( $failed > 0 ) {
            WP_CLI::line( WP_CLI::colorize( sprintf( '  %%RFailed: %d%%n', $failed ) ) );
        }
        WP_CLI::line( sprintf( '  Duration: %s', $this->format_duration( $duration ) ) );
        WP_CLI::line( '' );

        if ( $completed > 0 && $failed === 0 ) {
            WP_CLI::success( 'All seed operations completed successfully!' );
        } elseif ( $completed > 0 ) {
            WP_CLI::warning( 'Seed completed with some failures.' );
        } else {
            WP_CLI::error( 'Seed failed.' );
        }
    }

    /**
     * Calculate expected entitlements count
     *
     * @param int $employees Number of employees
     *
     * @return int
     */
    private function calculate_entitlements( $employees ) {
        // ~90% active employees * 3 common leave types * 2 years
        $active = (int) ( $employees * 0.9 );
        return $active * 3 * 2;
    }

    /**
     * Format duration in human readable format
     *
     * @param int $seconds Duration in seconds
     *
     * @return string
     */
    private function format_duration( $seconds ) {
        if ( $seconds < 60 ) {
            return $seconds . 's';
        }

        $minutes = floor( $seconds / 60 );
        $seconds = $seconds % 60;

        if ( $minutes < 60 ) {
            return sprintf( '%dm %ds', $minutes, $seconds );
        }

        $hours   = floor( $minutes / 60 );
        $minutes = $minutes % 60;

        return sprintf( '%dh %dm %ds', $hours, $minutes, $seconds );
    }
}

WP_CLI::add_command( 'hr seed', SeedCommand::class );
