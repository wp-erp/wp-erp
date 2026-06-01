<?php

namespace WeDevs\ERP\CRM\CLI;

use WP_CLI;
use WP_CLI_Command;

/**
 * CRM CLI class
 */
class Commands extends WP_CLI_Command {
    public function delete( $args, $assoc_args ) {
        global $wpdb;

        // truncate table
        $tables = [ 'erp_peoples', 'erp_peoplemeta', 'erp_people_type_relations', 'erp_crm_customer_activities', 'erp_crm_contact_subscriber' ];

        if ( in_array( 'with-groups', $assoc_args ) ) {
            $tables[] = 'erp_crm_contact_group';
        }

        foreach ( $tables as $table ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        WP_CLI::success( 'Tables deleted successfully!' );
    }
}

WP_CLI::add_command( 'crm', 'WeDevs\ERP\CRM\CLI\Commands' );

// Load seed commands.
$seed_dir = __DIR__ . '/Seed';

if ( is_dir( $seed_dir ) ) {
    // Load base classes first.
    $base_files = [ 'AbstractCrmSeeder.php', 'CrmDataProvider.php' ];

    foreach ( $base_files as $file ) {
        $file_path = $seed_dir . '/' . $file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }

    // Load all Seed*.php files. SeedCommand.php loaded last so sub-commands register first.
    foreach ( glob( $seed_dir . '/Seed*.php' ) as $seed_file ) {
        if ( basename( $seed_file ) === 'SeedCommand.php' ) {
            continue;
        }
        require_once $seed_file;
    }

    $seed_command = $seed_dir . '/SeedCommand.php';
    if ( file_exists( $seed_command ) ) {
        require_once $seed_command;
    }
}
