<?php
namespace WeDevs\ERP\HRM\Update;
/*
 * Add transaction_charge column in `erp_acct_pay_bill` table
 */
function erp_acct_alter_purchase_1_6_9() {
    global $wpdb;

    $cols  = $wpdb->get_col( "DESC {$wpdb->prefix}erp_acct_purchase" );

    if ( !in_array( 'tax', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$wpdb->prefix}erp_acct_purchase ADD `tax` decimal(20,2)  DEFAULT NULL  AFTER `amount`;"
            )
        );
    }

    if ( !in_array( 'tax_zone_id', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE {$wpdb->prefix}erp_acct_purchase  ADD `tax_zone_id` integer  DEFAULT NULL  AFTER `tax`;"
            )
        );
    }


}

/*
 * Add transaction_charge column in `erp_acct_pay_bill` table
 */
function erp_acct_alter_purchase_details_1_6_9() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_acct_purchase_details';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( !in_array( 'tax', $cols ) ) {
        $wpdb->query(
            $wpdb->prepare(
                "ALTER TABLE $table ADD `tax` decimal(20,2)  NULL DEFAULT NULL  AFTER `amount`;"
            )
        );
    }


}


function crate_erp_acct_purchase_details_tax_table_1_6_9() {

    global $wpdb;
    $charset_collate  = $wpdb->get_charset_collate() ;
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}erp_acct_purchase_details_tax (
              `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `invoice_details_id` int(20) NOT NULL,
              `agency_id` int(20) DEFAULT NULL,
              `tax_rate` decimal(20,2) NOT NULL,
              `created_at` timestamp DEFAULT NULL,
              `created_by` int(20) DEFAULT NULL,
              `updated_at` timestamp DEFAULT NULL,
              `updated_by` int(20) DEFAULT NULL,
              PRIMARY KEY  (`id`)
            ) DEFAULT $charset_collate";

    dbDelta($sql);
}


erp_acct_alter_purchase_1_6_9();
erp_acct_alter_purchase_details_1_6_9();
crate_erp_acct_purchase_details_tax_table_1_6_9();
