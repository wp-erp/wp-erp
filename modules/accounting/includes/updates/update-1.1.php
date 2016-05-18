<?php
global $wpdb;

$table = $wpdb->prefix . 'erp_ac_transactions';

$cols = $wpdb->get_col( "DESC " . $table );

if ( ! in_array( 'sub_total', $cols ) ) {
	$wpdb->query( "ALTER TABLE $table ADD `sub_total` DECIMAL(10,2) NOT NULL AFTER `conversion_rate`" );	
}

$item_table = $wpdb->prefix . 'erp_ac_transaction_items';
$item_cols  = $wpdb->get_col( "DESC " . $item_table );

if ( ! in_array( 'tax_rate',$item_cols ) ) {
	$wpdb->query( "ALTER TABLE $item_table ADD `tax_rate` DECIMAL(10,2) NOT NULL AFTER `tax`" );	
}

if ( ! in_array( 'tax_journal', $item_cols ) ) {
	$wpdb->query( "ALTER TABLE $item_table ADD `tax_journal` BIGINT(20) NOT NULL AFTER `tax_rate`" );	
}

$account_table = $wpdb->prefix . 'erp_ac_banks';

$wpdb->update( $account_table, array( 'ledger_id' => 62 ), array( 'id' => 2, 'ledger_id' => 60 ), array( '%d' ), array( '%d', '%d' ) );
