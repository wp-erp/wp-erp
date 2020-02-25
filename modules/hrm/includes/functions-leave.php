<?php

/**
 * Get holiday between two date
 *
 * @since  0.1
 *
 * @param  date $start_date
 * @param  date $end_date
 *
 * @return array
 */
function erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date ) {
    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();

    $holiday = $holiday->where( function ( $condition ) use ( $start_date ) {
        $condition->where( 'start', '<=', $start_date );
        $condition->where( 'end', '>=', $start_date );
    } );

    $holiday = $holiday->orWhere( function ( $condition ) use ( $end_date ) {
        $condition->where( 'start', '<=', $end_date );
        $condition->where( 'end', '>=', $end_date );
    } );


    $holiday = $holiday->orWhere( function ( $condition ) use ( $start_date, $end_date ) {
        $condition->where( 'start', '>=', $start_date );
        $condition->where( 'start', '<=', $end_date );
    } );

    $holiday = $holiday->orWhere( function ( $condition ) use ( $start_date, $end_date ) {
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
function erp_hrm_is_leave_recored_exist_between_date( $start_date, $end_date, $user_id ) {

    $start_date = date( 'Y-m-d', strtotime( $start_date ) );
    $end_date   = date( 'Y-m-d', strtotime( $end_date ) );

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_request();

    $holiday->where( 'user_id', '=', $user_id );

    $holiday = $holiday->where( function ( $condition ) use ( $start_date, $user_id ) {
        $condition->where( 'start_date', '<=', $start_date );
        $condition->where( 'end_date', '>=', $start_date );
        $condition->whereIn( 'status', [ 1, 2 ] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function ( $condition ) use ( $end_date, $user_id ) {
        $condition->where( 'start_date', '<=', $end_date );
        $condition->where( 'end_date', '>=', $end_date );
        $condition->whereIn( 'status', [ 1, 2 ] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function ( $condition ) use ( $start_date, $end_date, $user_id ) {
        $condition->where( 'start_date', '>=', $start_date );
        $condition->where( 'start_date', '<=', $end_date );
        $condition->whereIn( 'status', [ 1, 2 ] );
        $condition->where( 'user_id', '=', $user_id );
    } );

    $holiday = $holiday->orWhere( function ( $condition ) use ( $start_date, $end_date, $user_id ) {
        $condition->where( 'end_date', '>=', $start_date );
        $condition->where( 'end_date', '<=', $end_date );
        $condition->whereIn( 'status', [ 1, 2 ] );
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

    $user_request = $user_request->where( function ( $condition ) use ( $financial_start_date, $financial_end_date, $user_id, $policy_id ) {
        //$start_date = date( 'Y-m-d', strtotime( $start_date ) );
        // $end_date   = date( 'Y-m-d', strtotime( $end_date ) );

        $condition->where( 'start_date', '>=', $financial_start_date );
        $condition->where( 'end_date', '<=', $financial_end_date );
        $condition->where( 'user_id', '=', $user_id );
        $condition->where( 'policy_id', '=', $policy_id );
        $condition->where( 'status', '!=', 3 );
    } );

    $user_enti_count = $user_request->sum( 'days' );
    $policy_count    = $policy->find( $policy_id )->toArray();
    $working_day     = erp_hr_get_work_days_without_off_day( $start_date, $end_date );//erp_hr_get_work_days_between_dates( $start_date, $end_date );erp_hr_get_work_days_without_holiday
    $apply_days      = $working_day['total'] + $user_enti_count;

    if ( $apply_days > $policy_count['value'] ) {
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

    if ( $financial_start_date > $apply_start_date || $apply_start_date > $financial_end_date ) {
        return false;
    }

    if ( $financial_start_date > $apply_end_date || $apply_end_date > $financial_end_date ) {
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
 * @return int $policy_id
 */
function erp_hr_leave_insert_policy( $args = array() ) {
    $defaults = array(
        'id'          => null,
        'name'        => '',
        'value'       => 0,
        'color'       => '',
        'description' => '',
    );

    $args = wp_parse_args( $args, $defaults );

    // some validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No name provided.', 'erp' ) );
    }

    $exist = erp_hr_leave_get_policy_by_name( $args['name'] );
    if ( $exist && $args['id'] !== $exist->id ) {
        return new WP_Error( 'policy_name_exists', __( 'Policy name already exists, please use a different one.', 'erp' ) );
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

        if ( ! empty( $leave_policy ) ) {
            do_action( 'erp_hr_leave_policy_new', $leave_policy, $args );
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
 * Apply policy in existing employee
 *
 * @since 0.1
 * @since 1.2.0 Using `erp_hr_apply_policy_to_employee` for both Immediate and
 *              Scheduled policy when `instant_apply` is true
 *
 * @param  object $policy Leave_Policies model
 * @param  array $args
 *
 * @return void
 */
function erp_hr_apply_policy_existing_employee( $policy, $args ) {
    if ( ! erp_validate_boolean( $args['instant_apply'] ) ) {
        return;
    }

    erp_bulk_policy_assign($policy);
//    erp_hr_apply_policy_to_employee( $policy );
}

/**
 * Apply a leave policy to all or filtered employees
 *
 * @since 1.2.0
 *
 * @param object $policy Leave_Policies eloquent object
 * @param array $employee_ids Employee ids
 *
 * @return void
 */
function erp_hr_apply_policy_to_employee( $policy, $employee_ids = [] ) {
    if ( is_int( $policy ) ) {
        $policy = $policy = \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy );
    }

    $db     = \WeDevs\ORM\Eloquent\Facades\DB::instance();
    $prefix = $db->db->prefix;

    $employees = $db->table( 'erp_hr_employees as employee' )
                    ->select( 'employee.user_id' )
                    ->leftJoin( "{$prefix}usermeta as gender", function ( $join ) {
                        $join->on( 'employee.user_id', '=', 'gender.user_id' )->where( 'gender.meta_key', '=', 'gender' );
                    } )
                    ->leftJoin( "{$prefix}usermeta as marital_status", function ( $join ) {
                        $join->on( 'employee.user_id', '=', 'marital_status.user_id' )->where( 'marital_status.meta_key', '=', 'marital_status' );
                    } )
                    ->where( 'status', '=', 'active' );

    if ( ! empty( $employee_ids ) && is_array( $employee_ids ) ) {
        $employees->whereIn( 'employee.user_id', $employee_ids );
    }

    if ( $policy->department > 0 ) {
        $employees->where( 'department', $policy->department );
    }

    if ( $policy->designation > 0 ) {
        $employees->where( 'designation', $policy->designation );
    }

    if ( $policy->location > 0 ) {
        $employees->where( 'location', $policy->location );
    }

    if ( $policy->gender != - 1 ) {
        $employees->where( 'gender.meta_value', $policy->gender );
    }

    if ( $policy->marital != - 1 ) {
        $employees->where( 'marital_status.meta_value', $policy->marital );
    }

    if ( $policy->activate == 2 && ! empty( $policy->execute_day ) ) {
        $current_date = date( 'Y-m-d', current_time( 'timestamp' ) );

        $employees->where(
            $db->raw( "DATEDIFF( '$current_date', `employee`.`hiring_date` )" ), '>=', $policy->execute_day
        );
    }

    $employees = $employees->get();

    if ( ! empty( $employees ) ) {
        foreach ( $employees as $employee ) {
            erp_hr_apply_leave_policy( $employee->user_id, $policy );
        }
    }
}

/**
 * Assign entitlement
 *
 * @since 0.1
 * @since 1.2.0 Calculate from_date and to_date based on policy
 *              effective_date and financial start/end dates
 *
 * @param  int $user_id
 * @param  object $leave_policy
 *
 * @return void
 */
function erp_hr_apply_leave_policy( $user_id, $policy ) {
    $financial_year = erp_get_financial_year_dates();

    $from_date                      = ! empty( $policy->effective_date ) ? $policy->effective_date : $financial_year['start'];
    $from_date_timestamp            = strtotime( $from_date );
    $financial_year_start_timestamp = strtotime( $financial_year['start'] );

    if ( $from_date_timestamp < $financial_year_start_timestamp ) {
        $from_date = date( 'Y-m-d 00:00:00', $financial_year_start_timestamp );
    } else {
        $from_date = date( 'Y-m-d 00:00:00', $from_date_timestamp );
    }

    $to_date                      = $financial_year['end'];
    $financial_year_end_timestamp = strtotime( $financial_year['end'] );

    if ( $from_date_timestamp > $financial_year_end_timestamp ) {
        $financial_year_end_timestamp += YEAR_IN_SECONDS;
        $to_date                      = date( 'Y-m-d 23:59:59', $financial_year_end_timestamp );
    }

    $policy = [
        'user_id'   => $user_id,
        'policy_id' => $policy->id,
        'days'      => $policy->value,
        'from_date' => $from_date,
        'to_date'   => $to_date,
        'comments'  => $policy->description
    ];

    erp_hr_leave_insert_entitlement( $policy );
}

/**
 * Insert a new policy entitlement for an employee
 *
 * @since 0.1
 * @since 1.2.0 Use `erp_get_financial_year_dates` for financial start and end dates
 *
 * @param  array $args
 *
 * @return int|object New entitlement id or WP_Error object
 */
function erp_hr_leave_insert_entitlement( $args = [] ) {
    global $wpdb;

    $financial_year = erp_get_financial_year_dates();

    $defaults = array(
        'id'         => null,
        'user_id'    => 0,
        'policy_id'  => 0,
        'days'       => 0,
        'from_date'  => $financial_year['start'],
        'to_date'    => $financial_year['end'],
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

    $entitlement = $entitlement->where( function ( $condition ) use ( $user_id, $policy_id, $fields, $financial_year ) {
        $financial_start_date = $fields['from_date'] ? $fields['from_date'] : $financial_year['start'];
        $financial_end_date   = $fields['to_date'] ? $fields['to_date'] : $financial_year['end'];
        $condition->where( 'from_date', '>=', $financial_start_date );
        $condition->where( 'to_date', '<=', $financial_end_date );
        $condition->where( 'user_id', '=', $user_id );
        $condition->where( 'policy_id', '=', $policy_id );
    } );

    $existing_entitlement = $entitlement->get();

    if ( $existing_entitlement->count() ) {
        return $existing_entitlement->first()->id;
    }

    $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_entitlements', $fields );

    do_action( 'erp_hr_leave_insert_new_entitlement', $wpdb->insert_id, $fields );

    return $wpdb->insert_id;
}

/**
 * Apply `Immediately` type policies on new employee
 *
 * @since 1.2.0
 *
 * @param int $user_id Employee user_id provided by `erp_hr_employee_new` hook
 *
 * @return void
 */
function erp_hr_apply_policy_on_new_employee( $user_id ) {
    $policies = \WeDevs\ERP\HRM\Models\Leave_Policies::where( 'activate', 1 )->get();

    $policies->each( function ( $policy ) use ( $user_id ) {
        erp_hr_apply_policy_to_employee( $policy, [ $user_id ] );
    } );
}

/**
 * Apply `Scheduled` type policies on new employee
 *
 * @since 1.2.0
 *
 * @return void
 */
function erp_hr_apply_scheduled_policies() {
    $policies = \WeDevs\ERP\HRM\Models\Leave_Policies::where( 'activate', 2 )->get();

    $policies->each( function ( $policy ) {
        erp_hr_apply_policy_to_employee( $policy );
    } );
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
        'start'       => current_time( 'mysql' ),
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
            do_action( 'erp_hr_new_holiday', $leave_policy->id, $args );

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
        'number'  => 99,
        'offset'  => 0,
        'orderby' => 'name',
        'order'   => 'ASC',
    );

    $args = wp_parse_args( $args, $defaults );


    $cache_key = 'erp-leave-pol';
    $policies  = wp_cache_get( $cache_key, 'erp' );

    if ( false === $policies ) {

        $policies = erp_array_to_object(
            \WeDevs\ERP\HRM\Models\Leave_Policies::select( array(
                'id',
                'name',
                'value',
                'color',
                'department',
                'designation',
                'gender',
                'marital',
                'activate',
                'execute_day',
                'effective_date',
                'location',
                'description'
            ) )
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
 * Fetch a leave policy by match policy name
 *
 * @since 1.3.2
 *
 * @param string $name
 *
 * @return \stdClass
 */
function erp_hr_leave_get_policy_by_name( $name ) {
    return \WeDevs\ERP\HRM\Models\Leave_Policies::where( 'name', $name )->first();
}

/**
 * Fetch a leave policy by policy id
 *
 * @since 0.1
 * @since 1.2.0 Return Eloquent Leave_Policies model
 *
 * @param integer $policy_id
 *
 * @return \stdClass
 */
function erp_hr_leave_get_policy( $policy_id ) {
    return \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy_id );
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
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'created_at',
        'order'   => 'DESC',
    );

    $args = wp_parse_args( $args, $defaults );

    $holiday = new \WeDevs\ERP\HRM\Models\Leave_Holiday();

    $holiday_results = $holiday->select( array( 'id', 'title', 'start', 'end', 'description' ) );

    $holiday_results = erp_hr_holiday_filter_param( $holiday_results, $args );

    //if not search then execute nex code
    if ( isset( $args['id'] ) && ! empty( $args['id'] ) ) {
        $id              = intval( $args['id'] );
        $holiday_results = $holiday_results->where( 'id', '=', "$id" );
    }

    $cache_key = 'erp-get-holidays-' . md5( serialize( $args ) );
    $holidays  = wp_cache_get( $cache_key, 'erp' );

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
        $holiday = $holiday->where( 'title', 'LIKE', "%$args_s%" );
    }

    if ( isset( $args['from'] ) && ! empty( $args['from'] ) ) {
        $holiday = $holiday->where( 'start', '>=', $args['from'] );
    }

    if ( isset( $args['to'] ) && ! empty( $args['to'] ) ) {
        $holiday = $holiday->where( 'end', '<=', $args['to'] );
    }

    if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
        $holiday = $holiday->orWhere( 'description', 'LIKE', "%$args_s%" );
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

    foreach ( $policies as $policy ) {
        $dropdown[ $policy->id ] = stripslashes( $policy->name );
    }

    return $dropdown;
}

/**
 * Delete a policy
 *
 * @since 0.1
 *
 * @param  int|array $policy_ids
 *
 * @return void
 */
function erp_hr_leave_policy_delete( $policy_ids ) {
    if ( ! is_array( $policy_ids ) ) {
        $policy_ids = [ $policy_ids ];
    }

    $policies = \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy_ids );

    $policies->each( function ( $policy ) {
        $has_request = $policy->leave_requests()->count();

        if ( ! $has_request ) {
            $policy->entitlements()->delete();
            $policy->delete();

            do_action( 'erp_hr_leave_policy_delete', $policy );
        }
    } );
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

    $policies = \WeDevs\ERP\HRM\Models\Leave_Policies::select( $policy_tb . '.name', $policy_tb . '.id' )
                                                     ->leftjoin( $en_tb, $en_tb . '.policy_id', '=', $policy_tb . '.id' )
                                                     ->where( $en_tb . '.user_id', $employee_id )
                                                     ->where( 'from_date', '>=', $financial_start_date )
                                                     ->where( 'to_date', '<=', $financial_end_date )
                                                     ->distinct()
                                                     ->get()
                                                     ->toArray();

    if ( ! empty( $policies ) ) {
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
        'start_date'   => current_time( 'mysql' ),
        'end_date'     => current_time( 'mysql' ),
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
        foreach ( $period['days'] as $date ) {
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
 * @param  int $request_id
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
 * @param  array $args
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
        'start_date' => '',
        'end_date'   => ''
    );

    $args  = wp_parse_args( $args, $defaults );
    $where = '';

    // Check if the row want to search
    if ( ! empty( $args['s'] ) ) {
        $where .= " WHERE u.display_name LIKE '%{$args['s']}%'";
    }

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

    if ( $args['start_date']  && $args['end_date'] ) {
        $from_date = $args['start_date'];
        $to_date   = $args['end_date'];

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

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    $cache_key = 'erp-hr-leave-request-counts';
    $results   = wp_cache_get( $cache_key, 'erp' );

    if ( false === $results ) {
        $sql     = "SELECT status, COUNT(id) as num FROM {$wpdb->prefix}erp_hr_leave_requests WHERE status != 0 GROUP BY status;";
        $results = $wpdb->get_results( $sql );

        wp_cache_set( $cache_key, $results, 'erp' );
    }

    foreach ( $results as $row ) {
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
 * Statuses and their ids
 * delete -  3
 * reject -  3
 * approve - 1
 * pending - 2
 *
 * @since 0.1
 *
 * @param  integer $request_id
 * @param  string $status
 *
 * @return object Eloquent Leave_request model
 */
function erp_hr_leave_request_update_status( $request_id, $status ) {
    $request = \WeDevs\ERP\HRM\Models\Leave_request::find( $request_id );
    if ( empty( $request ) ) {
        return new WP_Error( 'no-request-found', __( 'Invalid leave request', 'erp' ) );
    }
    $status = absint( $status );
    $existing_request = erp_hr_get_leave_requests( array(
        'user_id'     => $request->user_id,
        'status'      => 1,
        'start_date'  => $request->start_date,
        'end_date'    => $request->end_date,
    ) );


    $request->status     = $status;
    $request->updated_by = get_current_user_id();
    $request->save();

    // notification email
    if ( 1 === $status ) {

        $approved_email = wperp()->emailer->get_email( 'Approved_Leave_Request' );

        if ( is_a( $approved_email, '\WeDevs\ERP\Email' ) ) {
            $approved_email->trigger( $request_id );
        }

    } else if ( 3 === $status ) {

        $rejected_email = wperp()->emailer->get_email( 'Rejected_Leave_Request' );

        if ( is_a( $rejected_email, '\WeDevs\ERP\Email' ) ) {
            $rejected_email->trigger( $request_id );
        }
    }

    $status = ( $status == 1 ) ? 'approved' : 'pending';

    do_action( "erp_hr_leave_request_{$status}", $request_id, $request );

    return $request;
}

/**
 * Get leave requests status
 *
 * @since 0.1
 *
 * @param  int|boolean $status
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
 * @param  integer $employee_id
 * @param  integer $policy_id
 * @param  integer $year
 *
 * @return bool
 */
function erp_hr_leave_has_employee_entitlement( $employee_id, $policy_id, $year ) {
    global $wpdb;

    $from_date = $year . '-01-01';
    $to_date   = $year . '-12-31';

    $query  = "SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements
        WHERE user_id = %d AND policy_id = %d AND from_date = %s AND to_date = %s";
    $result = $wpdb->get_var( $wpdb->prepare( $query, $employee_id, $policy_id, $from_date, $to_date ) );

    return $result;
}

/**
 * Get all the leave entitlements of a calendar year
 *
 * @since 0.1
 * @since 1.2.0 Depricate `year` arg and using `from_date` and `to_date` instead
 *
 * @param  integer $year
 *
 * @return array
 */
function erp_hr_leave_get_entitlements( $args = array() ) {
    global $wpdb;

    $financial_year_dates = erp_get_financial_year_dates();

    $defaults = array(
        'employee_id' => 0,
        'policy_id'   => 0,
        'from_date'   => $financial_year_dates['start'],
        'to_date'     => $financial_year_dates['end'],
        'number'      => 20,
        'offset'      => 0,
        'orderby'     => 'en.user_id, en.created_on',
        'order'       => 'DESC',
        'debug'       => false,
        'emp_status'  => ''
    );

    $args  = wp_parse_args( $args, $defaults );
    $where = 'WHERE 1 = 1';

    /**
     * @deprecated 1.2.0 Use $args['from_date'] and $args['to_date'] instead
     */
    if ( ! empty( $args['year'] ) ) {
        $from_date = date( $args['year'] . '-m-d H:i:s', strtotime( $financial_year_dates['start'] ) );
        $to_date   = date( $args['year'] . '-m-d H:i:s', strtotime( $financial_year_dates['end'] ) );
        $where     .= " AND en.from_date >= date('$from_date') AND en.to_date <= date('$to_date')";
    }

    if ( ! empty( $args['from_date'] ) ) {
        $where .= ' AND en.from_date >= "' . $args['from_date'] . '"';
    }

    if ( ! empty( $args['to_date'] ) ) {
        $where .= ' AND en.to_date <= "' . $args['to_date'] . '"';
    }

    if ( $args['employee_id'] ) {
        $where .= " AND en.user_id = " . intval( $args['employee_id'] );
    }

    if ( ! empty( $args['search'] ) ) {
        $where .= $wpdb->prepare(" AND u.display_name LIKE '%%%s%%' ", $args['search'] );
    }

    if ( $args['policy_id'] ) {
        $where .= " AND en.policy_id = " . intval( $args['policy_id'] );
    }

    if ( $args['emp_status'] == 'active') {
        $where .= " AND emp.status = 'active'";
    }

    $query = "SELECT en.*, u.display_name as employee_name, pol.name as policy_name, emp.status as emp_status
        FROM `{$wpdb->prefix}erp_hr_leave_entitlements` AS en
        LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS pol ON pol.id = en.policy_id
        LEFT JOIN {$wpdb->users} AS u ON en.user_id = u.ID
        LEFT JOIN {$wpdb->prefix}erp_hr_employees AS emp ON en.user_id = emp.user_id
        $where
        ORDER BY {$args['orderby']} {$args['order']}
        LIMIT %d,%d;";

    $sql     = $wpdb->prepare( $query, absint( $args['offset'] ), absint( $args['number'] ) );
    $results = $wpdb->get_results( $sql );

    return $results;
}

/**
 * Count leave entitlement
 *
 * @since 0.1
 *
 * @param  array $args
 *
 * @return integer
 */
function erp_hr_leave_count_entitlements( $args = array() ) {
    $financial_year_dates = erp_get_financial_year_dates();

    $defaults = [
        'from_date' => $financial_year_dates['start'],
        'to_date'   => $financial_year_dates['end']
    ];

    $args = wp_parse_args( $args, $defaults );

    return \WeDevs\ERP\HRM\Models\Leave_Entitlement::where( 'from_date', '>=', $args['from_date'] )
                                                   ->where( 'to_date', '<=', $args['to_date'] )
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
//    $leave_recored = \WeDevs\ERP\HRM\Models\Leave_request::where( 'user_id', '=', $user_id )
//                                                         ->where( 'policy_id', '=', $policy_id )->get()->toArray();
//    $leave_recored = wp_list_pluck( $leave_recored, 'status' );
//
//    if ( in_array( '1', $leave_recored ) ) {
//        return;
//    }
//
//    if ( \WeDevs\ERP\HRM\Models\Leave_Entitlement::find( $id )->delete() ) {
//        return \WeDevs\ERP\HRM\Models\Leave_request::where( 'user_id', '=', $user_id )
//                                                   ->where( 'policy_id', '=', $policy_id )
//                                                   ->delete();
//    }

    return \WeDevs\ERP\HRM\Models\Leave_Entitlement::find( $id )->delete();
}

/**
 * Erp get leave balance
 *
 * @since 0.1
 * @since 1.1.18 Add start_date in where clause
 * @since 1.2.1  Fix main query statement
 *
 * @param  integer $user_id
 *
 * @return float|boolean
 */
function erp_hr_leave_get_balance( $user_id ) {
    global $wpdb;

    $query = "select policy_id, days";
    $query .= " from {$wpdb->prefix}erp_hr_leave_entitlements";
    $query .= " where user_id = %d";

    $results = $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );

    $balance = [];

    if ( ! empty( $results ) ) {
        foreach ( $results as $result ) {
            $balance[ $result->policy_id ] = array(
                'policy_id'   => $result->policy_id,
                'scheduled'   => 0,
                'entitlement' => $result->days,
                'total'       => 0,
                'available'   => $result->days
            );
        }
    }

    $financial_start_date = erp_financial_start_date();
    $financial_end_date   = erp_financial_end_date();

    $query = "SELECT req.id, req.days, req.policy_id, req.start_date, en.days as entitlement";
    $query .= " FROM {$wpdb->prefix}erp_hr_leave_requests AS req";
    $query .= " LEFT JOIN {$wpdb->prefix}erp_hr_leave_entitlements as en on (req.user_id = en.user_id and req.policy_id = en.policy_id and en.from_date >= '$financial_start_date' )";
    $query .= " WHERE req.status = 1 and req.user_id = %d AND ( req.start_date >= '$financial_start_date' AND req.end_date <= '$financial_end_date' )";

    $sql     = $wpdb->prepare( $query, $user_id );
    $results = $wpdb->get_results( $sql );

    $temp         = [];
    $current_time = current_time( 'timestamp' );

    if ( $results ) {
        // group by policy
        foreach ( $results as $request ) {
            $temp[ $request->policy_id ][] = $request;
        }

        // calculate each policy
        foreach ( $temp as $policy_id => $requests ) {
            $balance[ $policy_id ] = array(
                'policy_id'   => $policy_id,
                'scheduled'   => 0,
                'entitlement' => 0,
                'total'       => 0,
                'available'   => 0
            );

            foreach ( $requests as $request ) {
                $balance[ $policy_id ]['entitlement'] = (int) $request->entitlement;
                $balance[ $policy_id ]['total']       += $request->days;

                if ( $current_time < strtotime( $request->start_date ) ) {
                    $balance[ $policy_id ]['scheduled'] += $request->days;
                }
            }
        }

        // calculate available
        foreach ( $balance as &$policy ) {
            $available           = $policy['entitlement'] - $policy['total'];
            $policy['available'] = $available < 0 ? 0 : $available;
        }
    }

    return $balance;
}

/**
 * Get cuurent month approve leave request list
 *
 * @since 0.1
 * @since 1.2.0 Ignore terminated employees
 * @since 1.2.2 Exclude past requests
 *              Sort results by start_date
 *
 * @return array
 */
function erp_hr_get_current_month_leave_list() {
    $db     = new \WeDevs\ORM\Eloquent\Database();
    $prefix = $db->db->prefix;

    return $db->table( 'erp_hr_leave_requests as req' )
              ->select( 'req.user_id', 'req.start_date', 'req.end_date', 'req.status', 'req.id' )
              ->leftJoin( "{$prefix}erp_hr_employees as em", 'req.user_id', '=', 'em.user_id' )
              ->where( 'em.status', '=', 'active' )
              ->whereDate( 'req.start_date', '>=', date( 'Y-m-d 00:00:00', current_time( 'timestamp' ) ) )
              ->whereDate( 'req.start_date', '<=', date( 'Y-m-d 23:59:59', strtotime( 'last day of this month' ) ) )
              ->where( 'req.status', 1 )
              ->orderBy( 'req.start_date', 'asc' )
              ->get();
}

/**
 * Get next month leave request approved list
 *
 * @since 0.1
 * @since 1.2.0 Ignore terminated employees
 * @since 1.2.2 Sort results by start_date
 *
 * @return array
 */
function erp_hr_get_next_month_leave_list() {
    $db     = new \WeDevs\ORM\Eloquent\Database();
    $prefix = $db->db->prefix;

    return $db->table( 'erp_hr_leave_requests as req' )
              ->select( 'req.user_id', 'req.start_date', 'req.end_date' )
              ->leftJoin( "{$prefix}erp_hr_employees as em", 'req.user_id', '=', 'em.user_id' )
              ->where( 'em.status', '!=', 'terminated' )
              ->where( 'req.start_date', '>=', date( 'Y-m-d 00:00:00', strtotime( 'first day of next month' ) ) )
              ->where( 'req.start_date', '<=', date( 'Y-m-d 23:59:59', strtotime( 'last day of next month' ) ) )
              ->where( 'req.status', 1 )
              ->orderBy( 'req.start_date', 'asc' )
              ->get();
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

    $entitlement = $entitlement->where( function ( $condition ) use ( $before_financial_start_date, $before_financial_end_date ) {
        $condition->where( 'from_date', '>=', $before_financial_start_date );
        $condition->where( 'to_date', '<=', $before_financial_end_date );
    } );

    $entitlements = $entitlement->get()->toArray();

    foreach ( $entitlements as $key => $entitlement ) {

        $policy = array(
            'user_id'   => $entitlement['user_id'],
            'policy_id' => $entitlement['policy_id'],
            'days'      => $entitlement['days'],
            'from_date' => erp_financial_start_date(),
            'to_date'   => erp_financial_end_date(),
            'comments'  => $entitlement['comments']
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

    $employee_tb = $wpdb->prefix . 'erp_hr_employees';
    $users_tb    = $wpdb->users;
    $request_tb  = $wpdb->prefix . 'erp_hr_leave_requests';
    $policy_tb   = $wpdb->prefix . 'erp_hr_leave_policies';

    $employee      = new \WeDevs\ERP\HRM\Models\Employee();
    $leave_request = new \WeDevs\ERP\HRM\Models\Leave_request();

    $department  = isset( $get['department'] ) && ! empty( $get['department'] ) && $get['department'] != '-1' ? intval( $get['department'] ) : false;
    $designation = isset( $get['designation'] ) && ! empty( $get['designation'] ) && $get['designation'] != '-1' ? intval( $get['designation'] ) : false;

    $leave_request = $leave_request->where( $request_tb . '.status', '!=', 3 );

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

        return erp_array_to_object( $request->get()->toArray() );
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

/**
 * Get year ranges based on available financial years
 *
 * @since 1.2.0
 *
 * @return array
 */
function get_entitlement_financial_years() {
    $db     = \WeDevs\ORM\Eloquent\Facades\DB::instance();
    $prefix = $db->db->prefix;

    $min_max_dates = $db->table( 'erp_hr_leave_entitlements' )
                        ->select( $db->raw( 'min( `from_date` ) as min' ), $db->raw( 'max( `to_date` ) max' ) )
                        ->first();

    $start_year = $end_year = current_time( 'Y' );

    if ( ! empty( $min_max_dates->min ) ) {
        $min_date_fy_year = get_financial_year_from_date( $min_max_dates->min );

        $start_year = $min_date_fy_year['start'];
        $end_year   = date( 'Y', strtotime( $min_max_dates->max ) );

    } else {
        return [];
    }

    $start_month = erp_get_option( 'gen_financial_month', 'erp_settings_general', 1 );

    $years = [];

    for ( $i = $start_year; $i <= $end_year; $i ++ ) {
        if ( 1 === absint( $start_month ) ) {
            $years[] = $i;

        } else if ( ! ( ( $i + 1 ) > $end_year ) ) {
            $years[] = $i . '-' . ( $i + 1 );

        } else if ( $start_year === $end_year ) {
            $years[] = ( $i - 1 ) . '-' . $i;
        }
    }

    return $years;
}

/**
 * Generate leave reports
 *
 * @since 1.3.2
 *
 * @param array $employees
 * @param null $start_date
 * @param null $end_date
 *
 * @return array
 *
 */
function erp_get_leave_report( array $employees, $start_date = null, $end_date = null ) {
    $year_dates = erp_get_financial_year_dates( date( 'Y-m-d' ) );
    if ( ! $start_date ) {
        $start_date = $year_dates['start'];
    }
    if ( ! $end_date ) {
        $end_date = $year_dates['end'];
    }
    $employees_report = \WeDevs\ERP\HRM\Models\Employee::whereIn( 'user_id', $employees );
    $employees_report = $employees_report->select( 'user_id' )
                                         ->with( [
                                             'leave_requests' => function ( $q ) use ( $start_date, $end_date ) {
                                                 $q->where( 'status', '=', '1' )
                                                   ->whereDate( 'start_date', '>=', $start_date )
                                                   ->whereDate( 'end_date', '<=', $end_date );
                                             },
                                             'entitlements'   => function ( $q ) use ( $start_date, $end_date ) {
                                                $entitlement_start_date = date('Y', strtotime( $start_date ) ) . '-01-01';
                                                $entitlement_end_date   = date('Y', strtotime( $end_date ) ) . '-12-31';

                                                $q->whereDate( 'from_date', '>=', $entitlement_start_date )
                                                   ->whereDate( 'to_date', '<=', $entitlement_end_date )
                                                   ->JoinWithPolicy();
                                             }
                                         ] )
                                         ->get();

    $reports = [];
    foreach ( $employees_report as $employee_report ) {
        $entitlements = [];
        foreach ( $employee_report->entitlements as $entitlement ) {
            $report = [
                'entitlement_id' => $entitlement->id,
                'days'           => $entitlement->days,
                'from_date'      => $entitlement->from_date,
                'to_date'        => $entitlement->to_date,
                'policy'         => $entitlement->name,
                'policy_id'      => $entitlement->policy_id,
                'color'          => $entitlement->color,
                'spent'          => 0,
            ];

            $entitlements[ $entitlement->policy_id ] = $report;
        }

        foreach ( $employee_report->leave_requests as $leave_report ) {
            if ( isset( $entitlements[ $leave_report->policy_id ] ) ) {
                $entitlements[ $leave_report->policy_id ]['spent'] += $leave_report->days;
            }

            $reports[ $employee_report->user_id ] = $entitlements;
        }

    }

    return $reports;
}

/**
 * Assign policy bulk
 *
 * @since 1.3.2
 *
 * @param $policy
 * @param array $employee_ids
 *
 */
function erp_bulk_policy_assign( $policy, $employee_ids = [] ) {
    if ( is_int( $policy ) ) {
        $policy = \WeDevs\ERP\HRM\Models\Leave_Policies::find( $policy );
    }

    $db     = \WeDevs\ORM\Eloquent\Facades\DB::instance();
    $prefix = $db->db->prefix;
    global $wpdb;

    $employees = $db->table( 'erp_hr_employees as employee' )
                    ->select( 'employee.user_id' )
                    ->leftJoin( "{$prefix}usermeta as gender", function ( $join ) {
                        $join->on( 'employee.user_id', '=', 'gender.user_id' )->where( 'gender.meta_key', '=', 'gender' );
                    } )
                    ->leftJoin( "{$prefix}usermeta as marital_status", function ( $join ) {
                        $join->on( 'employee.user_id', '=', 'marital_status.user_id' )->where( 'marital_status.meta_key', '=', 'marital_status' );
                    } )
                    ->where( 'status', '=', 'active' );
    if ( ! empty( $employee_ids ) && is_array( $employee_ids ) ) {
        $employees->whereIn( 'employee.user_id', $employee_ids );
    }

    if ( $policy->department > 0 ) {
        $employees->where( 'department', $policy->department );
    }

    if ( $policy->designation > 0 ) {
        $employees->where( 'designation', $policy->designation );
    }

    if ( $policy->location > 0 ) {
        $employees->where( 'location', $policy->location );
    }

    if ( $policy->gender != - 1 ) {
        $employees->where( 'gender.meta_value', $policy->gender );
    }

    if ( $policy->marital != - 1 ) {
        $employees->where( 'marital_status.meta_value', $policy->marital );
    }

    if ( $policy->activate == 2 && ! empty( $policy->execute_day ) ) {
        $current_date = date( 'Y-m-d', current_time( 'timestamp' ) );

        $employees->where(
            $db->raw( "DATEDIFF( '$current_date', `employee`.`hiring_date` )" ), '>=', $policy->execute_day
        );
    }

    $employees = $employees->get();

    if ( empty( $employees ) ) {
        return;
    }

    $financial_year = erp_get_financial_year_dates();

    $from_date                      = ! empty( $policy->effective_date ) ? $policy->effective_date : $financial_year['start'];
    $from_date_timestamp            = strtotime( $from_date );
    $financial_year_start_timestamp = strtotime( $financial_year['start'] );

    if ( $from_date_timestamp < $financial_year_start_timestamp ) {
        $from_date = date( 'Y-m-d 00:00:00', $financial_year_start_timestamp );
    } else {
        $from_date = date( 'Y-m-d 00:00:00', $from_date_timestamp );
    }

    $to_date                      = $financial_year['end'];
    $financial_year_end_timestamp = strtotime( $financial_year['end'] );

    if ( $from_date_timestamp > $financial_year_end_timestamp ) {
        $financial_year_end_timestamp += YEAR_IN_SECONDS;
        $to_date                      = date( 'Y-m-d 23:59:59', $financial_year_end_timestamp );
    }


    $fields = [
        'user_id'    => '',
        'policy_id'  => $policy->id,
        'days'       => $policy->value,
        'from_date'  => $from_date,
        'to_date'    => $to_date,
        'comments'   => $policy->description,
        'id'         => null,
        'status'     => 1,
        'created_by' => get_current_user_id(),
        'created_on' => current_time( 'mysql' )
    ];

    foreach ( $employees as $employee ) {
        $user_id           = $employee->user_id;
        $fields['user_id'] = $user_id;
        $entitlement       = new \WeDevs\ERP\HRM\Models\Leave_Entitlement();
        $entitlement       = $entitlement->where( function ( $condition ) use ( $fields, $financial_year ) {
            $financial_start_date = $fields['from_date'] ? $fields['from_date'] : $financial_year['start'];
            $financial_end_date   = $fields['to_date'] ? $fields['to_date'] : $financial_year['end'];
            $condition->whereBetween( 'from_date', [ $financial_year['start'], $financial_end_date ] );
            $condition->where( 'to_date', '<=', $financial_end_date );
            $condition->where( 'user_id', '=', $fields['user_id'] );
            $condition->where( 'policy_id', '=', $fields['policy_id'] );
        } );

        $existing_entitlement = $entitlement->first();

        if ( $existing_entitlement ) {
            continue;
        }

        $result = $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_entitlements', $fields );
        if ( $result ) {
            do_action( 'erp_hr_leave_insert_new_entitlement', $wpdb->insert_id, $fields );
        }

    }

}

/**
 * Import holidays from csv
 *
 * @since 1.5.10
 *
 * @param $file
 *
 */
function import_holidays_csv( $file ) {

    require_once WPERP_INCLUDES . '/lib/parsecsv.lib.php';

    $csv = new ParseCsv();
    $csv->encoding( null, 'UTF-8' );
    $csv->parse( $file );

    $error_list   = array();
    $valid_import = array();
    $line         = 1;
    $msg          = "";

    foreach ( $csv->data as $data ) {
        $title       = ( isset( $data['title'] ) ) ? $data['title'] : '';
        $start       = ( isset( $data['start'] ) ) ? $data['start'] : '';
        $end         = ( isset( $data['end'] ) ) ? $data['end'] : '';
        $description = ( isset( $data['description'] ) ) ? $data['description'] : '';

        if( ! empty( $title ) && ! empty( $start ) && ! empty( $end ) ) {
            if ( is_string( $title ) && is_string( $start ) && is_string( $end ) ) {
                if ( strlen( $title ) < 200 ) {
                    if ( DateTime::createFromFormat( 'Y-m-d H:i:s', $start ) !== FALSE &&
                            DateTime::createFromFormat( 'Y-m-d H:i:s', $end ) !== FALSE ) {
                        $holiday_id = erp_hr_leave_insert_holiday( array(
                            "title" => $title,
                            "start" => $start,
                            "end" => $end,
                            "description" => $description,
                        ) );

                        if ( is_wp_error( $holiday_id ) ) {
                            $error_list[]   = $line;
                        } else {
                            $valid_import[] = $line;
                        }
                    }
                }
            }
        } else {
            $error_list[] = $line;
        }
        $line++;
    }

    if( count( $valid_import ) > 0 ) {
        $html_class  = "updated notice";
        $msg        .= sprintf( __( "Successfully imported %u data<br>", 'wp-erp' ), count( $valid_import ) );
    }

    if ( count( $error_list ) > 0 ) {
        $html_class  = "error  notice";
        $err_string  = implode( ',' , $error_list );
        $msg        .= sprintf( __( "Failed to import line no  %s. Please check if title, start & end fields are following proper formation", 'wp-erp' ), $err_string );
    }

    return "<div class='{$html_class}'><p>{$msg}</p></div>";
}

/**
 * Save employee leave request attachment
 *
 * @since 1.5.10
 *
 * @param $file
 *
 */
function save_leave_attachment( $request_id, $request, $leaves ) {
    if ( isset( $_FILES['leave_document'] ) && isset( $_FILES['leave_document']['name'] ) && ! empty( $_FILES['leave_document']['name'][0] ) ) {
        $uploader = new \WeDevs\ERP\Uploader();

        foreach ( $_FILES['leave_document']['name'] as $key => $value ) {
            $upload = array(
                'name'     => isset( $_FILES['leave_document'], $_FILES['leave_document']['name'][$key] ) ? sanitize_text_field( wp_unslash( $_FILES['leave_document']['name'][$key] ) ) : '',
                'type'     => isset( $_FILES['leave_document'], $_FILES['leave_document']['type'][$key] ) ? sanitize_text_field( wp_unslash( $_FILES['leave_document']['type'][$key] ) ) : '',
                'tmp_name' => isset( $_FILES['leave_document'], $_FILES['leave_document']['tmp_name'][$key] ) ? $_FILES['leave_document']['tmp_name'][$key] : '',
                'error'    => isset( $_FILES['leave_document'], $_FILES['leave_document']['error'][$key] ) ? sanitize_text_field( wp_unslash( $_FILES['leave_document']['error'][$key] ) ) : '',
                'size'     => isset( $_FILES['leave_document'], $_FILES['leave_document']['size'][$key] ) ? sanitize_text_field( wp_unslash( $_FILES['leave_document']['size'][$key] ) ) : ''
            );

            $file   = $uploader->handle_upload( $upload );

            if( isset( $file['success'] ) && $file['success'] ) {
                add_user_meta( $request['user_id'], 'leave_document_' . $request_id , $file['attach_id'] );
            }
        }
    }
}
