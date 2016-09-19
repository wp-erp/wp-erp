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
        'AUD' => __( 'Australian Dollars', 'erp' ),
        'AZD' => __( 'Argentine Peso', 'erp' ),
        'BDT' => __( 'Bangladeshi Taka', 'erp' ),
        'BRL' => __( 'Brazilian Real', 'erp' ),
        'BGN' => __( 'Bulgarian Lev', 'erp' ),
        'CAD' => __( 'Canadian Dollars', 'erp' ),
        'CLP' => __( 'Chilean Peso', 'erp' ),
        'CNY' => __( 'Chinese Yuan', 'erp' ),
        'COP' => __( 'Colombian Peso', 'erp' ),
        'CZK' => __( 'Czech Koruna', 'erp' ),
        'DKK' => __( 'Danish Krone', 'erp' ),
        'DOP' => __( 'Dominican Peso', 'erp' ),
        'DZD' => __( 'Algerian Dinar', 'erp' ),
        'EUR' => __( 'Euros', 'erp' ),
        'HKD' => __( 'Hong Kong Dollar', 'erp' ),
        'HRK' => __( 'Croatia kuna', 'erp' ),
        'HUF' => __( 'Hungarian Forint', 'erp' ),
        'ISK' => __( 'Icelandic krona', 'erp' ),
        'IDR' => __( 'Indonesia Rupiah', 'erp' ),
        'INR' => __( 'Indian Rupee', 'erp' ),
        'NPR' => __( 'Nepali Rupee', 'erp' ),
        'ILS' => __( 'Israeli Shekel', 'erp' ),
        'JPY' => __( 'Japanese Yen', 'erp' ),
        'KIP' => __( 'Lao Kip', 'erp' ),
        'KRW' => __( 'South Korean Won', 'erp' ),
        'MYR' => __( 'Malaysian Ringgits', 'erp' ),
        'MXN' => __( 'Mexican Peso', 'erp' ),
        'NGN' => __( 'Nigerian Naira', 'erp' ),
        'NOK' => __( 'Norwegian Krone', 'erp' ),
        'NZD' => __( 'New Zealand Dollar', 'erp' ),
        'OMR' => __( 'Omani Rial', 'erp' ),
        'IRR' => __( 'Iranian Rial', 'erp' ),
        'PYG' => __( 'Paraguayan Guaraní', 'erp' ),
        'PHP' => __( 'Philippine Pesos', 'erp' ),
        'PLN' => __( 'Polish Zloty', 'erp' ),
        'GBP' => __( 'Pounds Sterling', 'erp' ),
        'RON' => __( 'Romanian Leu', 'erp' ),
        'RUB' => __( 'Russian Ruble', 'erp' ),
        'SR'  => __( 'Saudi Riyal', 'erp'),
        'SGD' => __( 'Singapore Dollar', 'erp' ),
        'ZAR' => __( 'South African rand', 'erp' ),
        'SEK' => __( 'Swedish Krona', 'erp' ),
        'CHF' => __( 'Swiss Franc', 'erp' ),
        'TWD' => __( 'Taiwan New Dollars', 'erp' ),
        'THB' => __( 'Thai Baht', 'erp' ),
        'TRY' => __( 'Turkish Lira', 'erp' ),
        'USD' => __( 'US Dollars', 'erp' ),
        'VND' => __( 'Vietnamese Dong', 'erp' ),
        'EGP' => __( 'Egyptian Pound', 'erp' ),
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
        case 'BDT' : $currency_symbol = '&#2547;'; break;
        case 'BRL' : $currency_symbol = '&#82;&#36;'; break;
        case 'BGN' : $currency_symbol = '&#1083;&#1074;.'; break;
        case 'AUD' :
        case 'AZD' :
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
        case 'EUR' : $currency_symbol = '&euro;'; break;
        case 'CNY' :
        case 'RMB' :
        case 'JPY' :
            $currency_symbol = '&yen;';
            break;
        case 'RUB' : $currency_symbol = '&#1088;&#1091;&#1073;.'; break;
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
        case 'INR' : $currency_symbol = '&#8377;'; break;
        case 'NPR' : $currency_symbol = 'Rs.'; break;
        case 'ISK' : $currency_symbol = 'Kr.'; break;
        case 'ILS' : $currency_symbol = '&#8362;'; break;
        case 'OMR' : $currency_symbol = 'ر.ع.'; break;
        case 'IRR' : $currency_symbol = '﷼'; break;
        case 'PHP' : $currency_symbol = '&#8369;'; break;
        case 'PLN' : $currency_symbol = '&#122;&#322;'; break;
        case 'SEK' : $currency_symbol = '&#107;&#114;'; break;
        case 'SR'  : $currency_symbol = 'SR'; break;
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
        case 'DZD' : $currency_symbol = 'DA;'; break;
        case 'KIP' : $currency_symbol = '&#8365;'; break;
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
 * Get all fields for import/export operation.
 *
 * @return array
 */
function erp_get_import_export_fields() {
     $erp_fields = [
        'contact' => [
            'required_fields' => [
                'first_name',
            ],
            'fields' => [
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
        'company' => [
            'required_fields' => [
                'email',
                'company',
            ],
            'fields' => [
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
            'fields' => [
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

    if ( 'erp-settings_page_erp-tools' !== $current_screen->base ) {
        return;
    }

    if ( ! isset( $_GET['tab'] ) || ! in_array( $_GET['tab'], ['import', 'export'] ) ) {
        return;
    }

    $erp_fields = erp_get_import_export_fields();
    ?>
    <script type="text/javascript">
        jQuery( document ).ready( function( $ ) {

            function erp_str_title_case( string ) {
                var str = string.replace( /_/g, ' ' );

                return str.toLowerCase().split( ' ' ).map( function ( word ) {
                    return ( word.charAt( 0 ).toUpperCase() + word.slice( 1 ) );
                } ).join(' ');
            }

            function erp_csv_field_mapper( file_selector, fields_selector ) {
                var file = file_selector.files[0];

                var reader = new FileReader();

                var first5000 = file.slice( 0, 5000 );
                reader.readAsText( first5000 );

                reader.onload = function( e ) {
                    var csv = reader.result;
                    // Split the input into lines
                    lines = csv.split('\n'),
                    // Extract column names from the first line
                    columnNamesLine = lines[0];
                    columnNames = columnNamesLine.split(',');

                    var html = '';

                    html += '<option value="">&mdash; Select Field &mdash;</option>';
                    columnNames.forEach( function( item, index ) {
                        item = item.replace( /"/g, "" );

                        html += '<option value="' + index + '">' + item + '</option>';
                    } );

                    if ( html ) {
                        $( fields_selector ).html( html );

                        var field, field_label;
                        $( fields_selector ).each( function() {
                            field_label = $( this ).parent().parent().find( 'label' ).text();

                            var options = $( this ).find( 'option' );
                            var targetOption = $( options ).filter ( function () {
                                var option_text = $( this ).html();

                                var re = new RegExp( field_label, 'i' );

                                return re.test( option_text );
                            } );

                            if ( targetOption ) {
                                $( options ).removeAttr( "selected" );
                                $( this ).val( $( targetOption ).val() );
                            }
                        } );
                    }
                };
            }

            function erp_csv_importer_field_handler( file_selector ) {
                $( '#fields_container' ).show();

                var fields_html = '';

                var type = $( 'form#import_form #type' ).val();

                fields = erp_fields[ type ] ? erp_fields[ type ].fields : [];
                required_fields = erp_fields[ type ] ? erp_fields[ type ].required_fields : [];

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
                                <label for="fields[` + fields[i] + `]" class="csv_field_labels">` + erp_str_title_case( fields[i] ) + red_span + `</label>
                            </th>
                            <td>
                                <select name="fields[` + fields[i] + `]" class="csv_fields" ` + required + `>
                                </select>
                            </td>
                        </tr>`;
                }

                $( '#fields_container' ).html( fields_html );

                erp_csv_field_mapper( file_selector, '.csv_fields' );
            }

            var fields = [];
            var required_fields = [];

            var erp_fields = <?php echo json_encode( $erp_fields ) ; ?>;

            var type = $( 'form#export_form #type' ).val();

            fields = erp_fields[ type ] ? erp_fields[ type ].fields : [];

            var html = '<ul class="erp-list list-inline">';
            for ( var i = 0;  i < fields.length; i++ ) {
                html += '<li><label><input type="checkbox" name="fields[]" value="' + fields[i] + '"> ' + erp_str_title_case( fields[i] ) + '</label></li>';
            }

            html += '<ul>';

            if ( html ) {
                $( '#fields' ).html( html );
            }

            $( 'form#export_form #type' ).on( 'change', function( e ) {
                e.preventDefault();

                $( "#export_form #selecctall" ).prop( 'checked', false );
                var type = $( this ).val();
                fields = erp_fields[ type ] ? erp_fields[ type ].fields : [];

                html = '<ul class="erp-list list-inline">';
                for ( var i = 0;  i < fields.length; i++ ) {
                    html += '<li><label><input type="checkbox" name="fields[]" value="' + fields[i] + '"> ' + erp_str_title_case( fields[i] ) + '</label></li>';
                }

                html += '<ul>';

                if ( html ) {
                    $( 'form#export_form #fields' ).html( html );
                }
            });

            $( 'form#import_form #csv_file' ).on( 'change', function( e ) {
                e.preventDefault();

                if ( ! this ) {
                    return;
                }

                erp_csv_importer_field_handler( this );
            });

            if ( $( 'form#import_form' ).find( '#type' ).val() == 'employee' ) {
                $( 'form#import_form' ).find( '#crm_contact_lifestage_owner_wrap' ).hide();
            } else {
                $( 'form#import_form' ).find( '#crm_contact_lifestage_owner_wrap' ).show();
            }

            $( 'form#import_form #type' ).on( 'change', function( e ) {
                $( '#fields_container' ).html( '' );
                $( '#fields_container' ).hide();

                if ( $( this ).val() == 'employee' ) {
                    $( 'form#import_form' ).find( '#crm_contact_lifestage_owner_wrap' ).hide();
                } else {
                    $( 'form#import_form' ).find( '#crm_contact_lifestage_owner_wrap' ).show();
                }

                var sample_csv_url = $( 'form#import_form' ).find( '#download_sample_wrap input' ).val();
                $( 'form#import_form' ).find( '#download_sample_wrap a' ).attr( 'href', sample_csv_url + '&type=' + $( this ).val() );

                if ( $( 'form#import_form #csv_file' ).val() == "" ) {
                    return;
                } else {
                    erp_csv_importer_field_handler( $( 'form#import_form #csv_file' ).get(0) );
                }
            } );

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

    $is_crm_activated = erp_is_module_active( 'crm' );
    $is_hrm_activated = erp_is_module_active( 'hrm' );

    $departments  = erp_hr_get_departments_dropdown_raw();
    $designations = erp_hr_get_designation_dropdown_raw();

    $field_builder_options = get_option( 'erp-contact-fields' );

    if ( ! empty( $field_builder_options ) ) {
        foreach ( $field_builder_options as $field ) {
            $field_builder_contacts_fields[] = $field['name'];
        }
    }

    if ( isset( $_POST['erp_import_csv'] ) ) {
        $fields = ! empty( $_POST['fields'] ) ? $_POST['fields'] : [];
        $type   = isset( $_POST['type'] ) ? $_POST['type'] : '';

        if ( empty( $type ) ) {
            return;
        }

        $data = ['type' => $type, 'fields' => $fields, 'file' => $_FILES['csv_file'] ];

        do_action( 'erp_tool_import_csv_action', $data );

        if ( ! in_array( $type, ['contact', 'company', 'employee'] ) ) {
            return;
        }

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
                                $data[$x][$key] = isset( $line[$value] ) ? $line[$value] : '';
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
                            $contact_owner = isset( $_POST['contact_owner'] ) ? intval( $_POST['contact_owner'] ) : get_current_user_id();
                            $life_stage    = isset( $_POST['life_stage'] ) ? sanitize_key( $_POST['life_stage'] ) : '';
                            erp_people_update_meta( $item_insert_id, '_assign_crm_agent', $contact_owner );
                            erp_people_update_meta( $item_insert_id, 'life_stage', $life_stage );

                            if ( isset( $_POST['contact_group'] ) && ! empty( $_POST['contact_group'] ) ) {
                                $contact_group = intval( $_POST['contact_group'] );
                                erp_crm_create_new_contact_subscriber( ['user_id' => (int) $item_insert_id, 'group_id' => $contact_group] );
                            }


                            if ( ! empty( $field_builder_contacts_fields ) ) {
                                foreach ( $field_builder_contacts_fields as $field ) {
                                    if ( isset( $data[$x][$field] ) ) {
                                        erp_people_update_meta( $item_insert_id, $field, $data[$x][$field] );
                                    }
                                }
                            }
                        }
                    }
                }

                $x++;
            }
        }

        wp_redirect( admin_url( 'admin.php?page=erp-tools&tab=import&imported=' . $x - 1 ) );
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

        erp_make_csv_file( $csv_items, $file_name );
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

    $columns = array_map( function( $column ) {
        $column = ucwords( str_replace( '_', ' ', $column ) );

        return $column;
    }, $columns );

    fputcsv( $output, $columns );

    if ( $field_data ) {
        foreach ( $items as $row ) {
            fputcsv( $output, $row );
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
    if ( ! isset( $_GET['action'] ) || $_GET['action'] != 'download_sample' ) {
        return;
    }

    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'erp-emport-export-sample-nonce' ) ) {
        return;
    }

    if ( ! isset( $_GET['type'] ) ) {
        return;
    }

    $type   = strtolower( $_GET['type'] );
    $fields = erp_get_import_export_fields();

    if ( isset( $fields[ $type ] ) ) {
        $keys      = $fields[ $type ]['fields'];
        $keys      = array_flip( $keys );
        $file_name = "sample_csv_{$type}.csv";

        erp_make_csv_file( [ $keys ], $file_name, false );
    }

    return;
}
