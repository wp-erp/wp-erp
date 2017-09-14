<?php
/**
 * Drop unused core campaigns table
 *
 * @since 1.2.5
 *
 * @return void
 */
function erp_crm_delete_core_campaign_tables_1_2_5() {
    global $wpdb;

    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}erp_crm_campaigns`;" );
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}erp_crm_campaign_group`;" );
}

erp_crm_delete_core_campaign_tables_1_2_5();

/**
 * Drop unused core campaigns table
 *
 * @since 1.2.5
 *
 * @return void
 */
function erp_create_table_indices_1_2_5() {
    global $wpdb;

    $query = "SELECT 1"
           . " FROM INFORMATION_SCHEMA.STATISTICS"
           . " WHERE table_schema=DATABASE() AND TABLE_NAME='{$wpdb->prefix}erp_hr_employees' AND INDEX_NAME='employee_id'"
           . " LIMIT 1";

    if ( $wpdb->get_var( $query ) ) {
        return;
    }

    $tables = [
        'erp_hr_employees' => [
            'employee_id',
            'designation',
            'department',
            'status',
        ],
        'erp_hr_leave_entitlements' => [
            'policy_id'
        ],
        'erp_hr_leaves' => [
            'date'
        ],
        'erp_hr_leave_requests' => [
            'status',
            'created_by',
            'updated_by'
        ],
        'erp_hr_announcement' => [
            'user_id',
            'post_id',
            'status'
        ],
        'erp_peoples' => [
            'first_name',
            'last_name',
            'email'
        ],
        'erp_audit_log' => [
            'component',
            'sub_component',
            'changetype',
            'created_by'
        ],
        'erp_crm_customer_companies' => [
            'customer_id',
            'company_id'
        ],
        'erp_crm_customer_activities' => [
            'user_id',
            'type',
            'log_type',
            'created_by'
        ],
        'erp_crm_activities_task' => [
            'activity_id',
            'user_id'
        ]
    ];


    foreach ( $tables as $table => $columns ) {
        foreach ( $columns as $column ) {
            $wpdb->query( "CREATE INDEX `{$column}` ON {$wpdb->prefix}{$table} (`{$column}`);" );
        }
    }
}

erp_create_table_indices_1_2_5();


/**
 * Add hash column in erp_crm_contact_subscriber
 *
 * It was a missing updater from v1.1.17 to v1.2.3
 *
 * @see https://github.com/wp-erp/wp-erp/pull/506
 *
 * @since 1.2.5
 *
 * @return void
 */
function erp_crm_update_table_1_2_5() {
    global $wpdb;

    // Add hash column in `erp_crm_contact_subscriber` table
    $table = $wpdb->prefix . 'erp_crm_contact_subscriber';
    $cols  = $wpdb->get_col( "DESC $table");

    if ( ! in_array( 'hash', $cols ) ) {
        $wpdb->query( "ALTER TABLE $table ADD `hash` VARCHAR(40) NULL DEFAULT NULL AFTER `unsubscribe_at`;" );
    }
}

erp_crm_update_table_1_2_5();
