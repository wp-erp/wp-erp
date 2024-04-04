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
        'sun' => 0,
    ];

    $option_key = 'erp_settings_erp-hr_workdays';

    $wizard_settings = get_option( $option_key, $default );

    $days = [
        'mon' => get_option( 'mon', $wizard_settings['mon'] ),
        'tue' => get_option( 'tue', $wizard_settings['tue'] ),
        'wed' => get_option( 'wed', $wizard_settings['wed'] ),
        'thu' => get_option( 'thu', $wizard_settings['thu'] ),
        'fri' => get_option( 'fri', $wizard_settings['fri'] ),
        'sat' => get_option( 'sat', $wizard_settings['sat'] ),
        'sun' => get_option( 'sun', $wizard_settings['sun'] ),
    ];

    return apply_filters( 'work_days', $days );
}

/**
 * Get working day without off day
 *
 * @since  0.1
 *
 * @param string $start_date
 * @param string $end_date
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

    $dates         = [
		'days' => [],
		'total' => 0,
	];
    $work_days     = erp_hr_get_work_days();
    $holiday_exist = erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date );

    foreach ( $between_dates as $date ) {
        $key       = strtolower( gmdate( 'D', strtotime( $date ) ) );
        $is_holidy = ( $work_days[ $key ] == 0 ) ? true : false;

        if ( ! $is_holidy ) {
            $is_holidy = in_array( $date, $holiday_exist ) ? true : false;
        }

        if ( class_exists( '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module' ) && get_option( 'erp_pro_sandwich_leave', '' ) === 'yes' ) {
            $dates['days'][] = [
                'date'  => $date,
                'count' => (int) ! $is_holidy,
            ];

            ++$dates['total'];
        } elseif ( ! $is_holidy ) {
            $dates['days'][] = [
                'date'  => $date,
                'count' => (int) ! $is_holidy,
            ];

            ++$dates['total'];
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
 * @param string $start_date
 * @param string $end_date
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

    $dates         = [
		'days' => [],
		'total' => 0,
		'sandwich' => 0,
	];
    $work_days     = erp_hr_get_work_days();
    $holiday_exist = erp_hr_leave_get_holiday_between_date_range( $start_date, $end_date );

    $sandwich_rules_applied = erp_hr_can_apply_sandwich_rules_between_dates( $start_date, $end_date, $user_id );

    if ( ! empty( $sandwich_rules_applied ) ) {
        $between_dates = array_merge( $sandwich_rules_applied, $between_dates );
    }

    foreach ( $between_dates as $date ) {
        $key       = strtolower( gmdate( 'D', strtotime( $date ) ) );
        $is_holidy = ( $work_days[ $key ] == '0' ) ? true : false;

        if ( ! $is_holidy ) {
            $is_holidy = in_array( $date, $holiday_exist ) ? true : false;
        }

        $dates['days'][] = [
            'date'  => $date,
            'count' => (int) ! $is_holidy,
        ];

        if ( class_exists( '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module' ) && get_option( 'erp_pro_sandwich_leave', '' ) === 'yes' ) {
            ++$dates['total'];

            // mark sandwich rule to true
            if ( $is_holidy ) {
                $dates['sandwich'] = 1;
            }
        } elseif ( ! $is_holidy ) {
            ++$dates['total'];
        }
    }

    return $dates;
}

/**
 * Check if we can apply sandwich rule between two dates
 *
 * @since  1.6.0
 *
 * @param string $start_date
 * @param string $end_date
 *
 * @return array
 */
function erp_hr_can_apply_sandwich_rules_between_dates( $start_date, $end_date, $user_id = null ) {
    // check pro active
    if ( ! class_exists( '\WeDevs\ERP_PRO\PRO\AdvancedLeave\Module' ) ) {
        return [];
    }

    global $wpdb;
    $work_days = erp_hr_get_work_days();

    $previous_dates = [];
    $next_dates     = [];

    if ( get_option( 'erp_pro_sandwich_leave', '' ) === 'yes' && absint( $user_id ) ) {
        // get previous leave request for this user either approved or pending, skip rejected
        $last_leave_request = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT rq.start_date, rq.end_date, rq.last_status
                        FROM {$wpdb->prefix}erp_hr_leave_requests as rq
                        where rq.user_id = %d and rq.end_date < %d order by rq.id DESC limit 1",
                [ $user_id, strtotime( $start_date ) ]
            ),
            ARRAY_A
        );

        if ( is_array( $last_leave_request ) && ! empty( $last_leave_request ) ) {
            // proceed further for pending or accepted request
            if ( $last_leave_request['last_status'] != 3 ) {
                $last_req_end_date  = erp_current_datetime()->setTimestamp( $last_leave_request['end_date'] )->modify( '+1 days' )->format( 'Y-m-d' );
                $start_day_previous = erp_current_datetime()->modify( $start_date )->modify( '-1 days' )->format( 'Y-m-d' );
                //date extract between last leave date and current leave start dates
                $previous_between_dates = erp_extract_dates( $last_req_end_date, $start_day_previous );

                //check holiday or non-working day exist between last_req and current start_date
                $previous_holiday_exist = erp_hr_leave_get_holiday_between_date_range( $last_req_end_date, $start_day_previous );

                foreach ( $previous_between_dates as $date ) {
                    $key       = strtolower( gmdate( 'D', strtotime( $date ) ) );
                    $is_holidy = ( $work_days[ $key ] == '0' ) ? true : false;

                    if ( ! $is_holidy ) {
                        $is_holidy = in_array( $date, $previous_holiday_exist ) ? true : false;
                    }

                    if ( $is_holidy ) {
                        $previous_dates[] = $date;
                    } else {
                        $previous_dates = [];
                        break;
                    }
                }
            }
        }

        //get next leave request
        $next_leave_request = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT rq.start_date, rq.end_date, rq.last_status
                        FROM {$wpdb->prefix}erp_hr_leave_requests as rq
                        where rq.user_id = %d and rq.start_date > %d order by rq.id DESC limit 1",
                [ $user_id, strtotime( $end_date ) ]
            ),
            ARRAY_A
        );

        if ( is_array( $next_leave_request ) && ! empty( $next_leave_request ) ) {
            // proceed further for pending or accepted request
            if ( $next_leave_request['last_status'] != 3 ) {
                $last_req_start_date = erp_current_datetime()->setTimestamp( $next_leave_request['start_date'] )->modify( '-1 days' )->format( 'Y-m-d' );
                $end_date_next_day   = erp_current_datetime()->modify( $end_date )->modify( '+1 days' )->format( 'Y-m-d' );
                //date extract between last leave date and current leave start dates
                $previous_between_dates = erp_extract_dates( $end_date_next_day, $last_req_start_date );

                //check holiday or non-working day exist between last_req and current start_date
                $previous_holiday_exist = erp_hr_leave_get_holiday_between_date_range( $end_date_next_day, $last_req_start_date );

                foreach ( $previous_between_dates as $date ) {
                    $key       = strtolower( gmdate( 'D', strtotime( $date ) ) );
                    $is_holidy = ( $work_days[ $key ] == '0' ) ? true : false;

                    if ( ! $is_holidy ) {
                        $is_holidy = in_array( $date, $previous_holiday_exist ) ? true : false;
                    }

                    if ( $is_holidy ) {
                        $next_dates[] = $date;
                    } else {
                        $next_dates = [];
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
 * @since 1.8.5 Fixed sorting when there is no parentless item
 *
 * @param array $objects input objects with attributes 'id' and 'parent'
 * @param array $result  (optional, reference) internal
 * @param int   $parent  (optional) internal
 * @param int   $depth   (optional) internal
 *
 * @return array output
 */
function erp_parent_sort( array $objects, array &$result = [], $parent = 0, $depth = 0 ) {
    $parents = [];

    foreach ( $objects as $object ) {
        $parents[] = intval( $object->parent );
    }

    if ( ! empty( $parents ) && min( $parents ) !== 0 ) {
        return $objects;
    }

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
    $anniversary_wish_email = wperp()->emailer->get_email( 'HiringAnniversaryWish' );

    if ( is_a( $anniversary_wish_email, '\WeDevs\ERP\Email' ) ) {
        $db = new \WeDevs\ORM\Eloquent\Database();

        $employees = erp_array_to_object( \WeDevs\ERP\HRM\Models\Employee::select( 'user_id', 'hiring_date' )
            ->where( $db->raw( "DATE_FORMAT( `hiring_date`, '%m %d' )" ), \Carbon\Carbon::today()->format( 'm d' ) )
            ->where( 'status', 'active' )
            ->get()
            ->toArray() );

        foreach ( $employees as $employee ) {
            $anniversary_wish_email->trigger( $employee->user_id, $employee->hiring_date );
        }
    }
}

/**
 * Prevent redirect to woocommerce my account page
 *
 * @param bool $prevent_access
 *
 * @since 1.1.18
 *
 * @return bool
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
function erp_hr_filter_collection_by_date( $collection, $date, $field = 'created_at' ) {
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
 */
function erp_hr_get_disabled_notification_users() {
    $user_emails = wp_cache_get( 'erp_hr_get_disabled_notification_users', 'erp' );

    if ( false == $user_emails ) {
        $user_query  = new WP_User_Query( [
            'fields'     => [ 'user_email' ],
            'meta_key'   => 'erp_hr_disable_notification',
            'meta_value' => 'on',
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
 */
function erp_hr_exclude_recipients( $recipients ) {
    $disabled_notification_user = erp_hr_get_disabled_notification_users();

    if ( ! empty( $disabled_notification_user ) ) {
        if ( is_string( $recipients ) ) {
            $recipients = explode( ',', $recipients );
        }
        $recipients                 = array_map( 'trim', $recipients );
        $recipients                 = array_map( 'strtolower', $recipients );
        $disabled_notification_user = array_map( 'strtolower', $disabled_notification_user );
        $recipients                 = array_diff( $recipients, $disabled_notification_user );
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
 */
function erp_hr_send_birthday_wish_email( $user_id ) {
    $birthday_wish_email = wperp()->emailer->get_email( 'BirthdayWish' );

    if ( is_a( $birthday_wish_email, '\WeDevs\ERP\Email' ) ) {
        $birthday_wish_email->trigger( $user_id );
    }
}
/****************** Send Birthday wish email End ********************/

/**
 * Send holiday reminder email to employees
 *
 * @since 1.7.1
 *
 * @return void
 */
function erp_hr_holiday_reminder_to_employees() {
    $start_date = erp_current_datetime()->format( 'Y-m-d H:i:s' );
    $end_date   = erp_current_datetime()->modify( 'next day' )->format( 'Y-m-d H:i:s' );

    $holiday = new \WeDevs\ERP\HRM\Models\LeaveHoliday();
    $holiday = $holiday->where(
        function ( $condition ) use ( $start_date, $end_date ) {
            $condition->whereBetween( 'start', [ $start_date, $end_date ] );
        }
    );
    $holidays        = $holiday->get()->toArray();
    $employees       = erp_hr_get_employees( [ 'number' => -1 ] );

    $emailer = wperp()->emailer->get_email( 'GovtHolidayReminder' );

    if ( ! is_a( $emailer, '\WeDevs\ERP\Email' ) ) {
        return;
    }
    foreach ( $holidays as $holiday ) {
        $holiday_title = $holiday['title'];
        $holiday_start = erp_current_datetime()->modify( $holiday['start'] );
        $holiday_end   = erp_current_datetime()->modify( $holiday['end'] );
        $day_diff      = $holiday_start->diff( $holiday_end )->days;
        $reopen_date   = $holiday_end->modify( 'next day' )->format( 'l, F j, Y' );

        $holiday_title_formated = '<strong>' . $holiday_title . '</strong>';
        $holiday_start_formated = $holiday_start->format( 'l, F j, Y' );
        $holiday_end_formated   = $holiday_end->format( 'l, F j, Y' );
        $reopen_date_formated   = '<strong>' . $reopen_date . '</strong>';

        if ( 0 === $day_diff ) {
            $holiday_duration = 'on <strong>' . $holiday_start_formated . '</strong>';
        } else {
            $holiday_duration = 'from <strong>' . $holiday_start_formated . ' to ' . $holiday_end_formated . '</strong>';
        }

        foreach ( $employees as $employee ) {
            $user_id = $employee->user_id;
            $emailer->trigger( $user_id, $holiday_title_formated, $holiday_duration, $reopen_date_formated );
        }
    }
}

/**
 * Retrieves html for hr people menu
 *
 * @since 1.8.0
 * @since 1.8.5 Added `Requests` as new menu item
 *
 * @param string $selected
 *
 * @return void
 */
function erp_hr_get_people_menu_html( $selected = '' ) {
    $dropdown = [
        'employee'     => [
            'title' => esc_html__( 'Employees', 'erp' ),
            'cap'   => 'erp_list_employee',
        ],
        'requests'     => [
            'title' => esc_html__( 'Requests', 'erp' ),
            'cap'   => 'erp_hr_manager',
        ],
        'department'   => [
            'title' => esc_html__( 'Departments', 'erp' ),
            'cap'   => 'erp_manage_department',
        ],
        'designation'  => [
            'title' => esc_html__( 'Designations', 'erp' ),
            'cap'   => 'erp_manage_designation',
        ],
        'announcement' => [
            'title' => esc_html__( 'Announcements', 'erp' ),
            'cap'   => 'erp_manage_announcement',
        ],
    ];

    $dropdown = apply_filters( 'erp_hr_people_menu_items', $dropdown );

    if ( empty( $selected ) ) {
        $selected = ! empty( $_GET['sub-section'] )
                    ? sanitize_text_field( wp_unslash( $_GET['sub-section'] ) )
                    : 'employee';
    }

    ob_start();
    ?>

    <div class="erp-custom-menu-container">
        <ul class="erp-nav">
            <?php foreach ( $dropdown as $key => $value ) : ?>
                <?php
                $pro_popup = '';
                $tooltip = '';
                if ( ! empty( $value['pro_popup'] ) ) {
                    // pro-popup-main
                    $pro_popup = '<span class="pro-popup-sub-nav erp-pro-tooltip pro-popup">Pro</span>';
//                    $tooltip = '<div class="erp-pro-tooltip-wrapper"><div class="erp-pro-tooltip-inner">
//                        <h4>Available in Pro. Also enjoy:</h4>
//                        <ul>
//                            <li><span class="dashicons dashicons-yes"></span>23+ premium extensions</li>
//                            <li><span class="dashicons dashicons-yes"></span>23+ premium extensions</li>
//                            <li><span class="dashicons dashicons-yes"></span>23+ premium extensions</li>
//                            <li><span class="dashicons dashicons-yes"></span>23+ premium extensions</li>
//                            <li><span class="dashicons dashicons-yes"></span>23+ premium extensions</li>
//                        </ul>
//                        <div class="tooltip-btn">
//                            <a href="#">Upgrade to PRO</a>
//                        </div>
//                    </div></div>';
                }

                if ( current_user_can( $value['cap'] ) ) :
					?>
                    <li class="<?php echo esc_attr( $key === $selected ? $key . ' active' : $key ); ?>"><a href="<?php echo ! empty( $value['pro_popup'] ) ? '#' : esc_url_raw( add_query_arg( array( 'sub-section' => $key ), admin_url( 'admin.php?page=erp-hr&section=people' ) ) ); ?>" class="" data-key="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $pro_popup ); ?></a> </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo ob_get_clean();
}

/**
 * Retrieves all employee request types
 *
 * @since 1.8.5
 *
 * @return array
 */
function erp_hr_get_employee_requests_types() {
    $results = erp_hr_get_leave_requests();

    $types = [
        'leave' => [
            'count'   => $results['total'],
            'label'   => __( 'Leave', 'erp' ),
        ],
    ];

    return apply_filters( 'erp_hr_employee_request_types', $types );
}

/**
 * Retrieves all pending requests counts
 *
 * @since 1.8.5
 *
 * @return array
 */
function erp_hr_get_employee_pending_requests_count() {
    $leave_requests = erp_hr_get_leave_requests( [
		'number' => -1,
		'status' => 2,
	] );

    $requests['leave'] = $leave_requests['total'];

    return apply_filters( 'erp_hr_employee_pending_request_count', $requests );
}


/**
 * Get ERP Financial Years
 *
 * @since 1.8.6
 *
 * @return array $f_years
 */
function erp_get_hr_financial_years() {
    global $wpdb;

    $f_years = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_hr_financial_years", ARRAY_A );

    return $f_years;
}

/**
 * ERP Settings save leave years
 *
 * @since 1.8.6
 *
 * @param array $post_data
 *
 * @return object|boolean WP_Error or true
 */
function erp_settings_save_leave_years( $post_data = [] ) {
    $year_names = [];

    // Error handles
    foreach ( $post_data as $key => $data ) {
        if ( empty( $data['fy_name'] ) ) {
            return new WP_Error( 'errors', __( 'Please give a financial year name on row #', 'erp' ) . ( $key + 1 ) );
        }
        if ( empty( $data['start_date'] ) ) {
            return new WP_Error( 'errors', __( 'Please give a financial year start date on row #', 'erp' ) . ( $key + 1 ) );
        }
        if ( empty( $data['end_date'] ) ) {
            return new WP_Error( 'errors', __( 'Please give a financial year end date on row #', 'erp' ) . ( $key + 1 ) );
        }
        if ( ( strtotime( $data['end_date'] ) < strtotime( $data['start_date'] ) ) || strtotime( $data['end_date'] ) === strtotime( $data['start_date'] ) ) {
            return new WP_Error( 'errors', __( 'End date must be greater than the start date on row #', 'erp' ) . ( $key + 1 ) );
        }

        if ( in_array( $data['fy_name'], $year_names ) ) {
            return new WP_Error( 'errors', __( 'Duplicate financial year name ', 'erp' ) . $data['fy_name'] . __( ' on row #', 'erp' ) . ( $key + 1 ) );
        } else {
            array_push( $year_names, $data['fy_name'] );
        }
    }

    global $wpdb;

    // Empty HR leave years data
    $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'erp_hr_financial_years' );

    // Insert leave years
    foreach ( $post_data as $data ) {
        $data['fy_name']     = sanitize_text_field( wp_unslash( $data['fy_name'] ) );
        $data['start_date']  = strtotime( sanitize_text_field( wp_unslash( $data['start_date'] ) ) );
        $data['end_date']    = strtotime( sanitize_text_field( wp_unslash( $data['end_date'] ) ) );
        $data['description'] = sanitize_text_field( wp_unslash( $data['description'] ) );
        $data['created_by']  = get_current_user_id();
        $data['created_at']  = gmdate( 'Y-m-d' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_hr_financial_years',
            $data,
            [ '%s', '%s', '%s', '%s', '%d', '%s' ]
        );
    }

    return true;
}
