<?php
/**
 * Update ERP Transaction Table
 *
 * @since 1.2.1-rc.1
 *
 * @return void
 */
function erp_accounting_update_table_1_2_1_rc_1() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_crm_contact_group';
    $cols  = $wpdb->get_col( "DESC $table");
    if ( ! in_array( 'private', $cols ) ) {
        $wpdb->query( "ALTER TABLE $table ADD `private` TINYINT(1) NOT NULL DEFAULT '0' AFTER `description`;" );
    }
}

erp_accounting_update_table_1_2_1_rc_1();
