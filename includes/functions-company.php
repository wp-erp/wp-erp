<?php

/**
 * Remove a company locaiton
 *
 * @param  int  $location_id
 *
 * @return bool
 */
function erp_company_location_delete( $location_id ) {
    global $wpdb;

    do_action( 'erp_company_location_delete', $location_id );

    return $wpdb->delete( $wpdb->prefix . 'erp_company_locations', array( 'id' => $location_id ) );
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

    $cache_key = 'erp_company-location';
    $locations = wp_cache_get( $cache_key, 'erp' );

    if ( false === $locations ) {
        $locations = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_company_locations" );
        wp_cache_set( $cache_key, $locations, 'erp' );
    }

    return $locations;
}

/**
 * Get a company location prepared for dropdown
 *
 * @param int     $company_id
 * @param string  $select_label pass any string to be as the first element
 *
 * @return array
 */
function erp_company_get_location_dropdown_raw( $select_label = null ) {
    $locations = erp_company_get_locations();
    $dropdown  = array( '-1' => __( 'Main Location', 'erp' ) );

    if ( $select_label ) {
        $dropdown    = array( '-1' => $select_label );
    }

    foreach ($locations as $location) {
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
    $default = array(
        'mon' => 8,
        'tue' => 8,
        'wed' => 8,
        'thu' => 8,
        'fri' => 8,
        'sat' => 0,
        'sun' => 0
    );

    $option_key = 'erp_hr_work_days';
    $saved      = get_option( $option_key, $default );

    if ( ! is_array( $saved ) || count( $saved ) < 7 ) {
        return $default;
    }

    return array_map( 'absint', $saved );
}
