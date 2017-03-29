<?php
/**
 * Update ERP Table
 *
 * @since 1.1.17
 *
 * @return void
 */
function erp_crm_update_table_1_1_17() {
    global $wpdb;

    // Add hash column in `erp_crm_contact_subscriber` table
    $table = $wpdb->prefix . 'erp_crm_contact_subscriber';
    $cols  = $wpdb->get_col( "DESC $table");

    if ( ! in_array( 'hash', $cols ) ) {
        $wpdb->query( "ALTER TABLE $table ADD `hash` VARCHAR(40) NULL DEFAULT NULL AFTER `unsubscribe_at`;" );
    }
}

erp_crm_update_table_1_1_17();
