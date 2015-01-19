<?php

/**
 * Create a new department
 *
 * @param  array   arguments
 *
 * @return int|false
 */
function erp_hr_create_department( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'          => 0,
        'company_id'  => 0,
        'title'       => '',
        'description' => '',
        'lead'        => 0,
        'parent'      => 0,
        'status'      => 1
    );

    $fields = wp_parse_args( $args, $defaults );

    // validation
    if ( ! $fields['company_id'] ) {
        return new WP_Error( 'no-company-id', __( 'No company provided.', 'wp-erp' ) );
    }

    if ( empty( $fields['title'] ) ) {
        return new WP_Error( 'no-name', __( 'No department name provided.', 'wp-erp' ) );
    }

    // unset the department id
    $dept_id = $fields['id'];
    unset( $fields['id'] );

    if ( ! $dept_id ) {

        if ( $wpdb->insert( $wpdb->prefix . 'erp_hr_depts', $fields ) ) {

            do_action( 'erp_hr_dept_new', $wpdb->insert_id, $fields );

            return $wpdb->insert_id;
        }

    } else {

        if ( $wpdb->update( $wpdb->prefix . 'erp_hr_depts', $fields, array( 'id' => $dept_id ) ) ) {

            do_action( 'erp_hr_dept_updated', $dept_id, $fields );

            return $dept_id;
        }

    }

    return false;
}

/**
 * Get all the departments of a company
 *
 * @param  int  the company id
 *
 * @return array  list of departments
 */
function erp_hr_get_departments( $company_id ) {
    global $wpdb;

    $cache_key = 'erp-get-departments-' . $company_id;
    $results   = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $results ) {
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_hr_depts WHERE company_id = %d", $company_id ) );
        wp_cache_set( $cache_key, $results, 'wp-erp' );
    }

    return $results;
}

/**
 * Delete a department
 *
 * @param  int  department id
 *
 * @return bool
 */
function erp_hr_delete_department( $department_id ) {
    global $wpdb;

    $department = new \WeDevs\ERP\HRM\Department( $department_id );
    if ( $department->num_of_employees() ) {
        return new WP_Error( 'not-empty', __( 'You can not delete this department because it contains employees.', 'wp-erp' ) );
    }

    do_action( 'erp_hr_dept_delete', $department_id );

    return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}erp_hr_depts WHERE id = %d", $department_id ) );
}

/**
 * Get the raw departments dropdown
 *
 * @param  int  company id
 * @param string  $select_label pass any string to be as the first element
 *
 * @return array  the key-value paired departments
 */
function erp_hr_get_departments_dropdown_raw( $company_id, $select_label = null ) {
    $departments = erp_hr_get_departments( $company_id );
    $dropdown    = array( 0 => __( '- Select Department -', 'wp-erp' ) );

    if ( $select_label ) {
        $dropdown    = array( 0 => $select_label );
    }

    if ( $departments ) {
        foreach ($departments as $key => $department) {
            $dropdown[$department->id] = stripslashes( $department->title );
        }
    }

    return $dropdown;
}

/**
 * Get company departments dropdown
 *
 * @param  int  company id
 * @param  string  selected department
 *
 * @return string  the dropdown
 */
function erp_hr_get_departments_dropdown( $company_id, $selected = '' ) {
    $departments = erp_hr_get_departments_dropdown_raw( $company_id );
    $dropdown    = '';

    if ( $departments ) {
        foreach ($departments as $key => $title) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}
