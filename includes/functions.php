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

		do_action( 'erp_action_' . $action, map_deep( wp_unslash( $_REQUEST ), 'sanitize_text_field' ) );
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
 * @param array  $caps    Capabilities for meta capability
 * @param string $cap     Capability name
 * @param int    $user_id User id
 * @param mixed  $args    Arguments
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
	return apply_filters(
		'erp_currencies',
		array(
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
		)
	);
}

/**
 * Get full list of currency ISO with symbol label.
 *
 * @return array
 */
function erp_get_currency_list_with_symbol() {
	$currencies       = erp_get_currencies();
	$currency_symbols = erp_get_currency_symbol();
	$currency_list    = array();

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
		$select   = ( $key === $selected ) ? ' selected="selected"' : '';
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

	$currency_id = erp_get_option( 'erp_currency', 'erp_settings_general', $usd );

	if ( $get_only_id ) {
		return $currency_id;
	}

	$currency_name = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT name FROM {$wpdb->prefix}erp_acct_currency_info WHERE id = %d",
			$currency_id
		)
	);

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
	$currency_symbols = array(
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
	);

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
	 * @return mixed stripped value
	 */
	function strip_tags_deep( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				$value[ $key ] = strip_tags_deep( $val );
			}
		} elseif ( is_string( $value ) ) {
			$value = wp_strip_all_tags( $value );
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
	 * @return mixed timmed value
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
 * @param string $label the label
 * @param string $value the value to print
 * @param string $sep   separator
 * @param string $type  field type
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
 * @return string the link
 */
function erp_get_clickable( $type = 'email', $value = '' ) {
	if ( 'email' === $type ) {
		return sprintf( '<a href="mailto:%1$s">%1$s</a>', $value );
	} elseif ( 'url' === $type ) {
		return sprintf( '<a target="_blank" href="%1$s">%1$s</a>', $value );
	} elseif ( 'phone' === $type ) {
		return sprintf( '<a href="tel:%1$s">%1$s</a>', $value );
	}
}

/**
 * Get erp date format setting
 *
 * @since 1.6.9
 *
 * @param mixed $format
 *
 * @return string
 */
function erp_get_date_format( $format = false ) {
	$format = $format ? $format : 'Y-m-d';

	return erp_get_option( 'date_format', 'erp_settings_general', $format );
}

/**
 * Get a formatted date from WordPress format
 *
 * @param string $date the date
 *
 * @return string formatted date
 */
function erp_format_date( $date, $format = false ) {
	if ( empty( $date ) ) {
		return false;
	}

	if ( ! $format ) {
		$format = erp_get_date_format();
	}

	if ( ! is_numeric( $date ) ) {
		$date = erp_current_datetime()->modify( $date )->getTimestamp();
	}

	if ( function_exists( 'wp_date' ) ) {
		return wp_date( $format, $date );
	}

	return date_i18n( $format, $date );
}

/**
 * Extract dates between two date range
 *
 * @param string $start_date example: 2016-12-16 00:00:00
 * @param string $end_date   example: 2016-12-16 23:59:59
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

	if ( $start_date > $end_date ) {
		$temp       = $start_date;
		$start_date = $end_date;
		$end_date   = $temp;
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
 * @param array $array array of arrays
 *
 * @return array
 */
function erp_array_to_object( $array = array() ) {
	$new_array = array();

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
 * @param date $start_date
 * @param date $end_date
 * @param date $date_from_user
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
 * @param date $start_date
 * @param date $end_date
 * @param date $user_date_start
 * @param date $user_date_end
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
 * @param date $start_date
 * @param date $end_date
 *
 * @return int
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
 * @param string $selected
 *
 * @return array
 */
function erp_performance_rating( $selected = '' ) {
	$rating = apply_filters(
		'erp_performance_rating',
		array(
			'1' => __( 'Very Bad', 'erp' ),
			'2' => __( 'Poor', 'erp' ),
			'3' => __( 'Average', 'erp' ),
			'4' => __( 'Good', 'erp' ),
			'5' => __( 'Excellent', 'erp' ),
		)
	);

	if ( $selected ) {
		return isset( $rating[ $selected ] ) ? $rating[ $selected ] : '';
	}

	return $rating;
}

/**
 * Get erp option from settings framework
 *
 * @param string $option_name name of the option
 * @param string $section     name of the section. if it's a separate option, don't provide any
 * @param string $default     default option
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
	$months = array();

	if ( $title ) {
		$months['-1'] = $title;
	}

	for ( $m = 1; $m <= 12; $m++ ) {
		$months[ $m ] = sprintf( __( '%s', 'erp' ), gmdate( 'F', mktime( 0, 0, 0, $m, 1 ) ) );
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
	return \WeDevs\ERP\Admin\Models\AuditLog::select( 'component' )->distinct()->get()->toArray();
}

/**
 * Get all modules inserted in log table
 *
 * @since 0.1
 *
 * @return array
 */
function erp_get_audit_log_sub_component() {
	return \WeDevs\ERP\Admin\Models\AuditLog::select( 'sub_component' )->distinct()->get()->toArray();
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
 * @param string $message
 * @param string $type
 *
 * @return void
 */
function erp_file_log( $message, $type = '' ) {
	if ( ! empty( $type ) ) {
		$message = sprintf( "[%s][%s] %s\n", gmdate( 'd.m.Y h:i:s' ), $type, $message );
	} else {
		$message = sprintf( "[%s] %s\n", gmdate( 'd.m.Y h:i:s' ), $message );
	}
}

/**
 * Get people types from various components
 *
 * @return array
 */
function erp_get_people_types() {
	return apply_filters( 'erp_people_types', array() );
}

/**
 * Get Country name by country code
 *
 * @since 1.0
 *
 * @param string $code
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
 * @param string $country
 * @param string $state
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
 * @param array $schedules
 *
 * @return array
 */
function erp_cron_intervals( $schedules ) {
	$schedules['per_minute'] = array(
		'interval' => MINUTE_IN_SECONDS,
		'display'  => __( 'Every Minute', 'erp' ),
	);

	$schedules['two_min'] = array(
		'interval' => MINUTE_IN_SECONDS * 2,
		'display'  => __( 'Every 2 Minutes', 'erp' ),
	);

	$schedules['five_min'] = array(
		'interval' => MINUTE_IN_SECONDS * 5,
		'display'  => __( 'Every 5 Minutes', 'erp' ),
	);

	$schedules['ten_min'] = array(
		'interval' => MINUTE_IN_SECONDS * 10,
		'display'  => __( 'Every 10 Minutes', 'erp' ),
	);

	$schedules['fifteen_min'] = array(
		'interval' => MINUTE_IN_SECONDS * 15,
		'display'  => __( 'Every 15 Minutes', 'erp' ),
	);

	$schedules['thirty_min'] = array(
		'interval' => MINUTE_IN_SECONDS * 30,
		'display'  => __( 'Every 30 Minutes', 'erp' ),
	);

	$schedules['weekly'] = array(
		'interval' => DAY_IN_SECONDS * 7,
		'display'  => __( 'Once Weekly', 'erp' ),
	);

	return (array) $schedules;
}

/**
 * Show user own media attachment
 *
 * @since 1.0
 *
 * @param string $query
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
	$licenses = array();

	return apply_filters( 'erp_settings_licenses', $licenses );
}

/**
 * Show a readable message about the license status
 *
 * @since 1.0
 *
 * @param array $addon
 *
 * @return string
 */
function erp_get_license_status( $addon ) {
	if ( ! is_object( $addon['status'] ) ) {
		return false;
	}

	$messages     = array();
	$html         = '';
	$license      = $addon['status'];
	$status_class = 'has-error';

	if ( false === $license->success ) {
		switch ( $license->error ) {
			case 'expired':
				$messages[] = sprintf(
					__( 'Your license key expired on %1$s. Please <a href="%2$s" target="_blank" title="Renew your license key">renew your license key</a>.', 'erp' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'mysql' ) ) ),
					'https://wperp.com/checkout/?edd_license_key=' . $addon['license'] . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
				);
				break;

			case 'missing':
				$messages[] = sprintf(
					__( 'Invalid license. Please <a href="%s" target="_blank" title="Visit account page">visit your account page</a> and verify it.', 'erp' ),
					'https://wperp.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=missing'
				);
				break;

			case 'invalid':
			case 'site_inactive':
				$messages[] = sprintf(
					__( 'Your %1$s is not active for this URL. Please <a href="%2$s" target="_blank" title="Visit account page">visit your account page</a> to manage your license key URLs.', 'erp' ),
					$addon['name'],
					'https://wperp.com/my-account?utm_campaign=admin&utm_source=licenses&utm_medium=invalid'
				);
				break;

			case 'item_name_mismatch':
				$messages[] = sprintf( __( 'This is not a %s.', 'erp' ), $addon['name'] );
				break;

			case 'no_activations_left':
				$messages[] = sprintf( __( 'Your license key has reached its activation limit. <a href="%s">View possible upgrades</a> now.', 'erp' ), 'https://wperp.com/my-account/' );
				break;
		}
	} else {
		switch ( $license->license ) {
			case 'expired':
				$messages[] = sprintf(
					__( 'Your license key expired on %1$s. Please <a href="%2$s" target="_blank" title="Renew your license key">renew your license key</a>.', 'erp' ),
					date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'mysql' ) ) ),
					'https://wperp.com/checkout/?edd_license_key=' . $addon['license'] . '&utm_campaign=admin&utm_source=licenses&utm_medium=expired'
				);
				break;

			case 'valid':
				$status_class = 'no-error';
				$now          = current_time( 'mysql' );
				$expiration   = strtotime( $license->expires, current_time( 'mysql' ) );

				if ( 'lifetime' === $license->expires ) {
					$messages[] = __( 'License key never expires.', 'erp' );
				} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {
					$messages[] = sprintf(
						__( 'Your license key expires soon! It expires on %1$s. <a href="%2$s" target="_blank" title="Renew license">Renew your license key</a>.', 'erp' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'mysql' ) ) ),
						'https://wperp.com/checkout/?edd_license_key=' . $addon['license'] . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
					);
				} else {
					$messages[] = sprintf(
						__( 'Your license key expires on %s.', 'erp' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'mysql' ) ) )
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
	$erp_fields = array(
		'contact'  => array(
			'required_fields' => array(
				'first_name',
				'email',
			),
			'fields'          => array(
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
			),
		),
		'company'  => array(
			'required_fields' => array(
				'email',
				'company',
			),
			'fields'          => array(
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
			),
		),
		'employee' => array(
			'required_fields' => array(
				'first_name',
				'last_name',
				'user_email',
			),
			'fields'          => array(
                'employee_id',
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
				'type',
				'pay_type',
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
			),
		),
		'vendor'   => array(
			'required_fields' => array(
				'first_name',
				'last_name',
				'email',
			),
			'fields'          => array(
				'first_name',
				'last_name',
				'email',
				'phone',
				'company',
				'mobile',
				'fax',
				'website',
				'notes',
				'street_1',
				'street_2',
				'city',
				'country',
				'state',
				'postal_code',
			),
		),
		'customer' => array(
			'required_fields' => array(
				'first_name',
				'last_name',
				'email',
			),
			'fields'          => array(
				'first_name',
				'last_name',
				'email',
				'phone',
				'company',
				'mobile',
				'fax',
				'website',
				'notes',
				'street_1',
				'street_2',
				'city',
				'country',
				'state',
				'postal_code',
			),
		),
		'product'  => array(
			'required_fields' => array(
				'name',
				'product_type_id',
				'category_id',
				'sale_price',
				'vendor',
			),
			'fields'          => array(
				'name',
				'product_type_id',
				'category_id',
				'cost_price',
				'sale_price',
				'vendor',
				'tax_cat_id',
			),
		),
	);

	return apply_filters( 'erp_import_export_csv_fields', $erp_fields );
}

/**
 * Process or handle csv file for export
 *
 * @since 1.8.5
 *
 * @return void
 */
function erp_process_csv_export() {
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-import-export-nonce' ) ) {
		return new \WP_Error( 'no-permission', __( 'Nonce verification failed!', 'erp' ) );
	}

	if ( ! is_user_logged_in() ) {
		return new \WP_Error( 'no-permission', __( 'Sorry ! You do not have permission to access this page', 'erp' ) );
	}

	$capability_for_type = array(
		'employee' => 'erp_list_employee',
		'contact'  => 'erp_crm_list_contact',
		'company'  => 'erp_crm_list_contact', // NB: no capability for company in CRM so using contact capability
		'customer' => 'erp_ac_view_customer',
		'vendor'   => 'erp_ac_view_vendor',
		'product'  => 'erp_ac_manager',
	);

	if ( isset( $_POST['erp_export_csv'] ) ) {
		define( 'ERP_IMPORT_EXPORT', true );

		if ( ! empty( $_POST['type'] ) && ! empty( $_POST['fields'] ) ) {
			$custom_fields  = array();
			$custom_options = array();
			$items          = array();
			$csv_items      = array();
			$is_employee    = false;
			$is_people      = false;
			$type           = sanitize_text_field( wp_unslash( $_POST['type'] ) );
			$fields         = array_map( 'sanitize_text_field', wp_unslash( $_POST['fields'] ) );

			if ( ! in_array( $type, array( 'contact', 'company', 'employee', 'vendor', 'customer', 'product' ), true ) ) {
				return new \WP_Error( 'no-permission', __( 'Unknown import type!', 'erp' ) );
			}

			if ( ! current_user_can( 'administrator' ) && ! current_user_can( $capability_for_type[ $type ] ) ) {
				return new \WP_Error( 'no-permission', __( 'Sorry ! You do not have permission to access this page', 'erp' ) );
			}

			switch ( $type ) {
				case 'employee':
					$custom_options = get_option( 'erp-employee-fields' );
					$items          = erp_hr_get_employees( array( 'number' => - 1 ) );
					$is_employee    = true;
					break;

				case 'contact':
					$is_people      = true;
					$custom_options = get_option( 'erp-contact-fields' );
					break;

				case 'company':
					$is_people      = true;
					$custom_options = get_option( 'erp-company-fields' );
					break;

				case 'customer':
					$is_people      = true;
					$custom_options = get_option( 'erp-customer-fields' );
					break;

				case 'vendor':
					$is_people      = true;
					$custom_options = get_option( 'erp-vendor-fields' );
					break;

				case 'product':
					$items = erp_acct_get_all_products( array( 'number' => - 1 ) );
					break;

				default:
			}

			if ( ! empty( $custom_options ) ) {
				foreach ( $custom_options as $field ) {
					$custom_fields[] = $field['name'];
				}
			}

			if ( $is_people ) {
				$items = erp_get_peoples(
					array(
						'type'   => $type,
						'offset' => 0,
						'number' => - 1,
					)
				);
			}

			foreach ( $items as $index => $item ) {
				if ( empty( $fields ) ) {
					continue;
				}

				$item = (object) $item;

				foreach ( $fields as $field ) {
					if ( $is_employee ) {
						if ( in_array( $field, $custom_fields, true ) ) {
							$csv_items[ $index ][ $field ] = get_user_meta( $item->id, $field, true );
						} else {

							$csv_items[ $index ][ $field ] = erp_hr_get_human_readable_name($item->id, $field, $item->{$field}, $item); // $item->{$field};
						}
					} elseif ( $is_people ) {
						if ( in_array( $field, $custom_fields, true ) ) {
							$csv_items[ $index ][ $field ] = erp_people_get_meta( $item->id, $field, true );
						} else {
							$csv_items[ $index ][ $field ] = $item->{$field};
						}
					} else {
						$csv_items[ $index ][ $field ] = $item->{$field};
					}
				}
			}

			$file_name = 'export_' . $type . '_' . gmdate( 'd_m_Y' ) . '.csv';

			erp_make_csv_file( $csv_items, $file_name );
		}
	}
}


function erp_hr_get_human_readable_name( $id, $field, $value, $item ) {

    if ( ! $id ) {
        return '';
    }

    switch ( $field ) {
        case 'department':
            return erp_hr_get_department_name( $id, $field, $value );
            break;
        case 'designation':
            return erp_hr_get_designation_name( $id, $field, $value );
            break;
        case 'type':
            return erp_hr_get_employee_type( $id, $field, $value );
        case 'reporting_to':
            return erp_hr_get_reporting_to( $id, $field, $value );
            break;
        case 'pay_type':
            return erp_hr_get_pay_type_name( $id, $field, $value );
            break;
        case 'status':
            return erp_hr_get_status_name( $id, $field, $value );
            break;
        case 'gender':
            return erp_hr_get_gender_name( $id, $field, $value );
            break;
        case 'marital_status':
            return erp_hr_get_marital_status_name( $id, $field, $value );
            break;
        case 'hiring_source':
            return erp_hr_get_hiring_source_name( $id, $field, $value );
            break;
        case 'country':
            return erp_get_country_name( $value );
            break;
        case 'state':
            return erp_get_state_name(  $item->country, $value );
            break;
        case 'location':
            return erp_get_location_name( $value );
            break;
        case 'nationality':
            return erp_get_country_name(  $value );
            break;
        case 'hiring_date':
        case 'date_of_birth':
        case 'termination_date':
            return  $value;
            break;
        // case 'blood_group':
        //     return erp_hr_get_blood_group( $id, $field, $value );
        //     break;


        default:
            return $value;
            break;
    }
}

function erp_hr_get_department_name( $id, $field, $value ) {
    $department = new \WeDevs\ERP\HRM\Models\Department();
    $department = $department->find( $value );

    if ( $department ) {
        return $department->title;
    }

    return '';
}

function erp_hr_get_designation_name( $id, $field, $value ) {
    $designation = new \WeDevs\ERP\HRM\Models\Designation();
    $designation = $designation->find( $value );
    if ( $designation ) {
        return $designation->title;
    }

    return '';
}

function erp_hr_get_employee_type( $id, $field, $value ) {
    $types = erp_hr_get_employee_types();

    if ( isset( $types[ $value ] ) ) {
        return $types[ $value ];
    }

    return '';
}

function erp_hr_get_reporting_to( $id, $field, $value ) {
    $user = get_user_by( 'id', $value );

    if ( $user ) {
        return $user->user_email;
    }

    return '';
}

function erp_hr_get_pay_type_name( $id, $field, $value ) {
    $types = erp_hr_get_pay_type();

    if ( isset( $types[ $value ] ) ) {
        return $types[ $value ];
    }

    return '';
}

// gender
function erp_hr_get_gender_name( $id, $field, $value ) {
    $types = erp_hr_get_genders();

    if ( isset( $types[ $value ] ) ) {
        return $types[ $value ];
    }

    return '';
}

// employee status
function erp_hr_get_status_name( $id, $field, $value ) {
    $types = erp_hr_get_employee_statuses();

    if ( isset( $types[ $value ] ) ) {
        return $types[ $value ];
    }

    return '';
}

// marital status
function erp_hr_get_marital_status_name( $id, $field, $value ) {
    $types = erp_hr_get_marital_statuses();

    if ( isset( $types[ $value ] ) ) {
        return $types[ $value ];
    }

    return '';
}

// hiring source
function erp_hr_get_hiring_source_name( $id, $field, $value ) {
    $types = erp_hr_get_employee_sources();

    if ( isset( $types[ $value ] ) ) {
        return $types[ $value ];
    }

    return '';
}


function erp_get_location_name( $location_id ) {
    $location = new WeDevs\ERP\Admin\Models\CompanyLocations();
    $location = $location->find( $location_id  );

    if ( $location ) {
        return $location->name;
    }

    return '';
}

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is similiar to WordPress wp_parse_args().
 * It's support multidimensional array.
 *
 * @param array $args
 * @param array $defaults optional
 *
 * @return array
 */
function erp_parse_args_recursive( &$args, $defaults = array() ) {
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
 * @return bool
 */
function erp_mail( $to, $subject, $message, $headers = '', $attachments = array(), $custom_headers = array() ) {
	if ( defined( 'ERP_IS_IMPORTING' ) && ERP_IS_IMPORTING ) {
		return true;
	}

	$erp_email_settings = get_option( 'erp_settings_erp-email_general', array() );
	$is_mail_sent       = false;

	if ( erp_is_smtp_enabled() ) {
		$callback = function ( $phpmailer ) use ( $custom_headers, $erp_email_settings ) {
			$erp_email_smtp_settings = get_option( 'erp_settings_erp-email_smtp', array() );

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

			// Return-Path
			$phpmailer->Sender = apply_filters( 'erp_mail_return_path', $phpmailer->From );

			if ( ! empty( $custom_headers ) ) {
				foreach ( $custom_headers as $key => $value ) {
					$phpmailer->addCustomHeader( $key, $value );
				}
			}

			if ( isset( $erp_email_smtp_settings['debug'] ) && $erp_email_smtp_settings['debug'] === 'yes' ) {
				$phpmailer->SMTPDebug = true;
			}

			if ( isset( $erp_email_smtp_settings['enable_smtp'] ) && $erp_email_smtp_settings['enable_smtp'] === 'yes' ) {
				$phpmailer->Mailer = 'smtp'; // 'smtp', 'mail', or 'sendmail'

				$phpmailer->Host       = $erp_email_smtp_settings['mail_server'];
				$phpmailer->SMTPSecure = ( $erp_email_smtp_settings['authentication'] !== '' ) ? $erp_email_smtp_settings['authentication'] : 'smtp';
				$phpmailer->Port       = $erp_email_smtp_settings['port'];

				if ( $erp_email_smtp_settings['authentication'] !== '' ) {
					$phpmailer->SMTPAuth = true;
					$phpmailer->Username = $erp_email_smtp_settings['username'];
					$phpmailer->Password = $erp_email_smtp_settings['password'];
				}
			}
		};

		add_action( 'phpmailer_init', $callback );
		$is_mail_sent = wp_mail( $to, $subject, $message, $headers, $attachments );
		remove_action( 'phpmailer_init', $callback );
	} elseif ( erp_is_mailgun_enabled() ) {
		$erp_mailgun_settings = get_option( 'erp_settings_erp-email_mailgun', array() );

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

		$from_email   = apply_filters( 'erp_mail_from', $from_email );
		$from_name    = apply_filters( 'erp_mail_from_name', $from_name );
		$content_type = apply_filters( 'erp_mail_content_type', $content_type );

		$private_api_key = ! empty( $erp_mailgun_settings['private_api_key'] ) ? $erp_mailgun_settings['private_api_key'] : '';
		$domain          = ! empty( $erp_mailgun_settings['domain'] ) ? $erp_mailgun_settings['domain'] : '';
		$region          = ! empty( $erp_mailgun_settings['region'] ) ? $erp_mailgun_settings['region'] : '';

		if ( ! empty( $private_api_key ) && ! empty( $domain ) && ! empty( $region ) ) {
			$mailgun = new \WeDevs\ERP\EmailMailgun( $private_api_key, $region, $domain );

			$data = array(
				'subject'         => $subject,
				'from_address'    => array(
					'email' => $from_email,
					'name'  => $from_name,
				),
				'to_address'      => array(
					'email' => $to,
					'name'  => '',
				),
				'cc_address'      => array(
					'email' => '',
					'name'  => '',
				),
				'headers'         => $headers,
				'customer_header' => $custom_headers,
				'attachment'      => $attachments,
				'message'         => $message,
			);

			$mailgun->send_email( $data );
		}
	} else {
		$is_mail_sent = wp_mail( $to, $subject, $message, $headers, $attachments );
	}

	return $is_mail_sent;
}

function erp_mail_send_via_gmail( $to, $subject, $message, $headers = '', $attachments = array(), $custom_headers = array() ) {
	global $phpmailer, $wp_version;

	// (Re)create it, if it's gone missing.
	if ( version_compare( $wp_version, '5.5' ) >= 0 ) {
		if ( ! ( $phpmailer instanceof \PHPMailer\PHPMailer\PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
			$phpmailer = new \PHPMailer\PHPMailer\PHPMailer( true );
		}
	} elseif ( ! ( $phpmailer instanceof PHPMailer ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
			$phpmailer = new \PHPMailer( true );
	}

	// Headers
	$cc       = array();
	$bcc      = array();
	$reply_to = array();

	if ( empty( $headers ) ) {
		$headers = array();
	} else {
		if ( ! is_array( $headers ) ) {
			// Explode the headers out, so this function can take both
			// string headers and an array of headers.
			$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
		} else {
			$tempheaders = $headers;
		}
		$headers = array();

		// If it's actually got contents
		if ( ! empty( $tempheaders ) ) {
			// Iterate through the raw headers
			foreach ( (array) $tempheaders as $header ) {
				if ( strpos( $header, ':' ) === false ) {
					if ( false !== stripos( $header, 'boundary=' ) ) {
						$parts    = preg_split( '/boundary=/i', trim( $header ) );
						$boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
					}
					continue;
				}
				// Explode them out
				[ $name, $content ] = explode( ':', trim( $header ), 2 );

				// Cleanup crew
				$name    = trim( $name );
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
							[ $type, $charset_content ] = explode( ';', $content );
							$content_type               = trim( $type );

							if ( false !== stripos( $charset_content, 'charset=' ) ) {
								$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
							} elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
								$boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
								$charset  = '';
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
						$headers[ trim( $name ) ] = trim( $content );
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
	if ( 'text/html' === $content_type ) {
		$phpmailer->isHTML( true );
	}

	// Return-Path
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
	$address_headers             = compact( 'to', 'cc', 'bcc', 'reply_to' );
	$address_headers['reply_to'] = array( $from_name . ' <' . $from_email . '>' );

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

	// add attachments
	if ( ! empty( $attachments ) ) {
		foreach ( $attachments as $attachment ) {
			try {
				$phpmailer->addAttachment( $attachment );
			} catch ( phpmailerException $e ) {
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
	} catch ( Google_Service_Exception $exception ) {
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
					'_wpnonce': '<?php echo esc_html( wp_create_nonce( 'erp-smtp-test-connection-nonce' ) ); ?>'
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

		jQuery('.email_tab_view li').click(function(){

			var elm = jQuery( this );
			var elm_id = elm.attr( 'id' );
			var target_elm = 'tag_' + elm_id.split("_")[1];
			jQuery('ul.email_tab_view li').removeClass('bt_active');
			elm.addClass('bt_active');
			jQuery('tbody#email_list_view tr').hide(1000);
			jQuery( '.'+ target_elm ).show(1000);

		});
	</script>
	<?php
}

/**
 * Determine if the inbound/imap mail feature is active or not.
 *
 * @return bool
 */
function erp_is_imap_active() {
	$options = get_option( 'erp_settings_erp-email_imap', array() );

	$imap_status = (bool) isset( $options['imap_status'] ) ? $options['imap_status'] : 0;
	$enable_imap = ( isset( $options['enable_imap'] ) && $options['enable_imap'] === 'yes' ) ? true : false;

	if ( $enable_imap && $imap_status ) {
		return true;
	}

	return false;
}

/**
 * Enable default WP Email settings
 *
 * @return void
 */
function erp_enable_default_wp_mail() {
	$wpmail_option                  = get_option( 'erp_settings_erp-email_wpmail', array() );
	$wpmail_option['enable_wpmail'] = 'yes';
	update_option( 'erp_settings_erp-email_wpmail', $wpmail_option );
}

/**
 * Check if the WP Email settings is enabled or not
 *
 * @return bool
 */
function erp_is_wp_mail_enabled() {
	$erp_email_wpmail_settings = get_option( 'erp_settings_erp-email_wpmail', array() );

	if ( ( ! erp_is_smtp_enabled() && ! erp_is_mailgun_enabled() ) || ( isset( $erp_email_wpmail_settings['enable_wpmail'] ) && filter_var( $erp_email_wpmail_settings['enable_wpmail'], FILTER_VALIDATE_BOOLEAN ) ) ) {
		return true;
	}

	return false;
}

/**
 * Check if the ERP Email SMTP settings is enabled or not
 *
 * @since 1.1.6
 *
 * @return bool
 */
function erp_is_smtp_enabled() {
	$erp_email_smtp_settings = get_option( 'erp_settings_erp-email_smtp', array() );

	if ( isset( $erp_email_smtp_settings['enable_smtp'] ) && filter_var( $erp_email_smtp_settings['enable_smtp'], FILTER_VALIDATE_BOOLEAN ) ) {
		return true;
	}

	return false;
}

/**
 * Check if the ERP Email Mailgun settings is enabled or not
 *
 * @since 1.10.0
 *
 * @return bool
 */
function erp_is_mailgun_enabled() {
	$erp_email_mailgun_settings = get_option( 'erp_settings_erp-email_mailgun', array() );

	if ( isset( $erp_email_mailgun_settings['enable_mailgun'] ) && filter_var( $erp_email_mailgun_settings['enable_mailgun'], FILTER_VALIDATE_BOOLEAN ) ) {
		return true;
	}

	return false;
}

/**
 * Determine if the module is active or not.
 *
 * @return bool
 */
function erp_is_module_active( $module_key ) {
	$modules = get_option( 'erp_modules', array() );

	return isset( $modules[ $module_key ] );
}

/**
 * Make csv file from array and force download
 *
 * @param array  $items
 * @param bool   $field_data (optional)
 * @param string $file_name
 */
function erp_make_csv_file( $items, $file_name, $field_data = true, $type = '' ) {
	$file_name = ( ! empty( $file_name ) ) ? $file_name : 'csv_' . gmdate( 'd_m_Y' ) . '.csv';

	if ( empty( $items ) ) {
		return;
	}

	$columns = array_keys( $items[0] );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $file_name );

	$output = fopen( 'php://output', 'w' );

	$columns = array_map(
		function ( $column ) {
			$column = ucwords( str_replace( '_', ' ', $column ) );

			return $column;
		},
		$columns
	);

	fputcsv( $output, $columns );

	if ( $field_data ) {
		foreach ( $items as $item ) {
			$csv_row = array_map(
				function ( $item_val ) {
					if ( is_array( $item_val ) ) {
						return implode( ', ', $item_val );
					}

					return $item_val;
				},
				$item
			);

			fputcsv( $output, $csv_row );
		}
	}

    if ( 'employee' === $type ) {

        $sample_data = get_sample_employee_data($items[0]);
        foreach ( $sample_data as $item ) {
            fputcsv( $output, $item );
        }
    }
	exit();
}


/**
 * Get sample employee data
 *
 * @return array
 */
function get_sample_employee_data($columns) {

    $sample_data = array(
        array(
            'employee_id' => '12345',
            'first_name' => 'John',
            'middle_name' => 'Karim',
            'last_name' => 'Desuza',
            'email' => 'john.doe@example.com',
            'designation' => 'Software Engineer',
            'department' => 'Engineering',
            'location' => 'Somewhere',
            'hiring_source' => 'Direct',
            'hire_date' => '2020-01-01',
            'date_of_birth' => '1999-01-01',
            'reporting_to' => 'someone@email.com',
            'pay_rate' => '50000.00',
            'type' => 'Full Time',
            'pay_type' => 'Monthly',
            'status' => 'Active',
            'other_email' => 'other_email@something.com',
            'phone' => '123-456-7890',
            'work_phone' => '123-456-7890',
            'mobile' => '123-456-7891',
            'address' => '123 Main Street, Anytown, CA 12345',
            'gender' => 'Male',
            'marital_status' => 'Single',
            'nationality' => 'Nationality',
            'driving_license' => 'DRV-12345',
            'hobbies' => 'Reading, Traveling',
            'user_url' => 'https://example.com',
            'description' => 'Sample employee data',
            'street_1' => '123 Main Street',
            'street_2' => 'Apt 123',
            'city' => 'Anytown',
            'country' => 'US',
            'state' => 'CA',
            'postal_code' => '12345',
        ),
    );
    return $sample_data;
}
/**
 * Import/Export sample CSV download action hook
 *
 * @param void
 */
function erp_import_export_download_sample() {
	if ( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] !== 'download_sample' ) {
		return;
	}

	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-import-export-nonce' ) ) {
		return;
	}

	if ( empty( $_REQUEST['type'] ) ) {
		return;
	}

	$sample_type   = strtolower( sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) );
	$fields = erp_get_import_export_fields();

	if ( isset( $fields[ $sample_type ] ) ) {
		$keys      = $fields[ $sample_type ]['fields'];
		$keys      = array_flip( $keys );
		$file_name = "sample_csv_{$sample_type}.csv";

		erp_make_csv_file( array( $keys ), $file_name, false, $sample_type );
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
		wp_enqueue_script( 'erp-fullcalendar-locale', WPERP_ASSETS . "/vendor/fullcalendar/lang/{$script}.js", array( 'erp-fullcalendar' ), gmdate( 'Ymd' ), true );
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
	include_once WPERP_INCLUDES . '/Admin/views/erp-modal.php';
	erp_get_js_template( WPERP_INCLUDES . '/Admin/views/address.php', 'erp-address' );
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
 * @param string $word the word in singular form
 *
 * @return string the word in plural form
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
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
	} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
		$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED'] ) );
	} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
		$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED_FOR'] ) );
	} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
		$ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) );
	} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
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
 * @return bool
 */
function erp_validate_boolean( $value ) {
	return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Get financial year start and end dates
 *
 * @param $date
 *
 * @since 1.2.0
 * since 1.3.0 $date added
 * @since 1.6.0 added timestamp support
 *
 * @return array
 */
function erp_get_financial_year_dates( $date = null ) {
	$start_month = erp_get_option( 'gen_financial_month', 'erp_settings_general', 1 );

	if ( $date == null ) {
		$year  = gmdate( 'Y' );
		$month = gmdate( 'n' );
	} else {
		if ( ! is_numeric( $date ) ) {
			$timestamp = erp_current_datetime()->modify( $date )->getTimestamp();
		} else {
			$timestamp = $date;
		}
		$year  = gmdate( 'Y', $timestamp );
		$month = gmdate( 'n', $timestamp );
	}

	/**
	 * Suppose, $start_month is July and today is May 2017. Then we should get
	 * start = 2016-07-01 00:00:00 and end = 2017-06-30 23:59:59.
	 *
	 * On the other hand, if $start_month = January, then we should get
	 * start = 2017-01-01 00:00:00 and end = 2017-12-31 23:59:59.
	 */
	if ( $month < $start_month ) {
		--$year;
	}

	$months = erp_months_dropdown();
	$start  = gmdate( 'Y-m-d 00:00:00', strtotime( "first day of $months[$start_month] $year" ) );
	$end    = gmdate( 'Y-m-d 23:59:59', strtotime( "$start + 12 months - 1 day" ) );

	return array(
		'start' => $start,
		'end'   => $end,
	);
}

/**
 * Get financial start and end years that a date belongs to
 *
 * @since 1.2.0
 * @since 1.6.0 added timestamp support
 *
 * @param string $date
 *
 * @return array
 */
function get_financial_year_from_date( $date ) {
	$fy_start_month = erp_get_option( 'gen_financial_month', 'erp_settings_general', 1 );
	$fy_start_month = absint( $fy_start_month );

	$date_timestamp = ! is_numeric( $date ) ? erp_current_datetime()->modify( $date )->getTimestamp() : $date;
	$date_year      = absint( gmdate( 'Y', $date_timestamp ) );
	$date_month     = absint( gmdate( 'n', $date_timestamp ) );

	if ( 1 === $fy_start_month ) {
		return array(
			'start' => $date_year,
			'end'   => $date_year,
		);
	} elseif ( $date_month <= ( $fy_start_month - 1 ) ) {
		return array(
			'start' => ( $date_year - 1 ),
			'end'   => $date_year,
		);
	} else {
		return array(
			'start' => $date_year,
			'end'   => ( $date_year + 1 ),
		);
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

	if ( $is_erp_redirect && isset( $user->roles ) && is_array( $user->roles ) && ! in_array( 'administrator', $user->roles, true ) ) {
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
 *
 * @since 1.3.1 function has been from crm
 * @since 1.2.4
 *
 * @return array
 */
function erp_get_editable_roles() {
	if ( ! function_exists( 'get_editable_roles' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}
	$wp_roles = get_editable_roles();

	if ( ! current_user_can( 'administrator' ) ) {
		unset( $wp_roles['administrator'] );
	}

	$roles = apply_filters( 'erp_editable_roles', $wp_roles );

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
 */
function erp_get_dates_in_range( $first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {
	$dates   = array();
	$current = strtotime( $first );
	$last    = strtotime( $last );

	while ( $current <= $last ) {
		$dates[] = gmdate( $output_format, $current );
		$current = strtotime( $step, $current );
	}

	return $dates;
}

/**
 * Sanitize a string destined to be a tooltip.
 * Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 *
 * @param string $var
 *
 * @return string
 *
 * @since 1.3.4
 */
function erp_sanitize_tooltip( $var ) {
	return htmlspecialchars(
		wp_kses(
			html_entity_decode( $var ),
			array(
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'small'  => array(),
				'span'   => array(),
				'ul'     => array(),
				'li'     => array(),
				'ol'     => array(),
				'p'      => array(),
			)
		)
	);
}

/**
 * Display an ERP help tip.
 *
 * @param string $tip        Help tip text
 * @param bool   $allow_html Allow sanitized HTML if true or escape
 *
 * @return string
 *
 * @since 1.3.4
 * @since 1.6.5
 */
function erp_help_tip( $tip, $allow_html = false, $tag = 'tips' ) {
	if ( $allow_html ) {
		$tip = erp_sanitize_tooltip( $tip );
	} else {
		$tip = wp_kses_post( $tip );
	}

	return sprintf( '<span class="erp-help-tip erp-tips" title="%s"></span>', $tip );
}

/**
 * Letter to number converter
 *
 * @param  $size
 *
 * @return $ret
 *
 * @since 1.3.4
 */
function erp_let_to_num( $size ) {
	$l   = substr( $size, -1 );
	$ret = substr( $size, 0, -1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
			// no break
		case 'T':
			$ret *= 1024;
			// no break
		case 'G':
			$ret *= 1024;
			// no break
		case 'M':
			$ret *= 1024;
			// no break
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
	$menu = array();

	return apply_filters( 'erp_menu', $menu );
}

/**
 * Add a menu item into ERP Menu
 *
 * @since 1.4.0
 *
 * @param string $component Name of Component to add menu
 * @param array  $args
 *
 * @return void
 */
function erp_add_menu( $component, $args ) {
	add_filter(
		'erp_menu',
		function ( $menu ) use ( $component, $args ) {
			$menu[ $component ][ $args['slug'] ] = $args;

			return $menu;
		}
	);
}

/**
 * Adds a submenu under a Menu item
 *
 * @since 1.4.0
 *
 * @param string $component Name of Component to add menu
 * @param string $parent    Slug of Parent menu item
 * @param array  $args
 *
 * @return void
 */
function erp_add_submenu( $component, $parent, $args ) {
	add_filter(
		'erp_menu',
		function ( $menu ) use ( $component, $parent, $args ) {
			if ( ! isset( $menu[ $component ][ $parent ] ) ) {
				return $menu;
			}
			$args['parent'] = $parent;
			$menu[ $component ][ $parent ]['submenu'][ $args['slug'] ] = $args;

			return $menu;
		}
	);
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

	if ( ! isset( $menu[ $component ] ) ) {
		return false;
	}
	// check current tab
	$tab = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'dashboard';

	?>
	<div class='erp-nav-container erp-hide-print'>
		<?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo erp_render_menu_header( $component );
		echo wp_kses_post( erp_build_menu( $menu[ $component ], $tab, $component ) );
		?>
	</div>
	<?php
}

/**
 * Build html for ERP menu
 *
 * @since 1.4.0
 *
 * @param $items
 * @param $active
 * @param $component main component slug
 * @param bool                          $dropdown
 *
 * @return string
 */
function erp_build_menu( $items, $active, $component, $dropdown = false ) {

	// check capability
	$items = array_filter(
		$items,
		function ( $item ) {
			if ( ! isset( $item['capability'] ) ) {
				return false;
			}

			return current_user_can( $item['capability'] );
		}
	);

	// sort items for position
	uasort(
		$items,
		function ( $a, $b ) {
			return $a['position'] <=> $b['position'];
		}
	);

	$html = '<ul class="erp-nav -primary erp-hide-print">';

	if ( $dropdown ) {
		$html = '<ul class="erp-nav-dropdown">';
	}

	foreach ( $items as $item ) {
		$link = add_query_arg(
			array(
				'page'    => 'erp-' . $component,
				'section' => $item['slug'],
			),
			admin_url( 'admin.php' )
		);

		$class     = $active === $item['slug'] ? 'active ' : '';
		$pro_popup = '';
		if ( ! empty( $item['pro_popup'] ) ) {
			$pro_popup = '<span class="pro-popup">Pro</span>';
			$class    .= ' pro-popup-main ';
		}

		if ( $dropdown ) {
			$link   = add_query_arg(
				array(
					'page'        => 'erp-' . $component,
					'section'     => $item['parent'],
					'sub-section' => $item['slug'],
				),
				admin_url( 'admin.php' )
			);
			$class .= ( ! empty( $_GET['sub-section'] ) && $_GET['sub-section'] === $item['slug'] ) ? 'active ' : '';
		}

		if ( ! empty( $item['direct_link'] ) ) {
			$link = $item['direct_link'];
		}

		$submenu = '';

		if ( isset( $item['submenu'] ) ) {
			$class   .= 'dropdown-nav';
			$submenu .= erp_build_menu( $item['submenu'], $active, $component, true );
		}

		$html .= sprintf( '<li class="%s"><a href="%s">%s</a>%s%s</li>', $class, $link, $item['title'], $submenu, $pro_popup );
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
	if ( empty( $_GET['page'] ) || $_GET['page'] !== 'erp-crm' ) {
		return false;
	}

	if ( empty( $_GET['section'] ) || $_GET['section'] !== 'contacts' || $_GET['section'] !== 'companies' ) {
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
	if ( empty( $_GET['page'] ) || $_GET['page'] !== $page ) {
		return false;
	}

	if ( empty( $_GET['section'] ) || $_GET['section'] !== $section ) {
		return false;
	}

	if ( ! empty( $subsection ) ) {
		if ( empty( $_GET['sub-section'] ) || $_GET['sub-section'] !== $subsection ) {
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
	$menu = array();

	return apply_filters( 'erp_menu_headers', $menu );
}

/**
 * Add Header part of Component
 *
 * @param $component
 * @param $title
 * @param string    $icon
 */
function erp_add_menu_header( $component, $title, $icon = '' ) {
	add_filter(
		'erp_menu_headers',
		function ( $menu ) use ( $component, $title, $icon ) {
			$menu[ $component ] = array(
				'title' => $title,
				'icon'  => $icon,
			);

			return $menu;
		}
	);
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

	if ( empty( $headers[ $component ] ) ) {
		return '';
	}

	$html = sprintf(
		'<div class="erp-page-header">
                        <div class="module-icon">
                            %s
                        </div>
                        <h2>%s</h2>
                    </div>',
		$headers[ $component ]['icon'],
		$headers[ $component ]['title']
	);

	return $html;
}

/**
 * RSS feed
 *
 * @return object|false
 */
function erp_web_feed() {
	$transient_name = 'erp_web_feed_cache';
	$cached_data = get_transient( $transient_name );

	if ( $cached_data !== false ) {
		return simplexml_load_string( $cached_data );
	}

	$url = apply_filters( 'erp_web_feed_url', 'https://wperp.com/feed/' );
	$args = array(
		'timeout'   => 15,
		'sslverify' => false,
	);

	$response = wp_remote_post( $url, $args );

	$data = '';
	if ( ! is_wp_error( $response ) ) {
		$data = wp_remote_retrieve_body( $response );

		set_transient( $transient_name, $data, DAY_IN_SECONDS );
	}

	return simplexml_load_string( $data );
}

/**
 * Build Mega for html for ERP Mega menu
 *
 * @since 1.4.0
 *
 * @param $items
 * @param $active
 * @param $component main component slug
 * @param bool                          $dropdown
 *
 * @return string
 */
function erp_build_mega_menu( $items, $active, $component, $dropdown = false ) {

	// check capability
	$items = array_filter(
		$items,
		function ( $item ) {
			if ( ! isset( $item['capability'] ) ) {
				return false;
			}

			return current_user_can( $item['capability'] );
		}
	);

	// sort items for position
	uasort(
		$items,
		function ( $a, $b ) {
			return $a['position'] <=> $b['position'];
		}
	);

	$html = '<ul class="erp-nav -primary">';

	if ( $dropdown ) {
		$html = '<ul class="erp-nav-dropdown">';
	}

	foreach ( $items as $item ) {
		if ( $component === 'accounting' ) {
			$link = add_query_arg( array( 'page' => 'erp-' . $component . '#/' . $item['slug'] ), admin_url( 'admin.php' ) );
		} else {
			$link = add_query_arg(
				array(
					'page'    => 'erp-' . $component,
					'section' => $item['slug'],
				),
				admin_url( 'admin.php' )
			);
		}

		$class = $active === $item['slug'] ? 'active ' : '';

		if ( $dropdown ) {
			$link   = add_query_arg(
				array(
					'page'        => 'erp-' . $component,
					'section'     => $item['parent'],
					'sub-section' => $item['slug'],
				),
				admin_url( 'admin.php' )
			);
			$class .= ( ! empty( $_GET['sub-section'] ) && $_GET['sub-section'] === $item['slug'] ) ? 'active ' : '';
		}

		if ( ! empty( $item['direct_link'] ) ) {
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

	$currencies_dropdown = array();

	foreach ( $currencies as $currency ) {
		$currencies_dropdown[ $currency['id'] ] = $currency['name'] . ' (' . $currency['sign'] . ')';
	}

	return $currencies_dropdown;
}

/**
 * Old functions
 * should be updated ASAP
 *
 * ================================*/

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
		if ( $get_option_value['is_enable'] === 'yes' ) {
			$is_enable = ' checked';
		}
	} else {
		$is_enable = '';
	}
	$can_not_be_disabled = apply_filters(
		'email_settings_enable_filter',
		array(
			'erp_email_settings_new-leave-request',
			'erp_email_settings_approved-leave-request',
			'erp_email_settings_rejected-leave-request',
			'erp_email_settings_employee-asset-request',
			'erp_email_settings_employee-asset-approve',
			'erp_email_settings_employee-asset-reject',
			'erp_email_settings_employee-asset-overdue',
		)
	);

	if ( in_array( $get_option_id, $can_not_be_disabled ) ) {
		echo '<td class="erp-settings-table-is_enable">
            <label class=""> &nbsp; </label>
        </td>';
	} else {
		echo '<td class="erp-settings-table-is_enable">
            <label class="cus_switch"><input type="checkbox" name="isEnableEmail[' . esc_attr( $get_option_id ) . ']"  ' . esc_attr( $is_enable ) . '><span class="cus_slider cus_round"></span></label>
        </td>';
	}
	/*
	echo '<td class="erp-settings-table-is_enable">
			<label class="cus_switch"><input type="checkbox" name="isEnableEmail['. $get_option_id .']"  ' . $is_enable . '><span class="cus_slider cus_round"></span></label>
		</td>';*/
}

/**
 * Update enable/disable column checkbox of email.
 *
 * @since  1.5.6
 *
 * @return null
 */
function add_enable_disable_option_save() {
	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'erp-settings-nonce' ) ) {
		return;
	}

	if ( isset( $_POST['save_email_enable_or_disable'] ) && $_POST['save_email_enable_or_disable'] === 'save_email_enable_or_disable' ) {
		$registered_email = array_keys( wperp()->emailer->get_emails() );

		foreach ( $registered_email as $remail ) {
			$cur_email_init   = wperp()->emailer->get_email( $remail );
			$cur_email_id     = 'erp_email_settings_' . $cur_email_init->id;
			$cur_email_option = get_option( $cur_email_id );

			if ( isset( $cur_email_option['is_enable'] ) ) {
				unset( $cur_email_option['is_enable'] );
				update_option( $cur_email_id, $cur_email_option );
			}
		}

		if ( isset( $_POST['isEnableEmail'] ) ) {
			$is_enable_email = array_map( 'sanitize_text_field', wp_unslash( $_POST['isEnableEmail'] ) );

			foreach ( $is_enable_email as $key => $value ) {
				$email_arr              = get_option( $key );
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
function erp_email_setting_columns_add_enable_disable( $array ) {
	$arr     = array();
	$counter = 1;

	foreach ( $array as $key => $value ) {
		$arr[ $key ] = $value;

		if ( count( $array ) - 1 === $counter ) {
			$arr['is_enable'] = __( 'Enable/Disable', 'erp' );
		}

		++$counter;
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
	$fields['general'][] = array(
		'default' => 'save_email_enable_or_disable',
		'type'    => 'hidden',
		'id'      => 'save_email_enable_or_disable',
	);

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
	$get_option_id       = $email->get_option_id();
	$can_not_be_disabled = apply_filters(
		'email_settings_enable_filter',
		array(
			'erp_email_settings_new-leave-request',
			'erp_email_settings_approved-leave-request',
			'erp_email_settings_rejected-leave-request',
			'erp_email_settings_employee-asset-request',
			'erp_email_settings_employee-asset-approve',
			'erp_email_settings_employee-asset-reject',
			'erp_email_settings_employee-asset-overdue',
		)
	);

	if ( in_array( $get_option_id, $can_not_be_disabled, true ) ) {
		return $email;
	}
	$get_email_settings = get_option( $get_option_id );

	if ( isset( $get_email_settings['is_enable'] ) && $get_email_settings['is_enable'] === 'yes' ) {
		return $email;
	}
	add_filter(
		'erp_email_recipient_' . $email->id,
		function ( $recipient, $object ) {
			return $recipient;
		},
		10,
		2
	);

	return $email;
}
/**** Add Enable Disable section for All Pre-generated email End ****/

/**
 *  A method for inserting multiple rows into the specified table
 *  Updated to include the ability to Update existing rows by primary key
 *
 *  Usage Example for insert:
 *
 *  $insert_arrays = array();
 *  foreach($assets as $asset) {
 *  $time = current_time( 'mysql' );
 *  $insert_arrays[] = array(
 *  'type' => "multiple_row_insert",
 *  'status' => 1,
 *  'name'=>$asset,
 *  'added_date' => $time,
 *  'last_update' => $time);
 *
 *  }
 *
 *
 *  wp_insert_rows($insert_arrays, $wpdb->tablename);
 *
 *  Usage Example for update:
 *
 *  wp_insert_rows($insert_arrays, $wpdb->tablename, true, "primary_column");
 *
 * @since 1.6.0
 *
 * @param array  $row_arrays    key value pairs of row data
 * @param string $wp_table_name table name with prefix added
 * @param bool   $update        set true for data updates, you need to specify primary_key parameter
 * @param string $primary_key   primary key field name, provide this field if you want to update the given data
 *
 * @return false|int return false on query error, otherwise return number of row effected, output can be 0 if no row is updated, consider this while checking for errors
 *
 * @author  Ugur Mirza ZEYREK
 * @contributor Travis Grenell
 */
function erp_wp_insert_rows( $row_arrays, $wp_table_name, $update = false, $primary_key = null ) {
	global $wpdb;
	$wp_table_name = esc_sql( $wp_table_name );
	// Setup arrays for Actual Values, and Placeholders.
	$values        = array();
	$place_holders = array();
	$query         = '';
	$query_columns = '';

	$query .= "INSERT INTO `{$wp_table_name}` (";

	foreach ( $row_arrays as $count => $row_array ) {
		foreach ( $row_array as $key => $value ) {
			if ( $count === 0 ) {
				if ( $query_columns ) {
					$query_columns .= ', ' . $key . '';
				} else {
					$query_columns .= '' . $key . '';
				}
			}

			$values[] = $value;

			$symbol = '%s';

			if ( is_numeric( $value ) ) {
				if ( is_float( $value ) ) {
					$symbol = '%f';
				} else {
					$symbol = '%d';
				}
			}

			if ( isset( $place_holders[ $count ] ) ) {
				$place_holders[ $count ] .= ", '$symbol'";
			} else {
				$place_holders[ $count ] = "( '$symbol'";
			}
		}
		// mind closing the GAP.
		$place_holders[ $count ] .= ')';
	}

	$query .= " $query_columns ) VALUES ";

	$query .= implode( ', ', $place_holders );

	if ( $update ) {
		// $update = " ON DUPLICATE KEY UPDATE $primary_key=VALUES( $primary_key ),";
		$update = ' ON DUPLICATE KEY UPDATE ';
		$cnt    = 0;

		foreach ( $row_arrays[0] as $key => $value ) {
			if ( $cnt === 0 ) {
				$update .= "$key=VALUES($key)";
				$cnt     = 1;
			} else {
				$update .= ", $key=VALUES($key)";
			}
		}
		$query .= $update;
	}

	return $wpdb->query( $wpdb->prepare( $query, $values ) ) === false ? false : true; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/**
 * This function will get mysql date string as input and will return php timestamp with default WordPress timzone
 *
 * @since 1.6.0
 *
 * @param string $time      mysql date format: Y-m-d H:i:s or Y-m-d. In case of Y-m-d only string H:i:s will be set to 00:00:00.
 * @param bool   $timestamp return false to get DateTimeImmutable object
 *
 * @return bool|int|DateTimeImmutable false on return php timestamp on success
 */
function erp_mysqldate_to_phptimestamp( $time, $timestamp = true ) {
	if ( ! preg_match( '/\d{2}:\d{2}:\d{2}$/', $time ) ) {
		$time = $time . ' 00:00:00';
	}

	$timezone = erp_wp_timezone();
	$datetime = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $time, $timezone );

	if ( false === $datetime ) {
		return false;
	}

	if ( $timestamp ) {
		return $datetime->getTimestamp();
	}

	return $datetime;
}

/**
 * Function current_datetime() compability for wp version < 5.3
 *
 * @since 1.6.0
 *
 * @return DateTimeImmutable
 */
function erp_current_datetime() {
	if ( function_exists( 'current_datetime' ) ) {
		return current_datetime();
	}

	return new DateTimeImmutable( 'now', erp_wp_timezone() );
}

/**
 * Function erp_wp_timezone() compability for wp version < 5.3
 *
 * @since 1.6.0
 *
 * @return DateTimeZone
 */
function erp_wp_timezone() {
	if ( function_exists( 'wp_timezone' ) ) {
		return wp_timezone();
	}

	return new DateTimeZone( erp_wp_timezone_string() );
}

/**
 * Function erp_wp_timezone_string() compability for wp version < 5.3
 *
 * @since 1.6.0
 *
 * @return string
 */
function erp_wp_timezone_string() {
	$timezone_string = get_option( 'timezone_string' );

	if ( $timezone_string ) {
		return $timezone_string;
	}

	$offset  = (float) get_option( 'gmt_offset' );
	$hours   = (int) $offset;
	$minutes = ( $offset - $hours );

	$sign      = ( $offset < 0 ) ? '-' : '+';
	$abs_hour  = abs( $hours );
	$abs_mins  = abs( $minutes * 60 );
	$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

	return $tz_offset;
}

/**
 * This method will return input value as integer if there is no . value, otherwise will return a float value
 *
 * @param $number
 *
 * @return int|float
 */
function erp_number_format_i18n( $number ) {
	// cast as string
	$number = (string) $number;

	// check if . exist
	if ( strpos( $number, '.' ) !== false ) {
		$extract = explode( '.', $number );

		if ( isset( $extract[1] ) && absint( $extract[1] > 0 ) ) {
			return number_format_i18n( $number, 1 );
		}
	}

	return number_format_i18n( $number );
}

/**
 * This method will check for a valid timestamp
 *
 * @param int|string $string timestamp
 *
 * @return bool
 */
function erp_is_timestamp( $string ) {
	try {
		new DateTime( '@' . $string );
	} catch ( Exception $e ) {
		return false;
	}

	return true;
}

/**
 * Checks if people's name contains some specific special charecters which should not e allowed
 *
 * @since 1.6.7
 *
 * @param string $name
 *
 * @return bool
 */
function erp_is_valid_name( $name ) {
	return ! preg_match_all( '/[_@!%#&:;"=<>\\/\*\+\?\$\^\{\}\[\]0-9]/', $name );
}

/**
 * Checks if a string contains some disallowed special charecters
 *
 * @since 1.6.8
 *
 * @param string $str
 *
 * @return bool
 */
function erp_contains_disallowed_chars( $str ) {
	return preg_match_all( '/[%;"=<>\\/\*\+\?\$\^\{\}\[\]]/', $str );
}

/**
 * Validates customer's age
 *
 * @since 1.6.7
 *
 * @param string $age
 *
 * @return bool
 */
function erp_is_valid_age( $age ) {
	return preg_match( '/^[1-9][0-9]{0,2}$/', $age );
}

/**
 * Validates date
 *
 * @since 1.6.7
 *
 * @param string $date
 *
 * @return bool
 */
function erp_is_valid_date( $date ) {
	if ( is_null( $date ) ) {
		return false;
	}

	try {
		$dt = new DateTime( trim( (string) $date ) );
	} catch ( Exception $e ) {
		return false;
	}

	$month = $dt->format( 'm' );
	$day   = $dt->format( 'd' );
	$year  = $dt->format( 'Y' );

	if ( checkdate( $month, $day, $year ) ) {
		return true;
	}

	return false;
}

/**
 * Validates mobile, phone, fax
 *
 * @since 1.6.7
 *
 * @param string $contact_no
 *
 * @return bool
 */
function erp_is_valid_contact_no( $contact_no ) {
	return preg_match( '/^\+?[0-9]{1,3}([\s\.\-]?[0-9]{1,5}){3}$/', $contact_no );
}

/**
 * Validates zip code
 *
 * @since 1.6.7
 *
 * @param string $zip_code
 *
 * @return bool
 */
function erp_is_valid_zip_code( $zip_code ) {
	return preg_match( '/^[A-Z0-9][ \-A-Z0-9]{3,12}+$/', $zip_code );
}

/**
 * Validates website url
 *
 * @since 1.6.7
 *
 * @param string $url
 *
 * @return bool
 */
function erp_is_valid_url( $url ) {
	return preg_match( '/^(?:(?:https?|ftp):\/\/)?(?:[a-z0-9-]+\.)*((?:[a-z0-9-]+\.)[a-z]+)/i', $url );
}

/**
 * Validates employee id
 *
 * @since 1.6.7
 *
 * @param string $emp_id
 *
 * @return bool
 */
function erp_is_valid_employee_id( $emp_id ) {
	return preg_match( '/^[A-Z0-9][\-A-Z0-9]*$/i', $emp_id );
}

/**
 * Validates currency amount
 *
 * @since 1.6.7
 *
 * @param string $amount
 *
 * @return bool
 */
function erp_is_valid_currency_amount( $amount ) {
	return preg_match( '/^[0-9]+(\.[0-9]{1,4})?$/', $amount );
}

/**
 * Get different array from two array
 *
 * @since 1.7.2
 *
 * @param array $new_data
 * @param array $old_data
 *
 * @return array
 */
function erp_get_array_diff( $new_data, $old_data, $is_seriazie = false ) {

    $old_value = [];
    $new_value = [];

    // Recursive diff finder
    $find_changes = function( $new, $old, &$new_out, &$old_out ) use ( &$find_changes ) {
        foreach ( $new as $key => $new_val ) {
            $old_val = $old[$key] ?? null;

            if ( is_array( $new_val ) && is_array( $old_val ) ) {
                $new_out[$key] = [];
                $old_out[$key] = [];
                $find_changes( $new_val, $old_val, $new_out[$key], $old_out[$key] );

                // Remove empty results
                if ( empty( $new_out[$key] ) ) {
                    unset( $new_out[$key], $old_out[$key] );
                }

            } elseif ( $new_val !== $old_val ) {
                $new_out[$key] = $new_val;
                $old_out[$key] = $old_val;
            }
        }
    };

    // Start recursive diff
    $find_changes( $new_data, $old_data, $new_value, $old_value );

    if ( ! $is_seriazie ) {
        return array(
            'new_value' => $new_value ? base64_encode( maybe_serialize( $new_value ) ) : '',
            'old_value' => $old_value ? base64_encode( maybe_serialize( $old_value ) ) : '',
        );
    } else {
        return array(
            'new_value' => $new_value,
            'old_value' => $old_value,
        );
    }
}

/**
 * Discards all non-numeric charecters from a given string
 *
 * @since 1.8.2
 *
 * @param string $str
 *
 * @return string
 */
function erp_discard_non_numeric_chars( $str ) {
	return preg_replace( '/[^0-9]/', '', $str );
}

/**
 * Sanitizes phone number to discard unwanted charecters
 *
 * @since 1.8.2
 *
 * @param string  $phone_no
 * @param boolean $allow_plus
 *
 * @return string
 */
function erp_sanitize_phone_number( $phone_no, $allow_plus = false ) {
	$result = erp_discard_non_numeric_chars( $phone_no );

	if ( ! $allow_plus ) {
		return $result;
	}

	if ( 0 === strpos( $phone_no, '+' ) ) {
		$result = '+' . $result;
	}

	return $result;
}

/**
 * Checks if a user has permission to view a page
 *
 * @since 1.8.5
 *
 * @param string $cap
 *
 * @return void
 */
function erp_verify_page_access_permission( $cap ) {
	if ( ! current_user_can( $cap ) ) {
		$error_message  = '<h2 style="text-align: center; margin-top:40px">';
		$error_message .= esc_html__( 'Sorry! You are not allowed to access this page.', 'erp' );
		$error_message .= '</h2>';

		wp_die( wp_kses_post( $error_message ) );
	}
}

/**
 * Disables mysql strict mode
 *
 * @since 1.8.5
 *
 * @return void
 */
function erp_disable_mysql_strict_mode() {
	global $wpdb;

	$wpdb->query( "SET SESSION SQL_MODE=''" );
	$wpdb->query( 'SET SQL_BIG_SELECTS=1' );
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @since 1.9.0
 *
 * @param string $code Code.
 *
 * @return void
 */
function erp_enqueue_js( $code ) {
	global $erp_queued_js;

	if ( empty( $erp_queued_js ) ) {
		$erp_queued_js = '';
	}

	$erp_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 *
 * @since 1.9.0
 *
 * @return void Print JS Code
 */
function erp_print_js() {
	global $erp_queued_js;

	if ( ! empty( $erp_queued_js ) ) {

		// Sanitization JS script if anythings invalid
		$erp_queued_js = wp_check_invalid_utf8( $erp_queued_js );
		$erp_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $erp_queued_js );
		$erp_queued_js = str_replace( "\r", '', $erp_queued_js );

		$js = "<!-- ERP JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $erp_queued_js });\n</script>\n";

		/**
		 * Queued JS Filter.
		 *
		 * @param string $js JavaScript code.
		 */
		echo esc_html( apply_filters( 'erp_queued_js', $js ) );

		unset( $erp_queued_js );
	}
}

/**
 * Reset ERP Data
 *
 * Remove Whole ERP Tables, Roles, Options
 * Deactivate and Activate Wp ERP & ERP-Pro
 *
 * @since 1.9.0
 *
 * @return boolean|object true|WP_Error
 */
function erp_reset_data() {
	global $wpdb;

	try {
		@ini_set( 'max_execution_time', '0' );

		$wpdb->query( 'START TRANSACTION' );

		$erp_roles = array(
			'erp_hr_manager',
			'employee',
			'erp_crm_manager',
			'erp_crm_agent',
			'erp_ac_manager',
			'erp_ac_agency',
		);

		// Delete users table data related to the employees/people
		$users = $wpdb->get_results( "SELECT user_id FROM {$wpdb->prefix}erp_peoples WHERE user_id <> 0" );

		foreach ( $users as $user ) {
			// Retrieves user object
			$user = get_userdata( $user->user_id );
			if ( ! $user ) {
				continue;
			}

			/*
			 * Check if user has any other role(s) not given by erp
			 * If not, delete the user.
			 * But if user has other roles, we shouldn't delete the user.
			 * In that case we will just remove all the erp roles
			 * from the user.
			 */
			$non_erp_roles = array_diff( (array) $user->roles, $erp_roles );
			if ( empty( $non_erp_roles ) ) {
				wp_delete_user( $user->ID );
				continue;
			}

			foreach ( $erp_roles as $erp_role ) {
				$user->remove_role( $erp_role );
			}
		}

		$tables = $wpdb->get_results(
			"SELECT TABLE_NAME FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '{$wpdb->dbname}'
            AND TABLE_NAME LIKE '{$wpdb->prefix}erp\_%'
            AND TABLE_NAME NOT LIKE '{$wpdb->prefix}erp\_audit\_log'"
		);

		$table_names = array();
		foreach ( $tables as $table ) {
			$table_name    = $table->TABLE_NAME;
			$table_names[] = $table_name;
			$wpdb->query( 'TRUNCATE TABLE ' . $table_name );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		// Delete all posts data related to WP ERP
		$erp_posts = get_posts( array( 'post_type' => array( 'erp_hr_announcement', 'erp_hr_training', 'erp_hr_questionnaire', 'erp_hr_recruitment', 'erp_inv_product' ) ) );
		foreach ( $erp_posts as $post ) {
			wp_delete_post( $post->ID, true );
		}

		$log_data = array(
			'component'     => '',
			'sub_component' => 'Reset',
			'changetype'    => 'delete',
			'created_by'    => get_current_user_id(),
			'old_value'     => base64_encode( maybe_serialize( $tables ) ),
			'new_value'     => base64_encode( maybe_serialize( array( $wpdb->prefix . 'erp_audit_log' ) ) ),
			'message'       => __( 'ERP data reset completed', 'erp' ),
		);

		erp_log()->insert_log( $log_data );

		foreach ( $erp_roles as $role ) {
			remove_role( $role );
		}

		$options = array(
			'wp_erp_version',
			'wp_erp_db_version',
			'erp_modules',
			'erp_setup_wizard_ran',
			'wp_erp_install_date',
			'erp_tracking_notice',
			'wp_erp_activation_dismiss',
			'_erp_admin_menu',
			'_erp_adminbar_menu',
			'_erp_company',
			'erp_acct_new_ledgers',
			'erp_email_settings_employee-welcome',
			'erp_email_settings_new-leave-request',
			'erp_email_settings_approved-leave-request',
			'erp_email_settings_rejected-leave-request',
			'erp_email_settings_new-task-assigned',
			'erp_email_settings_new-contact-assigned',
			'erp_email_settings_hiring-anniversary-wish',
			'erp_email_settings_govt-holiday-reminder',
			'erp_email_settings_transectional-email',
			'erp_email_settings_transectional-email-payments',
			'erp_email_settings_transectional-email-estimate',
			'erp_email_settings_transectional-email-purchase-order',
			'erp_email_settings_transectional-email-pay-purchase',
			'erp_settings_general',
			'erp_settings_accounting',
			'erp_settings_erp-hr_workdays',
			'erp_settings_erp-crm_subscription',
			'erp_settings_erp-email_general',
			'erp_settings_erp-wp_mail',
			'erp_settings_erp-email_smtp',
			'erp_settings_erp-email_mailgun',
			'erp_settings_erp-email_gmail',
			'erp_settings_erp-email_imap',
			'widget_erp-subscription-from-widget',
		);

		foreach ( $options as $option ) {
			delete_option( $option );
		}

		// Clear some other scheduled events registered as cron jobs
		wp_clear_scheduled_hook( 'erp_per_minute_scheduled_events' );
		wp_clear_scheduled_hook( 'erp_daily_scheduled_events' );
		wp_clear_scheduled_hook( 'erp_weekly_scheduled_events' );

		// Deactivate & activate wp-erp
		$wp_erp_url    = explode( '/', WPERP_URL );
		$plugin_wp_erp = end( $wp_erp_url ) . '/wp-erp.php';
		deactivate_plugins( $plugin_wp_erp );

		// Activate and add deafult modules
		activate_plugin( $plugin_wp_erp );
		$all_modules = wperp()->modules->get_modules();
		update_option( 'erp_modules', $all_modules );

		// If ERP Pro is installed & activated, do the same for this
		if ( function_exists( 'wp_erp_pro' ) ) {
			$erp_pro_url    = explode( '/', ERP_PRO_DIR );
			$plugin_erp_pro = end( $erp_pro_url ) . '/erp-pro.php';

			if ( is_plugin_active( $plugin_erp_pro ) ) {
				deactivate_plugins( $plugin_erp_pro );
				activate_plugin( $plugin_erp_pro );
			}
		}

		return true;
	} catch ( \Exception $e ) {
		$wpdb->query( 'ROLLBACK' );
		return new WP_Error( 'error', __( 'Something went wrong when resetting. Please try again.', 'erp' ) );
	}
}

/**
 * Get Standarized message for erp
 *
 * @since 1.8.6
 *
 * @param array $args
 *
 * @return string
 */
function erp_get_message( $args = array() ) {
	$defaults = array(
		'type'         => '',
		'message'      => '',
		'additional'   => null,
		'append_first' => true,
	);

	$args = wp_parse_args( $args, $defaults );

	switch ( $args['type'] ) {
		case 'error_nonce':
			$args['message'] = 'Nonce verification failed!';
			break;

		case 'error_permission':
			$args['message'] = 'You do not have sufficient permissions to do this action';
			break;

		case 'error_process':
			$args['message'] = 'Could not process the request. Try again later!';
			break;

		case 'save_success':
			$args['message'] = 'Saved Successfully!';
			break;

		case 'update_success':
			$args['message'] = 'Updated Successfully!';
			break;

		case 'insert_success':
			$args['message'] = 'Created Successfully!';
			break;

		case 'delete_success':
			$args['message'] = 'Deleted Successfully!';
			break;

		default:
			break;
	}

	if ( ! empty( $args['additional'] ) ) {
		if ( $args['append_first'] ) {
			$args['message'] = $args['additional'] . ' ' . $args['message'];
		} else {
			$args['message'] .= ' ' . $args['additional'];
		}
	}

	return sprintf( __( '%s', 'erp' ), $args['message'] );
}

/**
 * Convert a serialized corrupted String to an array
 *
 * @since 1.10.0
 *
 * @param string $serialized_string
 *
 * @return array converted array data
 */
function erp_serialize_string_to_array( $serialized_string ) {
	$data = preg_replace_callback(
		'!s:(\d+):"(.*?)";!',
		function ( $match ) {
			return ( $match[1] == strlen( $match[2] ) ) ? $match[0] : 's:' . strlen( $match[2] ) . ':"' . $match[2] . '";';
		},
		$serialized_string
	);

	return unserialize( $data );
}
