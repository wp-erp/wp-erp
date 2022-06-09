<?php

/**
 * Class to handle updates for version 1.11.0
 *
 * @since 1.11.0
 */
class ERP_Update_1_11_0 {

    /**
     * Class constructor.
     *
     * @since 1.11.0
     */
    public function __construct() {
        $this->modify_qty_columns();
    }

    /**
     * Modifies `qty` columns in different tables.
     *
     * @since 1.11.0
     *
     * @return void
     */
    private function modify_qty_columns() {
        global $wpdb;

        $tables = [
            'erp_acct_invoice_details',
            'erp_acct_purchase_details'
        ];

        foreach ( $tables as $table ) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}{$table} MODIFY COLUMN qty decimal(10,2) DEFAULT NULL" );
        }
    }
}

new ERP_Update_1_11_0();
