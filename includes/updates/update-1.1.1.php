<?php
global $wpdb;

$table = $wpdb->prefix . 'erp_ac_transactions';
$cols = $wpdb->get_col( "DESC " . $table );

if ( ! in_array( 'invoice', $cols ) ) {
    $wpdb->query( "ALTER TABLE $table ADD `invoice` varchar(200) NOT NULL AFTER `trans_total`" );
}