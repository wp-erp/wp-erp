<?php

/**
 * Update DB charset & collate
 *
 * @return void
 */
function erp_updater_db_collate() {
    global $wpdb;

    $db_name = DB_NAME;

    $tables = $wpdb->get_results(
        "SELECT table_name FROM information_schema.tables where table_schema = '{$db_name}' and table_name like '{$wpdb->prefix}erp_%'",
        ARRAY_A
    );

    foreach ( $tables as $table ) {
        $wpdb->query( "ALTER TABLE {$table['table_name']}
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

    if ( defined( 'DB_COLLATE' ) && DB_COLLATE ) {
        $charset = 'CHARSET=' . DB_CHARSET;
        $collate = 'COLLATE=' . DB_COLLATE;
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

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

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

    $wpdb->query( "UPDATE {$wpdb->prefix}erp_acct_invoices SET status = 3 WHERE estimate = 1" );
    $wpdb->query( "UPDATE {$wpdb->prefix}erp_acct_purchase SET status = 3 WHERE purchase_order = 1" );
}

/**
 * Populate ledger categories and ledgers
 */
function erp_acct_populate_charts_ledgers_155() {
    global $wpdb;

    $old_ledgers = [];
    $ledgers     = [];

    require WPERP_INCLUDES . '/ledgers.php';

    $o_ledgers = $wpdb->get_results( "SELECT
        ledger.code, ledger.id, ledger.system, chart_cat.id category_id, chart.id as chart_id, ledger.name
        FROM {$wpdb->prefix}erp_ac_ledger as ledger
        LEFT JOIN {$wpdb->prefix}erp_ac_chart_types AS chart_cat ON ledger.type_id = chart_cat.id
        LEFT JOIN {$wpdb->prefix}erp_ac_chart_classes AS chart ON chart_cat.class_id = chart.id ORDER BY chart_id", ARRAY_A );

    if ( ! empty( $o_ledgers ) ) {
        for ( $i = 0; $i < count( $o_ledgers ); $i++ ) {
            if ( $o_ledgers[$i]['chart_id'] == 3 ) {
                $o_ledgers[$i]['chart_id'] = 5;
            } elseif ( $o_ledgers[$i]['chart_id'] == 5 ) {
                $o_ledgers[$i]['chart_id'] = 3;
            }
        }
        $old_ledgers = $o_ledgers;
    }

    $old_banks = $wpdb->get_results( "SELECT	ledger_id, account_number as code, bank_name as name
        FROM {$wpdb->prefix}erp_ac_banks WHERE ledger_id <> 7", ARRAY_A );

    $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'erp_acct_ledgers' );

    foreach ( $old_ledgers as $old_ledger ) {
        if ( '120' == $old_ledger['code'] || '200' == $old_ledger['code'] ) {
            $old_ledger['unused'] = true;
        }

        $wpdb->insert(
            "{$wpdb->prefix}erp_acct_ledgers",
            [
                'id'       => $old_ledger['id'],
                'chart_id' => $old_ledger['chart_id'],
                'name'     => $old_ledger['name'],
                'slug'     => slugify( $old_ledger['name'] ),
                'code'     => $old_ledger['code'],
                'unused'   => isset( $old_ledger['unused'] ) ? $old_ledger['unused'] : null,
                'system'   => $old_ledger['system'],
            ]
        );
    }

    foreach ( array_keys( $ledgers ) as $array_key ) {
        foreach ( $ledgers[$array_key] as $value ) {
            $wpdb->insert(
                "{$wpdb->prefix}erp_acct_ledgers",
                [
                    'chart_id' => erp_acct_get_chart_id_by_slug( $array_key ),
                    'name'     => $value['name'],
                    'slug'     => slugify( $value['name'] ),
                    'code'     => $value['code'],
                    'system'   => $value['system'],
                ]
            );
        }
    }

    foreach ( $old_banks as $old_bank ) {
        $wpdb->insert(
            "{$wpdb->prefix}erp_acct_ledgers",
            [
                'chart_id' => 7,
                'name'     => $old_bank['name'],
                'slug'     => slugify( $old_bank['name'] ),
                'code'     => $old_bank['code'],
            ]
        );
    }
}

/**
 * This method will rename petty_cash to cash on ledger table
 *
 * @since 1.6.0
 *
 * @return void
 */
function erp_acct_rename_petty_cash() {
    global $wpdb;

    // get chart_id for expenses
    $petty_cash_chart_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}erp_acct_ledgers WHERE code = %d",
            [ 90 ]
        )
    );

    // update chart_id for 1403
    if ( null !== $petty_cash_chart_id && $petty_cash_chart_id > 0 ) {
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}erp_acct_ledgers SET name = %s, slug = %s WHERE id = %d",
                [ 'Cash', slugify( 'Cash' ), $petty_cash_chart_id ]
            )
        );
    }
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
    erp_acct_populate_charts_ledgers_155();
    erp_acct_rename_petty_cash();
}

wperp_update_1_5_5();
