<?php

/**
 * Delete an employee if removed from WordPress usre table
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

    if ( 'employee' == $role ) {
        \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $user_id )->withTrashed()->forceDelete();
    }
}

/**
 * Create a new employee
 *
 * @param  array  arguments
 *
 * @return int  employee id
 */
function erp_hr_employee_create( $args = array() ) {
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
 * @param  int $company_id company id
 * @param bool $no_object if set true, Employee object will be
 *                            returned as array. $wpdb rows otherwise
 *
 * @return array  the employees
 */
function erp_hr_get_employees( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'    => 20,
        'offset'    => 0,
        'orderby'   => 'hiring_date',
        'order'     => 'DESC',
        'no_object' => false,
        'count'     => false
    );

    $args  = wp_parse_args( $args, $defaults );
    $where = array();

    $employee_tbl = $wpdb->prefix . 'erp_hr_employees';
    $employees    = \WeDevs\ERP\HRM\Models\Employee::select( array( $employee_tbl . '.user_id', 'display_name' ) )
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

    $cache_key = 'erp-get-employees-' . md5( serialize( $args ) );
    $results   = wp_cache_get( $cache_key, 'erp' );
    $users     = array();

    // Check if want all data without any pagination
    if ( $args['number'] != '-1' && ! $args['count'] ) {
        $employees = $employees->skip( $args['offset'] )->take( $args['number'] );
    }

    // Check if args count true, then return total count customer according to above filter
    if ( $args['count'] ) {
        return $employees->count();
    }

    if ( false === $results ) {

        $results = $employees
            ->orderBy( $args['orderby'], $args['order'] )
            ->get()
            ->toArray();

        $results = erp_array_to_object( $results );
        wp_cache_set( $cache_key, $results, 'erp', HOUR_IN_SECONDS );
    }

    if ( $results ) {
        foreach ( $results as $key => $row ) {

            if ( true === $args['no_object'] ) {
                $users[] = $row;
            } else {
                $users[] = new \WeDevs\ERP\HRM\Employee( intval( $row->user_id ) );
            }
        }
    }

    return $users;
}


/**
 * Get all employees from a company
 *
 * @param  int $company_id company id
 * @param bool $no_object if set true, Employee object will be
 *                            returned as array. $wpdb rows otherwise
 *
 * @return array  the employees
 */
function erp_hr_count_employees() {

    $where = array();

    $employee = new \WeDevs\ERP\HRM\Models\Employee();

    if ( isset( $args['designation'] ) && ! empty( $args['designation'] ) ) {
        $designation = array( 'designation' => $args['designation'] );
        $where       = array_merge( $designation, $where );
    }

    if ( isset( $args['department'] ) && ! empty( $args['department'] ) ) {
        $department = array( 'department' => $args['department'] );
        $where      = array_merge( $where, $department );
    }

    if ( isset( $args['location'] ) && ! empty( $args['location'] ) ) {
        $location = array( 'location' => $args['location'] );
        $where    = array_merge( $where, $location );
    }

    if ( isset( $args['status'] ) && ! empty( $args['status'] ) ) {
        $status = array( 'status' => $args['status'] );
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

    $statuses = array( 'all' => __( 'All', 'erp' ) ) + erp_hr_get_employee_statuses();
    $counts   = array();

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    $cache_key = 'erp-hr-employee-status-counts';
    $results   = wp_cache_get( $cache_key, 'erp' );

    if ( false === $results ) {

        $employee = new \WeDevs\ERP\HRM\Models\Employee();
        $db       = new \WeDevs\ORM\Eloquent\Database();

        $results = $employee->select( array( 'status', $db->raw( 'COUNT(id) as num' ) ) )
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
 * @param  array|int $employee_ids
 *
 * @return void
 */
function erp_employee_restore( $employee_ids ) {
    if ( empty( $employee_ids ) ) {
        return;
    }

    if ( is_array( $employee_ids ) ) {
        foreach ( $employee_ids as $key => $user_id ) {
            \WeDevs\ERP\HRM\Models\Employee::withTrashed()->where( 'user_id', $user_id )->restore();
        }
    }

    if ( is_int( $employee_ids ) ) {
        \WeDevs\ERP\HRM\Models\Employee::withTrashed()->where( 'user_id', $employee_ids )->restore();
    }
}

/**
 * Employee Delete
 *
 * @since 1.0.0
 * @since 1.2.0 After delete an employee, remove HR roles instead of
 *              remove the related wp user
 *
 * @param  array|int $employee_ids
 * @param $force boolean
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
    } else if ( is_int( $employee_ids ) ) {
        $employees[] = $employee_ids;
    }

    // still do we have any ids to delete?
    if ( ! $employees ) {
        return;
    }

    // seems like we got some
    foreach ( $employees as $employee_wp_user_id ) {

        do_action( 'erp_hr_delete_employee', $employee_wp_user_id, $force );

        if ( $force ) {

            // find leave entitlements and leave requests and delete them as well
            \WeDevs\ERP\HRM\Models\Leave_request::where( 'user_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Leave_Entitlement::where( 'user_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Education::where( 'employee_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Performance::where( 'employee_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Work_Experience::where( 'employee_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Employee_History::where( 'user_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Employee_Note::where( 'user_id', '=', $employee_wp_user_id )->delete();
            \WeDevs\ERP\HRM\Models\Announcement::where( 'user_id', '=', $employee_wp_user_id )->delete();

            \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $employee_wp_user_id )->withTrashed()->forceDelete();
            $wp_user = get_userdata( $employee_wp_user_id );
            $wp_user->remove_role( erp_hr_get_manager_role() );
            $wp_user->remove_role( erp_hr_get_employee_role() );

            //finally remove from wordpress user
            $remove_wp_user = get_option('erp_hrm_remove_wp_user', 'no');
            if('yes' == $remove_wp_user ){
                $current_user = get_current_user_id();
                wp_delete_user($employee_wp_user_id, $current_user);
            }

        } else {
            \WeDevs\ERP\HRM\Models\Employee::where( 'user_id', $employee_wp_user_id )->delete();
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

    return erp_array_to_object( \WeDevs\ERP\HRM\Models\Employee::select( array( 'user_id', 'date_of_birth' ) )
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
 * @return array  the key-value paired employees
 */
function erp_hr_get_employees_dropdown_raw( $exclude = null ) {
    $employees = erp_hr_get_employees( [ 'number' => - 1, 'no_object' => true ] );
    $dropdown  = array( 0 => __( '- Select Employee -', 'erp' ) );

    if ( $employees ) {
        foreach ( $employees as $key => $employee ) {
            if ( $exclude && $employee->user_id == $exclude ) {
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
 * @return string  the dropdown
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
    $statuses = array(
        'active'     => __( 'Active', 'erp' ),
        'inactive'   => __( 'Inactive', 'erp' ),
        'terminated' => __( 'Terminated', 'erp' ),
        'deceased'   => __( 'Deceased', 'erp' ),
        'resigned'   => __( 'Resigned', 'erp' )
    );

    return apply_filters( 'erp_hr_employee_statuses', $statuses );
}

/**
 * Get the registered employee statuses
 *
 * @return array the employee statuses
 */
function erp_hr_get_employee_statuses_icons( $selected = null ) {
    $statuses = apply_filters( 'erp_hr_employee_statuses_icons', array(
        'active'     => sprintf( '<span class="erp-tips dashicons dashicons-yes" title="%s"></span>', __( 'Active', 'erp' ) ),
        'terminated' => sprintf( '<span class="erp-tips dashicons dashicons-dismiss" title="%s"></span>', __( 'Terminated', 'erp' ) ),
        'deceased'   => sprintf( '<span class="erp-tips dashicons dashicons-marker" title="%s"></span>', __( 'Deceased', 'erp' ) ),
        'resigned'   => sprintf( '<span class="erp-tips dashicons dashicons-warning" title="%s"></span>', __( 'Resigned', 'erp' ) )
    ) );

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
    $types = array(
        'permanent' => __( 'Full Time', 'erp' ),
        'parttime'  => __( 'Part Time', 'erp' ),
        'contract'  => __( 'On Contract', 'erp' ),
        'temporary' => __( 'Temporary', 'erp' ),
        'trainee'   => __( 'Trainee', 'erp' )
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
        'direct'        => __( 'Direct', 'erp' ),
        'referral'      => __( 'Referral', 'erp' ),
        'web'           => __( 'Web', 'erp' ),
        'newspaper'     => __( 'Newspaper', 'erp' ),
        'advertisement' => __( 'Advertisement', 'erp' ),
        'social'        => __( 'Social Network', 'erp' ),
        'other'         => __( 'Other', 'erp' ),
    );

    return apply_filters( 'erp_hr_employee_sources', $sources );
}

/**
 * Get marital statuses
 *
 * @return array all the statuses
 */
function erp_hr_get_marital_statuses( $select_text = null ) {

    if ( $select_text ) {
        $statuses = array(
            '-1'      => $select_text,
            'single'  => __( 'Single', 'erp' ),
            'married' => __( 'Married', 'erp' ),
            'widowed' => __( 'Widowed', 'erp' )
        );
    } else {
        $statuses = array(
            'single'  => __( 'Single', 'erp' ),
            'married' => __( 'Married', 'erp' ),
            'widowed' => __( 'Widowed', 'erp' )
        );
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
        'involuntary' => __( 'Involuntary', 'erp' )
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
    $reason = apply_filters( 'erp_hr_terminate_rehire_option', array(
        'yes'         => __( 'Yes', 'erp' ),
        'no'          => __( 'No', 'erp' ),
        'upon_review' => __( 'Upon Review', 'erp' )
    ) );

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
    $result   = $employee->terminate( $data );

    if ( is_wp_error( $result ) ) {
        return $result->get_error_message();
    }

    return $result;
}

/**
 * Get employee genders
 *
 * @return array all genders
 */
function erp_hr_get_genders( $select_text = null ) {

    if ( $select_text ) {
        $genders = array(
            '-1'     => $select_text,
            'male'   => __( 'Male', 'erp' ),
            'female' => __( 'Female', 'erp' ),
            'other'  => __( 'Other', 'erp' )
        );
    } else {
        $genders = array(
            'male'   => __( 'Male', 'erp' ),
            'female' => __( 'Female', 'erp' ),
            'other'  => __( 'Other', 'erp' )
        );
    }

    return apply_filters( 'erp_hr_genders', $genders );
}

/**
 * Get pay type
 *
 * @return array all pay types
 */
function erp_hr_get_pay_type() {
    $types = array(
        'hourly'   => __( 'Hourly', 'erp' ),
        'daily'    => __( 'Daily', 'erp' ),
        'weekly'   => __( 'Weekly', 'erp' ),
        'biweekly' => __( 'Biweekly', 'erp' ),
        'monthly'  => __( 'Monthly', 'erp' ),
        'contract' => __( 'Contract', 'erp' ),
    );

    return apply_filters( 'erp_hr_pay_type', $types );
}

/**
 * Get pay change reasons
 *
 * @return array all the pay change reasons
 */
function erp_hr_get_pay_change_reasons() {
    $reasons = array(
        'promotion'   => __( 'Promotion', 'erp' ),
        'performance' => __( 'Performance', 'erp' ),
        'increment'   => __( 'Increment', 'erp' )
    );

    return apply_filters( 'erp_hr_pay_change_reasons', $reasons );
}

/**
 * Add a new item in employee history table
 *
 * @param array $args
 *
 * @return array|bool|string|\WP_Error
 */
function erp_hr_employee_add_history( $args = array() ) {

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
 * @param  int $history_id
 *
 * @return bool
 */
function erp_hr_employee_remove_history( $history_id ) {
    global $wpdb;

    return $wpdb->delete( $wpdb->prefix . 'erp_hr_employee_history', array( 'id' => $history_id ) );
}

/**
 * Individual employee url
 *
 * @param  int  employee id
 *
 * @return string  url of the employee details page
 */
function erp_hr_url_single_employee( $employee_id, $tab = null ) {
    if ( $tab ) {
        $tab = '&tab=' . $tab;
    }

    $user = wp_get_current_user();
    $section = ( $user->ID === $employee_id ) ? 'my-profile' : 'employee';

    if ( in_array( 'employee', (array) $user->roles ) ) {
        add_query_arg( [ 'page' => 'erp-hrm', 'section' => $section, 'id' => $employee_id.$tab ], admin_url( 'admin.php' ) );
        $url = admin_url( 'admin.php?page=erp-hr&section=' . $section . '&action=view&id=' . $employee_id . $tab );
    } else {
        $url = admin_url( 'admin.php?page=erp-hr&section=' . $section . '&action=view&id=' . $employee_id . $tab );
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
    $tab_url = add_query_arg( array( 'tab' => $tab ), $emp_url );

    return apply_filters( 'erp_hr_employee_tab_url', $tab_url, $tab, $employee_id );
}

/**
 * Get Employee Announcement List
 *
 * @since 0.1
 *
 * @param  integer $user_id
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
    $modules = array(
        'employment',
        'compensation',
        'job',
    );

    return apply_filters( 'erp_hr_employee_history_modules', $modules );
}

/**
 * Translate generic module data to readable format
 *
 * @param array $history
 * @param bool $inserting if inserting data then true
 *
 * @return array|WP_Error
 */
function erp_hr_translate_employee_history( array $history = array(), $inserting = false ) {
    $available_modules = erp_hr_employee_history_modules();

    if ( empty( $history['module'] ) || ! in_array( $history['module'], $available_modules ) ) {
        return new \WP_Error( 'invalid-module-type', __( 'Unsupported module type', 'erp' ) );
    }

    $translators = array(
        'employment'   => array(
            'date'    => 'date',
            'type'    => 'type',
            'comment' => 'comment',
        ),
        'compensation' => array(
            'date'     => 'date',
            'comment'  => 'comment',
            'category' => 'pay_type',
            'type'     => 'pay_rate',
            'data'     => 'reason',
        ),
        'job'          => array(
            'date'     => 'date',
            'comment'  => 'designation',
            'category' => 'department',
            'data'     => 'reporting_to',
            'type'     => 'location',
        )
    );

    $translators = apply_filters( 'erp_hr_translatable_employee_history_module_params', $translators );

    $translator = $translators[ $history['module'] ];

    if ( $inserting ) {
        $translator = array_flip( $translator );
    }

    $formatted_history = array();
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
 * control user data visibility
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

    return array();
}

/**
 * Get employee full name
 *
 * @since 1.3.2
 *
 * @param $user_id
 *
 * @return string
 *
 */
function erp_hr_get_employee_name( $user_id ) {
    if ( ! $user_id instanceof WP_User ) {
        $user = new WP_User( $user_id );
    }

    $name = array();
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
 *
 */
function erp_hr_get_details_url( $user_id ) {
    return admin_url( 'admin.php?page=erp-hr&section=employee&action=view&id=' . $user_id );
}

/**
 * Get employee single url
 *
 * @since 1.3.11
 *
 * @param $user_id
 *
 * @return string
 *
 */
function erp_hr_get_single_link( $user_id ) {
    return sprintf( '<a href="%s">%s</a>', erp_hr_get_details_url( $user_id ), erp_hr_get_employee_name($user_id) );
}

/**
 * check if employee exist by email
 *
 * @since 1.3.12
 *
 * @param $email
 *
 * @return array
 *
 */
function erp_is_employee_exist( $email, $user_id ) {
    global $wpdb;
    $user_email = sanitize_email($email);
    $sql    = "select user.ID from {$wpdb->prefix}erp_hr_employees as employee inner join {$wpdb->prefix}users as user on user.ID=employee.user_id where user.user_email='{$user_email}' AND user.ID !='{$user_id}'";
    return $wpdb->get_col( $sql );
}

add_filter( 'user_has_cap', 'erp_revoke_terminated_employee_access', 10, 4 );

/**
 * Disable terminated users from accessing ERP
 *
 * @since 1.4.1
 *
 * @param array $capabilities
 * @param array $caps
 * @param array $args
 * @param WP_User $user
 *
 * @return array $capabilities
 */
function erp_revoke_terminated_employee_access( $capabilities, $caps, $args, $user ) {

    if ( !in_array( 'erp_list_employee', $caps ) && !in_array( 'upload_files', $caps ) ) {
        return $capabilities;
    }

    //check if user is employee
    if ( !in_array( erp_hr_get_employee_role(), $user->roles ) ) {
        return $capabilities;
    }

    $employee = new WeDevs\ERP\HRM\Employee( $user );
    if ( 'terminated' === $employee->get_status() ) {
        $capabilities['erp_list_employee'] = false;
        $capabilities['upload_files']      = false;
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
 *
 * @return object collection of fields;
 */

function get_employee_additional_fields( $fields, $id, $user ) {
    $user_id = $fields['user_id'];
    $fields['work']['end_date'] = get_user_meta( $user_id, 'end_date' );
    return $fields;
}
