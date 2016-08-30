<?php

/**
 * Get holiday between two date
 *
 * @since  0.1
 *
 * @param  date  $start_date
 * @param  date  $end_date
 *
 * @return array
 */
function erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date ) {
    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();

    $holiday = $holiday->where( function( $condition ) use( $start_date ) {
        $condition->where( 'start', '<=', $start_date );
        $condition->where( 'end', '>=', $start_date );
    } );

    $holiday = $holiday->orWhere( function( $condition ) use( $end_date ) {
        $condition->where( 'start', '<=', $end_date );
        $condition->where( 'end', '>=', $end_date );
    } );


    $holiday = $holiday->orWhere( function( $condition ) use( $start_date, $end_date ) {
        $condition->where( 'start', '>=', $start_date );
        $condition->where( 'start', '<=', $end_date );
    } );

    $holiday = $holiday->orWhere( function( $condition ) use( $start_date, $end_date ) {
        $condition->where( 'end', '>=', $start_date );
        $condition->where( 'end', '<=', $end_date );
    } );

    $results = $holiday->get()->toArray();

    $holiday_extrat    = [];
    $given_date_extrat = erp_extract_dates( $start_date, $end_date );

    foreach ( $results as $result ) {
        $date_extrat    = erp_extract_dates( $result['start'], $result['end'] );
        $holiday_extrat = array_merge( $holiday_extrat, $date_extrat );
    }

    $extract = array_intersect( $given_date_extrat, $holiday_extrat );
    return $extract;
}

/**
 * Checking is user take leave within date rang in before
 *
 * @since  0.1
 *
 * @param  string $start_date
 * @param  string $end_date
 * @param  int $user_id
 *
 * @return boolean
 */
function erp_hrm_is_leave_recored_exist_between_date( $start_date, $end_date, $user_id  ) {

    $start_date = date( 'Y-m-d', strtotime( $start_date ) );
    $end_date   = date( 'Y-m-d', strtotime( $end_date ) );

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_request();

    $holiday->where( 'user_id', '=', $user_id );

    $holiday = $holiday->where( function( $condition ) use( $start_date, $user_id ) {
        $condition->where( 'start_date', '<=', $start_date );
        $condition->where( 'end_date', '>=', $start_date );
        $condition->whereIn( 'status', [1, 2] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function( $condition ) use( $end_date, $user_id ) {
        $condition->where( 'start_date', '<=', $end_date );
        $condition->where( 'end_date', '>=', $end_date );
        $condition->whereIn( 'status', [1, 2] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function( $condition ) use( $start_date, $end_date, $user_id ) {
        $condition->where( 'start_date', '>=', $start_date );
        $condition->where( 'start_date', '<=', $end_date );
        $condition->whereIn( 'status', [1, 2] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function( $condition ) use( $start_date, $end_date, $user_id ) {
        $condition->where( 'end_date', '>=', $start_date );
        $condition->where( 'end_date', '<=', $end_date );
        $condition->whereIn( 'status', [1, 2] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $results = $holiday->get()->toArray();

    $holiday_extrat    = [];
    $given_date_extrat = erp_extract_dates( $start_date, $end_date );

    foreach ( $results as $result ) {
        $date_extrat    = erp_extract_dates( $result['start_date'], $result['end_date'] );
        $holiday_extrat = array_merge( $holiday_extrat, $date_extrat );
    }

    $extract = array_intersect( $given_date_extrat, $holiday_extrat );

    return $extract;
}

/**
 * Check leave duration exist or not the plicy days
 *
 * @since  0.1
 *
 * @param  string $start_date
 * @param  string $end_date
 * @param  int $policy_id
 * @param  int $user_id
 *
 * @return boolean
 */
function erp_hrm_is_valid_leave_duration( $start_date, $end_date, $policy_id, $user_id ) {

    if ( ! $user_id || ! $policy_id ) {
        return true;
    }

    $financial_start_date = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    $financial_end_date   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

    $user_request = new \WeDevs\ERP\HRM\Models\Leave_request();
    $policy       = new \WeDevs\ERP\HRM\Models\Leave_Policies();

    $user_request = $user_request->where( function( $condition ) use( $financial_start_date, $financial_end_date, $user_id, $policy_id ) {
        //$start_date = date( 'Y-m-d', strtotime( $start_date ) );
       // $end_date   = date( 'Y-m-d', strtotime( $end_date ) );

        $condition->where( 'start_date', '>=', $financial_start_date );
        $condition->where( 'end_date', '<=', $financial_end_date );
        $condition->where( 'user_id', '=', $user_id );
        $condition->where( 'policy_id', '=', $policy_id );
        $condition->where( 'status', '!=', 3 );
    } );

    $user_enti_count = $user_request->sum( 'days' );
    $policy_count    = $policy->where( 'id', '=', $policy_id )->pluck('value');
    $working_day     = erp_hr_get_work_days_without_off_day( $start_date, $end_date );//erp_hr_get_work_days_between_dates( $start_date, $end_date );erp_hr_get_work_days_without_holiday
    $apply_days      = $working_day['total'] + $user_enti_count;

    if ( $apply_days >  $policy_count ) {
        return false;
    }

    return true;
}

/**
 * Leave request time checking the apply date duration with the financial date duration
 *
 * @since  0.1
 *
 * @param  string $start_date
 * @param  string $end_date
 *
 * @return boolean
 */
function erp_hrm_is_valid_leave_date_range_within_financial_date_range( $start_date, $end_date ) {

    $financial_start_date = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    $financial_end_date   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );
    $apply_start_date     = date( 'Y-m-d', strtotime( $start_date ) );
    $apply_end_date       = date( 'Y-m-d', strtotime( $end_date ) );

    if ( $financial_start_date > $apply_start_date ||  $apply_start_date > $financial_end_date ) {
        return false;
    }

    if ( $financial_start_date > $apply_end_date ||  $apply_end_date > $financial_end_date ) {
        return false;
    }

    return true;
}

/**
 * Insert a new leave policy
 *
 * @since 0.1
 *
 * @param array $args
 *
 * @return integer [$policy_id]
 */
function erp_hr_leave_insert_policy( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'         => null,
        'name'       => '',
        'value'      => 0,
        'color'      => '',
        'description'    => '',
    );

    $args = wp_parse_args( $args, $defaults );

    // some validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No name provided.', 'erp' ) );
    }

    if ( ! intval( $args['value'] ) ) {
        return new WP_Error( 'no-value', __( 'No duration provided.', 'erp' ) );
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
        do_action( 'erp_hr_leave_before_policy_updated', $policy_id, $args );

        if ( $leave_policies->find( $policy_id )->update( $args ) ) {
            do_action( 'erp_hr_leave_after_policy_updated', $policy_id, $args );
            return $policy_id;
        }
    }
}

/**
 * Insert a leave holiday
 *
 * @since 0.1
 *
 * @param array $args
 *
 * @return integer [$holiday_id]
 */
function erp_hr_leave_insert_holiday( $args = array() ) {

    $defaults = array(
        'id'          => null,
        'title'       => '',
        'start'       => current_time('mysql'),
        'end'         => '',
        'description' => '',
    );

    $args = wp_parse_args( $args, $defaults );

    // some validation
    if ( empty( $args['title'] ) ) {
        return new WP_Error( 'no-name', __( 'No title provided.', 'erp' ) );
    }

    if ( empty( $args['start'] ) ) {
        return new WP_Error( 'no-value', __( 'No start date provided.', 'erp' ) );
    }

    if ( empty( $args['end'] ) ) {
        return new WP_Error( 'no-value', __( 'No end date provided.', 'erp' ) );
    }

    $args['title'] = sanitize_text_field( $args['title'] );

    $holiday_id = (int) $args['id'];
    unset( $args['id'] );

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();

    if ( ! $holiday_id ) {
        // insert a new
        $leave_policy = $holiday->create( $args );

        if ( $leave_policy ) {
            do_action( 'erp_hr_new_holiday', $leave_policy->insert_id, $args );
            return $leave_policy->id;
        }

    } else {
        do_action( 'erp_hr_before_update_holiday', $holiday_id, $args );

        if ( $holiday->find( $holiday_id )->update( $args ) ) {
            do_action( 'erp_hr_after_update_holiday', $holiday_id, $args );
            return $holiday_id;
        }
    }
}

/**
 * Get all leave policies with different condition
 *
 * @since 0.1
 *
 * @param array $args
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
    $policies = wp_cache_get( $cache_key, 'erp' );

    if ( false === $policies ) {

        $policies = erp_array_to_object(
            \WeDevs\ERP\HRM\Models\Leave_Policies::select( array( 'id', 'name', 'value', 'color', 'department', 'designation', 'gender', 'marital', 'activate', 'execute_day', 'effective_date', 'location', 'description' ) )
            ->skip( $args['offset'] )
            ->take( $args['number'] )
            ->orderBy( $args['orderby'], $args['order'] )
            ->get()
            ->toArray()
        );

        wp_cache_set( $cache_key, $policies, 'erp' );
    }

    return $policies;
}

/**
 * Fetch a leave policy by policy id
 *
 * @since 0.1
 *
 * @param integer $policy_id
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
 * @since 0.1
 *
 * @return integer
 */
function erp_hr_count_leave_policies() {
    return \WeDevs\ERP\HRM\Models\Leave_Policies::count();
}

/**
 * Fetch all holidays by company
 *
 * @since 0.1
 *
 * @param array $args
 *
 * @return array
 */
function erp_hr_get_holidays( $args = [] ) {

    $defaults = array(
        'number' => 20,
        'offset' => 0,
    );

    $args  = wp_parse_args( $args, $defaults );

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();

    $holiday_results = $holiday->select( array( 'id', 'title', 'start', 'end', 'description' ) );

    $holiday_results = erp_hr_holiday_filter_param( $holiday_results, $args );

    //if not search then execute nex code
    if ( isset( $args['id'] ) && ! empty( $args['id'] ) ) {
        $id = intval( $args['id'] );
        $holiday_results = $holiday_results->where( 'id', '=',  "$id" );
    }

    $cache_key = 'erp-get-holidays-' . md5( serialize( $args ) );
    $holidays = wp_cache_get( $cache_key, 'erp' );

    if ( false === $holidays ) {

        if ( $args['number'] == '-1' ) {
            $holidays = erp_array_to_object( $holiday_results->get()->toArray() );
        } else {
            $holidays = erp_array_to_object(
                $holiday_results->skip( $args['offset'] )
                                ->take( $args['number'] )
                                ->orderBy( $args['orderby'], $args['order'] )
                                ->get()
                                ->toArray()
            );
        }

        wp_cache_set( $cache_key, $holidays, 'erp' );
    }

    return $holidays;
}

/**
 * Count total holidays
 *
 * @since 0.1
 *
 * @return \stdClass
 */
function erp_hr_count_holidays( $args ) {

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();
    $holiday = erp_hr_holiday_filter_param( $holiday, $args );

    return $holiday->count();
}

/**
 * Filter parameter for holidays
 *
 * @since 0.1
 *
 * @param  object $holiday
 * @param  array $args
 *
 * @return object
 */
function erp_hr_holiday_filter_param( $holiday, $args ) {

    $args_s = isset( $args['s'] ) ? $args['s'] : '';

    if ( $args_s && ! empty( $args['s'] ) ) {
        $holiday = $holiday->where( 'title', 'LIKE',  "%$args_s%" );
    }

    if ( isset( $args['from'] ) && ! empty( $args['from'] ) ) {
        $holiday = $holiday->where( 'start', '>=', $args['from'] );
    }

    if ( isset( $args['to'] ) && ! empty( $args['to'] ) ) {
        $holiday = $holiday->where( 'end', '<=', $args['to'] );
    }

    if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
        $holiday = $holiday->orWhere( 'description', 'LIKE',  "%$args_s%" );
    }

    return $holiday;
}

/**
 * Remove holidays
 *
 * @since 0.1
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
 * @since 0.1
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
 * @since 0.1
 *
 * @param  integer  policy_id
 *
 * @return boolean
 */
function erp_hr_leave_policy_delete( $policy_id ) {

    $existing_req = \WeDevs\ERP\HRM\Models\Leave_request::where( 'policy_id', $policy_id )->count();

    //if any existing req found under given policy then Do not delete
    if ( $existing_req ) {
        return;
    }

    if ( is_array( $policy_id ) ) {
        foreach ( $policy_id as $key => $id ) {
            do_action( 'erp_hr_leave_policy_delete', $id );
        }
        return \WeDevs\ERP\HRM\Models\Leave_Policies::destroy( $policy_id );
    }

    do_action( 'erp_hr_leave_policy_delete', $policy_id );

    return \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy_id )->delete();
}

/**
 * Insert a new policy entitlement for an employee
 *
 * @since 0.1
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
        return new WP_Error( 'no-user', __( 'No employee provided.', 'erp' ) );
    }

    if ( ! intval( $fields['policy_id'] ) ) {
        return new WP_Error( 'no-policy', __( 'No policy provided.', 'erp' ) );
    }

    if ( empty( $fields['from_date'] ) || empty( $fields['to_date'] ) ) {
        return new WP_Error( 'no-date', __( 'No date provided.', 'erp' ) );
    }

    $entitlement = new \WeDevs\ERP\HRM\Models\Leave_Entitlement();
    $user_id     = intval( $fields['user_id'] );
    $policy_id   = intval( $fields['policy_id'] );

    $entitlement = $entitlement->where( function( $condition ) use( $user_id, $policy_id, $fields ) {
        $financial_start_date = $fields['from_date'] ? $fields['from_date'] : erp_financial_start_date();
        $financial_end_date   = $fields['to_date'] ? $fields['to_date'] :  erp_financial_end_date();
        $condition->where( 'from_date', '>=', $financial_start_date );
        $condition->where( 'to_date', '<=', $financial_end_date );
        $condition->where( 'user_id', '=', $user_id );
        $condition->where( 'policy_id', '=', $policy_id );
    } );

    $results = $entitlement->get()->toArray();

    if ( $results ) {
        $entitlement = reset( $results );
        return $entitlement['id'];
    }

    $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_entitlements', $fields );

    do_action( 'erp_hr_leave_insert_new_entitlement', $wpdb->insert_id, $fields );

    return $wpdb->insert_id;
}

/**
 * Get assign policies according to employee entitlement
 *
 * @since 0.1
 *
 * @param  integer $employee_id
 *
 * @return boolean|array
 */
function erp_hr_get_assign_policy_from_entitlement( $employee_id ) {

    global $wpdb;

    $data      = [];
    $dropdown  = [];
    $policy    = new \WeDevs\ERP\HRM\Models\Leave_Policies();
    $en        = new \WeDevs\ERP\HRM\Models\Leave_Entitlement();
    $policy_tb = $wpdb->prefix . 'erp_hr_leave_policies';
    $en_tb     = $wpdb->prefix . 'erp_hr_leave_entitlements';

    $financial_start_date = erp_financial_start_date();
    $financial_end_date   = erp_financial_end_date();

    $policies = \WeDevs\ERP\HRM\Models\Leave_Policies::select( $policy_tb .'.name', $policy_tb.'.id' )
           ->leftjoin( $en_tb, $en_tb.'.policy_id', '=', $policy_tb.'.id' )
           ->where( $en_tb.'.user_id', $employee_id )
           ->where( 'from_date', '>=', $financial_start_date )
           ->where( 'to_date', '<=', $financial_end_date )
           ->distinct()
           ->get()
           ->toArray();

    if ( !empty( $policies ) ) {
        foreach ( $policies as $policy ) {
            $dropdown[ $policy['id'] ] = stripslashes( $policy['name'] );
        }
        return $dropdown;
    }

    return false;
}

/**
 * Add a new leave request
 *
 * @since 0.1
 *
 * @param  array $args
 *
 * @return integet request_id
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
        return new WP_Error( 'no-employee', __( 'No employee ID provided.', 'erp' ) );
    }

    if ( ! intval( $leave_policy ) ) {
        return new WP_Error( 'no-policy', __( 'No leave policy provided.', 'erp' ) );
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

        $request = apply_filters( 'erp_hr_leave_new_args', [
            'user_id'    => $user_id,
            'policy_id'  => $leave_policy,
            'days'       => count( $leaves ),
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'reason'     => wp_kses_post( $reason ),
            'status'     => 2, // default is pending
            'created_by' => get_current_user_id(),
            'created_on' => current_time( 'mysql' ),
        ] );

        if ( $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_requests', $request ) ) {
            $request_id = $wpdb->insert_id;

            foreach ( $leaves as $leave ) {
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
 * Fetch a single request
 *
 * @param  int  $request_id
 *
 * @return object
 */
function erp_hr_get_leave_request( $request_id ) {
    global $wpdb;

    $sql = "SELECT req.id, req.user_id, u.display_name, req.policy_id, pol.name as policy_name, req.status, req.reason, req.comments, req.created_on, req.days, req.start_date, req.end_date
        FROM {$wpdb->prefix}erp_hr_leave_requests AS req
        LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS pol ON pol.id = req.policy_id
        LEFT JOIN $wpdb->users AS u ON req.user_id = u.ID
        WHERE req.id = %d";

    $row = $wpdb->get_row( $wpdb->prepare( $sql, $request_id ) );

    return $row;
}

/**
 * Fetch the leave requests
 *
 * @since 0.1
 *
 * @param  array   $args
 *
 * @return array
 */
function erp_hr_get_leave_requests( $args = array() ) {
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
    $requests  = wp_cache_get( $cache_key, 'erp' );
    $limit     = $args['number'] == '-1' ? '' : 'LIMIT %d, %d';

    $sql = "SELECT req.id, req.user_id, u.display_name, req.policy_id, pol.name as policy_name, req.status, req.reason, req.comments, req.created_on, req.days, req.start_date, req.end_date
        FROM {$wpdb->prefix}erp_hr_leave_requests AS req
        LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS pol ON pol.id = req.policy_id
        LEFT JOIN $wpdb->users AS u ON req.user_id = u.ID
        $where
        ORDER BY {$args['orderby']} {$args['order']}
        $limit";

    if ( $requests === false ) {
        if ( $args['number'] == '-1' ) {
            $requests = $wpdb->get_results( $sql );
        } else {
            $requests = $wpdb->get_results( $wpdb->prepare( $sql, absint( $args['offset'] ), absint( $args['number'] ) ) );
        }
        wp_cache_set( $cache_key, $requests, 'erp', HOUR_IN_SECONDS );
    }

    return $requests;
}

/**
 * Get leave requests count
 *
 * @since 0.1
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
    $results = wp_cache_get( $cache_key, 'erp' );

    if ( false === $results ) {
        $sql     = "SELECT status, COUNT(id) as num FROM {$wpdb->prefix}erp_hr_leave_requests WHERE status != 0 GROUP BY status;";
        $results = $wpdb->get_results( $sql );

        wp_cache_set( $cache_key, $results, 'erp' );
    }

    foreach ($results as $row) {
        if ( array_key_exists( $row->status, $counts ) ) {
            $counts[ $row->status ]['count'] = (int) $row->num;
        }

        $counts['all']['count'] += (int) $row->num;
    }

    return $counts;
}

/**
 * Update leave request status
 *
 * @since 0.1
 *
 * @param  integer $request_id
 * @param  string $status
 *
 * @return array
 */
function erp_hr_leave_request_update_status( $request_id, $status ) {
    global $wpdb;

    $updated = $wpdb->update( $wpdb->prefix . 'erp_hr_leave_requests',
        [ 'status' => $status ],
        [ 'id' => $request_id ]
    );

    $status = ( $status == 1 ) ? 'approved' : 'pending';

    do_action( "erp_hr_leave_request_{$status}", $request_id );

    return $updated;
}

/**
 * Get leave requests status
 *
 * @since 0.1
 *
 * @param  int|boolean  $status
 *
 * @return array|string
 */
function erp_hr_leave_request_get_statuses( $status = false ) {
    $statuses = array(
        'all' => __( 'All', 'erp' ),
        '1'   => __( 'Approved', 'erp' ),
        '2'   => __( 'Pending', 'erp' ),
        '3'   => __( 'Rejected', 'erp' )
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
 * @since 0.1
 *
 * @param  integer  $employee_id
 * @param  integer  $policy_id
 * @param  integer  $year
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
 * @since 0.1
 *
 * @param  integer  $year
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
        $from_date = date( $args['year'].'-m-d H:i:s',  strtotime( erp_financial_start_date() ) );
        $to_date   = date( $args['year'].'-m-d H:i:s',  strtotime( erp_financial_end_date() ) );
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

/**
 * Count leave entitlement
 *
 * @since 0.1
 *
 * @param  array  $args
 *
 * @return integer
 */
function erp_hr_leave_count_entitlements( $args = array() ) {
    $defaults = array(
        'year'        => date( 'Y' ),
    );

    $args  = wp_parse_args( $args, $defaults );

    $from_date = erp_financial_start_date();
    $to_date   = erp_financial_end_date();

    return \WeDevs\ERP\HRM\Models\Leave_Entitlement::where('from_date', '>=', $from_date )
            ->where( 'to_date', '<=', $to_date )
            ->count();
}

/**
 * Delete entitlement with leave request
 *
 * @since 0.1
 *
 * @param  integer $id
 * @param  integer $user_id
 * @param  integer $policy_id
 *
 * @return void
 */
function erp_hr_delete_entitlement( $id, $user_id, $policy_id ) {
    $leave_recored = \WeDevs\ERP\HRM\Models\Leave_request::where( 'user_id', '=', $user_id )
                ->where( 'policy_id', '=', $policy_id )->get()->toArray();
    $leave_recored = wp_list_pluck( $leave_recored, 'status' );

    if ( in_array( '1', $leave_recored ) ) {
        return;
    }

    if ( \WeDevs\ERP\HRM\Models\Leave_Entitlement::find( $id )->delete() ) {
        return \WeDevs\ERP\HRM\Models\Leave_request::where( 'user_id', '=', $user_id )
                ->where( 'policy_id', '=', $policy_id )
                ->delete();
    }
}

/**
 * Erp get leave balance
 *
 * @since 0.1
 *
 * @param  integer $user_id
 *
 * @return float|boolean
 */
function erp_hr_leave_get_balance( $user_id ) {
    global $wpdb;

    $financial_start_date = erp_financial_start_date();
    $financial_end_date   = erp_financial_end_date();

    $query = "SELECT req.id, req.days, req.policy_id, req.start_date, en.days as entitlement
        FROM {$wpdb->prefix}erp_hr_leave_requests AS req
        LEFT JOIN {$wpdb->prefix}erp_hr_leave_entitlements AS en ON ( ( en.policy_id = req.policy_id ) AND (en.user_id = req.user_id ) )
        WHERE req.status = 1 and req.user_id = %d and ( en.`from_date`<= '$financial_start_date' AND en.`to_date` >= '$financial_end_date' )";

    $sql     = $wpdb->prepare( $query, $user_id );
    $results = $wpdb->get_results( $sql );

    $temp         = [];
    $balance      = [];
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
            $available = $policy['entitlement'] - $policy['total'];
            $policy['available'] = $available < 0 ? 0 : $available;
        }

        return $balance;
    }

    return $balance;
}

/**
 * After create employee apply leave policy, you can also create leave policy by passing employee id.
 *
 * @since 0.1
 *
 * @param  int $employee
 * @param  array $policies
 *
 * @return void
 */
function erp_hr_apply_new_employee_policy( $employee = false, $policies = false ) {

    if ( is_int( $employee ) ) {
        $user_id  = intval( $employee );
        $employee_obj = new \WeDevs\ERP\HRM\Employee( $user_id );
        $employee     = $employee_obj->to_array();

    } else {
        $user_id  = intval( $employee['id'] );
    }

    $department  = isset( $employee['work']['department'] ) ? $employee['work']['department'] : '';
    $designation = isset( $employee['work']['designation'] ) ? $employee['work']['designation'] : '';
    $gender      = isset( $employee['personal']['gender'] ) ? $employee['personal']['gender'] : '';
    $location    = isset( $employee['work']['location'] ) ? $employee['work']['location'] : '';
    $marital     = isset( $employee['personal']['marital_status'] ) ? $employee['personal']['marital_status'] : '';
    $today       = date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) );

    if ( ! $policies ) {
        $policies    = \WeDevs\ERP\HRM\Models\Leave_Policies::where( 'activate', '1' )->get()->toArray();
    } else {
        $policies    = array( $policies );
    }

    $selected_policy = [];

    foreach ( $policies as $key => $policy ) {

        $effective_date = date( 'Y-m-d', strtotime( $policy['effective_date'] ) );

        if ( strtotime( $effective_date ) < 0 || $today < $effective_date ) {
            continue;
        }

        if ( $policy['department'] != '-1' && $policy['department'] != $department ) {
            continue;
        }

        if ( $policy['designation'] != '-1' && $policy['designation'] != $designation ) {
            continue;
        }

        if ( $policy['gender'] != '-1' && $policy['gender'] != $gender ) {
            continue;
        }

        if ( $policy['location'] != '-1' && $policy['location'] != $location ) {
            continue;
        }

        if ( $policy['marital'] != '-1' && $policy['marital'] != $marital ) {
            continue;
        }

        $selected_policy[] = $policy;
    }

    foreach ( $selected_policy as $key => $leave_policy ) {
        erp_hr_apply_leave_policy( $user_id, $leave_policy );
    }
}

/**
 * Assign entitlement
 *
 * @since 0.1
 *
 * @param  int $user_id
 * @param  array $leave_policy
 *
 * @return void
 */
function erp_hr_apply_leave_policy( $user_id, $leave_policy ) {

    $policy = array(
        'user_id'    => $user_id,
        'policy_id'  => $leave_policy['id'],
        'days'       => $leave_policy['value'],
        'from_date'  => erp_financial_start_date(),
        'to_date'    => erp_financial_end_date(), // @TODO -- Analysis remaining
        'comments'   => $leave_policy['description']
    );

    erp_hr_leave_insert_entitlement( $policy );
}

/**
 * Assign for schedule leave policy
 *
 * @since 0.1
 *
 * @return void
 */
function erp_hr_apply_policy_schedule() {

    $active_employes = \WeDevs\ERP\HRM\Models\Employee::select('user_id')->where( 'status', 'active' )->get()->toArray();
    $policies        = \WeDevs\ERP\HRM\Models\Leave_Policies::get()->toArray();
    $selected_policy = [];
    $today           = date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) );

    foreach ( $active_employes as $key => $employee ) {

        $employee_obj  = new \WeDevs\ERP\HRM\Employee( intval( $employee['user_id'] ) );
        $employee_data = $employee_obj->to_array();
        $department    = isset( $employee_data['work']['department'] ) ? $employee_data['work']['department'] : '';
        $designation   = isset( $employee_data['work']['designation'] ) ? $employee_data['work']['designation'] : '';
        $gender        = isset( $employee_data['personal']['gender'] ) ? $employee_data['personal']['gender'] : '';
        $location      = isset( $employee_data['work']['location'] ) ? $employee_data['work']['location'] : '';
        $marital       = isset( $employee_data['personal']['marital_status'] ) ? $employee_data['personal']['marital_status'] : '';
        $hire_date     = isset( $employee_data['work']['hiring_date'] ) ? $employee_data['work']['hiring_date'] : '';
        $current_time  = current_time( 'mysql' );
        $daydiff       = count( erp_extract_dates( $hire_date, $current_time ) ) - 1;

        foreach ( $policies as $key => $policy ) {
            if ( $policy['activate'] == 1 ) {
                erp_hr_apply_new_employee_policy( $employee_data, $policy );
                continue;
            }
            $effective_date = date( 'Y-m-d', strtotime( $policy['effective_date'] ) );

            if ( strtotime( $effective_date ) < 0 && $today < $effective_date ) {
                continue;
            }

            if ( $daydiff <= $policy['execute_day'] ) {
                continue;
            }

            if ( $policy['department'] != '-1' && $policy['department'] != $department) {
                continue;
            }

            if ( $policy['designation'] != '-1' && $policy['designation'] != $designation ) {
                continue;
            }

            if ( $policy['gender'] != '-1' && $policy['gender'] != $gender ) {
                continue;
            }

            if ( $policy['location'] != '-1' && $policy['location'] != $location ) {
                continue;
            }

            if ( $policy['marital'] != '-1' && $policy['marital'] != $marital ) {
                continue;
            }

            erp_hr_apply_leave_policy( intval( $employee['user_id'] ), $policy );
        }
    }

}

/**
 * Apply policy in existing employee
 *
 * @since 0.1
 *
 * @param  integer $policy_id
 * @param  array $args
 *
 * @return void
 */
function erp_hr_apply_policy_existing_employee( $policy_id, $args ) {

    if ( $args['instant_apply'] !== true ) {
        return;
    }

    if ( $args['activate'] == 2 ) {
       erp_hr_apply_policy_schedule();
    }

    if ( $args['activate'] == 1 ) {
        $active_employes = \WeDevs\ERP\HRM\Models\Employee::select('user_id')->where( 'status', 'active' )->get()->toArray();
        foreach ( $active_employes as $key => $employee ) {
            erp_hr_apply_new_employee_policy( intval( $employee['user_id'] ) );
        }
    }
}

/**
 * Get cuurent month approve leave request list
 *
 * @since 0.1
 *
 * @return array
 */
function erp_hr_get_current_month_leave_list() {
    $db = new \WeDevs\ORM\Eloquent\Database();
    return erp_array_to_object( \WeDevs\ERP\HRM\Models\Leave_request::select('user_id', 'start_date', 'end_date' )
            ->where( $db->raw("DATE_FORMAT( `start_date`, '%m %Y' )" ), \Carbon\Carbon::today()->format('m Y') )
            ->where( $db->raw("DATE_FORMAT( `end_date`, '%d-%m-%Y' )" ), '>=', \Carbon\Carbon::today()->format('d-m-Y') )
            ->where('status', 1 )
            ->get()
            ->toArray() );
}

/**
 * Get newxt month leave request approved list
 *
 * @since 0.1
 *
 * @return array
 */
function erp_hr_get_next_month_leave_list() {
    $db = new \WeDevs\ORM\Eloquent\Database();
    return erp_array_to_object( \WeDevs\ERP\HRM\Models\Leave_request::select('user_id', 'start_date', 'end_date' )
            ->where( $db->raw("DATE_FORMAT( `start_date`, '%m %Y' )" ), \Carbon\Carbon::today()->addMonth(1)->format('m Y') )
            ->where('status', 1 )
            ->get()
            ->toArray());
}


/**
 * Leave period dropdown at entitlement create time
 *
 * @since 0.1
 *
 * @return void
 */
function erp_hr_leave_period() {

    $next_sart_date = date( 'Y-m-01 H:i:s', strtotime( '+1 year', strtotime( erp_financial_start_date() ) ) );
    $next_end_date  = date( 'Y-m-t H:i:s', strtotime( '+1 year', strtotime( erp_financial_end_date() ) ) );

    $date = [
        erp_financial_start_date() => erp_format_date( erp_financial_start_date() ) . ' - ' . erp_format_date( erp_financial_end_date() ),
        $next_sart_date            => erp_format_date( $next_sart_date ) . ' - ' . erp_format_date( $next_end_date )
    ];

    return $date;
}

/**
 * Apply entitlement yearly
 *
 * @since 0.1
 *
 * @return void
 */
function erp_hr_apply_entitlement_yearly() {

    $financial_start_date = erp_financial_start_date();
    $financial_end_date   = erp_financial_end_date();

    $before_financial_start_date = date( 'Y-m-01 H:i:s', strtotime( '-1 year', strtotime( $financial_start_date ) ) );
    $before_financial_end_date   = date( 'Y-m-t H:i:s', strtotime( '+11 month', strtotime( $before_financial_start_date ) ) );

    $entitlement = new \WeDevs\ERP\HRM\Models\Leave_Entitlement();

    $entitlement = $entitlement->where( function( $condition ) use( $before_financial_start_date, $before_financial_end_date ) {
        $condition->where( 'from_date', '>=', $before_financial_start_date );
        $condition->where( 'to_date', '<=', $before_financial_end_date );
    });

    $entitlements = $entitlement->get()->toArray();

    foreach ( $entitlements as $key => $entitlement ) {

        $policy = array(
            'user_id'    => $entitlement['user_id'],
            'policy_id'  => $entitlement['policy_id'],
            'days'       => $entitlement['days'],
            'from_date'  => erp_financial_start_date(),
            'to_date'    => erp_financial_end_date(),
            'comments'   => $entitlement['comments']
        );

        erp_hr_leave_insert_entitlement( $policy );
    }
}

/**
 * Get calendar leave events
 *
 * @param   array|boolean $get filter args
 * @param   int|boolean $user_id Get leaves for given user only
 * @param   boolean $approved_only Get leaves which are approved
 *
 * @since 0.1
 *
 * @return array
 */
function erp_hr_get_calendar_leave_events( $get = false, $user_id = false, $approved_only = false ) {

    global $wpdb;

    $employee_tb   = $wpdb->prefix . 'erp_hr_employees';
    $users_tb      = $wpdb->users;
    $request_tb    = $wpdb->prefix . 'erp_hr_leave_requests';
    $policy_tb     = $wpdb->prefix . 'erp_hr_leave_policies';

    $employee      = new \WeDevs\ERP\HRM\Models\Employee();
    $leave_request = new \WeDevs\ERP\HRM\Models\Leave_request();

    $department    = isset( $get['department'] ) && ! empty( $get['department'] ) && $get['department'] != '-1' ? intval( $get['department'] ) : false;
    $designation   = isset( $get['designation'] ) && ! empty( $get['designation'] ) && $get['designation'] != '-1' ? intval( $get['designation'] ) : false;

    if ( ! $get ) {
        $request = $leave_request->leftJoin( $users_tb, $request_tb . '.user_id', '=', $users_tb . '.ID' )
                ->leftJoin( $policy_tb, $request_tb . '.policy_id', '=', $policy_tb . '.id' )
                ->select( $users_tb . '.display_name', $request_tb . '.*', $policy_tb . '.color' );

        if ( $user_id ) {
            $request = $request->where( $request_tb . '.user_id', $user_id );
        }

        if ( $approved_only ) {
            $request = $request->where( $request_tb . '.status', 1 );
        }

        return  erp_array_to_object( $request->get()->toArray() );
    }

    if ( $department && $designation ) {
        $leave_requests = $leave_request->leftJoin( $employee_tb, $request_tb . '.user_id', '=', $employee_tb . '.user_id' )
            ->leftJoin( $users_tb, $request_tb . '.user_id', '=', $users_tb . '.ID' )
            ->leftJoin( $policy_tb, $request_tb . '.policy_id', '=', $policy_tb . '.id' )
            ->select( $users_tb . '.display_name', $request_tb . '.*', $policy_tb . '.color' )
            ->where( $employee_tb . '.designation', '=', $designation )
            ->where( $employee_tb . '.department', '=', $department );

        if ( $approved_only ) {
            $leave_requests = $leave_requests->where( $request_tb . '.status', 1 );
        }

        $leave_requests = erp_array_to_object( $leave_requests->get()->toArray() );

    } else if ( $designation ) {
        $leave_requests = $leave_request->leftJoin( $employee_tb, $request_tb . '.user_id', '=', $employee_tb . '.user_id' )
            ->leftJoin( $users_tb, $request_tb . '.user_id', '=', $users_tb . '.ID' )
            ->leftJoin( $policy_tb, $request_tb . '.policy_id', '=', $policy_tb . '.id' )
            ->select( $users_tb . '.display_name', $request_tb . '.*', $policy_tb . '.color' )
            ->where( $employee_tb . '.designation', '=', $designation );

        if ( $approved_only ) {
            $leave_requests = $leave_requests->where( $request_tb . '.status', 1 );
        }

        $leave_requests = erp_array_to_object( $leave_requests->get()->toArray() );

    } else if ( $department ) {
        $leave_requests = $leave_request->leftJoin( $employee_tb, $request_tb . '.user_id', '=', $employee_tb . '.user_id' )
            ->leftJoin( $users_tb, $request_tb . '.user_id', '=', $users_tb . '.ID' )
            ->leftJoin( $policy_tb, $request_tb . '.policy_id', '=', $policy_tb . '.id' )
            ->select( $users_tb . '.display_name', $request_tb . '.*', $policy_tb . '.color' )
            ->where( $employee_tb . '.department', '=', $department );

        if ( $approved_only ) {
            $leave_requests = $leave_requests->where( $request_tb . '.status', 1 );
        }

        $leave_requests = erp_array_to_object( $leave_requests->get()->toArray() );
    }

    return $leave_requests;
}
