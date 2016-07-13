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
 * @param array $caps Capabilities for meta capability
 * @param string $cap Capability name
 * @param int $user_id User id
 * @param mixed $args Arguments
 */
function erp_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
    return apply_filters( 'erp_map_meta_caps', $caps, $cap, $user_id, $args );
}
/**
 * Get full list of currency codes.
 *
 * @return array
 */
function erp_get_currencies() {
    return array_unique( apply_filters( 'erp_currencies', array(
        'AED' => __( 'United Arab Emirates Dirham', 'erp' ),
		'AFN' => __( 'Afghanistan Afghani', 'erp' ),
		'ALL' => __( 'Albania Lek', 'erp' ),
		'AMD' => __( 'Armenia Dram', 'erp' ),
		'ANG' => __( 'Netherlands Antilles Guilder', 'erp' ),
		'AOA' => __( 'Angola Kwanza', 'erp' ),
		'ARS' => __( 'Argentina Peso', 'erp' ),
		'AUD' => __( 'Australia Dollar', 'erp' ),
		'AWG' => __( 'Aruba Guilder', 'erp' ),
		'AZN' => __( 'Azerbaijan New Manat', 'erp' ),
		'BAM' => __( 'Bosnia and Herzegovina Convertible Marka', 'erp' ),
		'BBD' => __( 'Barbados Dollar', 'erp' ),
		'BDT' => __( 'Bangladesh Taka', 'erp' ),
		'BGN' => __( 'Bulgaria Lev', 'erp' ),
		'BHD' => __( 'Bahrain Dinar', 'erp' ),
		'BIF' => __( 'Burundi Franc', 'erp' ),
		'BMD' => __( 'Bermuda Dollar', 'erp' ),
		'BND' => __( 'Brunei Darussalam Dollar', 'erp' ),
		'BOB' => __( 'Bolivia Bolíviano', 'erp' ),
		'BRL' => __( 'Brazil Real', 'erp' ),
		'BSD' => __( 'Bahamas Dollar', 'erp' ),
		'BTN' => __( 'Bhutan Ngultrum', 'erp' ),
		'BWP' => __( 'Botswana Pula', 'erp' ),
		'BYR' => __( 'Belarus Ruble', 'erp' ),
		'BZD' => __( 'Belize Dollar', 'erp' ),
		'CAD' => __( 'Canada Dollar', 'erp' ),
		'CDF' => __( 'Congo/Kinshasa Franc', 'erp' ),
		'CHF' => __( 'Switzerland Franc', 'erp' ),
		'CLP' => __( 'Chile Peso', 'erp' ),
		'CNY' => __( 'China Yuan Renminbi', 'erp' ),
		'COP' => __( 'Colombia Peso', 'erp' ),
		'CRC' => __( 'Costa Rica Colon', 'erp' ),
		'CUC' => __( 'Cuba Convertible Peso', 'erp' ),
		'CUP' => __( 'Cuba Peso', 'erp' ),
		'CVE' => __( 'Cape Verde Escudo', 'erp' ),
		'CZK' => __( 'Czech Republic Koruna', 'erp' ),
		'DJF' => __( 'Djibouti Franc', 'erp' ),
		'DKK' => __( 'Denmark Krone', 'erp' ),
		'DOP' => __( 'Dominican Republic Peso', 'erp' ),
		'DZD' => __( 'Algeria Dinar', 'erp' ),
		'EGP' => __( 'Egypt Pound', 'erp' ),
		'ERN' => __( 'Eritrea Nakfa', 'erp' ),
		'ETB' => __( 'Ethiopia Birr', 'erp' ),
		'EUR' => __( 'Euro Member Countries', 'erp' ),
		'FJD' => __( 'Fiji Dollar', 'erp' ),
		'FKP' => __( 'Falkland Islands (Malvinas) Pound', 'erp' ),
		'GBP' => __( 'United Kingdom Pound', 'erp' ),
		'GEL' => __( 'Georgia Lari', 'erp' ),
		'GGP' => __( 'Guernsey Pound', 'erp' ),
		'GHS' => __( 'Ghana Cedi', 'erp' ),
		'GIP' => __( 'Gibraltar Pound', 'erp' ),
		'GMD' => __( 'Gambia Dalasi', 'erp' ),
		'GNF' => __( 'Guinea Franc', 'erp' ),
		'GTQ' => __( 'Guatemala Quetzal', 'erp' ),
		'GYD' => __( 'Guyana Dollar', 'erp' ),
		'HKD' => __( 'Hong Kong Dollar', 'erp' ),
		'HNL' => __( 'Honduras Lempira', 'erp' ),
		'HRK' => __( 'Croatia Kuna', 'erp' ),
		'HTG' => __( 'Haiti Gourde', 'erp' ),
		'HUF' => __( 'Hungary Forint', 'erp' ),
		'IDR' => __( 'Indonesia Rupiah', 'erp' ),
		'ILS' => __( 'Israel Shekel', 'erp' ),
		'IMP' => __( 'Isle of Man Pound', 'erp' ),
		'INR' => __( 'India Rupee', 'erp' ),
		'IQD' => __( 'Iraq Dinar', 'erp' ),
		'IRR' => __( 'Iran Rial', 'erp' ),
		'ISK' => __( 'Iceland Krona', 'erp' ),
		'JEP' => __( 'Jersey Pound', 'erp' ),
		'JMD' => __( 'Jamaica Dollar', 'erp' ),
		'JOD' => __( 'Jordan Dinar', 'erp' ),
		'JPY' => __( 'Japan Yen', 'erp' ),
		'KES' => __( 'Kenya Shilling', 'erp' ),
		'KGS' => __( 'Kyrgyzstan Som', 'erp' ),
		'KHR' => __( 'Cambodia Riel', 'erp' ),
		'KMF' => __( 'Comoros Franc', 'erp' ),
		'KPW' => __( 'Korea (North) Won', 'erp' ),
		'KRW' => __( 'Korea (South) Won', 'erp' ),
		'KWD' => __( 'Kuwait Dinar', 'erp' ),
		'KYD' => __( 'Cayman Islands Dollar', 'erp' ),
		'KZT' => __( 'Kazakhstan Tenge', 'erp' ),
		'LAK' => __( 'Laos Kip', 'erp' ),
		'LBP' => __( 'Lebanon Pound', 'erp' ),
		'LKR' => __( 'Sri Lanka Rupee', 'erp' ),
		'LRD' => __( 'Liberia Dollar', 'erp' ),
		'LSL' => __( 'Lesotho Loti', 'erp' ),
		'LYD' => __( 'Libya Dinar', 'erp' ),
		'MAD' => __( 'Morocco Dirham', 'erp' ),
		'MDL' => __( 'Moldova Leu', 'erp' ),
		'MGA' => __( 'Madagascar Ariary', 'erp' ),
		'MKD' => __( 'Macedonia Denar', 'erp' ),
		'MMK' => __( 'Myanmar (Burma) Kyat', 'erp' ),
		'MNT' => __( 'Mongolia Tughrik', 'erp' ),
		'MOP' => __( 'Macau Pataca', 'erp' ),
		'MRO' => __( 'Mauritania Ouguiya', 'erp' ),
		'MUR' => __( 'Mauritius Rupee', 'erp' ),
		'MVR' => __( 'Maldives (Maldive Islands) Rufiyaa', 'erp' ),
		'MWK' => __( 'Malawi Kwacha', 'erp' ),
		'MXN' => __( 'Mexico Peso', 'erp' ),
		'MYR' => __( 'Malaysia Ringgit', 'erp' ),
		'MZN' => __( 'Mozambique Metical', 'erp' ),
		'NAD' => __( 'Namibia Dollar', 'erp' ),
		'NGN' => __( 'Nigeria Naira', 'erp' ),
		'NIO' => __( 'Nicaragua Cordoba', 'erp' ),
		'NOK' => __( 'Norway Krone', 'erp' ),
		'NPR' => __( 'Nepal Rupee', 'erp' ),
		'NZD' => __( 'New Zealand Dollar', 'erp' ),
		'OMR' => __( 'Oman Rial', 'erp' ),
		'PAB' => __( 'Panama Balboa', 'erp' ),
		'PEN' => __( 'Peru Sol', 'erp' ),
		'PGK' => __( 'Papua New Guinea Kina', 'erp' ),
		'PHP' => __( 'Philippines Peso', 'erp' ),
		'PKR' => __( 'Pakistan Rupee', 'erp' ),
		'PLN' => __( 'Poland Zloty', 'erp' ),
		'PYG' => __( 'Paraguay Guarani', 'erp' ),
		'QAR' => __( 'Qatar Riyal', 'erp' ),
		'RON' => __( 'Romania New Leu', 'erp' ),
		'RSD' => __( 'Serbia Dinar', 'erp' ),
		'RUB' => __( 'Russia Ruble', 'erp' ),
		'RWF' => __( 'Rwanda Franc', 'erp' ),
		'SAR' => __( 'Saudi Arabia Riyal', 'erp' ),
		'SBD' => __( 'Solomon Islands Dollar', 'erp' ),
		'SCR' => __( 'Seychelles Rupee', 'erp' ),
		'SDG' => __( 'Sudan Pound', 'erp' ),
		'SEK' => __( 'Sweden Krona', 'erp' ),
		'SGD' => __( 'Singapore Dollar', 'erp' ),
		'SHP' => __( 'Saint Helena Pound', 'erp' ),
		'SLL' => __( 'Sierra Leone Leone', 'erp' ),
		'SOS' => __( 'Somalia Shilling', 'erp' ),
		'SRD' => __( 'Suriname Dollar', 'erp' ),
		'STD' => __( 'São Tomé and Príncipe Dobra', 'erp' ),
		'SVC' => __( 'El Salvador Colon', 'erp' ),
		'SYP' => __( 'Syria Pound', 'erp' ),
		'SZL' => __( 'Swaziland Lilangeni', 'erp' ),
		'THB' => __( 'Thailand Baht', 'erp' ),
		'TJS' => __( 'Tajikistan Somoni', 'erp' ),
		'TMT' => __( 'Turkmenistan Manat', 'erp' ),
		'TND' => __( 'Tunisia Dinar', 'erp' ),
		'TOP' => __( 'Tonga Paanga', 'erp' ),
		'TRY' => __( 'Turkey Lira', 'erp' ),
		'TTD' => __( 'Trinidad and Tobago Dollar', 'erp' ),
		'TVD' => __( 'Tuvalu Dollar', 'erp' ),
		'TWD' => __( 'Taiwan New Dollar', 'erp' ),
		'TZS' => __( 'Tanzania Shilling', 'erp' ),
		'UAH' => __( 'Ukraine Hryvnia', 'erp' ),
		'UGX' => __( 'Uganda Shilling', 'erp' ),
		'USD' => __( 'United States Dollar', 'erp' ),
		'UYU' => __( 'Uruguay Peso', 'erp' ),
		'UZS' => __( 'Uzbekistan Som', 'erp' ),
		'VEF' => __( 'Venezuela Bolivar', 'erp' ),
		'VND' => __( 'Viet Nam Dong', 'erp' ),
		'VUV' => __( 'Vanuatu Vatu', 'erp' ),
		'WST' => __( 'Samoa Tala', 'erp' ),
		'XAF' => __( 'Communauté Financière Africaine (BEAC) CFA Franc BEAC', 'erp' ),
		'XCD' => __( 'East Caribbean Dollar', 'erp' ),
		'XOF' => __( 'Communauté Financière Africaine (BCEAO) Franc', 'erp' ),
		'XPF' => __( 'Comptoirs Français du Pacifique (CFP) Franc', 'erp' ),
		'YER' => __( 'Yemen Rial', 'erp' ),
		'ZAR' => __( 'South Africa Rand', 'erp' ),
		'ZMW' => __( 'Zambia Kwacha', 'erp' ),
		'ZWD' => __( 'Zimbabwe Dollar', 'erp' ),
    ) ) );
}
/**
 * Get full list of currency ISO with symbol label.
 *
 * @return array
 */
function erp_get_currency_list_with_symbol() {
    $currencies      = erp_get_currencies();
    $currency_symbol = [];
    foreach ( $currencies as $iso => $currency ) {
        $currency_symbol[$iso] = sprintf( '%1$s (%2$s)', $currency, erp_get_currency_symbol( $iso ) );
    }
    return $currency_symbol;
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
        case 'AED' : $currency_symbol = 'د.إ'; break;
		case 'AED' : $currency_symbol = '&#1583;.&#1573;'; break;
		case 'AFN' : $currency_symbol = '&#65;&#102;'; break;
		case 'ALL' : $currency_symbol = '&#76;&#101;&#107;'; break;
		case 'AMD' : $currency_symbol = ''; break;
		case 'ANG' : $currency_symbol = '&#402;'; break;
		case 'AOA' : $currency_symbol = '&#75;&#122;'; break;
		case 'ARS' : $currency_symbol = '&#36;'; break;
		case 'AUD' : $currency_symbol = '&#36;'; break;
		case 'AWG' : $currency_symbol = '&#402;'; break;
		case 'AZN' : $currency_symbol = '&#1084;&#1072;&#1085;'; break;
		case 'BAM' : $currency_symbol = '&#75;&#77;'; break;
		case 'BBD' : $currency_symbol = '&#36;'; break;
		case 'BDT' : $currency_symbol = '&#2547;'; break;
		case 'BGN' : $currency_symbol = '&#1083;&#1074;'; break;
		case 'BHD' : $currency_symbol = '.&#1583;.&#1576;'; break;
		case 'BIF' : $currency_symbol = '&#70;&#66;&#117;'; break;
		case 'BMD' : $currency_symbol = '&#36;'; break;
		case 'BND' : $currency_symbol = '&#36;'; break;
		case 'BOB' : $currency_symbol = '&#36;&#98;'; break;
		case 'BRL' : $currency_symbol = '&#82;&#36;'; break;
		case 'BSD' : $currency_symbol = '&#36;'; break;
		case 'BTN' : $currency_symbol = '&#78;&#117;&#46;'; break;
		case 'BWP' : $currency_symbol = '&#80;'; break;
		case 'BYR' : $currency_symbol = '&#112;&#46;'; break;
		case 'BZD' : $currency_symbol = '&#66;&#90;&#36;'; break;
		case 'CAD' : $currency_symbol = '&#36;'; break;
		case 'CDF' : $currency_symbol = '&#70;&#67;'; break;
		case 'CHF' : $currency_symbol = '&#67;&#72;&#70;'; break;
		case 'CLF' : $currency_symbol = ''; break;
		case 'CLP' : $currency_symbol = '&#36;'; break;
		case 'CNY' : $currency_symbol = '&#165;'; break;
		case 'COP' : $currency_symbol = '&#36;'; break;
		case 'CRC' : $currency_symbol = '&#8353;'; break;
		case 'CUP' : $currency_symbol = '&#8396;'; break;
		case 'CVE' : $currency_symbol = '&#36;'; break;
		case 'CZK' : $currency_symbol = '&#75;&#269;'; break;
		case 'DJF' : $currency_symbol = '&#70;&#100;&#106;'; break;
		case 'DKK' : $currency_symbol = '&#107;&#114;'; break;
		case 'DOP' : $currency_symbol = '&#82;&#68;&#36;'; break;
		case 'DZD' : $currency_symbol = '&#1583;&#1580;'; break;
		case 'EGP' : $currency_symbol = '&#163;'; break;
		case 'ETB' : $currency_symbol = '&#66;&#114;'; break;
		case 'EUR' : $currency_symbol = '&#8364;'; break;
		case 'FJD' : $currency_symbol = '&#36;'; break;
		case 'FKP' : $currency_symbol = '&#163;'; break;
		case 'GBP' : $currency_symbol = '&#163;'; break;
		case 'GEL' : $currency_symbol = '&#4314;'; break;
		case 'GHS' : $currency_symbol = '&#162;'; break;
		case 'GIP' : $currency_symbol = '&#163;'; break;
		case 'GMD' : $currency_symbol = '&#68;'; break;
		case 'GNF' : $currency_symbol = '&#70;&#71;'; break;
		case 'GTQ' : $currency_symbol = '&#81;'; break;
		case 'GYD' : $currency_symbol = '&#36;'; break;
		case 'HKD' : $currency_symbol = '&#36;'; break;
		case 'HNL' : $currency_symbol = '&#76;'; break;
		case 'HRK' : $currency_symbol = '&#107;&#110;'; break;
		case 'HTG' : $currency_symbol = '&#71;'; break;
		case 'HUF' : $currency_symbol = '&#70;&#116;'; break;
		case 'IDR' : $currency_symbol = '&#82;&#112;'; break;
		case 'ILS' : $currency_symbol = '&#8362;'; break;
		case 'INR' : $currency_symbol = '&#8377;'; break;
		case 'IQD' : $currency_symbol = '&#1593;.&#1583;'; break;
		case 'IRR' : $currency_symbol = '&#65020;'; break;
		case 'ISK' : $currency_symbol = '&#107;&#114;'; break;
		case 'JEP' : $currency_symbol = '&#163;'; break;
		case 'JMD' : $currency_symbol = '&#74;&#36;'; break;
		case 'JOD' : $currency_symbol = '&#74;&#68;'; break;
		case 'JPY' : $currency_symbol = '&#165;'; break;
		case 'KES' : $currency_symbol = '&#75;&#83;&#104;'; break;
		case 'KGS' : $currency_symbol = '&#1083;&#1074;'; break;
		case 'KHR' : $currency_symbol = '&#6107;'; break;
		case 'KMF' : $currency_symbol = '&#67;&#70;'; break;
		case 'KPW' : $currency_symbol = '&#8361;'; break;
		case 'KRW' : $currency_symbol = '&#8361;'; break;
		case 'KWD' : $currency_symbol = '&#1583;.&#1603;'; break;
		case 'KYD' : $currency_symbol = '&#36;'; break;
		case 'KZT' : $currency_symbol = '&#1083;&#1074;'; break;
		case 'LAK' : $currency_symbol = '&#8365;'; break;
		case 'LBP' : $currency_symbol = '&#163;'; break;
		case 'LKR' : $currency_symbol = '&#8360;'; break;
		case 'LRD' : $currency_symbol = '&#36;'; break;
		case 'LSL' : $currency_symbol = '&#76;'; break;
		case 'LTL' : $currency_symbol = '&#76;&#116;'; break;
		case 'LVL' : $currency_symbol = '&#76;&#115;'; break;
		case 'LYD' : $currency_symbol = '&#1604;.&#1583;'; break;
		case 'MAD' : $currency_symbol = '&#1583;.&#1605;.'; break;
		case 'MDL' : $currency_symbol = '&#76;'; break;
		case 'MGA' : $currency_symbol = '&#65;&#114;'; break;
		case 'MKD' : $currency_symbol = '&#1076;&#1077;&#1085;'; break;
		case 'MMK' : $currency_symbol = '&#75;'; break;
		case 'MNT' : $currency_symbol = '&#8366;'; break;
		case 'MOP' : $currency_symbol = '&#77;&#79;&#80;&#36;'; break;
		case 'MRO' : $currency_symbol = '&#85;&#77;'; break;
		case 'MUR' : $currency_symbol = '&#8360;'; break;
		case 'MVR' : $currency_symbol = '.&#1923;'; break;
		case 'MWK' : $currency_symbol = '&#77;&#75;'; break;
		case 'MXN' : $currency_symbol = '&#36;'; break;
		case 'MYR' : $currency_symbol = '&#82;&#77;'; break;
		case 'MZN' : $currency_symbol = '&#77;&#84;'; break;
		case 'NAD' : $currency_symbol = '&#36;'; break;
		case 'NGN' : $currency_symbol = '&#8358;'; break;
		case 'NIO' : $currency_symbol = '&#67;&#36;'; break;
		case 'NOK' : $currency_symbol = '&#107;&#114;'; break;
		case 'NPR' : $currency_symbol = '&#8360;'; break;
		case 'NZD' : $currency_symbol = '&#36;'; break;
		case 'OMR' : $currency_symbol = '&#65020;'; break;
		case 'PAB' : $currency_symbol = '&#66;&#47;&#46;'; break;
		case 'PEN' : $currency_symbol = '&#83;&#47;&#46;'; break;
		case 'PGK' : $currency_symbol = '&#75;'; break;
		case 'PHP' : $currency_symbol = '&#8369;'; break;
		case 'PKR' : $currency_symbol = '&#8360;'; break;
		case 'PLN' : $currency_symbol = '&#122;&#322;'; break;
		case 'PYG' : $currency_symbol = '&#71;&#115;'; break;
		case 'QAR' : $currency_symbol = '&#65020;'; break;
		case 'RON' : $currency_symbol = '&#108;&#101;&#105;'; break;
		case 'RSD' : $currency_symbol = '&#1044;&#1080;&#1085;&#46;'; break;
		case 'RUB' : $currency_symbol = '&#1088;&#1091;&#1073;'; break;
		case 'RWF' : $currency_symbol = '&#1585;.&#1587;'; break;
		case 'SAR' : $currency_symbol = '&#65020;'; break;
		case 'SBD' : $currency_symbol = '&#36;'; break;
		case 'SCR' : $currency_symbol = '&#8360;'; break;
		case 'SDG' : $currency_symbol = '&#163;'; break;
		case 'SEK' : $currency_symbol = '&#107;&#114;'; break;
		case 'SGD' : $currency_symbol = '&#36;'; break;
		case 'SHP' : $currency_symbol = '&#163;'; break;
		case 'SLL' : $currency_symbol = '&#76;&#101;'; break;
		case 'SOS' : $currency_symbol = '&#83;'; break;
		case 'SRD' : $currency_symbol = '&#36;'; break;
		case 'STD' : $currency_symbol = '&#68;&#98;'; break;
		case 'SVC' : $currency_symbol = '&#36;'; break;
		case 'SYP' : $currency_symbol = '&#163;'; break;
		case 'SZL' : $currency_symbol = '&#76;'; break;
		case 'THB' : $currency_symbol = '&#3647;'; break;
		case 'TJS' : $currency_symbol = '&#84;&#74;&#83;'; break;
		case 'TMT' : $currency_symbol = '&#109;'; break;
		case 'TND' : $currency_symbol = '&#1583;.&#1578;'; break;
		case 'TOP' : $currency_symbol = '&#84;&#36;'; break;
		case 'TRY' : $currency_symbol = '&#8356;'; break;
		case 'TTD' : $currency_symbol = '&#36;'; break;
		case 'TWD' : $currency_symbol = '&#78;&#84;&#36;'; break;
		case 'TZS' : $currency_symbol = ''; break;
		case 'UAH' : $currency_symbol = '&#8372;'; break;
		case 'UGX' : $currency_symbol = '&#85;&#83;&#104;'; break;
		case 'USD' : $currency_symbol = '&#36;'; break;
		case 'UYU' : $currency_symbol = '&#36;&#85;'; break;
		case 'UZS' : $currency_symbol = '&#1083;&#1074;'; break;
		case 'VEF' : $currency_symbol = '&#66;&#115;'; break;
		case 'VND' : $currency_symbol = '&#8363;'; break;
		case 'VUV' : $currency_symbol = '&#86;&#84;'; break;
		case 'WST' : $currency_symbol = '&#87;&#83;&#36;'; break;
		case 'XAF' : $currency_symbol = '&#70;&#67;&#70;&#65;'; break;
		case 'XCD' : $currency_symbol = '&#36;'; break;
		case 'XDR' : $currency_symbol = ''; break;
		case 'XOF' : $currency_symbol = ''; break;
		case 'XPF' : $currency_symbol = '&#70;'; break;
		case 'YER' : $currency_symbol = '&#65020;'; break;
		case 'ZAR' : $currency_symbol = '&#82;'; break;
		case 'ZMK' : $currency_symbol = '&#90;&#75;'; break;
		case 'ZWL' : $currency_symbol = '&#90;&#36;'; break;
        default    : $currency_symbol = ''; break;
    }
    return apply_filters( 'erp_currency_symbol', $currency_symbol, $currency );
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
        echo '<script type="text/html" id="tmpl-' . $id . '">' . "\n";
        include_once $file_path;
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
        echo '<script type="text/x-template" id="'. $id . '">' . "\n";
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
            foreach ($value as $key => $val) {
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
            foreach ($value as $key => $val) {
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
 * @param  string  the label
 * @param  string  the value to print
 * @param  string  separator
 *
 * @return void
 */
function erp_print_key_value( $label, $value, $sep = ' : ' ) {
    $value = empty( $value ) ? '&mdash;' : $value;
    printf( '<label>%s</label> <span class="sep">%s</span> <span class="value">%s</span>', $label, $sep, $value );
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
        return sprintf( '<a href="%1$s">%1$s</a>', $value );
    } elseif ( 'phone' == $type ) {
        return sprintf( '<a href="tel:%1$s">%1$s</a>', $value );
    }
}
/**
 * Get a formatted date from WordPress format
 *
 * @param  string  $date the date
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
 * @param  string  $start_date
 * @param  string  $end_date
 *
 * @return array
 */
function erp_extract_dates( $start_date, $end_date ) {
    $start_date = new DateTime( $start_date );
    $end_date   = new DateTime( $end_date );
    $end_date->modify( '+1 day' ); // to get proper days in duration
    $diff = $start_date->diff( $end_date );
    // we got a negative date
    if ( $diff->invert || ! $diff->days ) {
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
 * @param  array   $array array of arrays
 *
 * @return array
 */
function erp_array_to_object( $array = [] ) {
    $new_array = [];
    if ( $array ) {
        foreach ($array as $key => $value) {
            $new_array[] = (object) $value;
        }
    }
    return $new_array;
}
/**
 * Check date in range or not
 *
 * @param  date   $start_date
 * @param  date   $end_date
 * @param  date   $date_from_user
 *
 * @return boolen
 */
function erp_check_date_in_range( $start_date, $end_date, $date_from_user ) {
    // Convert to timestamp
    $start_ts = strtotime( $start_date );
    $end_ts   = strtotime( $end_date );
    $user_ts  = strtotime( $date_from_user );
    // Check that user date is between start & end
    if ( ($user_ts >= $start_ts) && ($user_ts <= $end_ts) ) {
        return true;
    }
    return false;
}
/**
 * Check date range any point in range or not
 *
 * @param  date   $start_date
 * @param  date   $end_date
 * @param  date   $user_date_start
 * @param  date   $user_date_end
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
 * @param  date   $start_date
 * @param  date   $end_date
 *
 * @return integer
 */
function erp_date_duration( $start_date, $end_date ) {
    $datetime1 = new DateTime( $start_date );
    $datetime2 = new DateTime( $end_date );
    $interval  = $datetime1->diff( $datetime2 );
    return $interval->format('%a');
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
        return isset( $rating[$selected] ) ? $rating[$selected] : '';
    }
    return $rating;
}
/**
 * Get erp option from settings framework
 *
 * @param  sting  $option_name name of the option
 * @param  string $section     name of the section. if it's a separate option, don't provide any
 * @param  string $default     default option
 * @return string
 */
function erp_get_option( $option_name, $section = false, $default = '' ) {
    if ( $section ) {
        $option = get_option( $section );
        if ( isset( $option[$option_name] ) ) {
            return $option[$option_name];
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
    for ( $m = 1; $m <= 12; $m++ ) {
        $months[$m] = date( 'F', mktime( 0, 0, 0, $m ) );
    }
    return $months;
}
/**
 * Get Company financial start date
 *
 * @since  0.1
 *
 * @return string date
 */
function erp_financial_start_date() {
    return date( 'Y-m-d H:i:s', mktime( 0, 0, 0,  erp_get_option( 'gen_financial_month', 'erp_settings_general', 1 ), 1 ) );
}
/**
 * Get Company financial end date
 *
 * @since  0.1
 *
 * @return string date
 */
function erp_financial_end_date() {
    $start_date = erp_financial_start_date();
    return  date( 'Y-m-t H:i:s', strtotime( '+11 month', strtotime( $start_date ) ) );
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
 * @param  string  $message
 * @param  string  $type
 *
 * @return void
 */
function erp_file_log( $message, $type = '' ) {
    if ( ! empty( $type ) ) {
        $message = sprintf( "[%s][%s] %s\n", date( 'd.m.Y h:i:s' ), $type, $message );
    } else {
        $message = sprintf( "[%s] %s\n", date( 'd.m.Y h:i:s' ), $message );
    }
    error_log( $message, 3, dirname( WPERP_FILE ) . '/debug.log' );
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
    $countries = $load_cuntries_states->countries;
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
    $states = $load_cuntries_states->states;
    // Handle full state name
    $full_state   = ( $country && $state && isset( $states[ $country ][ $state ] ) ) ? $states[ $country ][ $state ] : $state;
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
        'interval'  => MINUTE_IN_SECONDS,
        'display'   => __( 'Every Minute', 'erp' )
    );
    $schedules['two_min'] = array(
        'interval'  => MINUTE_IN_SECONDS * 2,
        'display'   => __( 'Every 2 Minutes', 'erp' )
    );
    $schedules['five_min'] = array(
        'interval'  => MINUTE_IN_SECONDS * 5,
        'display'   => __( 'Every 5 Minutes', 'erp' )
    );
    $schedules['ten_min'] = array(
        'interval'  => MINUTE_IN_SECONDS * 10,
        'display'   => __( 'Every 10 Minutes', 'erp' )
    );
    $schedules['fifteen_min'] = array(
        'interval'  => MINUTE_IN_SECONDS * 15,
        'display'   => __( 'Every 15 Minutes', 'erp' )
    );
    $schedules['thirty_min'] = array(
        'interval'  => MINUTE_IN_SECONDS * 30,
        'display'   => __( 'Every 30 Minutes', 'erp' )
    );
    $schedules['weekly'] = array(
        'interval'  => DAY_IN_SECONDS * 7,
        'display'  => __( 'Once Weekly', 'erp' )
    );
    return (array)$schedules;
}
/**
 * forward given end_date by 1 day to make fullcalendar range compatible
 *
 * @param string $end_date saved $end_date from db
 *
 * @since 0.1
 *
 * @return string end_date
 */
function erp_fullcalendar_end_date( $end_date ) {
    return date( 'Y-m-d H:i:s', strtotime( $end_date . '+1 day' ) );
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
 * @param  array  $addon
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
        switch( $license->error ) {
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
        switch( $license->license ) {
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
                } elseif( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {
                    $messages[] = sprintf(
                        __( 'Your license key expires soon! It expires on %s. <a href="%s" target="_blank" title="Renew license">Renew your license key</a>.', 'erp' ),
                        date_i18n( get_option( 'date_format' ), strtotime( $license->expires, current_time( 'timestamp' ) ) ),
                        'https://wperp.com/checkout/?edd_license_key=' . $value . '&utm_campaign=admin&utm_source=licenses&utm_medium=renew'
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
        foreach( $messages as $message ) {
            $html .= '<div class="erp-license-status ' . $status_class . '">';
                $html .= '<p class="help">' . $message . '</p>';
            $html .= '</div>';
        }
    }
    return $html;
}
/**
 * ERP Import/Export JavaScript enqueue.
 *
 * @since  1.0
 *
 * @return void
 */
function erp_import_export_javascript() {
    $contact_fields = [
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
        'type',
    ];
    $field_builder_contacts_fields = get_option( 'erp-contact-fields' );
    if ( ! empty( $field_builder_contacts_fields ) ) {
        foreach ( $field_builder_contacts_fields as $field ) {
            $contact_fields[] = $field['name'];
        }
    }
    $company_fields = [
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
        'type',
    ];
    $employee_fields = [
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
        'postal_code'
    ];
    ?>
    <script type="text/javascript">
        function erp_title_case(string) {
            // \u00C0-\u00ff for a happy Latin-1
            return string.toLowerCase().replace(/_/g, ' ').replace(/\b([a-z\u00C0-\u00ff])/g, function (_, initial) {
                return initial.toUpperCase();
            }).replace(/(\s(?:de|a|o|e|da|do|em|ou|[\u00C0-\u00ff]))\b/ig, function (_, match) {
                return match.toLowerCase();
            });
        }
        jQuery(document).ready(function($) {
            var fields = [];
            var required_fields = [];
            var contact_fields = <?php echo json_encode( $contact_fields ) ; ?>;
            var company_fields = <?php echo json_encode( $company_fields ); ?>;
            var employee_fields = <?php echo json_encode( $employee_fields ); ?>;
            switch ( $( 'form#export_form #type' ).val() ) {
                case 'contact':
                    fields = contact_fields;
                    break;
                case 'company':
                    fields = company_fields;
                    break;
                case 'employee':
                    fields = employee_fields;
                    break;
            }
            var contact_required_fields = [
                'first_name'
            ];
            var company_required_fields = [
                'email',
                'company'
            ];
            var employee_required_fields = [
                'first_name',
                'last_name',
                'user_email',
            ];
            var html = '<ul class="erp-list list-inline">';
            for ( var i = 0;  i < fields.length; i++ ) {
                html += '<li><label><input type="checkbox" name="fields[]" value="' + fields[i] + '"> ' + erp_title_case( fields[i] ) + '</label></li>';
            }
            html += '<ul>';
            if ( html ) {
                $( '#fields' ).html( html );
            }
            $( 'form#export_form #type' ).on( 'change', function( e ) {
                e.preventDefault();
                switch ( $(this).val() ) {
                    case 'contact':
                        fields = contact_fields;
                        break;
                    case 'company':
                        fields = company_fields;
                        break;
                    case 'employee':
                        fields = employee_fields;
                        break;
                }
                html = '<ul class="erp-list list-inline">';
                for ( var i = 0;  i < fields.length; i++ ) {
                    html += '<li><label><input type="checkbox" name="fields[]" value="' + fields[i] + '"> ' + erp_title_case( fields[i] ) + '</label></li>';
                }
                html += '<ul>';
                if ( html ) {
                    $( 'form#export_form #fields' ).html( html );
                }
            });
            $( 'form#import_form #csv_file' ).on( 'change', function( e ) {
                e.preventDefault();
                $( '#fields_container' ).show();
                var fields_html = '';
                switch ( $( 'form#import_form #type' ).val() ) {
                    case 'contact':
                        fields = contact_fields;
                        required_fields = contact_required_fields;
                        break;
                    case 'company':
                        fields = company_fields;
                        required_fields = company_required_fields;
                        break;
                    case 'employee':
                        fields = employee_fields;
                        required_fields = employee_required_fields;
                        break;
                }
                var required = '';
                var red_span = '';
                for ( var i = 0;  i < fields.length; i++ ) {
                    if ( required_fields.indexOf( fields[i] ) !== -1 ) {
                        required = 'required';
                        red_span = ' <span class="required">*</span>';
                    } else {
                        required = '';
                        red_span = '';
                    }
                    fields_html += `
                        <tr>
                            <th>
                                <label for="fields[` + fields[i] + `]">` + erp_title_case( fields[i] ) + red_span + `</label>
                            </th>
                            <td>
                                <select name="fields[` + fields[i] + `]" class="csv_fields" ` + required + `>
                                </select>
                            </td>
                        </tr>`;
                }
                $( '#fields_container' ).html( fields_html );
                var file = this.files[0];
                var reader = new FileReader();
                var first5000 = file.slice(0, 5000);
                reader.readAsText( first5000 );
                // reader.readAsText(file);
                reader.onload = function(e) {
                    var csv = reader.result;
                    // Split the input into lines
                    lines = csv.split('\n'),
                    // Extract column names from the first line
                    columnNamesLine = lines[0];
                    columnNames = columnNamesLine.split(',');
                    var html = '';
                    html += '<option value=""><?php _e( '&mdash; Select Field &mdash;', 'erp' ); ?></option>';
                    columnNames.forEach( function( item, index ) {
                        item = item.replace(/"/g, ""); ;
                        html += '<option value="' + index + '">' + item + '</option>';
                    } );
                    if ( html ) {
                        $( '.csv_fields' ).html( html );
                    }
                };
            });
            $( "#export_form #selecctall" ).change( function(e) {
                e.preventDefault();
                $( "#export_form #fields input[type=checkbox]" ).prop( 'checked', $(this).prop( "checked" ) );
            });
            $( "#users_import_form" ).on( 'submit', function(e) {
                e.preventDefault();
                statusDiv = $( "div#import-status-indicator" );
                statusDiv.show();
                var form = $(this),
                    submit = form.find( 'input[type=submit]' );
                submit.attr( 'disabled', 'disabled' );
                var data = {
                    'action': 'erp_import_users_as_contacts',
                    'user_role': $(this).find('select[name=user_role]').val(),
                    'contact_owner': $(this).find('select[name=contact_owner]').val(),
                    'life_stage': $(this).find('select[name=life_stage]').val(),
                    'contact_group': $(this).find('select[name=contact_group]').val(),
                    '_wpnonce': $(this).find('input[name=_wpnonce]').val()
                };
                var total_items = 0, left = 0, imported = 0, exists = 0, percent = 0, type = 'success', message = '';
                $.post( ajaxurl, data, function(response) {
                    if ( response.success ) {
                        total_items = response.data.total_items;
                        left = response.data.left;
                        exists = response.data.exists;
                        imported = total_items - left;
                        done = imported - exists;
                        if ( imported > 0 || total_items > 0 ) {
                            percent = Math.floor( ( 100 / total_items ) * ( imported ) );
                            type = 'success';
                            message = 'Successfully imported all users!';
                        } else {
                            message = 'No users found to import!';
                            type = 'error';
                        }
                        statusDiv.find( '#progress-total' ).html( percent + '%' );
                        statusDiv.find( '#progressbar-total' ).val( percent );
                        statusDiv.find( '#completed-total' ).html( 'Imported ' + done + ' out of ' + response.data.total_items );
                        if ( exists > 0 ) {
                            statusDiv.find( '#failed-total' ).html( 'Already Exist ' + exists );
                        }
                        if ( response.data.left > 0 ) {
                            form.submit();
                            return;
                        } else {
                            submit.removeAttr( 'disabled' );
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
 * @return void
 */
function erp_process_import_export() {
    if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'erp-import-export-nonce' ) ) {
        return;
    }
    $is_crm_activated = wperp()->modules->is_module_active( 'crm' );
    $is_hrm_activated = wperp()->modules->is_module_active( 'hrm' );
    $departments  = erp_hr_get_departments_dropdown_raw();
    $designations = erp_hr_get_designation_dropdown_raw();
    $field_builder_options = get_option( 'erp-contact-fields' );
    if ( ! empty( $field_builder_options ) ) {
        foreach ( $field_builder_options as $field ) {
            $field_builder_contacts_fields[] = $field['name'];
        }
    }
    if ( isset( $_POST['erp_import_csv'] ) ) {
        $fields = $_POST['fields'];
        $type   = $_POST['type'];
        $employee_fields = [
            'work' => [
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
        $handle = fopen( $_FILES['csv_file']['tmp_name'], 'r' );
        if ( $handle ) {
            $data = [];
            $x = 0;
            while ( $line = fgetcsv( $handle ) ) {
                if ( $x > 0 ) {
                    foreach ( $fields as $key => $value ) {
                        if ( is_numeric( $value ) ) {
                            if ( $type == 'employee' ) {
                                if ( in_array( $key, $employee_fields['work'] ) ) {
                                    if ( $key == 'designation' ) {
                                        $data[$x]['work'][$key] = array_search( $line[$value], $designations );
                                    } else if ( $key == 'department' ) {
                                        $data[$x]['work'][$key] = array_search( $line[$value], $departments );
                                    } else {
                                        $data[$x]['work'][$key] = $line[$value];
                                    }
                                } else if ( in_array( $key, $employee_fields['personal'] ) ) {
                                    $data[$x]['personal'][$key] = $line[$value];
                                } else {
                                    $data[$x][$key] = $line[$value];
                                }
                            } else {
                                $data[$x][$key] = $line[$value];
                                $data[$x]['type'] = $type;
                            }
                        }
                    }
                    if ( $type == 'employee' && $is_hrm_activated ) {
                        if ( ! isset( $data[$x]['work']['status'] ) ) {
                            $data[$x]['work']['status'] = 'active';
                        }
                        $item_insert_id = erp_hr_employee_create( $data[$x] );
                        if ( is_wp_error( $item_insert_id ) ) {
                            continue;
                        }
                    }
                    if ( ( $type == 'contact' || $type == 'company' ) && $is_crm_activated ) {
                        // If not exist any email address then generate a dummy one.
                        if ( ! isset( $data[$x]['email'] ) ) {
                            $rand = substr( sha1( uniqid( time() ) ), 0, 8 );
                            $data[$x]['email'] = "rand_{$rand}@example.com";
                        }
                        if ( empty( $data[$x]['last_name'] ) ) {
                            $name_parts = explode( ' ' , trim( $data[$x]['first_name'] ) );
                            if ( count( $name_parts ) > 1 ) {
                                $last_name = trim( array_pop( $name_parts ) );
                            }
                            if ( ! empty( $last_name ) ) {
                                $data[$x]['first_name'] = implode( ' ' , $name_parts );
                                $data[$x]['last_name'] = $last_name;
                            } else {
                                $data[$x]['last_name'] = '_';
                            }
                        }
                        $item_insert_id = erp_insert_people( $data[$x] );
                        if ( is_wp_error( $item_insert_id ) ) {
                            continue;
                        } else {
                            $current_user  = get_current_user_id();
                            $contact_owner = erp_get_option( 'contact_owner', 'erp_settings_erp-crm_contacts', $current_user );
                            $life_stage    = erp_get_option( 'life_stage', 'erp_settings_erp-crm_contacts', 'opportunity' );
                            erp_people_update_meta( $item_insert_id, '_assign_crm_agent', $contact_owner );
                            erp_people_update_meta( $item_insert_id, 'life_stage', $life_stage );
                            if ( ! empty( $field_builder_contacts_fields ) ) {
                                foreach ( $field_builder_contacts_fields as $field ) {
                                    erp_people_update_meta( $item_insert_id, $field, $data[$x][$field] );
                                }
                            }
                        }
                    }
                }
                $x++;
            }
        }
        wp_redirect( admin_url( 'admin.php?page=erp-tools&tab=import&imported=' . $x ) );
        exit();
    }
    if ( isset( $_POST['erp_export_csv'] ) ) {
        $type   = $_POST['type'];
        $fields = $_POST['fields'];
        if ( $type == 'employee' && $is_hrm_activated ) {
            $args = [
                'number' => -1,
            ];
            $items = erp_hr_get_employees( $args );
        }
        if( ($type == 'contact' || $type == 'company') && $is_crm_activated ) {
            $args = [
                'type'   => $type,
                'count'  => true,
            ];
            $total_items = erp_get_peoples( $args );
            $args = [
                'type'   => $type,
                'offset' => 0,
                'number' => -1,
            ];
            $items = erp_get_peoples( $args );
        }
        //@todo do_action()
        $csv_items = [];
        $x = 0;
        foreach ( $items as $item ) {
            foreach ( $fields as $field ) {
                if ( $type == 'employee' ) {
                    switch ( $field ) {
                        case 'department':
                            $csv_items[$x][$field] = $item->get_department_title();
                            break;
                        case 'designation':
                            $csv_items[$x][$field] = $item->get_job_title();
                            break;
                        default:
                            $csv_items[$x][$field] = $item->{$field};
                            break;
                    }
                } else {
                    if ( isset( $item->{$field} ) ) {
                        $csv_items[$x][$field] = $item->{$field};
                    }
                }
            }
            $x++;
        }
        $file_name = 'export_' . date( 'd_m_Y' ) . '.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $file_name );
        $output = fopen( 'php://output', 'w' );
        $columns = array_map( function( $replace ) {
            $replace = ucwords( str_replace( '_', ' ', $replace ) );
            return $replace;
        }, $fields );
        fputcsv( $output, $columns );
        foreach( $csv_items as $row )
        {
            fputcsv( $output, $row );
        }
        exit();
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
        $message = sprintf( __( '%s items successfully imported.', 'erp' ), number_format_i18n( $_REQUEST['imported'] ) );
        echo "<div class='updated'><p>{$message}</p></div>";
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
    $callback = function( $phpmailer ) use( $custom_headers ) {
        $erp_email_settings      = get_option( 'erp_settings_erp-email_general', [] );
        $erp_email_smtp_settings = get_option( 'erp_settings_erp-email_smtp', [] );
        if ( ! isset( $erp_email_settings['from_email'] ) ) {
            $from_email = get_option( 'admin_email' );
        } else {
            $from_email = $erp_email_settings['from_email'];
        }
        if ( ! isset( $erp_email_settings['from_name'] ) ) {
            global $current_user;
            $from_name = $current_user->display_name;
        } else {
            $from_name = $erp_email_settings['from_name'];
        }
        $content_type           = 'text/html';
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
            $phpmailer->Mailer     = 'smtp'; //'smtp', 'mail', or 'sendmail'
            $phpmailer->Host       = $erp_email_smtp_settings['mail_server'];
            $phpmailer->SMTPSecure = ( $erp_email_smtp_settings['authentication'] != '' ) ? $erp_email_smtp_settings['authentication'] : 'smtp';
            $phpmailer->Port       = $erp_email_smtp_settings['port'];
            if ( $erp_email_smtp_settings['authentication'] != '' ) {
                $phpmailer->SMTPAuth   = true;
                $phpmailer->Username   = $erp_email_smtp_settings['username'];
                $phpmailer->Password   = $erp_email_smtp_settings['password'];
            }
        }
    };
    add_action( 'phpmailer_init', $callback );
    ob_start();
    $is_mail_sent = wp_mail( $to, $subject, $message, $headers, $attachments );
    $debug_log = ob_get_clean();
    if ( ! $is_mail_sent ) {
        error_log( $debug_log );
    }
    remove_action( 'phpmailer_init', $callback );
    return $is_mail_sent;
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
        jQuery( document ).ready( function($) {
            $( "a#smtp-test-connection" ).click( function(e) {
                e.preventDefault();
                $( "a#smtp-test-connection" ).attr( 'disabled', 'disabled' );
                $( "a#smtp-test-connection" ).parent().find( '.erp-loader' ).show();
                var data = {
                    'action': 'erp_smtp_test_connection',
                    'enable_smtp': $('input[name=enable_smtp]:checked').val(),
                    'mail_server': $('input[name=mail_server]').val(),
                    'port': $('input[name=port]').val(),
                    'authentication': $('select[name=authentication]').val(),
                    'username': $('input[name=username]').val(),
                    'password': $('input[name=password]').val(),
                    'to' : $('#smtp_test_email_address').val(),
                    '_wpnonce': '<?php echo wp_create_nonce( "erp-smtp-test-connection-nonce" ); ?>'
                };
                $.post( ajaxurl, data, function(response) {
                    $( "a#smtp-test-connection" ).removeAttr( 'disabled' );
                    $( "a#smtp-test-connection" ).parent().find( '.erp-loader' ).hide();
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
        jQuery( document ).ready( function($) {
            $( "a#imap-test-connection" ).click( function(e) {
                e.preventDefault();
                $( "a#imap-test-connection" ).attr( 'disabled', 'disabled' );
                $( "a#imap-test-connection" ).parent().find( '.erp-loader' ).show();
                var data = {
                    'action': 'erp_imap_test_connection',
                    'mail_server': $('input[name=mail_server]').val(),
                    'username': $('input[name=username]').val(),
                    'password': $('input[name=password]').val(),
                    'protocol': $('select[name=protocol]').val(),
                    'port': $('input[name=port]').val(),
                    'authentication': $('select[name=authentication]').val(),
                    '_wpnonce': '<?php echo wp_create_nonce( "erp-imap-test-connection-nonce" ); ?>'
                };
                $.post( ajaxurl, data, function(response) {
                    $( "a#imap-test-connection" ).removeAttr( 'disabled' );
                    $( "a#imap-test-connection" ).parent().find( '.erp-loader' ).hide();
                    var type = response.success ? 'success' : 'error';
                    if ( response.data ) {
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
    $options = get_option( 'erp_settings_erp-email_imap', [] );
    $imap_status = (boolean) isset( $options['imap_status'] ) ? $options['imap_status'] : 0;
    $enable_imap = ( isset( $options['enable_imap'] ) && $options['enable_imap'] == 'yes' ) ? true : false;
    if ( $enable_imap && $imap_status ) {
        return true;
    }
    return false;
}