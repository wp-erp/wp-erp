<?php

namespace WeDevs\ERP\CRM\CLI\Seed;

use WP_CLI;

class SeedContactGroups extends AbstractCrmSeeder {

    /**
     * Generate contact groups/segments.
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of contact groups to create.
     * ---
     * default: 8
     * ---
     *
     * ## EXAMPLES
     *
     *     wp crm seed:contact-groups
     *     wp crm seed:contact-groups --count=10
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function __invoke( $args, $assoc_args ) {
        $this->ensure_admin();

        global $wpdb;

        $count  = (int) ( $assoc_args['count'] ?? 8 );
        $groups = CrmDataProvider::contact_groups();

        $progress    = $this->progress( 'Creating contact groups', min( $count, count( $groups ) ) );
        $created_ids = [];

        for ( $i = 0; $i < $count && $i < count( $groups ); $i++ ) {
            $group = $groups[ $i ];

            // Check if group already exists.
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}erp_crm_contact_group WHERE name = %s",
                    $group['name']
                )
            );

            if ( $existing ) {
                $created_ids[] = (int) $existing;
                $progress->tick();
                continue;
            }

            $result = $wpdb->insert(
                $wpdb->prefix . 'erp_crm_contact_group',
                [
                    'name'        => $group['name'],
                    'description' => $group['description'],
                    'private'     => 0,
                    'created_at'  => current_time( 'mysql' ),
                    'updated_at'  => current_time( 'mysql' ),
                ],
                [ '%s', '%s', '%d', '%s', '%s' ]
            );

            if ( $result ) {
                $created_ids[] = $wpdb->insert_id;
            }

            $progress->tick();
        }

        $progress->finish();

        $this->store_ids( 'contact_group_ids', $created_ids );

        WP_CLI::success( sprintf( 'Created %d contact groups.', count( $created_ids ) ) );
    }
}

WP_CLI::add_command( 'crm seed:contact-groups', __NAMESPACE__ . '\\SeedContactGroups' );
