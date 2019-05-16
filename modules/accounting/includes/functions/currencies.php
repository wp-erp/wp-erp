<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all currencies
 *
 * @return array
 */
function erp_acct_get_all_currencies () {
    global $wpdb;

    return $wpdb->get_results( "SELECT id, name, sign FROM {$wpdb->prefix}erp_acct_currency_info", ARRAY_A );
}

/**
 * Get currencies dropdown
 *
 * @return array
 */
function erp_acct_get_currencies_for_dropdown () {
    $currencies = erp_acct_get_all_currencies();

    $currencies_dropdown = [];

    foreach ( $currencies as $currency ) {
        $currencies_dropdown[ $currency['id'] ] = $currency['name'] . ' (' . $currency['sign'] . ')';
    }

    return $currencies_dropdown;
}

/**
 * Get active currency symbol
 *
 * @return string
 */
function erp_acct_get_currency_symbol () {
    global $wpdb;

    $active_currency_id = erp_get_option( 'erp_currency', 'erp_settings_general' );

    $sql = $wpdb->prepare(
        "SELECT sign FROM {$wpdb->prefix}erp_acct_currency_info WHERE id = %d",
        absint( $active_currency_id )
    );

    return $wpdb->get_var( $sql );
}
