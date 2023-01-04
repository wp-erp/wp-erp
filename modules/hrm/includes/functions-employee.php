<?php

/**
 * Delete an employee if removed from WordPress usre table
 *
 * @since 1.7.2 Added erp_hr_after_delete_employee action hook
 *
 * @param  int  the user id
 *
 * @return void
 */
function erp_hr_employee_on_delete( $user_id, $hard = 0 ) {
    global $wpdb;

    $user = get_user_by( 'id', $user_id );

    if ( ! $user ) {
        return;
    }

    $role = reset( $user->roles );

    if ( 'employee' === $role ) {
        $deleted = \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $user_id )->withTrashed()->forceDelete();

        if ( true === $deleted ) {
            do_action( 'erp_hr_after_delete_employee', $user_id, true );
        }
    }
}

/**
 * Create a new employee
 *
 * @param  array  arguments
 *
 * @return int employee id
 */
function erp_hr_employee_create( $args = [] ) {
    $employee = new \WeDevs\ERP\HRM\Employee( null );
    $result   = $employee->create_employee( $args );

    if ( is_wp_error( $result ) ) {
        return $result->get_error_message();
    }

    return $result->user_id;
}

/**
 * Get all employees from a company
 *
 * @param int  $company_id company id
 * @param bool $no_object  if set true, Employee object will be
 *                         returned as array. $wpdb rows otherwise
 *
 * @return array the employees
 */
function erp_hr_get_employees( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'    => 20,
        'offset'    => 0,
        'orderby'   => 'hiring_date',
        'order'     => 'DESC',
        'no_object' => false,
        'count'     => false,
    ];

    $args  = wp_parse_args( $args, $defaults );

    $last_changed  = erp_cache_get_last_changed( 'hrm', 'employee' );
    $cache_key     = 'erp-get-employees-' . md5( serialize( $args ) ) . " : $last_changed";
    $results       = wp_cache_get( $cache_key, 'erp' );

    $cache_key_counts = 'erp-get-employees-count-' . md5( serialize( $args ) ) . " : $last_changed";
    $results_counts   = wp_cache_get( $cache_key_counts, 'erp' );

    if ( false === $results ) {

        $employee_tbl = $wpdb->prefix . 'erp_hr_employees';
        $employees    = \WeDevs\ERP\HRM\Models\Employee::select( [ $employee_tbl . '.user_id', 'display_name' ] )
            ->leftJoin( $wpdb->users, $employee_tbl . '.user_id', '=', $wpdb->users . '.ID' )
            ->leftJoin( "{$wpdb->prefix}usermeta as gender", function ( $join ) use ( $employee_tbl ) {
                $join->on( $employee_tbl . '.user_id', '=', 'gender.user_id' )->where( 'gender.meta_key', '=', 'gender' );
            } )
            ->leftJoin( "{$wpdb->prefix}usermeta as marital_status", function ( $join ) use ( $employee_tbl ) {
                $join->on( $employee_tbl . '.user_id', '=', 'marital_status.user_id' )->where( 'marital_status.meta_key', '=', 'marital_status' );
            } );

        if ( isset( $args['designation'] ) && $args['designation'] != '-1' ) {
            $employees = $employees->where( 'designation', $args['designation'] );
        }

        if ( isset( $args['department'] ) && $args['department'] != '-1' ) {
            $employees = $employees->where( 'department', $args['department'] );
        }

        if ( isset( $args['location'] ) && $args['location'] != '-1' ) {
            $employees = $employees->where( 'location', $args['location'] );
        }

        if ( isset( $args['type'] ) && $args['type'] != '-1' ) {
            $employees = $employees->where( 'type', $args['type'] );
        }

        /******** Check gender & marital status start ***********/
        if ( isset( $args['gender'] ) && $args['gender'] != '-1' ) {
            $employees = $employees->where( 'gender.meta_value', $args['gender'] );
        }

        if ( isset( $args['marital_status'] ) && $args['marital_status'] != '-1' ) {
            $employees = $employees->where( 'marital_status.meta_value', $args['marital_status'] );
        }
        /******** Check gender & marital status end ***********/

        if ( isset( $args['status'] ) && ! empty( $args['status'] ) ) {
            if ( $args['status'] == 'trash' ) {
                $employees = $employees->onlyTrashed();
            } else {
                if ( $args['status'] != 'all' ) {
                    $employees = $employees->where( 'status', $args['status'] );
                }
            }
        } else {
            $employees = $employees->where( 'status', 'active' );
        }

        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s     = $args['s'];
            $employees = $employees->where( 'display_name', 'LIKE', "%$arg_s%" );
        }

        if ( 'employee_name' === $args['orderby'] ) {
            $employees = $employees->leftJoin( $wpdb->usermeta . ' as umeta', function ( $join ) use ( $wpdb, $employee_tbl ) {
                $join->on( $employee_tbl . '.user_id', '=', 'umeta.user_id' )
                    ->where( 'umeta.meta_key', '=', 'first_name' );
            } );

            $args['orderby'] = 'umeta.meta_value';
        }

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' && ! $args['count'] ) {
            $employees = $employees->skip( $args['offset'] )->take( $args['number'] );
        }

        // Check if args count true, then return total count customer according to above filter
        if ( $args['count'] ) {
            $results_counts = $employees->count();

            wp_cache_set( $cache_key_counts, $results_counts, 'erp', HOUR_IN_SECONDS );
        }

        $results = $employees
            ->orderBy( $args['orderby'], $args['order'] )
            ->get()
            ->toArray();

        $results = erp_array_to_object( $results );

        foreach ( $results as $key => $row ) {
            if ( true === $args['no_object'] ) {
                $users[] = $row;
            } else {
                $users[] = new \WeDevs\ERP\HRM\Employee( intval( $row->user_id ) );
            }
        }

        $results = ! empty( $users ) ? $users : [];
        wp_cache_set( $cache_key, $results, 'erp', HOUR_IN_SECONDS );
    }

    if ( $args['count'] ) {
        return $results_counts;
    }

    return $results;
}

/**
 * Get all employees from a company
 *
 * @param int  $company_id company id
 * @param bool $no_object  if set true, Employee object will be
 *                         returned as array. $wpdb rows otherwise
 *
 * @return array the employees
 */
function erp_hr_count_employees() {
    $where = [];

    $employee = new \WeDevs\ERP\HRM\Models\Employee();

    if ( isset( $args['designation'] ) && ! empty( $args['designation'] ) ) {
        $designation = [ 'designation' => $args['designation'] ];
        $where       = array_merge( $designation, $where );
    }

    if ( isset( $args['department'] ) && ! empty( $args['department'] ) ) {
        $department = [ 'department' => $args['department'] ];
        $where      = array_merge( $where, $department );
    }

    if ( isset( $args['location'] ) && ! empty( $args['location'] ) ) {
        $location = [ 'location' => $args['location'] ];
        $where    = array_merge( $where, $location );
    }

    if ( isset( $args['status'] ) && ! empty( $args['status'] ) ) {
        $status = [ 'status' => $args['status'] ];
        $where  = array_merge( $where, $status );
    }

    $counts = $employee->where( $where )->count();

    return $counts;
}

/**
 * Get Employee status count
 *
 * @since 0.1
 *
 * @return array
 */
function erp_hr_employee_get_status_count() {
    global $wpdb;

    $statuses = [ 'all' => __( 'All', 'erp' ) ] + erp_hr_get_employee_statuses();
    $counts   = [];

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = [ 'count' => 0, 'label' => $label ];
    }

    $cache_key = 'erp-hr-employee-status-counts';
    $results   = wp_cache_get( $cache_key, 'erp' );

    if ( false === $results ) {
        $employee = new \WeDevs\ERP\HRM\Models\Employee();
        $db       = new \WeDevs\ORM\Eloquent\Database();

        $results = $employee->select( [ 'status', $db->raw( 'COUNT(id) as num' ) ] )
            ->where( 'status', '!=', '0' )
            ->groupBy( 'status' )
            ->get()->toArray();

        wp_cache_set( $cache_key, $results, 'erp' );
    }

    foreach ( $results as $row ) {
        if ( array_key_exists( $row['status'], $counts ) ) {
            $counts[ $row['status'] ]['count'] = (int) $row['num'];
        }

        $counts['all']['count'] += (int) $row['num'];
    }

    return $counts;
}

/**
 * Count trash employee
 *
 * @since 0.1
 *
 * @return int [no of trash employee]
 */
function erp_hr_count_trashed_employees() {
    $employee = new \WeDevs\ERP\HRM\Models\Employee();

    return $employee->onlyTrashed()->count();
}

/**
 * Employee Restore from trash
 *
 * @since 0.1
 *
 * @param array|int $employee_ids
 * @param boolean $restore_role_only, Restore the roles only when employee is trashed
 *
 * @return void
 */
function erp_employee_restore( $employee_ids, $restore_role_only = false ) {
    if ( empty( $employee_ids ) ) {
        return;
    }

    $employee_ids = is_array( $employee_ids ) ? $employee_ids : [ ( int ) $employee_ids ];

    foreach ( $employee_ids as $user_id ) {
        \WeDevs\ERP\HRM\Models\Employee::withTrashed()->where( 'user_id', $user_id )->restore();

        if ( $restore_role_only ) {
            $wp_user = get_userdata( $user_id );

            if ( ! empty( $wp_user ) ) {
                $role = get_user_meta( $user_id, 'erp_last_removed_role', true );

                if ( ! empty( $role ) ) {
                    $wp_user->add_role( $role );
                }

                delete_user_meta( $user_id, 'erp_last_removed_role' );
            }
        }
    }

    erp_hrm_purge_cache( ['list' => 'employee'] );
}

/**
 * Employee Delete
 *
 * @since 1.0.0
 * @since 1.2.0 After delete an employee, remove HR roles instead of
 *              remove the related wp user
 *
 * @param array|int $employee_ids
 * @param $force boolean
 *
 * @return void
 */
function erp_employee_delete( $employee_ids, $force = false ) {
    if ( empty( $employee_ids ) ) {
        return;
    }

    $employees = [];

    if ( is_array( $employee_ids ) ) {
        foreach ( $employee_ids as $key => $user_id ) {
            $employees[] = $user_id;
        }
    } elseif ( is_int( $employee_ids ) ) {
        $employees[] = $employee_ids;
    }

    // still do we have any ids to delete?
    if ( ! $employees ) {
        return;
    }

    // seems like we got some
    foreach ( $employees as $employee_wp_user_id ) {
        do_action( 'erp_hr_delete_employee', $employee_wp_user_id, $force );

        erp_hrm_purge_cache( ['list' => 'employee'] );

        $wp_user = get_userdata( $employee_wp_user_id );

        if ( $force ) {

            // find leave entitlements and leave requests and delete them as well
            $leave_requests = \WeDevs\ERP\HRM\Models\LeaveRequest::where( 'user_id', '=', $employee_wp_user_id )->get()->toArray();

            foreach ( $leave_requests as $lr ) {
                // deleting leave requests and entitlements with approval_status
                erp_hr_delete_leave_request( absint( $lr['id'] ) );
            }

            // deleting rest of the leave entitlements
            \WeDevs\ERP\HRM\Models\LeaveEntitlement::where( 'user_id', '=', $employee_wp_user_id )->delete();

            \WeDevs\ERP\HRM\Models\Education::where( 'employee_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Performance::where( 'employee_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\WorkExperience::where( 'employee_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\EmployeeHistory::where( 'user_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Employee_Note::where( 'user_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Announcement::where( 'user_id', '=', $employee_wp_user_id )->delete();

            \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $employee_wp_user_id )->withTrashed()->forceDelete();
            $wp_user->remove_role( erp_hr_get_manager_role() );
            $wp_user->remove_role( erp_hr_get_employee_role() );

            //finally remove from WordPress user
            $remove_wp_user = get_option( 'erp_hrm_remove_wp_user', 'no' );

            if ( 'yes' === $remove_wp_user ) {
                $current_user = get_current_user_id();
                wp_delete_user( $employee_wp_user_id, $current_user );
            }
        } else {
            \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $employee_wp_user_id )->delete();

            $current_role = erp_hr_get_user_role( $employee_wp_user_id );

            if ( ! empty ( $current_role ) ) {
                $wp_user->remove_role( $current_role );

                add_user_meta( $employee_wp_user_id, 'erp_last_removed_role', $current_role );
                $user_default_role = get_option( 'default_role', 'subscriber' );
                $wp_user->add_role( $user_default_role );
            }
        }

        do_action( 'erp_hr_after_delete_employee', $employee_wp_user_id, $force );
    }
}

/**
 * Get Todays Birthday
 *
 * @since 0.1
 * @since 1.1.14 Add where condition to remove terminated employees
 *
 * @return object collection of user_id
 */
function erp_hr_get_todays_birthday() {
    $db = new \WeDevs\ORM\Eloquent\Database();

    return erp_array_to_object( \WeDevs\ERP\HRM\Models\Employee::select( 'user_id' )
        ->where( $db->raw( "DATE_FORMAT( `date_of_birth`, '%m %d' )" ), \Carbon\Carbon::today()->format( 'm d' ) )
        ->where( 'status', 'active' )
        ->get()
        ->toArray() );
}

/**
 * Get next seven days birthday
 *
 * @since 0.1
 * @since 1.1.14 Add where condition to remove terminated employees
 *
 * @return object user_id, date_of_birth
 */
function erp_hr_get_next_seven_days_birthday() {
    $db = new \WeDevs\ORM\Eloquent\Database();

    return erp_array_to_object( \WeDevs\ERP\HRM\Models\Employee::select( [ 'user_id', 'date_of_birth' ] )
        ->where( $db->raw( "DATE_FORMAT( `date_of_birth`, '%m %d' )" ), '>', \Carbon\Carbon::today()->format( 'm d' ) )
        ->where( $db->raw( "DATE_FORMAT( `date_of_birth`, '%m %d' )" ), '<=', \Carbon\Carbon::tomorrow()->addWeek()->format( 'm d' ) )
        ->where( 'status', 'active' )
        ->get()
        ->toArray() );
}

/**
 * Get the raw employees dropdown
 *
 * @param  int  company id
 *
 * @return array the key-value paired employees
 */
function erp_hr_get_employees_dropdown_raw( $exclude = null ) {
    $employees = erp_hr_get_employees( [ 'number' => - 1, 'no_object' => true ] );
    $dropdown  = [ 0 => __( '- Select Employee -', 'erp' ) ];

    if ( $employees ) {
        foreach ( $employees as $key => $employee ) {
            if ( $exclude && intval( $employee->user_id ) === intval( $exclude ) ) {
                continue;
            }

            $dropdown[ $employee->user_id ] = $employee->display_name;
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
 * @return string the dropdown
 */
function erp_hr_get_employees_dropdown( $selected = '' ) {
    $employees = erp_hr_get_employees_dropdown_raw();
    $dropdown  = '';

    if ( $employees ) {
        foreach ( $employees as $key => $title ) {
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
    $statuses = [
        'active'     => __( 'Active', 'erp' ),
        'inactive'   => __( 'Inactive', 'erp' ),
        'terminated' => __( 'Terminated', 'erp' ),
        'deceased'   => __( 'Deceased', 'erp' ),
        'resigned'   => __( 'Resigned', 'erp' ),
    ];

    return apply_filters( 'erp_hr_employee_statuses', $statuses );
}

/**
 * Get the registered employee statuses
 *
 * @return array the employee statuses
 */
function erp_hr_get_employee_statuses_icons( $selected = null ) {
    $statuses = apply_filters( 'erp_hr_employee_statuses_icons', [
        'active'     => sprintf( '<span class="erp-tips dashicons dashicons-yes" title="%s"></span>', __( 'Active', 'erp' ) ),
        'terminated' => sprintf( '<span class="erp-tips dashicons dashicons-dismiss" title="%s"></span>', __( 'Terminated', 'erp' ) ),
        'deceased'   => sprintf( '<span class="erp-tips dashicons dashicons-marker" title="%s"></span>', __( 'Deceased', 'erp' ) ),
        'resigned'   => sprintf( '<span class="erp-tips dashicons dashicons-warning" title="%s"></span>', __( 'Resigned', 'erp' ) ),
    ] );

    if ( $selected && array_key_exists( $selected, $statuses ) ) {
        return $statuses[ $selected ];
    }

    return false;
}

/**
 * Get the registered employee statuses
 *
 * @return array the employee statuses
 */
function erp_hr_get_employee_types() {
    $types = [
        'permanent' => __( 'Full Time', 'erp' ),
        'parttime'  => __( 'Part Time', 'erp' ),
        'contract'  => __( 'On Contract', 'erp' ),
        'temporary' => __( 'Temporary', 'erp' ),
        'trainee'   => __( 'Trainee', 'erp' ),
    ];

    return apply_filters( 'erp_hr_employee_types', $types );
}

/**
 * Get the registered employee hire sources
 *
 * @return array the employee hire sources
 */
function erp_hr_get_employee_sources() {
    $sources = [
        'direct'        => __( 'Direct', 'erp' ),
        'referral'      => __( 'Referral', 'erp' ),
        'web'           => __( 'Web', 'erp' ),
        'newspaper'     => __( 'Newspaper', 'erp' ),
        'advertisement' => __( 'Advertisement', 'erp' ),
        'social'        => __( 'Social Network', 'erp' ),
        'other'         => __( 'Other', 'erp' ),
    ];

    return apply_filters( 'erp_hr_employee_sources', $sources );
}

/**
 * Get marital statuses
 *
 * @return array all the statuses
 */
function erp_hr_get_marital_statuses( $select_text = null ) {
    if ( $select_text ) {
        $statuses = [
            '-1'      => $select_text,
            'single'  => __( 'Single', 'erp' ),
            'married' => __( 'Married', 'erp' ),
            'widowed' => __( 'Widowed', 'erp' ),
        ];
    } else {
        $statuses = [
            'single'  => __( 'Single', 'erp' ),
            'married' => __( 'Married', 'erp' ),
            'widowed' => __( 'Widowed', 'erp' ),
        ];
    }

    return apply_filters( 'erp_hr_marital_statuses', $statuses );
}

/**
 * Get Terminate Type
 *
 * @return array all the type
 */
function erp_hr_get_terminate_type( $selected = null ) {
    $type = apply_filters( 'erp_hr_terminate_type', [
        'voluntary'   => __( 'Voluntary', 'erp' ),
        'involuntary' => __( 'Involuntary', 'erp' ),
    ] );

    if ( $selected ) {
        return ( isset( $type[ $selected ] ) ) ? $type[ $selected ] : '';
    }

    return $type;
}

/**
 * Get Terminate Reason
 *
 * @return array all the reason
 */
function erp_hr_get_terminate_reason( $selected = null ) {
    $reason = apply_filters( 'erp_hr_terminate_reason', [
        'attendance'            => __( 'Attendance', 'erp' ),
        'better_employment'     => __( 'Better Employment Conditions', 'erp' ),
        'career_prospect'       => __( 'Career Prospect', 'erp' ),
        'death'                 => __( 'Death', 'erp' ),
        'desertion'             => __( 'Desertion', 'erp' ),
        'dismissed'             => __( 'Dismissed', 'erp' ),
        'dissatisfaction'       => __( 'Dissatisfaction with the job', 'erp' ),
        'higher_pay'            => __( 'Higher Pay', 'erp' ),
        'other_employement'     => __( 'Other Employment', 'erp' ),
        'personality_conflicts' => __( 'Personality Conflicts', 'erp' ),
        'relocation'            => __( 'Relocation', 'erp' ),
        'retirement'            => __( 'Retirement', 'erp' ),
    ] );

    if ( $selected ) {
        return ( isset( $reason[ $selected ] ) ) ? $reason[ $selected ] : '';
    }

    return $reason;
}

/**
 * Get Terminate Reason
 *
 * @return array all the reason
 */
function erp_hr_get_terminate_rehire_options( $selected = null ) {
    $reason = apply_filters( 'erp_hr_terminate_rehire_option', [
        'yes'         => __( 'Yes', 'erp' ),
        'no'          => __( 'No', 'erp' ),
        'upon_review' => __( 'Upon Review', 'erp' ),
    ] );

    if ( $selected ) {
        return ( isset( $reason[ $selected ] ) ) ? $reason[ $selected ] : '';
    }

    return $reason;
}

/**
 * Employee terminated action
 *
 * @since 1.0.0
 *
 * @param $data
 *
 * @return $this|string|\WP_Error
 */
function erp_hr_employee_terminate( $data ) {
    if ( ! $data['user_id'] ) {
        return new WP_Error( 'no-user-id', 'No User id found' );
    }

    $employee = new \WeDevs\ERP\HRM\Employee( intval( $data['user_id'] ) );
    $old_data = $employee->get_data();
    $result   = $employee->terminate( $data );

    if ( is_wp_error( $result ) ) {
        return $result->get_error_message();
    }

    do_action( 'erp_hr_employee_update', $data['user_id'] , $old_data );

    return $result;
}

/**
 * Get employee genders
 *
 * @return array all genders
 */
function erp_hr_get_genders( $select_text = null ) {
    if ( $select_text ) {
        $genders = [
            '-1'     => $select_text,
            'male'   => __( 'Male', 'erp' ),
            'female' => __( 'Female', 'erp' ),
            'other'  => __( 'Other', 'erp' ),
        ];
    } else {
        $genders = [
            'male'   => __( 'Male', 'erp' ),
            'female' => __( 'Female', 'erp' ),
            'other'  => __( 'Other', 'erp' ),
        ];
    }

    return apply_filters( 'erp_hr_genders', $genders );
}

/**
 * Get pay type
 *
 * @return array all pay types
 */
function erp_hr_get_pay_type() {
    $types = [
        'hourly'   => __( 'Hourly', 'erp' ),
        'daily'    => __( 'Daily', 'erp' ),
        'weekly'   => __( 'Weekly', 'erp' ),
        'biweekly' => __( 'Biweekly', 'erp' ),
        'monthly'  => __( 'Monthly', 'erp' ),
        'contract' => __( 'Contract', 'erp' ),
    ];

    return apply_filters( 'erp_hr_pay_type', $types );
}

/**
 * Get pay change reasons
 *
 * @return array all the pay change reasons
 */
function erp_hr_get_pay_change_reasons() {
    $reasons = [
        'promotion'   => __( 'Promotion', 'erp' ),
        'performance' => __( 'Performance', 'erp' ),
        'increment'   => __( 'Increment', 'erp' ),
    ];

    return apply_filters( 'erp_hr_pay_change_reasons', $reasons );
}

/**
 * Add a new item in employee history table
 *
 * @param array $args
 *
 * @return array|bool|string|\WP_Error
 */
function erp_hr_employee_add_history( $args = [] ) {
    if ( ! $args['user_id'] ) {
        return new WP_Error( 'no-user-id', 'No User id found' );
    }
    $employee = new \WeDevs\ERP\HRM\Employee( intval( $args['user_id'] ) );
    $result   = $employee->create_or_update_history( $args );

    if ( is_wp_error( $result ) ) {
        return $result->get_error_message();
    }

    return $result;
}

/**
 * Remove an item from the history
 *
 * @param int $history_id
 *
 * @return bool
 */
function erp_hr_employee_remove_history( $history_id ) {
    global $wpdb;

    return $wpdb->delete( $wpdb->prefix . 'erp_hr_employee_history', [ 'id' => $history_id ] );
}

/**
 * Individual employee url
 *
 * @param  int  employee id
 *
 * @return string url of the employee details page
 */
function erp_hr_url_single_employee( $employee_id, $tab = null ) {
    if ( $tab ) {
        $tab = '&tab=' . $tab;
    }

    $user    = wp_get_current_user();
    $section = ( $user->ID === $employee_id ) ? 'my-profile' : 'people';

    if ( 'people' === $section ) {
        if ( in_array( 'employee', (array) $user->roles, true ) ) {
            add_query_arg( [ 'page' => 'erp-hrm', 'section' => $section, 'sub-section' => 'employee', 'id' => $employee_id . $tab ], admin_url( 'admin.php' ) );
            $url = admin_url( 'admin.php?page=erp-hr&section=' . $section . '&sub-section=employee&action=view&id=' . $employee_id . $tab );
        } else {
            $url = admin_url( 'admin.php?page=erp-hr&section=' . $section . '&sub-section=employee&action=view&id=' . $employee_id . $tab );
        }
    } else {
        if ( in_array( 'employee', (array) $user->roles, true ) ) {
            add_query_arg( [ 'page' => 'erp-hrm', 'section' => $section, 'id' => $employee_id . $tab ], admin_url( 'admin.php' ) );
            $url = admin_url( 'admin.php?page=erp-hr&section=' . $section . '&action=view&id=' . $employee_id . $tab );
        } else {
            $url = admin_url( 'admin.php?page=erp-hr&section=' . $section . '&action=view&id=' . $employee_id . $tab );
        }
    }

    return apply_filters( 'erp_hr_url_single_employee', $url, $employee_id );
}

/**
 * Individual employee tab url
 *
 * @param string $tab
 * @param        int employee id
 *
 * @since  1.1.10
 *
 * @return string
 */
function erp_hr_employee_tab_url( $tab, $employee_id ) {
    $emp_url = erp_hr_url_single_employee( intval( $employee_id ) );
    $tab_url = add_query_arg( [ 'tab' => $tab ], $emp_url );

    return apply_filters( 'erp_hr_employee_tab_url', $tab_url, $tab, $employee_id );
}

/**
 * Get Employee Announcement List
 *
 * @since 0.1
 *
 * @param int $user_id
 *
 * @return array
 */
function erp_hr_employee_dashboard_announcement( $user_id ) {
    global $wpdb;

    return erp_array_to_object( \WeDevs\ERP\HRM\Models\Announcement::join( $wpdb->posts, 'post_id', '=', $wpdb->posts . '.ID' )
        ->where( 'user_id', '=', $user_id )
        ->orderby( $wpdb->posts . '.post_date', 'desc' )
        ->take( 8 )
        ->get()
        ->toArray() );
}

/**
 * [erp_hr_employee_single_tab_general description]
 *
 * @return void
 */
function erp_hr_employee_single_tab_general( $employee ) {
    include WPERP_HRM_VIEWS . '/employee/tab-general.php';
}

/**
 * [erp_hr_employee_single_tab_job description]
 *
 * @return void
 */
function erp_hr_employee_single_tab_job( $employee ) {
    include WPERP_HRM_VIEWS . '/employee/tab-job.php';
}

/**
 * [erp_hr_employee_single_tab_leave description]
 *
 * @return void
 */
function erp_hr_employee_single_tab_leave( $employee ) {
    include WPERP_HRM_VIEWS . '/employee/tab-leave.php';
}

/**
 * [erp_hr_employee_single_tab_notes description]
 *
 * @return void
 */
function erp_hr_employee_single_tab_notes( $employee ) {
    include WPERP_HRM_VIEWS . '/employee/tab-notes.php';
}

/**
 * [erp_hr_employee_single_tab_performance description]
 *
 * @return void
 */
function erp_hr_employee_single_tab_performance( $employee ) {
    include WPERP_HRM_VIEWS . '/employee/tab-performance.php';
}

/**
 * [erp_hr_employee_single_tab_permission description]
 *
 * @return void
 */
function erp_hr_employee_single_tab_permission( $employee ) {
    include WPERP_HRM_VIEWS . '/employee/tab-permission.php';
}

/**
 * Get employee's available history module
 *
 * @since 1.3.0
 *
 * @return array
 */
function erp_hr_employee_history_modules() {
    $modules = [
        'employee',
        'employment',
        'compensation',
        'job',
    ];

    return apply_filters( 'erp_hr_employee_history_modules', $modules );
}

/**
 * Translate generic module data to readable format
 *
 * @param bool $inserting if inserting data then true
 *
 * @return array|WP_Error
 */
function erp_hr_translate_employee_history( array $history = [], $inserting = false ) {
    $available_modules = erp_hr_employee_history_modules();

    if ( empty( $history['module'] ) || ! in_array( $history['module'], $available_modules, true ) ) {
        return new \WP_Error( 'invalid-module-type', __( 'Unsupported module type', 'erp' ) );
    }

    $translators = [
        'employment'   => [
            'date'    => 'date',
            'type'    => 'type',
            'comment' => 'comment',
        ],
        'compensation' => [
            'date'     => 'date',
            'comment'  => 'comment',
            'category' => 'pay_type',
            'type'     => 'pay_rate',
            'data'     => 'reason',
        ],
        'job'          => [
            'date'     => 'date',
            'comment'  => 'designation',
            'category' => 'department',
            'data'     => 'reporting_to',
            'type'     => 'location',
        ],
    ];

    $translators = apply_filters( 'erp_hr_translatable_employee_history_module_params', $translators );

    $translator = $translators[ $history['module'] ];

    if ( $inserting ) {
        $translator = array_flip( $translator );
    }

    $formatted_history = [];

    foreach ( $translator as $key => $val ) {
        $formatted_history[ $val ] = '';

        if ( ! empty( $history[ $key ] ) ) {
            if ( ! $inserting ) {
                $formatted_history['id'] = ! empty( $history['id'] ) ? intval( $history['id'] ) : null;
            }
            $formatted_history['module'] = $history['module'];
            $formatted_history[ $val ]   = $history[ $key ];
        }
    }

    return $formatted_history;
}

/**
 * Control user data visibility
 *
 * @since  1.3.0
 *
 * @param $data
 * @param $user_id (of browsing user)
 *
 * @return array;
 */
function erp_hr_control_restricted_data( $data, $user_id ) {
    global $current_user;

    if ( ( ! current_user_can( erp_hr_get_manager_role() ) && ( $current_user->ID !== $user_id ) ) ) {
        $restricted = [
            'pay_rate',
            'pay_type',
            'hiring_source',
            'hiring_date',
        ];

        return array_merge( $data, $restricted );
    }

    return [];
}

/**
 * Get employee full name
 *
 * @since 1.3.2
 *
 * @param $user_id
 *
 * @return string
 */
function erp_hr_get_employee_name( $user_id ) {
    if ( ! $user_id instanceof WP_User ) {
        $user = new WP_User( $user_id );
    }

    $name = [];

    if ( $user->first_name ) {
        $name[] = $user->first_name;
    }

    if ( $user->middle_name ) {
        $name[] = $user->middle_name;
    }

    if ( $user->last_name ) {
        $name[] = $user->last_name;
    }

    return implode( ' ', $name );
}

/**
 * Get employee details admin url
 *
 * @since 1.3.11
 *
 * @param $user_id
 *
 * @return string
 */
function erp_hr_get_details_url( $user_id ) {
    return admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=' . $user_id );
}

/**
 * Get employee single url
 *
 * @since 1.3.11
 *
 * @param $user_id
 *
 * @return string
 */
function erp_hr_get_single_link( $user_id ) {
    return sprintf( '<a href="%s">%s</a>', erp_hr_get_details_url( $user_id ), erp_hr_get_employee_name( $user_id ) );
}

/**
 * Check if employee exist by email
 *
 * @since 1.3.12
 *
 * @param $email
 *
 * @return array
 */
function erp_is_employee_exist( $email, $user_id ) {
    global $wpdb;
    $user_email = sanitize_email( $email );
    return $wpdb->get_col( $wpdb->prepare( "select ID from {$wpdb->prefix}users where user_email=%s AND ID !=%s", $user_email, $user_id ) );
}

add_filter( 'user_has_cap', 'erp_revoke_terminated_employee_access', 10, 4 );

/**
 * Disable terminated users from accessing ERP
 *
 * @since 1.4.1
 *
 * @param array   $capabilities
 * @param array   $caps
 * @param array   $args
 * @param WP_User $user
 *
 * @return array $capabilities
 */
function erp_revoke_terminated_employee_access( $capabilities, $caps, $args, $user ) {
    if ( ! in_array( 'erp_list_employee', $caps, true ) &&
         ! in_array( 'upload_files', $caps, true ) &&
         ! in_array( 'erp_ac_manager', $caps, true ) &&
         ! in_array( 'erp_crm_manage_dashboard', $caps, true )
    ) {
        return $capabilities;
    }

    //check if user is employee
    if ( ! in_array( erp_hr_get_employee_role(), $user->roles, true ) ) {
        return $capabilities;
    }

    $employee = new WeDevs\ERP\HRM\Employee( $user );

    if ( 'active' !== $employee->get_status() ) {
        $capabilities['erp_list_employee']        = false; // hr menu capabilities
        $capabilities['upload_files']             = false;
        $capabilities['erp_ac_manager']           = false; // accounting menu capabilities
        $capabilities['erp_crm_manage_dashboard'] = false; // crm menu capabilities
    }

    return $capabilities;
}

/**
 * Get Contractual Employees
 *
 * @since 0.1
 * @since 1.1.14 Add where condition to remove terminated employees
 *
 * @return object collection of user_id
 */
function erp_hr_get_contractual_employee() {
    $db = new \WeDevs\ORM\Eloquent\Database();

    return erp_array_to_object( \WeDevs\ERP\HRM\Models\Employee::select( 'user_id', 'hiring_date', 'type' )
        ->where( 'status', 'active' )
        ->where( 'type', 'contract' )
        ->orWhere( 'type', 'trainee' )
        ->get()
        ->toArray() );
}

/**
 * Get Contractual Employees
 *
 * @since 1.5.6 Add Closing date for employee
 * @since 1.7.2 Added user url for employee
 *
 * @return object collection of fields;
 */
function get_employee_additional_fields( $fields, $id, $user ) {
    $user_id                        = $fields['user_id'];
    $fields['work']['end_date']     = get_user_meta( $user_id, 'end_date' );
    $fields['personal']['user_url'] = get_user_meta( $user_id, 'user_url' ) ? get_user_meta( $user_id, 'user_url' ) : '';

    return $fields;
}

/**
 * Get Education Result Types
 *
 * @since 1.8.3
 *
 * @param string $selected value
 *
 * @return array all the types
 */
function erp_hr_get_education_result_type_options( $selected = null ) {
    $types = [
        'grade'      => __( 'Grade',  'erp' ),
        'percentage' => __( 'Pecentage', 'erp' )
    ];

    $types = apply_filters( 'erp_hr_education_result_type_option', $types );

    if ( $selected ) {
        return ( isset( $types[ $selected ] ) ) ? $types[ $selected ] : '';
    }

    return $types;
}
