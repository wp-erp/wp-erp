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

        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_acct_tax_sales_tax_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tax_id` int(11) DEFAULT NULL,
            `sales_tax_category_id` int(11) DEFAULT NULL,
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
 * Get previous accounting data
 *
 * @return array
 */
function erp_acct_populate_tax_data() {
    global $wpdb;

    //=============================
    // first get previous tax info
    //=============================
    $taxes     = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}erp_ac_tax");
    $tax_items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}erp_ac_tax_items");

    error_log(print_r($taxes, true));
    error_log('-------------------');
    error_log(print_r($tax_items, true));
}



/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_accounting_module_1_5_0() {
    erp_acct_create_accounting_tables();

    erp_acct_populate_tax_data();
}

wperp_update_accounting_module_1_5_0();
