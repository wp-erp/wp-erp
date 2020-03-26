<?php

/**
 * Get company work days
 *
 * @since 1.0.0
 * @since 1.1.14 Using settings saved in ERP Settings > HR > Workdays tab
 *
 * @return array
 */
function erp_hr_get_work_days() {
    $default = [
        'mon' => 8,
        'tue' => 8,
        'wed' => 8,
        'thu' => 8,
        'fri' => 8,
        'sat' => 0,
        'sun' => 0
    ];

    $option_key = 'erp_settings_erp-hr_workdays';

    $wizard_settings = get_option( $option_key, $default );

    return [
        'mon' => get_option( 'mon', $wizard_settings['mon'] ),
        'tue' => get_option( 'tue', $wizard_settings['tue'] ),
        'wed' => get_option( 'wed', $wizard_settings['wed'] ),
        'thu' => get_option( 'thu', $wizard_settings['thu'] ),
        'fri' => get_option( 'fri', $wizard_settings['fri'] ),
        'sat' => get_option( 'sat', $wizard_settings['sat'] ),
        'sun' => get_option( 'sun', $wizard_settings['sun'] )
    ];
}

/**
 * Get working day without off day
 *
 * @since  0.1
 *
 * @param  string $start_date
 * @param  string $end_date
 *
 * @return array
 */
function erp_hr_get_work_days_without_off_day( $start_date, $end_date, $user_id = null ) {

    $between_dates = erp_extract_dates( $start_date, $end_date );

    if ( is_wp_error( $between_dates ) ) {
        return $between_dates;
    }

    $sandwich_rules_applied = erp_hr_can_apply_sandwich_rules_between_dates( $start_date, $end_date, $user_id );

    if ( ! empty( $sandwich_rules_applied ) ) {
        $between_dates = array_merge( $sandwich_rules_applied, $between_dates );
    }

    $dates         = array( 'days' => array(), 'total' => 0 );
    $work_days     = erp_hr_get_work_days();
    $holiday_exist = erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date );

    foreach ( $between_dates as $date ) {

        $key       = strtolower( date( 'D', strtotime( $date ) ) );
        $is_holidy = ( $work_days[ $key ] == 0 ) ? true : false;

        if ( ! $is_holidy ) {
            $is_holidy = in_array( $date, $holiday_exist ) ? true : false;
        }

        if ( get_option( 'erp_pro_sandwich_leave', '') === 'yes'  ) {
            $dates['days'][] = array(
                'date'  => $date,
                'count' => (int) ! $is_holidy
            );

            $dates['total'] += 1;
        } elseif ( ! $is_holidy ) {

            $dates['days'][] = array(
                'date'  => $date,
                'count' => (int) ! $is_holidy
            );

            $dates['total'] += 1;
        }
    }

    return $dates;
}

/**
 * Get working day with off day
 *
 * @since  0.1
 * @since 1.6.0 added sandwich rules
 *
 * @param  string $start_date
 * @param  string $end_date
 *
 * @return array
 */
function erp_hr_get_work_days_between_dates( $start_date, $end_date, $user_id = null ) {
    global $wpdb;

    if ( is_numeric( $start_date ) ) {
        $start_date = erp_current_datetime()->setTimestamp( $start_date )->format( 'Y-m-d' );
    }

    if ( is_numeric( $end_date ) ) {
        $end_date = erp_current_datetime()->setTimestamp( $end_date )->format( 'Y-m-d' );
    }

    $between_dates = erp_extract_dates( $start_date, $end_date );

    if ( is_wp_error( $between_dates ) ) {
        return $between_dates;
    }

    $dates         = array( 'days' => array(), 'total' => 0, 'sandwich' => 0 );
    $work_days     = erp_hr_get_work_days();
    $holiday_exist = erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date );

    $sandwich_rules_applied = erp_hr_can_apply_sandwich_rules_between_dates( $start_date, $end_date, $user_id );

    if ( ! empty( $sandwich_rules_applied ) ) {
        $between_dates = array_merge( $sandwich_rules_applied, $between_dates );
    }

    foreach ( $between_dates as $date ) {

        $key       = strtolower( date( 'D', strtotime( $date ) ) );
        $is_holidy = ( $work_days[ $key ] == '0' ) ? true : false;

        if ( ! $is_holidy ) {
            $is_holidy = in_array( $date, $holiday_exist ) ? true : false;
        }

        $dates['days'][] = array(
            'date'  => $date,
            'count' => (int) ! $is_holidy
        );

        if ( get_option( 'erp_pro_sandwich_leave', '') === 'yes'  ) {
            $dates['total'] += 1;

            // mark sandwich rule to true
            if ( $is_holidy ) {
                $dates['sandwich'] = 1;
            }
        }
        elseif ( ! $is_holidy ) {
            $dates['total'] += 1;
        }
    }

    return $dates;
}

/**
 * Check if we can apply sandwich rule between two dates
 *
 * @since  1.6.0
 *
 * @param  string $start_date
 * @param  string $end_date
 *
 * @return array
 */
function erp_hr_can_apply_sandwich_rules_between_dates( $start_date, $end_date, $user_id = null ) {
    global $wpdb;
    $work_days     = erp_hr_get_work_days();

    $can_apply_sandwich_rule_on_previous_leave = false;
    $previous_dates = array();
    $next_dates = array();

    if ( get_option( 'erp_pro_sandwich_leave', '') === 'yes' && absint( $user_id ) ) {
        // get previous leave request for this user either approved or pending, skip rejected
        $last_leave_request = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT rq.start_date, rq.end_date, st.approval_status_id
                        FROM {$wpdb->prefix}erp_hr_leave_requests as rq
                        left join {$wpdb->prefix}erp_hr_leave_approval_status as st on st.leave_request_id = rq.id
                        where rq.user_id = %d and rq.end_date < %d order by rq.id DESC limit 1",
                array( $user_id, strtotime( $start_date ) )
            ),
            ARRAY_A
        );

        if ( is_array( $last_leave_request ) && ! empty( $last_leave_request ) ) {
            // proceed further for pending or accepted request
            if ( $last_leave_request['approval_status_id'] != 3  ) {
                $last_req_end_date = erp_current_datetime()->setTimestamp( $last_leave_request['end_date'] )->modify( '+1 days' )->format( 'Y-m-d' );
                $start_day_previous = erp_current_datetime()->modify( $start_date )->modify( '-1 days' )->format( 'Y-m-d' );
                //date extract between last leave date and current leave start dates
                $previous_between_dates = erp_extract_dates( $last_req_end_date, $start_day_previous );

                //check holiday or non-working day exist between last_req and current start_date
                $previous_holiday_exist = erp_hr_leave_get_holiday_between_date_range( $last_req_end_date, $start_day_previous );

                foreach ( $previous_between_dates as $date ) {
                    $key       = strtolower( date( 'D', strtotime( $date ) ) );
                    $is_holidy = ( $work_days[ $key ] == '0' ) ? true : false;

                    if ( ! $is_holidy ) {
                        $is_holidy = in_array( $date, $previous_holiday_exist ) ? true : false;
                    }

                    if ( $is_holidy ) {
                        $previous_dates[] = $date;
                        $can_apply_sandwich_rule_on_previous_leave = true;
                    }
                    else {
                        $can_apply_sandwich_rule_on_previous_leave = false;
                        $previous_dates = array();
                        break;
                    }
                }
            }
        }

        //get next leave request
        $next_leave_request = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT rq.start_date, rq.end_date, st.approval_status_id
                        FROM {$wpdb->prefix}erp_hr_leave_requests as rq
                        left join {$wpdb->prefix}erp_hr_leave_approval_status as st on st.leave_request_id = rq.id
                        where rq.user_id = %d and rq.start_date > %d order by rq.id DESC limit 1",
                array( $user_id, strtotime( $end_date ) )
            ),
            ARRAY_A
        );

        if ( is_array( $next_leave_request ) && ! empty( $next_leave_request ) ) {
            // proceed further for pending or accepted request
            if ( $next_leave_request['approval_status_id'] != 3  ) {
                $last_req_start_date = erp_current_datetime()->setTimestamp( $next_leave_request['start_date'] )->modify( '-1 days' )->format( 'Y-m-d' );
                $end_date_next_day = erp_current_datetime()->modify( $end_date )->modify( '+1 days' )->format( 'Y-m-d' );
                //date extract between last leave date and current leave start dates
                $previous_between_dates = erp_extract_dates( $end_date_next_day, $last_req_start_date );

                //check holiday or non-working day exist between last_req and current start_date
                $previous_holiday_exist = erp_hr_leave_get_holiday_between_date_range( $end_date_next_day, $last_req_start_date );

                foreach ( $previous_between_dates as $date ) {
                    $key       = strtolower( date( 'D', strtotime( $date ) ) );
                    $is_holidy = ( $work_days[ $key ] == '0' ) ? true : false;

                    if ( ! $is_holidy ) {
                        $is_holidy = in_array( $date, $previous_holiday_exist ) ? true : false;
                    }

                    if ( $is_holidy ) {
                        $next_dates[] = $date;
                        $can_apply_sandwich_rule_on_previous_leave = true;
                    }
                    else {
                        $can_apply_sandwich_rule_on_previous_leave = false;
                        $next_dates = array();
                        break;
                    }
                }
            }
        }

    }

    return array_merge( $previous_dates, $next_dates );
}


/**
 * Sort parents before children
 *
 * @since 1.0
 *
 * @param array   $objects input objects with attributes 'id' and 'parent'
 * @param array   $result (optional, reference) internal
 * @param integer $parent (optional) internal
 * @param integer $depth (optional) internal
 *
 * @return array           output
 */
function erp_parent_sort( array $objects, array &$result = array(), $parent = 0, $depth = 0 ) {
    foreach ( $objects as $key => $object ) {
        if ( $object->parent == $parent ) {
            $object->depth = $depth;
            array_push( $result, $object );
            unset( $objects[ $key ] );
            erp_parent_sort( $objects, $result, $object->id, $depth + 1 );
        }
    }

    return $result;
}

/**
 * Check today's birthday through schedule job.
 *
 * @since 1.1
 *
 * @return array
 */
function erp_hr_schedule_check_todays_birthday() {
    $birthdays = erp_hr_get_todays_birthday();

    // Do the action if someone's birthday today run only in cron
    if ( defined( 'DOING_CRON' ) && DOING_CRON && ! empty( $birthdays ) ) {
        do_action( 'erp_hr_happened_birthday_today_all', $birthdays );

        foreach ( $birthdays as $birthday ) {
            do_action( 'erp_hr_happened_birthday_today', $birthday->user_id );
        }
    }

    return $birthdays;
}

/**
 * Check today's work anniversary through schedule job.
 *
 * @since 1.5.6
 *
 * @return array
 */
function erp_hr_schedule_check_todays_work_anniversary() {
    $anniversary_wish_email = wperp()->emailer->get_email( 'Hiring_Anniversary_Wish' );

    if ( is_a( $anniversary_wish_email, '\WeDevs\ERP\Email' ) ) {
        $db = new \WeDevs\ORM\Eloquent\Database();

        $employees = erp_array_to_object( \WeDevs\ERP\HRM\Models\Employee::select( 'user_id', 'hiring_date' )
            ->where( $db->raw( "DATE_FORMAT( `hiring_date`, '%m %d' )" ), \Carbon\Carbon::today()->format( 'm d' ) )
            ->where( 'status', 'active' )
            ->get()
            ->toArray() );
        foreach( $employees as $employee ) {
            $anniversary_wish_email->trigger( $employee->user_id, $employee->hiring_date );
        }
    }
}


/**
 * Prevent redirect to woocommerce my account page
 *
 * @param boolean $prevent_access
 *
 * @since 1.1.18
 *
 * @return boolean
 */
function erp_hr_wc_prevent_admin_access( $prevent_access ) {
    if ( current_user_can( erp_hr_get_manager_role() ) || current_user_can( erp_hr_get_employee_role() ) ) {
        return false;
    }

    return $prevent_access;
}

/**
 * Redirect hr role based user to their page
 *
 * @since 1.2.5
 *
 * @param $redirect_to
 * @param $roles
 *
 * @return string
 */
function erp_hr_login_redirect( $redirect_to, $roles ) {
    $hr_role       = erp_hr_get_manager_role();
    $employee_role = erp_hr_get_employee_role();

    if ( in_array( $hr_role, $roles ) || in_array( $employee_role, $roles ) ) {
        $redirect_to = get_admin_url( null, 'admin.php?page=erp-hr' );
    }

    return $redirect_to;
}

/**
 * Filter collection by date
 *
 * @since 1.3.0
 *
 * @param \Illuminate\Database\Eloquent\Builder $collection
 * @param  $date
 * @param $field string
 *
 * @return \Illuminate\Database\Eloquent\Builder
 */
function erp_hr_filter_collection_by_date( $collection, $date,  $field = 'created_at' ) {
    if ( $collection && is_array( $date ) ) {
        $default     = [
            'Y' => null,
            'm' => null,
            'd' => null,
        ];
        $parsed_date = wp_parse_args( $date, $default );

        if ( ! empty( $date['Y'] ) ) {
            $collection = $collection->whereYear( $field, '=', intval( $parsed_date['Y'] ) );
        }
        if ( ! empty( $date['m'] ) ) {
            $collection = $collection->whereMonth( $field, '=', intval( $parsed_date['m'] ) );
        }
        if ( ! empty( $date['d'] ) ) {
            $collection = $collection->whereDay( $field, '=', intval( $parsed_date['d'] ) );
        }
    }

    return $collection;
}

/**
 * get the list of email who disabled notification from wperp
 *
 * @since 1.3.10
 *
 * @return array
 *
 */
function erp_hr_get_disabled_notification_users() {
    $user_emails = wp_cache_get( 'erp_hr_get_disabled_notification_users', 'erp' );
    if ( false == $user_emails ) {
        $user_query  = new WP_User_Query( [
            'fields'     => [ 'user_email' ],
            'meta_key'   => 'erp_hr_disable_notification',
            'meta_value' => 'on'
        ] );
        $user_emails = [];
        if ( $user_query->get_total() ) {
            $user_emails = wp_list_pluck( $user_query->get_results(), 'user_email' );
        }

        wp_cache_add( 'erp_hr_get_disabled_notification_users', $user_emails, 'erp' );
    }

    return $user_emails;
}
/**
 * exclude recipients per profile settings
 *
 * @since 1.3.10
 *
 * @param $recipients
 *
 * @return mixed
 *
 */
function erp_hr_exclude_recipients( $recipients ) {
    $disabled_notification_user = erp_hr_get_disabled_notification_users();
    if ( ! empty( $disabled_notification_user ) ) {
        if ( is_string( $recipients ) ) {
            $recipients = explode( ',', $recipients );
        }
        $recipients = array_map( 'trim', $recipients );
        $recipients = array_map( 'strtolower', $recipients );
        $disabled_notification_user = array_map( 'strtolower', $disabled_notification_user );
        $recipients = array_diff($recipients, $disabled_notification_user);
    }

    return $recipients;
}




/****************** Send Birthday wish email Start ********************/


/**
 * Send birthday wish email to employee
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function erp_hr_send_birthday_wish_email( $user_id ) {
    $birthday_wish_email = wperp()->emailer->get_email( 'Birthday_Wish' );

    if ( is_a( $birthday_wish_email, '\WeDevs\ERP\Email' ) ) {
        $birthday_wish_email->trigger( $user_id );
    }
}
/****************** Send Birthday wish email End ********************/
