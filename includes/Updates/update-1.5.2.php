<?php

/**
 * Create people transaction details table
 *
 * @return void
 */
function erp_acct_updater_create_people_trn_details_table() {
    global $wpdb;

    $charset = 'CHARSET=utf8mb4';
    $collate = 'COLLATE=utf8mb4_unicode_ci';

    if ( defined( 'DB_COLLATE' ) && DB_COLLATE ) {
        $charset = 'CHARSET=' . DB_CHARSET;
        $collate = 'COLLATE=' . DB_COLLATE;
    }

    $charset_collate = $charset . ' ' . $collate;

    $table = [
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_people_trn_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `people_id` varchar(255) DEFAULT NULL,
            `voucher_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",
    ];

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $table );
}

/**
 * Populate people transactions data
 *
 * @return void
 */
function erp_acct_updater_populate_people_transactions() {
    global $wpdb;
    global $bg_process_people_trn;

    //=======================================
    // get ledger details
    //=======================================
    $vouchers = $wpdb->get_results(
        "SELECT voucher.id, voucher.type FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        WHERE voucher.type = 'invoice' OR voucher.type = 'payment' OR
        voucher.type = 'expense' OR voucher.type = 'check' OR voucher.type = 'bill' OR
        voucher.type = 'pay_bill' OR voucher.type = 'purchase' OR voucher.type = 'pay_purchase' OR
        voucher.type = 'people_trn'", ARRAY_A );

    if ( !class_exists( '\WeDevs\ERP\Updates\BP\ERPACCTBGProcess1_5_0PeopleTrn' ) || empty( $bg_process_people_trn ) ) {
        $bg_process_people_trn = new \WeDevs\ERP\Updates\BP\ERPACCTBGProcess1_5_0PeopleTrn();
    }

    // loop through vouchers
    foreach ( $vouchers as $voucher ) {
        $bg_process_people_trn->push_to_queue( $voucher );
    }

    $bg_process_people_trn->save()->dispatch();
}

/**
 * Update missing `trn_by_ledger_id` in pay purchases table
 */
function erp_acct_updater_missing_pay_purchase_trn_by_ledger_id() {
    global $wpdb;

    $pay_purchase_ids = $wpdb->get_results( "SELECT voucher_no FROM {$wpdb->prefix}erp_acct_pay_purchase", ARRAY_A );

    for ( $idx = 0; $idx < count( $pay_purchase_ids ); $idx++ ) {
        $ledger_id = $wpdb->get_row( "SELECT ledger_id FROM {$wpdb->prefix}erp_acct_ledger_details WHERE trn_no={$pay_purchase_ids[$idx]['voucher_no']}" );

        $wpdb->update( $wpdb->prefix . 'erp_acct_pay_purchase',
            [
                'trn_by_ledger_id' => $ledger_id->ledger_id,
            ],
            [ 'voucher_no' => $pay_purchase_ids[$idx]['voucher_no'] ]
        );
    }
}

/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_accounting_module_1_5_2() {
    erp_acct_updater_create_people_trn_details_table();
    erp_acct_updater_populate_people_transactions();
    erp_acct_updater_missing_pay_purchase_trn_by_ledger_id();
}

wperp_update_accounting_module_1_5_2();
