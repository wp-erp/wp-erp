<?php

/**
 * Create people transaction details table
 *
 * @return void
 */
function erp_acct_updater_create_people_trn_details_table() {
    global $wpdb;

    $collate = '';

    if ( defined( 'DB_COLLATE' ) ) {
        $collate = DB_COLLATE;
    }

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
        ) $collate;"
    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
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
        voucher.type = 'pay_bill' OR voucher.type = 'purchase' OR voucher.type = 'pay_purchase'", ARRAY_A );

    if ( !class_exists( '\WeDevs\ERP\Updates\BP\ERP_ACCT_BG_Process_People_Trn' ) || empty( $bg_process_people_trn ) ) {
        $bg_process_people_trn = new \WeDevs\ERP\Updates\BP\ERP_ACCT_BG_Process_People_Trn;
    }

    // loop through vouchers
    foreach ( $vouchers as $voucher ) {
        $bg_process_people_trn->push_to_queue( $voucher );
    }

    $bg_process_people_trn->save()->dispatch();
}

/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_accounting_module_1_5_1() {
    erp_acct_updater_create_people_trn_details_table();
    erp_acct_updater_populate_people_transactions();
}

wperp_update_accounting_module_1_5_1();
