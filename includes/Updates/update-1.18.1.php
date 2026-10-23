<?php

/*
 * Add the missing `forward_to` column to `erp_hr_leave_approval_status`.
 *
 * ERP Pro's Advanced Leave multilevel approval has always written a `forward_to`
 * value when a leave request is forwarded, and both its AJAX handler and its v2
 * approval-chain endpoint read `$approval->leave_forward_to->display_name` back.
 * The column was never in any schema, so the write was silently dropped and the
 * "Forwarded To" column rendered `-` in every install. The model gained the
 * relation in 1.18.1; this gives it something to point at.
 */
function erp_hr_add_forward_to_column_1_18_1() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_hr_leave_approval_status';
    $cols  = $wpdb->get_col( "DESC {$table}", 0 );

    if ( is_array( $cols ) && ! in_array( 'forward_to', $cols, true ) ) {
        $wpdb->query( "ALTER TABLE `{$table}` ADD `forward_to` bigint(20) UNSIGNED DEFAULT NULL AFTER `approved_by`" );
    }
}

erp_hr_add_forward_to_column_1_18_1();
