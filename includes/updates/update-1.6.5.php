<?php

namespace WeDevs\ERP\HRM\Update;

function erp_acct_alter_decimal_1_6_5() {
    $tables = [
        [
            'table'     => 'erp_acct_bill_account_details',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_bill_details',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_bills',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_invoice_account_details',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_invoice_details',
            'fields'    => [ 'unit_price', 'discount', 'tax', 'item_total' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_invoice_receipts',
            'fields'    => [ 'amount', 'transaction_charge' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_invoice_receipts_details',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_invoices',
            'fields'    => [ 'amount', 'discount', 'tax' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_journal_details',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_journals',
            'fields'    => [ 'voucher_amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_ledger_details',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_cash_at_banks',
            'fields'    => [ 'balance' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_transfer_voucher',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_opening_balances',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_pay_bill',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_pay_bill_details',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_pay_purchase',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_pay_purchase_details',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_people_account_details',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_people_trn',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_people_trn_details',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_products',
            'fields'    => [ 'cost_price', 'sale_price' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_purchase',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_purchase_account_details',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_purchase_details',
            'fields'    => [ 'price', 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_tax_cat_agency',
            'fields'    => [ 'tax_rate' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_tax_pay',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_tax_agency_details',
            'fields'    => [ 'debit', 'credit' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_invoice_details_tax',
            'fields'    => [ 'tax_rate', 'tax_amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_expenses',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_expense_details',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_expense_checks',
            'fields'    => [ 'amount' ],
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00',
        ],

        [
            'table'     => 'erp_acct_product_categories',
            'fields'    => [ 'parent' ],
            'type'      => 'int(11)',
            'null'      => 'NOT NULL',
            'default'   => '0',
        ],
    ];

    global $erp_bg_process_1_6_5;

    foreach ( $tables as $table_data ) {
        $erp_bg_process_1_6_5->push_to_queue( $table_data );
    }

    $erp_bg_process_1_6_5->save();

    $erp_bg_process_1_6_5->dispatch();
}

function erp_acct_alter_audit_log_1_6_5() {
    global $wpdb;

    // Add hash column in `wp_erp_acct_expenses` table
    $table = $wpdb->prefix . 'erp_audit_log';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( ! in_array( 'data_id', $cols ) ) {
        $wpdb->query(
            "ALTER TABLE $table ADD data_id bigint(20) DEFAULT NULL AFTER sub_component;"
        );
    }
}

erp_acct_alter_audit_log_1_6_5();
erp_acct_alter_decimal_1_6_5();
