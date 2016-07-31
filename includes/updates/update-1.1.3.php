<?php
global $wpdb;
$table = $wpdb->prefix . 'erp_ac_transactions';
$cols = $wpdb->get_col( "DESC " . $table );

if ( ! in_array( 'invoice_prefix', $cols ) ) {
	$wpdb->query( "ALTER TABLE $table ADD `invoice_prefix` VARCHAR(20) NOT NULL AFTER `invoice_number`;");
}

$wpdb->query( "ALTER TABLE $table CHANGE `invoice_number` `invoice_number` INT(10) UNSIGNED NULL DEFAULT '0';");