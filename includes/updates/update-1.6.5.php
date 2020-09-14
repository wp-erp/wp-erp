<?php
namespace WeDevs\ERP\HRM\Update;

function erp_acct_alter_decimal_1_6_5() {
    global $wpdb;

    $tables = array(
        'erp_acct_bill_details'             => array( 'amount' ),
        'erp_acct_bills'                    => array( 'amount' ),
        'erp_acct_invoice_account_details'  => array( 'debit', 'credit' ),
        'erp_acct_bill_account_details'     => array( 'debit', 'credit' ),
        'erp_acct_invoice_details'          => array( 'unit_price', 'discount', 'tax', 'item_total' ),
        'erp_acct_invoice_receipts'         => array( 'amount' ),
        'erp_acct_invoice_receipts_details' => array( 'amount' ),
        'erp_acct_invoices'                 => array( 'amount', 'discount', 'tax ' ),
        'erp_acct_journal_details'          => array( 'debit', 'credit' ),
        'erp_acct_journals'                 => array( 'voucher_amount' ),
        'erp_acct_ledger_details'           => array( 'debit', 'credit' ),
        'erp_acct_cash_at_banks'            => array( 'balance' ),
        'erp_acct_transfer_voucher'         => array( 'amount' ),
        'erp_acct_opening_balances'         => array( 'debit', 'credit' ),
        'erp_acct_pay_bill'                 => array( 'amount' ),
        'erp_acct_pay_bill_details'         => array( 'amount' ),
        'erp_acct_pay_purchase'             => array( 'amount' ),
        'erp_acct_pay_purchase_details'     => array( 'amount' ),
        'erp_acct_people_account_details'   => array( 'debit', 'credit' ),
        'erp_acct_people_trn'               => array( 'amount' ),
        'erp_acct_people_trn_details'       => array( 'debit', 'credit' ),
        'erp_acct_products'                 => array( 'cost_price', 'sale_price' ),
        'erp_acct_purchase'                 => array( 'amount' ),
        'erp_acct_purchase_account_details' => array( 'debit', 'credit' ),
        'erp_acct_purchase_details'         => array( 'price', 'amount' ),
        'erp_acct_tax_cat_agency'           => array( 'tax_rate' ),
        'erp_acct_tax_pay'                  => array( 'amount' ),
        'erp_acct_tax_agency_details'       => array( 'debit', 'credit' ),
        'erp_acct_invoice_details_tax'      => array( 'tax_rate', 'tax_amount' ),
        'erp_acct_expenses'                 => array( 'amount' ),
        'erp_acct_expense_details'          => array( 'amount' ),
        'erp_acct_expense_checks'           => array( 'amount' ),
    );

    foreach ( $tables as $table_name => $tale_fields ) {
        $table_name     = $wpdb->prefix . $table_name;
        $field_sql_txt  = "";
        foreach ( $tale_fields as $tale_field ) {
            $field_sql_txt .= "CHANGE {$tale_field} {$tale_field} DECIMAL(20,2) NULL DEFAULT '0.00',";
        }
        $field_sql_txt  = rtrim( $field_sql_txt, "," );
        $sql            = "ALTER TABLE {$table_name} {$field_sql_txt};";
        $wpdb->query( $sql );
    }
}

function erp_alter_acct_product_categories() {
    global $wpdb;
    $wpdb->query( "ALTER TABLE {$wpdb->prefix}erp_acct_product_categories CHANGE parent parent INT(11) NOT NULL DEFAULT '0';" );
}

erp_acct_alter_decimal_1_6_5();
erp_alter_acct_product_categories();
