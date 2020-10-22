<?php

/**
 * Remove a company locaiton
 *
 * @param int $location_id
 *
 * @return bool
 */
function erp_company_location_delete( $location_id ) {
    global $wpdb;

    do_action( 'erp_company_location_delete', $location_id );

    return $wpdb->delete( $wpdb->prefix . 'erp_company_locations', [ 'id' => $location_id ] );
}

/**
 * Get a companies locations
 *
 * @param int $company_id
 *
 * @return array
 */
function erp_company_get_locations() {
    global $wpdb;

    $cache_key = 'erp_company-locations';
    $locations = wp_cache_get( $cache_key, 'erp' );

    if ( ! $locations ) {
        $locations = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_company_locations" );

        $company = new \WeDevs\ERP\Company();

        $main_location = (object) [
            'id'         => -1,
            'company_id' => null,
            'name'       => erp_get_company_default_location_name(),
            'address_1'  => $company->address['address_1'],
            'address_2'  => $company->address['address_2'],
            'city'       => $company->address['city'],
            'state'      => $company->address['state'],
            'zip'        => isset( $company->address['zip'] ) ? $company->address['zip'] : $company->address['postcode'],
            'country'    => $company->address['country'],
            'fax'        => $company->fax,
            'phone'      => $company->phone,
            'created_at' => null,
            'updated_at' => null,
        ];

        array_unshift( $locations, $main_location );

        wp_cache_set( $cache_key, $locations, 'erp' );
    }

    return $locations;
}

/**
 * Get a company location prepared for dropdown
 *
 * @param int    $company_id
 * @param string $select_label pass any string to be as the first element
 *
 * @return array
 */
function erp_company_get_location_dropdown_raw( $select_label = null ) {
    $locations = erp_company_get_locations();
    $dropdown  = [];

    if ( $select_label ) {
        $dropdown    = [ '-1' => $select_label ];
    }

    foreach ( $locations as $location ) {
        $dropdown[ $location->id ] = $location->name;
    }

    return $dropdown;
}

/**
 * Get working days of a company
 *
 * @return array
 */
function erp_company_get_working_days() {
    $default = [
        'mon' => 8,
        'tue' => 8,
        'wed' => 8,
        'thu' => 8,
        'fri' => 8,
        'sat' => 0,
        'sun' => 0,
    ];

    $option_key = 'erp_hr_work_days';
    $saved      = get_option( $option_key, $default );

    if ( ! is_array( $saved ) || count( $saved ) < 7 ) {
        return $default;
    }

    return array_map( 'absint', $saved );
}

/**
 * Company's default location name
 *
 * You can filter this and change it to "Head Office" or something like that
 *
 * @since 1.1.12
 *
 * @return string
 */
function erp_get_company_default_location_name() {
    return apply_filters( 'erp-company-default-name', __( 'Main Location', 'erp' ) );
}
