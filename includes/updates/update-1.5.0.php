<?php

/**
 * Create accounting tables
 *
 * @return void
 */
function erp_acct_create_accounting_tables() {
    global $wpdb;

    $charset = 'CHARSET=utf8mb4';
    $collate = 'COLLATE=utf8mb4_unicode_ci';

    if ( defined('DB_COLLATE') && DB_COLLATE )  {
        $charset = 'CHARSET=' . DB_CHARSET;
        $collate = 'COLLATE=' . DB_COLLATE;
    }

    $charset_collate = $charset . ' ' . $collate;


    $table_schema = [

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_voucher_no` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type` varchar(255) DEFAULT NULL,
            `currency` varchar(50) DEFAULT NULL,
            `editable` tinyint DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_bills` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `vendor_id` int(11) DEFAULT NULL,
            `vendor_name` varchar(255) DEFAULT NULL,
            `address` varchar(255) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `due_date` date DEFAULT NULL,
            `ref` varchar(255) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `particulars` varchar(255) DEFAULT NULL,
            `status` int(11) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_chart_of_accounts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_currency_info` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `sign` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate CHARSET=utf8;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_invoice_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_no` int(11) DEFAULT NULL,
            `product_id` int(11) DEFAULT NULL,
            `qty` int(11) DEFAULT NULL,
            `unit_price` decimal(10,2) DEFAULT 0,
            `discount` decimal(10,2) DEFAULT 0,
            `tax` decimal(10,2) DEFAULT 0,
            `item_total` decimal(10,2) DEFAULT 0,
            `ecommerce_type` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_invoice_receipts_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `invoice_no` int(11) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_journals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_date` date DEFAULT NULL,
            `ref` varchar(255) DEFAULT NULL,
            `voucher_no` int(11) DEFAULT NULL,
            `voucher_amount` decimal(10,2) DEFAULT 0,
            `particulars` varchar(255) DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_ledger_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ledger_id` int(11) DEFAULT NULL,
            `short_code` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_cash_at_banks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ledger_id` int(11) DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            `balance` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_transfer_voucher` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voucher_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT NULL,
            `ac_from` int(11) DEFAULT NULL,
            `ac_to` int(11) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_opening_balances` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `financial_year_id` int(11) DEFAULT NULL,
            `chart_id` int(11) DEFAULT NULL,
            `ledger_id` int(11) DEFAULT NULL,
            `type` varchar(50) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_financial_years` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `start_date` date DEFAULT NULL,
            `end_date` date DEFAULT NULL,
            `description` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_people_account_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `people_id` varchar(255) DEFAULT NULL,
            `trn_no` int(11) DEFAULT NULL,
            `trn_date` date DEFAULT NULL,
            `trn_by` varchar(255) DEFAULT NULL,
            `voucher_type` varchar(255) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `debit` decimal(10,2) DEFAULT 0,
            `credit` decimal(10,2) DEFAULT 0,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_people_trn` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `people_id` varchar(255) DEFAULT NULL,
            `voucher_no` int(11) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `trn_date` date DEFAULT NULL,
            `trn_by` varchar(255) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `voucher_type` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_product_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `parent` int(11) NOT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_product_types` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_product_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) DEFAULT NULL,
            `trn_no` int(11) DEFAULT NULL,
            `stock_in` int(11) DEFAULT NULL,
            `stock_out` int(11) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
            `purchase_order` boolean DEFAULT NULL,
            `attachments` varchar(255) DEFAULT NULL,
            `particulars` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) DEFAULT NULL,
            `description` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_taxes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tax_rate_name` varchar(255) DEFAULT NULL,
            `tax_number` varchar(100) DEFAULT NULL,
            `default` boolean DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_agencies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `ecommerce_type` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_trn_status_types` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `type_name` varchar(255) DEFAULT NULL,
            `slug` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_payment_methods` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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
        ) $charset_collate;",

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
        ) $charset_collate;",

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_expense_checks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `trn_no` int(11) DEFAULT NULL,
            `check_no` varchar(255) DEFAULT NULL,
            `voucher_type` varchar(255) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT 0,
            `bank` varchar(255) DEFAULT NULL,
            `name` varchar(255) DEFAULT NULL,
            `pay_to` varchar(255) DEFAULT NULL,
            `created_at` date DEFAULT NULL,
            `created_by` varchar(50) DEFAULT NULL,
            `updated_at` date DEFAULT NULL,
            `updated_by` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) $charset_collate;",

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

    do_action( 'erp_acct_before_new_acct_populate_data' );

    // check if people_types exists
    $sql = "INSERT IGNORE INTO `{$wpdb->prefix}erp_people_types` (`id`, `name`) VALUES (5, 'employee')";

    $wpdb->query( $sql );

    /** ===========
     * Accounitng
     * ============
     */

    // insert chart of accounts
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_chart_of_accounts` LIMIT 0, 1" ) ) {
        $charts = [ 'Asset', 'Liability', 'Equity', 'Income', 'Expense', 'Asset & Liability', 'Bank' ];

        for ( $i = 0; $i < count( $charts ); $i++ ) {
            $wpdb->insert( "{$wpdb->prefix}erp_acct_chart_of_accounts", [
                'name' => $charts[$i],
                'slug' => slugify( $charts[$i] )
            ] );
        }
    }


    // insert payment methods
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_payment_methods` LIMIT 0, 1" ) ) {
        $methods = [ 'Cash', 'Bank', 'Check' ];

        for ( $i = 0; $i < count( $methods ); $i++ ) {
            $wpdb->insert( "{$wpdb->prefix}erp_acct_payment_methods", [
                'name' => $methods[$i],
            ] );
        }
    }

    // insert status types
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_trn_status_types` LIMIT 0, 1" ) ) {
        $statuses = [
            'Draft',
            'Awaiting Payment',
            'Pending',
            'Paid',
            'Partially Paid',
            'Approved',
            'Closed',
            'Void'
        ];

        for ( $i = 0; $i < count( $statuses ); $i++ ) {
            $wpdb->insert( "{$wpdb->prefix}erp_acct_trn_status_types", [
                'type_name' => $statuses[$i],
                'slug'      => slugify( $statuses[$i] )
            ] );
        }
    }

    // insert product types
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_product_types` LIMIT 0, 1" ) ) {
        $sql = "INSERT INTO `{$wpdb->prefix}erp_acct_product_types` (`id`, `name`, `slug`)
                    VALUES (1, 'Inventory', 'inventory'), (2, 'Service', 'service')";

        $wpdb->query( $sql );
    }

    // insert currency info
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_currency_info` LIMIT 0, 1" ) ) {
        $currency_symbols = [
            'AED' => 'د.إ',
            'AFN' => '؋',
            'ALL' => 'L',
            'AMD' => 'AMD',
            'ANG' => 'ƒ',
            'AOA' => 'Kz',
            'ARS' => '$',
            'AUD' => '$',
            'AWG' => 'ƒ',
            'AZN' => '₼',
            'BAM' => 'KM',
            'BBD' => '$',
            'BDT' => '৳',
            'BGN' => 'лв',
            'BHD' => '.د.ب',
            'BIF' => 'Fr',
            'BMD' => '$',
            'BND' => '$',
            'BOB' => 'Bs.',
            'BRL' => 'R$',
            'BSD' => '$',
            'BTN' => 'Nu.',
            'BWP' => 'P',
            'BYN' => 'Br',
            'BYR' => 'Br',
            'BZD' => '$',
            'CAD' => '$',
            'CDF' => 'Fr',
            'CHF' => 'Fr',
            'CLP' => '$',
            'CNY' => '¥',
            'COP' => '$',
            'CRC' => '₡',
            'CUC' => '$',
            'CUP' => '$',
            'CVE' => '$',
            'CZK' => 'Kč',
            'DJF' => 'Fr',
            'DKK' => 'kr',
            'DOP' => '$',
            'DZD' => 'د.ج',
            'EGP' => '£',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'EUR' => '€',
            'FJD' => '$',
            'FKP' => '£',
            'GBP' => '£',
            'GEL' => 'GEL',
            'GGP' => '£',
            'GHS' => '₵',
            'GIP' => '£',
            'GMD' => 'D',
            'GNF' => 'Fr',
            'GTQ' => 'Q',
            'GYD' => '$',
            'HKD' => '$',
            'HNL' => 'L',
            'HRK' => 'kn',
            'HTG' => 'G',
            'HUF' => 'Ft',
            'IDR' => 'Rp',
            'ILS' => '₪',
            'IMP' => '£',
            'INR' => '₹',
            'IQD' => 'ع.د',
            'IRR' => '﷼',
            'ISK' => 'kr',
            'JEP' => '£',
            'JMD' => '$',
            'JOD' => 'د.ا',
            'JPY' => '¥',
            'KES' => 'Sh',
            'KGS' => 'с',
            'KHR' => '៛',
            'KMF' => 'Fr',
            'KPW' => '₩',
            'KRW' => '₩',
            'KWD' => 'د.ك',
            'KYD' => '$',
            'KZT' => 'KZT',
            'LAK' => '₭',
            'LBP' => 'ل.ل',
            'LKR' => 'Rs',
            'LRD' => '$',
            'LSL' => 'L',
            'LYD' => 'ل.د',
            'MAD' => 'د.م.',
            'MDL' => 'L',
            'MGA' => 'Ar',
            'MKD' => 'ден',
            'MMK' => 'Ks',
            'MNT' => '₮',
            'MOP' => 'P',
            'MRO' => 'UM',
            'MUR' => '₨',
            'MVR' => 'MVR',
            'MWK' => 'MK',
            'MXN' => '$',
            'MYR' => 'RM',
            'MZN' => 'MT',
            'NAD' => '$',
            'NGN' => '₦',
            'NIO' => 'C$',
            'NOK' => 'kr',
            'NPR' => '₨',
            'NZD' => '$',
            'OMR' => 'ر.ع.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '₱',
            'PKR' => '₨',
            'PLN' => 'zł',
            'PRB' => 'р.',
            'PYG' => '₲',
            'QAR' => 'ر.ق',
            'RON' => 'lei',
            'RSD' => 'дин',
            'RUB' => '₽',
            'RWF' => 'Fr',
            'SAR' => 'ر.س',
            'SBD' => '$',
            'SCR' => '₨',
            'SDG' => 'ج.س.',
            'SEK' => 'kr',
            'SGD' => '$',
            'SHP' => '£',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '$',
            'SSP' => '£',
            'STD' => 'Db',
            'SYP' => '£',
            'SZL' => 'L',
            'THB' => '฿',
            'TJS' => 'ЅМ',
            'TMT' => 'm',
            'TND' => 'د.ت',
            'TOP' => 'T$',
            'TRY' => 'TRY',
            'TTD' => '$',
            'TVD' => '$',
            'TWD' => '$',
            'TZS' => 'Sh',
            'UAH' => '₴',
            'UGX' => 'Sh',
            'USD' => '$',
            'UYU' => '$',
            'UZS' => 'UZS',
            'VEF' => 'Bs',
            'VND' => '₫',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => 'Fr',
            'XCD' => '$',
            'XOF' => 'Fr',
            'XPF' => 'Fr',
            'YER' => '﷼',
            'ZAR' => 'R',
            'ZMW' => 'ZK',
            'ZWL' => '$',
        ];

        foreach ( $currency_symbols as $key => $val ) {
            $sql = "INSERT INTO `{$wpdb->prefix}erp_acct_currency_info` (`name`, `sign`)
                VALUES ( '{$key}', '{$val}' )";

            $wpdb->query( $sql );
        }
    }

    //Insert default financial years
    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_financial_years` LIMIT 0, 1" ) ) {

        $general         = get_option( 'erp_settings_general', array() );
        $financial_month = isset( $general['gen_financial_month'] ) ? $general['gen_financial_month'] : '1';

        $start_date = new DateTime( date( 'Y-' . $financial_month . '-1' ) );

        $start_date = $start_date->format( "Y-m-d" );

        $end_date = date( 'Y-m-d', strtotime( '+1 year', strtotime( $start_date ) ) );
        $end_date = new DateTime( $end_date );
        $end_date->modify( "-1 day" );

        $end_date = $end_date->format( "Y-m-d" );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_financial_years', array(
            'name'       => date( "Y" ),
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'created_at' => date( "Y-m-d" ),
            'created_by' => get_current_user_id()
        ) );
    }

    do_action( 'erp_acct_after_new_acct_populate_data' );
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
    $db_tax_items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_ac_tax_items", ARRAY_A );

    $tax_agencies = array_map( function( $tax ) {
        return $tax['agency_name'];
    }, $db_tax_items );

    $unique_agencies = erp_acct_array_unique( $tax_agencies );

    foreach ( $unique_agencies as $unique_agency ) {
        $wpdb->insert(
        // `erp_acct_tax_agencies`
            "{$wpdb->prefix}erp_acct_tax_agencies", [
                'name'       => $unique_agency,
                'created_at' => date( 'Y-m-d' ),
                'created_by' => 1
            ]
        );
    }

    // name, id ( order is very* *very important here )
    $db_tax_agencies = $wpdb->get_results( "SELECT name, id FROM {$wpdb->prefix}erp_acct_tax_agencies", OBJECT_K );
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
    $taxes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_ac_tax", ARRAY_A );

    for ( $i = 0; $i < count( $taxes ); $i++ ) {
        $wpdb->insert(
        // `erp_acct_taxes`
            "{$wpdb->prefix}erp_acct_taxes", [
                'tax_rate_name' => $taxes[$i]['name'],
                'tax_number'    => $taxes[$i]['tax_number'],
                'default'       => 0 === $i ? 1 : 0,           // if first record
                'created_at'    => date( 'Y-m-d' ),
                'created_by'    => $taxes[$i]['created_by']
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
                        'created_at'     => date( 'Y-m-d' ),
                        'created_by'     => $taxes[$i]['created_by']
                    ]
                );
            } // if
        } // foreach
    } // for
}

/**
 * Populate transactions data
 *
 * @return void
 */
function erp_acct_populate_transactions() {
    global $wpdb;
    global $bg_process;

    //=======================================
    // get transaction status types (new)
    //=======================================
    $status_types = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_acct_trn_status_types", ARRAY_A );

    //=============================
    // get transactions (old)
    //=============================
    $transactions = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}erp_ac_transactions", ARRAY_A );

    if ( !class_exists( '\WeDevs\ERP\Updates\BP\ERP_ACCT_BG_Process' ) || empty( $bg_process ) ) {
        $bg_process = new \WeDevs\ERP\Updates\BP\ERP_ACCT_BG_Process;
    }

    // loop through transactions
    for ( $i = 0; $i < count( $transactions ); $i++ ) {
        $bg_process->push_to_queue( $transactions[$i]['id'] );
    }

    $bg_process->save()->dispatch();
}

/**
 * Migrate Existing Employees to people
 */
function erp_employees_to_people_migration() {
    $employees = erp_hr_get_employees( array(
        'number' => '-1',
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

    $old_ledgers = [];
    $ledgers     = [];

    require_once WPERP_INCLUDES . '/ledgers.php';

    $o_ledgers = $wpdb->get_results( "SELECT
        ledger.code, ledger.id, ledger.system, chart_cat.id category_id, chart.id as chart_id, ledger.name
        FROM {$wpdb->prefix}erp_ac_ledger as ledger
        LEFT JOIN {$wpdb->prefix}erp_ac_chart_types AS chart_cat ON ledger.type_id = chart_cat.id
        LEFT JOIN {$wpdb->prefix}erp_ac_chart_classes AS chart ON chart_cat.class_id = chart.id ORDER BY chart_id", ARRAY_A );

    if ( ! empty( $o_ledgers ) ) {
        for( $i = 0; $i < count( $o_ledgers ); $i++ ) {
            if ( $o_ledgers[$i]['chart_id'] == 3 ) {
                $o_ledgers[$i]['chart_id'] = 5;
            } else if ( $o_ledgers[$i]['chart_id'] == 5 ) {
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
                'system'   => $old_ledger['system']
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
                    'system'   => $value['system']
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
                'code'     => $old_bank['code']
            ]
        );
    }

}

//=======================================
// Balance Sheet migration helpers
//=======================================

/**
 * Get closing debit and credit
 *
 * @return array
 */
function _erp_ac_get_opening_income_expense( $financial_end = false ) {
    global $wpdb;

    $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
    $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
    $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    $start_date    = $wpdb->get_var( "SELECT MIN(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );
    $financial_end = $wpdb->get_var( "SELECT MAX(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );

    $financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );

    $sql = $wpdb->prepare(
        "SELECT ledger.id, ledger.code, ledger.name, ledger.type_id, type.name as type_name, type.class_id, class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit
        FROM $tbl_class as class
        LEFT JOIN $tbl_type as type ON type.class_id = class.id
        LEFT JOIN $tbl_ledger as ledger ON ledger.type_id = type.id
        LEFT JOIN $tbl_journals as jour ON jour.ledger_id = ledger.id
        LEFT JOIN $tbl_transaction as trans ON trans.id = jour.transaction_id
        WHERE class.id IN ( 3, 4 )
        AND ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
        AND ( trans.issue_date <= '%s' ) AND trans.type = 'journal'
        GROUP BY ledger.id", $financial_end
    );

    return $wpdb->get_results( $sql );
}

/**
 * Transaction report query
 *
 * @param string $financial_end
 *
 * @return array
 */
function _erp_ac_reporting_query( $financial_end = false ) {
    global $wpdb;

    $start_date    = $wpdb->get_var( "SELECT MIN(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );
    $financial_end = $wpdb->get_var( "SELECT MAX(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );

    $unit_balance      = _erp_ac_get_asset_liability_equity_balance( $financial_end );
    $ope_in_ex_balance = _erp_ac_get_opening_income_expense( $financial_end );
    $report            = array_merge( $unit_balance, $ope_in_ex_balance );
    return $report;
}

/**
 * Get closing Balnace
 *
 * @return array
 */
function _erp_ac_get_asset_liability_equity_balance( $financial_end = false ) {
    global $wpdb;

    $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
    $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
    $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    $start_date    = $wpdb->get_var( "SELECT MIN(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );
    $financial_end = $wpdb->get_var( "SELECT MAX(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );

    $sql = $wpdb->prepare(
        "SELECT ledger.id, ledger.code, ledger.name, ledger.type_id, type.name as type_name, type.class_id, class.name as class_name, sum(jour.debit) as debit, sum(jour.credit) as credit
        FROM $tbl_class as class
        LEFT JOIN $tbl_type as type ON type.class_id = class.id
        LEFT JOIN $tbl_ledger as ledger ON ledger.type_id = type.id
        LEFT JOIN $tbl_journals as jour ON jour.ledger_id = ledger.id
        LEFT JOIN $tbl_transaction as trans ON trans.id = jour.transaction_id
        WHERE class.id IN ( 1, 2, 5 )
        AND ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
        AND ( trans.issue_date <= '%s' ) AND trans.type = 'journal'
        GROUP BY ledger.id", $financial_end
    );

    return $wpdb->get_results( $sql );
}

/**
 * Get closing debit and credit
 *
 * @return array
 */
function _erp_ac_get_closing_income_expense( $financial_end = false ) {
    global $wpdb;

    $tbl_ledger      = $wpdb->prefix . 'erp_ac_ledger';
    $tbl_type        = $wpdb->prefix . 'erp_ac_chart_types';
    $tbl_class       = $wpdb->prefix . 'erp_ac_chart_classes';
    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    $start_date    = $wpdb->get_var( "SELECT MIN(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );
    $financial_end = $wpdb->get_var( "SELECT MAX(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );

    $sql = $wpdb->prepare(
        "SELECT sum(jour.debit) as debit, sum(jour.credit) as credit
        FROM $tbl_class as class
        LEFT JOIN $tbl_type as type ON type.class_id = class.id
        LEFT JOIN $tbl_ledger as ledger ON ledger.type_id = type.id
        LEFT JOIN $tbl_journals as jour ON jour.ledger_id = ledger.id
        LEFT JOIN $tbl_transaction as trans ON trans.id = jour.transaction_id
        WHERE class.id IN ( 3, 4 )
        AND ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
        AND ( trans.issue_date < '%s' ) AND trans.type = 'journal'", $financial_end
    );

    $balance = $wpdb->get_results( $sql );
    $balance = reset( $balance );

    if ( $balance->credit > $balance->debit ) {
        $balance->credit = abs( $balance->credit - $balance->debit );
        $balance->debit  = abs( 0 );

    } else {
        if ( $balance->credit < $balance->debit ) {
            $balance->debit  = abs( $balance->debit - $balance->credit );
            $balance->credit = abs( 0 );

        } else {
            $balance->debit  = abs( 0 );
            $balance->credit = abs( 0 );
        }
    }

    return $balance;
}

/**
 * Get total sales amount without tax
 *
 * @param array $charts
 *
 * @return int
 */
function _erp_ac_get_sales_total_without_tax( $charts ) {

    $sales_journals = isset( $charts[4] ) ? $charts[4] : [];
    $sales_total    = 0;

    foreach ( $sales_journals as $key => $ledger_jours ) {
        $sales_total = $sales_total + array_sum( wp_list_pluck( $ledger_jours, 'credit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'debit' ) );
    }

    return $sales_total;
}


/**
 * Getting all tax query
 *
 * @param array $args
 *
 * @return array
 */
function _erp_ac_get_all_tax( $args = [] ) {
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}erp_ac_tax";

    $items = $wpdb->get_results( $sql );

    return $items;
}

/**
 * Create tax dropdown
 *
 * @return array
 */
function _erp_ac_get_tax_dropdown() {
    $taxs    = _erp_ac_get_all_tax( [ 'number' => '-1' ] );
    $drpdown = [];

    foreach ( $taxs as $tax ) {
        $drpdown[$tax->id] = $tax->name;
    }

    return $drpdown;
}

/**
 * Get all payable tax ledger
 *
 * @return array
 */
function _erp_ac_get_tax_payable_ledger() {
    global $wpdb;
    $all_tax_id = array_keys( _erp_ac_get_tax_dropdown() );

    $array = implode( "','", $all_tax_id );

    $payables = $wpdb->query( "CREATE TEMPORARY TABLE IF NOT EXISTS erp_acct_temp_tax_ledger as ( SELECT * FROM {$wpdb->prefix}erp_ac_ledger AS ledger WHERE tax IN ('" . $array . "') )" );

    $payables = $wpdb->get_results( "SELECT * FROM erp_acct_temp_tax_ledger as ledger LEFT JOIN {$wpdb->prefix}erp_ac_chart_types AS type ON ledger.type_id = type.id WHERE type.class_id = 2", ARRAY_A );

    foreach ( $payables as $key => $payable ) {
        if ( ! empty( $payable['charts'] ) ) {
            if ( ! count( $payable['charts'] ) ) {
                unset( $payables[$key] );
            }
        }
    }

    return $payables;
}

/**
 * Get total sales total amount
 *
 * @param array $charts
 *
 * @return int
 */
function _erp_ac_get_sales_tax_total( $charts ) {
    $payable_tax                   = _erp_ac_get_tax_payable_ledger();
    $payable_tax                   = wp_list_pluck( $payable_tax, 'id' );
    $payable_tax_journals          = [];
    $tax_total                     = 0;
    $libility_payable_tax_journals = isset( $charts[2] ) ? $charts[2] : [];

    foreach ( $libility_payable_tax_journals as $key => $libility_journal ) {
        if ( in_array( $key, $payable_tax ) ) {
            $payable_tax_journals[$key] = $libility_journal;
        }
    }

    foreach ( $payable_tax_journals as $key => $ledger_jours ) {
        $tax_total = $tax_total + array_sum( wp_list_pluck( $ledger_jours, 'credit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'debit' ) );
    }

    return $tax_total;
}


/**
 * Get cost of good sold amount
 *
 * @param string $charts
 *
 * @return int
 */
function _erp_ac_get_good_sold_total_amount( $financial_end = false ) {
    global $wpdb;

    $costs_of_goods_sold = 24;

    $start_date    = $wpdb->get_var( "SELECT MIN(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );
    $financial_end = $wpdb->get_var( "SELECT MAX(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );

    $tbl_journals    = $wpdb->prefix . 'erp_ac_journals';
    $tbl_transaction = $wpdb->prefix . 'erp_ac_transactions';

    $sql = $wpdb->prepare(
        "SELECT trans.id as transaction_id
                FROM $tbl_transaction as trans
                LEFT JOIN $tbl_journals as jour ON jour.transaction_id = trans.id
                WHERE jour.ledger_id = '%d'
                AND ( trans.status IS NULL OR trans.status NOT IN ( 'draft', 'void', 'awaiting_approval' ) )
                AND ( trans.issue_date < '%s' ) AND trans.type = 'journal'", $costs_of_goods_sold, $financial_end
    );

    $results   = $wpdb->get_results( $sql );
    $trans_ids = implode( "','", wp_list_pluck( $results, 'transaction_id' ) );

    $sql     = "SELECT sum( jour.debit ) as debit FROM $tbl_journals as jour WHERE jour.transaction_id IN ( '$trans_ids' )";
    $results = $wpdb->get_var( $sql );

    return $results;
}

/**
 * Get total expense amount without tax
 *
 * @param array $charts
 *
 * @return int
 */
function _erp_ac_get_expense_total_with_tax( $charts ) {
    $expense_journals = isset( $charts[3] ) ? $charts[3] : [];
    $expense_total    = 0;

    foreach ( $expense_journals as $key => $ledger_jours ) {
        $expense_total = $expense_total + array_sum( wp_list_pluck( $ledger_jours, 'debit' ) ) - array_sum( wp_list_pluck( $ledger_jours, 'credit' ) );
    }
    return $expense_total;

}

/**
 * Migrate balance sheet as opening balance
 *
 */
function erp_acct_migrate_balance_sheet() {
    global $wpdb;

    $ob_data = [];
    $data    = [];

    // $acct_rec_sql     = "SELECT `id`,`user_id`,`due` as debit FROM `{$wpdb->prefix}erp_ac_transactions` WHERE `form_type`='invoice' HAVING due>0";
    // $data['acct_rec'] = $wpdb->get_results( $acct_rec_sql, ARRAY_A );

    // $acct_pay_sql     = "SELECT `id`,`user_id`,`due` as credit FROM `{$wpdb->prefix}erp_ac_transactions` WHERE `form_type`='vendor_credit' HAVING due>0";
    // $data['acct_pay'] = $wpdb->get_results( $acct_pay_sql, ARRAY_A );

    $general         = get_option( 'erp_settings_general', array() );
    $financial_month = isset( $general['gen_financial_month'] ) ? $general['gen_financial_month'] : '1';

    $start_date = $wpdb->get_var( "SELECT MIN(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );

    $current_fy = erp_acct_get_current_financial_year();
    $next_fy_id = '';

    $next_fy_name  = date( "Y" ) . '_1';
    $next_fy_start = date( 'Y-m-d', strtotime( ' +1 day' ) );
    $next_fy_end   = date( 'Y-m-d', strtotime( 'Dec 31' ) );

    $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}erp_acct_financial_years" );

    if ( ! $wpdb->get_var( "SELECT id FROM `{$wpdb->prefix}erp_acct_financial_years` WHERE name='{$next_fy_name}' LIMIT 0, 1" ) ) {
        //Insert current FY

        $general         = get_option( 'erp_settings_general', array() );
        $financial_month = isset( $general['gen_financial_month'] ) ? $general['gen_financial_month'] : '1';

        $start_date = $wpdb->get_var( "SELECT MIN(issue_date) FROM {$wpdb->prefix}erp_ac_transactions LIMIT 1" );

        $end_date = date( 'Y-m-d' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_financial_years', array(
            'name'       => date( "Y" ),
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'created_at' => date( "Y-m-d" ),
            'created_by' => get_current_user_id()
        ) );

        //Next FY
        $wpdb->insert( $wpdb->prefix . 'erp_acct_financial_years', array(
            'name'       => $next_fy_name,
            'start_date' => $next_fy_start,
            'end_date'   => $next_fy_end,
            'created_at' => date( "Y-m-d" ),
            'created_by' => get_current_user_id()
        ) );
        $next_fy_id = $wpdb->insert_id;
    }

    // if ( ! empty( $data['acct_rec'] ) ) {
    //     foreach ( $data['acct_rec'] as $acct_rec ) {
    //         $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
    //             'financial_year_id' => $current_fy->id,
    //             'ledger_id'         => $acct_rec['user_id'],
    //             'type'              => 'people',
    //             'debit'             => $acct_rec['debit'],
    //             'credit'            => 0,
    //             'created_at'        => date( "Y-m-d" ),
    //             'created_by'        => get_current_user_id()
    //         ] );
    //     }
    // }

    // if ( ! empty( $data['acct_pay'] ) ) {
    //     foreach ( $data['acct_pay'] as $acct_pay ) {
    //         $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
    //             'financial_year_id' => $current_fy->id,
    //             'ledger_id'         => $acct_pay['user_id'],
    //             'type'              => 'people',
    //             'debit'             => 0,
    //             'credit'            => $acct_pay['credit'],
    //             'created_at'        => date( "Y-m-d" ),
    //             'created_by'        => get_current_user_id()
    //         ] );
    //     }
    // }

    $ledgers              = _erp_ac_reporting_query( $end_date );
    $clos_inc_exp_balance = _erp_ac_get_closing_income_expense( $end_date );

    $closing_balance = ( $clos_inc_exp_balance->credit > 0 ) ? $clos_inc_exp_balance->credit : -$clos_inc_exp_balance->debit;

    $charts = [];

    foreach ( $ledgers as $ledger ) {
        $charts[$ledger->class_id][$ledger->id][] = $ledger;
    }

    $assets      = isset( $charts[1] ) ? $charts[1] : [];
    $liabilities = isset( $charts[2] ) ? $charts[2] : [];
    $equities    = isset( $charts[5] ) ? $charts[5] : [];

    $sales_total   = _erp_ac_get_sales_total_without_tax( $charts ) + _erp_ac_get_sales_tax_total( $charts );
    $goods_sold    = _erp_ac_get_good_sold_total_amount( $end_date );
    $expense_total = _erp_ac_get_expense_total_with_tax( $charts );
    $expense_total = $expense_total - $goods_sold;
    $tax_total     = _erp_ac_get_sales_tax_total( $charts );
    $gross         = $sales_total - $goods_sold;
    $operating     = $gross - $expense_total;
    $net_income    = $operating - $tax_total;

    foreach ( $assets as $key => $asset ) {
        if ( $asset[0]->code == '120' ) {
            continue;
        }

        $asset = (array) $asset[0];

        $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
            'financial_year_id' => $current_fy->id,
            'ledger_id'         => $asset['id'],
            'chart_id'          => $asset['class_id'],
            'type'              => 'ledger',
            'debit'             => $asset['debit'],
            'credit'            => 0,
            'created_at'        => date( "Y-m-d" ),
            'created_by'        => get_current_user_id()
        ] );
    }

    foreach ( $liabilities as $key => $liability ) {
        if ( $liability[0]->code == '200' ) {
            continue;
        }

        $liability = (array) $liability[0];

        $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
            'financial_year_id' => $current_fy->id,
            'ledger_id'         => $liability['id'],
            'chart_id'          => $liability['class_id'],
            'type'              => 'ledger',
            'debit'             => 0,
            'credit'            => $liability['credit'],
            'created_at'        => date( "Y-m-d" ),
            'created_by'        => get_current_user_id()
        ] );
    }

    foreach ( $equities as $key => $equity ) {
        $equity = (array) $equity[0];

        $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
            'financial_year_id' => $current_fy->id,
            'ledger_id'         => $equity['id'],
            'chart_id'          => $equity['class_id'],
            'type'              => 'ledger',
            'debit'             => 0,
            'credit'            => $equity['credit'],
            'created_at'        => date( "Y-m-d" ),
            'created_by'        => get_current_user_id()
        ] );
    }

    $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::get_instance();
    $ledger_id  = $ledger_map->get_ledger_id_by_slug( 'owner_s_equity' );

    $profit = 0;
    $loss   = 0;

    if ( $net_income < 0 ) {
        $loss = abs( $net_income );
    } else {
        $profit = abs( $net_income );
    }

    $wpdb->insert( $wpdb->prefix . 'erp_acct_opening_balances', [
        'financial_year_id' => $current_fy->id,
        'ledger_id'         => $ledger_id,
        'chart_id'          => 3,
        'type'              => 'ledger',
        'debit'             => $loss,
        'credit'            => $profit,
        'created_at'        => date( "Y-m-d" ),
        'created_by'        => get_current_user_id()
    ] );

    $args = [
        'f_year_id'  => (int) $next_fy_id,
        'start_date' => $next_fy_start,
        'end_date'   => $next_fy_end
    ];

    erp_acct_clsbl_close_balance_sheet_now( $args );

}

/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_accounting_module_1_5_0() {
    erp_acct_create_accounting_tables();
    erp_acct_populate_charts_ledgers();
    erp_acct_populate_data();

    erp_acct_populate_tax_agencies();
    erp_acct_populate_tax_data();

    erp_acct_populate_transactions();

    erp_employees_to_people_migration();

    erp_acct_migrate_balance_sheet();
}

wperp_update_accounting_module_1_5_0();
