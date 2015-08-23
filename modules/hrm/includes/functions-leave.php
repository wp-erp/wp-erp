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
        'name'       => '',
        'unit'       => 'day',
        'value'      => 0,
        'color'      => '',
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

    $leave_policies = new \WeDevs\ERP\HRM\Models\Leave_Policies();

    if ( ! $policy_id ) {
        // insert a new
        $leave_policy = $leave_policies->create( $args );

        if ( $leave_policy ) {

            do_action( 'erp_hr_leave_policy_new', $wpdb->insert_id, $args );
            return $leave_policy->id;
        }

    } else {
        // do update method here
        if ( $leave_policies->find( $policy_id )->update( $args ) ) {

            do_action( 'erp_hr_leave_policy_updated', $policy_id, $args );
            return $policy_id;
        }
    }
}

/**
 * Insert a leave holiday
 *
 * @param array $args
 */
function erp_hr_leave_insert_holiday( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'          => null,
        'title'       => '',
        'start'       => current_filter('mysql'),
        'end'         => '',
        'description' => '',
    );

    $args = wp_parse_args( $args, $defaults );

    // some validation
    if ( empty( $args['title'] ) ) {
        return new WP_Error( 'no-name', __( 'No title provided.', 'wp-error' ) );
    }

    if ( empty( $args['start'] ) ) {
        return new WP_Error( 'no-value', __( 'No start date provided.', 'wp-error' ) );
    }

    if ( empty( $args['end'] ) ) {
        return new WP_Error( 'no-value', __( 'No end date provided.', 'wp-error' ) );
    }

    $args['title'] = sanitize_text_field( $args['title'] );

    $holiday_id = (int) $args['id'];
    unset( $args['id'] );

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();

    if ( ! $holiday_id ) {
        // insert a new
        $leave_policy = $holiday->create( $args );

        if ( $leave_policy ) {

            do_action( 'erp_hr_new_holiday', $wpdb->insert_id, $args );
            return $leave_policy->id;
        }

    } else {
        // do update method here
        if ( $holiday->find( $holiday_id )->update( $args ) ) {

            do_action( 'erp_hr_update_holiday', $holiday_id, $args );
            return $holiday_id;
        }
    }
}

/**
 * Fetch leave policies by company
 *
 * @return array
 */
function erp_hr_leave_get_policies( $args = array() ) {

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'name',
        'order'      => 'ASC',
    );

    $args = wp_parse_args( $args, $defaults );


    $cache_key = 'erp-leave-pol';
    $policies = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $policies ) {

        $policies = erp_array_to_object(
                        \WeDevs\ERP\HRM\Models\Leave_Policies::select( array( 'id', 'name', 'value', 'color' ) )
                        ->skip( $args['offset'] )
                        ->take( $args['number'] )
                        ->orderBy( $args['orderby'], $args['order'] )
                        ->get()
                        ->toArray()
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

    $policy = \WeDevs\ERP\HRM\Models\Leave_Policies::select( array( 'id', 'name', 'value', 'color' ) )
                ->find( $policy_id )
                ->toArray();

    return  (object) $policy;
}

/**
 * Count total leave policies
 *
 * @return integer
 */
function erp_hr_count_leave_policies() {
    return \WeDevs\ERP\HRM\Models\Leave_Policies::count();
}

/**
 * Fetch holidays by company
 *
 * @return array
 */
function erp_hr_get_holidays( $args = [] ) {
    $defaults = array(
        'number' => 20,
        'offset' => 0,
    );

    $args  = wp_parse_args( $args, $defaults );

    $holidays = erp_hr_holiday_search( $args );

    if ( $holidays !== false ) {
        return $holidays;
    }

    //if not search then execute nex code
    if ( isset( $args['id'] ) && ! empty( $args['id'] ) ) {
        $id = intval( $args['id'] );
    }

    $cache_key = 'erp-get-holidays-' . md5( serialize( $args ) );
    $holidays = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $holidays ) {
        if ( isset( $id ) ) {
            $holidays = erp_array_to_object(
                \WeDevs\ERP\HRM\Models\Leave_Holiday::select( array( 'id', 'title', 'start', 'end', 'description' ) )
                ->where( 'id', '=', $id )
                ->skip( $args['offset'] )
                ->take( $args['number'] )
                ->get()
                ->toArray()
            );
        } else {
            $holidays = erp_array_to_object(
                \WeDevs\ERP\HRM\Models\Leave_Holiday::select( array( 'id', 'title', 'start', 'end', 'description' ) )
                ->skip( $args['offset'] )
                ->take( $args['number'] )
                ->get()
                ->toArray()
            );
        }


        wp_cache_set( $cache_key, $holidays, 'wp-erp' );
    }

    return $holidays;
}

function erp_hr_holiday_search( $args ) {

    $args_s = isset( $args['s'] ) ? $args['s'] : '';

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();

    $holiday_results = $holiday->select( array( 'id', 'title', 'start', 'end', 'description' ) );

    if ( $args_s && ! empty( $args['s'] ) ) {
        $holiday_results = $holiday_results->where( 'title', 'LIKE',  "%$args_s%" );
    }

    if ( isset( $args['from'] ) && ! empty( $args['from'] ) ) {
        $holiday_results = $holiday_results->Where( 'start', '>=', $args['from'] );
    }

    if ( isset( $args['to'] ) && ! empty( $args['to'] ) ) {
        $holiday_results = $holiday_results->Where( 'end', '<=', $args['to'] );
    }

    if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
        $holiday_results = $holiday_results->orWhere( 'description', 'LIKE',  "%$args_s%" );
    }


    $holidays = erp_array_to_object(
        $holiday_results->skip( $args['offset'] )
                        ->take( $args['number'] )
                        ->get()
                        ->toArray()
    );

    return $holidays;
}

/**
 * count total holidays
 *
 * @return \stdClass
 */
function erp_hr_count_holidays( $args ) {


    $args_s = isset( $args['s'] ) ? $args['s'] : '';

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();

    if ( $args_s && ! empty( $args['s'] ) ) {
        $holiday = $holiday->where( 'title', 'LIKE',  "%$args_s%" );
    }

    if ( isset( $args['from'] ) && ! empty( $args['from'] ) ) {
        $$holiday = $holiday->Where( 'start', '>=', $args['from'] );
    }

    if ( isset( $args['to'] ) && ! empty( $args['to'] ) ) {
        $holiday = $holiday->Where( 'end', '<=', $args['to'] );
    }

    if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
        $holiday = $holiday->orWhere( 'description', 'LIKE',  "%$args_s%" );
    }

    return $holiday->count();
}

/**
 * Remove holiday
 *
 * @return \stdClass
 */
function erp_hr_delete_holidays( $holidays_id ) {

    if ( is_array( $holidays_id ) ) {

        foreach ( $holidays_id as $key => $holiday_id ) {
            do_action( 'erp_hr_leave_holiday_delete', $holiday_id );
        }

        \WeDevs\ERP\HRM\Models\Leave_Holiday::destroy( $holidays_id );

    } else {
        do_action( 'erp_hr_leave_holiday_delete', $holidays_id );

        return \WeDevs\ERP\HRM\Models\Leave_Holiday::find( $holidays_id )->delete();
    }
}

/**
 * Get policies as formatted for dropdown
 *
 * @return array
 */
function erp_hr_leave_get_policies_dropdown_raw() {
    $policies = erp_hr_leave_get_policies();
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

    do_action( 'erp_hr_leave_policy_delete', $policy_id );

    return \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy_id )->delete();
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
            'days'       => count( $leaves ),
            'start_date' => $start_date,
            'end_date'   => $end_date,
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
        'user_id'   => 0,
        'policy_id' => 0,
        'status'    => 1,
        'year'      => date( 'Y' ),
        'number'    => 20,
        'offset'    => 0,
        'orderby'   => 'created_on',
        'order'     => 'DESC',
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
            $where .= " `status` IN(" . implode( ",", array_map( 'intval', $args['status'] ) ) . ") ";
        } else {
            $where .= " `status` = " . intval( $args['status'] ) . " ";
        }
    }

    if ( $args['user_id'] != '0' ) {

        if ( empty( $where ) ) {
            $where .= " WHERE req.user_id = " . intval( $args['user_id'] );
        } else {
            $where .= " AND req.user_id = " . intval( $args['user_id'] );
        }
    }

    if ( $args['policy_id'] ) {

        if ( empty( $where ) ) {
            $where .= " WHERE req.policy_id = " . intval( $args['policy_id'] );
        } else {
            $where .= " AND req.policy_id = " . intval( $args['policy_id'] );
        }
    }

    if ( ! empty( $args['year'] ) ) {
        $from_date = $args['year'] . '-01-01';
        $to_date   = $args['year'] . '-12-31';

        if ( empty( $where ) ) {
            $where .= " WHERE req.start_date >= date('$from_date') AND req.start_date <= date('$to_date')";
        } else {
            $where .= " AND req.start_date >= date('$from_date') AND req.start_date <= date('$to_date')";
        }
    }

    $cache_key = 'erp_hr_leave_requests_' . md5( serialize( $args ) );
    $requests  = wp_cache_get( $cache_key, 'wp-erp' );

    $sql = "SELECT req.id, req.user_id, u.display_name, req.policy_id, pol.name as policy_name, req.status, req.reason, req.comments, req.created_on, req.days, req.start_date, req.end_date
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
function erp_hr_leave_get_entitlements( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'employee_id' => 0,
        'policy_id'   => 0,
        'year'        => date( 'Y' ),
        'number'      => 20,
        'offset'      => 0,
        'orderby'     => 'en.user_id, en.created_on',
        'order'       => 'DESC',
        'debug'       => false
    );

    $args  = wp_parse_args( $args, $defaults );
    $where = 'WHERE 1 = 1';

    if ( ! empty( $args['year'] ) ) {
        $from_date = $args['year'] . '-01-01';
        $to_date   = $args['year'] . '-12-31';

        $where .= " AND en.from_date >= date('$from_date') AND en.to_date <= date('$to_date')";
    }

    if ( $args['employee_id'] ) {
        $where .= " AND en.user_id = " . intval( $args['employee_id'] );
    }

    if ( $args['policy_id'] ) {
        $where .= " AND en.policy_id = " . intval( $args['policy_id'] );
    }

    $query = "SELECT en.*, u.display_name as employee_name, pol.name as policy_name
        FROM `{$wpdb->prefix}erp_hr_leave_entitlements` AS en
        LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS pol ON pol.id = en.policy_id
        LEFT JOIN {$wpdb->users} AS u ON en.user_id = u.ID
        $where
        ORDER BY {$args['orderby']} {$args['order']}
        LIMIT %d,%d;";

    $sql     = $wpdb->prepare( $query, absint( $args['offset'] ), absint( $args['number'] ) );
    $results = $wpdb->get_results( $sql );

    if ( $args['debug'] ) {
        printf( '<pre>%s</pre>', print_r( $sql, true ) );
    }

    return $results;
}

function erp_hr_leave_get_balance( $user_id ) {
    global $wpdb;

    $query = "SELECT req.id, req.days, req.policy_id, req.start_date, en.days as entitlement
        FROM {$wpdb->prefix}erp_hr_leave_requests AS req
        LEFT JOIN {$wpdb->prefix}erp_hr_leave_entitlements AS en ON ( ( en.policy_id = req.policy_id ) AND (en.user_id = req.user_id ) )
        WHERE req.status = 1 and req.user_id = %d";

    $sql     = $wpdb->prepare( $query, $user_id );
    $results = $wpdb->get_results( $sql );

    $temp         = array();
    $balance      = array();
    $current_time = current_time( 'timestamp' );

    if ( $results ) {
        // group by policy
        foreach ($results as $request) {
            $temp[ $request->policy_id ][] = $request;
        }

        // calculate each policy
        foreach ($temp as $policy_id => $requests) {
            $balance[$policy_id] = array(
                'policy_id'   => $policy_id,
                'scheduled'   => 0,
                'entitlement' => 0,
                'total'       => 0,
                'available'   => 0
            );

            foreach ($requests as $request) {
                $balance[$policy_id]['entitlement'] = (int) $request->entitlement;
                $balance[$policy_id]['total']       += $request->days;

                if ( $current_time < strtotime( $request->start_date ) ) {
                    $balance[$policy_id]['scheduled'] += $request->days;
                }
            }
        }

        // calculate available
        foreach ($balance as &$policy) {
            $policy['available'] = $policy['entitlement'] - $policy['total'];
        }

        return $balance;
    }

    return false;
}
