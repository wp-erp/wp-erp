<?php

/**
 * Regenerate necessary tables for leave & holiday
 *
 * @return void
 */
function erp_generate_table_1_5_5() {
    global $wpdb;

    $collate = '';

    if ( defined('DB_COLLATE') )  {
        $collate = DB_COLLATE;
    }

    $table_schema = [
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_holidays_indv` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `holiday_id` int(11) DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `date` date DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) $collate;",
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_user_leaves` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(11) DEFAULT NULL,
            `request_id` int(11) DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `date` date DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) $collate;",
    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach ( $table_schema as $table ) {
        dbDelta( $table );
    }
}


/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_1_5_5() {
    erp_generate_table_1_5_5();
}

wperp_update_1_5_5();
