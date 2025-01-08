<?php

use WeDevs\ERP\HRM\Employee;
use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\FinancialYear;
use WeDevs\ERP\HRM\Models\Leave;
use WeDevs\ERP\HRM\Models\LeaveApprovalStatus;
use WeDevs\ERP\HRM\Models\LeaveEntitlement;
use WeDevs\ERP\HRM\Models\LeaveHoliday;
use WeDevs\ERP\HRM\Models\LeavePoliciesSegregation;
use WeDevs\ERP\HRM\Models\LeavePolicy;
use WeDevs\ERP\HRM\Models\LeaveRequest;
use WeDevs\ERP\HRM\Models\LeaveRequestDetail;
use WeDevs\ERP\HRM\Models\LeavesUnpaid;

/**
 * Get holiday between two date
 *
 * @since  0.1
 *
 * @param date $start_date
 * @param date $end_date
 *
 * @return array
 */
function erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date ) {
    $holiday = new LeaveHoliday();

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
 * @since 1.6.0
 *
 * @param string   $start_date
 * @param string   $end_date
 * @param int      $user_id
 * @param int|null $f_year
 *
 * @return bool
 */
function erp_hrm_is_leave_recored_exist_between_date( $start_date, $end_date, $user_id, $f_year = null ) {
    global $wpdb;

    if ( ! is_numeric( $start_date ) ) {
        $start_date = erp_current_datetime()->modify( $start_date )->setTime( 0, 0, 0 )->getTimestamp();
    }

    if ( ! is_numeric( $end_date ) ) {
        $end_date = erp_current_datetime()->modify( $end_date )->setTime( 0, 0, 0 )->getTimestamp();
    }

    if ( null === $f_year ) {
        $current_f_year = erp_hr_get_financial_year_from_date( $start_date );
        $f_year         = ! empty( $current_f_year ) ? $current_f_year->id : '';
    }

    $available = LeaveRequestDetail::where( 'user_id', '=', $user_id )
        ->where( 'f_year', '=', $f_year )
        ->where( 'leave_date', '>=', $start_date )
        ->where( 'leave_date', '<=', $end_date )
        ->count();

    return $available ? true : false;
}

/**
 * Check leave duration exist or not the policy days
 *
 * @since  0.1
 * @since 1.6.0
 *
 * @param string $start_date
 * @param string $end_date
 * @param int    $policy_id
 * @param int    $user_id
 *
 * @return bool
 */
function erp_hrm_is_valid_leave_duration( $start_date, $end_date, $policy_id, $user_id ) {
    if ( ! $user_id || ! $policy_id ) {
        return true;
    }

    $balance = erp_hr_leave_get_balance_for_single_entitlement( $policy_id );

    $user_enti_count    = $balance['day_out'];
    $policy_days        = $balance['total'];
    $working_day        = erp_hr_get_work_days_without_off_day( $start_date, $end_date, $user_id ); //erp_hr_get_work_days_between_dates( $start_date, $end_date );erp_hr_get_work_days_without_holiday
    $apply_days         = $working_day['total'] + $user_enti_count;

    if ( $apply_days > $policy_days ) {
        return false;
    }

    return true;
}

/**
 * Leave request time checking the apply date duration with the financial date duration
 *
 * @since  0.1
 * @since 1.6.0 updated according to new db structure
 *
 * @param string|int $start_date
 * @param string|int $end_date
 * @param int|null   $f_year
 *
 * @return bool
 */
function erp_hrm_is_valid_leave_date_range_within_financial_date_range( $start_date, $end_date, $f_year = null ) {
    if ( ! is_numeric( $start_date ) ) {
        $start_date = erp_current_datetime()->modify( $start_date )->getTimestamp();
    }

    if ( ! is_numeric( $end_date ) ) {
        $end_date = erp_current_datetime()->modify( $end_date )->getTimestamp();
    }

    if ( null === $f_year ) {
        $current_f_year = erp_hr_get_financial_year_from_date();
    } elseif ( is_numeric( $f_year ) ) {
        $current_f_year = FinancialYear::find( $f_year );
    }

    if ( null === $current_f_year ) {
        return false;
    }

    if ( ( $start_date < $current_f_year->start_date || $start_date > $current_f_year->end_date )
         || ( $end_date < $current_f_year->start_date || $end_date > $current_f_year->end_date ) ) {
        return false;
    }

    return true;
}

/**
 * Insert a new leave policy
 *
 * @since 1.0.0
 * @since 1.6.0
 * @since 1.6.5 added employee_type filter
 *
 * @param array $args
 *
 * @return int $policy_id
 */
function erp_hr_leave_insert_policy( $args = [] ) {
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        return new WP_Error( 'no-permission', esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $defaults = [
        'id' => null,
    ];

    $args = wp_parse_args( $args, $defaults );

    $common = [
        'leave_id'       => $args['leave_id'],
        'employee_type'  => $args['employee_type'],
        'department_id'  => $args['department_id'],
        'designation_id' => $args['designation_id'],
        'location_id'    => $args['location_id'],
        'gender'         => $args['gender'],
        'marital'        => $args['marital'],
        'f_year'         => $args['f_year'],
    ];

    $extra = apply_filters( 'erp_hr_leave_insert_policy_extra', [
        'description'          => $args['description'],
        'days'                 => $args['days'],
        'color'                => $args['color'],
        'applicable_from_days' => $args['applicable_from'],
        'apply_for_new_users'  => $args['apply_for_new_users'],
    ] );

    /*
     * Update
     */
    if ( $args['id'] ) {
        $where = [];

        foreach ( $common as $key => $value ) {
            $where[] = [ $key, $value ];
        }

        $exists = LeavePolicy::where( $where )->where( 'id', '<>', $args['id'] )->first();

        if ( $exists ) {
            return new WP_Error( 'exists', esc_html__( 'Policy already exists.', 'erp' ) );
        }

        // won't update days
        unset( $extra['days'] );

        $old_policy = \WeDevs\ERP\HRM\Models\LeavePolicy::find( $args['id'] )->toArray();

        $leave_policy = LeavePolicy::find( $args['id'] );
        $leave_policy->update( $extra );

        do_action( 'erp_hr_leave_update_policy', $args['id'] );
        do_action( 'erp_hr_leave_before_policy_updated', $args['id'], $old_policy );

        erp_hrm_purge_cache( [
			'list' => 'leave_policy',
			'leave_policy_id' => $args['id'],
		] );

        return $leave_policy->id;
    }

    /**
     * Create
     */
    $leave_policy = LeavePolicy::firstOrCreate( $common, $extra );

    if ( ! $leave_policy->wasRecentlyCreated ) {
        return new WP_Error( 'exists', esc_html__( 'Policy already exists.', 'erp' ) );
    }

    // create policy segregation
    $segre = isset( $_POST['segre'] ) ? array_map( 'absint', wp_unslash( $_POST['segre'] ) ) : [];

    $segre['leave_policy_id'] = $leave_policy->id;

    LeavePoliciesSegregation::create( $segre );

    do_action( 'erp_hr_leave_insert_policy', $leave_policy->id );

    erp_hrm_purge_cache( [
		'list' => 'leave_policy',
		'leave_policy_id' => $leave_policy->id,
	] );

    return $leave_policy->id;
}

/**
 * Apply policy in existing employee
 *
 * @since 0.1
 * @since 1.2.0 Using `erp_hr_apply_policy_to_employee` for both Immediate and
 *              Scheduled policy when `instant_apply` is true
 * @since 1.6.0 changed according to new db structure
 * @since 1.6.5 added employee_type filter
 *
 * @param int $policy_id newly created policy id
 *
 * @return void
 */
function erp_hr_apply_policy_existing_employee( $policy_id ) {
    $apply_for_existing_users = ! empty( $_POST['apply-for-existing-users'] ) ? 1 : 0;

    if ( ! $apply_for_existing_users ) {
        return;
    }

    $policy = LeavePolicy::find( $policy_id );

    if ( ! $policy ) {
        return;
    }

    $employees = erp_hr_get_employees( [
        'type'              => $policy->employee_type,
        'department'        => $policy->department_id,
        'location'          => $policy->location_id,
        'designation'       => $policy->designation_id,
        'gender'            => $policy->gender,
        'marital_status'    => $policy->marital,
        'number'            => '-1',
        'no_object'         => true,
    ] );

    if ( count( $employees ) === 0 ) {
        return;
    }

    $entitlement_bg = new \WeDevs\ERP\HRM\LeaveEntitlementBGProcess();

    foreach ( $employees as $employee ) {
        $entitlement_bg->push_to_queue(
            [
                'user_id'       => $employee->user_id,
                'leave_id'      => $policy->leave_id,
                'created_by'    => get_current_user_id(),
                'trn_id'        => $policy->id,
                'trn_type'      => 'leave_policies',
                'day_in'        => $policy->days,
                'day_out'       => 0,
                'description'   => $policy->description != '' ? $policy->description : 'Generated',
                'f_year'        => $policy->f_year,
            ]
        );
    }

    $entitlement_bg->save();

    /*
     * Run the queue, starting with leave entitlements data
     */
    $entitlement_bg->dispatch();

    // erp_bulk_policy_assign( $policy_id );
    // erp_hr_apply_policy_to_employee( $policy );
}

/**
 * Insert a new policy entitlement for an employee
 *
 * @since 0.1
 * @since 1.2.0 Use `erp_get_financial_year_dates` for financial start and end dates
 * @since 1.6.0 updated due to database structure change
 * @since 1.6.5 added employee_type filter
 *
 * @param array $args
 *
 * @return int|object New entitlement id or WP_Error object
 */
function erp_hr_leave_insert_entitlement( $args = [] ) {

    /*
     * Workflow:
     * 1. Check if required fields exists
     * 2. Check if policy already assigned for current user
     * 3. Check if we can assign this employee ie: 60 days waiting period based on joining date -- user trn_id
     * 5. Check if we can assign this policy to this employee based on filters
     * 4. finally add this policy to database.
     */

    $defaults = [
        'user_id'       => 0,
        'leave_id'      => 0,
        'created_by'    => get_current_user_id(),
        'trn_id'        => 0,
        'trn_type'      => 'leave_policies',
        'day_in'        => 0,
        'day_out'       => 0,
        'description'   => '',
        'f_year'        => '',
    ];

    $fields = wp_parse_args( $args, $defaults );

    // sanitize user inputs
    array_walk_recursive( $fields, function ( &$value, $key ) {
        $value = wp_unslash( $value );
        switch ( $key ) {
            case 'user_id':
            case 'leave_id':
            case 'created_by':
            case 'trn_id':
            case 'f_year':
                $value = absint( $value );
                break;

            case 'day_in':
            case 'day_out':
                // can be float value
                if ( (float) $value == $value ) {
                    $value = floatval( $value );
                } else {
                    $value = absint( $value );
                }
                break;

            default:
                $value = sanitize_text_field( $value );
                break;
        }
    } );

    // Check if required fields exists
    if ( ! $fields['user_id'] ) {
        return new WP_Error( 'no-user', esc_attr__( 'No employee provided.', 'erp' ) );
    }

    if ( ! $fields['leave_id'] ) {
        return new WP_Error( 'no-policy', esc_attr__( 'No policy provided.', 'erp' ) );
    }

    if ( ! $fields['trn_id'] ) {
        return new WP_Error( 'no-trn-id', esc_attr__( 'No transaction id is provided.', 'erp' ) );
    }

    if ( ! $fields['trn_type'] ) {
        return new WP_Error( 'no-trn-type', esc_attr__( 'No transaction type is provided.', 'erp' ) );
    }

    if ( ! $fields['f_year'] ) {
        return new WP_Error( 'no-financial-year', esc_attr__( 'No year is provided.', 'erp' ) );
    }

    // Check if policy already assigned for current user - only if transaction type is: leave_policies
    if ( $fields['trn_type'] === 'leave_policies' ) {
        $entitlement = LeaveEntitlement::where( 'user_id', '=', $fields['user_id'] );
        $entitlement->where( 'leave_id', '=', $fields['leave_id'] );
        $entitlement->where( 'trn_id', '=', $fields['trn_id'] );
        $entitlement->where( 'trn_type', '=', 'leave_policies' );
        $entitlement->where( 'f_year', '=', $fields['f_year'] );

        $existing_entitlement = $entitlement->get();

        if ( $existing_entitlement->count() ) {
            return $existing_entitlement->first()->id;
            //return new WP_Error( 'policy-already-assigned', 'policy already assigned. ID: ' . $existing_entitlement->first()->id);
        }

        // Check if we can assign this employee

        // get employee from user id
        $employee = new Employee( $fields['user_id'] );

        // check if this user is a valid employee
        if ( ! $employee->is_employee() ) {
            return new WP_Error( 'invalid-employee-' . $fields['user_id'], esc_attr__( 'Error: Invalid Employee. No employee found with given ID: ', 'erp' ) . $fields['user_id'] );
        }

        // check employee status
        if ( $employee->get_status() !== 'active' ) {
            return new WP_Error( 'invalid-employee-' . $fields['user_id'], esc_attr__( 'Error: Invalid Employee. Employee job status is not active: ', 'erp' ) . $fields['user_id'] );
        }

        // get policy data
        $policy = LeavePolicy::find( $fields['trn_id'] );

        if ( ! $policy ) {
            return new WP_Error( 'invalid-policy-' . $fields['trn_id'], esc_attr__( 'Error: Invalid Policy. No leave policy found with given ID: ', 'erp' ) . $fields['trn_id'] );
        }

        // check policy filter is same as employee filters
        if ( ( $policy->department_id != '-1' && $employee->get_department() != $policy->department_id )
             || ( $policy->designation_id != '-1' && $employee->get_designation() != $policy->designation_id )
             || ( $policy->location_id != '-1' && $employee->get_location() != $policy->location_id )
             || ( $policy->gender != '-1' && $employee->get_gender() != $policy->gender )
             || ( $policy->marital != '-1' && $employee->get_marital_status() != $policy->marital )
             || ( $policy->employee_type != '-1' && $employee->get_type() != $policy->employee_type )
        ) {
            return new WP_Error( 'invalid-employee-' . $fields['user_id'], esc_attr__( 'Error: Invalid Employee. Policy does not match with employee profile.', 'erp' ) );
        }

        if ( $policy->applicable_from_days && $policy->applicable_from_days > 0 ) {
            // get employee joining date and compare it with policy's applicable form date
            if ( $employee->get_hiring_date() ) {
                // get hiring date
                $hiring_date = erp_current_datetime()->modify( $employee->get_hiring_date() )->setTime( 0, 0, 0 );

                // get current date
                $today = erp_current_datetime()->setTime( 0, 0, 0 );

                // check if hiring date in the future
                $interval = date_diff( $hiring_date, $today );

                if ( $interval->invert == 1 ) {
                    return new WP_Error( 'invalid-joining-date', esc_attr__( 'Error: Employee joining date is in the future: ', 'erp' ) . $fields['user_id'] );
                }

                $compare_with = $hiring_date->modify( '+ ' . $policy->applicable_from_days . ' days' );

                if ( $compare_with > $today ) {
                    return new WP_Error( 'invalid-joining-date', esc_attr__( 'Error: Employee is not eligible for this leave policy yet: ', 'erp' ) . $fields['user_id'] );
                }
            } else {
                return new WP_Error( 'invalid-joining-date', esc_attr__( 'Error: Employee joining date is invalid: ', 'erp' ) . $fields['user_id'] );
            }
        }
    }

    $fields = apply_filters( 'erp_hr_leave_before_insert_new_entitlement', $fields );

    // bailout in case of errors
    if ( is_wp_error( $fields ) ) {
        return $fields;
    }

    // add this policy to database.
    $entitlement                = new LeaveEntitlement();
    $entitlement->user_id       = $fields['user_id'];
    $entitlement->leave_id      = $fields['leave_id'];
    $entitlement->created_by    = $fields['created_by'];
    $entitlement->trn_id        = $fields['trn_id'];
    $entitlement->trn_type      = $fields['trn_type'];
    $entitlement->day_in        = $fields['day_in'];
    $entitlement->day_out       = $fields['day_out'];
    $entitlement->description   = $fields['description'];
    $entitlement->f_year        = $fields['f_year'];
    $entitlement->save();

    if ( $fields['trn_type'] === 'leave_policies' ) {
        do_action( 'erp_hr_leave_insert_new_entitlement', $entitlement->id, $fields );
    }

    erp_hrm_purge_cache( [ 'list' => 'leave_entitlement' ] );

    return $entitlement->id;
}

/**
 * Apply `Immediately` type policies on new employee
 *
 * @since 1.2.0
 * @since 1.6.0 updated according to new leave database
 * @since 1.6.5 added employee_type filter
 *
 * @param int $user_id Employee user_id provided by `erp_hr_employee_new` hook
 *
 * @return void
 */
function erp_hr_apply_policy_on_new_employee( $user_id ) {
    // 1. get current financial year
    $f_year = erp_hr_get_financial_year_from_date();

    if ( empty( $f_year ) ) {
        return;
    }

    // 2. get employee information
    $employee = new Employee( $user_id );

    // 3. get policies where automatic policy assign is enabled.
    $policies = LeavePolicy::where( 'apply_for_new_users', 1 )
        ->where( 'f_year', $f_year->id )
        ->get();

    $policies->each( function ( $policy ) use ( $user_id ) {
        $data = [
            'user_id'       => $user_id,
            'leave_id'      => $policy->leave_id,
            'created_by'    => get_current_user_id(),
            'trn_id'        => $policy->id,
            'trn_type'      => 'leave_policies',
            'day_in'        => $policy->days,
            'day_out'       => 0,
            'description'   => $policy->description != '' ? $policy->description : 'Generated',
            'f_year'        => $policy->f_year,
        ];

        $inserted = erp_hr_leave_insert_entitlement( $data );
    } );

    erp_hrm_purge_cache( [ 'list' => 'leave_entitlement' ] );
}

/**
 * Apply `Scheduled` type policies on new employee
 *
 * @since 1.2.0
 * @since 1.6.0
 * @since 1.6.5 added employee type filter
 *
 * @return void
 */
function erp_hr_apply_scheduled_policies() {
    $policies = LeavePolicy::where( 'apply_for_new_users', 1 )->get();

    $policies->each( function ( $policy ) {
        // 1. get all employee
        $employees = erp_hr_get_employees( [
            'type'              => $policy->employee_type,
            'department'        => $policy->department_id,
            'location'          => $policy->location_id,
            'designation'       => $policy->designation_id,
            'gender'            => $policy->gender,
            'marital_status'    => $policy->marital,
            'number'            => '-1',
            'no_object'         => true,
        ] );

        if ( ! count( $employees ) ) {
            return;
        }

        $employees = wp_list_pluck( $employees, 'display_name', 'user_id' );

        // 2. get already entitled users and remove them from queue
        if ( $policy->entitlements ) {
            foreach ( $policy->entitlements as $entitlement ) {
                if ( array_key_exists( $entitlement['user_id'], $employees ) ) {
                    unset( $employees[ $entitlement['user_id'] ] );
                }
            }
        }

        if ( count( $employees ) ) {
            $entitlement_bg = new \WeDevs\ERP\HRM\LeaveEntitlementBGProcess();

            foreach ( $employees as $employee_id => $employee_name ) {
                $entitlement_bg->push_to_queue(
                    [
                        'user_id'       => $employee_id,
                        'leave_id'      => $policy->leave_id,
                        'created_by'    => get_current_user_id(),
                        'trn_id'        => $policy->id,
                        'trn_type'      => 'leave_policies',
                        'day_in'        => $policy->days,
                        'day_out'       => 0,
                        'description'   => $policy->description != '' ? $policy->description : 'Generated',
                        'f_year'        => $policy->f_year,
                    ]
                );
            }

            $entitlement_bg->save();

            /*
             * Run the queue, starting with leave entitlements data
             */
            $entitlement_bg->dispatch();
        }

        //erp_hr_apply_policy_to_employee( $policy );
    } );
}

/**
 * Insert a leave holiday
 *
 * @since 0.1
 *
 * @param array $args
 *
 * @return int [$holiday_id]
 */
function erp_hr_leave_insert_holiday( $args = [] ) {
    $defaults = [
        'id'          => null,
        'title'       => '',
        'start'       => current_time( 'mysql' ),
        'end'         => '',
        'description' => '',
    ];

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

    $holiday = new LeaveHoliday();

    erp_hrm_purge_cache( [ 'list' => 'leave_holiday' ] );

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
 * @since 1.6.0 updated code to reflect new leave data structure
 *
 * @param array $args
 *
 * @return array
 */
function erp_hr_leave_get_policies( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'         => 99,
        'offset'         => 0,
        'orderby'        => 'id',
        'order'          => 'ASC',
        'employee_type'  => '',
        'department_id'  => '',
        'location_id'    => '',
        'designation_id' => '',
        'gender'         => '',
        'marital'        => '',
        'f_year'         => '0',
    ];

    $args = wp_parse_args( $args, $defaults );

    // sanitize inputs
    array_walk_recursive( $args, function ( &$value, $key ) {
        $value = wp_unslash( $value );
        switch ( $key ) {
            case 'number':
            case 'offset':
            case 'department_id':
            case 'location_id':
            case 'designation_id':
            case 'f_year':
                $value = intval( $value );
                break;

            case 'orderby':
            case 'order':
            case 'gender':
            case 'marital':
                $value = sanitize_text_field( $value );
                break;

            default:
                $value = sanitize_text_field( $value );
                break;
        }
    } );

    $last_changed = erp_cache_get_last_changed( 'hrm', 'leave_policy' );
    $cache_key    = 'erp-get-policies-' . md5( serialize( $args ) ) . ": $last_changed";
    $policies     = wp_cache_get( $cache_key, 'erp' );

    $total_policies_cache_key = 'erp-policies-count-' . md5( serialize( $args ) ) . ": $last_changed";

    $total_row_found = wp_cache_get( $total_policies_cache_key, 'erp' );

    if ( false === $policies ) {
        $policies = LeavePolicy::select( \WeDevs\ORM\Eloquent\Facades\DB::raw( 'SQL_CALC_FOUND_ROWS *' ) )
        ->skip( $args['offset'] )
        ->take( $args['number'] );

        // If filtered by name, Get the ID of this leave type by name and do ordering
        if ( $args['orderby'] === 'name' ) {
            $policies = $policies->join( "{$wpdb->prefix}erp_hr_leaves as leave", 'leave.id', '=', "{$wpdb->prefix}erp_hr_leave_policies.leave_id" )
                        ->orderBy( 'leave.name', $args['order'] );
        } else {
            $policies = $policies->orderBy( $args['orderby'], $args['order'] );
        }

        if ( $args['department_id'] ) {
            $policies->where( 'department_id', '=', $args['department_id'] );
        }

        if ( $args['location_id'] ) {
            $policies->where( 'location_id', '=', $args['location_id'] );
        }

        if ( $args['designation_id'] ) {
            $policies->where( 'designation_id', '=', $args['designation_id'] );
        }

        if ( $args['gender'] ) {
            $policies->where( 'gender', '=', $args['gender'] );
        }

        if ( $args['marital'] ) {
            $policies->where( 'marital', '=', $args['marital'] );
        }

        if ( $args['f_year'] ) {
            $policies->where( 'f_year', '=', $args['f_year'] );
        }

        if ( $args['employee_type'] ) {
            $policies->where( 'employee_type', '=', $args['employee_type'] );
        }

        $policies = $policies->get();

        $total_row_found = absint( $wpdb->get_var( 'SELECT FOUND_ROWS()' ) );
        wp_cache_set( $total_policies_cache_key, $total_row_found, 'erp' );

        $formatted_data = [];

        $employee_types = erp_hr_get_employee_types();

        foreach ( $policies as $key => $policy ) {
            $department    = empty( $policy->department ) ? esc_attr__( 'All', 'erp' ) : $policy->department->title;
            $designation   = empty( $policy->designation ) ? esc_attr__( 'All', 'erp' ) : $policy->designation->title;
            $gender        = $policy->gender == '-1' ? esc_attr__( 'All', 'erp' ) : ucwords( $policy->gender );
            $marital       = $policy->marital == '-1' ? esc_attr__( 'All', 'erp' ) : ucwords( $policy->marital );
            $location      = $policy->location_id == '-1' ? esc_attr__( 'All', 'erp' ) : $policy->location->name;
            $employee_type = array_key_exists( $policy->employee_type, $employee_types ) ? $employee_types[ $policy->employee_type ] : __( 'All', 'erp' );

            $formatted_data[ $key ]['id']             = $policy->id;
            $formatted_data[ $key ]['leave_id']       = $policy->leave_id;
            $formatted_data[ $key ]['name']           = $policy->leave->name;
            $formatted_data[ $key ]['description']    = $policy->description;
            $formatted_data[ $key ]['days']           = $policy->days;
            $formatted_data[ $key ]['color']          = $policy->color;
            $formatted_data[ $key ]['department_id']  = $policy->department_id;
            $formatted_data[ $key ]['department']     = $department;
            $formatted_data[ $key ]['designation_id'] = $policy->designation_id;
            $formatted_data[ $key ]['designation']    = $designation;
            $formatted_data[ $key ]['location']       = $location;
            $formatted_data[ $key ]['f_year']         = $policy->financial_year->fy_name;
            $formatted_data[ $key ]['gender']         = $gender;
            $formatted_data[ $key ]['marital']        = $marital;
            $formatted_data[ $key ]['employee_type']  = $employee_type;
        }

        $policies = erp_array_to_object( $formatted_data );
        wp_cache_set( $cache_key, $policies, 'erp' );
    }

    return [
		'data' => $policies,
		'total' => $total_row_found,
	];
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
    return LeavePolicy::where( 'name', $name )->first();
}

/**
 * Fetch a leave policy by policy id
 *
 * @since 0.1
 * @since 1.2.0 Return Eloquent Leave_Policies model
 *
 * @param int $policy_id
 *
 * @return \stdClass
 */
function erp_hr_leave_get_policy( $policy_id ) {
    return LeavePolicy::find( $policy_id );
}

/**
 * Count total leave policies
 *
 * @since 0.1
 *
 * @return int
 */
function erp_hr_count_leave_policies() {
    return LeavePolicy::count();
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
    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'created_at',
        'order'   => 'DESC',
    ];

    $args = wp_parse_args( $args, $defaults );

    $holiday = new LeaveHoliday();

    $holiday_results = $holiday->select( [ 'id', 'title', 'start', 'end', 'description' ] );

    $holiday_results = erp_hr_holiday_filter_param( $holiday_results, $args );

    //if not search then execute nex code
    if ( isset( $args['id'] ) && ! empty( $args['id'] ) ) {
        $id              = intval( $args['id'] );
        $holiday_results = $holiday_results->where( 'id', '=', "$id" );
    }

    $last_changed    = erp_cache_get_last_changed( 'hrm', 'leave_holiday' );
    $cache_key       = 'erp-get-holidays-' . md5( serialize( $args ) ) . "  : $last_changed";
    $cache_key_count = 'erp-holidays-count-' . md5( serialize( $args ) ) . ": $last_changed";

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
        wp_cache_set( $cache_key_count, count( $holidays ), 'erp' );
    }

    return $holidays;
}

/**
 * Count total holidays
 *
 * @since 0.1
 * @since 1.8.2 add caching system
 *
 * @return \stdClass
 */
function erp_hr_count_holidays( $args ) {
    $last_changed   = erp_cache_get_last_changed( 'hrm', 'leave_holiday' );
    $cache_key      = 'erp-holidays-count-' . md5( serialize( $args ) ) . ": $last_changed";
    $holidays_count = wp_cache_get( $cache_key, 'erp' );

    if ( false === $holidays_count ) {
        $holiday = new LeaveHoliday();
        $holiday = erp_hr_holiday_filter_param( $holiday, $args );

        $holidays_count = $holiday->count();
        wp_cache_set( $cache_key, $holidays_count, 'erp' );
    }

    return $holidays_count;
}

/**
 * Filter parameter for holidays
 *
 * @since 0.1
 *
 * @param object $holiday
 * @param array  $args
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
    erp_hrm_purge_cache( [ 'list' => 'leave_holiday' ] );

    if ( is_array( $holidays_id ) ) {
        foreach ( $holidays_id as $key => $holiday_id ) {
            do_action( 'erp_hr_leave_holiday_delete', $holiday_id );
        }

        LeaveHoliday::destroy( $holidays_id );
    } else {
        do_action( 'erp_hr_leave_holiday_delete', $holidays_id );

        return LeaveHoliday::find( $holidays_id )->delete();
    }
}

/**
 * Get policies as formatted for dropdown
 *
 * @param array $args data to filter leave policies
 *
 * @return array
 *
 * @since 0.1
 * @since 1.6.0 added $args argument
 */
function erp_hr_leave_get_policies_dropdown_raw( $args = [] ) {
    $data = erp_hr_leave_get_policies( $args );

    return wp_list_pluck( $data['data'], 'name', 'id' );
}

/**
 * Delete a policy
 *
 * @since 0.1
 * @since 1.6.0
 *
 * @param int|array $policy_ids
 *
 * @return void
 */
function erp_hr_leave_policy_delete( $policy_ids ) {

    // Check permission
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    if ( ! is_array( $policy_ids ) ) {
        $policy_ids = [ $policy_ids ];
    }

    $policies = LeavePolicy::find( $policy_ids );

    erp_hrm_purge_cache( [ 'list' => 'leave_policy' ] );

    $policies->each( function ( $policy ) {
        if ( $policy->entitlements ) {
            foreach ( $policy->entitlements as $entitlement ) {
                // check entitlement employee status
                $employee = new Employee( $entitlement->user_id );

                if ( $policy->employee_type !== '-1' && $policy->employee_type != $employee->get_type() ) {
                    continue;
                }

                if ( $entitlement->leave_requests ) {
                    foreach ( $entitlement->leave_requests as $request ) {
                        if ( $request->approval_status ) {
                            foreach ( $request->approval_status as $status ) {
                                if ( $status->entitlements ) {
                                    foreach ( $status->entitlements as $entl ) {
                                        $entl->delete();
                                    }
                                }
                                $status->delete();
                            }
                        }

                        if ( $request->details ) {
                            foreach ( $request->details as $detail ) {
                                $detail->delete();
                            }
                        }

                        if ( $request->unpaid ) {
                            $request->unpaid->delete();
                        }

                        $request->delete();
                    }
                }
                $entitlement->delete();
            }
        }
        $policy->delete();

        do_action( 'erp_hr_leave_policy_delete', $policy );
    } );
}

/**
 * Get assign policies according to employee entitlement
 *
 * @since 0.1
 * @since 1.6.0 changed according to new db structure.
 *
 * @param int $employee_id
 *
 * @return bool|array
 */
function erp_hr_get_assign_policy_from_entitlement( $employee_id, $date = null ) {
    $f_year = erp_hr_get_financial_year_from_date( $date );

    if ( empty( $f_year ) ) {
        return false;
    }

    global $wpdb;

    $entitlement_table = "{$wpdb->prefix}erp_hr_leave_entitlements";
    $leave_table       = "{$wpdb->prefix}erp_hr_leaves";

    $policies = LeaveEntitlement::select( $entitlement_table . '.id', $leave_table . '.name' )
        ->leftjoin( $leave_table, $entitlement_table . '.leave_id', '=', $leave_table . '.id' )
        ->where( $entitlement_table . '.trn_type', '=', 'leave_policies' )
        ->where( $entitlement_table . '.user_id', '=', $employee_id )
        ->where( $entitlement_table . '.f_year', '=', $f_year->id )
        ->get()
        ->toArray();

    if ( is_array( $policies ) && ! empty( $policies ) ) {
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
 * @since 1.6.0
 *
 * @param array $args
 *
 * @return integet request_id
 */
function erp_hr_leave_insert_request( $args = [] ) {
    global $wpdb;

    $defaults = [
        'user_id'      => 0,
        'leave_policy' => 0,
        'start_date'   => erp_current_datetime()->getTimestamp(),
        'end_date'     => erp_current_datetime()->getTimestamp(),
        'reason'       => '',
        'status'       => 0,
    ];

    $args = wp_parse_args( $args, $defaults );

    if ( ! intval( $args['user_id'] ) ) {
        return new WP_Error( 'no-employee', esc_attr__( 'No employee ID provided.', 'erp' ) );
    }

    if ( ! intval( $args['leave_policy'] ) ) {
        return new WP_Error( 'no-policy', esc_attr__( 'No leave policy provided.', 'erp' ) );
    }

    $period = erp_hr_get_work_days_between_dates( $args['start_date'], $args['end_date'], $args['user_id'] );

    if ( is_wp_error( $period ) ) {
        return $period;
    }

    // get balance
    $entitlement = LeaveEntitlement::find( $args['leave_policy'] );

    if ( ! $entitlement ) {
        return new WP_Error( 'no-entitlement', esc_attr__( 'No entitlement found with given id.', 'erp' ) );
    }

    // validate start and end date
    if ( $args['start_date'] > $args['end_date'] ) {
        return new WP_Error( 'invalid-dates', esc_attr__( 'Invalid date range.', 'erp' ) );
    }

    // check for unpaid leave
    if ( get_option( 'enable_extra_leave', 'no' ) !== 'yes' ) {
        $is_policy_valid = erp_hrm_is_valid_leave_duration( $args['start_date'], $args['end_date'], $args['leave_policy'], $args['user_id'] );

        if ( ! $is_policy_valid ) {
            return new WP_Error( 'invalid-dates', esc_attr__( 'Sorry! You do not have any leave left under this leave policy.', 'erp' ) );
        }
    }

    // check start_date and end_date are in the same f_year
    $f_year_start = erp_current_datetime()->setTimestamp( $entitlement->financial_year->start_date )->setTime( 0, 0, 0 )->format( 'Y-m-d H:i:s' );
    $f_year_end   = erp_current_datetime()->setTimestamp( $entitlement->financial_year->end_date )->setTime( 23, 59, 59 )->format( 'Y-m-d H:i:s' );

    if ( ( $args['start_date'] < $f_year_start || $args['start_date'] > $f_year_end ) || ( $args['end_date'] < $f_year_start || $args['end_date'] > $f_year_end ) ) {
        return new WP_Error( 'invalid-dates', sprintf( esc_attr__( 'Invalid leave duration. Please apply between %1$s and %2$s.', 'erp' ), erp_format_date( $f_year_start ), erp_format_date( $f_year_end ) ) );
    }

    // handle overlapped leaves
    $leave_record_exist = erp_hrm_is_leave_recored_exist_between_date( $args['start_date'], $args['end_date'], $args['user_id'], $entitlement->f_year );

    if ( $leave_record_exist ) {
        return new WP_Error( 'invalid-dates', esc_attr__( 'Existing Leave Record found within selected range!', 'erp' ) );
    }

    // prepare the periods
    $leaves = [];

    if ( $period['days'] ) {
        foreach ( $period['days'] as $date ) {
            if ( class_exists( '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module' ) && get_option( 'erp_pro_sandwich_leave', '' ) !== 'yes' && ! $date['count'] ) { // skip if holiday or not working day
                continue;
            }

            $leaves[] = [
                'date'          => $date['date'],
                'length_hours'  => '08:00:00',
                'length_days'   => '1.00',
                'start_time'    => '00:00:00',
                'end_time'      => '00:00:00',
                'duration_type' => 1,
            ];
        }
    }

    if ( $leaves ) {
        $request = apply_filters( 'erp_hr_leave_new_args', [
            'user_id'              => $args['user_id'],
            'leave_id'             => $entitlement->leave_id,
            'leave_entitlement_id' => $entitlement->id,
            'day_status_id'        => '1',
            //'days'       => count( $leaves ),
            'days'        => absint( $period['total'] ),
            'start_date'  => erp_current_datetime()->modify( $args['start_date'] )->setTime( 0, 0, 0 )->getTimestamp(),
            'end_date'    => erp_current_datetime()->modify( $args['end_date'] )->setTime( 0, 0, 0 )->getTimestamp(),
            'reason'      => wp_kses_post( $args['reason'] ),
            'last_status' => '2',
            'created_by'  => get_current_user_id(),
            'created_at'  => erp_current_datetime()->getTimestamp(),
            'updated_at'  => erp_current_datetime()->getTimestamp(),
        ] );

        if ( $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_requests', $request ) ) {
            $request_id = $wpdb->insert_id;

            do_action( 'erp_hr_leave_new', $request_id, $request, $leaves );

            erp_hrm_purge_cache( [ 'list' => 'leave_request' ] );

            return $request_id;
        }
    }

    return false;
}

/**
 * Fetch a single request
 *
 * @param int $request_id
 *
 * @return object
 */
function erp_hr_get_leave_request( $request_id ) {
    $request_data = erp_hr_get_leave_requests( [ 'request_id' => $request_id ] );

    if ( isset( $request_data['data'] ) && is_array( $request_data['data'] ) && ! empty( $request_data['data'] ) ) {
        return $request_data['data'][0];
    }

    return null;
}

/**
 * Fetch the leave requests
 *
 * @since 0.1
 * @since 1.6.0
 *
 * @param array $args
 * @param boolean $cached
 *
 * @return array
 */
function erp_hr_get_leave_requests( $args = [], $cached = true ) {
    global $wpdb;

    $defaults = [
        'request_id'     => 0,
        'user_id'        => 0,
        'policy_id'      => 0,
        'status'         => '',
        'year'           => 0,
        'f_year'         => 0,
        'number'         => 20,
        'offset'         => 0,
        'orderby'        => 'created_at',
        'order'          => 'DESC',
        'start_date'     => '',
        'end_date'       => '',
        'department_id'  => 0,
        'designation_id' => 0,
        'lead'           => 0,
        's'              => '',
        'created_at'     => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    array_walk_recursive( $args, function ( &$value, $key ) {
        $value = wp_unslash( $value );
        switch ( $key ) {
            case 'request_id':
            case 'user_id':
            case 'policy_id':
            case 'f_year':
            case 'offset':
            case 'year':
            case 'lead':
            case 'calendar':
                $value = absint( $value );
                break;

            case 'number':
            case 'department_id':
            case 'designation_id':
                $value = intval( $value );
                break;

            default:
                $value = sanitize_text_field( trim( $value ) );
                break;
        }
    } );

    // fix orderby parameter
    if ( $args['orderby'] == 'created_on' ) {
        $args['orderby'] = 'request.created_at';
    } elseif ( in_array( $args['orderby'], [ 'start_date', 'end_date', 'name', 'created_at' ] ) ) {
        $args['orderby'] = 'request.' . $args['orderby'];
    } elseif ( $args['orderby'] == 'name' ) {
        $args['orderby'] = 'u.' . $args['orderby'];
    }elseif ( $args['orderby'] == 'days' ) {
        $args['orderby'] = 'request.days';
    }elseif ( $args['orderby'] == 'policy' ) {
        $args['orderby'] = 'policy.id';
    }elseif ( $args['orderby'] == 'last_status' ) {
        $args['orderby'] = 'request.last_status';
    }elseif ( $args['orderby'] == 'available' ) {
        $args['orderby'] = 'request.leave_entitlement_id';
    }

    $last_changed = erp_cache_get_last_changed( 'hrm', 'leave_request' );
    $cache_key    = 'erp_hr_leave_requests_' . md5( serialize( $args ) ) . ": $last_changed";
    $requests     = wp_cache_get( $cache_key, 'erp' );

    if ( false !== $requests && true === $cached ) {
        return $requests;
    }

    //return erp_array_to_object( $formatted_data );

    $leave_requests_table = $wpdb->prefix . 'erp_hr_leave_requests';
    $tables               = " FROM $leave_requests_table as request";

    $fields = 'SELECT SQL_CALC_FOUND_ROWS request.id, u.display_name, request.created_at as created_at';
    $fields .= ', policy.color';

    $join = " LEFT JOIN {$wpdb->users} AS u ON u.ID = request.user_id";
    $join .= " LEFT JOIN {$wpdb->prefix}erp_hr_leave_entitlements AS entl ON request.leave_entitlement_id = entl.id";
    $join .= " LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS policy ON policy.id = entl.trn_id";

    $where = ' WHERE 1=1';
    $where .= " AND entl.trn_type = 'leave_policies'";

    $groupby = '';
    $orderby = $wpdb->prepare( " ORDER BY %s %s", $args['orderby'], $args['order'] );
    $orderby = esc_sql( str_replace( '\'', '', $orderby ) );
    $offset = absint( $args['offset'] );
    $number = absint( $args['number'] );
    $limit  = $args['number'] == '-1' ? '' : $wpdb->prepare(" LIMIT %d, %d", $offset, $number);

    // filter by request_id
    if ( $args['lead'] ) {
        $args['user'] = 0;

        // get all user ids for this lead
        $args['users'] = erp_hr_get_dept_lead_subordinate_employees( $args['lead'] );

        $where .= $wpdb->prepare( " AND request.user_id in (%s)", implode( ', ', $args['users'] ) );
    }

    if ( $args['department_id'] && $args['designation_id'] ) {
        $args['user'] = 0;
        $users        = \WeDevs\ERP\HRM\Models\Employee::select( 'user_id' )
            ->where( 'department', $args['department_id'] )
            ->where( 'designation', $args['designation_id'] );

        if ( $users->count() ) {
            $user_ids = $users->pluck( 'user_id' )->toArray();
            $where .= $wpdb->prepare( " AND request.user_id in (%s)", implode( ', ', $user_ids ) );
        }
    } elseif ( $args['department_id'] ) {
        $args['user'] = 0;
        $users        = \WeDevs\ERP\HRM\Models\Employee::select( 'user_id' )
            ->where( 'department', $args['department_id'] );

        if ( $users->count() ) {
            $user_ids = $users->pluck( 'user_id' )->toArray();
            $where .= $wpdb->prepare( " AND request.user_id in (%s)", implode( ', ', $user_ids ) );
        }
    } elseif ( $args['designation_id'] ) {
        $args['user'] = 0;
        $users        = \WeDevs\ERP\HRM\Models\Employee::select( 'user_id' )
            ->where( 'designation', $args['designation_id'] );

        if ( $users->count() ) {
            $user_ids = $users->pluck( 'user_id' )->toArray();
            $where .= $wpdb->prepare( " AND request.user_id in (%s)", implode( ', ', $user_ids ) );

        }
    }

    if ( is_numeric( $args['request_id'] ) && $args['request_id'] > 0 ) {
        $where .= $wpdb->prepare( " AND request.id = %d", $args['request_id'] );
    }

    // filter by name
    if ( ! empty( $args['s'] ) ) {
        $like_s = '%' . $wpdb->esc_like( $args['s'] ) . '%';
        $where .= $wpdb->prepare( " AND u.display_name like %s ", $like_s );
    }

    // filter by leave status (approved, pending, rejected)
    if ( is_array( $args['status'] ) && ! empty( $args['status'] ) ) {
        $placeholders = implode( ', ', array_fill( 0, count( $args['status'] ), '%s' ) );
        $where .= $wpdb->prepare( " AND request.last_status IN ($placeholders)", $args['status'] );
    } elseif ( ! empty( $args['status'] ) && $args['status'] !== 'all' ) {
        $where .= $wpdb->prepare( " AND request.last_status = %d", absint( $args['status'] ) );
    }

    if ( $args['user_id'] ) {
        $where .= $wpdb->prepare( " AND request.user_id = %d", $args['user_id'] );
    }

    if ( $args['policy_id'] ) { // @since 1.6.0 policy_id is equal to leave_id
        $where .= $wpdb->prepare( " AND request.leave_id = %d", $args['policy_id'] );
    }

    if ( $args['year'] ) {
        $from_date_string = $args['year'] . '-01-01 00:00:00';
        $from_date        = erp_current_datetime();
        $from_date        = $from_date->modify( $from_date_string );

        $to_date_string = $args['year'] . '-12-31 23:59:59';
        $to_date        = erp_current_datetime();
        $to_date        = $to_date->modify( $to_date_string );

        $where .= $wpdb->prepare( " AND request.start_date >= %d AND request.end_date <= %d", $from_date->getTimestamp(), $to_date->getTimestamp() );
    }

    if ( $args['f_year'] ) {
        $where .= $wpdb->prepare( " AND entl.f_year = %d", $args['f_year'] );
    }

    if ( $args['start_date'] && $args['end_date'] ) {
        //dates can be timestamps
        if ( is_numeric( $args['start_date'] ) ) {
            $from_date = erp_current_datetime();
            $from_date = $from_date->setTimestamp( $args['start_date'] );
        } else {
            $from_date = erp_current_datetime();
            $from_date = $from_date->modify( $args['start_date'] )->setTime( 0, 0, 0 );
        }

        // dates can be timstamps
        if ( is_numeric( $args['end_date'] ) ) {
            $to_date = erp_current_datetime();
            $to_date = $to_date->setTimestamp( $args['end_date'] );
        } else {
            $to_date = erp_current_datetime();
            $to_date = $to_date->modify( $args['end_date'] )->setTime( 23, 59, 59 );
        }

        $where .= $wpdb->prepare( " AND request.start_date >= %d AND request.start_date <= %d", $from_date->getTimestamp(), $to_date->getTimestamp() );
    }

    $query = $fields . $tables . $join . $where . $orderby . $limit;

    $requests = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    $total_row_found = absint( $wpdb->get_var( 'SELECT FOUND_ROWS()' ) );

    $formatted_data = [];

    if ( is_array( $requests ) && ! empty( $requests ) ) {
        $available_leaves = [];

        foreach ( $requests as $single_request ) {
            $request = LeaveRequest::find( $single_request['id'] );

            // get available days
            if ( ! isset( $available_leaves[ $request->user_id ] ) || ! array_key_exists( $request->leave->id, $available_leaves[ $request->user_id ] ) ) {
                $policy_data = erp_hr_leave_get_balance_for_single_entitlement( $request->leave_entitlement_id );

                if ( ! is_array( $policy_data ) || empty( $policy_data ) ) {
                    continue;
                }

                $available_leaves[ $request->user_id ][ $request->leave->id ] = $policy_data;
            }

            $available = isset( $available_leaves[ $request->user_id ][ $request->leave->id ] )
                ? $available_leaves[ $request->user_id ][ $request->leave->id ]['available'] : 0;
            $extra = isset( $available_leaves[ $request->user_id ][ $request->leave->id ] )
                ? $available_leaves[ $request->user_id ][ $request->leave->id ]['extra_leave'] : 0;
            $spent = isset( $available_leaves[ $request->user_id ][ $request->leave->id ] )
                ? $available_leaves[ $request->user_id ][ $request->leave->id ]['spent'] : 0;
            $entitlement = isset( $available_leaves[ $request->user_id ][ $request->leave->id ] )
                ? $available_leaves[ $request->user_id ][ $request->leave->id ]['entitlement'] : 0;

            $temp_data                   = [];
            $temp_data['id']             = $request->id;
            $temp_data['user_id']        = $request->user_id;
            $temp_data['name']           = $single_request['display_name'];
            $temp_data['display_name']   = $single_request['display_name'];
            $temp_data['leave_id']       = $request->leave_id;
            $temp_data['policy_name']    = $request->leave->name;
            $temp_data['start_date']     = $request->start_date;
            $temp_data['end_date']       = $request->end_date;
            $temp_data['days']           = $request->days;
            $temp_data['entitlement']    = $entitlement;
            $temp_data['available']      = $available;
            $temp_data['extra_leaves']   = $extra;
            $temp_data['spent']          = $spent;
            $temp_data['status']         = $request->last_status;
            $temp_data['reason']         = $request->reason;
            $temp_data['message']        = $request->latest_approval_status ? $request->latest_approval_status->message : '';
            $temp_data['color']          = isset( $single_request['color'] ) ? $single_request['color'] : '';
            $temp_data['day_status_id']  = $request->day_status_id;
            $temp_data['f_year']         = $request->entitlement->f_year;
            $temp_data['created_at']     = $single_request['created_at'];

            $formatted_data[] = $temp_data;
        }
    }

    $requests_data = [
		'data' => erp_array_to_object( $formatted_data ),
		'total' => $total_row_found,
		'sql' => $query,
	];
    wp_cache_set( $cache_key, $requests_data, 'erp', HOUR_IN_SECONDS );
    return $requests_data;
}

/**
 * Get leave requests count
 *
 * @since 0.1
 * @since 1.6.0 updated according to new db strucutre
 *
 * @return array
 */
function erp_hr_leave_get_requests_count( $f_year ) {
    global $wpdb;

    $statuses = erp_hr_leave_request_get_statuses();

    $cache_key = 'erp-hr-leave-request-counts';
    $counts    = wp_cache_get( $cache_key, 'erp' );

    if ( false === $counts ) {
        $counts   = [];
        $total    = 0;

        foreach ( $statuses as $status => $label ) {
            $counts[ $status ] = [
				'count' => 0,
				'label' => $label,
			];
        }

        $total_leave_count = $wpdb->get_results( $wpdb->prepare( "
            SELECT rq.last_status as id, COUNT( rq.last_status ) AS count
            FROM {$wpdb->prefix}erp_hr_leave_requests AS rq
            LEFT JOIN {$wpdb->prefix}erp_hr_leave_entitlements AS en ON en.id = rq.leave_entitlement_id
            WHERE en.f_year = %d
            GROUP BY last_status
            ", $f_year ), ARRAY_A
        );

        if ( is_array( $total_leave_count ) && ! empty( $total_leave_count ) ) {
            foreach ( $total_leave_count as $item ) {
                $counts[ $item['id'] ]['count'] = $item['count'];
                $total += $item['count'];
            }
        }

        $counts['all']['count'] = $total;

        if ( ! class_exists( '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module' ) ) {
            if ( isset( $counts['4'] ) ) {
                unset( $counts['4'] );
            }
        } elseif ( class_exists( '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module' ) && get_option( 'erp_pro_multilevel_approval' ) !== 'yes' ) {
            if ( isset( $counts['4'] ) ) {
                unset( $counts['4'] );
            }
        }

        wp_cache_set( $cache_key, $counts, 'erp' );
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
 * @param int    $request_id
 * @param string $status
 *
 * @return object Eloquent Leave_request model
 */
function erp_hr_leave_request_update_status( $request_id, $status, $comments = '' ) {
    if ( current_user_can( 'erp_leave_manage' ) === false && erp_hr_is_current_user_dept_lead() === false ) {
        return new WP_Error( 'no-permission', esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $request  = LeaveRequest::find( $request_id );
    $old_data = $request->toArray();

    if ( empty( $request ) ) {
        return new WP_Error( 'no-request-found', __( 'Invalid leave request', 'erp' ) );
    }

    if ( erp_hr_is_current_user_dept_lead() && current_user_can( 'erp_leave_manage' ) === false ) {
        $is_valid = erp_hr_match_user_dept_lead_with_current_user( $request->user_id );

        if ( ! $is_valid ) {
            return new WP_Error( 'no-permission', esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
        }
    }

    $status = absint( $status );

    $old_leave_status = isset( $request->latest_approval_status->approval_status_id ) ? absint( $request->latest_approval_status->approval_status_id ) : 2; // by default status is pending

    // return if old and new status are same.
    if ( $request->last_status == $status ) {
        return new WP_Error( 'no-leave-status', __( 'Invalid leave status. Please check your input.', 'erp' ) );
    }

    // check if reject reason exist
    if ( $status === 3 && $comments === '' ) {
        return new WP_Error( 'no-leave-reason', __( 'Please provide a reject reason for given leave request.', 'erp' ) );
    }

    // get entitlements
    if ( ! $request->entitlement->id ) {
        return new WP_Error( 'invalid-entitlement', __( 'No Entitlement found for given request.', 'erp' ) );
    }

    // past records can't be edited
    $financial_years = [];

    // get current financial year
    $current_f_year = erp_hr_get_financial_year_from_date();

    if ( null === $current_f_year ) {
        return new WP_Error( 'invalid-f_year', __( 'No current leave year found.', 'erp' ) );
    }

    foreach ( FinancialYear::all() as $f_year ) {
        if ( $f_year['start_date'] < $current_f_year->start_date ) {
            continue;
        }
        $financial_years[ $f_year['id'] ] = $f_year['fy_name'];
    }

    if ( ! array_key_exists( $request->entitlement->f_year, $financial_years ) ) {
        return new WP_Error( 'invalid-entitlement', __( 'Error: You can not modify past leave year requests.', 'erp' ) );
    }

    do_action( 'erp_hr_leave_request_before_process', $request_id, $status, $comments );

    // approval status table data
    $approval_status_data = [
        'leave_request_id'      => $request_id,
        'approval_status_id'    => $status,
        'approved_by'           => get_current_user_id(),
    ];

    // leave entitlement table data
    $entitlement_data = [
        'user_id'       => $request->user_id,
        'leave_id'      => $request->leave_id,
        'created_by'    => get_current_user_id(),
        'trn_id'        => 0,
        'trn_type'      => 'leave_approval_status',
        'description'   => '',
        'day_in'        => 0,
        'day_out'       => 0,
        'f_year'        => 0,
    ];

    // unpaid leave table data
    $unpaid_leave_data = [
        'leave_id'                  => $request->leave_id,
        'leave_request_id'          => $request_id,
        'leave_approval_status_id'  => 0,
        'user_id'                   => $request->user_id,
        'days'                      => 0,
        'f_year'                    => $request->entitlement->f_year,
    ];

    switch ( $request->last_status ) {
        case 1: // approved
            if ( $status === 3 ) { // reject this request
                // 1. Get latest approval_status_id for current request
                if ( ! $request->latest_approval_status->id ) {
                    return new WP_Error( 'no-approval-status', esc_attr__( 'Invalid Request: No previous records found for given request.' ) );
                }
                $old_approval_status_id = $request->latest_approval_status->id;

                // 2. Add new approval status record
                $approval_status_data['message'] = $comments;
                $approval_status                 = LeaveApprovalStatus::create( $approval_status_data );

                // 3. Add new leave entitlement record
                $entitlement_data['day_in']      = $request->days;
                $entitlement_data['f_year']      = $request->entitlement->f_year;
                $entitlement_data['trn_id']      = $approval_status->id;
                $entitlement_data['description'] = 'Rejected';
                $leave_entitlement               = LeaveEntitlement::create( $entitlement_data );

                // 4. Delete data from leave request details table
                $deleted = LeaveRequestDetail::where( 'leave_request_id', '=', $request->id )->delete();

                // 5. Delete data from unpaid leave table
                $deleted = LeavesUnpaid::where( 'leave_request_id', '=', $request->id )->delete();

                // 6. Delete data from entitlement table for trn_type = unpaid leave
                $deleted = LeaveEntitlement::where( 'trn_id', '=', $old_approval_status_id )
                    ->where( 'trn_type', '=', 'unpaid_leave' )->delete();
            }
            break;

        case 2: // pending
        case 3: // rejected
        case 4: // forwarded
            if ( $status === 1 ) { // approve this request
                // 0. check if we are in the same financial year
                $f_year_start = $request->entitlement->financial_year->start_date;
                $f_year_end   = $request->entitlement->financial_year->end_date;

                if ( ( $request->start_date < $f_year_start || $request->start_date > $f_year_end ) || ( $request->end_date < $f_year_start || $request->end_date > $f_year_end ) ) {
                    return new WP_Error( 'invalid-date-range', sprintf( esc_attr__( 'Invalid leave duration. Please apply between %1$s and %2$s.', 'erp' ), erp_format_date( $f_year_start ), erp_format_date( $f_year_end ) ) );
                }

                // 1. check if we got same date range already approved
                $leave_exist = erp_hrm_is_leave_recored_exist_between_date( $request->start_date, $request->end_date, $request->user_id, $request->entitlement->f_year );

                if ( $leave_exist ) {
                    return new WP_Error( 'leave-exist', esc_attr__( 'Approved leave request found within given range!', 'erp' ) );
                }

                $balance = erp_hr_leave_get_balance_for_single_entitlement( $request->entitlement->id );

                if ( empty( $balance ) ) {
                    return new WP_Error( 'invalid-entitlement-id', esc_attr__( 'No entitlement found for given request. Please check your input.', 'erp' ) );
                }

                $work_days = erp_hr_get_work_days_between_dates( $request->start_date, $request->end_date, $request->user_id );

                if ( is_wp_error( $work_days ) ) {
                    return $work_days;
                }

                $extra_days = 0;

                // 2. check if leave available
                if ( $request->days > $balance['available'] ) {
                    // check if extra leaves enabled
                    $is_extra_leave_enabled = get_option( 'enable_extra_leave', 'no' );

                    if ( $is_extra_leave_enabled !== 'yes' ) {
                        return new WP_Error( 'extra-leave-disabled', esc_attr__( 'Sorry! given request do not have any leave left under this leave policy. If you still want to process this request, please enable Extra Leave feature from settings.', 'erp' ) );
                    }

                    // some days are unpaid leaves
                    $extra_days = $request->days - $balance['available'];
                }

                // 3. send data to leave approval status table
                $approval_status_data['message'] = $comments;
                $approval_status                 = LeaveApprovalStatus::create( $approval_status_data );

                // 4. send data to leave entitlement table - day out
                $entitlement_data['day_out']     = $request->days;
                $entitlement_data['f_year']      = $request->entitlement->f_year;
                $entitlement_data['trn_id']      = $approval_status->id;
                $entitlement_data['description'] = 'Approved';

                $leave_entitlement = LeaveEntitlement::create( $entitlement_data );

                // 5. send data to request details table
                $leave_request_details_data = [];

                foreach ( $work_days['days'] as $work_day ) {
                    $leave_request_details_data[] = [
                        'leave_request_id'          => $request_id,
                        'leave_approval_status_id'  => $approval_status->id,
                        'workingday_status'         => $work_day['count'],
                        'user_id'                   => $request->user_id,
                        'f_year'                    => $request->entitlement->f_year,
                        'leave_date'                => erp_current_datetime()->modify( $work_day['date'] )->setTime( 0, 0, 0 )->getTimestamp(),
                        'created_at'                => erp_current_datetime()->getTimestamp(),
                        'updated_at'                => erp_current_datetime()->getTimestamp(),
                    ];
                }

                if ( ! empty( $leave_request_details_data ) ) {
                    $request_details = LeaveRequestDetail::insert( $leave_request_details_data );
                }

                // 6. insert extra if exist
                if ( $extra_days ) {
                    // 1. insert into leave entitlement table
                    $entitlement_data['day_in']      = $extra_days;
                    $entitlement_data['day_out']     = 0;
                    $entitlement_data['trn_type']    = 'unpaid_leave';
                    $entitlement_data['description'] = 'Account';

                    $second_leave_entitlement = LeaveEntitlement::create( $entitlement_data );

                    // 2. insert into unpaid leave table
                    $unpaid_leave_data['days']                     = $extra_days;
                    $unpaid_leave_data['leave_approval_status_id'] = $approval_status->id;
                    $unpaid_leave                                  = LeavesUnpaid::create( $unpaid_leave_data );
                }
            } elseif ( $status === 3 ) { // reject this request
                // 1. send data to leave approval status table
                $approval_status_data['message'] = $comments;
                $approval_status                 = LeaveApprovalStatus::create( $approval_status_data );
            }
            break;

        default:
            return new WP_Error( 'invalid-status', esc_attr__( 'Invalid leave request status.', 'erp' ) );
            break;
    }

    // update last_status of current request
    $request->last_status = $status;
    $request->save();

    // notification email
    if ( 1 === $status ) {
        $approved_email = wperp()->emailer->get_email( 'ApprovedLeaveRequest' );

        if ( is_a( $approved_email, '\WeDevs\ERP\Email' ) ) {
            $approved_email->trigger( $request_id );
        }
    } elseif ( 3 === $status ) {
        $rejected_email = wperp()->emailer->get_email( 'RejectedLeaveRequest' );

        if ( is_a( $rejected_email, '\WeDevs\ERP\Email' ) ) {
            $rejected_email->trigger( $request_id );
        }
    }

    $status = ( $status == 1 ) ? 'approved' : ( $status == 2 ? 'pending' : 'reject' );

    do_action( "erp_hr_leave_request_{$status}", $request_id, $request );
    do_action( 'erp_hr_leave_update', $request_id, $old_data );

    erp_hrm_purge_cache( [
		'list' => 'leave_request',
		'request_id' => $request_id,
	] );

    return $request;
}

/**
 * Delete a single leave request
 *
 * @since 1.6.0
 *
 * @param int $request_id
 *
 * @return WP_Error|int
 */
function erp_hr_delete_leave_request( $request_id ) {
    // Check permission
    if ( ! current_user_can( 'erp_leave_manage' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to do this action', 'erp' ) );
    }

    $request = LeaveRequest::find( $request_id );

    if ( ! $request ) {
        return new WP_Error( 'invalid_leave_id', esc_attr__( 'No leave request found with give request id.', 'erp' ) );
    }

    if ( $request->approval_status ) {
        foreach ( $request->approval_status as $status ) {
            if ( $status->entitlements ) {
                foreach ( $status->entitlements as $entl ) {
                    $entl->delete();
                }
            }
            $status->delete();
        }
    }

    if ( $request->details ) {
        foreach ( $request->details as $detail ) {
            $detail->delete();
        }
    }

    if ( $request->unpaid ) {
        $request->unpaid->delete();
    }

    $request->delete();

    erp_hrm_purge_cache( [
		'list' => 'leave_request',
		'request_id' => $request_id,
	] );

    return $request_id;
}

/**
 * Get leave requests status
 *
 * added filter `erp_hr_leave_approval_statuses` on version 1.6.0
 *
 * @since 0.1
 *
 * @param int|bool $status
 *
 * @return array|string
 */
function erp_hr_leave_request_get_statuses( $status = false ) {
    $statuses = apply_filters( 'erp_hr_leave_approval_statuses', [
        'all' => esc_attr__( 'All', 'erp' ),
        '1'   => esc_attr__( 'Approved', 'erp' ),
        '2'   => esc_attr__( 'Pending', 'erp' ),
        '3'   => esc_attr__( 'Rejected', 'erp' ),
    ] );

    if ( false !== $status && array_key_exists( $status, $statuses ) ) {
        return $statuses[ $status ];
    }

    return $statuses;
}

/**
 * Get leave requests day status
 *
 * @since 1.6.0
 *
 * @param int|bool $status
 *
 * @return array|string
 */
function erp_hr_leave_request_get_day_statuses( $status = false ) {
    $statuses = apply_filters( 'erp_hr_leave_day_statuses', [
        'all' => esc_attr__( 'All', 'erp' ),
        '1'   => esc_attr__( 'Full Day', 'erp' ),
        '2'   => esc_attr__( 'Morning', 'erp' ),
        '3'   => esc_attr__( 'Afternoon', 'erp' ),
    ] );

    if ( false !== $status ) {
        return array_key_exists( $status, $statuses ) ? $statuses[ $status ] : '';
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
 * @param int $employee_id
 * @param int $policy_id
 * @param int $year
 *
 * @return bool
 */
function erp_hr_leave_has_employee_entitlement( $employee_id, $policy_id, $year ) {
    global $wpdb;

    $from_date = $year . '-01-01';
    $to_date   = $year . '-12-31';

    $result = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE user_id = %d AND policy_id = %d AND from_date = %s AND to_date = %s", $employee_id, $policy_id, $from_date, $to_date ) );

    return $result;
}

/**
 * Get Leave Entitlement For A User
 *
 * @since 1.6.0
 *
 * @param array $args
 */
function erp_hr_leave_get_entitlement( $user_id, $leave_id, $start_date ) {
    global $wpdb;
    // get financial year from date
    $f_year = erp_hr_get_financial_year_from_date( $start_date );

    if ( empty( $f_year ) ) {
        return new WP_Error( 'invalid-financial-year', esc_attr__( 'No leave year found with given date.', 'erp' ) );
    }

    // get entitlement
    $entitlement = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE user_id = %d and leave_id = %d and f_year = %d and trn_type = %s",
            [ $user_id, $leave_id, $f_year->id, 'leave_policies' ]
        )
    );

    if ( null === $entitlement ) {
        return new WP_Error( 'invalid-leave-entitlement', esc_attr__( 'No entitlement found.', 'erp' ) );
    }

    return $entitlement;
}

/**
 * Get all the leave entitlements of a calendar year
 *
 * @since 0.1
 * @since 1.2.0 Depricate `year` arg and using `from_date` and `to_date` instead
 * @since 1.6.0
 *
 * @param int $year
 *
 * @return array
 */
function erp_hr_leave_get_entitlements( $args = [] ) {
    global $wpdb;

    $defaults = [
        'user_id'       => 0,
        'leave_id'      => 0,
        'policy_id'     => 0,
        'year'          => 0,
        'number'        => 20,
        'offset'        => 0,
        'orderby'       => 'en.created_at',
        'order'         => 'DESC',
        'debug'         => false,
        'emp_status'    => '',
        'employee_type' => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $last_changed       = erp_cache_get_last_changed( 'hrm', 'leave_entitlement' );
    $cache_key          = 'erp-get-leave-entitlements-' . md5( serialize( $args ) ) . ": $last_changed";
    $cache_key_total    = 'erp-get-leave-entitlements-counts-' . md5( serialize( $args ) ) . ": $last_changed";
    $cache_key_sql      = 'erp-get-leave-entitlements-sql-' . md5( serialize( $args ) ) . ": $last_changed";

    $leave_entitlements     = wp_cache_get( $cache_key, 'erp' );
    $total_row_found        = wp_cache_get( $cache_key_total, 'erp' );
    $leave_entitlements_sql = wp_cache_get( $cache_key_sql, 'erp' );

    if ( false === $leave_entitlements ) {
        $where = "WHERE 1 = 1 AND en.trn_type = 'leave_policies'";

        if ( absint( $args['year'] ) ) {
            $where .= $wpdb->prepare( " AND en.f_year = %d", absint( $args['year'] ) );
        }

        if ( absint( $args['user_id'] ) ) {
            $where .= $wpdb->prepare( " AND en.user_id = %d", absint( $args['user_id'] ) );
        }

        if ( ! empty( $args['search'] ) ) {
            $like_search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where .= $wpdb->prepare( " AND u.display_name LIKE %s ", $like_search );
        }

        if ( $args['leave_id'] ) {
            $where .= $wpdb->prepare( " AND en.leave_id = %d", absint( $args['leave_id'] ) );
        }

        if ( $args['policy_id'] ) {
            $where .= $wpdb->prepare( " AND en.trn_id = %d", absint( $args['policy_id'] ) );
        }

        if ( $args['emp_status'] == 'active' ) {
            $where .= " AND emp.status = 'active'";
        }

        if ( $args['employee_type'] ) {
            $where .= $wpdb->prepare( " AND policy.employee_type = %s",  $args['employee_type'] );
        }

        $offset = absint( $args['offset'] );
        $number = absint( $args['number'] );
        $limit  = $args['number'] == '-1' ? '' : $wpdb->prepare(" LIMIT %d, %d", $offset, $number);

        $query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS en.*, u.display_name as employee_name, l.name as policy_name, emp.status as emp_status
            FROM `{$wpdb->prefix}erp_hr_leave_entitlements` AS en
            LEFT JOIN {$wpdb->prefix}erp_hr_leaves AS l ON l.id = en.leave_id
            LEFT JOIN {$wpdb->users} AS u ON en.user_id = u.ID
            LEFT JOIN {$wpdb->prefix}erp_hr_employees AS emp ON en.user_id = emp.user_id
            LEFT JOIN {$wpdb->prefix}erp_hr_leave_policies AS policy ON en.trn_id = policy.id
            {$where}
            ORDER BY %s %s %s", $args['orderby'], $args['order'], $limit);

        $leave_entitlements = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        wp_cache_set( $cache_key, $leave_entitlements, 'erp' );

        $total_row_found = absint( $wpdb->get_var( 'SELECT FOUND_ROWS()' ) );
        wp_cache_set( $cache_key_total, $total_row_found, 'erp' );

        $leave_entitlements_sql = $query;
        wp_cache_set( $cache_key_sql, $query, 'erp' );
    }

    return [
		'data' => $leave_entitlements,
		'total' => $total_row_found,
		'sql' => $leave_entitlements_sql,
	];
}

/**
 * Count leave entitlement
 *
 * @since 0.1
 *
 * @param array $args
 *
 * @return int
 */
function erp_hr_leave_count_entitlements( $args = [] ) {
    $financial_year_dates = erp_get_financial_year_dates();

    $defaults = [
        'from_date' => $financial_year_dates['start'],
        'to_date'   => $financial_year_dates['end'],
    ];

    $args = wp_parse_args( $args, $defaults );

    return LeaveEntitlement::where( 'from_date', '>=', $args['from_date'] )
        ->where( 'to_date', '<=', $args['to_date'] )
        ->count();
}

/**
 * Delete entitlement with leave request
 *
 * @since 0.1
 * @since 1.6.0 both $id and $entitlement_id are same ie: entitlement table id
 *
 * @param int $id
 * @param int $user_id
 * @param int $entitlement_id
 *
 * @return void
 */
function erp_hr_delete_entitlement( $id, $user_id, $entitlement_id ) {

    // 1. Get all leave request id associated with this entitlement id

    // 2. Get all approval status id associated with step 1 request ids

    // 3. delete all entitlement ids based on step 2 approval status id

    // 4. delete all unpaid leaves data based on step 1 request_id

    // 5. delete all approval status data based on step 2 data

    // 6. delete all leave request data based on step 1

    $entitlement = LeaveEntitlement::find( $entitlement_id );

    // get policy
    $policy = $entitlement->policy;

    // check entitlement employee status
    $employee = new Employee( $entitlement->user_id );

    if ( $entitlement->leave_requests ) {
        foreach ( $entitlement->leave_requests as $request ) {
            if ( $policy->employee_type !== '-1' && $policy->employee_type != $employee->get_type() ) {
                continue;
            }

            if ( $request->approval_status ) {
                foreach ( $request->approval_status as $status ) {
                    if ( $status->entitlements ) {
                        foreach ( $status->entitlements as $entl ) {
                            $entl->delete();
                        }
                    }
                    $status->delete();
                }
            }

            if ( $request->details ) {
                foreach ( $request->details as $detail ) {
                    $detail->delete();
                }
            }

            if ( $request->unpaid ) {
                $request->unpaid->delete();
            }

            $request->delete();
        }
    }

    erp_hrm_purge_cache( [ 'list' => 'leave_entitlement' ] );

    return $entitlement->delete();
}

/**
 * Erp get leave balance
 *
 * @since 0.1
 * @since 1.1.18 Add start_date in where clause
 * @since 1.2.1  Fix main query statement
 * @since 1.6.0 changed according to new db structure
 *
 * @param int $user_id
 *
 * @return float|bool
 */
function erp_hr_leave_get_balance( $user_id, $date = null ) {
    global $wpdb;

    $query = "
    SELECT en.id, en.leave_id, en.user_id, en.f_year, fy.start_date, fy.end_date, l.name AS policy_name,
    IFNULL( sum(en.day_in), 0 ) AS policy_day_in,
    IFNULL( ( SELECT sum(en2.day_in) AS total_day_in FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en2 WHERE en2.user_id = en.user_id AND en2.leave_id = en.leave_id AND en2.f_year = en.f_year ), 0 ) AS total_day_in,
    IFNULL( ( SELECT sum(en2.day_out) AS total_day_out FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en2 WHERE en2.user_id = en.user_id AND en2.leave_id = en.leave_id AND en2.f_year = en.f_year ), 0 ) AS total_day_out,
    IFNULL( ( SELECT sum(en2.day_in) AS extra_leaves FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en2 WHERE en2.user_id = en.user_id AND en2.leave_id = en.leave_id AND en2.f_year = en.f_year AND en2.trn_type = 'unpaid_leave' ), 0 ) AS extra_leaves,
    IFNULL( ( SELECT sum(rq.days) AS leave_spent FROM {$wpdb->prefix}erp_hr_leave_requests AS rq WHERE rq.user_id = en.user_id AND rq.leave_id = en.leave_id AND rq.last_status = 1 AND rq.start_date BETWEEN fy.start_date AND fy.end_date ), 0 ) AS leave_spent
    FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en
    LEFT JOIN {$wpdb->prefix}erp_hr_financial_years AS fy ON fy.id = en.f_year
    LEFT JOIN {$wpdb->prefix}erp_hr_leaves AS l ON l.id = en.leave_id
    WHERE en.user_id = %d AND en.trn_type='leave_policies'
    ";

    if ( $date === null ) {
        $financial_year = erp_hr_get_financial_year_from_date();
        $date           = ! empty( $financial_year ) ? $financial_year->id : null;
    }

    if ( $date !== null ) {
        $query .= $wpdb->prepare( " AND fy.id = %d", absint( $date ) );
    }

    $query .= ' GROUP BY en.leave_id, en.f_year';

    $results = $wpdb->get_results( $wpdb->prepare( $query, $user_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    $balance = [];

    if ( ! empty( $results ) ) {
        foreach ( $results as $result ) {
            $balance[ $result->leave_id ] = [
                'entitlement_id' => $result->id,
                'days'           => $result->policy_day_in,
                'from_date'      => $result->start_date,
                'to_date'        => $result->end_date,
                'leave_id'       => $result->leave_id,
                'policy_id'      => $result->id,
                'policy'         => $result->policy_name,
                'scheduled'      => 0,
                'entitlement'    => $result->policy_day_in,
                'total'          => $result->total_day_in,
                'available'      => $result->total_day_in - $result->total_day_out,
                'extra_leave'    => $result->extra_leaves,
                'day_in'         => $result->total_day_in,
                'day_out'        => $result->total_day_out,
                'spent'          => $result->leave_spent,
            ];
        }
    }

    return $balance;
}

/**
 * Erp get leave balance for a single entitlement
 *
 * @since 1.6.0
 *
 * @param int $user_id
 *
 * @return float|bool
 */
function erp_hr_leave_get_balance_for_single_entitlement( $entitlement_id ) {
    global $wpdb;

    $result = $wpdb->get_row( $wpdb->prepare( "
    SELECT en.id, en.leave_id, en.user_id, en.f_year, fy.start_date, fy.end_date, l.name AS policy_name,
    IFNULL( sum(en.day_in), 0 ) AS policy_day_in,
    IFNULL( ( SELECT sum(en2.day_in) AS total_day_in FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en2 WHERE en2.user_id = en.user_id AND en2.leave_id = en.leave_id AND en2.f_year = en.f_year ), 0 ) AS total_day_in,
    IFNULL( ( SELECT sum(en2.day_out) AS total_day_out FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en2 WHERE en2.user_id = en.user_id AND en2.leave_id = en.leave_id AND en2.f_year = en.f_year ), 0 ) AS total_day_out,
    IFNULL( ( SELECT sum(en2.day_in) AS extra_leaves FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en2 WHERE en2.user_id = en.user_id AND en2.leave_id = en.leave_id AND en2.f_year = en.f_year AND en2.trn_type = 'unpaid_leave' ), 0 ) AS extra_leaves,
    IFNULL( ( SELECT sum(rq.days) AS leave_spent FROM {$wpdb->prefix}erp_hr_leave_requests AS rq WHERE rq.user_id = en.user_id AND rq.leave_id = en.leave_id AND rq.last_status = 1 AND rq.start_date BETWEEN fy.start_date AND fy.end_date ), 0 ) AS leave_spent
    FROM {$wpdb->prefix}erp_hr_leave_entitlements AS en
    LEFT JOIN {$wpdb->prefix}erp_hr_financial_years AS fy ON fy.id = en.f_year
    LEFT JOIN {$wpdb->prefix}erp_hr_leaves AS l ON l.id = en.leave_id
    WHERE en.id = %d
    ", $entitlement_id ) );

    $balance = [];

    if ( ! empty( $result ) ) {
        $balance = [
            'entitlement_id' => $result->id,
            'days'           => $result->policy_day_in,
            'from_date'      => $result->start_date,
            'to_date'        => $result->end_date,
            'leave_id'       => $result->leave_id,
            'policy_id'      => $result->id,
            'policy'         => $result->policy_name,
            'scheduled'      => 0,
            'entitlement'    => $result->policy_day_in,
            'total'          => $result->total_day_in,
            'available'      => $result->total_day_in - $result->total_day_out,
            'extra_leave'    => $result->extra_leaves,
            'day_in'         => $result->total_day_in,
            'day_out'        => $result->total_day_out,
            'spent'          => $result->leave_spent,
        ];
    }

    return $balance;
}

/**
 * Erp get leave balance for a single policy for a user
 *
 * @since 1.6.0
 * @deprecated since 1.6.0 use erp_hr_leave_get_balance_for_single_entitlement() instead.
 *
 * @param int|object $leave_entitlement_id
 *
 * @return float|bool
 */
function erp_hr_leave_get_balance_for_single_policy( $entitlement ) {
    global $wpdb;

    if ( is_int( $entitlement ) ) {
        $entitlement = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE id = %d AND trn_type = %s",
                [ $entitlement, 'leave_policies' ]
            )
        );
    }

    if ( ! is_object( $entitlement ) ) {
        return new WP_Error( 'invalid-entitlement-object', esc_attr__( 'Invalid entitlement data.', 'erp' ) );
    }

    return erp_hr_leave_get_balance_for_single_entitlement( $entitlement->id );
}

/**
 * Get current month approve leave request list
 *
 * @since 0.1
 * @since 1.2.0 Ignore terminated employees
 * @since 1.2.2 Exclude past requests
 *              Sort results by start_date
 * @since 1.6.0
 *
 * @return array
 */
function erp_hr_get_current_month_leave_list() {
    $end_of_current_month = erp_current_datetime()->modify( 'last day of this month' )->setTime( 23, 59, 59 );
    $args                 = [
        'status'        => 1, // get only approved
        'start_date'    => erp_current_datetime()->setTime( 0, 0 )->getTimestamp(),
        'end_date'      => $end_of_current_month->getTimestamp(),
    ];
    $leave_requests = erp_hr_get_leave_requests( $args );

    return $leave_requests['data'];
}

/**
 * Get next month leave request approved list
 *
 * @since 0.1
 * @since 1.2.0 Ignore terminated employees
 * @since 1.2.2 Sort results by start_date
 * @since 1.6.0
 *
 * @return array
 */
function erp_hr_get_next_month_leave_list() {
    $args = [
        'status'        => 1, // get only approved
        'start_date'    => erp_current_datetime()->modify( 'first day of next month' )->setTime( 0, 0 )->getTimestamp(),
        'end_date'      => erp_current_datetime()->modify( 'last day of next month' )->setTime( 23, 59, 59 )->getTimestamp(),
    ];
    $leave_requests = erp_hr_get_leave_requests( $args );

    return $leave_requests['data'];
}

/**
 * Leave period dropdown at entitlement create time
 *
 * @since 0.1
 *
 * @return void
 */
function erp_hr_leave_period() {
    $next_sart_date = gmdate( 'Y-m-01 H:i:s', strtotime( '+1 year', strtotime( erp_financial_start_date() ) ) );
    $next_end_date  = gmdate( 'Y-m-t H:i:s', strtotime( '+1 year', strtotime( erp_financial_end_date() ) ) );

    $date = [
        erp_financial_start_date() => erp_format_date( erp_financial_start_date() ) . ' - ' . erp_format_date( erp_financial_end_date() ),
        $next_sart_date            => erp_format_date( $next_sart_date ) . ' - ' . erp_format_date( $next_end_date ),
    ];

    return $date;
}

/**
 * Get calendar leave events
 *
 * @param array|bool $get           filter args
 * @param int|bool   $user_id       Get leaves for given user only
 * @param bool       $approved_only Get leaves which are approved
 *
 * @since 0.1
 * @since 1.6.0
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
    $leave_request = new \WeDevs\ERP\HRM\Models\LeaveRequest();

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
    } elseif ( $designation ) {
        $leave_requests = $leave_request->leftJoin( $employee_tb, $request_tb . '.user_id', '=', $employee_tb . '.user_id' )
            ->leftJoin( $users_tb, $request_tb . '.user_id', '=', $users_tb . '.ID' )
            ->leftJoin( $policy_tb, $request_tb . '.policy_id', '=', $policy_tb . '.id' )
            ->select( $users_tb . '.display_name', $request_tb . '.*', $policy_tb . '.color' )
            ->where( $employee_tb . '.designation', '=', $designation );

        if ( $approved_only ) {
            $leave_requests = $leave_requests->where( $request_tb . '.status', 1 );
        }

        $leave_requests = erp_array_to_object( $leave_requests->get()->toArray() );
    } elseif ( $department ) {
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
        $end_year   = gmdate( 'Y', strtotime( $min_max_dates->max ) );
    } else {
        return [];
    }

    $start_month = erp_get_option( 'gen_financial_month', 'erp_settings_general', 1 );

    $years = [];

    for ( $i = $start_year; $i <= $end_year; $i ++ ) {
        if ( 1 === absint( $start_month ) ) {
            $years[] = $i;
        } elseif ( ! ( ( $i + 1 ) > $end_year ) ) {
            $years[] = $i . '-' . ( $i + 1 );
        } elseif ( $start_year === $end_year ) {
            $years[] = ( $i - 1 ) . '-' . $i;
        }
    }

    return $years;
}

/**
 * Generate leave reports
 *
 * @since 1.3.2
 * @since 1.6.0 updated according to new database change
 *
 * @param int $f_year
 *
 * @return array
 */
function erp_get_leave_report( array $employees, $f_year, $start_date = null, $end_date = null ) {
    $return = [];

    foreach ( $employees as $employee_id ) {
        //$return[ $employee_id ] = erp_hr_leave_get_balance( $employee_id, $f_year );
        $return[ $employee_id ] = erp_hr_get_custom_leave_report( $employee_id, $f_year, $start_date, $end_date );
    }

    return $return;
}

function erp_hr_get_custom_leave_report( $user_id, $f_year = null, $start_date = null, $end_date = null ) {
    global $wpdb;

    $query = 'select en.id, en.leave_id, en.day_in, en.f_year, fy.start_date, fy.end_date, l.name as policy_name';
    $query .= " from {$wpdb->prefix}erp_hr_leave_entitlements as en";
    $query .= " LEFT JOIN {$wpdb->prefix}erp_hr_financial_years as fy on fy.id = en.f_year";
    $query .= " LEFT JOIN {$wpdb->prefix}erp_hr_leaves as l on l.id = en.leave_id";
    $query .= " where user_id = %d and trn_type='leave_policies'";

    if ( $f_year === null || 'custom' === $f_year ) {
        $financial_year = erp_hr_get_financial_year_from_date( $end_date );
    } else {
        $financial_year = FinancialYear::find( $f_year );
    }

    if ( empty( $financial_year ) ) {
        return [];
    }

    $query .= $wpdb->prepare( " and fy.id = %d", absint( $financial_year->id ) );

    $results = $wpdb->get_results( $wpdb->prepare( $query, $user_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    $balance = [];

    if ( ! empty( $results ) ) {

        // fix start date
        if ( null === $start_date || '' === $start_date ) {
            $start_date = $financial_year->start_date;
        } else {
            $start_date = current_datetime()->modify( $start_date )->setTime( 0, 0, 0 )->getTimestamp();
        }

        // fix end date
        if ( null === $end_date || '' === $end_date ) {
            $end_date = $financial_year->end_date;
        } else {
            $end_date = current_datetime()->modify( $end_date )->setTime( 23, 59, 59 )->getTimestamp();
        }

        foreach ( $results as $result ) {
            $days_count = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT sum(day_in) as day_in, sum(day_out) as day_out FROM {$wpdb->prefix}erp_hr_leave_entitlements WHERE user_id = %d AND leave_id = %d and f_year = %d ",
                    [ $user_id, $result->leave_id, $result->f_year ]
                ),
                ARRAY_A
            );

            if ( is_array( $days_count ) && ! empty( $days_count ) ) {
                $day_in  = floatval( $days_count['day_in'] );
                $day_out = floatval( $days_count['day_out'] );

                // total spent
                $leave_spent = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT count(rq_details.id) FROM {$wpdb->prefix}erp_hr_leave_request_details as rq_details
                                LEFT JOIN {$wpdb->prefix}erp_hr_leave_requests as rq on rq.id = rq_details.leave_request_id
                                WHERE rq.leave_entitlement_id = %d AND rq_details.user_id = %d AND rq_details.workingday_status = %d AND rq_details.leave_date BETWEEN %d AND %d",
                        [ $result->id, $user_id, 1, $start_date, $end_date ]
                    )
                );

                $balance[ $result->leave_id ] = [
                    'entitlement_id' => $result->id,
                    'days'           => $result->day_in,
                    'from_date'      => $result->start_date,
                    'to_date'        => $result->end_date,
                    'leave_id'       => $result->leave_id,
                    'policy_id'      => $result->id,
                    'policy'         => $result->policy_name,
                    'scheduled'      => 0,
                    'entitlement'    => $result->day_in,
                    'total'          => $day_in,
                    'day_in'         => $day_in,
                    'day_out'        => $day_out,
                    'spent'          => $leave_spent,
                ];
            }
        }
    }

    return $balance;
}

/**
 * Import holidays from csv
 *
 * @since 1.5.10
 *
 * @param $file
 */
function import_holidays_csv( $file ) {
    //require_once WPERP_INCLUDES . '/Lib/parsecsv.lib.php';

    $csv = new ParseCsv\Csv();
    $csv->encoding( null, 'UTF-8' );
    $csv->parse( $file );

    /*
     * We'll ignore duplicate entries with the same title and
     * start date in the foreach loop when inserting an entry
     */
    $holiday_model = new \WeDevs\ERP\HRM\Models\LeaveHoliday();

    $error_msg    = '';
    $parsed_data  = [];

    foreach ( $csv->data as $data_key => $data ) {
        $line_error  = '';
        $title       = ( isset( $data['title'] ) ) ? $data['title'] : '';
        $start       = ( isset( $data['start'] ) ) ? $data['start'] : '';
        $end         = ( isset( $data['end'] ) ) ? $data['end'] : '';
        $description = ( isset( $data['description'] ) ) ? $data['description'] : '';

        if ( empty( $title ) ) {
            $line_error .= __( 'Title can not be empty', 'erp' ) . '<br>';
        }

        if ( strlen( $title ) > 200 ) {
            $line_error .= __( 'Title can not be more than 200 characters', 'erp' ) . '<br>';
        }

        if ( empty( $start ) || empty( $end ) ) {
            $line_error .= __( 'Start OR End date can not be empty', 'erp' ) . '<br>';
        }

        if ( ! is_string( $title ) && ! is_string( $start ) && ! is_string( $end ) ) {
            $line_error .= __( 'Title, Start & End must be', 'erp' ) . '<br>';
        }

        if ( ! preg_match ( '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $start ) ) {
            $line_error .= __( 'Start date should be valid format. Ex YYYY-MM-DD', 'erp' ) . '<br>';
        } elseif ( DateTime::createFromFormat( 'Y-m-d H:i:s', $start ) === false ) {
            $start = erp_current_datetime()->modify( $start )->format( 'Y-m-d 00:00:00' );
            $csv->data[ $data_key ]['start'] = $start;
        }

        if ( ! preg_match ( '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $end ) ) {
            $line_error .= __( 'End date should be valid format. Ex YYYY-MM-DD', 'erp' ) . '<br>';
        } elseif ( DateTime::createFromFormat( 'Y-m-d H:i:s', $end ) === false ) {
            $end = erp_current_datetime()->modify( $end )->format( 'Y-m-d 23:59:59' );
            $csv->data[ $data_key ]['end'] = $end;
        }

        // check for duplicate entries
        $holiday = $holiday_model->where( 'title', '=', $title )->where( 'start', '=', $start );

        if ( $holiday->count() ) {
            $line_error .= __( 'Holiday entry already exists', 'erp' ) . '<br>';
        }

        if ( ! empty( $line_error ) ) {
            $error_msg .= __( '<strong>Error at #ROW ' . ( $data_key + 1 ) . '</strong>', 'erp' ) . '<br>';
            $error_msg .= $line_error;
        }

        $days = erp_date_duration( $start, $end );
        $days = $days . ' ' . _n( __( 'day', 'erp' ), __( 'days', 'erp' ), $days );

        $parsed_data[] = [
            'title'       => $title,
            'start'       => $start,
            'end'         => $end,
            'duration'    => $days,
            'description' => $description,
        ];
    }

    if ( ! empty( $error_msg ) ) {
        return "<div class='error  notice'><p>{$error_msg}</p></div>";
    } elseif ( empty( $parsed_data ) ) {
        return __( 'No data found.', 'erp' );
    }

    erp_hrm_purge_cache( [ 'list' => 'leave_holiday' ] );

    return $parsed_data;
}

/**
 * Get leave requests day status
 *
 * @since 1.6.0
 *
 * @param int|bool $status
 *
 * @return array|string
 */
function erp_hr_leave_days_get_statuses( $status = false ) {
    $statuses = apply_filters( 'erp_hr_leave_day_statuses', [
        'all' => esc_attr__( 'All', 'erp' ),
        '1'   => esc_attr__( 'Full Day', 'erp' ),
        '2'   => esc_attr__( 'Morning', 'erp' ),
        '3'   => esc_attr__( 'Afternoon', 'erp' ),
    ] );

    if ( false !== $status && array_key_exists( $status, $statuses ) ) {
        return $statuses[ $status ];
    }

    return $statuses;
}

/**
 * Get Leave Policy Names or Leave Types
 *
 * @since 1.8.2
 *
 * @param array $args
 *
 * @return array|integer \WeDevs\ERP\HRM\Models\Leave|leave type counts
 */
function erp_hr_get_leave_policy_names( $args = [] ) {
    $defaults = [
        'count' => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    $last_changed = erp_cache_get_last_changed( 'hrm', 'leave_policy_name' );
    $cache_key    = 'erp-get-policy-names-' . md5( serialize( $args ) ) . ": $last_changed";
    $policy_names = wp_cache_get( $cache_key, 'erp' );

    $policy_name_counts_cache_key = 'policy-names-counts';
    $policy_names_counts          = wp_cache_get( $policy_name_counts_cache_key, 'erp' );

    if ( false === $policy_names ) {
        $policy_names = \WeDevs\ERP\HRM\Models\Leave::all();
        wp_cache_set( $cache_key, $policy_names, 'erp' );

        if ( $args['count'] ) {
            $policy_names_counts = \WeDevs\ERP\HRM\Models\Leave::count();
            wp_cache_set( $policy_name_counts_cache_key, $policy_names_counts, 'erp' );
        }
    }

    if ( $args['count'] ) {
        return $policy_names_counts;
    } else {
        return $policy_names;
    }
}

/**
 * Insert / Update new leave policy name
 *
 * @since 1.6.0
 *
 * @return int
 */
function erp_hr_insert_leave_policy_name( $args = [] ) {
    $defaults = [
        'id' => null,
    ];

    $args = wp_parse_args( $args, $defaults );

    erp_hrm_purge_cache( [ 'list' => 'leave_policy_name' ] );

    /*
     * Update
     */
    if ( $args['id'] ) {
        $exists = Leave::where( 'name', $args['name'] )
            ->where( 'id', '<>', $args['id'] )
            ->first();

        if ( $exists ) {
            return new WP_Error( 'exists', esc_html__( 'Name already exists', 'erp' ) );
        }

        $leave = Leave::find( $args['id'] );

        if ( ! $leave ) {
            return new WP_Error( 'not_exists', __( 'Leave Type doesn\'t exists.', 'erp' ) );
        }

        $leave->update( $args );

        return $leave->id;
    }

    /**
     * Create
     */
    $exists = Leave::where( 'name', $args['name'] )->first();

    if ( $exists ) {
        return new WP_Error( 'exists', esc_html__( 'Name already exists', 'erp' ) );
    }

    $leave = Leave::create( $args );

    return $leave->id;
}

/**
 * Remove leave policy name
 *
 * @since 1.6.0
 *
 * @return void
 */
function erp_hr_remove_leave_policy_name( $id ) {
    $has_policy = LeavePolicy::where( 'leave_id', $id )->first();

    if ( $has_policy ) {
        return new WP_Error( 'has_policy', __( 'This leave type cannot be deleted as it is associated with a leave policy', 'erp' ) );
    }

    $leave = Leave::find( $id );

    erp_hrm_purge_cache( [ 'list' => 'leave_policy_name' ] );

    $leave->delete();
}


/**
 * Get leave entitlements count associated with a leave policy
 *
 * @since 1.10.0
 *
 * @param int $leave_policy_id
 *
 * @return int|WP_Error
 */
function erp_hr_get_entitlemnt_of_leave_policy( $leave_policy_id ) {
    $leave_policy = LeavePolicy::find( $leave_policy_id );

    if ( ! $leave_policy ) {
        return new WP_Error( 'invalid-policy', __( 'No valid leave policy found', 'erp' ) );
    }

    return count( $leave_policy->entitlements );
}

/**
 * Build and return new policy create URL
 *
 * @since 1.6.0
 *
 * @return string
 */
function erp_hr_new_policy_url( $id = null, $action = '', $paged = 1 ) {
    $params = [
        'page'        => 'erp-hr',
        'section'     => 'leave',
        'sub-section' => 'policies',
        'action'      => 'new',
        'paged'       => $paged,
    ];

    if ( $id ) {
        $params['id']     = absint( $id );
        $params['action'] = $action;
    }

    return add_query_arg( $params, admin_url( 'admin.php' ) );
}

/**
 * Build and return new policy create URL
 *
 * @since 1.6.0
 *
 * @return string
 */
function erp_hr_new_policy_name_url( $id = null ) {
    $params = [
        'page'        => 'erp-hr',
        'section'     => 'leave',
        'sub-section' => 'policies',
        'type'        => 'policy-name',
    ];

    if ( $id ) {
        $params['id']     = absint( $id );
        $params['action'] = 'edit';
    }

    return add_query_arg( $params, admin_url( 'admin.php' ) );
}

/**
 * Get all departments leads id
 *
 * @since 1.6.0
 *
 * @return array
 */
function erp_hr_get_department_leads_id() {
    global $wpdb;

    $results   = $wpdb->get_results( "SELECT `lead` FROM {$wpdb->prefix}erp_hr_depts", ARRAY_A );
    $dep_leads = wp_list_pluck( $results, 'lead' );

    return $dep_leads;
}

/**
 * Check if current user is lead
 *
 * @since 1.6.0
 *
 * @return bool
 */
function erp_hr_is_current_user_dept_lead() {
    $leads        = erp_hr_get_department_leads_id();
    $logged_in_us = get_current_user_id();

    return (bool) in_array( $logged_in_us, $leads );
}

/**
 * Save employee leave request attachment
 *
 * @since 1.5.10
 * @since 1.6.0 added erp_hr_ prefix to function name
 *
 * @param $request_id
 * @param $request
 * @param $leaves
 */
function erp_hr_save_leave_attachment( $request_id, $request, $leaves ) {
    if ( ! isset( $_FILES['leave_document'] ) ) {
        return;
    }

    $file_names     = isset( $_FILES['leave_document']['name'] ) ? array_map( 'sanitize_file_name', (array) wp_unslash( $_FILES['leave_document']['name'] ) ) : [];
    $file_tmp_names = isset( $_FILES['leave_document']['tmp_name'] ) ? array_map( 'sanitize_url', (array) wp_unslash( $_FILES['leave_document']['tmp_name'] ) ) : [];
    $file_types     = isset( $_FILES['leave_document']['type'] ) ? array_map( 'sanitize_text_field', (array) $_FILES['leave_document']['type'] ) : [];
    $file_sizes     = isset( $_FILES['leave_document']['size'] ) ? array_map( 'sanitize_text_field', (array) $_FILES['leave_document']['size'] ) : [];
    $file_errors    = isset( $_FILES['leave_document']['error'] ) ? array_map( 'sanitize_text_field', (array) $_FILES['leave_document']['error'] ) : [];

    $uploader = new \WeDevs\ERP\Uploader();

    for ( $i = 0; $i < count( $file_names ); ++ $i ) {
        $fileinfo = wp_check_filetype_and_ext( $file_tmp_names[ $i ], $file_names[ $i ] );

        if ( ! $fileinfo['ext'] || ! $fileinfo['type'] || 0 !== (int) $file_errors[ $i ] ) {
            continue;
        }

        $uploaded = $uploader->handle_upload(
            [
                'name'     => $file_names[ $i ],
                'tmp_name' => $file_tmp_names[ $i ],
                'type'     => $file_types[ $i ],
                'error'    => $file_errors[ $i ],
                'size'     => $file_sizes[ $i ],
            ]
        );

        if ( ! empty( $uploaded['success'] ) ) {
            add_user_meta( $request['user_id'], 'leave_document_' . $request_id, $uploaded['attach_id'] );
        }
    }
}

/**
 * Get financial year data that a date belongs to
 *
 * @since 1.6.0 rewritten whole function
 *
 * @param string|int|null $date
 *
 * @return \WeDevs\ERP\HRM\Models\FinancialYear|null
 */
function erp_hr_get_financial_year_from_date( $date = null ) {
    global $wpdb;

    if ( empty( $date ) ) {
        $date = erp_current_datetime()->setTime( 0, 0, 0 )->getTimestamp();
    }

    if ( ! is_numeric( $date ) ) {
        if ( erp_is_valid_date( $date ) ) {
            $date = erp_current_datetime()->modify( $date )->setTime( 0, 0, 0 )->getTimestamp();
        } else {
            return null;
        }
    }

    $query = $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}erp_hr_financial_years
                    WHERE (start_date <= %d AND end_date >= %d)
                    OR (start_date >= %d and start_date <= %d)
                    OR (end_date >= %d and end_date <= %d)
                    ",
        [
            $date,
			$date,
            $date,
			$date,
            $date,
			$date,
        ]
    );

    $fid = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    if ( null === $fid ) {  // no financial year found with given range
        return $fid;
    }

    $financial_year = \WeDevs\ERP\HRM\Models\FinancialYear::find( $fid );

    if ( ! $financial_year ) {
        return null;
    }

    return $financial_year;
}

/**
 * Get financial year id(s) that belongs to a date range
 *
 * @since 1.6.0
 *
 * @param int|string $start_date
 * @param int|string $end_date
 *
 * @return array<int>
 */
function erp_hr_get_financial_year_from_date_range( $start_date, $end_date ) {
    global $wpdb;

    if ( ! is_numeric( $start_date ) ) {
        $start_date = erp_current_datetime()->modify( $start_date )->getTimestamp();
    }

    if ( ! is_numeric( $end_date ) ) {
        $end_date = erp_current_datetime()->modify( $end_date )->getTimestamp();
    }

    /*
     * select wp_erp_hr_leave_requests.id, st.approval_status_id from wp_erp_hr_leave_requests
     * left join wp_erp_hr_leave_approval_status as st on st.leave_request_id = wp_erp_hr_leave_requests.id
     * where (start_date <= 1578441600 and end_date >= 1578441600 and user_id = 23)
     * or (start_date <= 1579046399 and end_date >= 1579046399 and user_id = 23)
     * or (start_date >= 1578441600 and start_date <= 1579046399 and user_id = 23)
     * or (end_date >= 1578441600 and end_date <= 1579046399 and user_id = 23)
     */

    return $wpdb->get_col(
        $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}erp_hr_financial_years
                    WHERE (start_date <= %d AND end_date >= %d)
                    OR (start_date <= %d AND end_date >= %d)
                    OR (start_date >= %d and start_date <= %d)
                    OR (end_date >= %d and end_date <= %d)
                    ",
            [
                $start_date,
				$start_date,
                $end_date,
				$end_date,
                $start_date,
				$end_date,
                $start_date,
				$end_date,
            ]
        )
    );
}

/**
 * Automatic removal of old entitlements and assignment of new entitlements
 * after employee type change
 *
 * @since 1.10.2
 *
 * @param object $erp_user instance of \WeDevs\ERP\HRM\Models\Employee
 *
 * @return void
 */
function erp_hr_manage_leave_policy_on_employee_type_change( $erp_user ) {
    $user_previous_entitlements = erp_hr_leave_get_entitlements( [
        'user_id' => $erp_user->user_id,
    ] ) ['data'];

    $f_year = erp_hr_get_financial_year_from_date();

    if ( empty( $f_year ) ) {
        return;
    }

    $policies = get_employee_matched_leave_policies( $erp_user, $f_year );

    usort(
        $user_previous_entitlements,
        function ( $a, $b ) {
            if ( $a->day_in > $b->day_in ) {
                return -1;
            } elseif ( $a->day_in < $b->day_in ) {
                return 1;
            } else {
                return 0;
            }
        }
    );

    foreach ( $policies as $policy ) {
        $no_entitlements = count( $user_previous_entitlements );
        $do_not_delete   = [];

        for ( $i = 0; $i < $no_entitlements; $i++ ) {
            if ( $user_previous_entitlements[ $i ]->leave_id === $policy->leave_id ) {
                $do_not_delete[] = $i;
            }
        }

        foreach ( $do_not_delete as $index ) {
            unset( $user_previous_entitlements[ $index ] );
        }
    }

    $last_entitlement_id = false;

    foreach ( $policies as $policy ) {
        $data = [
            'user_id'     => $erp_user->user_id,
            'leave_id'    => $policy->leave_id,
            'created_by'  => get_current_user_id(),
            'trn_id'      => $policy->id,
            'trn_type'    => 'leave_policies',
            'day_in'      => $policy->days,
            'day_out'     => 0,
            'description' => ! empty( $policy->description ) ? $policy->description : 'Generated',
            'f_year'      => $policy->f_year,
        ];

        $new_entitlement_id = erp_hr_leave_insert_entitlement( $data );

        if ( is_wp_error( $new_entitlement_id ) ) {
            //TODO handle can't create new entitlement
        } elseif ( ! empty( $user_previous_entitlements ) ) {
            $last_entitlement_id = $new_entitlement_id;
            $new_entitlement     = LeaveEntitlement::find( $new_entitlement_id );
            $leave               = Leave::find( $new_entitlement->leave_id );
            $old_entitlement     = LeaveEntitlement::find( array_shift( $user_previous_entitlements )->id );

            transfer_requests_to_new_entitlements( $old_entitlement, $new_entitlement, $leave );

            $old_entitlement->delete();
        }
    }

    if ( empty( $user_previous_entitlements ) || $last_entitlement_id === false ) {
        erp_hrm_purge_cache( [ 'list' => 'leave_entitlement' ] );
        return;
    }

    // if count( $user_previous_entitlements ) > count( $policies )
    $new_entitlement = LeaveEntitlement::find( $last_entitlement_id );
    $leave           = Leave::find( $new_entitlement->leave_id );

    foreach ( $user_previous_entitlements as $old_entitlement_info ) {
        $old_entitlement = LeaveEntitlement::find( $old_entitlement_info->id );

        transfer_requests_to_new_entitlements( $old_entitlement, $new_entitlement, $leave );

        $old_entitlement->delete();
    }
    // if no policy matched, no old entitlement will be deleted or no new entitlement will be created

    erp_hrm_purge_cache( [ 'list' => 'leave_entitlement' ] );
}

/**
 * Get applicable leave policies of the Employee
 *
 * @since 1.10.2
 *
 * @param object $erp_user instance of \WeDevs\ERP\HRM\Models\Employee
 * @param object $f_year
 *
 * return object
 */
function get_employee_matched_leave_policies( $erp_user, $f_year ) {
    $policies = LeavePolicy::where(
        function ( $query ) use ( $erp_user ) {
            $query->where( 'employee_type', $erp_user->type )
                    ->orWhere( 'employee_type', '-1' );
        }
    );

    if ( $erp_user->department !== '0' ) {
        $policies = $policies->where(
            function ( $query ) use ( $erp_user ) {
                $query->where( 'department_id', $erp_user->department )
                        ->orWhere( 'department_id', '-1' );
            }
        );
    }

    if ( $erp_user->designation !== '0' ) {
        $policies = $policies->where(
            function ( $query ) use ( $erp_user ) {
                $query->where( 'designation_id', $erp_user->designation )
                        ->orWhere( 'designation_id', '-1' );
            }
        );
    }

    if ( $erp_user->location !== '0' ) {
        $policies = $policies->where(
            function ( $query ) use ( $erp_user ) {
                $query->where( 'location_id', $erp_user->location )
                        ->orWhere( 'location_id', '-1' );
            }
        );
    }

    $employee = new \WeDevs\ERP\HRM\Employee( $erp_user->user_id );

    if ( $employee->get_gender() ) {
        $policies = $policies->where(
            function ( $query ) use ( $employee ) {
                $query->where( 'gender', $employee->get_gender() )
                        ->orWhere( 'gender', '-1' );
            }
        );
    }

    if ( $employee->get_marital_status() ) {
        $policies = $policies->where(
            function ( $query ) use ( $employee ) {
                $query->where( 'marital', $employee->get_marital_status() )
                        ->orWhere( 'marital', '-1' );
            }
        );
    }

    $policies = $policies->where(
        function ( $query ) use ( $f_year ) {
            $query->where( 'f_year', $f_year->id );
        }
    );

    return $policies->orderByDesc( 'days' )
                        ->get();
}

/**
 * Assign requests of old entitlements to new entitlement
 *
 * @since 1.10.2
 *
 * @param object $old_entitlement
 * @param object $new_entitlement
 * @param object $leave
 *
 * return void
 */
function transfer_requests_to_new_entitlements( $old_entitlement, $new_entitlement, $leave ) {
    if ( ! $old_entitlement->leave_requests ) {
        return;
    }

    // assign the old entitlement requests to the new one
    foreach ( $old_entitlement->leave_requests as $request ) {
        if ( ! $request->approval_status ) {
            $new_entitlement->leave_requests()->save( $request );
            $leave->requests()->save( $request );
            continue;
        }

        foreach ( $request->approval_status as $status ) {
            if ( ! $status->entitlements ) {
                continue;
            }

            foreach ( $status->entitlements as $entitlement_after_processed_request ) {
                $leave->entitlements()->save( $entitlement_after_processed_request );
            }
        }

        if ( $request->unpaid ) {
            $leave->unpaids()->save( $request->unpaid );
        }

        $new_entitlement->leave_requests()->save( $request );
        $leave->requests()->save( $request );
    }
}
