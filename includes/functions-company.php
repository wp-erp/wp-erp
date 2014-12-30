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
        'name'      => '',
        'id'        => 0,
        'logo'      => 0,
        'address_1' => '',
        'address_2' => '',
        'city'      => '',
        'state'     => '',
        'zip'       => '',
        'country'   => '',
        'currency'  => '',
        'phone'     => '',
        'fax'       => '',
        'fax'       => '',
        'mobile'    => '',
        'website'   => '',
        'created'   => current_time( 'mysql' ),
        'status'    => 1
    );
    $fields = wp_parse_args( $args, $defaults );
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
        unset( $fields['created'] );
        unset( $fields['status'] );

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
    $companies = wp_cache_get( $cache_key, 'erp' );

    if ( false === $companies ) {
        $companies = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_companies" );
        wp_cache_set( $cache_key, $companies, 'erp' );
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