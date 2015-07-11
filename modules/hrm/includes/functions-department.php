<?php

/**
 * Create a new department
 *
 * @param  array   arguments
 *
 * @return int|false
 */
function erp_hr_create_department( $args = array() ) {

    $defaults = array(
        'id'          => 0,
        'title'       => '',
        'description' => '',
        'lead'        => 0,
        'parent'      => 0,
        'status'      => 1
    );

    $fields = wp_parse_args( $args, $defaults );

    // validation
    if ( empty( $fields['title'] ) ) {
        return new WP_Error( 'no-name', __( 'No department name provided.', 'wp-erp' ) );
    }

    // unset the department id
    $dept_id = $fields['id'];
    unset( $fields['id'] );

    $department = new \WeDevs\ERP\HRM\Models\Department();

    if ( ! $dept_id ) {
        $dept = $department->create( $fields );

        do_action( 'erp_hr_dept_new', $dept->id, $fields );

        return $department->id;

    } else {

        $department->find( $dept_id )->update( $fields );

        do_action( 'erp_hr_dept_updated', $dept_id, $fields );

        return $dept_id;
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
function erp_hr_get_departments() {

    $cache_key = 'erp-get-departments';
    $results   = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $results ) {
        $results = erp_array_to_object( \WeDevs\ERP\HRM\Models\Department::all()->toArray() );

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

    $department = new \WeDevs\ERP\HRM\Department( $department_id );
    if ( $department->num_of_employees() ) {
        return new WP_Error( 'not-empty', __( 'You can not delete this department because it contains employees.', 'wp-erp' ) );
    }

    do_action( 'erp_hr_dept_delete', $department_id );

    return \WeDevs\ERP\HRM\Models\Department::find( $department_id )->delete();
}

/**
 * Get the raw departments dropdown
 *
 * @param  int  company id
 * @param string  $select_label pass any string to be as the first element
 *
 * @return array  the key-value paired departments
 */
function erp_hr_get_departments_dropdown_raw($select_label = null ) {
    $departments = erp_hr_get_departments();
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
 * @param  string  selected department
 *
 * @return string  the dropdown
 */
function erp_hr_get_departments_dropdown( $selected = '' ) {
    $departments = erp_hr_get_departments_dropdown_raw();
    $dropdown    = '';

    if ( $departments ) {
        foreach ($departments as $key => $title) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}
