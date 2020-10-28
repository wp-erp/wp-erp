<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all invoices
 *
 * @return mixed
 */
function erp_acct_get_sales_voucher( $args = [] ) {
    global $wpdb;
    $sql = '' ;
    return $wpdb->get_results( $sql, ARRAY_A );
}
