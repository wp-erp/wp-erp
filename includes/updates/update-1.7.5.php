<?php
namespace WeDevs\ERP\HRM\Update;

/**
 * Insert necessary ledgers for purchase return
 */
function erp_acct_insert_to_erp_acct_ledgers_1_7_5() {
    global $wpdb;

    $purchase_return_exists  = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return' ]
        )
    );

    if ( empty( $purchase_return_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Purchase Return', 'purchase_return', '1408', 1, date( 'Y-m-d' ) ]
            )
        );
    }

    $purchase_return_tax_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return_tax' ]
        )
    );

    if ( empty( $purchase_return_tax_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 4, 'Purchase Return Tax', 'purchase_return_tax', '1409', 1, date( 'Y-m-d' ) ]
            )
        );
    }

    $purchase_return_discount_exists = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_ledgers WHERE slug = %s", [ 'purchase_return_discount' ]
        )
    );

    if ( empty( $purchase_return_discount_exists ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}erp_acct_ledgers ( `chart_id`, `name`, `slug`, `code`, `system`, `created_at` ) VALUES ( %d, %s, %s, %s, %d, %s )",
                [ 5, 'Purchase Return Discount', 'purchase_return_discount', '1410', 1, date( 'Y-m-d' ) ]
            )
        );
    }
}

erp_acct_insert_to_erp_acct_ledgers_1_7_5();