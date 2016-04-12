<?php

/**
 * Update CRM new roles and capabilities
 *
 * @since 1.0
 *
 * @return void
 */
function wperp_update_1_0_set_role() {
    remove_role( 'erp_hr_manager' );
    remove_role( 'employee' );
    remove_role( 'erp_crm_manager' );
    remove_role( 'erp_crm_agent' );

    $installer = new \WeDevs_ERP_Installer();
    $installer->create_roles();
}

/**
 * Create and update table schema
 *
 * @since 1.0
 *
 * @return [type] [description]
 */
function wperp_update_1_0_create_table() {
    global $wpdb;

    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty($wpdb->charset ) ) {
            $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }

        if ( ! empty($wpdb->collate ) ) {
            $collate .= " COLLATE $wpdb->collate";
        }
    }

    $schema = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_save_email_replies` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name` text,
              `subject` text,
              `template` longtext,
              PRIMARY KEY (`id`)
            ) $collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $schema );
}

wperp_update_1_0_set_role();
wperp_update_1_0_create_table();

// clear legacy crons
wp_clear_scheduled_hook( 'erp_hr_policy_schedule' );
wp_clear_scheduled_hook( 'erp_crm_notification_schedule' );

// setup new crons
wp_schedule_event( time(), 'per_minute', 'erp_per_minute_scheduled_events' );
wp_schedule_event( time(), 'daily', 'erp_daily_scheduled_events' );
wp_schedule_event( time(), 'weekly', 'erp_weekly_scheduled_events' );
