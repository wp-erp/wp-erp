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
function erp_hr_get_work_days_without_off_day( $start_date, $end_date ) {

    $between_dates = erp_extract_dates( $start_date, $end_date );

    if ( is_wp_error( $between_dates ) ) {
        return $between_dates;
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

        if ( ! $is_holidy ) {

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
 *
 * @param  string $start_date
 * @param  string $end_date
 *
 * @return array
 */
function erp_hr_get_work_days_between_dates( $start_date, $end_date ) {

    $between_dates = erp_extract_dates( $start_date, $end_date );

    if ( is_wp_error( $between_dates ) ) {
        return $between_dates;
    }

    $dates         = array( 'days' => array(), 'total' => 0 );
    $work_days     = erp_hr_get_work_days();
    $holiday_exist = erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date );

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

        if ( ! $is_holidy ) {
            $dates['total'] += 1;
        }
    }

    return $dates;
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


/****************************  Weekly Digest Email to HR manager  Start   **********************************/

/**
 * Get Employees based on hiring date period
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function get_employees_by_hiring_date( $from_date, $to_date ) {
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_hr_employees WHERE `hiring_date` BETWEEN '{$from_date}' AND '{$to_date}' AND status = 'active' ORDER BY hiring_date" );
    return $results;
}

/**
 * Get Employees based on current month birthday
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function get_employees_by_birth_month( $current_date, $after_7_days_date ) {
    global $wpdb;
    $current_month_date         = date('m d', strtotime( $current_date ) );
    $after_7_days_month_date    = date('m d', strtotime( $after_7_days_date ) );
    $results                    = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}erp_hr_employees` WHERE DATE_FORMAT(`date_of_birth`, \"%m %d\") BETWEEN '{$current_month_date}' AND '{$after_7_days_month_date}' AND status='active' ORDER BY date_of_birth" );

    $results_arr = [];

    foreach( $results as $result) {
        $results_arr[] = ( object ) [
            'user_id'               => $result->user_id,
            'date_of_birth'         => $result->date_of_birth,
            'date_of_birth_only'    => date( 'd', strtotime( $result->date_of_birth ) ),
        ];
    }
    usort($results_arr, function($a, $b) {
        return $a->date_of_birth_only > $b->date_of_birth_only;
    });
    return ( object ) $results_arr;
}

/**
 * Get Trainee & Contractual Employees based on End date is about to end
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function get_about_to_end_employees( $current_date ) {
    $c_t_employees      = erp_hr_get_contractual_employee();
    $filtered_employees = [];
    foreach( $c_t_employees as $key => $user ) {
        $date1          = date_create($current_date);
        $end_date       = get_user_meta( $user->user_id, 'end_date', true );
        $date2          = date_create( $end_date );
        $diff           = date_diff($date1,$date2);
        if ( $diff->days > 0 && $diff->days < 7) {
            $filtered_employees[] = ( object ) [
                'user_id'   => $user->user_id,
                'end_date'  => $end_date
            ];
        }
    }
    usort($filtered_employees, function($a, $b) {
        return $a->end_date > $b->end_date;
    });
    return ( object ) $filtered_employees;
}

/**
 * Get Hiring anniversary of the employees
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function get_upcomming_hiring_date_anniversary( $current_date, $after_7_days_date ) {
    global $wpdb;
    $current_month_date         = date('m d', strtotime( $current_date ) );
    $after_7_days_month_date    = date('m d', strtotime( $after_7_days_date ) );
    $results                    = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}erp_hr_employees` WHERE DATE_FORMAT(`hiring_date`, \"%m %d\") BETWEEN '{$current_month_date}' AND '{$after_7_days_month_date}' AND status='active' ORDER BY hiring_date" );

    $results_arr = [];

    foreach( $results as $result) {
        $results_arr[] = ( object ) [
            'user_id'           => $result->user_id,
            'hiring_date'       => $result->hiring_date,
            'hiring_date_only'  => date( 'd', strtotime( $result->hiring_date ) ),
        ];
    }
    usort($results_arr, function($a, $b) {
        return $a->hiring_date_only > $b->hiring_date_only;
    });
    return ( object ) $results_arr;
}

/**
 * Generate email section body for weekly digest email
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function generate_mail_section_body( $data, $start_tag, $heading,  $end_tag = null ) {
    $loop_text = "";
    if ( count( ( array ) $data ) == 0 ) {
        //return "";
        $loop_text .= "<li> Currently there is no upcoming information for this week </li>";
    }
    foreach( $data as $dt ) {

        if ( $end_tag != null ) {
            $end_tag_date = " &mdash; " . date( ' M j', strtotime( $dt->$end_tag ) );
        } else {
            $end_tag_date = '';
        }

        $loop_text .=
            "<li>" . get_user_meta( $dt->user_id, 'first_name', true ) . ' ' . get_user_meta( $dt->user_id, 'last_name', true ) . ' &mdash; ' . date( 'M j', strtotime( $dt->$start_tag ) ) . $end_tag_date . "</li>";
    }
    return "<div style='padding: 20px;background-color: #f0f8ff;width: 90%; margin: 20px auto;border-radius: 15px;'><h3> {$heading} </h3><ul>{$loop_text}</ul></div>";
}


/**
 * Get approved email of this week
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function get_approved_leave_by_week( $current_date, $after_7_days_date ) {
    $leave_list =  erp_hr_get_current_month_leave_list();
    $leave_list_arr = [];
    foreach( $leave_list as $ll ) {
        if ( $ll->start_date >= $current_date && $ll->start_date <= $after_7_days_date ) {
            $leave_list_arr[] = ( object ) [
                'user_id'       => $ll->user_id,
                'start_date'    => $ll->start_date,
                'end_date'      => $ll->end_date,
            ];
        }
    }
    return ( object ) $leave_list_arr;
}

/**
 * Generate email body for weekly digest email
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function get_digest_email_body( $current_date, $after_7_days_date ) {

    $get_employees_by_hiring_date               = get_employees_by_hiring_date( $current_date, $after_7_days_date );
    $html_for_new_member_joining                = generate_mail_section_body( $get_employees_by_hiring_date, 'hiring_date', 'New Team Member Joining' );

    $get_employees_by_birth_month               = get_employees_by_birth_month( $current_date, $after_7_days_date );
    $html_for_birth_month                       = generate_mail_section_body( $get_employees_by_birth_month, 'date_of_birth', 'Birthday This Week' );

    $leave_request                              = get_approved_leave_by_week( $current_date, $after_7_days_date );
    $html_for_leave_request                     = generate_mail_section_body( $leave_request, 'start_date', 'Who is Out This Week', 'end_date' );

    $c_t_employees                              = get_about_to_end_employees( $current_date );
    $html_for_c_t_employees                     = generate_mail_section_body( $c_t_employees, 'end_date', 'Contract About to End' );

    $get_upcomming_hiring_date_anniversary      = get_upcomming_hiring_date_anniversary( $current_date, $after_7_days_date );
    $html_for_hiring_date_anniversary           = generate_mail_section_body( $get_upcomming_hiring_date_anniversary, 'hiring_date', 'Work Anniversary This Week' );


    $html_wrapper                               = "<div>{$html_for_new_member_joining} {$html_for_birth_month} {$html_for_leave_request} {$html_for_c_t_employees} {$html_for_hiring_date_anniversary}</div>";
    return $html_wrapper;
}

/**
 * Send weekly digest email to hr
 *
 * @since 1.5.6
 *
 * @return mixed
 *
 */
function send_weekly_digest_email_to_hr() {

    if ( current_time( 'l' ) != 'Monday' ) {
        return false;
    }

    $current_date                   = current_time( 'Y-m-d' );
    $after_7_days_date              = date( 'Y-m-d', strtotime( '+6 days' ) );

    $current_m_d                    = date( 'M d', strtotime( $current_date ) );
    $after_7_days_date_m_d          = date( 'M d', strtotime( $after_7_days_date ) );
    $current_year                   = date( 'Y', strtotime( $current_date ) );

    $args = array(
        'role'    => 'erp_hr_manager',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );

    $hr_managers = get_users( $args );

    $email_recipient = "";
    foreach( $hr_managers as $hr_manager ) {
        $email_recipient .= $hr_manager->user_email . ',';
    }

    $email              = new WeDevs\ERP\Email();
    $email->id          = 'weekly-digest-email-to-hr';
    $email->title       = __( 'Weekly digest email to HR Manager', 'erp' );
    $email->description = __( 'Send weekly digest email to HR Manager with general information', 'erp' );
    $email->subject     = __( 'Weekly Digest on ' . get_bloginfo( 'name' ), 'erp' );
    $email->heading     = __( "Weekly Digest Email <h3> {$current_m_d} - {$after_7_days_date_m_d}, {$current_year}</h3>", 'erp' );
    $email->recipient   = $email_recipient;

    $email_body         = $email->get_template_content( WPERP_INCLUDES . '/email/email-body.php', [
        'email_heading' => $email->heading,
        'email_body'    => wpautop( get_digest_email_body( $current_date, $after_7_days_date ) ),
    ] );
    $email->send( $email->get_recipient(), $email->get_subject(), $email_body, $email->get_headers(), $email->get_attachments() );
}

/****************************  Weekly Digest Email to HR manager  End   **********************************/

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
