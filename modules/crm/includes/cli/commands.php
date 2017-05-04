<?php
namespace WeDevs\ERP\CRM\CLI;

/**
 * CRM CLI class
 */
class Commands extends \WP_CLI_Command {

    public function delete() {
        global $wpdb;

        // truncate table
        $tables = [ 'erp_peoples', 'erp_peoplemeta', 'erp_people_type_relations', 'erp_crm_customer_activities', 'erp_crm_contact_subscriber' ];
        foreach ($tables as $table) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $table);
        }

        \WP_CLI::success( "Table deleted successfully!" );
    }

}

\WP_CLI::add_command( 'crm', 'WeDevs\ERP\CRM\CLI\Commands' );
