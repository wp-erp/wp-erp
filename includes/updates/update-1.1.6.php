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
	$table_name = $wpdb->prefix . 'erp_ac_transaction_items';
	$wpdb->query( "ALTER TABLE $table_name CHANGE `qty` `qty` int(11) unsigned NOT NULL DEFAULT '1';" );
}

erp_ac_update_transaction_item_table_1_1_6();