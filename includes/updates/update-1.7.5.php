<?php

namespace WeDevs\ERP\HRM\Update;

/*
 * Add tax and tax_zone_id columns in `erp_acct_purchase` table
 */
function erp_acct_alter_table_erp_acct_purchase_1_7_5() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_purchase';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( ! in_array( 'tax_zone_id', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `tax_zone_id` integer DEFAULT NULL AFTER `amount`;"
            )
        );
    }

    if ( ! in_array( 'tax', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `tax` decimal(20,2) DEFAULT NULL AFTER `amount`;"
            )
        );
    }
}

/*
 * Add tax column in `erp_acct_purchase_details` table
 */
function erp_acct_alter_table_erp_acct_purchase_details_1_7_5() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_purchase_details';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( ! in_array( 'tax', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `tax` decimal(20,2) DEFAULT NULL AFTER `amount`;"
            )
        );
    }
}

/*
 * Create `erp_acct_purchase_details_tax` table
 */
function erp_acct_create_erp_acct_purchase_details_tax_1_7_5() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$wpdb->prefix}erp_acct_purchase_details_tax (
                id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice_details_id int(20) NOT NULL,
                agency_id int(20) DEFAULT NULL,
                tax_rate decimal(20,2) NOT NULL,
                created_at timestamp DEFAULT NULL,
                created_by int(20) DEFAULT NULL,
                updated_at timestamp DEFAULT NULL,
                updated_by int(20) DEFAULT NULL,
                PRIMARY KEY  (`id`)
            ) $charset_collate";

    dbDelta( $sql );
}

/*
 * Add `Purchase Vat` system ledger
 */
function erp_acct_insert_into_table_erp_acct_ledgers_1_7_5() {
    global $wpdb;

    $checkPurchaseVat = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_vat' ]
        )
    );

    if ( empty( $checkPurchaseVat ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 5, 'Purchase Vat', 'purchase_vat', '1509', 1, date( 'Y-m-d' ) ]
            )
        );
    }
}

/*
 * Update `Asset Purchase` system ledger
 */
function erp_acct_update_table_erp_acct_ledgers_1_7_5() {
    global $wpdb;

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_ledgers',
        [ 'code' => '1507', 'updated_at' => date( 'Y-m-d' ) ],
        [ 'slug' => 'asset_purchase' ],
        [ '%s', '%s' ],
        [ '%s' ]
    );
}

erp_acct_alter_table_erp_acct_purchase_1_7_5();
erp_acct_alter_table_erp_acct_purchase_details_1_7_5();
erp_acct_create_erp_acct_purchase_details_tax_1_7_5();
erp_acct_insert_into_table_erp_acct_ledgers_1_7_5();
erp_acct_update_table_erp_acct_ledgers_1_7_5();