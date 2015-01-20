<?php

/**
 * Insert a new leave policy
 *
 * @param array $args
 */
function erp_hr_leave_insert_policy( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'         => null,
        'company_id' => erp_get_current_company_id(),
        'name'       => '',
        'unit'       => 'day',
        'value'      => 0,
        'color'      => '',
        'created_on' => current_time( 'mysql' )
    );

    $args = wp_parse_args( $args, $defaults );

    // some validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No name provided.', 'wp-error' ) );
    }

    if ( ! intval( $args['value'] ) ) {
        return new WP_Error( 'no-value', __( 'No duration provided.', 'wp-error' ) );
    }

    $args['name'] = sanitize_text_field( $args['name'] );

    $policy_id = (int) $args['id'];
    unset( $args['id'] );

    if ( ! $policy_id ) {
        // it's new
        if ( $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_policies', $args ) ) {

            do_action( 'erp_hr_leave_policy_new', $wpdb->insert_id, $args );

            return $wpdb->insert_id;
        }

    } else {
        // do update method here
        unset( $args['created_on'] );
        $args['updated_on'] = current_time( 'mysql' );

        if ( $wpdb->update( $wpdb->prefix . 'erp_hr_leave_policies', $args, array( 'id' => $policy_id ) ) ) {

            do_action( 'erp_hr_leave_policy_updated', $policy_id, $args );

            return $policy_id;
        }
    }
}

/**
 * Fetch leave policies by company
 *
 * @return array
 */
function erp_hr_leave_get_policies( $company_id ) {
    global $wpdb;

    $cache_key = 'erp-leave-pol-' . $company_id;
    $policies = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $policies ) {
        $policies = $wpdb->get_results(
            $wpdb->prepare( "SELECT id, name, value, color FROM {$wpdb->prefix}erp_hr_leave_policies WHERE company_id = %d", $company_id )
        );

        wp_cache_set( $cache_key, $policies, 'wp-erp' );
    }

    return $policies;
}

/**
 * Fetch a leave policy
 *
 * @return \stdClass
 */
function erp_hr_leave_get_policy( $policy_id ) {
    global $wpdb;

    $policy = $wpdb->get_row(
        $wpdb->prepare( "SELECT id, name, value, color FROM {$wpdb->prefix}erp_hr_leave_policies WHERE id = %d", $policy_id )
    );

    return $policy;
}

/**
 * Get policies as formatted for dropdown
 *
 * @param  int  $company_id
 *
 * @return array
 */
function erp_hr_leave_get_policies_dropdown_raw( $company_id ) {
    $policies = erp_hr_leave_get_policies( $company_id );
    $dropdown = array();

    foreach ($policies as $policy) {
        $dropdown[ $policy->id ] = stripslashes( $policy->name );
    }

    return $dropdown;
}

/**
 * Delete a policy
 *
 * @param  int  policy id
 *
 * @return bool
 */
function erp_hr_leave_policy_delete( $policy_id ) {
    global $wpdb;

    do_action( 'erp_hr_leave_policy_delete', $policy_id );

    return $wpdb->delete( $wpdb->prefix . 'erp_hr_leave_policies', array( 'id' => $policy_id ) );
}

/**
 * Insert a new policy entitlement for an employee
 *
 * @param  array   $args
 *
 * @return int|\WP_Error
 */
function erp_hr_leave_insert_entitlement( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'         => null,
        'user_id'    => 0,
        'policy_id'  => 0,
        'days'       => 0,
        'from_date'  => '',
        'to_date'    => '',
        'comments'   => '',
        'status'     => 1,
        'created_by' => get_current_user_id(),
        'created_on' => current_time( 'mysql' )
    );

    $fields = wp_parse_args( $args, $defaults );

    if ( ! intval( $fields['user_id'] ) ) {
        return new WP_Error( 'no-user', __( 'No employee provided.', 'wp-erp' ) );
    }

    if ( ! intval( $fields['policy_id'] ) ) {
        return new WP_Error( 'no-policy', __( 'No policy provided.', 'wp-erp' ) );
    }

    if ( empty( $fields['from_date'] ) || empty( $fields['to_date'] ) ) {
        return new WP_Error( 'no-date', __( 'No date provided.', 'wp-erp' ) );
    }

    return $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_entitlements', $fields );
}

/**
 * Add a new leave request
 *
 * @param  array   $args
 *
 * @return int  request id
 */
function erp_hr_leave_insert_request( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'user_id'      => 0,
        'leave_policy' => 0,
        'start_date'   => current_time('mysql'),
        'end_date'     => current_time('mysql'),
        'comment'      => '',
        'status'       => 0
    );

    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    if ( ! intval( $user_id ) ) {
        return new WP_Error( 'no-employee', __( 'No employee ID provided.', 'wp-error' ) );
    }

    if ( ! intval( $leave_policy ) ) {
        return new WP_Error( 'no-policy', __( 'No leave policy provided.', 'wp-error' ) );
    }

    $start_date = new DateTime( $start_date );
    $end_date   = new DateTime( $end_date );
    $end_date->modify( '+1 day' ); // to get proper days in duration
    $diff = $start_date->diff( $end_date );

    // we got a negative date
    if ( $diff->invert || ! $diff->days ) {
        return new WP_Error( 'invalid-date', __( 'Invalid date provided', 'wp-erp' ) );
    }

    $interval = DateInterval::createFromDateString( '1 day' );
    $period   = new DatePeriod( $start_date, $interval, $end_date );

    // prepare the periods
    $leaves = array();
    foreach ( $period as $dt ) {
        $leaves[] = array(
            'date'          => $dt->format( 'Y-m-d' ),
            'length_hours'  => '08:00:00',
            'length_days'   => '1.00',
            'start_time'    => '00:00:00',
            'end_time'      => '00:00:00',
            'duration_type' => 1
        );
    }

    if ( $leaves ) {

        $request = array(
            'user_id'    => $user_id,
            'policy_id'  => $leave_policy,
            'comments'   => wp_kses_post( $comment ),
            'status'     => 0,
            'created_by' => get_current_user_id(),
            'created_on' => current_time( 'mysql' ),
        );

        if ( $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_requests', $request ) ) {
            $request_id = $wpdb->insert_id;

            foreach ($leaves as $leave) {
                $leave['request_id'] = $request_id;

                $wpdb->insert( $wpdb->prefix . 'erp_hr_leaves', $leave );
            }

            do_action( 'erp_hr_leave_new', $request_id, $request, $leaves );

            return $request_id;
        }
    }

    return false;
}

/**
 * Get leave requests status
 *
 * @param  int|boolean  $status
 *
 * @return array|string
 */
function erp_hr_leave_request_get_statuses( $status = false ) {
    $statuses = array(
        'all' => __( 'All', 'wp-erp' ),
        '1'   => __( 'Approved', 'wp-erp' ),
        '0'   => __( 'Pending', 'wp-erp' ),
        '2'   => __( 'Rejected', 'wp-erp' )
    );

    if ( false !== $status && array_key_exists( $status, $statuses ) ) {
        return $statuses[ $status ];
    }

    return $statuses;
}

/**
 * Entitlement checking
 *
 * Check if an employee has already entitled to a policy in
 * a certain calendar year
 *
 * @param  int  $employee_id
 * @param  int  $policy_id
 * @param  int  $year
 *
 * @return bool
 */
function erp_hr_leave_has_employee_entitlement( $employee_id, $policy_id, $year ) {
    global $wpdb;

    $from_date = $year . '-01-01';
    $to_date   = $year . '-12-31';

    $query = "SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements
        WHERE user_id = %d AND policy_id = %d AND from_date = %s AND to_date = %s";
    $result = $wpdb->get_var( $wpdb->prepare( $query, $employee_id, $policy_id, $from_date, $to_date ) );

    return $result;
}

/**
 * Get all the leave entitlements of a calendar year
 *
 * @param  int  $year
 *
 * @return array
 */
function erp_hr_leave_get_entitlements( $year ) {
    global $wpdb;

    $from_date = $year . '-01-01';
    $to_date   = $year . '-12-31';

    $query = "SELECT en.*, u.display_name as employee_name, pol.name as policy_name
        FROM `{$wpdb->prefix}erp_hr_leave_entitlements` AS en
        LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS pol ON pol.id = en.policy_id
        LEFT JOIN {$wpdb->users} AS u ON en.user_id = u.ID
        WHERE en.from_date >= '$from_date' AND en.to_date <= '$to_date'
        ORDER BY en.user_id, en.created_on DESC";

    $results = $wpdb->get_results( $query );

    return $results;
}

/**
 * Employee leave entitlement form handler
 *
 * @return void
 */
function erp_hr_leave_entitlement_handler() {
    if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-hr-leave-assign' ) ) {
        die( __( 'Something went wrong!', 'wp-erp' ) );
    }

    $affected        = 0;
    $errors          = array();
    $employees       = array();
    $cur_year        = (int) date( 'Y' );
    $page_url        = admin_url( 'admin.php?page=erp-leave-assign&tab=assignment' );

    $is_single       = ! isset( $_POST['assignment_to'] );
    $leave_policy    = isset( $_POST['leave_policy'] ) ? intval( $_POST['leave_policy'] ) : 0;
    $leave_period    = isset( $_POST['leave_period'] ) ? intval( $_POST['leave_period'] ) : 0;
    $single_employee = isset( $_POST['single_employee'] ) ? intval( $_POST['single_employee'] ) : 0;
    $location        = isset( $_POST['location'] ) ? intval( $_POST['location'] ) : 0;
    $department      = isset( $_POST['department'] ) ? intval( $_POST['department'] ) : 0;
    $comment         = isset( $_POST['comment'] ) ? wp_kses_post( $_POST['comment'] ) : 0;

    if ( ! $leave_policy ) {
        $errors[] = 'invalid-policy';
    }

    if ( ! in_array( $leave_period, array( $cur_year, $cur_year + 1 ) ) ) {
        $errors[] = 'invalid-period';
    }

    if ( $is_single && ! $single_employee ) {
        $errors[] = 'invalid-employee';
    }

    // bail out if error found
    if ( $errors ) {
        $first_error = reset( $errors );
        $redirect_to = add_query_arg( array( 'error' => $first_error ), $page_url );
        wp_safe_redirect( $redirect_to );
        exit;
    }

    // fetch employees if not single
    if ( ! $is_single ) {
        $company_id = erp_get_current_company_id();

        $employees = erp_hr_employees_get_by_location_department( $company_id, $location, $department );
    } else {

        $user              = get_user_by( 'id', $single_employee );
        $emp               = new \stdClass();
        $emp->user_id      = $user->ID;
        $emp->display_name = $user->display_name;

        $employees[] = $emp;
    }

    if ( $employees ) {
        $from_date = $leave_period . '-01-01';
        $to_date   = $leave_period . '-12-31';
        $policy    = erp_hr_leave_get_policy( $leave_policy );

        if ( ! $policy ) {
            return;
        }

        foreach ($employees as $employee) {
            if ( ! erp_hr_leave_has_employee_entitlement( $employee->user_id, $leave_policy, $leave_period ) ) {
                $data = array(
                    'user_id'   => $employee->user_id,
                    'policy_id' => $leave_policy,
                    'days'      => $policy->value,
                    'from_date' => $from_date,
                    'to_date'   => $to_date,
                    'comments'  => $comment,
                    'status'    => 1
                );

                $inserted = erp_hr_leave_insert_entitlement( $data );

                if ( ! is_wp_error( $inserted ) ) {
                    $affected += 1;
                }
            }
        }

        $redirect_to = add_query_arg( array( 'affected' => $affected ), $page_url );
        wp_safe_redirect( $redirect_to );
        exit;
    }
}

add_action( 'erp_action_hr-leave-assign-policy', 'erp_hr_leave_entitlement_handler' );
