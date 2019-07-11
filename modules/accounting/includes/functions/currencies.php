<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all currencies
 *
 * @return array
 */
function erp_acct_get_all_currencies() {
    global $wpdb;

    return $wpdb->get_results( "SELECT id, name, sign FROM {$wpdb->prefix}erp_acct_currency_info", ARRAY_A );
}

/**
 * Get currencies dropdown
 *
 * @return array
 */
function erp_acct_get_currencies_for_dropdown() {
    $currencies = erp_acct_get_all_currencies();

    $currencies_dropdown = [];

    foreach ( $currencies as $currency ) {
        $currencies_dropdown[$currency['id']] = $currency['name'] . ' (' . $currency['sign'] . ')';
    }

    return $currencies_dropdown;
}

/**
 * Get active currency symbol
 *
 * @return string
 */
function erp_acct_get_currency_symbol() {
    global $wpdb;

    $active_currency_id = erp_get_option( 'erp_currency', 'erp_settings_general' );

    $sql = $wpdb->prepare(
        "SELECT sign FROM {$wpdb->prefix}erp_acct_currency_info WHERE id = %d",
        absint( $active_currency_id )
    );

    return $wpdb->get_var( $sql );
}

/**
 * Get the price format depending on the currency position.
 *
 * @return string
 */
function erp_acct_get_price_format() {
    $currency_pos = erp_get_option( 'erp_ac_currency_position', false, 'left' );
    $format       = '%1$s%2$s';

    switch ( $currency_pos ) {
        case 'left':
            $format = '%1$s%2$s';
            break;
        case 'right':
            $format = '%2$s%1$s';
            break;
        case 'left_space':
            $format = '%1$s&nbsp;%2$s';
            break;
        case 'right_space':
            $format = '%2$s&nbsp;%1$s';
            break;
    }

    return apply_filters( 'erp_acct_price_format', $format, $currency_pos );
}
