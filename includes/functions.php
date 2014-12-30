<?php

/**
 * Processes all ERP actions sent via REQUEST by looking for the 'erp-action'
 * request and running do_action() to call the function
 *
 * @return void
 */
function erp_process_actions() {
    if ( isset( $_REQUEST['erp-action'] ) ) {
        do_action( 'erp_action_' . $_REQUEST['erp-action'], $_REQUEST );
    }
}

add_action( 'admin_init', 'erp_process_actions' );

/**
 * Get full list of currency codes.
 *
 * @return array
 */
function erp_get_currencies() {
    return array_unique( apply_filters( 'erp_currencies', array(
        'AED' => __( 'United Arab Emirates Dirham', 'wp-erp' ),
        'AUD' => __( 'Australian Dollars', 'wp-erp' ),
        'BDT' => __( 'Bangladeshi Taka', 'wp-erp' ),
        'BRL' => __( 'Brazilian Real', 'wp-erp' ),
        'BGN' => __( 'Bulgarian Lev', 'wp-erp' ),
        'CAD' => __( 'Canadian Dollars', 'wp-erp' ),
        'CLP' => __( 'Chilean Peso', 'wp-erp' ),
        'CNY' => __( 'Chinese Yuan', 'wp-erp' ),
        'COP' => __( 'Colombian Peso', 'wp-erp' ),
        'CZK' => __( 'Czech Koruna', 'wp-erp' ),
        'DKK' => __( 'Danish Krone', 'wp-erp' ),
        'DOP' => __( 'Dominican Peso', 'wp-erp' ),
        'EUR' => __( 'Euros', 'wp-erp' ),
        'HKD' => __( 'Hong Kong Dollar', 'wp-erp' ),
        'HRK' => __( 'Croatia kuna', 'wp-erp' ),
        'HUF' => __( 'Hungarian Forint', 'wp-erp' ),
        'ISK' => __( 'Icelandic krona', 'wp-erp' ),
        'IDR' => __( 'Indonesia Rupiah', 'wp-erp' ),
        'INR' => __( 'Indian Rupee', 'wp-erp' ),
        'NPR' => __( 'Nepali Rupee', 'wp-erp' ),
        'ILS' => __( 'Israeli Shekel', 'wp-erp' ),
        'JPY' => __( 'Japanese Yen', 'wp-erp' ),
        'KIP' => __( 'Lao Kip', 'wp-erp' ),
        'KRW' => __( 'South Korean Won', 'wp-erp' ),
        'MYR' => __( 'Malaysian Ringgits', 'wp-erp' ),
        'MXN' => __( 'Mexican Peso', 'wp-erp' ),
        'NGN' => __( 'Nigerian Naira', 'wp-erp' ),
        'NOK' => __( 'Norwegian Krone', 'wp-erp' ),
        'NZD' => __( 'New Zealand Dollar', 'wp-erp' ),
        'PYG' => __( 'Paraguayan Guaraní', 'wp-erp' ),
        'PHP' => __( 'Philippine Pesos', 'wp-erp' ),
        'PLN' => __( 'Polish Zloty', 'wp-erp' ),
        'GBP' => __( 'Pounds Sterling', 'wp-erp' ),
        'RON' => __( 'Romanian Leu', 'wp-erp' ),
        'RUB' => __( 'Russian Ruble', 'wp-erp' ),
        'SGD' => __( 'Singapore Dollar', 'wp-erp' ),
        'ZAR' => __( 'South African rand', 'wp-erp' ),
        'SEK' => __( 'Swedish Krona', 'wp-erp' ),
        'CHF' => __( 'Swiss Franc', 'wp-erp' ),
        'TWD' => __( 'Taiwan New Dollars', 'wp-erp' ),
        'THB' => __( 'Thai Baht', 'wp-erp' ),
        'TRY' => __( 'Turkish Lira', 'wp-erp' ),
        'USD' => __( 'US Dollars', 'wp-erp' ),
        'VND' => __( 'Vietnamese Dong', 'wp-erp' ),
        'EGP' => __( 'Egyptian Pound', 'wp-erp' ),
    ) ) );
}

/**
 * [erp_get_currencies_dropdown description]
 *
 * @param  string  [description]
 *
 * @return string
 */
function erp_get_currencies_dropdown( $selected = '' ) {
    $options    = '';
    $currencies = erp_get_currencies();

    foreach ($currencies as $key => $value) {
        $select  = ( $key == $selected ) ? ' selected="selected"' : '';
        $options .= sprintf( "<option value='%s'%s>%s</option>\n", esc_attr( $key ), $select, $value );
    }

    return $options;
}

/**
 * Get Currency symbol.
 *
 * @param string $currency (default: '')
 * @return string
 */
function erp_get_currency_symbol( $currency = '' ) {

    switch ( $currency ) {
        case 'AED' :
            $currency_symbol = 'د.إ';
            break;
        case 'BDT':
            $currency_symbol = '&#2547;&nbsp;';
            break;
        case 'BRL' :
            $currency_symbol = '&#82;&#36;';
            break;
        case 'BGN' :
            $currency_symbol = '&#1083;&#1074;.';
            break;
        case 'AUD' :
        case 'CAD' :
        case 'CLP' :
        case 'COP' :
        case 'MXN' :
        case 'NZD' :
        case 'HKD' :
        case 'SGD' :
        case 'USD' :
            $currency_symbol = '&#36;';
            break;
        case 'EUR' :
            $currency_symbol = '&euro;';
            break;
        case 'CNY' :
        case 'RMB' :
        case 'JPY' :
            $currency_symbol = '&yen;';
            break;
        case 'RUB' :
            $currency_symbol = '&#1088;&#1091;&#1073;.';
            break;
        case 'KRW' : $currency_symbol = '&#8361;'; break;
            case 'PYG' : $currency_symbol = '&#8370;'; break;
        case 'TRY' : $currency_symbol = '&#8378;'; break;
        case 'NOK' : $currency_symbol = '&#107;&#114;'; break;
        case 'ZAR' : $currency_symbol = '&#82;'; break;
        case 'CZK' : $currency_symbol = '&#75;&#269;'; break;
        case 'MYR' : $currency_symbol = '&#82;&#77;'; break;
        case 'DKK' : $currency_symbol = 'kr.'; break;
        case 'HUF' : $currency_symbol = '&#70;&#116;'; break;
        case 'IDR' : $currency_symbol = 'Rp'; break;
        case 'INR' : $currency_symbol = 'Rs.'; break;
        case 'NPR' : $currency_symbol = 'Rs.'; break;
        case 'ISK' : $currency_symbol = 'Kr.'; break;
        case 'ILS' : $currency_symbol = '&#8362;'; break;
        case 'PHP' : $currency_symbol = '&#8369;'; break;
        case 'PLN' : $currency_symbol = '&#122;&#322;'; break;
        case 'SEK' : $currency_symbol = '&#107;&#114;'; break;
        case 'CHF' : $currency_symbol = '&#67;&#72;&#70;'; break;
        case 'TWD' : $currency_symbol = '&#78;&#84;&#36;'; break;
        case 'THB' : $currency_symbol = '&#3647;'; break;
        case 'GBP' : $currency_symbol = '&pound;'; break;
        case 'RON' : $currency_symbol = 'lei'; break;
        case 'VND' : $currency_symbol = '&#8363;'; break;
        case 'NGN' : $currency_symbol = '&#8358;'; break;
        case 'HRK' : $currency_symbol = 'Kn'; break;
        case 'EGP' : $currency_symbol = 'EGP'; break;
        case 'DOP' : $currency_symbol = 'RD&#36;'; break;
        case 'KIP' : $currency_symbol = '&#8365;'; break;
        default    : $currency_symbol = ''; break;
    }

    return apply_filters( 'erp_currency_symbol', $currency_symbol, $currency );
}
