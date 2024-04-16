<?php
/**
 * Update existing tables
 *
 * @since 1.1.1
 *
 * @return void
 */
function erp_ac_table_update_1_1_1() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_ac_transactions';
    $cols  = $wpdb->get_col( 'DESC ' . $table ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    if ( ! in_array( 'invoice_number', $cols ) ) {
        $wpdb->query( "ALTER TABLE $table ADD `invoice_number` varchar(200) NOT NULL AFTER `trans_total`" );
    }
}

/**
 * Update existing tables
 *
 * @since 1.1.1
 *
 * @return void
 */
function erp_crm_update_1_1_1_table_column() {
    global $wpdb;

    $activity_tb        = $wpdb->prefix . 'erp_crm_customer_activities';
    $activity_tb_col    = $wpdb->get_col( 'DESC ' . $activity_tb ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    if ( ! in_array( 'sent_notification', $activity_tb_col ) ) {
        $wpdb->query( "ALTER TABLE {$wpdb->prefix}erp_crm_customer_activities ADD `sent_notification` TINYINT(4) DEFAULT '0' AFTER `extra`" );
    }
}

erp_ac_table_update_1_1_1();
erp_crm_update_1_1_1_table_column();
