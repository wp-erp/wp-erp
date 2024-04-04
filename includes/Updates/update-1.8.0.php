<?php

/**
 * Insert necessary ledgers for purchase return
 */
function erp_acct_insert_to_erp_acct_ledgers_1_8_0() {
    global $wpdb;

    $sales_return_discount_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'sales_return_discount' ]
        )
    );

    if ( empty( $sales_return_discount_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Sales Return Discount', 'sales_return_discount', '1406', 1, gmdate( 'Y-m-d' ) ]
            )
        );
    }

    $sales_return_tax_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'sales_return_tax' ]
        )
    );

    if ( empty( $sales_return_tax_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Sales Return Tax', 'sales_return_tax', '1407', 1, gmdate( 'Y-m-d' ) ]
            )
        );
    }

    $purchase_return_exists  = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return' ]
        )
    );

    if ( empty( $purchase_return_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Purchase Return', 'purchase_return', '1408', 1, gmdate( 'Y-m-d' ) ]
            )
        );
    }

    $purchase_return_vat_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return_vat' ]
        )
    );

    if ( empty( $purchase_return_vat_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Purchase Return VAT', 'purchase_return_vat', '1409', 1, gmdate( 'Y-m-d' ) ]
            )
        );
    }

    $purchase_return_discount_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return_discount' ]
        )
    );

    if ( empty( $purchase_return_discount_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 5, 'Purchase Return Discount', 'purchase_return_discount', '1410', 1, gmdate( 'Y-m-d' ) ]
            )
        );
    }

    $purchase_vat_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_vat' ]
        )
    );

    if ( empty( $purchase_vat_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 5, 'Purchase VAT', 'purchase_vat', '1509', 1, gmdate( 'Y-m-d' ) ]
            )
        );
    }
}

/*
 * Add tax and tax_zone_id columns in `erp_acct_purchase` table
 */
function erp_acct_alter_table_erp_acct_purchase_1_8_0() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_purchase';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( ! in_array( 'tax_zone_id', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE `{$wpdb->prefix}erp_acct_purchase` ADD `tax_zone_id` integer DEFAULT NULL AFTER `amount`;"
            )
        );
    }

    if ( ! in_array( 'tax', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE `{$wpdb->prefix}erp_acct_purchase` ADD `tax` decimal(20,2) DEFAULT NULL AFTER `amount`;"
            )
        );
    }
}

/*
 * Add tax column in `erp_acct_purchase_details` table
 */
function erp_acct_alter_table_erp_acct_purchase_details_1_8_0() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_purchase_details';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( ! in_array( 'tax', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE `{$wpdb->prefix}erp_acct_purchase_details` ADD `tax` decimal(20,2) DEFAULT NULL AFTER `amount`;"
            )
        );
    }
}

/*
 * Create `erp_acct_purchase_details_tax` table
 */
function erp_acct_create_erp_acct_purchase_details_tax_1_8_0() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$wpdb->prefix}erp_acct_purchase_details_tax (
                id int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                invoice_details_id int(20) NOT NULL,
                agency_id int(20) DEFAULT NULL,
                tax_rate decimal(20,2) NOT NULL,
                created_at datetime DEFAULT NULL,
                created_by int(20) DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                updated_by int(20) DEFAULT NULL,
                PRIMARY KEY  (`id`)
            ) $charset_collate";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( $sql );
}

/*
 * Update `Asset Purchase` system ledger
 */
function erp_acct_update_table_erp_acct_ledgers_1_8_0() {
    global $wpdb;

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_ledgers',
        [ 'code' => '1507', 'updated_at' => gmdate( 'Y-m-d' ) ],
        [ 'slug' => 'asset_purchase' ],
        [ '%s', '%s' ],
        [ '%s' ]
    );
}

/*
 * Add `returned` transaction status type
 */
function erp_acct_insert_into_table_trn_status_types_1_8_0() {
    global $wpdb;

    $returned_trn_type = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}erp_acct_trn_status_types WHERE slug = %s",
            'returned'
        )
    );

    if ( empty( $returned_trn_type ) ) {
        $wpdb->insert( "{$wpdb->prefix}erp_acct_trn_status_types", [
            'type_name' => 'Returned',
            'slug'      => 'returned',
        ] );
    }

    $part_returned_trn_type = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}erp_acct_trn_status_types WHERE slug = %s",
            'partially_returned'
        )
    );

    if ( empty( $part_returned_trn_type ) ) {
        $wpdb->insert( "{$wpdb->prefix}erp_acct_trn_status_types", [
            'type_name' => 'Partially Returned',
            'slug'      => 'partially_returned',
        ] );
    }
}

/*
 * Add tax_zone_id column in `erp_acct_invoices` table
 */
function erp_acct_alter_table_erp_acct_invoices_1_8_0() {
    global $wpdb;

    $cols = $wpdb->get_col( "DESC {$wpdb->prefix}erp_acct_invoices" );

    if ( ! in_array( 'tax_zone_id', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE `{$wpdb->prefix}erp_acct_invoices` ADD `tax_zone_id` int(11) DEFAULT NULL AFTER `tax`;"
            )
        );
    }
}

erp_acct_alter_table_erp_acct_purchase_1_8_0();
erp_acct_alter_table_erp_acct_purchase_details_1_8_0();
erp_acct_create_erp_acct_purchase_details_tax_1_8_0();
erp_acct_insert_to_erp_acct_ledgers_1_8_0();
erp_acct_update_table_erp_acct_ledgers_1_8_0();
erp_acct_insert_into_table_trn_status_types_1_8_0();
erp_acct_alter_table_erp_acct_invoices_1_8_0();