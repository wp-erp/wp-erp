<?php

/*
 * Modify result column in `erp_hr_education` table
 */
function erp_acct_alter_table_erp_hr_education_1_8_5() {
    global $wpdb;

    $cols = $wpdb->get_col( "DESC {$wpdb->prefix}erp_hr_education" );

    if ( in_array( 'result', $cols ) ) {
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}erp_hr_education` MODIFY `result` VARCHAR(50);" );
    }
}

erp_acct_alter_table_erp_hr_education_1_8_5();
