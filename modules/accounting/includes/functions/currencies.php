<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all currencies
 *
 * @return mixed
 */
function erp_acct_get_all_currencies( $count = false ) {
    global $wpdb;

    if ( $count ) {
        return $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}erp_acct_currency_info" );
    }

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
        $currencies_dropdown[ $currency['id'] ] = $currency['name'] . ' (' . $currency['sign'] . ')';
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

    $active_currency_id = erp_get_currency(true);

    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT sign FROM {$wpdb->prefix}erp_acct_currency_info WHERE id = %d",
            absint( $active_currency_id )
        )
    );
}

/**
 * Get the price format depending on the currency position.
 *
 * Use it for JavaScript
 *
 * @return string
 */
function erp_acct_get_price_format() {
    $currency_pos = erp_get_option( 'erp_ac_currency_position', false, 'left' );
    $format       = '%s%v';

    switch ( $currency_pos ) {
        case 'left':
            $format = '%s%v';
            break;
        case 'right':
            $format = '%v%s';
            break;
        case 'left_space':
            $format = '%s&nbsp;%v';
            break;
        case 'right_space':
            $format = '%v&nbsp;%s';
            break;
    }

    return apply_filters( 'erp_acct_price_format', $format, $currency_pos );
}

/**
 * Get the price format depending on the currency position.
 *
 * Use it for PHP
 *
 * @return string
 */
function erp_acct_get_price_format_php() {
    $currency_pos = erp_get_option( 'erp_ac_currency_position', false, 'left' );
    $format       = '%1$s%2$s';

    switch ($currency_pos) {
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

    return apply_filters( 'erp_acct_price_format_php', $format, $currency_pos );
}

/**
 * Format the price with a currency symbol.
 *
 * @param float $price
 *
 * @param array $args (default: array())
 *
 * @return string
 */
function erp_acct_get_price( $main_price, $args = array() ) {
    extract(
        apply_filters(
            'erp_acct_price_args',
            wp_parse_args(
                $args,
                array(
					'currency'           => erp_get_currency(),
					'decimal_separator'  => erp_get_option( 'erp_ac_de_separator', false, '.' ),
					'thousand_separator' => erp_get_option( 'erp_ac_th_separator', false, ',' ),
					'decimals'           => absint( erp_get_option( 'erp_ac_nm_decimal', false, 2 ) ),
					'price_format'       => erp_acct_get_price_format_php(),
					'symbol'             => true,
					'currency_symbol'    => erp_acct_get_currency_symbol(),
                )
            )
        )
    );

    $price           = number_format( abs( $main_price ), $decimals, $decimal_separator, $thousand_separator );
    $formatted_price = $symbol ? sprintf( $price_format, $currency_symbol, $price ) : $price;
    $formatted_price = ( $main_price < 0 ) ? '(' . $formatted_price . ')' : $formatted_price;

    return apply_filters( 'erp_acct_price', $formatted_price, $price, $args );
}
