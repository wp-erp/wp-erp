<?php

/**
 * Insert new ERP employee row on new employee user role creation
 *
 * @param  int  user id
 *
 * @return void
 */
function erp_hr_employee_on_initialize( $user_id ) {
    global $wpdb;

    $user = get_user_by( 'id', $user_id );
    $role = reset( $user->roles );

    if ( 'employee' == $role ) {
        $wpdb->insert( $wpdb->prefix . 'erp_hr_employees', array(
            'employee_id' => $user_id,
            'company_id'  => erp_get_current_company_id(),
            'designation' => 0,
            'department'  => 0,
            'status'      => 1
        ) );
    }
}

add_action( 'user_register', 'erp_hr_employee_on_initialize' );

/**
 * Delete an employee if removed from WordPress usre table
 *
 * @param  int  the user id
 *
 * @return void
 */
function erp_hr_employee_on_delete( $user_id ) {
    global $wpdb;

    $user = get_user_by( 'id', $user_id );
    $role = reset( $user->roles );

    if ( 'employee' == $role ) {

        do_action( 'erp_hr_employee_delete', $user_id, $user );

        $wpdb->delete( $wpdb->prefix . 'erp_hr_employees', array(
            'employee_id' => $user_id
        ) );
    }
}

add_action( 'delete_user', 'erp_hr_employee_on_delete' );

/**
 * Create a new employee
 *
 * @param  array  arguments
 *
 * @return int  employee id
 */
function erp_hr_employee_create( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'employee_id'     => 0,
        'user_email'      => '',
        'photo_id'        => 0,
        'company_id'      => erp_get_current_company_id(),
        'name'            => array(
            'first_name'      => '',
            'middle_name'     => '',
            'last_name'       => ''
        ),
        'work'            => array(
            'department'        => '',
            'designation'       => '',
            'reporting_to'      => '',
            'joined'            => '',
            'hiring_source'     => '',
            'status'            => '',
            'type'              => '',
            'phone'             => '',
        ),
        'personal'        => array(
            'phone'           => '',
            'mobile'          => '',
            'address'         => '',
            'other_email'     => '',
            'dob'             => '',
            'gender'          => '',
            'nationality'     => '',
            'marital_status'  => '',
            'driving_license' => '',
            'hobbies'         => '',
            'user_url'        => '',
            'description'     => '',
        )
    );

    $posted = array_map( 'strip_tags_deep', $args );
    $posted = array_map( 'trim_deep', $posted );
    $data   = wp_parse_args( $posted, $defaults );

    // some basic validation
    if ( empty( $data['name']['first_name'] ) ) {
        return new WP_Error( 'empty-first-name', __( 'Please provide the first name.', 'wp-erp' ) );
    }

    if ( empty( $data['name']['last_name'] ) ) {
        return new WP_Error( 'empty-last-name', __( 'Please provide the last name.', 'wp-erp' ) );
    }

    if ( ! is_email( $data['user_email'] ) ) {
        return new WP_Error( 'invalid-email', __( 'Please provide a valid email address.', 'wp-erp' ) );
    }

    // attempt to create the user
    $password = wp_generate_password( 12 );
    $userdata = array(
        'user_login' => $data['user_email'],
        'user_pass'  => $password,
        'user_email' => $data['user_email'],
        'first_name' => $data['name']['first_name'],
        'last_name'  => $data['name']['last_name'],
        'role'       => 'employee'
    );

    // if user id exists, do an update
    $user_id = isset( $posted['user_id'] ) ? intval( $posted['user_id'] ) : 0;
    $update  = false;

    if ( $user_id ) {
        $update = true;
        $userdata['ID'] = $user_id;
    }

    $userdata = apply_filters( 'erp_hr_employee_args', $userdata );
    $user_id  = wp_insert_user( $userdata );

    if ( is_wp_error( $user_id ) ) {
        return $user_id;
    }

    $not_meta = array(
        'department',
        'designation',
        'company_id',
        'user_email'
    );

    // update the erp table
    $wpdb->update( $wpdb->prefix . 'erp_hr_employees', array(
        'company_id'  => (int) $data['company_id'],
        'department'  => (int) $data['work']['department'],
        'designation' => (int) $data['work']['designation']
    ), array( 'employee_id' => $user_id ) );

    // unset some non-required fields
    if ( isset( $data['user_id'] ) ) {
        unset( $data['user_id'] );
    }

    // make sure reporting_to != user_id
    $data['work']['reporting_to'] = ( $user_id != $data['work']['reporting_to'] ) ? intval( $data['work']['reporting_to'] ) : 0;

    // if reached here, seems like we have success creating the user
    foreach ($data as $key => $value) {
        if ( is_array( $value ) ) {

            foreach ($value as $new_key => $new_value) {
                if ( ! in_array( $new_key, $not_meta ) ) {

                    // except "name", save others to $parent_$child key name
                    if ( in_array( $key, array( 'name' ) ) ) {
                        $meta_key = $new_key;
                    } else {
                        $meta_key = $key . '_' . $new_key;
                    }

                    update_user_meta( $user_id, $meta_key, $new_value );
                }
            }

        } else {
            if ( ! in_array( $key, $not_meta ) ) {
                update_user_meta( $user_id, $key, $value );
            }
        }
    }

    if ( $update ) {
        do_action( 'erp_hr_employee_update', $user_id, $data );
    } else {
        do_action( 'erp_hr_employee_new', $user_id, $data );
    }

    return $user_id;
}

/**
 * Get all employees from a company
 *
 * @param  int  company id
 *
 * @return array  the employees
 */
function erp_hr_get_employees( $company_id ) {
    global $wpdb;

    $sql = "SELECT employee_id as user_id
        FROM {$wpdb->prefix}erp_hr_employees
        WHERE company_id = %d";

    $results = $wpdb->get_results( $wpdb->prepare( $sql, $company_id ) );
    $users   = array();

    if ( $results ) {
        foreach ($results as $key => $row) {
            $users[] = new \WeDevs\ERP\HRM\Employee( intval( $row->user_id ) );
        }
    }

    return $users;
}

/**
 * Get the raw employees dropdown
 *
 * @param  int  company id
 *
 * @return array  the key-value paired employees
 */
function erp_hr_get_employees_dropdown_raw( $company_id ) {
    $employees = erp_hr_get_employees( $company_id );
    $dropdown  = array( 0 => __( '- Select Employee -', 'wp-erp' ) );

    if ( $employees ) {
        foreach ($employees as $key => $employee) {
            $dropdown[$employee->id] = $employee->get_full_name();
        }
    }

    return $dropdown;
}

/**
 * Get company employees dropdown
 *
 * @param  int  company id
 * @param  string  selected department
 *
 * @return string  the dropdown
 */
function erp_hr_get_employees_dropdown( $company_id, $selected = '' ) {
    $employees = erp_hr_get_employees_dropdown_raw( $company_id );
    $dropdown  = '';

    if ( $employees ) {
        foreach ($employees as $key => $title) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}

/**
 * Get the registered employee statuses
 *
 * @return array the employee statuses
 */
function erp_hr_get_employee_statuses() {
    $statuses = array(
        'active'     => __( 'Active', 'wp-erp' ),
        'terminated' => __( 'Terminated', 'wp-erp' ),
        'deceased'   => __( 'Deceased', 'wp-erp' ),
        'resigned'   => __( 'Resigned', 'wp-erp' )
    );

    return apply_filters( 'erp_hr_employee_statuses', $statuses );
}

/**
 * Get the registered employee statuses
 *
 * @return array the employee statuses
 */
function erp_hr_get_employee_types() {
    $types = array(
        'permanent' => __( 'Permanent', 'wp-erp' ),
        'contract'  => __( 'On Contract', 'wp-erp' ),
        'temporary' => __( 'Temporary', 'wp-erp' ),
        'trainee'   => __( 'Trainee', 'wp-erp' )
    );

    return apply_filters( 'erp_hr_employee_types', $types );
}

/**
 * Get the registered employee hire sources
 *
 * @return array the employee hire sources
 */
function erp_hr_get_employee_sources() {
    $sources = array(
        'direct'        => __( 'Direct', 'wp-erp' ),
        'referral'      => __( 'Referral', 'wp-erp' ),
        'web'           => __( 'Web', 'wp-erp' ),
        'newspaper'     => __( 'Newspaper', 'wp-erp' ),
        'advertisement' => __( 'Advertisement', 'wp-erp' ),
        'social'        => __( 'Social Network', 'wp-erp' ),
        'other'         => __( 'Other', 'wp-erp' ),
    );

    return apply_filters( 'erp_hr_employee_sources', $sources );
}

/**
 * Get marital statuses
 *
 * @return array all the statuses
 */
function erp_hr_get_marital_statuses() {
    $statuses = array(
        'single'  => __( 'Single', 'wp-erp' ),
        'married' => __( 'Married', 'wp-erp' ),
        'widowed' => __( 'Widowed', 'wp-erp' )
    );

    return apply_filters( 'erp_hr_marital_statuses', $statuses );
}

/**
 * Get marital statuses
 *
 * @return array all the statuses
 */
function erp_hr_get_genders() {
    $genders = array(
        'male'   => __( 'Male', 'wp-erp' ),
        'female' => __( 'Female', 'wp-erp' ),
        'other'  => __( 'Other', 'wp-erp' )
    );

    return apply_filters( 'erp_hr_genders', $genders );
}
