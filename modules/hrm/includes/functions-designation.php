<?php

/**
 * Create a new designation
 *
 * @param  array   arguments
 *
 * @return int|false
 */
function erp_hr_create_designation( $args = [] ) {
    global $wpdb;

    $defaults = [
        'id'          => 0,
        'title'       => '',
        'description' => '',
        'status'      => 1,
    ];

    $fields = wp_parse_args( $args, $defaults );

    // validation
    if ( empty( $fields['title'] ) ) {
        return new WP_Error( 'no-name', __( 'No designation name provided.', 'erp' ) );
    }

    // unset the department id
    $desig_id = $fields['id'];
    unset( $fields['id'] );

    $designation = new \WeDevs\ERP\HRM\Models\Designation();

    erp_hrm_purge_cache( [ 'list' => 'designation', 'designation_id' => $desig_id ] );

    if ( ! $desig_id ) {
        $desig = $designation->create( $fields );

        do_action( 'erp_hr_desig_new', $desig->id, $fields );

        return $desig->id;
    } else {
        do_action( 'erp_hr_desig_before_updated', $desig_id, $fields );

        $designation->find( $desig_id )->update( $fields );

        do_action( 'erp_hr_desig_after_updated', $desig_id, $fields );

        return $desig_id;
    }

    return false;
}

/**
 * Get all the departments of a company
 *
 * @param  int  the company id
 *
 * @return array list of departments
 */
function erp_hr_get_designations( $args = [] ) {
    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'title',
        'order'      => 'ASC',
    ];

    $args = wp_parse_args( $args, $defaults );

    $last_changed  = erp_cache_get_last_changed( 'hrm', 'designation' );
    $cache_key     = 'erp-get-designations-' . md5( serialize( $args ) ) . " : $last_changed";
    $designations  = wp_cache_get( $cache_key, 'erp' );

    if ( false === $designations ) {

        $designation = new \WeDevs\ERP\HRM\Models\Designation();

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' ) {
            $designation = $designation->skip( $args['offset'] )->take( $args['number'] );
        }

        $results = $designation
            ->orderBy( $args['orderby'], $args['order'] )
            ->get()
            ->toArray();

        $designations = erp_array_to_object( $results );

        wp_cache_set( $cache_key, $designations, 'erp' );
    }

    return $designations;
}

/**
 * Get a single designation by id
 *
 * @since 1.8.2
 *
 * @param int $designation_id
 *
 * @return object \WeDevs\ERP\HRM\Designation
 */
function erp_hr_get_single_designation( $designation_id ) {
    $cache_key   = "erp-get-designation-by-$designation_id";
    $designation = wp_cache_get( $cache_key, 'erp' );

    if ( false === $designation ) {
        $designation = new \WeDevs\ERP\HRM\Designation( $designation_id );
        wp_cache_set( $cache_key, $designation, 'erp' );
    }

    return $designation;
}

/**
 * Delete a designation
 *
 * @param int designation id
 *
 * @return bool
 */
function erp_hr_delete_designation( $designation_id ) {
    if ( is_array( $designation_id ) ) {
        $exist_employee     = [];
        $not_exist_employee = [];

        erp_hrm_purge_cache( [ 'list' => 'designation', 'designation_id' => $designation_id ] );

        foreach ( $designation_id as $key => $designation ) {
            $desig = new \WeDevs\ERP\HRM\Designation( intval( $designation ) );

            if ( $desig->num_of_employees() ) {
                $exist_employee[] = $designation;
            } else {
                do_action( 'erp_hr_desig_delete', $desig );
                $not_exist_employee[] = $designation;
            }
        }

        if ( $not_exist_employee ) {
            \WeDevs\ERP\HRM\Models\Designation::destroy( $not_exist_employee );
        }

        return $exist_employee;
    } else {
        $designation = new \WeDevs\ERP\HRM\Designation( $designation_id );

        if ( $designation->num_of_employees() ) {
            return new WP_Error( 'not-empty', __( 'You can not delete this designation because it contains employees.', 'erp' ) );
        }

        do_action( 'erp_hr_desig_delete', $designation );

        return \WeDevs\ERP\HRM\Models\Designation::find( $designation_id )->delete();
    }
}

/**
 * Get the raw designations dropdown
 *
 * @param  int  company id
 *
 * @return array the key-value paired designations
 */
function erp_hr_get_designation_dropdown_raw( $select_text = '' ) {
    $select_text  = empty( $select_text ) ? __( '- Select Designation -', 'erp' ) : $select_text;
    $designations = erp_hr_get_designations( ['number' => -1 ] );
    $dropdown     = [ '-1' => $select_text ];

    if ( $designations ) {
        foreach ( $designations as $key => $designation ) {
            $dropdown[$designation->id] = stripslashes( $designation->title );
        }
    }

    return $dropdown;
}

/**
 * Get company designations dropdown
 *
 * @param  int  company id
 * @param  string  selected designation
 *
 * @return string the dropdown
 */
function erp_hr_get_designation_dropdown( $selected = '' ) {
    $designations = erp_hr_get_designation_dropdown_raw();
    $dropdown     = '';

    if ( $designations ) {
        foreach ( $designations as $key => $title ) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}

function erp_hr_count_designation() {
    return \WeDevs\ERP\HRM\Models\Designation::count();
}

/**
 *  get all designations from database
 *  example output: [1 => 'Department 1', 2 => 'Department 2']
 * */
function erp_hr_get_designations_fresh() {
    $designations = \WeDevs\ERP\HRM\Models\Designation::all(['id', 'title']);
    return array_column( $designations->toArray(), 'title', 'id');
}
