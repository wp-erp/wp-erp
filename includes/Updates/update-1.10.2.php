<?php
/**
 * Modify attachments column from `varchar(255)` to `text` in whole Accounting module.
 *
 * Tables: erp_acct_pay_bill, erp_acct_pay_purchase, erp_acct_purchase, erp_acct_expenses
 *         erp_acct_bills, erp_acct_invoice_receipts, erp_acct_invoices, erp_acct_journals
 */
function erp_acct_tables_modify_attachments_column_1_10_2() {
    global $wpdb;

    $table_names = [
        'erp_acct_pay_bill',
        'erp_acct_pay_purchase',
        'erp_acct_purchase',
        'erp_acct_expenses',
        'erp_acct_bills',
        'erp_acct_invoice_receipts',
        'erp_acct_invoices',
        'erp_acct_journals',
    ];

    // Change `attachments` column to `text` in all tables.
    foreach ( $table_names as $table_name ) {
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}{$table_name}
            MODIFY `attachments` TEXT DEFAULT NULL"
        );
    }
}

erp_disable_mysql_strict_mode();
erp_acct_tables_modify_attachments_column_1_10_2();
