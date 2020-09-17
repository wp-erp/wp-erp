<?php
namespace WeDevs\ERP\HRM\Update;

function erp_acct_alter_decimal_1_6_5() {
    global $wpdb;

    $tables = array(
        array(
            'table'     => 'erp_acct_bill_account_details',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_bill_details',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_bills',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_invoice_account_details',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_invoice_details',
            'fields'    => array( 'unit_price', 'discount', 'tax', 'item_total' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_invoice_receipts',
            'fields'    => array( 'amount', 'transaction_charge' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_invoice_receipts_details',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_invoices',
            'fields'    => array( 'amount', 'discount', 'tax' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_journal_details',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_journals',
            'fields'    => array( 'voucher_amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_ledger_details',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_cash_at_banks',
            'fields'    => array( 'balance' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_transfer_voucher',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_opening_balances',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_pay_bill',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_pay_bill_details',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_pay_purchase',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_pay_purchase_details',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_people_account_details',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_people_trn',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_people_trn_details',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_products',
            'fields'    => array( 'cost_price', 'sale_price' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_purchase',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_purchase_account_details',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_purchase_details',
            'fields'    => array( 'price', 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_tax_cat_agency',
            'fields'    => array( 'tax_rate' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_tax_pay',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_tax_agency_details',
            'fields'    => array( 'debit', 'credit' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_invoice_details_tax',
            'fields'    => array( 'tax_rate', 'tax_amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_expenses',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_expense_details',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_expense_checks',
            'fields'    => array( 'amount' ),
            'type'      => 'decimal(20,2)',
            'null'      => 'NULL',
            'default'   => '0.00'
        ),

        array(
            'table'     => 'erp_acct_product_categories',
            'fields'    => array( 'parent' ),
            'type'      => 'int(11)',
            'null'      => 'NOT NULL',
            'default'   => '0'
        ),
    );

    global $erp_bg_process_1_6_5;

    foreach ( $tables as $table_data ) {
        $erp_bg_process_1_6_5->push_to_queue( $table_data );
    }

    $erp_bg_process_1_6_5->save();

    $erp_bg_process_1_6_5->dispatch();
}

erp_acct_alter_decimal_1_6_5();
