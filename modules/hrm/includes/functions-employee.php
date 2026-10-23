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
    $usermeta_table = apply_filters( 'erp_hrm_usermeta_table_name', $wpdb->prefix . 'usermeta' );

    if ( $args['count'] && false !== $results_counts ) {
        return $results_counts;
    }

    if ( false === $results ) {

        $employee_tbl = $wpdb->prefix . 'erp_hr_employees';
        $employees    = \WeDevs\ERP\HRM\Models\Employee::select( [ $employee_tbl . '.user_id', 'display_name' ] )
            ->leftJoin( $wpdb->users, $employee_tbl . '.user_id', '=', $wpdb->users . '.ID' )
            ->leftJoin( "{$usermeta_table} as gender", function ( $join ) use ( $employee_tbl ) {
                $join->on( $employee_tbl . '.user_id', '=', 'gender.user_id' )->where( 'gender.meta_key', '=', 'gender' );
            } )
            ->leftJoin( "{$usermeta_table} as marital_status", function ( $join ) use ( $employee_tbl ) {
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

        if ( isset( $args['status'] ) && is_array( $args['status'] ) ) {
            // A list of statuses (since 2.0.0): any of them, trashed rows excluded.
            $employees = $employees->whereIn( 'status', array_values( array_map( 'sanitize_key', $args['status'] ) ) );
        } elseif ( isset( $args['status'] ) && ! empty( $args['status'] ) ) {
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
            // Escape LIKE wildcards so "%" or "_" in the term match literally.
            $arg_s     = $wpdb->esc_like( $args['s'] );
            // Match the name or the HR Employee ID (grouped so it ANDs with the
            // status / department / designation filters above).
            $employees = $employees->where( function ( $query ) use ( $arg_s, $employee_tbl ) {
                $query->where( 'display_name', 'LIKE', "%$arg_s%" )
                      ->orWhere( $employee_tbl . '.employee_id', 'LIKE', "%$arg_s%" );
            } );
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

            // A count call only needs the number: skip loading and hydrating every row.
            return $results_counts;
        }

        $results = $employees
            ->orderBy( $args['orderby'], $args['order'] )
            ->get()
            ->toArray();

        $results = erp_array_to_object( $results );

        do_action( 'erp_hr_get_employees_result', $results );

        if ( true !== $args['no_object'] && ! empty( $results ) ) {
            // Prime the user + usermeta caches in one go instead of two queries per Employee.
            cache_users( array_map( 'intval', wp_list_pluck( $results, 'user_id' ) ) );
        }

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

            // Read before the HR roles are stripped below. No role at all
            // counts: trashing an employee already took theirs away.
            $only_employee = $wp_user
                && ! array_diff( (array) $wp_user->roles, [ erp_hr_get_employee_role() ] )
                && ! user_can( $wp_user, 'edit_posts' );

            if ( $wp_user ) {
                $wp_user->remove_role( erp_hr_get_manager_role() );
                $wp_user->remove_role( erp_hr_get_employee_role() );
            }

            //finally remove from WordPress user
            $remove_wp_user = get_option( 'erp_hrm_remove_wp_user', 'no' );

            // The login goes only when the caller could delete it in WordPress,
            // or it was nothing but an employee's. An HR manager has no
            // `delete_users`, and this was their way to delete any account
            // with the employee role, an administrator's included.
            $current_user = get_current_user_id();
            $may_delete   = (int) $employee_wp_user_id !== $current_user
                && ( current_user_can( 'delete_user', $employee_wp_user_id ) || $only_employee );

            if ( 'yes' === $remove_wp_user && $may_delete ) {
                if ( ! function_exists( 'wp_delete_user' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/user.php';
                }

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
 * Published announcements only. The assignment row in `erp_hr_announcement` is
 * written once and never revisited, so without this condition an announcement
 * still in draft — or one trashed precisely to retract it — kept being served
 * to the assigned employee, title and body alike. The manager-side listing has
 * always passed `post_status => 'publish'`; only this employee-side query did
 * not.
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
        ->where( $wpdb->posts . '.post_status', '=', 'publish' )
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
    // `$wpdb->users`, not `{prefix}users`: on a multisite subsite the latter
    // names a table that does not exist, so every email passed as unused.
    return $wpdb->get_col( $wpdb->prepare( "select ID from {$wpdb->users} where user_email=%s AND ID !=%s", $user_email, $user_id ) );
}

/**
 * Whether the current user may take charge of a WordPress account as HR.
 *
 * Changing an employee's login email hands over the account (it is the reset
 * address), and converting a user gives them the employee role and an HR
 * record. Both need the same say over the account:
 *
 * - never a super admin, an administrator, or a user of another site in the
 *   network, unless the caller is one too;
 * - always when WordPress lets the caller edit that user anyway;
 * - otherwise an HR manager only, and only when every capability the account
 *   holds, from its roles or granted to it directly, is one the caller holds.
 *
 * @since 1.18.0
 *
 * @param int $user_id Target user ID.
 *
 * @return bool
 */
function erp_hr_can_manage_wp_account( $user_id ) {
    $user_id = (int) $user_id;
    $target  = get_userdata( $user_id );

    if ( ! $target ) {
        return false;
    }

    if ( is_multisite() && ! is_user_member_of_blog( $user_id ) && ! is_super_admin() ) {
        return false;
    }

    if ( is_super_admin( $user_id ) && ! is_super_admin() ) {
        return false;
    }

    if ( ( in_array( 'administrator', (array) $target->roles, true ) || user_can( $target, 'manage_options' ) )
        && ! current_user_can( 'manage_options' ) ) {
        return false;
    }

    if ( current_user_can( 'edit_user', $user_id ) ) {
        return true;
    }

    if ( ! current_user_can( 'erp_edit_employee' ) ) {
        return false;
    }

    // Compare capabilities directly rather than through
    // `erp_can_current_user_assign_role()`: that helper also counts the
    // deprecated `level_N` caps (subscriber carries `level_0`), which an HR
    // manager role never holds, so any second role blocked the correction.
    if ( current_user_can( 'promote_users' ) ) {
        return true;
    }

    $user_caps = array_filter( (array) wp_get_current_user()->allcaps );

    /** This filter is documented in includes/functions.php */
    $meta_caps = apply_filters( 'erp_role_comparison_ignored_caps', [
        'edit_post',
        'read_post',
        'delete_post',
        'edit_page',
        'read_page',
        'delete_page',
        'edit_comment',
        'edit_user',
        'delete_user',
        'remove_user',
        'add_user_to_blog',
    ] );

    // The caps of every role, plus any granted to the user directly (those
    // sit in `caps` beside the role names).
    $target_caps = [];

    foreach ( (array) $target->roles as $role ) {
        $role_object = get_role( $role );

        if ( ! $role_object ) {
            return false;
        }

        $target_caps += (array) $role_object->capabilities;
    }

    foreach ( (array) $target->caps as $cap => $granted ) {
        if ( ! wp_roles()->is_role( $cap ) ) {
            $target_caps[ $cap ] = $granted;
        }
    }

    foreach ( $target_caps as $cap => $granted ) {
        if ( ! $granted
            || in_array( $cap, $meta_caps, true )
            || preg_match( '/^level_\d+$/', (string) $cap ) ) {
            continue;
        }

        if ( empty( $user_caps[ $cap ] ) ) {
            return false;
        }
    }

    return true;
}

/**
 * Whether a WordPress account ranks above the current user.
 *
 * True for a super admin or an administrator (anyone who can manage_options)
 * when the current user is not one. Such an account's roles, login email,
 * website and name are not HR's to change; its HR record still is.
 *
 * Narrower than `erp_hr_can_manage_wp_account()`, which also refuses any
 * account holding a capability the caller lacks (a CRM agent, say): HR keeps
 * editing those employees' names as before.
 *
 * @since 1.18.0
 *
 * @param int $user_id Target user ID.
 *
 * @return bool
 */
function erp_hr_is_account_above_current_user( $user_id ) {
    $target = get_userdata( (int) $user_id );

    if ( ! $target ) {
        return false;
    }

    if ( is_super_admin( $target->ID ) && ! is_super_admin() ) {
        return true;
    }

    return ( in_array( 'administrator', (array) $target->roles, true ) || user_can( $target, 'manage_options' ) )
        && ! current_user_can( 'manage_options' );
}

add_filter( 'user_has_cap', 'erp_revoke_terminated_employee_access', 10, 4 );

wp_cache_add_non_persistent_groups( [ 'erp_hr_offboarded' ] );

/**
 * Whether a user's HR record is no longer active: terminated, resigned,
 * deceased, inactive or trashed.
 *
 * Such a user keeps reading what is theirs, but holds no HR authority: no
 * HR-manager rights, no department-lead moderation, no line-manager reviews.
 * A user with no HR record at all is not offboarded.
 *
 * Read once per request per user; the status hooks below forget the answer
 * when it changes mid-request.
 *
 * @since 2.0.0
 *
 * @param int $user_id WordPress user id.
 *
 * @return bool
 */
function erp_hr_is_offboarded_user( $user_id ) {
    global $wpdb;

    $user_id = absint( $user_id );

    if ( ! $user_id ) {
        return false;
    }

    $found = false;
    $row   = wp_cache_get( $user_id, 'erp_hr_offboarded', false, $found );

    if ( ! $found ) {
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT status, deleted_at FROM {$wpdb->prefix}erp_hr_employees WHERE user_id = %d",
                $user_id
            )
        );

        wp_cache_set( $user_id, $row ? $row : 0, 'erp_hr_offboarded' );
    }

    return ! empty( $row ) && ( 'active' !== $row->status || ! empty( $row->deleted_at ) );
}

/**
 * Forget a user's cached offboarded state after their HR record changed.
 *
 * @since 2.0.0
 *
 * @param int $user_id WordPress user id.
 *
 * @return void
 */
function erp_hr_forget_offboarded_state( $user_id ) {
    wp_cache_delete( absint( $user_id ), 'erp_hr_offboarded' );
}

add_action( 'erp_hr_employee_update', 'erp_hr_forget_offboarded_state' );
add_action( 'erp_hr_employee_after_update_status', 'erp_hr_forget_offboarded_state' );
add_action( 'erp_hr_employee_employment_status_create', 'erp_hr_forget_offboarded_state' );
add_action( 'erp_hr_after_delete_employee', 'erp_hr_forget_offboarded_state' );

/**
 * Take HR-manager authority from an offboarded user.
 *
 * Terminating or trashing an employee who is also an HR manager left the
 * `erp_hr_manager` role in place, and every manager check maps to that role,
 * so the person kept full HR access with their old session. Site
 * administrators are left alone, so an owner can never lock themselves out.
 *
 * @since 2.0.0
 *
 * @param array    $capabilities All capabilities of the user.
 * @param array    $caps         Capabilities being checked.
 * @param array    $args         Arguments.
 * @param \WP_User $user         The user.
 *
 * @return array
 */
function erp_hr_revoke_offboarded_manager_access( $capabilities, $caps, $args, $user ) {
    $manager_role = erp_hr_get_manager_role();

    if ( empty( $capabilities[ $manager_role ] ) || ! empty( $capabilities['manage_options'] ) ) {
        return $capabilities;
    }

    if ( ! erp_hr_is_offboarded_user( $user->ID ) ) {
        return $capabilities;
    }

    // Only what the manager role adds: the basics every employee has (read,
    // their own profile, the list) stay, so they can still see what is theirs.
    $manager_caps = array_diff(
        array_keys( erp_hr_get_caps_for_role( $manager_role ) ),
        array_keys( erp_hr_get_caps_for_role( erp_hr_get_employee_role() ) ),
        [ 'read' ]
    );

    foreach ( $caps as $cap ) {
        if ( $cap === $manager_role || in_array( $cap, $manager_caps, true ) ) {
            $capabilities[ $cap ] = false;
        }
    }

    return $capabilities;
}

add_filter( 'user_has_cap', 'erp_hr_revoke_offboarded_manager_access', 11, 4 );

/**
 * Drop a deleted employee from the department lead and line manager slots.
 *
 * Only on permanent delete: a trashed or terminated person already holds no
 * authority (see erp_hr_is_offboarded_user()), and keeping the pointers lets a
 * restore bring the team back as it was.
 *
 * @since 2.0.0
 *
 * @param int  $user_id WordPress user id.
 * @param bool $force   Whether the record was deleted permanently.
 *
 * @return void
 */
function erp_hr_release_deleted_employee_relations( $user_id, $force = false ) {
    global $wpdb;

    if ( ! $force ) {
        return;
    }

    $wpdb->update( "{$wpdb->prefix}erp_hr_depts", [ 'lead' => 0 ], [ 'lead' => absint( $user_id ) ] );
    $wpdb->update( "{$wpdb->prefix}erp_hr_employees", [ 'reporting_to' => 0 ], [ 'reporting_to' => absint( $user_id ) ] );
}

add_action( 'erp_hr_after_delete_employee', 'erp_hr_release_deleted_employee_relations', 10, 2 );

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

    // Trashed counts too: a trashed record keeps status `active`.
    if ( erp_hr_is_offboarded_user( $user->ID ) ) {
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
    $user_id                    = $fields['user_id'];
    $fields['work']['end_date'] = get_user_meta( $user_id, 'end_date', true );

    // user_url is a WordPress native column on wp_users.
    $user_url = '';
    if ( $user_id ) {
        $wp_user = get_user_by( 'id', $user_id );
        if ( $wp_user && ! empty( $wp_user->user_url ) ) {
            $user_url = $wp_user->user_url;
        }
    }
    $fields['personal']['user_url'] = $user_url;

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


/**
 * Check if a user has an employee record
 *
 * @param int $user_id User ID
 *
 * @return bool True if the user has an employee record, false otherwise
 */
function wperp_hrm_user_has_employee($user_id) {
    // Returns true if Employee record exists for this user, including trashed
    return \WeDevs\ERP\HRM\Models\Employee::withTrashed()->where('user_id', $user_id)->exists();
}

/**
 * Check if bulk delete action is being performed and if users have employee records
 * If so, display an error message and stop further processing
 *
 * @return void
 */
function intercept_bulk_wpuser_delete() {
    // Skip if this is a reset data action
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'erp_reset_data') {
        return;
    }

    if (
        is_admin() &&
        isset($_REQUEST['action']) &&
        $_REQUEST['action'] === 'delete' &&
        !empty($_REQUEST['users'])
    ) {

        // Check if user confirmed to delete employees
        if (isset($_REQUEST['confirm_delete_employees']) && $_REQUEST['confirm_delete_employees'] === '1') {
            // Verify nonce for security
            check_admin_referer('bulk-users');

            $users = (array) $_REQUEST['users'];
            $deleted_count = 0;
            $current_user_id = get_current_user_id();

            foreach ($users as $user_id) {
                $user_id = (int) $user_id;

                // Skip if trying to delete current user
                if ($user_id === $current_user_id) {
                    continue;
                }

                // Delete employee record if exists
                if (wperp_hrm_user_has_employee($user_id)) {
                    erp_employee_delete($user_id, true);
                }

                // Delete WordPress user
                require_once(ABSPATH . 'wp-admin/includes/user.php');
                if (wp_delete_user($user_id, $current_user_id)) {
                    $deleted_count++;
                }
            }

            // Redirect with success message
            wp_redirect(add_query_arg(
                'deleted',
                $deleted_count,
                admin_url('users.php')
            ));
            exit;
        }

        $users = (array) $_REQUEST['users'];
        $users_with_employee = [];
        foreach ($users as $user_id) {
            if (wperp_hrm_user_has_employee($user_id)) {
                $users_with_employee[] = $user_id;
            }
        }

        if (!empty($users_with_employee)) {

                // Prepare usernames, display names, and profile images for display
                $items = [];
                foreach ($users_with_employee as $uid) {
                    $user = get_userdata($uid);
                    if ($user) {
                        $avatar = get_avatar($uid, 32);
                        $login = esc_html($user->user_login);
                        $display = esc_html($user->display_name);
                        $items[] = '<li style="margin-bottom:4px;display:flex;align-items:center;">' . $avatar . '<span style="margin-left:8px;"><strong>' . $login . '</strong> <em>(' . $display . ')</em></span></li>';
                    }
                }

                $user_list = '<ul style="margin-left:20px;">' . implode('', $items) . '</ul>';

                wp_die(
                    sprintf(
                        /* translators: %s: List of usernames */
                        __('The following users have associated Employee profiles in WP ERP HRM and cannot be deleted. Please delete the Employee profiles first:%s', 'erp'),
                        $user_list
                    ),
                    __('Cannot Delete Users', 'erp'),
                    ['back_link' => true]
                );


            // Prepare usernames, display names, and profile images for display
            $items = [];
            foreach ($users_with_employee as $uid) {
                $user = get_userdata($uid);
                if ($user) {
                    $avatar = get_avatar($uid, 32);
                    $login = esc_html($user->user_login);
                    $display = esc_html($user->display_name);
                    $items[] = '<li style="margin-bottom:4px;display:flex;align-items:center;">' . $avatar . '<span style="margin-left:8px;"><strong>' . $login . '</strong> <em>(' . $display . ')</em></span></li>';
                }
            }

            // Build confirmation form
            $user_list = '<ul style="margin-left:20px;">' . implode('', $items) . '</ul>';

            $message = sprintf(
                /* translators: %s: List of usernames */
                __('The following users have associated Employee profiles in WP ERP HRM:%s', 'erp'),
                $user_list
            );

            $message .= '<div style="margin-top:20px;padding:15px;background:#fff;border:1px solid #ddd;border-radius:4px;">';
            $message .= '<p><strong>' . __('What would you like to do?', 'erp') . '</strong></p>';
            $message .= '<form method="post" action="" style="margin-top:10px;">';

            // Preserve all original request parameters
            foreach ($_REQUEST as $key => $value) {
                if ($key !== 'confirm_delete_employees' && !is_array($value)) {
                    $message .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
            }

            // Handle users array
            if (is_array($_REQUEST['users'])) {
                foreach ($_REQUEST['users'] as $user) {
                    $message .= '<input type="hidden" name="users[]" value="' . esc_attr($user) . '">';
                }
            }

            $message .= '<input type="hidden" name="confirm_delete_employees" value="1">';
            $message .= '<p style="margin-bottom:15px;">';
            $message .= '<button type="submit" class="button button-primary button-large" style="margin-right:10px;">';
            $message .= __('Delete Employee Profiles and WordPress Users', 'erp');
            $message .= '</button>';
            $message .= '<a href="' . esc_url(admin_url('users.php')) . '" class="button button-large">';
            $message .= __('Cancel', 'erp');
            $message .= '</a>';
            $message .= '</p>';
            $message .= '<p style="color:#d63638;margin-top:10px;"><em>' . __('Warning: This action cannot be undone. All employee data including leave records, notes, and history will be permanently deleted.', 'erp') . '</em></p>';
            $message .= '</form>';
            $message .= '</div>';

            wp_die(
                $message,
                __('Employee Profiles Found', 'erp'),
                ['back_link' => false]
            );
        }
    }
}

/**
 * Intercept single user delete action and prevent deletion if the user has an employee record
 *
 * @param int $user_id User ID
 *
 * @return void
 */
function intercept_single_user_delete($user_id) {
    // Skip if this is a reset data action
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'erp_reset_data') {
        return;
    }
    if (wperp_hrm_user_has_employee($user_id)) {
        // Check if user confirmed to delete employee
        if (isset($_REQUEST['confirm_delete_employee']) && $_REQUEST['confirm_delete_employee'] === '1') {
            // Verify nonce for security
            check_admin_referer('delete-user_' . $user_id);

            $current_user_id = get_current_user_id();

            // Don't allow deleting current user
            if ($user_id === $current_user_id) {
                wp_die(__('You cannot delete yourself.', 'erp'));
            }

            // Delete employee record
            erp_employee_delete($user_id, true);

            // Delete WordPress user
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            wp_delete_user($user_id, $current_user_id);

            // Redirect with success message
            wp_redirect(add_query_arg(
                'deleted',
                '1',
                admin_url('users.php')
            ));
            exit;
        }


        $user = get_userdata($user_id);
        $avatar = get_avatar($user_id, 64);

        $message = '<div style="text-align:center;margin-bottom:20px;">' . $avatar . '</div>';
        $message .= sprintf(
            /* translators: 1: username, 2: display name */
            __('User <strong>%1$s</strong> (%2$s) has an associated Employee profile in WP ERP HRM.', 'erp'),
            esc_html($user->user_login),
            esc_html($user->display_name)
        );

        $message .= '<div style="margin-top:20px;padding:15px;background:#fff;border:1px solid #ddd;border-radius:4px;">';
        $message .= '<p><strong>' . __('What would you like to do?', 'erp') . '</strong></p>';
        $message .= '<form method="post" action="" style="margin-top:10px;">';

        foreach ($_REQUEST as $key => $value) {
            if ($key !== 'confirm_delete_employee' && !is_array($value)) {
                $message .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
            }
        }

        $message .= '<input type="hidden" name="confirm_delete_employee" value="1">';
        $message .= '<p style="margin-bottom:15px;">';
        $message .= '<button type="submit" class="button button-primary button-large" style="margin-right:10px;">';
        $message .= __('Delete Employee Profile and WordPress User', 'erp');
        $message .= '</button>';
        $message .= '<a href="' . esc_url(admin_url('users.php')) . '" class="button button-large">';
        $message .= __('Cancel', 'erp');
        $message .= '</a>';
        $message .= '</p>';
        $message .= '<p style="color:#d63638;margin-top:10px;"><em>' . __('Warning: This action cannot be undone. All employee data including leave records, notes, and history will be permanently deleted.', 'erp') . '</em></p>';
        $message .= '</form>';
        $message .= '</div>';

        wp_die(
            $message,
            __('Employee Profile Found', 'erp'),
            ['back_link' => false]
        );
    }
}
