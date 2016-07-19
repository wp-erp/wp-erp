<?php
global $wpdb;
$table = $wpdb->prefix . 'erp_ac_transactions';
$wpdb->query( "ALTER TABLE $table 
	CHANGE `sub_total` `sub_total` DECIMAL(13,4) NULL DEFAULT '0.00', 
	CHANGE `total` `total` DECIMAL(13,4) NULL DEFAULT '0.00', 
	CHANGE `due` `due` DECIMAL(13,4) UNSIGNED NULL DEFAULT '0.00', 
	CHANGE `trans_total` `trans_total` DECIMAL(13,4) NULL DEFAULT '0.00';" 
);
