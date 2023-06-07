<?php

/**
 * Class to handle updates for version 1.11.0
 *
 * @since 1.11.0
 */
class ERP_Update_1_12_6 {

    /**
     * Class constructor.
     *
     * @since 1.11.0
     */
    public function __construct() {
        $this->add_column_to_erp_acct_invoices();
    }

    /**
     * Modifies `qty` columns in different tables.
     *
     * @since 1.11.0
     *
     * @return void
     */
    private function add_column_to_erp_acct_invoices() {
        global $wpdb;

        // Don't modify table, if already modified.
        if ( $this->is_column_already_exists() ) {
            return;
        }

        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}erp_acct_invoices` ADD `additional_notes` TEXT NULL DEFAULT NULL AFTER `particulars`;" );
    }

    /**
     * Check if qty column is already modified.
     *
     * @since 1.11.0
     *
     * @return bool
     */
    private function is_column_already_exists() {
        global $wpdb;

        $result = $wpdb->get_results( "SHOW COLUMNS FROM `{$wpdb->prefix}erp_acct_invoices` LIKE 'additional_notes'" );

        if ( ! empty( $result ) ) {
            return true;
        }

        return false;
	}
}

new ERP_Update_1_12_6();
