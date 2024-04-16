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

    $installer = new \WeDevsERPInstaller();
    $installer->create_roles();
}

/**
 * Create and update table schema
 *
 * @since 1.0
 *
 * @return void
 */
function wperp_update_1_0_create_table() {
    global $wpdb;

    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty( $wpdb->charset ) ) {
            $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }

        if ( ! empty( $wpdb->collate ) ) {
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

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $schema );
}

/**
 * Create erp_people_types table
 *
 * @since 1.0
 *
 * @return void
 */
function wperp_update_1_0_create_people_types_table() {
    global $wpdb;

    $collate = '';

    if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty( $wpdb->charset ) ) {
            $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }

        if ( ! empty( $wpdb->collate ) ) {
            $collate .= " COLLATE $wpdb->collate";
        }
    }

    $types_table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_people_types` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(20) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `name` (`name`)
            ) $collate;";

    $relations_table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_people_type_relations` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `people_id` bigint(20) unsigned DEFAULT NULL,
                `people_types_id` int(11) unsigned DEFAULT NULL,
                `deleted_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `people_id` (`people_id`),
                KEY `people_types_id` (`people_types_id`)
            ) $collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $types_table );
    dbDelta( $relations_table );

    // seed the types table
    $seed_types = 'INSERT INTO ' . $wpdb->prefix . "erp_people_types (name) VALUES ('contact'), ('company'), ('customer'), ('vendor');";
    $wpdb->query( $seed_types ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/**
 * Clear exisiting crons and setup new ones
 *
 * @return void
 */
function wperp_update_1_0_schedules() {
    // clear legacy crons
    wp_clear_scheduled_hook( 'erp_hr_policy_schedule' );
    wp_clear_scheduled_hook( 'erp_crm_notification_schedule' );

    // setup new crons
    wp_schedule_event( time(), 'per_minute', 'erp_per_minute_scheduled_events' );
    wp_schedule_event( time(), 'daily', 'erp_daily_scheduled_events' );
    wp_schedule_event( time(), 'weekly', 'erp_weekly_scheduled_events' );
}

/**
 * Populate the contact relations table with people type data
 *
 * @since 1.0
 *
 * @return void
 */
function wperp_update_1_0_populate_types_table() {
    global $wpdb;

    $query   = "SELECT * FROM {$wpdb->prefix}erp_peoples";
    $peoples = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    if ( ! $peoples ) {
        return;
    }

    // as we know the id's, don't create extra queries for the first migration
    $type_id_mapping = [
        'contact'  => 1,
        'company'  => 2,
        'customer' => 3,
        'vendor'   => 4,
    ];

    $table_name     = 'INSERT INTO ' . $wpdb->prefix . 'erp_people_type_relations (people_id, people_types_id, deleted_at ) VALUES';
    $insert_queries = [];

    foreach ( $peoples as $people ) {
        $insert_queries[] = $wpdb->prepare(
            "(%d, %d, %s)",
            $people->id,
            $type_id_mapping[ $people->type ],
            $people->deleted_at
        );
    }

    $insert_query = $table_name . ' ' . implode( ', ', $insert_queries );

    $wpdb->query( $insert_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/**
 * Drop the type column in people table
 *
 * @since 1.0
 *
 * @return void
 */
function wperp_update_1_0_drop_types_column() {
    global $wpdb;
    $wpdb->query( "ALTER TABLE {$wpdb->prefix}erp_peoples DROP COLUMN `type`, `deleted_at`" );
}

wperp_update_1_0_set_role();
wperp_update_1_0_schedules();

wperp_update_1_0_create_table();
wperp_update_1_0_create_people_types_table();
wperp_update_1_0_populate_types_table();
wperp_update_1_0_drop_types_column();
