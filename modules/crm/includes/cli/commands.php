<?php
namespace WeDevs\ERP\CRM\CLI;

/**
 * CRM CLI class
 */
class Commands extends \WP_CLI_Command {

    public function delete( $args, $assoc_args ) {
        global $wpdb;

        // truncate table
        $tables = [ 'erp_peoples', 'erp_peoplemeta', 'erp_people_type_relations', 'erp_crm_customer_activities', 'erp_crm_contact_subscriber' ];

        if ( in_array( 'with-groups', $assoc_args ) ) {
            $tables[] = 'erp_crm_contact_group';
        }

        foreach ( $tables as $table ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table );
        }

        \WP_CLI::success( "Tables deleted successfully!" );
    }

}

\WP_CLI::add_command( 'crm', 'WeDevs\ERP\CRM\CLI\Commands' );
