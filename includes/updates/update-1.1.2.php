<?php

global $wpdb;
$table    = $wpdb->prefix . 'erp_ac_transactions';
$items    = $wpdb->prefix . 'erp_ac_transaction_items';
$journals = $wpdb->prefix . 'erp_ac_journals';

$wpdb->query( "ALTER TABLE $table
	CHANGE `sub_total` `sub_total` DECIMAL(13,4) NULL DEFAULT '0.00',
	CHANGE `total` `total` DECIMAL(13,4) NULL DEFAULT '0.00',
	CHANGE `due` `due` DECIMAL(13,4) UNSIGNED NULL DEFAULT '0.00',
	CHANGE `trans_total` `trans_total` DECIMAL(13,4) NULL DEFAULT '0.00';"
);

$wpdb->query( "ALTER TABLE $items
	CHANGE `unit_price` `unit_price` DECIMAL(13,4) NULL DEFAULT '0.00',
	CHANGE `tax_rate` `tax_rate` DECIMAL(13,4) NULL DEFAULT '0.00',
	CHANGE `line_total` `line_total` DECIMAL(13,4) UNSIGNED NULL DEFAULT '0.00';"
);

$wpdb->query( "ALTER TABLE $journals
	CHANGE `debit` `debit` DECIMAL(13,4) NULL DEFAULT '0.00',
	CHANGE `credit` `credit` DECIMAL(13,4) NULL DEFAULT '0.00';"
);
