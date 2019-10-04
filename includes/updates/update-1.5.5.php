<?php

/**
 * Update DB charset & collate
 *
 * @return void
 */
function erp_updater_db_collate() {
    global $wpdb;

    $tables = $wpdb->get_results(
        "SELECT table_name FROM information_schema.tables where table_name like '{$wpdb->prefix}erp_%'",
        ARRAY_A
    );

    foreach( $tables as $table ) {
        $wpdb->query("ALTER TABLE {$table['table_name']}
            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }
}

/**
 * Regenerate necessary tables for leave & holiday
 *
 * @return void
 */
function erp_updater_generate_holiday_leave_tables() {
    global $wpdb;

    $charset = 'CHARSET=utf8mb4';
    $collate = 'COLLATE=utf8mb4_unicode_ci';

    if ( defined('DB_COLLATE') && DB_COLLATE )  {
        $charset = DB_CHARSET;
        $collate = DB_COLLATE;
    }

    $charset_collate = $charset . ' ' . $collate;

    $table_schema = [
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_holidays_indv` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `holiday_id` int(11) DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `date` date DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_user_leaves` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(11) DEFAULT NULL,
            `request_id` int(11) DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `date` date DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",
    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach ( $table_schema as $table ) {
        dbDelta( $table );
    }
}

/**
 * Update estimate-order status
 *
 * @return void
 */
function erp_acct_updater_estimate_order_status() {
    global $wpdb;

    $wpdb->query("UPDATE {$wpdb->prefix}erp_acct_invoices SET status = 3 WHERE estimate = 1");
    $wpdb->query("UPDATE {$wpdb->prefix}erp_acct_purchase SET status = 3 WHERE purchase_order = 1");
}

/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_1_5_5() {
    erp_updater_db_collate();
    erp_updater_generate_holiday_leave_tables();
    erp_acct_updater_estimate_order_status();
}

wperp_update_1_5_5();
