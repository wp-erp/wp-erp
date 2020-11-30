<?php
namespace WeDevs\ERP\HRM\Update;



function crate_erp_acct_sales_return_table_1_7_0() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_acct_purchase_return (
              `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `invoice_id` int(20) NOT NULL,
              `voucher_no` int(20) NOT NULL,
              `vendor_id` int(20) DEFAULT NULL,
              `vendor_name` varchar(255) DEFAULT NULL,
              `trn_date` date  NOT NULL,
              `amount` decimal(20,2) NOT NULL,
              `discount` decimal(20,2) DEFAULT 0,
              `discount_type` varchar(255) DEFAULT NULL,
              `tax` decimal(20,2) DEFAULT 0,
              `reason` text DEFAULT NULL,
              `comments` text DEFAULT NULL,
              `status` int(20) DEFAULT NULL COMMENT '0 means drafted, 1 means confirmed return',
              `created_at` timestamp DEFAULT NULL,
              `created_by` int(20) DEFAULT NULL,
              `updated_at` timestamp DEFAULT NULL,
              `updated_by` int(20) DEFAULT NULL,
              PRIMARY KEY  (`id`)
            ) DEFAULT $charset_collate";

    dbDelta($sql);
}


function crate_erp_return_details_table_1_7_0() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_acct_purchase_return_details (
              `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `invoice_details_id` int(20) NOT NULL,
              `trn_no` int(20) NOT NULL,
              `product_id` int(20) NOT NULL,
              `qty` int(20) NOT NULL,
              `price` decimal(20,2) NOT NULL,
              `discount` decimal(20,2) DEFAULT 0,
              `tax` decimal(20,2) DEFAULT 0,
              `created_at` timestamp DEFAULT NULL,
              `created_by` int(20) DEFAULT NULL,
              `updated_at` timestamp DEFAULT NULL,
              `updated_by` int(20) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT $charset_collate";

    dbDelta($sql);
}


function erp_acct_insert_to_erp_acct_ledgers_1_7_0() {
    global $wpdb;

    $checkPurchaseReturn  = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return' ]
        )
    );

    if ( empty( $checkPurchaseReturn ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Purchase Return', 'purchase_return', '1408', 1, date( 'Y-m-d' ) ]
            )
        );
    }

    $checkPurchaseReturnTax = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return_tax' ]
        )
    );

    if ( empty( $checkPurchaseReturnTax ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Purchase Return Tax', 'purchase_return_tax', '1409', 1, date( 'Y-m-d' ) ]
            )
        );
    }

    $checkPurchaseReturnDiscount = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return_discount' ]
        )
    );

    if ( empty( $checkPurchaseReturnDiscount ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 5, 'Purchase Return Discount', 'purchase_return_discount', '1410', 1, date( 'Y-m-d' ) ]
            )
        );
    }
}

crate_erp_acct_sales_return_table_1_7_0();
crate_erp_return_details_table_1_7_0();
erp_acct_insert_to_erp_acct_ledgers_1_7_0();
