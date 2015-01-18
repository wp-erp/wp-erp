<?php

/**
 * [erp_create_company description]
 *
 * @param  array   [description]
 *
 * @return [type]  [description]
 */
function erp_create_company( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'title'      => '',
        'id'         => 0,
        'logo'       => 0,
        'address_1'  => '',
        'address_2'  => '',
        'city'       => '',
        'state'      => '',
        'zip'        => '',
        'country'    => '',
        'currency'   => '',
        'phone'      => '',
        'fax'        => '',
        'fax'        => '',
        'mobile'     => '',
        'website'    => '',
        'created_on' => current_time( 'mysql' ),
        'status'     => 1
    );
    $fields = wp_parse_args( $args, $defaults );

    // validation
    if ( empty( $fields['title'] ) ) {
        return new WP_Error( 'no-name', __( 'No company name provided.', 'wp-erp' ) );
    }

    $fields = apply_filters( 'erp_create_compnay_args', $fields );

    // unset the company id
    $company_id = $fields['id'];
    unset( $fields['id'] );

    if ( ! $company_id ) {

        // it's a new compnay
        if ( $wpdb->insert( $wpdb->prefix . 'erp_companies', $fields ) ) {

            do_action( 'erp_company_new', $wpdb->insert_id, $fields );

            return $wpdb->insert_id;
        }
    } else {

        // do update method here
        unset( $fields['created_on'] );
        unset( $fields['status'] );

        $fields['updated_on'] = current_time( 'mysql' );

        if ( $wpdb->update( $wpdb->prefix . 'erp_companies', $fields, array( 'id' => $company_id ) ) ) {

            do_action( 'erp_company_updated', $company_id, $fields );

            return true;
        }
    }

    return false;
}

/**
 * [erp_get_companies description]
 *
 * @return array
 */
function erp_get_companies() {
    global $wpdb;

    $cache_key = 'erp_companies';
    $companies = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $companies ) {
        $companies = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_companies" );
        wp_cache_set( $cache_key, $companies, 'wp-erp' );
    }

    return $companies;
}

/**
 * Get the current selected company for the user
 *
 * @return \WeDevs\ERP\Company the company instance
 */
function erp_get_current_company() {
    global $current_user;

    $companies   = erp_get_companies();

    if ( ! $companies ) {
        return false;
    }

    $company_ids = array_map( 'intval', wp_list_pluck( $companies, 'id' ) );
    $selected    = (int) get_user_meta( $current_user->ID, '_erp_company', true );
    $key         = array_search( $selected, $company_ids);

    if ( false !== $key ) {
        return new \WeDevs\ERP\Company( $companies[$key] );
    }

    // no company found selected, return the first one
    $first = reset( $companies );
    return new \WeDevs\ERP\Company( $first );
}

/**
 * Get the ID of current company
 *
 * @return int the company id
 */
function erp_get_current_company_id() {
    $company = erp_get_current_company();

    if ( false === $company ) {
        return false;
    }

    return $company->id;
}

/**
 * Create new or update a company location
 *
 * @param  array  $args
 *
 * @return int
 */
function erp_company_create_location( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'         => 0,
        'company_id' => erp_get_current_company_id(),
        'name'       => '',
        'address_1'  => '',
        'address_2'  => '',
        'city'       => '',
        'state'      => '',
        'zip'        => '',
        'country'    => '',
    );
    $fields = wp_parse_args( $args, $defaults );

    // validation
    if ( ! intval( $fields['company_id'] ) ) {
        return new WP_Error( 'no-company', __( 'No company id provided.', 'wp-erp' ) );
    }

    if ( empty( $fields['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No location name provided.', 'wp-erp' ) );
    }

    if ( empty( $fields['address_1'] ) ) {
        return new WP_Error( 'no-address_1', __( 'No address provided.', 'wp-erp' ) );
    }

    if ( empty( $fields['country'] ) ) {
        return new WP_Error( 'no-country', __( 'No country provided.', 'wp-erp' ) );
    }

    $location_id = intval( $fields['id'] );
    unset( $fields['id'] );

    if ( ! $location_id ) {

        // it's a new compnay
        if ( $wpdb->insert( $wpdb->prefix . 'erp_company_locations', $fields ) ) {

            do_action( 'erp_company_location_new', $wpdb->insert_id, $fields );

            return $wpdb->insert_id;
        }

    } else {

        if ( $wpdb->update( $wpdb->prefix . 'erp_company_locations', $fields, array( 'id' => $location_id ) ) ) {

            do_action( 'erp_company_location_updated', $location_id, $fields );

            return true;
        }
    }

    return false;
}

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
function erp_company_get_locations( $company_id ) {
    global $wpdb;

    $cache_key = 'erp_company-location-' . $company_id;
    $locations = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $locations ) {
        $locations = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_company_locations WHERE company_id = %d", $company_id ) );
        wp_cache_set( $cache_key, $locations, 'wp-erp' );
    }

    return $locations;
}

/**
 * Get a company location prepared for dropdown
 *
 * @param  int  $company_id
 *
 * @return array
 */
function erp_company_get_location_dropdown_raw( $company_id ) {
    $locations = erp_company_get_locations( $company_id );
    $dropdown  = array();

    foreach ($locations as $location) {
        $dropdown[ $location->id ] = $location->name;
    }

    return $dropdown;
}

/**
 * Get working days of a company
 *
 * @param  int  $company_id
 *
 * @return array
 */
function erp_company_get_working_days( $company_id ) {
    $default = array(
        'mon' => 8,
        'tue' => 8,
        'wed' => 8,
        'thu' => 8,
        'fri' => 8,
        'sat' => 0,
        'sun' => 0
    );

    $option_key = 'erp_hr_work_days_' . $company_id;
    $saved = get_option( $option_key, $default );

    if ( ! is_array( $saved ) || count( $saved ) < 7 ) {
        return $default;
    }

    return array_map( 'absint', $saved );
}