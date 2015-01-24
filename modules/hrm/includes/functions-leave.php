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
        'reason'       => '',
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

    $period = erp_hr_get_work_days_between_dates( $start_date, $end_date );

    if ( is_wp_error( $period ) ) {
        return $period;
    }

    // prepare the periods
    $leaves = array();
    if ( $period['days'] ) {
        foreach ($period['days'] as $date) {
            if ( ! $date['count'] ) {
                continue;
            }

            $leaves[] = array(
                'date'          => $date['date'],
                'length_hours'  => '08:00:00',
                'length_days'   => '1.00',
                'start_time'    => '00:00:00',
                'end_time'      => '00:00:00',
                'duration_type' => 1
            );
        }
    }

    if ( $leaves ) {

        $request = array(
            'user_id'    => $user_id,
            'policy_id'  => $leave_policy,
            'reason'     => wp_kses_post( $reason ),
            'status'     => 2, // default is pending
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
 * Fetch the leave requests
 *
 * @param  array   $args
 *
 * @return array
 */
function erp_hr_leave_get_requests( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'  => 20,
        'offset'  => 0,
        'user_id' => 0,
        'status'  => '',
        'orderby' => 'created_on',
        'order'   => 'DESC',
    );

    $args  = wp_parse_args( $args, $defaults );
    $where = '';

    if ( 'all' != $args['status'] && $args['status'] != '' ) {

        if ( empty( $where ) ) {
            $where .= " WHERE";
        } else {
            $where .= " AND";
        }

        if ( is_array( $args['status'] ) ) {
            $where .= " `status` IN('" . implode( "','", array_map( 'intval', $args['status'] ) ) . "') ";
        } else {
            $where .= " `status` = '" . intval( $args['status'] ) . "' ";
        }
    }

    if ( $args['user_id'] != '0' ) {

        if ( empty( $where ) ) {
            $where .= " WHERE req.user_id = " . intval( $args['user_id'] );
        } else {
            $where .= " AND req.user_id = " . intval( $args['user_id'] );
        }
    }

    $cache_key = 'erp_hr_leave_requests_' . md5( serialize( $args ) );
    $requests  = wp_cache_get( $cache_key, 'wp-erp' );

    $sql = "SELECT req.id, req.user_id, u.display_name, req.policy_id, pol.name as policy_name, req.status, req.reason, req.comments, req.created_on, ( SELECT count(id) FROM wp_erp_hr_leaves WHERE request_id = req.id) as days
        FROM {$wpdb->prefix}erp_hr_leave_requests AS req
        LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS pol ON pol.id = req.policy_id
        LEFT JOIN $wpdb->users AS u ON req.user_id = u.ID
        $where
        ORDER BY {$args['orderby']} {$args['order']}
        LIMIT %d,%d;";
    // echo $sql;

    if ( $requests === false ) {
        $requests = $wpdb->get_results( $wpdb->prepare( $sql, absint( $args['offset'] ), absint( $args['number'] ) ) );
        wp_cache_set( $cache_key, $requests, 'wp-erp', HOUR_IN_SECONDS );
    }

    return $requests;
}

/**
 * Get leave requests count
 *
 * @return array
 */
function erp_hr_leave_get_requests_count() {
    global $wpdb;

    $statuses = erp_hr_leave_request_get_statuses();
    $counts   = array();

    foreach ($statuses as $status => $label) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    $cache_key = 'erp-hr-leave-request-counts';
    $results = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $results ) {
        $sql     = "SELECT status, COUNT(id) as num FROM {$wpdb->prefix}erp_hr_leave_requests WHERE status != 0 GROUP BY status;";
        $results = $wpdb->get_results( $sql );

        wp_cache_set( $cache_key, $results, 'wp-erp' );
    }

    foreach ($results as $row) {
        if ( array_key_exists( $row->status, $counts ) ) {
            $counts[ $row->status ]['count'] = (int) $row->num;
        }

        $counts['all']['count'] += (int) $row->num;
    }

    return $counts;
}

function erp_hr_leave_request_update_status( $request_id, $status ) {
    global $wpdb;

    return $wpdb->update( $wpdb->prefix . 'erp_hr_leave_requests',
        array( 'status' => $status ),
        array( 'id' => $request_id )
    );
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
        '2'   => __( 'Pending', 'wp-erp' ),
        '3'   => __( 'Rejected', 'wp-erp' )
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
