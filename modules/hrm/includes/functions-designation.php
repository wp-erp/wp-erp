<?php

/**
 * Create a new designation
 *
 * @param  array   arguments
 *
 * @return int|false
 */
function erp_hr_create_designation( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'          => 0,
        'title'       => '',
        'description' => '',
        'status'      => 1
    );

    $fields = wp_parse_args( $args, $defaults );

    // validation
    if ( empty( $fields['title'] ) ) {
        return new WP_Error( 'no-name', __( 'No department name provided.', 'wp-erp' ) );
    }

    // unset the department id
    $desig_id = $fields['id'];
    unset( $fields['id'] );

    $designation = new \WeDevs\ERP\HRM\Models\Designation();

    if ( ! $desig_id ) {

        $desig = $designation->create( $fields );

        do_action( 'erp_hr_desig_new', $desig->id, $fields );

        return $desig->id;

    } else {

        $designation->find( $desig_id )->update( $fields );

        do_action( 'erp_hr_desig_updated', $desig_id, $fields );

        return $desig_id;

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
function erp_hr_get_designations( $args = array() ) {
    global $wpdb;

     $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'title',
        'order'      => 'ASC',
    );

    $args = wp_parse_args( $args, $defaults );

    $cache_key = 'erp-designations';
    $designations = wp_cache_get( $cache_key, 'wp-erp' );

    $designation = new \WeDevs\ERP\HRM\Models\Designation();

    if ( false === $designations ) {

        $results = $designation
                ->skip( $args['offset'] )
                ->take( $args['number'] )
                ->orderBy( $args['orderby'], $args['order'] )
                ->get()
                ->toArray();

        $designations = erp_array_to_object( $results );

        wp_cache_set( $cache_key, $designations, 'wp-erp' );
    }


    return $designations;
}

/**
 * Delete a department
 *
 * @param  int  department id
 *
 * @return bool
 */
function erp_hr_delete_designation( $designation_id ) {
    global $wpdb;

    $designation = new \WeDevs\ERP\HRM\Designation( $designation_id );
    if ( $designation->num_of_employees() ) {
        return new WP_Error( 'not-empty', __( 'You can not delete this designation because it contains employees.', 'wp-erp' ) );
    }

    do_action( 'erp_hr_desig_delete', $designation_id );

    return \WeDevs\ERP\HRM\Models\Designation::find( $designation_id )->delete();
}

/**
 * Get the raw designations dropdown
 *
 * @param  int  company id
 *
 * @return array  the key-value paired designations
 */
function erp_hr_get_designation_dropdown_raw() {
    $designations = erp_hr_get_designations();
    $dropdown     = array( 0 => __( '- Select Designation -', 'wp-erp' ) );

    if ( $designations ) {
        foreach ($designations as $key => $designation) {
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
 * @return string  the dropdown
 */
function erp_hr_get_designation_dropdown( $company_id, $selected = '' ) {
    $designations = erp_hr_get_designation_dropdown_raw( $company_id );
    $dropdown     = '';

    if ( $designations ) {
        foreach ($designations as $key => $title) {
            $dropdown .= sprintf( "<option value='%s'>%s</option>\n", $key, $title );
        }
    }

    return $dropdown;
}

function erp_hr_count_designation() {
    return \WeDevs\ERP\HRM\Models\Designation::count();
}
