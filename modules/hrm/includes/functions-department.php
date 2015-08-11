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

        return $dept->id;

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
function erp_hr_get_departments( $args = [] ) {

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'title',
        'order'      => 'ASC',
        'no_object'  => false
    );

    $args  = wp_parse_args( $args, $defaults );

    $cache_key = 'erp-get-departments';
    $results   = wp_cache_get( $cache_key, 'wp-erp' );

    $department = new \WeDevs\ERP\HRM\Models\Department();

    if ( isset( $args['s'] ) ) {
        $results = $department
                ->where( 'title', 'LIKE', '%'.$_GET['s'].'%' )
                //->skip($args['offset'])
                //->take( $args['number'])
                ->get()
                ->toArray();
        $results = erp_array_to_object( $results );
    }

    if ( false === $results ) {
        $results = $department
                //->skip($args['offset'])
                //->take( $args['number'])
                ->get()
                ->toArray();

        $results = erp_array_to_object( $results );
        wp_cache_set( $cache_key, $results, 'wp-erp' );
    }
 
    $results = erp_parent_sort( $results );

    if ( $results ) {
        foreach ($results as $key => $row) {

            if ( true === $args['no_object'] ) {
                $departments[] = $row;
            } else {

                $departments[] = new WeDevs\ERP\HRM\Department( intval( $row->id ));
            }
        }
    }

    return $departments;
}

/**
 * Get all department from a company
 *
 * @param  int   $company_id  company id
 * @param bool $no_object     if set true, Department object will be
 *                            returned as array. $wpdb rows otherwise
 *
 * @return array  the department
 */
function erp_hr_count_departments() {

    return \WeDevs\ERP\HRM\Models\Department::count();
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


function erp_elm_compare( $a, $b ) {
   
    if ( $a->parent == 0 ) return -1;
    if ( $b->parent == 0 ) return 1;

    if ( $a->id == $b->parent ) return -1;
    if ( $b->id == $a->parent ) return 1;

    return 0;
}


function erp_short_heararchycal_elem( $elem ) {
    usort( $elem, 'erp_elm_compare');
    return $elem;
}
