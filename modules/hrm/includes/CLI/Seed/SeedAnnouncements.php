<?php

namespace WeDevs\ERP\HRM\CLI\Seed;

use WP_CLI;

/**
 * Seed announcements
 */
class SeedAnnouncements extends AbstractSeeder {

    /**
     * Seed announcements
     *
     * ## OPTIONS
     *
     * [--count=<number>]
     * : Number of announcements to create
     * ---
     * default: 12
     * ---
     *
     * ## EXAMPLES
     *
     *     wp hr seed:announcements --count=12
     *
     * @param array $args       Positional arguments
     * @param array $assoc_args Named arguments
     *
     * @return void
     */
    public function __invoke( $args, $assoc_args ) {
        $count = isset( $assoc_args['count'] ) ? absint( $assoc_args['count'] ) : 12;

        $this->ensure_admin();
        $this->suppress_emails();

        // Get dependencies
        $employee_ids = $this->get_ids( 'employees' );
        $dept_ids     = $this->get_ids( 'departments' );
        $desig_ids    = $this->get_ids( 'designations' );

        if ( empty( $employee_ids ) ) {
            // Fallback: query employees
            global $wpdb;
            $employee_ids = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active'" );
        }

        if ( empty( $dept_ids ) ) {
            // Fallback: query departments
            global $wpdb;
            $dept_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_hr_depts" );
        }

        if ( empty( $desig_ids ) ) {
            // Fallback: query designations
            global $wpdb;
            $desig_ids = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}erp_hr_designations" );
        }

        if ( empty( $employee_ids ) ) {
            WP_CLI::error( 'No employees found. Run `wp hr seed:employees` first.' );
        }

        $templates = DataProvider::announcement_templates();
        $progress  = $this->progress( 'Creating announcements', $count );

        $created_ids = [];
        $types       = [ 'all_employee', 'by_department', 'by_designation' ];

        for ( $i = 0; $i < $count; $i++ ) {
            $template = DataProvider::random_element( $templates );
            $type     = $types[ $i % 3 ];

            // Generate date within the past 2 years
            $post_date = DataProvider::random_date_between(
                date( 'Y-m-d', strtotime( '-2 years' ) ),
                date( 'Y-m-d' )
            );

            $post_data = [
                'post_type'    => 'erp_hr_announcement',
                'post_title'   => $template['title'],
                'post_content' => $template['body'],
                'post_status'  => 'publish',
                'post_date'    => $post_date . ' 09:00:00',
                'post_author'  => 1,
            ];

            $post_id = wp_insert_post( $post_data );

            if ( is_wp_error( $post_id ) ) {
                WP_CLI::warning( "Failed to create announcement: {$template['title']}" );
                continue;
            }

            // Assign to recipients based on type
            $selected = [];

            switch ( $type ) {
                case 'all_employee':
                    $selected = []; // Empty means all employees
                    break;

                case 'by_department':
                    if ( ! empty( $dept_ids ) ) {
                        // Pick 1-3 random departments
                        $num_depts = rand( 1, min( 3, count( $dept_ids ) ) );
                        $selected  = array_rand( array_flip( $dept_ids ), $num_depts );
                        if ( ! is_array( $selected ) ) {
                            $selected = [ $selected ];
                        }
                    }
                    break;

                case 'by_designation':
                    if ( ! empty( $desig_ids ) ) {
                        // Pick 1-3 random designations
                        $num_desigs = rand( 1, min( 3, count( $desig_ids ) ) );
                        $selected   = array_rand( array_flip( $desig_ids ), $num_desigs );
                        if ( ! is_array( $selected ) ) {
                            $selected = [ $selected ];
                        }
                    }
                    break;
            }

            // Use ERP function to assign announcements
            if ( function_exists( 'erp_hr_assign_announcements_to_employees' ) ) {
                erp_hr_assign_announcements_to_employees( $post_id, $type, $selected );
            }

            $created_ids[] = $post_id;
            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'announcements', $created_ids );

        WP_CLI::success( sprintf( 'Created %d announcements.', count( $created_ids ) ) );
    }
}

WP_CLI::add_command( 'hr seed:announcements', SeedAnnouncements::class );
