<?php
namespace WeDevs\ERP\HRM\Update;


/*
 * Add transaction_charge column in `erp_acct_expenses` table
 */
function erp_acct_alter_acct_expenses_1_6_8() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_expenses';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( !in_array( 'transaction_charge', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `transaction_charge` decimal(20,2) DEFAULT 0 AFTER `trn_by`;"
            )
        );
    }

}


function crate_erp_acct_sales_return_table_1_6_8() {

    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_acct_sales_return (
              `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `invoice_id` int(20) NOT NULL,
              `voucher_no` int(20) NOT NULL,
              `customer_id` int(20) DEFAULT NULL,
              `customer_name` varchar(255) DEFAULT NULL,
              `trn_date` date  NOT NULL,
              `amount` decimal(20,2) NOT NULL,
              `discount` decimal(20,2) DEFAULT 0,
              `discount_type` varchar(255) DEFAULT NULL,
              `tax` decimal(20,2) DEFAULT 0,
              `reason` text DEFAULT NULL,
              `comments` text DEFAULT NULL,
              `created_at` timestamp DEFAULT NULL,
              `created_by` int(20) DEFAULT NULL,
              `updated_at` timestamp DEFAULT NULL,
              `updated_by` int(20) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8";

    dbDelta($sql);
}


function crate_erp_return_details_table_1_6_8() {

    global $wpdb;
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_acct_sales_return_details (
              `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `trn_no` int(20) NOT NULL,
              `product_id` int(20) NOT NULL,
              `qty` int(20) NOT NULL,
              `unit_price` decimal(20,2) NOT NULL,
              `discount` decimal(20,2) DEFAULT 0,
              `tax` decimal(20,2) DEFAULT 0,
              `item_total` decimal(20,2) NOT NULL,
              `ecommerce_type` varchar(255) DEFAULT NULL,
              `created_at` timestamp DEFAULT NULL,
              `created_by` int(20) DEFAULT NULL,
              `updated_at` timestamp DEFAULT NULL,
              `updated_by` int(20) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8";

    dbDelta($sql);
}


function erp_acct_insert_to_erp_acct_ledgers_1_6_8() {
    global $wpdb;

    $checkSalesReturn = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'sales_return' ]
        )
    );

    if ( empty( $checkSalesReturn ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 5, 'Sales Return', 'sales_return', '1509', 1, date( 'Y-m-d' ) ]
            )
        );
    }


    $checkSalesReturnDiscount = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'sales_return_discount' ]
        )
    );

    if ( empty( $checkSalesReturnDiscount ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Sales Return Discount', 'sales_return_discount', '1406', 1, date( 'Y-m-d' ) ]
            )
        );
    }

    $checkSalesReturnTax = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'sales_return_tax' ]
        )
    );

    if ( empty( $checkSalesReturnDiscount ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Sales Return Tax', 'sales_return_tax', '1407', 1, date( 'Y-m-d' ) ]
            )
        );
    }
}


erp_acct_alter_acct_expenses_1_6_8();
crate_erp_acct_sales_return_table_1_6_8();
crate_erp_return_details_table_1_6_8();
erp_acct_insert_to_erp_acct_ledgers_1_6_8();
