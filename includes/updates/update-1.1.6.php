<?php
/**
 * Transaction item tables quantity column type change from tinyint(5) to int(11)
 *
 * @since  1.1.6
 *
 * @return void
 */
function erp_ac_update_transaction_item_table_1_1_6() {
    global $wpdb;
    $transaction_items = $wpdb->prefix . 'erp_ac_transaction_items';
    $wpdb->query( "ALTER TABLE $transaction_items CHANGE `qty` `qty` int(11) unsigned NOT NULL DEFAULT '1';" );

    $announcements = $wpdb->prefix . 'erp_hr_announcement';
    $wpdb->query( "ALTER TABLE $announcements ADD `email_status` VARCHAR(30) NOT NULL AFTER `status`;" );
}

erp_ac_update_transaction_item_table_1_1_6();