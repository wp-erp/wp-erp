<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

class SeedLeavePolicies extends AbstractSeeder {

    /**
     * Generate leave policies for each leave type and financial year.
     *
     * ## EXAMPLES
     *
     *     wp hr seed:leave-policies
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        $leave_type_ids    = $this->get_leave_type_ids();
        $financial_year_ids = $this->get_financial_year_ids();

        if ( empty( $leave_type_ids ) ) {
            WP_CLI::error( 'Leave types must be created first. Run seed:leave-types.' );
        }

        if ( empty( $financial_year_ids ) ) {
            WP_CLI::error( 'Financial years must be created first. Run seed:financial-years.' );
        }

        $leave_types = DataProvider::leave_types();
        $colors      = [ '#4CAF50', '#F44336', '#2196F3', '#E91E63', '#9C27B0' ];

        $total    = count( $leave_type_ids ) * count( $financial_year_ids );
        $progress = $this->progress( 'Creating leave policies', $total );
        $created_ids = [];

        foreach ( $financial_year_ids as $fy_id ) {
            foreach ( $leave_type_ids as $index => $leave_id ) {
                $lt    = isset( $leave_types[ $index ] ) ? $leave_types[ $index ] : $leave_types[0];
                $color = isset( $colors[ $index ] ) ? $colors[ $index ] : '#607D8B';

                $gender = '-1';

                if ( stripos( $lt['name'], 'Maternity' ) !== false ) {
                    $gender = 'female';
                } elseif ( stripos( $lt['name'], 'Paternity' ) !== false ) {
                    $gender = 'male';
                }

                $policy_args = [
                    'leave_id'            => $leave_id,
                    'description'         => $lt['description'],
                    'days'                => $lt['days'],
                    'color'               => $color,
                    'employee_type'       => '-1',
                    'department_id'       => '-1',
                    'designation_id'      => '-1',
                    'location_id'         => '-1',
                    'gender'              => $gender,
                    'marital'             => '-1',
                    'f_year'              => $fy_id,
                    'applicable_from'     => 0,
                    'apply_for_new_users' => 1,
                ];

                $result = erp_hr_leave_insert_policy( $policy_args );

                if ( is_wp_error( $result ) ) {
                    WP_CLI::warning( "Failed to create policy for leave {$leave_id}, FY {$fy_id}: " . $result->get_error_message() );
                } else {
                    $created_ids[] = $result;
                }

                $progress->tick();
            }
        }

        $progress->finish();

        $this->store_ids( 'leave_policy_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d leave policies.', count( $created_ids ) ) );
    }
}

WP_CLI::add_command( 'hr seed:leave-policies', __NAMESPACE__ . '\\SeedLeavePolicies' );
