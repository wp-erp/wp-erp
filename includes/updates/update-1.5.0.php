<?php

/**
 * Create accounting tables
 *
 * @return void
 */
function erp_acct_create_accounting_tables() {
    global $wpdb;

    $collate = '';

    if ( defined('DB_COLLATE') )  {
        $collate = DB_COLLATE;
    }

    $table_schema = [

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_voucher_no` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type` varchar(255) DEFAULT NULL,
            `currency` varchar(50) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_bill_account_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `bill_no` int(11) DEFAULT NULL,
            `trn_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_bill_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_no` int(11) DEFAULT NULL,
            `ledger_id` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_bills` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `vendor_id` int(11) DEFAULT NULL,
            `vendor_name` varchar(255) DEFAULT NULL,
            `address` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `due_date` date DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `ref` varchar(255) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `status` int(11) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_chart_of_accounts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_currency_info` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `sign` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate CHARSET=utf8;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_invoice_account_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_no` int(11) DEFAULT NULL,
            `trn_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_invoice_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_no` int(11) DEFAULT NULL,
            `product_id` varchar(255) DEFAULT NULL,
            `qty` int(11) DEFAULT NULL,
            `unit_price` decimal(10,2) DEFAULT 0,
            `discount` decimal(10,2) DEFAULT 0,
            `tax` decimal(10,2) DEFAULT 0,
            `item_total` decimal(10,2) DEFAULT 0,
            `tax_percent` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_invoice_receipts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `customer_id` int(11) DEFAULT NULL,
            `customer_name` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `particulars` varchar(255) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `status` int(11) DEFAULT NULL,
            `trn_by` varchar(255) DEFAULT NULL,
            `trn_by_ledger_id` int(11) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_invoice_receipts_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `invoice_no` int(11) DEFAULT NULL,
            `amount` int(11) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_invoices` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `customer_id` int(11) DEFAULT NULL,
            `customer_name` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `due_date` date DEFAULT NULL,
            `billing_address` varchar(255) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `discount` decimal(10,2) DEFAULT 0,
            `discount_type` varchar(255) DEFAULT NULL,
            `tax_rate_id` int(11) DEFAULT NULL,
            `tax` decimal(10,2) DEFAULT 0,
            `estimate` boolean DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `status` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_journal_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_no` int(11) DEFAULT NULL,
            `ledger_id` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_journals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_date` date DEFAULT NULL,
            `voucher_no` int(11) DEFAULT NULL,
            `voucher_amount` decimal(10,2) DEFAULT 0,
            `particulars` varchar(255) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_ledger_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `chart_id` int(11) DEFAULT NULL,
            `parent_id` int(11) DEFAULT NULL,
            `system` tinyint(1) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_ledger_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ledger_id` int(11) DEFAULT NULL,
            `trn_no` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `trn_date` date DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_ledger_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ledger_id` int(11) DEFAULT NULL,
            `short_code` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_ledgers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `chart_id` int(11) DEFAULT NULL,
            `category_id` int(11) DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `code` int(11) DEFAULT NULL,
            `unused` tinyint(1) DEFAULT NULL,
            `system` tinyint(1) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_cash_at_banks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ledger_id` int(11) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_transfer_voucher` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `amount` int(11) DEFAULT NULL,
            `ac_from` int(11) DEFAULT NULL,
            `ac_to` int(11) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_opening_balance` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `balance_date` date DEFAULT NULL,
            `ledger_id` int(11) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_pay_bill` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `vendor_id` int(11) DEFAULT NULL,
            `vendor_name` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `trn_by` varchar(255) DEFAULT NULL,
            `trn_by_ledger_id` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `status` int(11) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_pay_bill_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `bill_no` int(11) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_pay_purchase` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `vendor_id` int(11) DEFAULT NULL,
            `vendor_name` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `trn_by` varchar(255) DEFAULT NULL,
            `trn_by_ledger_id` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `status` int(11) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_pay_purchase_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `purchase_no` int(11) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_people_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `people_id` varchar(255) DEFAULT NULL,
            `trn_no` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `voucher_type` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_product_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `parent` int(11) NOT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_product_types` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `product_type_id` int(11) DEFAULT NULL,
            `category_id` int(11) DEFAULT NULL,
            `tax_cat_id` int(11) DEFAULT NULL,
            `vendor` int(11) DEFAULT NULL,
            `cost_price` decimal(10,2) DEFAULT 0,
            `sale_price` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_purchase` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `vendor_id` int(11) DEFAULT NULL,
            `vendor_name` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `due_date` date DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `ref` varchar(255) DEFAULT NULL,
            `status` int(11) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_purchase_account_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `purchase_no` int(11) DEFAULT NULL,
            `trn_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_purchase_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_no` int(11) DEFAULT NULL,
            `product_id` int(11) DEFAULT NULL,
            `qty` int(11) DEFAULT NULL,
            `price` decimal(10,2) DEFAULT 0,
            `amount` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) DEFAULT NULL,
            `description` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_taxes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tax_rate_id` int(11) DEFAULT NULL,
            `tax_number` varchar(100) DEFAULT NULL,
            `default` boolean DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_cat_agency` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tax_id` int(11) DEFAULT NULL,
            `component_name` varchar(255) DEFAULT NULL,
            `tax_cat_id` int(11) DEFAULT NULL,
            `agency_id` int(11) DEFAULT NULL,
            `tax_rate` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_rate_names` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_agencies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_pay` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `voucher_type` varchar(255) DEFAULT NULL,
            `trn_by` int(11) DEFAULT NULL,
            `agency_id` int(11) DEFAULT NULL,
            `ledger_id` int(11) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_agency_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `agency_id` int(11) DEFAULT NULL,
            `trn_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10, 2) DEFAULT 0,
            `credit` decimal(10, 2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_invoice_details_tax` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `invoice_details_id` int(11) DEFAULT NULL,
            `agency_id` int(11) DEFAULT NULL,
            `tax_rate` decimal(10,2) DEFAULT 0,
            `tax_amount` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_trn_status_types` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type_name` varchar(255) DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_payment_methods` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_expenses` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `people_id` int(11) DEFAULT NULL,
            `people_name` varchar(255) DEFAULT NULL,
            `address` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `ref` varchar(255) DEFAULT NULL,
            `check_no` varchar(255) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `status` int(11) DEFAULT NULL,
            `trn_by` int(11) DEFAULT NULL,
            `trn_by_ledger_id` int(11) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_expense_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_no` int(11) DEFAULT NULL,
            `ledger_id` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_expense_checks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_no` int(11) DEFAULT NULL,
            `check_no` int(11) DEFAULT NULL,
            `voucher_type` varchar(255) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `name` varchar(255) DEFAULT NULL,
            `pay_to` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $collate;",

    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    foreach ( $table_schema as $table ) {
        dbDelta( $table );
    }
}

/**
 * Populate tables with initial data
 *
 * @return void
 */
function erp_acct_populate_data() {
    global $wpdb;

    // check if people_types exists
        $sql = "INSERT IGNORE INTO `{$wpdb->prefix}erp_people_types` (`id`, `name`) VALUES (5, 'employee')";

        $wpdb->query( $sql );

    /** ===========
     * Accounitng
     * ============
     */

    // insert chart of accounts
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_chart_of_accounts` LIMIT 0, 1" ) ) {
        $charts = ['Asset', 'Liability', 'Equity', 'Income', 'Expense', 'Asset & Liability', 'Bank'];

        for ( $i = 0; $i < count($charts); $i++ ) {
            $wpdb->insert( "{$wpdb->prefix}erp_acct_chart_of_accounts", [
                'name' => $charts[$i],
                'slug' => slugify($charts[$i])
            ] );
        }
    }


    // insert payment methods
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_payment_methods` LIMIT 0, 1" ) ) {
        $methods = ['Cash', 'Bank', 'Check'];

        for ( $i = 0; $i < count($methods); $i++ ) {
            $wpdb->insert( "{$wpdb->prefix}erp_acct_payment_methods", [
                'name' => $methods[$i],
            ] );
        }
    }

    // insert status types
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_trn_status_types` LIMIT 0, 1" ) ) {
        $statuses = [
            'Draft',
            'Awaiting Approval',
            'Pending',
            'Paid',
            'Partially Paid',
            'Approved',
            'Bounced',
            'Closed',
            'Void'
        ];

        for ( $i = 0; $i < count($statuses); $i++ ) {
            $wpdb->insert( "{$wpdb->prefix}erp_acct_trn_status_types", [
                'type_name' => $statuses[$i],
                'slug'      => slugify( $statuses[$i] )
            ] );
        }
    }

    // insert product types
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_product_types` LIMIT 0, 1" ) ) {
        $sql = "INSERT INTO `{$wpdb->prefix}erp_acct_product_types` (`id`, `name`)
                VALUES (1, 'Product'), (2, 'Service')";

        $wpdb->query( $sql );
    }

    // insert currency info
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_currency_info` LIMIT 0, 1" ) ) {
        $sql = "INSERT INTO `{$wpdb->prefix}erp_acct_currency_info` (`id`, `name`, `sign`)
                VALUES (1, 'USD', '$'), (2, 'EUR', '€')";

        $wpdb->query( $sql );
    }
}

/**
 * ===========================================================================
 * Begin the hard work ...
 * ====================================================================
 */

global $db_tax_items;
global $db_tax_agencies;

/**
 * Custom array unique
 */
function erp_acct_array_unique( $array ) {
    return array_intersect_key(
        $array,
        array_unique( array_map( 'strtolower', $array ) )
    );
}

/**
 * Populate tax agencies
 *
 * @return array
 */
function erp_acct_populate_tax_agencies() {
    global $wpdb;
    global $db_tax_items;
    global $db_tax_agencies;

    //=============================
    // get previous tax info
    //=============================
    $db_tax_items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}erp_ac_tax_items", ARRAY_A);

    $tax_agencies = array_map(function($tax) {
        return $tax['agency_name'];
    }, $db_tax_items);

    $unique_agencies = erp_acct_array_unique( $tax_agencies );

    foreach ( $unique_agencies as $unique_agency ) {
        $wpdb->insert(
            // `erp_acct_tax_agencies`
            "{$wpdb->prefix}erp_acct_tax_agencies", [
                'name'       => $unique_agency,
                'created_at' => date('Y-m-d'),
                'created_by' => 1
            ]
        );
    }

    // name, id ( order is very* *very important here )
    $db_tax_agencies = $wpdb->get_results("SELECT name, id FROM {$wpdb->prefix}erp_acct_tax_agencies", OBJECT_K);
}

/**
 * Populate tax data
 *
 * @return array
 */
function erp_acct_populate_tax_data() {
    global $wpdb;
    global $db_tax_items;
    global $db_tax_agencies;

    //=============================
    // first get previous tax info
    //=============================
    $taxes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}erp_ac_tax", ARRAY_A);

    for ( $i = 0; $i < count($taxes); $i++ ) {
        $wpdb->insert(
            // `erp_acct_taxes`
            "{$wpdb->prefix}erp_acct_taxes", [
                'tax_rate_id' => $i + 1,
                'tax_number'  => $taxes[$i]['tax_number'],
                'default'     => 0 === $i ? 1 : 0, // if first record
                'created_at'  => date('Y-m-d'),
                'created_by'  => $taxes[$i]['created_by']
            ]
        );

        foreach ( $db_tax_items as $db_tax_item ) {
            if ( $taxes[$i]['id'] === $db_tax_item['tax_id'] ) {
                $wpdb->insert(
                    // `erp_acct_tax_cat_agency`
                    "{$wpdb->prefix}erp_acct_tax_cat_agency", [
                        'tax_id'         => $db_tax_item['tax_id'],
                        'component_name' => $db_tax_item['component_name'],
                        'tax_cat_id'     => null,
                        'agency_id'      => $db_tax_agencies[$db_tax_item['agency_name']]->id,
                        'tax_rate'       => $db_tax_item['tax_rate'],
                        'created_at'     => date('Y-m-d'),
                        'created_by'     => $taxes[$i]['created_by']
                    ]
                );
            } // if
        } // foreach
    } // for

    foreach ( $taxes as $tax ) {
        $wpdb->insert(
            // `erp_acct_tax_rate_names`
            "{$wpdb->prefix}erp_acct_tax_rate_names", [
                'name'       => $tax['name'],
                'created_at' => date('Y-m-d'),
            ]
        );
    } // foreach
}

/**
 * Populate transactions data
 *
 * @return void
 */
function erp_acct_populate_transactions() {
    global $wpdb;
    // global $currencies;

    //=======================================
    // get transaction status types (new)
    //=======================================
    $status_types = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}erp_acct_trn_status_types", ARRAY_A);

    //=============================
    // get transactions (old)
    //=============================
    $transactions = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}erp_ac_transactions", ARRAY_A);

    global $bg_process;

    // loop through transactions
    for ( $i = 0; $i < count($transactions); $i++ ) {
        $bg_process->push_to_queue( $transactions[$i] );
    }

    $bg_process->save()->dispatch();
}

/**
 * Migrate Existing Employees to people
 */
function erp_employees_to_people_migration() {
    $employees = erp_hr_get_employees( array(
        'number'     => '-1',
    ) );

    foreach ( $employees as $employee ) {
        erp_acct_add_employee_as_people( $employee->data );
    }
}

/**
 * Populate ledger categories and ledgers
 */
function erp_acct_populate_charts_ledgers() {
    global $wpdb;

    $wpdb->query( "UPDATE {$wpdb->prefix}erp_ac_ledger SET name='Cash' WHERE name='Petty Cash'" );

    $old_ledgers = $wpdb->get_results( "SELECT 
        ledger.id, chart.id as chart_id, chart_cat.id category_id, ledger.name, ledger.code, ledger.system 
        FROM {$wpdb->prefix}erp_ac_ledger as ledger
        LEFT JOIN {$wpdb->prefix}erp_ac_chart_types AS chart_cat ON ledger.type_id = chart_cat.id
        LEFT JOIN {$wpdb->prefix}erp_ac_chart_classes AS chart ON chart_cat.class_id = chart.id ORDER BY chart_id;", ARRAY_A );

    foreach ( $old_ledgers as $old_ledger ) {
        if ( '120' == $old_ledger['code'] || '200' == $old_ledger['code'] ) {
            $value['unused'] = true;
        }

        $wpdb->insert(
            "{$wpdb->prefix}erp_acct_ledgers",
            [
                'chart_id' => $old_ledger['chart_id'],
                'name'     => $old_ledger['name'],
                'slug'     => slugify( $old_ledger['name'] ),
                'code'     => $old_ledger['code'],
                'unused'   => isset( $old_ledger['unused'] ) ? $old_ledger['unused'] : null,
                'system'   => $old_ledger['system']
            ]
        );
    }

    if ( get_option( 'erp_acct_new_ledgers' ) ) {
        return;
    } else {
        $ledgers_json = file_get_contents( WPERP_ASSETS . '/ledgers.json' );
        $ledgers = json_decode( $ledgers_json, true );

        foreach ( array_keys( $ledgers ) as $array_key ) {
            foreach ( $ledgers[$array_key] as $value ) {
                $wpdb->insert(
                    "{$wpdb->prefix}erp_acct_ledgers",
                    [
                        'chart_id' => erp_acct_get_chart_id_by_slug($array_key),
                        'name'     => $value['name'],
                        'slug'     => slugify( $value['name'] ),
                        'code'     => $value['code'],
                        'system'   => $value['system']
                    ]
                );
            }
        }
    }

}

/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_accounting_module_1_5_0() {
    erp_acct_create_accounting_tables();
    erp_acct_populate_data();

    erp_acct_populate_tax_agencies();
    erp_acct_populate_tax_data();

    erp_acct_populate_transactions();

    erp_acct_populate_charts_ledgers();

    erp_employees_to_people_migration();
}

wperp_update_accounting_module_1_5_0();
