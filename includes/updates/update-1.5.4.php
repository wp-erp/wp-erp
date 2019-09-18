<?php

/**
 * Regenerate all transaction pdfs
 *
 * @return void
 */
function erp_acct_updater_regenerate_transaction_pdfs() {
    global $wpdb;

    $voucher_nos = $wpdb->get_results( "SELECT id, type FROM {$wpdb->prefix}erp_acct_voucher_no", ARRAY_A );

    for ( $i = 0; $i < count( $voucher_nos ); $i++ ) {

        if ( $voucher_nos[$i]['type'] == 'journal' ) {
            continue;
        }

        $transaction = erp_acct_get_transaction( $voucher_nos[$i]['id'] );
        $filename    = erp_acct_get_pdf_filename( $voucher_nos[$i]['id'] );
        erp_acct_generate_pdf( [], $transaction, $filename, 'F' );
    }
}


/**
 * Call other function related to this update
 *
 * @return void
 */
function wperp_update_accounting_module_1_5_4() {
    erp_acct_updater_regenerate_transaction_pdfs();
}

wperp_update_accounting_module_1_5_4();
