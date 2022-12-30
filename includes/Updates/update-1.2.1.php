<?php
/**
 * Update ERP Transaction Table
 *
 * @since 1.2.1
 *
 * @return void
 */
function erp_accounting_update_table_1_2_1() {
    global $wpdb;

    $table = $wpdb->prefix . 'erp_ac_transaction_items';
    $cols  = $wpdb->get_col( "DESC $table" );

    if ( in_array( 'qty', $cols ) ) {
        $wpdb->query( "ALTER TABLE $table MODIFY `qty` DECIMAL (10,2)" );
    }
}

erp_accounting_update_table_1_2_1();
