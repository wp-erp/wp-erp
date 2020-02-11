<?php

/**
 * Processes all ERP actions sent via REQUEST by looking for the 'erp-action'
 * request and running do_action() to call the function
 *
 * @return void
 */
function erp_process_actions() {
    if ( isset( $_REQUEST['erp-action'] ) ) {
        $action = sanitize_text_field( wp_unslash( $_REQUEST['erp-action'] ) );

        do_action( 'erp_action_' .$action, $_REQUEST );
    }
}

/**
 * Return the WP ERP version
 *
 * @return string The WP ERP version
 */
function erp_get_version() {
    return wperp()->version;
}

/**
 * Maps various caps to built in WordPress caps
 *
 *
 * @param array  $caps Capabilities for meta capability
 * @param string $cap Capability name
 * @param int    $user_id User id
 * @param mixed  $args Arguments
 */
function erp_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
    return apply_filters( 'erp_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Get full list of currency codes.
 *
 * @since 1.0.0
 * @since 1.1.14 Add most of the current circulating currencies
 *
 * @return array
 */
function erp_get_currencies() {
    return apply_filters( 'erp_currencies', [
        'AFN' => __( 'Afghan Afghani', 'erp' ),
        'ALL' => __( 'Albanian Lek', 'erp' ),
        'DZD' => __( 'Algerian Dinar', 'erp' ),
        'ADP' => __( 'Andorran Peseta', 'erp' ),
        'AOA' => __( 'Angolan Kwanza', 'erp' ),
        'ARA' => __( 'Argentine Austral', 'erp' ),
        'ARS' => __( 'Argentine Peso', 'erp' ),
        'AMD' => __( 'Armenian Dram', 'erp' ),
        'AWG' => __( 'Aruban Florin', 'erp' ),
        'AUD' => __( 'Australian Dollar', 'erp' ),
        'ATS' => __( 'Austrian Schilling', 'erp' ),
        'AZN' => __( 'Azerbaijani Manat', 'erp' ),
        'BSD' => __( 'Bahamian Dollar', 'erp' ),
        'BHD' => __( 'Bahraini Dinar', 'erp' ),
        'BDT' => __( 'Bangladeshi Taka', 'erp' ),
        'BBD' => __( 'Barbadian Dollar', 'erp' ),
        'BYR' => __( 'Belarusian Ruble', 'erp' ),
        'BEF' => __( 'Belgian Franc', 'erp' ),
        'BZD' => __( 'Belize Dollar', 'erp' ),
        'BMD' => __( 'Bermudan Dollar', 'erp' ),
        'BTN' => __( 'Bhutanese Ngultrum', 'erp' ),
        'BOB' => __( 'Bolivian Boliviano', 'erp' ),
        'BOV' => __( 'Bolivian Mvdol', 'erp' ),
        'BOP' => __( 'Bolivian Peso', 'erp' ),
        'BAM' => __( 'Bosnia-Herzegovina Convertible Mark', 'erp' ),
        'BWP' => __( 'Botswanan Pula', 'erp' ),
        'BRL' => __( 'Brazilian Real', 'erp' ),
        'GBP' => __( 'British Pound Sterling', 'erp' ),
        'BND' => __( 'Brunei Dollar', 'erp' ),
        'BGN' => __( 'Bulgarian Lev', 'erp' ),
        'BUK' => __( 'Burmese Kyat', 'erp' ),
        'BIF' => __( 'Burundian Franc', 'erp' ),
        'KHR' => __( 'Cambodian Riel', 'erp' ),
        'CAD' => __( 'Canadian Dollar', 'erp' ),
        'CVE' => __( 'Cape Verdean Escudo', 'erp' ),
        'KYD' => __( 'Cayman Islands Dollar', 'erp' ),
        'XOF' => __( 'CFA Franc BCEAO', 'erp' ),
        'XAF' => __( 'CFA Franc BEAC', 'erp' ),
        'XPF' => __( 'CFP Franc', 'erp' ),
        'CLP' => __( 'Chilean Peso', 'erp' ),
        'CNY' => __( 'Chinese Yuan', 'erp' ),
        'COP' => __( 'Colombian Peso', 'erp' ),
        'KMF' => __( 'Comorian Franc', 'erp' ),
        'CDF' => __( 'Congolese Franc', 'erp' ),
        'CRC' => __( 'Costa Rican Colón', 'erp' ),
        'HRK' => __( 'Croatian Kuna', 'erp' ),
        'CUP' => __( 'Cuban Peso', 'erp' ),
        'CYP' => __( 'Cypriot Pound', 'erp' ),
        'CZK' => __( 'Czech Republic Koruna', 'erp' ),
        'DKK' => __( 'Danish Krone', 'erp' ),
        'DJF' => __( 'Djiboutian Franc', 'erp' ),
        'DOP' => __( 'Dominican Peso', 'erp' ),
        'NLG' => __( 'Dutch Guilder', 'erp' ),
        'XCD' => __( 'East Caribbean Dollar', 'erp' ),
        'ECS' => __( 'Ecuadorian Sucre', 'erp' ),
        'EGP' => __( 'Egyptian Pound', 'erp' ),
        'GQE' => __( 'Equatorial Guinean Ekwele', 'erp' ),
        'ERN' => __( 'Eritrean Nakfa', 'erp' ),
        'EEK' => __( 'Estonian Kroon', 'erp' ),
        'ETB' => __( 'Ethiopian Birr', 'erp' ),
        'EUR' => __( 'Euro', 'erp' ),
        'FKP' => __( 'Falkland Islands Pound', 'erp' ),
        'FJD' => __( 'Fijian Dollar', 'erp' ),
        'FIM' => __( 'Finnish Markka', 'erp' ),
        'FRF' => __( 'French Franc', 'erp' ),
        'GMD' => __( 'Gambian Dalasi', 'erp' ),
        'GEL' => __( 'Georgian Lari', 'erp' ),
        'DEM' => __( 'German Mark', 'erp' ),
        'GHS' => __( 'Ghanaian Cedi', 'erp' ),
        'GIP' => __( 'Gibraltar Pound', 'erp' ),
        'GRD' => __( 'Greek Drachma', 'erp' ),
        'GTQ' => __( 'Guatemalan Quetzal', 'erp' ),
        'GWP' => __( 'Guinea-Bissau Peso', 'erp' ),
        'GNF' => __( 'Guinean Franc', 'erp' ),
        'GYD' => __( 'Guyanaese Dollar', 'erp' ),
        'HTG' => __( 'Haitian Gourde', 'erp' ),
        'HNL' => __( 'Honduran Lempira', 'erp' ),
        'HKD' => __( 'Hong Kong Dollar', 'erp' ),
        'HUF' => __( 'Hungarian Forint', 'erp' ),
        'ISK' => __( 'Icelandic Króna', 'erp' ),
        'INR' => __( 'Indian Rupee', 'erp' ),
        'IDR' => __( 'Indonesian Rupiah', 'erp' ),
        'IRR' => __( 'Iranian Rial', 'erp' ),
        'IQD' => __( 'Iraqi Dinar', 'erp' ),
        'IEP' => __( 'Irish Pound', 'erp' ),
        'ILS' => __( 'Israeli New Sheqel', 'erp' ),
        'ITL' => __( 'Italian Lira', 'erp' ),
        'JMD' => __( 'Jamaican Dollar', 'erp' ),
        'JPY' => __( 'Japanese Yen', 'erp' ),
        'JOD' => __( 'Jordanian Dinar', 'erp' ),
        'KZT' => __( 'Kazakhstani Tenge', 'erp' ),
        'KES' => __( 'Kenyan Shilling', 'erp' ),
        'KWD' => __( 'Kuwaiti Dinar', 'erp' ),
        'KGS' => __( 'Kyrgystani Som', 'erp' ),
        'LAK' => __( 'Laotian Kip', 'erp' ),
        'LVL' => __( 'Latvian Lats', 'erp' ),
        'LBP' => __( 'Lebanese Pound', 'erp' ),
        'LSL' => __( 'Lesotho Loti', 'erp' ),
        'LRD' => __( 'Liberian Dollar', 'erp' ),
        'LYD' => __( 'Libyan Dinar', 'erp' ),
        'LTL' => __( 'Lithuanian Litas', 'erp' ),
        'LTT' => __( 'Lithuanian Talonas', 'erp' ),
        'LUF' => __( 'Luxembourgian Franc', 'erp' ),
        'MOP' => __( 'Macanese Pataca', 'erp' ),
        'MKD' => __( 'Macedonian Denar', 'erp' ),
        'MGA' => __( 'Malagasy Ariary', 'erp' ),
        'MWK' => __( 'Malawian Kwacha', 'erp' ),
        'MYR' => __( 'Malaysian Ringgit', 'erp' ),
        'MVR' => __( 'Maldivian Rufiyaa', 'erp' ),
        'MLF' => __( 'Malian Franc', 'erp' ),
        'MTL' => __( 'Maltese Lira', 'erp' ),
        'MRO' => __( 'Mauritanian Ouguiya', 'erp' ),
        'MUR' => __( 'Mauritian Rupee', 'erp' ),
        'MXN' => __( 'Mexican Peso', 'erp' ),
        'MDL' => __( 'Moldovan Leu', 'erp' ),
        'MCF' => __( 'Monegasque Franc', 'erp' ),
        'MNT' => __( 'Mongolian Tugrik', 'erp' ),
        'MAD' => __( 'Moroccan Dirham', 'erp' ),
        'MZN' => __( 'Mozambican Metical', 'erp' ),
        'MMK' => __( 'Myanmar Kyat', 'erp' ),
        'NAD' => __( 'Namibian Dollar', 'erp' ),
        'NPR' => __( 'Nepalese Rupee', 'erp' ),
        'ANG' => __( 'Netherlands Antillean Guilder', 'erp' ),
        'TWD' => __( 'New Taiwan Dollar', 'erp' ),
        'NZD' => __( 'New Zealand Dollar', 'erp' ),
        'NIO' => __( 'Nicaraguan Córdoba', 'erp' ),
        'NGN' => __( 'Nigerian Naira', 'erp' ),
        'KPW' => __( 'North Korean Won', 'erp' ),
        'NOK' => __( 'Norwegian Krone', 'erp' ),
        'OMR' => __( 'Omani Rial', 'erp' ),
        'PKR' => __( 'Pakistani Rupee', 'erp' ),
        'PAB' => __( 'Panamanian Balboa', 'erp' ),
        'PGK' => __( 'Papua New Guinean Kina', 'erp' ),
        'PYG' => __( 'Paraguayan Guarani', 'erp' ),
        'PEI' => __( 'Peruvian Inti', 'erp' ),
        'PHP' => __( 'Philippine Peso', 'erp' ),
        'PLN' => __( 'Polish Zloty', 'erp' ),
        'PTE' => __( 'Portuguese Escudo', 'erp' ),
        'QAR' => __( 'Qatari Rial', 'erp' ),
        'RHD' => __( 'Rhodesian Dollar', 'erp' ),
        'RON' => __( 'Romanian Leu', 'erp' ),
        'RUB' => __( 'Russian Ruble', 'erp' ),
        'RWF' => __( 'Rwandan Franc', 'erp' ),
        'SVC' => __( 'Salvadoran Colón', 'erp' ),
        'WST' => __( 'Samoan Tala', 'erp' ),
        'STD' => __( 'São Tomé & Príncipe Dobra', 'erp' ),
        'SAR' => __( 'Saudi Riyal', 'erp' ),
        'RSD' => __( 'Serbian Dinar', 'erp' ),
        'SCR' => __( 'Seychellois Rupee', 'erp' ),
        'SLL' => __( 'Sierra Leonean Leone', 'erp' ),
        'SGD' => __( 'Singapore Dollar', 'erp' ),
        'SKK' => __( 'Slovak Koruna', 'erp' ),
        'SIT' => __( 'Slovenian Tolar', 'erp' ),
        'SBD' => __( 'Solomon Islands Dollar', 'erp' ),
        'SOS' => __( 'Somali Shilling', 'erp' ),
        'ZAR' => __( 'South African Rand', 'erp' ),
        'KRW' => __( 'South Korean Won', 'erp' ),
        'SSP' => __( 'South Sudanese Pound', 'erp' ),
        'ESP' => __( 'Spanish Peseta', 'erp' ),
        'LKR' => __( 'Sri Lankan Rupee', 'erp' ),
        'SHP' => __( 'St. Helena Pound', 'erp' ),
        'SDG' => __( 'Sudanese Pound', 'erp' ),
        'SRD' => __( 'Surinamese Dollar', 'erp' ),
        'SZL' => __( 'Swazi Lilangeni', 'erp' ),
        'SEK' => __( 'Swedish Krona', 'erp' ),
        'CHF' => __( 'Swiss Franc', 'erp' ),
        'SYP' => __( 'Syrian Pound', 'erp' ),
        'TJS' => __( 'Tajikistani Somoni', 'erp' ),
        'TZS' => __( 'Tanzanian Shilling', 'erp' ),
        'THB' => __( 'Thai Baht', 'erp' ),
        'TPE' => __( 'Timorese Escudo', 'erp' ),
        'TOP' => __( 'Tongan Paʻanga', 'erp' ),
        'TTD' => __( 'Trinidad & Tobago Dollar', 'erp' ),
        'TND' => __( 'Tunisian Dinar', 'erp' ),
        'TRY' => __( 'Turkish Lira', 'erp' ),
        'TMT' => __( 'Turkmenistani Manat', 'erp' ),
        'UGX' => __( 'Ugandan Shilling', 'erp' ),
        'UAH' => __( 'Ukrainian Hryvnia', 'erp' ),
        'AED' => __( 'United Arab Emirates Dirham', 'erp' ),
        'UYU' => __( 'Uruguayan Peso', 'erp' ),
        'USD' => __( 'US Dollar', 'erp' ),
        'UZS' => __( 'Uzbekistan Som', 'erp' ),
        'VUV' => __( 'Vanuatu Vatu', 'erp' ),
        'VEF' => __( 'Venezuelan Bolívar', 'erp' ),
        'VND' => __( 'Vietnamese Dong', 'erp' ),
        'YER' => __( 'Yemeni Rial', 'erp' ),
        'ZMW' => __( 'Zambian Kwacha', 'erp' ),
        'ZWL' => __( 'Zimbabwean Dollar', 'erp' ),
    ] );
}

/**
 * Get full list of currency ISO with symbol label.
 *
 * @return array
 */
function erp_get_currency_list_with_symbol() {
    $currencies       = erp_get_currencies();
    $currency_symbols = erp_get_currency_symbol();
    $currency_list    = [];

    foreach ( $currencies as $iso => $currency ) {
        $symbol = isset( $currency_symbols[ $iso ] ) ? $currency_symbols[ $iso ] : $iso;

        $currency_list[ $iso ] = sprintf( '%1$s (%2$s)', $currency, $symbol );
    }

    return $currency_list;
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

    foreach ( $currencies as $key => $value ) {
        $select  = ( $key == $selected ) ? ' selected="selected"' : '';
        $options .= sprintf( "<option value='%s'%s>%s</option>\n", esc_attr( $key ), $select, $value );
    }

    return $options;
}

/**
 * Get global currency
 *
 * @since 1.1.6
 *
 * @return string
 */
function erp_get_currency( $get_only_id = false ) {
    global $wpdb;

    $usd = 148;

    $currency_id = erp_get_option('erp_currency', 'erp_settings_general', $usd);

    if ( $get_only_id ) {
        return $currency_id;
    }

    $currency_name = $wpdb->get_var( $wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}erp_acct_currency_info WHERE id = %d",
        $currency_id
    ) );

    return $currency_name;
}

/**
 * Get Currency symbol.
 *
 * @since 1.0.0
 * @since 1.1.14 Add most of the current circulating currencies.
 *               If no $currency provided, full symbol list will be returned.
 * @since 1.2.1  Fix symbol for South African rand
 *
 * @param string $currency
 *
 * @return string|array
 */
function erp_get_currency_symbol( $currency = '' ) {

    /**
     * Source: https://en.wikipedia.org/wiki/List_of_circulating_currencies
     *
     * In wikipedia some of the symbols are in SVG image like 'AMD'
     * or not supported by UTF-8 like 'GEL'. For those symbols currency codes
     * are used as symbols
     */

    $currency_symbols = [
        'AED' => 'د.إ',
        'AFN' => '؋',
        'ALL' => 'L',
        'AMD' => 'AMD',
        'ANG' => 'ƒ',
        'AOA' => 'Kz',
        'ARS' => '$',
        'AUD' => '$',
        'AWG' => 'ƒ',
        'AZN' => '₼',
        'BAM' => 'KM',
        'BBD' => '$',
        'BDT' => '৳',
        'BGN' => 'лв',
        'BHD' => '.د.ب',
        'BIF' => 'Fr',
        'BMD' => '$',
        'BND' => '$',
        'BOB' => 'Bs.',
        'BRL' => 'R$',
        'BSD' => '$',
        'BTN' => 'Nu.',
        'BWP' => 'P',
        'BYN' => 'Br',
        'BYR' => 'Br',
        'BZD' => '$',
        'CAD' => '$',
        'CDF' => 'Fr',
        'CHF' => 'Fr',
        'CLP' => '$',
        'CNY' => '¥',
        'COP' => '$',
        'CRC' => '₡',
        'CUC' => '$',
        'CUP' => '$',
        'CVE' => '$',
        'CZK' => 'Kč',
        'DJF' => 'Fr',
        'DKK' => 'kr',
        'DOP' => '$',
        'DZD' => 'د.ج',
        'EGP' => '£',
        'ERN' => 'Nfk',
        'ETB' => 'Br',
        'EUR' => '€',
        'FJD' => '$',
        'FKP' => '£',
        'GBP' => '£',
        'GEL' => 'GEL',
        'GGP' => '£',
        'GHS' => '₵',
        'GIP' => '£',
        'GMD' => 'D',
        'GNF' => 'Fr',
        'GTQ' => 'Q',
        'GYD' => '$',
        'HKD' => '$',
        'HNL' => 'L',
        'HRK' => 'kn',
        'HTG' => 'G',
        'HUF' => 'Ft',
        'IDR' => 'Rp',
        'ILS' => '₪',
        'IMP' => '£',
        'INR' => '₹',
        'IQD' => 'ع.د',
        'IRR' => '﷼',
        'ISK' => 'kr',
        'JEP' => '£',
        'JMD' => '$',
        'JOD' => 'د.ا',
        'JPY' => '¥',
        'KES' => 'Sh',
        'KGS' => 'с',
        'KHR' => '៛',
        'KMF' => 'Fr',
        'KPW' => '₩',
        'KRW' => '₩',
        'KWD' => 'د.ك',
        'KYD' => '$',
        'KZT' => 'KZT',
        'LAK' => '₭',
        'LBP' => 'ل.ل',
        'LKR' => 'Rs',
        'LRD' => '$',
        'LSL' => 'L',
        'LYD' => 'ل.د',
        'MAD' => 'د.م.',
        'MDL' => 'L',
        'MGA' => 'Ar',
        'MKD' => 'ден',
        'MMK' => 'Ks',
        'MNT' => '₮',
        'MOP' => 'P',
        'MRO' => 'UM',
        'MUR' => '₨',
        'MVR' => 'MVR',
        'MWK' => 'MK',
        'MXN' => '$',
        'MYR' => 'RM',
        'MZN' => 'MT',
        'NAD' => '$',
        'NGN' => '₦',
        'NIO' => 'C$',
        'NOK' => 'kr',
        'NPR' => '₨',
        'NZD' => '$',
        'OMR' => 'ر.ع.',
        'PAB' => 'B/.',
        'PEN' => 'S/.',
        'PGK' => 'K',
        'PHP' => '₱',
        'PKR' => '₨',
        'PLN' => 'zł',
        'PRB' => 'р.',
        'PYG' => '₲',
        'QAR' => 'ر.ق',
        'RON' => 'lei',
        'RSD' => 'дин',
        'RUB' => '₽',
        'RWF' => 'Fr',
        'SAR' => 'ر.س',
        'SBD' => '$',
        'SCR' => '₨',
        'SDG' => 'ج.س.',
        'SEK' => 'kr',
        'SGD' => '$',
        'SHP' => '£',
        'SLL' => 'Le',
        'SOS' => 'Sh',
        'SRD' => '$',
        'SSP' => '£',
        'STD' => 'Db',
        'SYP' => '£',
        'SZL' => 'L',
        'THB' => '฿',
        'TJS' => 'ЅМ',
        'TMT' => 'm',
        'TND' => 'د.ت',
        'TOP' => 'T$',
        'TRY' => 'TRY',
        'TTD' => '$',
        'TVD' => '$',
        'TWD' => '$',
        'TZS' => 'Sh',
        'UAH' => '₴',
        'UGX' => 'Sh',
        'USD' => '$',
        'UYU' => '$',
        'UZS' => 'UZS',
        'VEF' => 'Bs',
        'VND' => '₫',
        'VUV' => 'Vt',
        'WST' => 'T',
        'XAF' => 'Fr',
        'XCD' => '$',
        'XOF' => 'Fr',
        'XPF' => 'Fr',
        'YER' => '﷼',
        'ZAR' => 'R',
        'ZMW' => 'ZK',
        'ZWL' => '$',
    ];

    if ( ! empty( $currency ) ) {
        $symbol = ! empty( $currency_symbols[ $currency ] ) ? $currency_symbols[ $currency ] : $currency;

        return apply_filters( 'erp_currency_symbol', $symbol, $currency );
    } else {
        return apply_filters( 'erp_currency_symbol_list', $currency_symbols );
    }

}

/**
 * Get default country
 *
 * @since 1.4.0
 *
 * @return string
 */
function erp_get_country() {
    return erp_get_option( 'erp_country', 'erp_settings_general', -1 );
}

/**
 * Embed a JS template page with its ID
 *
 * @param  string  the file path of the file
 * @param  string  the script id
 *
 * @return void
 */
function erp_get_js_template( $file_path, $id ) {
    if ( file_exists( $file_path ) ) {
        echo '<script type="text/html" id="tmpl-' . esc_html( $id ) . '">' . "\n";
        include_once apply_filters( 'erp_crm_js_template_file_path', $file_path, esc_html( $id ) );
        echo "\n" . '</script>' . "\n";
    }
}

/**
 * Embed a Vue Component template page with its ID
 *
 * @param  string  the file path of the file
 * @param  string  the script id
 *
 * @return void
 */
function erp_get_vue_component_template( $file_path, $id ) {
    if ( file_exists( $file_path ) ) {
        echo '<script type="text/x-template" id="' . esc_html( $id ) . '">' . "\n";
        include_once $file_path;
        echo "\n" . '</script>' . "\n";
    }
}


if ( ! function_exists( 'strip_tags_deep' ) ) {

    /**
     * Strip tags from string or array
     *
     * @param  mixed  array or string to strip
     *
     * @return mixed  stripped value
     */
    function strip_tags_deep( $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $val ) {
                $value[ $key ] = strip_tags_deep( $val );
            }
        } elseif ( is_string( $value ) ) {
            $value = strip_tags( $value );
        }

        return $value;
    }
}

if ( ! function_exists( 'trim_deep' ) ) {

    /**
     * Trim from string or array
     *
     * @param  mixed  array or string to trim
     *
     * @return mixed  timmed value
     */
    function trim_deep( $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $val ) {
                $value[ $key ] = trim_deep( $val );
            }
        } elseif ( is_string( $value ) ) {
            $value = trim( $value );
        }

        return $value;
    }
}

/**
 * Helper function to print a label and value with a separator
 *
 * @since 1.0.0
 * @since 1.1.14 Add $type param
 * @since 1.1.16 Apply if-else condition to set `$value`
 *
 * @param  string $label the label
 * @param  string $value the value to print
 * @param  string $sep separator
 * @param  string $type field type
 *
 * @return void
 */
function erp_print_key_value( $label, $value, $sep = ' : ', $type = 'text' ) {
    if ( empty( $value ) ) {
        $value = '&mdash;';

    } else {
        switch ( $type ) {
            case 'email':
            case 'url':
            case 'phone':
                $value = erp_get_clickable( $type, $value );
                break;
        }
    }

    printf(
        '<label>%s</label> <span class="sep">%s</span> <span class="value">%s</span>',
        wp_kses_post( $label ),
        esc_html( $sep ),
        wp_kses_post( $value )
    );
}

/**
 * Get a clickable phone or email address link
 *
 * @param  string  type. e.g: email|phone|url
 * @param  string  the value
 *
 * @return string  the link
 */
function erp_get_clickable( $type = 'email', $value = '' ) {
    if ( 'email' == $type ) {
        return sprintf( '<a href="mailto:%1$s">%1$s</a>', $value );
    } elseif ( 'url' == $type ) {
        return sprintf( '<a target="_blank" href="%1$s">%1$s</a>', $value );
    } elseif ( 'phone' == $type ) {
        return sprintf( '<a href="tel:%1$s">%1$s</a>', $value );
    }
}

/**
 * Get a formatted date from WordPress format
 *
 * @param  string $date the date
 *
 * @return string  formatted date
 */
function erp_format_date( $date, $format = false ) {
    if ( ! $format ) {
        $format = erp_get_option( 'date_format', 'erp_settings_general', 'd-m-Y' );
    }

    $time = strtotime( $date );

    return date_i18n( $format, $time );
}

/**
 * Extract dates between two date range
 *
 * @param  string $start_date example: 2016-12-16 00:00:00
 * @param  string $end_date example: 2016-12-16 23:59:59
 *
 * @return array
 */
function erp_extract_dates( $start_date, $end_date ) {
    // if start date has no time set, then add 00:00:00 or 12:00 AM
    if ( ! preg_match( '/\d{2}:\d{2}:\d{2}$/', $start_date ) ) {
        $start_date = $start_date . ' 00:00:00';
    }

    // if end date has no time set, then add 23:59:59 or 11:59 PM
    if ( ! preg_match( '/\d{2}:\d{2}:\d{2}$/', $end_date ) ) {
        $end_date = $end_date . ' 23:59:59';
    }

    $start_date = new DateTime( $start_date );
    $end_date   = new DateTime( $end_date );
    $diff       = $start_date->diff( $end_date );

    // we got a negative date
    if ( $diff->invert ) {
        return new WP_Error( 'invalid-date', __( 'Invalid date provided', 'erp' ) );
    }

    $interval = DateInterval::createFromDateString( '1 day' );
    $period   = new DatePeriod( $start_date, $interval, $end_date );

    // prepare the periods
    $dates = array();
    foreach ( $period as $dt ) {
        $dates[] = $dt->format( 'Y-m-d' );
    }

    return $dates;
}

/**
 * Convert an two dimentational array to one dimentional array object
 *
 * @param  array $array array of arrays
 *
 * @return array
 */
function erp_array_to_object( $array = [] ) {
    $new_array = [];

    if ( $array ) {
        foreach ( $array as $key => $value ) {
            $new_array[] = (object) $value;
        }
    }

    return $new_array;
}

/**
 * Check date in range or not
 *
 * @param  date $start_date
 * @param  date $end_date
 * @param  date $date_from_user
 *
 * @return boolen
 */
function erp_check_date_in_range( $start_date, $end_date, $date_from_user ) {
    // Convert to timestamp
    $start_ts = strtotime( $start_date );
    $end_ts   = strtotime( $end_date );
    $user_ts  = strtotime( $date_from_user );

    // Check that user date is between start & end
    if ( ( $user_ts >= $start_ts ) && ( $user_ts <= $end_ts ) ) {
        return true;
    }

    return false;
}

/**
 * Check date range any point in range or not
 *
 * @param  date $start_date
 * @param  date $end_date
 * @param  date $user_date_start
 * @param  date $user_date_end
 *
 * @return boolen
 */
function erp_check_date_range_in_range_exist( $start_date, $end_date, $user_date_start, $user_date_end ) {

    if ( erp_check_date_in_range( $start_date, $end_date, $user_date_start ) ) {
        return true;
    }

    if ( erp_check_date_in_range( $start_date, $end_date, $user_date_end ) ) {
        return true;
    }

    return false;
}

/**
 * Get durataion between two date
 *
 * @param  date $start_date
 * @param  date $end_date
 *
 * @return integer
 */
function erp_date_duration( $start_date, $end_date ) {
    $diff       = abs( strtotime( $start_date ) - strtotime( $end_date ) );
    $hours_diff = ceil( $diff / ( 60 * 60 ) );

    return ceil( $hours_diff / 24 );
}

/**
 * Performance rating elemet
 *
 * @since 0.1
 *
 * @param  string $selected
 *
 * @return array
 */
function erp_performance_rating( $selected = '' ) {

    $rating = apply_filters( 'erp_performance_rating', array(
        '1' => __( 'Very Bad', 'erp' ),
        '2' => __( 'Poor', 'erp' ),
        '3' => __( 'Average', 'erp' ),
        '4' => __( 'Good', 'erp' ),
        '5' => __( 'Excellent', 'erp' )
    ) );

    if ( $selected ) {
        return isset( $rating[ $selected ] ) ? $rating[ $selected ] : '';
    }

    return $rating;
}

/**
 * Get erp option from settings framework
 *
 * @param  string $option_name name of the option
 * @param  string $section name of the section. if it's a separate option, don't provide any
 * @param  string $default default option
 *
 * @return string
 */
function erp_get_option( $option_name, $section = false, $default = '' ) {

    if ( $section ) {
        $option = get_option( $section );

        if ( isset( $option[ $option_name ] ) ) {
            return $option[ $option_name ];
        } else {
            return $default;
        }
    } else {
        return get_option( $option_name, $default );
    }
}

/**
 * Get erp logo
 *
 * @return string url of the logo
 */
function erp_get_site_logo() {
    $logo = (int) erp_get_option( 'logo', 'erp_settings_design' );

    if ( $logo ) {
        $logo_url = wp_get_attachment_url( $logo );

        return $logo_url;
    }
}

/**
 * Get month array
 *
 * @param string $title
 *
 * @since  0.1
 *
 * @return array
 */
function erp_months_dropdown( $title = false ) {

    $months = [];

    if ( $title ) {
        $months['-1'] = $title;
    }

    for ( $m = 1; $m <= 12; $m ++ ) {
        $months[ $m ] = date( 'F', mktime( 0, 0, 0, $m, 1 ) );
    }

    return $months;

}

/**
 * Get Company financial start date
 *
 * @since  0.1
 * @since 1.2.0 Using `erp_get_financial_year_dates` function
 *
 * @return string date
 */
function erp_financial_start_date() {
    $financial_year_dates = erp_get_financial_year_dates();

    return $financial_year_dates['start'];
}

/**
 * Get Company financial end date
 *
 * @since  0.1
 * @since 1.2.0 Using `erp_get_financial_year_dates` function
 *
 * @return string date
 */
function erp_financial_end_date() {
    $financial_year_dates = erp_get_financial_year_dates();

    return $financial_year_dates['end'];
}

/**
 * Get all modules inserted in log table
 *
 * @since 0.1
 *
 * @return array
 */
function erp_get_audit_log_modules() {
    return \WeDevs\ERP\Admin\Models\Audit_Log::select( 'component' )->distinct()->get()->toArray();
}

/**
 * Get all modules inserted in log table
 *
 * @since 0.1
 *
 * @return array
 */
function erp_get_audit_log_sub_component() {
    return \WeDevs\ERP\Admin\Models\Audit_Log::select( 'sub_component' )->distinct()->get()->toArray();
}

/**
 * Erp Logging functions
 *
 * @since 0.1
 *
 * @return instance
 */
function erp_log() {
    return \WeDevs\ERP\Log::instance();
}

/**
 * A file based logging function for debugging
 *
 * @since 0.1
 *
 * @param  string $message
 * @param  string $type
 *
 * @return void
 */
function erp_file_log( $message, $type = '' ) {
    if ( ! empty( $type ) ) {
        $message = sprintf( "[%s][%s] %s\n", date( 'd.m.Y h:i:s' ), $type, $message );
    } else {
        $message = sprintf( "[%s] %s\n", date( 'd.m.Y h:i:s' ), $message );
    }
}

/**
 * Get people types from various components
 *
 * @return array
 */
function erp_get_people_types() {
    return apply_filters( 'erp_people_types', [] );
}

/**
 * Get Country name by country code
 *
 * @since 1.0
 *
 * @param  string $code
 *
 * @return string
 */
function erp_get_country_name( $country ) {

    $load_cuntries_states = \WeDevs\ERP\Countries::instance();
    $countries            = $load_cuntries_states->countries;

    // Handle full country name
    if ( '-1' != $country ) {
        $full_country = ( isset( $countries[ $country ] ) ) ? $countries[ $country ] : $country;
    } else {
        $full_country = '—';
    }

    return $full_country;
}

/**
 * Get State name by country and state code
 *
 * @since 1.0
 *
 * @param  string $country
 * @param  string $state
 *
 * @return string
 */
function erp_get_state_name( $country, $state ) {
    $load_cuntries_states = \WeDevs\ERP\Countries::instance();
    $states               = $load_cuntries_states->states;

    // Handle full state name
    $full_state = ( $country && $state && isset( $states[ $country ][ $state ] ) ) ? $states[ $country ][ $state ] : $state;

    return $full_state;
}

/**
 * Cron Intervel
 *
 * @since 1.0
 *
 * @param  array $schedules
 *
 * @return array
 */
function erp_cron_intervals( $schedules ) {

    $schedules['per_minute'] = array(
        'interval' => MINUTE_IN_SECONDS,
        'display'  => __( 'Every Minute', 'erp' )
    );

    $schedules['two_min'] = array(
        'interval' => MINUTE_IN_SECONDS * 2,
        'display'  => __( 'Every 2 Minutes', 'erp' )
    );

    $schedules['five_min'] = array(
        'interval' => MINUTE_IN_SECONDS * 5,
        'display'  => __( 'Every 5 Minutes', 'erp' )
    );

    $schedules['ten_min'] = array(
        'interval' => MINUTE_IN_SECONDS * 10,
        'display'  => __( 'Every 10 Minutes', 'erp' )
    );

    $schedules['fifteen_min'] = array(
        'interval' => MINUTE_IN_SECONDS * 15,
        'display'  => __( 'Every 15 Minutes', 'erp' )
    );

    $schedules['thirty_min'] = array(
        'interval' => MINUTE_IN_SECONDS * 30,
        'display'  => __( 'Every 30 Minutes', 'erp' )
    );

    $schedules['weekly'] = array(
        'interval' => DAY_IN_SECONDS * 7,
        'display'  => __( 'Once Weekly', 'erp' )
    );

    return (array) $schedules;
}

/**
 * Show user own media attachment
 *
 * @since 1.0
 *
 * @param  string $query
 *
 * @return string
 */
function erp_show_users_own_attachments( $query ) {
    if ( ! is_user_logged_in() ) {
        return $query;
    }

    $id = get_current_user_id();

    if ( ! current_user_can( 'manage_options' ) ) {
        if ( current_user_can( 'erp_hr_manager' )
             || current_user_can( 'employee' )
             || current_user_can( 'erp_crm_manager' )
             || current_user_can( 'erp_crm_agent' )
        ) {
            $query['author'] = $id;
        }
    }

    return $query;
}

/**
 * Get all registered addons for licensing
 *
 * @since 1.0
 *
 * @return array
 */
function erp_addon_licenses() {
    $licenses = [];

    return apply_filters( 'erp_settings_licenses', $licenses );
}

/**
 * Show a readable message about the license status
 *
 * @since 1.0
 *
 * @param  array $addon
 *
 * @return string
 */
function erp_get_license_status( $addon ) {
    if ( ! is_object( $addon['status'] ) ) {
        return false;
    }

    $messages     = [];
    $html         = '';
    $license      = $addon['status'];
    $status_class = 'has-error';

    if ( false === $license->success ) {

        switch ( $license->error ) {

            case 'expired' :

                $messages[] = sprintf(
                    __( 'Your license key expired on %s. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>.', 'erp' ),
                    date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                    'https://wperp.com/checkout/?edd_license_key=' . $addon['license'] . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
                );
                break;

            case 'missing' :

                $messages[] = sprintf(
                    __( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'erp' ),
                    'https://wperp.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
                );
                break;

            case 'invalid' :
            case 'site_inactive' :

                $messages[] = sprintf(
                    __( 'Your %s is not active for this URL. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'erp' ),
                    $addon['name'],
                    'https://wperp.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
                );
                break;

            case 'item_name_mismatch' :

                $messages[] = sprintf( __( 'This is not a %s.', 'erp' ), $addon['name'] );
                break;

            case 'no_activations_left':
                $messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'erp' ), 'https://wperp.com/my-account/' );
                break;

        }
    } else {

        switch ( $license->license ) {
            case 'expired' :

                $messages[] = sprintf(
                    __( 'Your license key expired on %s. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>.', 'erp' ),
                    date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                    'https://wperp.com/checkout/?edd_license_key=' . $addon['license'] . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
                );
                break;

            case 'valid':
                $status_class = 'no-error';
                $now          = current_time( 'timestamp' );
                $expiration   = strtotime( $license->expires, current_time( 'timestamp' ) );

                if ( 'lifetime' === $license->expires ) {

                    $messages[] = __( 'License key never expires.', 'erp' );

                } elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {

                    $messages[] = sprintf(
                        __( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank" title="Renew license">Renew your license key</a>.', 'erp' ),
                        date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                        'https://wperp.com/checkout/?edd_license_key=' . $addon['license'] . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
                    );

                } else {

                    $messages[] = sprintf(
                        __( 'Your license key expires on %s.', 'erp' ),
                        date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) )
                    );

                }
                break;
        }

    }

    if ( ! empty( $messages ) ) {
        foreach ( $messages as $message ) {

            $html .= '<div class="erp-license-status ' . $status_class . '">';
            $html .= '<p class="help">' . $message . '</p>';
            $html .= '</div>';

        }
    }

    return $html;
}

/**
 * Get all fields for import/export operation.
 *
 * @return array
 */
function erp_get_import_export_fields() {
    $erp_fields = [
        'contact'  => [
            'required_fields' => [
                'first_name',
                'email'
            ],
            'fields'          => [
                'first_name',
                'last_name',
                'email',
                'phone',
                'mobile',
                'other',
                'website',
                'fax',
                'notes',
                'street_1',
                'street_2',
                'city',
                'state',
                'postal_code',
                'country',
                'currency',
            ]
        ],
        'company'  => [
            'required_fields' => [
                'email',
                'company',
            ],
            'fields'          => [
                'email',
                'company',
                'phone',
                'mobile',
                'other',
                'website',
                'fax',
                'notes',
                'street_1',
                'street_2',
                'city',
                'state',
                'postal_code',
                'country',
                'currency',
            ]
        ],
        'employee' => [
            'required_fields' => [
                'first_name',
                'last_name',
                'user_email',
            ],
            'fields'          => [
                'first_name',
                'middle_name',
                'last_name',
                'user_email',
                'designation',
                'department',
                'location',
                'hiring_source',
                'hiring_date',
                'date_of_birth',
                'reporting_to',
                'pay_rate',
                'pay_type',
                'type',
                'status',
                'other_email',
                'phone',
                'work_phone',
                'mobile',
                'address',
                'gender',
                'marital_status',
                'nationality',
                'driving_license',
                'hobbies',
                'user_url',
                'description',
                'street_1',
                'street_2',
                'city',
                'country',
                'state',
                'postal_code',
            ]
        ]
    ];

    return apply_filters( 'erp_import_export_csv_fields', $erp_fields );
}

/**
 * ERP Import/Export JavaScript enqueue.
 *
 * @since  1.0
 *
 * @return void
 */
function erp_import_export_javascript() {
    global $current_screen;
    $hook = str_replace( sanitize_title( __( 'ERP Settings', 'erp' ) ), 'erp-settings', $current_screen->base );
    if ( 'wp-erp_page_erp-tools' !== $current_screen->base ) {
        return;
    }

    if ( ! isset( $_GET['tab'] ) || ! in_array( $_GET['tab'], [ 'import', 'export' ] ) ) {
        return;
    }

    $erp_fields = erp_get_import_export_fields();
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {

            function erp_str_title_case(string) {
                var str = string.replace(/_/g, ' ');

                return str.toLowerCase().split(' ').map(function (word) {
                    return (word.charAt(0).toUpperCase() + word.slice(1));
                }).join(' ');
            }

            function erp_csv_field_mapper(file_selector, fields_selector) {
                var file = file_selector.files[0];

                var reader = new FileReader();

                var first5000 = file.slice(0, 5000);
                reader.readAsText(first5000);

                reader.onload = function (e) {
                    var csv = reader.result;
                    // Split the input into lines
                    lines = csv.split('\n'),
                        // Extract column names from the first line
                        columnNamesLine = lines[0];
                    columnNames = columnNamesLine.split(',');

                    var html = '';

                    html += '<option value="">&mdash; Select Field &mdash;</option>';
                    columnNames.forEach(function (item, index) {
                        item = item.replace(/"/g, "");

                        html += '<option value="' + index + '">' + item + '</option>';
                    });

                    if (html) {
                        $(fields_selector).html(html);

                        var field, field_label;
                        $(fields_selector).each(function () {
                            field_label = $(this).parent().parent().find('label').text();

                            var options = $(this).find('option');
                            var targetOption = $(options).filter(function () {
                                var option_text = $(this).html();

                                var re = new RegExp(field_label, 'i');

                                return re.test(option_text);
                            });

                            if (targetOption) {
                                $(options).removeAttr("selected");
                                $(this).val($(targetOption).val());
                            }
                        });
                    }
                };
            }

            function erp_csv_importer_field_handler(file_selector) {
                $('#fields_container').show();

                var fields_html = '';

                var type = $('form#import_form #type').val();

                fields = erp_fields[type] ? erp_fields[type].fields : [];
                required_fields = erp_fields[type] ? erp_fields[type].required_fields : [];

                var required = '';
                var red_span = '';
                for (var i = 0; i < fields.length; i++) {

                    if (required_fields.indexOf(fields[i]) !== -1) {
                        required = 'required';
                        red_span = ' <span class="required">*</span>';
                    } else {
                        required = '';
                        red_span = '';
                    }

                    fields_html += `
                        <tr>
                            <th>
                                <label for="fields[` + fields[i] + `]" class="csv_field_labels">` + erp_str_title_case(fields[i]) + red_span + `</label>
                            </th>
                            <td>
                                <select name="fields[` + fields[i] + `]" class="csv_fields" ` + required + `>
                                </select>
                            </td>
                        </tr>`;
                }

                $('#fields_container').html(fields_html);

                erp_csv_field_mapper(file_selector, '.csv_fields');
            }

            var fields = [];
            var required_fields = [];

            var erp_fields = <?php echo json_encode( $erp_fields ); ?>;

            var type = $('form#export_form #type').val();

            fields = erp_fields[type] ? erp_fields[type].fields : [];

            var html = '<ul class="erp-list list-inline">';
            for (var i = 0; i < fields.length; i++) {
                html += '<li><label><input type="checkbox" name="fields[]" value="' + fields[i] + '"> ' + erp_str_title_case(fields[i]) + '</label></li>';
            }

            html += '<ul>';

            if (html) {
                $('#fields').html(html);
            }

            $('form#export_form #type').on('change', function (e) {
                e.preventDefault();
                $("#export_form #selecctall").prop('checked', false);
                var type = $(this).val();
                fields = erp_fields[type] ? erp_fields[type].fields : [];

                html = '<ul class="erp-list list-inline">';
                for (var i = 0; i < fields.length; i++) {
                    html += '<li><label><input type="checkbox" name="fields[]" value="' + fields[i] + '"> ' + erp_str_title_case(fields[i]) + '</label></li>';
                }

                html += '<ul>';

                if (html) {
                    $('form#export_form #fields').html(html);
                }
            });

            $('form#import_form #csv_file').on('change', function (e) {
                e.preventDefault();

                if (!this) {
                    return;
                }

                erp_csv_importer_field_handler(this);
            });

            if ($('form#import_form').find('#type').val() == 'employee') {
                $('form#import_form').find('#crm_contact_lifestage_owner_wrap').hide();
            } else {
                $('form#import_form').find('#crm_contact_lifestage_owner_wrap').show();
            }

            $('form#import_form #type').on('change', function (e) {
                $('#fields_container').html('');
                $('#fields_container').hide();

                if ($(this).val() == 'employee') {
                    $('form#import_form').find('#crm_contact_lifestage_owner_wrap').hide();
                } else {
                    $('form#import_form').find('#crm_contact_lifestage_owner_wrap').show();
                }

                var sample_csv_url = $('form#import_form').find('#download_sample_wrap input').val();
                $('form#import_form').find('#download_sample_wrap a').attr('href', sample_csv_url + '&type=' + $(this).val());

                if ($('form#import_form #csv_file').val() == "") {
                    return;
                } else {
                    erp_csv_importer_field_handler($('form#import_form #csv_file').get(0));
                }
            });

            $("#export_form #selecctall").change(function (e) {
                e.preventDefault();

                $("#export_form #fields input[type=checkbox]").prop('checked', $(this).prop("checked"));
            });

            $("#users_import_form").on('submit', function (e) {
                e.preventDefault();
                statusDiv = $("div#import-status-indicator");

                statusDiv.show();

                var form = $(this),
                    submit = form.find('input[type=submit]');
                submit.attr('disabled', 'disabled');

                var data = {
                    'action': 'erp_import_users_as_contacts',
                    'user_role': $(this).find('select[name=user_role]').val(),
                    'contact_owner': $(this).find('select[name=contact_owner]').val(),
                    'life_stage': $(this).find('select[name=life_stage]').val(),
                    'contact_group': $(this).find('select[name=contact_group]').val(),
                    '_wpnonce': $(this).find('input[name=_wpnonce]').val()
                };

                var total_items = 0, left = 0, imported = 0, exists = 0, percent = 0, type = 'success', message = '';

                $.post(ajaxurl, data, function (response) {
                    if (response.success) {
                        total_items = response.data.total_items;
                        left = response.data.left;
                        exists = response.data.exists;
                        imported = total_items - left;
                        done = imported - exists;

                        if (imported > 0 || total_items > 0) {
                            percent = Math.floor((100 / total_items) * (imported));

                            type = 'success';
                            message = 'Successfully imported all users!';
                        } else {
                            message = 'No users found to import!';
                            type = 'error';
                        }

                        statusDiv.find('#progress-total').html(percent + '%');
                        statusDiv.find('#progressbar-total').val(percent);
                        statusDiv.find('#completed-total').html('Imported ' + done + ' out of ' + response.data.total_items);
                        if (exists > 0) {
                            statusDiv.find('#failed-total').html('Already Exist ' + exists);
                        }

                        if (response.data.left > 0) {
                            form.submit();
                            return;
                        } else {
                            submit.removeAttr('disabled');

                            swal({
                                title: '',
                                text: message,
                                type: type,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#008ec2'
                            });
                        }
                    }
                });
            });

        });
    </script>
    <?php
}

/**
 * Process or handle import/export submit.
 *
 * @since 1.0.0
 * @since 1.1.15 Declare `field_builder_contacts_fields` with empty an array
 * @since 1.1.18 Handle exporting when no field is given.
 *               Introduce `ERP_IS_IMPORTING` while importing data
 * @since 1.1.19 Import partial people data in case of existing people
 *
 * @return void
 */
function erp_process_import_export() {
    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-import-export-nonce' ) ) {
        return;
    }

    $is_crm_activated = erp_is_module_active( 'crm' );
    $is_hrm_activated = erp_is_module_active( 'hrm' );

    $departments  = $is_hrm_activated ? erp_hr_get_departments_dropdown_raw() : [];
    $designations = $is_hrm_activated ? erp_hr_get_designation_dropdown_raw() : [];

    $field_builder_contact_options = get_option( 'erp-contact-fields' );
    $field_builder_contacts_fields = [];

    if ( ! empty( $field_builder_contact_options ) ) {
        foreach ( $field_builder_contact_options as $field ) {
            $field_builder_contacts_fields[] = $field['name'];
        }
    }

    $field_builder_company_options  = get_option( 'erp-company-fields' );
    $field_builder_companies_fields = [];

    if ( ! empty( $field_builder_company_options ) ) {
        foreach ( $field_builder_company_options as $field ) {
            $field_builder_companies_fields[] = $field['name'];
        }
    }

    $field_builder_employee_options = get_option( 'erp-employee-fields' );
    $field_builder_employees_fields = array();

    if ( ! empty( $field_builder_employee_options ) ) {
        foreach ( $field_builder_employee_options as $field ) {
            $field_builder_employees_fields[] = $field['name'];
        }
    }

    if ( isset( $_POST['erp_import_csv'] ) ) {
        define( 'ERP_IS_IMPORTING', true );

        $fields = ! empty( $_POST['fields'] ) ?  array_map( 'sanitize_text_field', wp_unslash( $_POST['fields'] ) ) : [];
        $type   = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

        if ( empty( $type ) ) {
            return;
        }

        $csv_file = isset( $_FILES['csv_file'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_FILES['csv_file'] ) ) : [];

        $data = [ 'type' => $type, 'fields' => $fields, 'file' => $csv_file ];

        do_action( 'erp_tool_import_csv_action', $data );

        if ( ! in_array( $type, [ 'contact', 'company', 'employee' ] ) ) {
            return;
        }

        $employee_fields = [
            'work'     => [
                'designation',
                'department',
                'location',
                'hiring_source',
                'hiring_date',
                'date_of_birth',
                'reporting_to',
                'pay_rate',
                'pay_type',
                'type',
                'status',
            ],
            'personal' => [
                'photo_id',
                'user_id',
                'first_name',
                'middle_name',
                'last_name',
                'other_email',
                'phone',
                'work_phone',
                'mobile',
                'address',
                'gender',
                'marital_status',
                'nationality',
                'driving_license',
                'hobbies',
                'user_url',
                'description',
                'street_1',
                'street_2',
                'city',
                'country',
                'state',
                'postal_code',
            ]
        ];

        require_once WPERP_INCLUDES . '/lib/parsecsv.lib.php';

        $csv = new ParseCsv();
        $csv->encoding( null, 'UTF-8' );
        $csv->parse( $csv_file['tmp_name'] );

        if ( empty( $csv->data ) ) {
            wp_redirect( admin_url( "admin.php?page=erp-tools&tab=import" ) );
            exit;
        }

        $csv_data = [];

        $csv_data[] = array_keys( $csv->data[0] );

        foreach ( $csv->data as $data_item ) {
            $csv_data[] = array_values( $data_item );
        }

        if ( ! empty( $csv_data ) ) {
            $count = 0;

            foreach ( $csv_data as $line ) {
                if ( empty( $line ) ) {
                    continue;
                }

                $line_data = [];

                if ( is_array( $fields ) && ! empty( $fields ) ) {
                    foreach ($fields as $key => $value) {

                        if (!empty($line[$value]) && is_numeric($value)) {
                            if ($type == 'employee') {
                                if (in_array($key, $employee_fields['work'])) {
                                    if ($key == 'designation') {
                                        $line_data['work'][$key] = array_search($line[$value], $designations);
                                    } else if ($key == 'department') {
                                        $line_data['work'][$key] = array_search($line[$value], $departments);
                                    } else {
                                        $line_data['work'][$key] = $line[$value];
                                    }

                                } else if (in_array($key, $employee_fields['personal'])) {
                                    $line_data['personal'][$key] = $line[$value];
                                } else {
                                    $line_data[$key] = $line[$value];
                                }
                            } else {
                                $line_data[$key] = isset($line[$value]) ? $line[$value] : '';
                                $line_data['type'] = $type;
                            }
                        }
                    }
                }

                if ( $type == 'employee' && $is_hrm_activated ) {
                    if ( ! isset( $line_data['work']['status'] ) ) {
                        $line_data['work']['status'] = 'active';
                    }


                    $item_insert_id = erp_hr_employee_create( $line_data );

                    if ( is_wp_error( $item_insert_id ) ) {
                        continue;
                    }
                }

                if ( ( $type == 'contact' || $type == 'company' ) && $is_crm_activated ) {
                    $contact_owner = isset( $_POST['contact_owner'] ) ? absint( $_POST['contact_owner'] ) : erp_crm_get_default_contact_owner();
                    $line_data['contact_owner'] = $contact_owner;
                    $people = erp_insert_people( $line_data, true );

                    if ( is_wp_error( $people ) ) {
                        continue;
                    } else {
                        $contact       = new \WeDevs\ERP\CRM\Contact( absint( $people->id ), 'contact' );
                        $life_stage    = isset( $_POST['life_stage'] ) ? sanitize_key( $_POST['life_stage'] ) : '';

                        if ( ! $people->exists ) {
                            $contact->update_life_stage( $life_stage );

                        } else {
                            if ( ! $contact->get_life_stage() ) {
                                $contact->update_life_stage( $life_stage );
                            }
                        }

                        if ( ! empty( $_POST['contact_group'] ) ) {
                            $contact_group = absint( $_POST['contact_group'] );

                            $existing_data = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( [
                                'group_id' => $contact_group,
                                'user_id'  => $people->id
                            ] )->first();

                            if ( empty( $existing_data ) ) {
                                $hash = sha1( microtime() . 'erp-subscription-form' . $contact_group . $people->id );

                                erp_crm_create_new_contact_subscriber( [
                                    'group_id'       => $contact_group,
                                    'user_id'        => $people->id,
                                    'status'         => 'subscribe',
                                    'subscribe_at'   => current_time( 'mysql' ),
                                    'unsubscribe_at' => null,
                                    'hash'           => $hash
                                ] );
                            }
                        }


                        if ( ! empty( $field_builder_contacts_fields ) ) {
                            foreach ( $field_builder_contacts_fields as $field ) {
                                if ( isset( $line_data[ $field ] ) ) {
                                    erp_people_update_meta( $people->id, $field, $line_data[ $field ] );
                                }
                            }
                        }
                    }
                }

                ++ $count;
            }

        }

        wp_redirect( admin_url( "admin.php?page=erp-tools&tab=import&imported=$count" ) );
        exit;
    }

    if ( isset( $_POST['erp_export_csv'] ) ) {
        if ( ! empty( $_POST['type'] ) && ! empty( $_POST['fields'] ) ) {
            $type   = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
            $fields = array_map( 'sanitize_text_field', wp_unslash( $_POST['fields'] ) );

            if ( $type == 'employee' && $is_hrm_activated ) {
                $args = [
                    'number' => - 1,
                    'status' => 'all'
                ];

                $items = erp_hr_get_employees( $args );
            }

            if ( ( $type == 'contact' || $type == 'company' ) && $is_crm_activated ) {
                $args        = [
                    'type'  => $type,
                    'count' => true,
                ];
                $total_items = erp_get_peoples( $args );

                $args  = [
                    'type'   => $type,
                    'offset' => 0,
                    'number' => - 1,
                ];
                $items = erp_get_peoples( $args );
            }

            //@todo do_action()
            $csv_items = [];

            $x = 0;
            foreach ( $items as $item ) {

                if ( empty( $fields ) ) {
                    continue;
                }

                foreach ( $fields as $field ) {
                    if ( $type == 'employee' ) {

                        if ( in_array( $field, $field_builder_employees_fields ) ) {
                            $csv_items[ $x ][ $field ] = get_user_meta( $item->id, $field, true );
                        } else {
                            switch ( $field ) {
                                case 'department':
                                    $csv_items[ $x ][ $field ] = $item->get_department_title();
                                    break;

                                case 'designation':
                                    $csv_items[ $x ][ $field ] = $item->get_job_title();
                                    break;

                                default:
                                    $csv_items[ $x ][ $field ] = $item->{$field};
                                    break;
                            }
                        }

                    } else {
                        if ( $type == 'contact' ) {
                            if ( in_array( $field, $field_builder_contacts_fields ) ) {
                                $csv_items[ $x ][ $field ] = erp_people_get_meta( $item->id, $field, true );
                            } else {
                                $csv_items[ $x ][ $field ] = $item->{$field};
                            }
                        }
                        if ( $type == 'company' ) {
                            if ( in_array( $field, $field_builder_companies_fields ) ) {
                                $csv_items[ $x ][ $field ] = erp_people_get_meta( $item->id, $field, true );
                            } else {
                                $csv_items[ $x ][ $field ] = $item->{$field};
                            }
                        }
                    }
                }

                $x ++;
            }

            $file_name = 'export_' . date( 'd_m_Y' ) . '.csv';

            erp_make_csv_file( $csv_items, $file_name );

        } else {
            wp_redirect( admin_url( "admin.php?page=erp-tools&tab=export" ) );
            exit();
        }
    }
}

/**
 * Display importer tool notice.
 *
 *
 * @return void
 */
function erp_importer_notices() {
    if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != 'erp-tools' || ! isset( $_REQUEST['tab'] ) || $_REQUEST['tab'] != 'import' ) {
        return;
    }

    if ( isset( $_REQUEST['imported'] ) ) {
        if ( intval( $_REQUEST['imported'] ) == 0 ) {
            $message = __( 'Nothing to import or items are already exists.', 'erp' );
            echo "<div class='notice error'><p>" . esc_html( $message ) . "</p></div>";
        } else {
            $message = sprintf( __( '%s items successfully imported.', 'erp' ),
                number_format_i18n( sanitize_text_field( wp_unslash( $_REQUEST['imported'] ) ) )
            );
            echo "<div class='notice updated'><p>" . esc_html( $message ) . "</p></div>";
        }
    }
}

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is similiar to wordpress wp_parse_args().
 * It's support multidimensional array.
 *
 * @param  array $args
 * @param  array $defaults Optional.
 *
 * @return array
 */
function erp_parse_args_recursive( &$args, $defaults = [] ) {
    $args     = (array) $args;
    $defaults = (array) $defaults;
    $r        = $defaults;

    foreach ( $args as $k => &$v ) {
        if ( is_array( $v ) && isset( $r[ $k ] ) ) {
            $r[ $k ] = erp_parse_args_recursive( $v, $r[ $k ] );
        } else {
            $r[ $k ] = $v;
        }
    }

    return $r;
}

/**
 * ERP Email sender
 *
 * @since 1.1.0
 * @since 1.1.17 Use site name instead of current user name for default From header
 * @since 1.2.0  Always return true during any importing process
 *
 * @param string|array $to
 * @param string       $subject
 * @param string       $message
 * @param string|array $headers
 * @param array        $attachments
 * @param array        $custom_headers
 *
 * @return boolean
 */
function erp_mail( $to, $subject, $message, $headers = '', $attachments = [], $custom_headers = [] ) {

    if ( defined( 'ERP_IS_IMPORTING' ) && ERP_IS_IMPORTING ) {
        return true;
    }

    $callback = function ( $phpmailer ) use ( $custom_headers ) {
        $erp_email_settings      = get_option( 'erp_settings_erp-email_general', [] );
        $erp_email_smtp_settings = get_option( 'erp_settings_erp-email_smtp', [] );

        if ( ! isset( $erp_email_settings['from_email'] ) ) {
            $from_email = get_option( 'admin_email' );
        } else {
            $from_email = $erp_email_settings['from_email'];
        }

        if ( ! isset( $erp_email_settings['from_name'] ) ) {
            $from_name = get_bloginfo( 'name' );
        } else {
            $from_name = $erp_email_settings['from_name'];
        }

        $content_type = 'text/html';

        $phpmailer->From        = apply_filters( 'erp_mail_from', $from_email );
        $phpmailer->FromName    = apply_filters( 'erp_mail_from_name', $from_name );
        $phpmailer->ContentType = apply_filters( 'erp_mail_content_type', $content_type );

        //Return-Path
        $phpmailer->Sender = apply_filters( 'erp_mail_return_path', $phpmailer->From );

        if ( ! empty( $custom_headers ) ) {
            foreach ( $custom_headers as $key => $value ) {
                $phpmailer->addCustomHeader( $key, $value );
            }
        }

        if ( isset( $erp_email_smtp_settings['debug'] ) && $erp_email_smtp_settings['debug'] == 'yes' ) {
            $phpmailer->SMTPDebug = true;
        }

        if ( isset( $erp_email_smtp_settings['enable_smtp'] ) && $erp_email_smtp_settings['enable_smtp'] == 'yes' ) {
            $phpmailer->Mailer = 'smtp'; //'smtp', 'mail', or 'sendmail'

            $phpmailer->Host       = $erp_email_smtp_settings['mail_server'];
            $phpmailer->SMTPSecure = ( $erp_email_smtp_settings['authentication'] != '' ) ? $erp_email_smtp_settings['authentication'] : 'smtp';
            $phpmailer->Port       = $erp_email_smtp_settings['port'];

            if ( $erp_email_smtp_settings['authentication'] != '' ) {
                $phpmailer->SMTPAuth = true;
                $phpmailer->Username = $erp_email_smtp_settings['username'];
                $phpmailer->Password = $erp_email_smtp_settings['password'];
            }
        }
    };

    add_action( 'phpmailer_init', $callback );

    ob_start();
    $is_mail_sent = wp_mail( $to, $subject, $message, $headers, $attachments );
    $debug_log    = ob_get_clean();
    if ( ! $is_mail_sent ) {
        error_log( $debug_log );
    }

    remove_action( 'phpmailer_init', $callback );

    return $is_mail_sent;
}

function erp_mail_send_via_gmail( $to, $subject, $message, $headers = '', $attachments = [], $custom_headers = [] ) {

    global $phpmailer;

    // (Re)create it, if it's gone missing
    if ( ! ( $phpmailer instanceof PHPMailer ) ) {
        require_once ABSPATH . WPINC . '/class-phpmailer.php';
        require_once ABSPATH . WPINC . '/class-smtp.php';
        $phpmailer = new PHPMailer( true );
    }

    // Headers
    $cc = $bcc = $reply_to = array();

    if ( empty( $headers ) ) {
        $headers = array();
    } else {
        if ( !is_array( $headers ) ) {
            // Explode the headers out, so this function can take both
            // string headers and an array of headers.
            $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        } else {
            $tempheaders = $headers;
        }
        $headers = array();

        // If it's actually got contents
        if ( !empty( $tempheaders ) ) {
            // Iterate through the raw headers
            foreach ( (array) $tempheaders as $header ) {
                if ( strpos($header, ':') === false ) {
                    if ( false !== stripos( $header, 'boundary=' ) ) {
                        $parts = preg_split('/boundary=/i', trim( $header ) );
                        $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                    }
                    continue;
                }
                // Explode them out
                list( $name, $content ) = explode( ':', trim( $header ), 2 );

                // Cleanup crew
                $name    = trim( $name    );
                $content = trim( $content );

                switch ( strtolower( $name ) ) {
                    // Mainly for legacy -- process a From: header if it's there
                    case 'from':
                        $bracket_pos = strpos( $content, '<' );
                        if ( $bracket_pos !== false ) {
                            // Text before the bracketed email is the "From" name.
                            if ( $bracket_pos > 0 ) {
                                $from_name = substr( $content, 0, $bracket_pos - 1 );
                                $from_name = str_replace( '"', '', $from_name );
                                $from_name = trim( $from_name );
                            }

                            $from_email = substr( $content, $bracket_pos + 1 );
                            $from_email = str_replace( '>', '', $from_email );
                            $from_email = trim( $from_email );

                            // Avoid setting an empty $from_email.
                        } elseif ( '' !== trim( $content ) ) {
                            $from_email = trim( $content );
                        }
                        break;
                    case 'content-type':
                        if ( strpos( $content, ';' ) !== false ) {
                            list( $type, $charset_content ) = explode( ';', $content );
                            $content_type = trim( $type );
                            if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                            } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                $charset = '';
                            }

                            // Avoid setting an empty $content_type.
                        } elseif ( '' !== trim( $content ) ) {
                            $content_type = trim( $content );
                        }
                        break;
                    case 'cc':
                        $cc = array_merge( (array) $cc, explode( ',', $content ) );
                        break;
                    case 'bcc':
                        $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                        break;
                    case 'reply-to':
                        $reply_to = array_merge( (array) $reply_to, explode( ',', $content ) );
                        break;
                    default:
                        // Add it to our grand headers array
                        $headers[trim( $name )] = trim( $content );
                        break;
                }
            }
        }
    }

    $phpmailer->clearAllRecipients();
    $phpmailer->clearAttachments();
    $phpmailer->clearCustomHeaders();
    $phpmailer->clearReplyTos();

    $from_email = get_option( 'erp_gmail_authenticated_email', true );
    $from_name  = erp_crm_get_email_from_name();

    $content_type = 'text/html';

    $phpmailer->From        = $from_email;
    $phpmailer->FromName    = $from_name;
    $phpmailer->ContentType = apply_filters( 'erp_mail_content_type', $content_type );

    // Set whether it's plaintext, depending on $content_type
    if ( 'text/html' == $content_type )
        $phpmailer->isHTML( true );

    //Return-Path
    $phpmailer->Sender = apply_filters( 'erp_mail_return_path', $phpmailer->From );

    if ( ! empty( $custom_headers ) ) {
        foreach ( $custom_headers as $key => $value ) {
            $phpmailer->addCustomHeader( $key, $value );
        }
    }

    // Set mail's subject and body
    $phpmailer->Subject = $subject;
    $phpmailer->Body    = $message;
    // Set destination addresses, using appropriate methods for handling addresses
    $address_headers = compact( 'to', 'cc', 'bcc', 'reply_to' );
    $address_headers['reply_to'] = [ $from_name. ' <'.$from_email.'>' ];

    foreach ( $address_headers as $address_header => $addresses ) {
        if ( empty( $addresses ) ) {
            continue;
        }

        foreach ( (array) $addresses as $address ) {
            try {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                $recipient_name = '';

                if ( preg_match( '/(.*)<(.+)>/', $address, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $recipient_name = $matches[1];
                        $address        = $matches[2];
                    }
                }

                switch ( $address_header ) {
                    case 'to':
                        $phpmailer->addAddress( $address, $recipient_name );
                        break;
                    case 'cc':
                        $phpmailer->addCc( $address, $recipient_name );
                        break;
                    case 'bcc':
                        $phpmailer->addBcc( $address, $recipient_name );
                        break;
                    case 'reply_to':
                        $phpmailer->addReplyTo( $address, $recipient_name );
                        break;
                }
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    //add attachments
    if ( !empty( $attachments ) ) {
        foreach ( $attachments as $attachment ) {
            try {
                $phpmailer->addAttachment( $attachment );
            }
            catch ( phpmailerException $e ) {
                continue;
            }
        }
    }

    $phpmailer->preSend();

    $email = new \Google_Service_Gmail_Message();

    $base64 = str_replace(
        array( '+', '/', '=' ),
        array( '-', '_', '' ),
        base64_encode( $phpmailer->getSentMIMEMessage() )
    ); // url safe.

    $email->setRaw( $base64 );

    $service = new \Google_Service_Gmail( wperp()->google_auth->get_client() );
    try {
        $response = $service->users_messages->send( 'me', $email );
        error_log('Sending email to : '. $to);
    } catch ( Google_Service_Exception $exception ) {
        error_log( 'Failed sending email to : ------------------------ ' );
        error_log(print_r( $to,1 ) );
        error_log(print_r( $subject,1 ) );
        error_log(print_r( $headers,1 ) );
        error_log( print_r( $exception->getMessage(), 1));
//        error_log(print_r(debug_backtrace(),1));
        error_log( '-------------------------------' );
        return false;
    }

    return true;
}

/**
 * Email JavaScript enqueue.
 *
 * @return void
 */
function erp_email_settings_javascript() {
    wp_enqueue_style( 'erp-sweetalert' );
    wp_enqueue_script( 'erp-sweetalert' );
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $("a#smtp-test-connection").click(function (e) {
                e.preventDefault();
                $("a#smtp-test-connection").attr('disabled', 'disabled');
                $("a#smtp-test-connection").parent().find('.erp-loader').show();

                var data = {
                    'action': 'erp_smtp_test_connection',
                    'enable_smtp': $('input[name=enable_smtp]:checked').val(),
                    'mail_server': $('input[name=mail_server]').val(),
                    'port': $('input[name=port]').val(),
                    'authentication': $('select[name=authentication]').val(),
                    'username': $('input[name=username]').val(),
                    'password': $('input[name=password]').val(),
                    'to': $('#smtp_test_email_address').val(),
                    '_wpnonce': '<?php echo esc_html( wp_create_nonce( "erp-smtp-test-connection-nonce" ) ); ?>'
                };

                $.post(ajaxurl, data, function (response) {
                    $("a#smtp-test-connection").removeAttr('disabled');
                    $("a#smtp-test-connection").parent().find('.erp-loader').hide();

                    var type = response.success ? 'success' : 'error';

                    if (response.data) {
                        swal({
                            title: '',
                            text: response.data,
                            type: type,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#008ec2'
                        });
                    }
                });
            });
        });

        jQuery(document).ready(function ($) {
            $("a#imap-test-connection").click(function (e) {
                e.preventDefault();
                $("a#imap-test-connection").attr('disabled', 'disabled');
                $("a#imap-test-connection").parent().find('.erp-loader').show();

                var data = {
                    'action': 'erp_imap_test_connection',
                    'mail_server': $('input[name=mail_server]').val(),
                    'username': $('input[name=username]').val(),
                    'password': $('input[name=password]').val(),
                    'protocol': $('select[name=protocol]').val(),
                    'port': $('input[name=port]').val(),
                    'authentication': $('select[name=authentication]').val(),
                    '_wpnonce': '<?php echo esc_html( wp_create_nonce( "erp-imap-test-connection-nonce" ) ); ?>'
                };

                $.post(ajaxurl, data, function (response) {
                    $("a#imap-test-connection").removeAttr('disabled');
                    $("a#imap-test-connection").parent().find('.erp-loader').hide();

                    var type = response.success ? 'success' : 'error';

                    if (response.data) {
                        var status = response.success ? 1 : 0;
                        $('#imap_status').val(status);

                        swal({
                            title: '',
                            text: response.data,
                            type: type,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#008ec2'
                        });
                    }
                });
            });
        });
    </script>
    <?php
}

/**
 * Determine if the inbound/imap mail feature is active or not.
 *
 * @return boolean
 */
function erp_is_imap_active() {
    $options = get_option( 'erp_settings_erp-crm_email_connect_imap', [] );

    $imap_status = (boolean) isset( $options['imap_status'] ) ? $options['imap_status'] : 0;
    $enable_imap = ( isset( $options['enable_imap'] ) && $options['enable_imap'] == 'yes' ) ? true : false;

    if ( $enable_imap && $imap_status ) {
        return true;
    }

    return false;
}

/**
 * Check if the ERP Email SMTP settings is enabled or not
 *
 * @since 1.1.6
 *
 * @return boolean
 */
function erp_is_smtp_enabled() {
    $erp_email_smtp_settings = get_option( 'erp_settings_erp-email_smtp', [] );

    if ( isset( $erp_email_smtp_settings['enable_smtp'] ) && filter_var( $erp_email_smtp_settings['enable_smtp'], FILTER_VALIDATE_BOOLEAN ) ) {
        return true;
    }

    return false;
}

/**
 * Determine if the module is active or not.
 *
 * @return boolean
 */
function erp_is_module_active( $module_key ) {
    $modules = get_option( 'erp_modules', [] );

    return isset( $modules[ $module_key ] );
}

/**
 * Make csv file from array and force download
 *
 * @param array   $items
 * @param boolean $field_data (optional)
 *
 * @param string  $file_name
 */
function erp_make_csv_file( $items, $file_name, $field_data = true ) {
    $file_name = ( ! empty( $file_name ) ) ? $file_name : 'csv_' . date( 'd_m_Y' ) . '.csv';

    if ( empty( $items ) ) {
        return;
    }

    $columns = array_keys( $items[0] );

    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=' . $file_name );

    $output = fopen( 'php://output', 'w' );

    $columns = array_map( function ( $column ) {
        $column = ucwords( str_replace( '_', ' ', $column ) );

        return $column;
    }, $columns );

    fputcsv( $output, $columns );

    if ( $field_data ) {
        foreach ( $items as $item ) {
            $csv_row = array_map( function ( $item_val ) {

                if ( is_array( $item_val ) ) {
                    return implode( ', ', $item_val );
                }

                return $item_val;

            }, $item );

            fputcsv( $output, $csv_row );
        }
    }

    exit();
}

/**
 * Import/Export sample CSV download action hook
 *
 * @param void
 */
function erp_import_export_download_sample_action() {

    $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

    if ( ! isset( $_GET['action'] ) || $_GET['action'] != 'download_sample' ) {
        return;
    }

    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'erp-emport-export-sample-nonce' ) ) {
        return;
    }

    if ( ! isset( $type ) ) {
        return;
    }

    $type   = strtolower( $type );
    $fields = erp_get_import_export_fields();

    if ( isset( $fields[ $type ] ) ) {
        $keys      = $fields[ $type ]['fields'];
        $keys      = array_flip( $keys );
        $file_name = "sample_csv_{$type}.csv";

        erp_make_csv_file( [ $keys ], $file_name, false );
    }

    return;
}

/**
 * Enqueue locale scripts for fullcalendar
 *
 * @since 1.0.0
 *
 * @return void
 */
function enqueue_fullcalendar_locale() {
    $locale = get_locale();
    $script = '';

    // no need to add locale for en_US
    if ( 'en_US' === $locale ) {
        return;
    }

    $locale = explode( '_', $locale );

    // make sure we have two segments - 1.lang, 2.country
    if ( count( $locale ) < 2 ) {
        return;
    }

    $lang    = $locale[0];
    $country = strtolower( $locale[1] );

    if ( $lang === $country ) {
        $script = $lang;
    } else {
        $script = $lang . '-' . $country;
    }

    if ( file_exists( WPERP_PATH . "/assets/vendor/fullcalendar/lang/{$script}.js" ) ) {
        wp_enqueue_script( 'erp-fullcalendar-locale', WPERP_ASSETS . "/vendor/fullcalendar/lang/{$script}.js", array( 'erp-fullcalendar' ), null, true );
    }
}

/**
 * Generate random key
 *
 * @since 1.1.8
 *
 * @return string
 */
function erp_generate_key() {
    if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
        $key = bin2hex( openssl_random_pseudo_bytes( 20 ) );
    } else {
        $key = sha1( wp_rand() );
    }

    return $key;
}

/**
 * Include required HTML form erp-popup
 *
 * @since 1.1.12
 *
 * @return void
 */
function erp_include_popup_markup() {
    include_once WPERP_INCLUDES . '/admin/views/erp-modal.php';
    erp_get_js_template( WPERP_INCLUDES . '/admin/views/address.php', 'erp-address' );
}

/**
 * Dequeue/Deregister select2 from other plugins
 *
 * @since 1.1.13
 *
 * @return void
 */
function erp_dequeue_other_select2_sources() {
    // select2 handle is used by woocommerce
    wp_deregister_script( 'select2' );
    wp_dequeue_script( 'select2' );
}

/**
 * Remove select2 enqueued by other plugins
 *
 * Whenever enqueue erp-select2, call this function to
 * make sure only one select2 is loaded
 *
 * @since 1.1.13
 *
 * @return void
 */
function erp_remove_other_select2_sources() {
    add_action( 'admin_enqueue_scripts', 'erp_dequeue_other_select2_sources', 999999 );
    add_action( 'wp_enqueue_scripts', 'erp_dequeue_other_select2_sources', 999999 );
}

/**
 * Returns a word in plural form.
 *
 * @since 1.1.16
 *
 * @param string $word The word in singular form.
 *
 * @return string The word in plural form.
 */
function erp_pluralize( $word ) {
    return \Doctrine\Common\Inflector\Inflector::pluralize( $word );
}

/**
 * Get the client IP address
 *
 * @since 1.1.16
 *
 * @return string
 */
function erp_get_client_ip() {
    $ipaddress = '';

    if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
    } else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
    } else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED'] ) );
    } else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED_FOR'] ) );
    } else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) );
    } else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

/**
 * Converts any value to boolean true or false
 *
 * @since 1.2.0
 *
 * @param mixed $value
 *
 * @return boolean
 */
function erp_validate_boolean( $value ) {
    return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Get financial year start and end dates
 * @param $date
 * @since 1.2.0
 * since 1.3.0 $date added
 * @return array
 */
function erp_get_financial_year_dates( $date = null ) {
    $start_month = erp_get_option( 'gen_financial_month', 'erp_settings_general', 1 );
    if ( $date == null ) {
        $year  = date( 'Y' );
        $month = date( 'n' );
    } else {
        $year  = date( 'Y', strtotime( $date ) );
        $month = date( 'n', strtotime( $date ) );
    }

    /**
     * Suppose, $start_month is July and today is May 2017. Then we should get
     * start = 2016-07-01 00:00:00 and end = 2017-06-30 23:59:59.
     *
     * On the other hand, if $start_month = January, then we should get
     * start = 2017-01-01 00:00:00 and end = 2017-12-31 23:59:59.
     */
    if ( $month < $start_month ) {
        $year = $year - 1;
    }

    $months = erp_months_dropdown();
    $start  = date( 'Y-m-d 00:00:00', strtotime( "first day of $months[$start_month] $year" ) );
    $end    = date( 'Y-m-d 23:59:59', strtotime( "$start + 12 months - 1 day" ) );

    return [
        'start' => $start,
        'end'   => $end
    ];
}

/**
 * Get finanicial start and end years that a date belongs to
 *
 * @since 1.2.0
 *
 * @param string $date
 *
 * @return array
 */
function get_financial_year_from_date( $date ) {
    $fy_start_month = erp_get_option( 'gen_financial_month', 'erp_settings_general', 1 );
    $fy_start_month = absint( $fy_start_month );

    $date_timestamp = strtotime( $date );
    $date_year      = absint( date( 'Y', $date_timestamp ) );
    $date_month     = absint( date( 'n', $date_timestamp ) );

    if ( 1 === $fy_start_month ) {
        return [
            'start' => $date_year,
            'end'   => $date_year
        ];

    } else if ( $date_month <= ( $fy_start_month - 1 ) ) {
        return [
            'start' => ( $date_year - 1 ),
            'end'   => $date_year
        ];

    } else {
        return [
            'start' => $date_year,
            'end'   => ( $date_year + 1 )
        ];
    }
}

/**
 * Redirect erp role based user to their page
 *
 * @since 1.2.5
 *
 * @param $redirect_to
 * @param $request
 * @param $user
 *
 * @return string
 */
function erp_login_redirect_manager( $redirect_to, $request, $user ) {
    $is_erp_redirect = erp_get_option( 'role_based_login_redirection', 'erp_settings_general', false );

    if ( $is_erp_redirect && isset( $user->roles ) && is_array( $user->roles ) && ! in_array( 'administrator', $user->roles ) ) {
        return $redirect_to = apply_filters( 'erp_login_redirect', $redirect_to, $user->roles );
    } else {
        return $redirect_to;
    }
}

/**
 * Flatten any multi dimensional array
 *
 * @since 1.3.0
 *
 * @param $array
 *
 * @return array|bool
 */
function erp_array_flatten( $array ) {
    if ( ! is_array( $array ) ) {
        return false;
    }
    $result = array();
    foreach ( $array as $key => $value ) {
        if ( is_array( $value ) ) {
            $result = array_merge( $result, erp_array_flatten( $value ) );
        } else {
            $result[ $key ] = $value;
        }
    }

    return $result;
}



/**
 * Fetch a filtered list of user roles that the current user is
 * allowed to edit.
 * @since 1.3.1 function has been from crm
 * @since 1.2.4
 *
 * @return array
 */
function erp_get_editable_roles (){
    if(!  function_exists('get_editable_roles')){
        require_once(ABSPATH . 'wp-admin/includes/user.php');
    }
    $wp_roles = get_editable_roles();

    if( !current_user_can( 'administrator' ) ){
        unset( $wp_roles['administrator'] );
    }

    $roles =  apply_filters( 'erp_editable_roles', $wp_roles );

    return $roles;
}

/**
 * Get dates in range
 *
 * @since 1.3.2
 *
 * @param $first
 * @param $last
 * @param string $step
 * @param string $output_format
 *
 * @return array
 *
 */
function erp_get_dates_in_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {

    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while( $current <= $last ) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

/**
 * Sanitize a string destined to be a tooltip.
 * Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 *
 * @param string $var
 * @return string
 * @since 1.3.4
 */
function erp_sanitize_tooltip( $var ) {
    return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
        'br'     => array(),
        'em'     => array(),
        'strong' => array(),
        'small'  => array(),
        'span'   => array(),
        'ul'     => array(),
        'li'     => array(),
        'ol'     => array(),
        'p'      => array(),
    ) ) );
}

/**
 * Display an ERP help tip.
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 * @since 1.3.4
 */
function erp_help_tip( $tip, $allow_html = false ) {
    if ( $allow_html ) {
        $tip = erp_sanitize_tooltip( $tip );
    } else {
        $tip = wp_kses_post( $tip );
    }

    return '<span class="erp-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Letter to number converter
 * @param  $size
 * @return $ret
 * @since 1.3.4
 */
function erp_let_to_num( $size ) {
    $l   = substr( $size, -1 );
    $ret = substr( $size, 0, -1 );
    switch ( strtoupper( $l ) ) {
        case 'P':
            $ret *= 1024;
        case 'T':
            $ret *= 1024;
        case 'G':
            $ret *= 1024;
        case 'M':
            $ret *= 1024;
        case 'K':
            $ret *= 1024;
    }

    return $ret;
}

/**
 * Get ERP Menu array
 *
 * @since 1.4.0
 *
 * @return array $menu
 */
function erp_menu() {
    $menu = [];
    return apply_filters( 'erp_menu', $menu );
}

/**
 * Add a menu item into ERP Menu
 *
 * @since 1.4.0
 *
 * @param String $component Name of Component to add menu
 *
 * @param array $args
 *
 * @return void
 */
function erp_add_menu( $component, $args ) {
    add_filter('erp_menu', function($menu) use( $component, $args ) {
        $menu[ $component ][$args['slug']] = $args;

        return $menu;
    });
}

/**
 * Adds a submenu under a Menu item
 *
 * @since 1.4.0
 *
 * @param string $component Name of Component to add menu
 *
 * @param string $parent Slug of Parent menu item
 *
 * @param array $args
 *
 * @return void
 */
function erp_add_submenu( $component, $parent, $args ) {
    add_filter( 'erp_menu', function ( $menu ) use ( $component, $parent, $args ) {
        if ( !isset( $menu[$component][$parent] ) ) {
            return $menu;
        }
        $args['parent'] = $parent;
        $menu[$component][$parent]['submenu'][$args['slug']] = $args;

        return $menu;
    } );
}

/**
 * Render A menu for given component
 *
 * @since 1.4.0
 *
 * @param string $component slug of Component to render
 *
 * @return bool
 */
function erp_render_menu( $component ) {
    $menu = erp_menu();

    if ( !isset( $menu[$component] ) ) {
        return false;
    }
    //check current tab
    $tab = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'dashboard';

    echo "<div class='erp-nav-container erp-hide-print'>";
    echo erp_render_menu_header( $component );
    echo wp_kses_post( erp_build_menu( $menu[$component], $tab, $component ) );
    echo "</div>";
}

/**
 * Build html for ERP menu
 *
 * @since 1.4.0
 *
 * @param $items
 *
 * @param $active
 *
 * @param $component main component slug
 *
 * @param bool $dropdown
 *
 * @return string
 */
function erp_build_menu( $items, $active, $component, $dropdown = false ) {


    //check capability
    $items = array_filter( $items, function( $item ) {
        if ( !isset( $item['capability'] ) ) {
            return false;
        }
        return current_user_can( $item['capability'] );
    } );

    //sort items for position
    uasort( $items, function ( $a, $b ) {
        return $a['position'] > $b['position'];
    } );

    $html = '<ul class="erp-nav -primary erp-hide-print">';

    if ( $dropdown ) {
        $html = '<ul class="erp-nav-dropdown">';
    }
    foreach ( $items as $item ) {

        $link = add_query_arg( [ 'page' => 'erp-'.$component, 'section' => $item['slug'] ], admin_url( 'admin.php' ) );

        $class = $active == $item['slug'] ? 'active ' : '';

        if ( $dropdown ) {
            $link = add_query_arg( [ 'page' => 'erp-' . $component, 'section' => $item['parent'], 'sub-section' => $item['slug'] ], admin_url( 'admin.php' ) );
            $class .= ( !empty( $_GET['sub-section'] ) && $_GET['sub-section'] == $item['slug'] ) ? 'active ' : '';
        }

        if ( !empty( $item['direct_link'] ) ) {
            $link = $item['direct_link'];
        }

        $submenu =  '';

        if ( isset( $item['submenu'] ) ) {
            $class.= "dropdown-nav";
            $submenu .= erp_build_menu( $item['submenu'], $active, $component, true );
        }

        $html .= sprintf( '<li class="%s"><a href="%s">%s</a>%s</li>', $class, $link, $item['title'], $submenu );
    }

    $html .= '</ul>';

    return $html;
}

/**
 * Check if the current page is contact or company listing
 *
 * @since 1.4.0
 *
 * @return bool
 */
function erp_is_contacts_page() {
    if ( empty( $_GET['page'] ) || $_GET['page'] != 'erp-crm' ) {
        return false;
    }

    if ( empty( $_GET['section'] ) || $_GET['section'] != 'contacts' || $_GET['section'] != 'companies' ) {
        return false;
    }

    return true;
}

/**
 * Check if the current page is valid for given args
 *
 * @since 1.4.1
 *
 * @param string $page
 * @param string $section
 * @param string $subsection
 *
 * @return bool
 */
function erp_is_current_page( $page, $section, $subsection = '' ) {
    if ( empty( $_GET['page'] ) || $_GET['page'] != $page ) {
        return false;
    }

    if ( empty( $_GET['section'] ) || $_GET['section'] != $section ) {
        return false;
    }

    if ( !empty( $subsection ) ) {
        if ( empty( $_GET['sub-section'] ) || $_GET['sub-section'] != $subsection ) {
            return false;
        }
    }

    return true;
}

/**
 * Get ERP Menu array
 *
 * @since 1.4.0
 *
 * @return array $menu
 */
function erp_get_menu_headers() {
    $menu = [];
    return apply_filters( 'erp_menu_headers', $menu );
}

/**
 * Add Header part of Component
 *
 * @param $component
 * @param $title
 * @param string $icon
 */
function erp_add_menu_header( $component, $title, $icon = "" ) {
    add_filter('erp_menu_headers', function($menu) use( $component, $title, $icon ) {
        $menu[ $component ] = [ 'title' => $title, 'icon' => $icon ];
        return $menu;
    });
}

/**
 * Render header part of erp menu
 *
 * @param $component
 *
 * @return string
 */
function erp_render_menu_header( $component ) {
    $headers = erp_get_menu_headers();
    if ( empty( $headers[$component] ) ) {
       return "";
    }

    $html = sprintf( '<div class="erp-page-header">
                        <div class="module-icon">
                            %s
                        </div>
                        <h2>%s</h2>
                    </div>',
        $headers[$component]['icon'], $headers[$component]['title'] );

    return $html;
}

/**
 * RSS feed
 *
 * @return void
 */
function erp_web_feed() {
    $url="https://wperp.com/feed/";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);

    return simplexml_load_string($data);
}


/**
 * Build Mega for html for ERP Mega menu
 *
 * @since 1.4.0
 *
 * @param $items
 *
 * @param $active
 *
 * @param $component main component slug
 *
 * @param bool $dropdown
 *
 * @return string
 */
function erp_build_mega_menu( $items, $active, $component, $dropdown = false ) {

    //check capability
    $items = array_filter( $items, function( $item ) {
        if ( !isset( $item['capability'] ) ) {
            return false;
        }
        return current_user_can( $item['capability'] );
    } );

    //sort items for position
    uasort( $items, function ( $a, $b ) {
        return $a['position'] > $b['position'];
    } );

    $html = '<ul class="erp-nav -primary">';

    if ( $dropdown ) {
        $html = '<ul class="erp-nav-dropdown">';
    }
    foreach ( $items as $item ) {

        if ( $component === 'accounting' ) {
            $link = add_query_arg( [ 'page' => 'erp-'.$component . '#/' . $item['slug'] ], admin_url( 'admin.php' ) );
        } else {
            $link = add_query_arg( [ 'page' => 'erp-'.$component, 'section' => $item['slug'] ], admin_url( 'admin.php' ) );
        }

        $class = $active == $item['slug'] ? 'active ' : '';
        if ( $dropdown ) {
            $link = add_query_arg( [ 'page' => 'erp-' . $component, 'section' => $item['parent'], 'sub-section' => $item['slug'] ], admin_url( 'admin.php' ) );
            $class .= ( !empty( $_GET['sub-section'] ) && $_GET['sub-section'] == $item['slug'] ) ? 'active ' : '';
        }

        if ( !empty( $item['direct_link'] ) ) {
            $link = $item['direct_link'];
        }

        $html .= sprintf( '<li class="%s"><a href="%s">%s</a></li>', $class, $link, __( $item['title'], 'erp' ) );
    }

    $html .= '</ul>';

    return $html;
}

/**
 * Get currencies dropdown
 *
 * @return array
 */
function erp_get_currencies_for_dropdown() {
    global $wpdb;

    $currencies = $wpdb->get_results( "SELECT id, name, sign FROM {$wpdb->prefix}erp_acct_currency_info", ARRAY_A );

    $currencies_dropdown = [];

    foreach ( $currencies as $currency ) {
        $currencies_dropdown[$currency['id']] = $currency['name'] . ' (' . $currency['sign'] . ')';
    }

    return $currencies_dropdown;
}

/**
 * Old functions
 * should be updated ASAP
 *
================================*/

/**
 * Return the thousand separator for prices.
 *
 * @since  2.3
 *
 * @return string
 */
function erp_ac_get_price_thousand_separator() {
    $separator = stripslashes( erp_get_option( 'erp_ac_th_separator', false, ',' ) );

    return $separator;
}


/**** Add Enable Disable section for All Pre-generated email End ****/

/**
 * Return the enable/disable column checkbox of email.
 *
 * @since  1.5.6
 *
 * @return string
 */
function add_enable_disable_section_to_email_column( $email ) {

    $get_option_id    = $email->get_option_id();
    $get_option_value = get_option( $get_option_id );

    if ( isset( $get_option_value['is_enable'] ) ) {
        if ( $get_option_value['is_enable'] == 'yes' ) {
            $is_enable = ' checked';
        }
    } else {
        $is_enable = '';
    }
    $can_not_be_disabled = apply_filters( 'email_settings_enable_filter', [
        'erp_email_settings_new-leave-request',
        'erp_email_settings_approved-leave-request',
        'erp_email_settings_rejected-leave-request',
        'erp_email_settings_employee-asset-request',
        'erp_email_settings_employee-asset-approve',
        'erp_email_settings_employee-asset-reject',
        'erp_email_settings_employee-asset-overdue'
    ] );
    if ( in_array( $get_option_id, $can_not_be_disabled ) ) {
        echo '<td class="erp-settings-table-is_enable">
            <label class=""> &nbsp; </label>
        </td>';
    } else {
        echo '<td class="erp-settings-table-is_enable">
            <label class="cus_switch"><input type="checkbox" name="isEnableEmail['. esc_attr( $get_option_id ) .']"  ' . esc_attr( $is_enable ) . '><span class="cus_slider cus_round"></span></label>
        </td>';
    }
    /*echo '<td class="erp-settings-table-is_enable">
            <label class="cus_switch"><input type="checkbox" name="isEnableEmail['. $get_option_id .']"  ' . $is_enable . '><span class="cus_slider cus_round"></span></label>
        </td>';*/
}

/**
 * update enable/disable column checkbox of email.
 *
 * @since  1.5.6
 *
 * @return null
 */
function add_enable_disable_option_save() {
    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-nonce' ) ) {
        // die();
    }

    if ( isset( $_POST['save_email_enable_or_disable'] ) && $_POST['save_email_enable_or_disable'] == 'save_email_enable_or_disable' ) {
        $registered_email = array_keys( wperp()->emailer->get_emails() );
        foreach( $registered_email as $remail ) {
            $cur_email_init     = wperp()->emailer->get_email( $remail );
            $cur_email_id       = 'erp_email_settings_'.$cur_email_init->id;
            $cur_email_option   = get_option( $cur_email_id );
            if ( isset( $cur_email_option['is_enable'] ) ) {
                unset( $cur_email_option['is_enable'] );
                update_option( $cur_email_id, $cur_email_option );
            }
        }
        if ( isset( $_POST['isEnableEmail'] ) ) {

            $is_enable_email = array_map( 'sanitize_text_field', $_POST['isEnableEmail'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            $is_enable_email = array_map( 'wp_unslash', $is_enable_email );
            foreach ($is_enable_email as $key => $value) {
                $email_arr = get_option($key);
                $email_arr['is_enable'] = 'yes';
                update_option( $key, $email_arr );
            }
        }
    }
}

/**
 * Add enable/disable column checkbox of email.
 *
 * @since  1.5.6
 *
 * @return array
 */
function erp_email_setting_columns_add_enable_disable( $array ){
    $arr = [];
    $counter = 1;
    foreach( $array as $key => $value ) {
        $arr[$key] = $value;
        if ( count( $array ) - 1 == $counter ) {
            $arr['is_enable'] = __( 'Enable/Disable', 'erp' );
        }
        $counter++;
    }
    return $arr;
}

/**
 * Add hidden field at email settings page.
 *
 * @since  1.5.6
 *
 * @return array
 */
function add_checkbox_hidden_field( $fields, $section ) {
    $fields['general'][] = [
        'default'   => 'save_email_enable_or_disable',
        'type'      => 'hidden',
        'id'        => 'save_email_enable_or_disable',

    ];
    return $fields;
}

/**
 * Filtering emails if those are enabled OR disabled by settings.
 *
 * @since  1.5.6
 *
 * @return string
 */
function filter_enabled_email( $email ) {

    $get_option_id          = $email->get_option_id();
    $can_not_be_disabled    = apply_filters( 'email_settings_enable_filter', [
        'erp_email_settings_new-leave-request',
        'erp_email_settings_approved-leave-request',
        'erp_email_settings_rejected-leave-request',
        'erp_email_settings_employee-asset-request',
        'erp_email_settings_employee-asset-approve',
        'erp_email_settings_employee-asset-reject',
        'erp_email_settings_employee-asset-overdue'
    ] );
    if ( in_array( $get_option_id, $can_not_be_disabled ) ) {
        return $email;
    }
    $get_email_settings = get_option( $get_option_id );
    if ( isset( $get_email_settings['is_enable'] ) && $get_email_settings['is_enable'] == 'yes' ) {
        return $email;
    }
    add_filter( 'erp_email_recipient_'.$email->id, function( $recipient, $object){
        return '';
    }, 10, 2 );
    return $email;
}
/**** Add Enable Disable section for All Pre-generated email End ****/
