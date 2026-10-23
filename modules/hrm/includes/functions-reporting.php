<?php
/**
 * Get All Reporting Titles
 *
 * @return array
 */
function erp_hr_get_reports() {
    $reports = [
        'age-profile' => [
            'title'       => __( 'Age Profile', 'erp' ),
            'description' => __( 'Shows age breakdown data in your company in different departments.', 'erp' ),
        ],
        'gender-profile' => [
            'title'       => __( 'Gender Profile', 'erp' ),
            'description' => __( 'Shows differentiation data by gender in your company.', 'erp' ),
        ],
        'headcount' => [
            'title'       => __( 'Head Count', 'erp' ),
            'description' => __( 'Displays actual number of individuals in your company in different departments.', 'erp' ),
        ],
        'salary-history' => [
            'title'       => __( 'Salary History', 'erp' ),
            'description' => __( 'Shows Salary History of the employees of your company.', 'erp' ),
        ],
        'years-of-service' => [
            'title'       => __( 'Years of Service', 'erp' ),
            'description' => __( 'Shows longevity and experience report of the employees of your company.', 'erp' ),
        ],
        'leaves' => [
            'title'       => __( 'Leaves', 'erp' ),
            'description' => __( 'Shows leaves report.', 'erp' ),
        ],
    ];

    return apply_filters( 'erp_hr_reports', $reports );
}

/**
 * Get report breakdown by age
 *
 * @return array
 */
function get_employee_breakdown_by_age( $employees ) {
    $_under18   = 0;
    $_18_to_25  = 0;
    $_26_to_35  = 0;
    $_36_to_45  = 0;
    $_46_to_55  = 0;
    $_56_to_65  = 0;
    $_65_plus   = 0;

    foreach ( $employees as $employee ) {
        if ( ! erp_is_valid_date( $employee->date_of_birth ) ) {
            continue;
        }

        $dob      = new DateTime( $employee->date_of_birth );
        $now      = new DateTime();
        $interval = $now->diff( $dob );
        $age      = $interval->y;

        if ( $age > 0 && $age < 18 ) {
            $_under18++;
            continue;
        }

        if ( $age >= 18 && $age <= 25 ) {
            $_18_to_25++;
            continue;
        }

        if ( $age >= 26 && $age <= 35 ) {
            $_26_to_35++;
            continue;
        }

        if ( $age >= 36 && $age <= 45 ) {
            $_36_to_45++;
            continue;
        }

        if ( $age >= 46 && $age <= 55 ) {
            $_46_to_55++;
            continue;
        }

        if ( $age >= 56 && $age <= 65 ) {
            $_56_to_65++;
            continue;
        }

        if ( $age > 65 ) {
            $_65_plus++;
        }
    }

    $count = [
        '_under_18'  => $_under18,
        '_18_to_25'  => $_18_to_25,
        '_26_to_35'  => $_26_to_35,
        '_36_to_45'  => $_36_to_45,
        '_46_to_55'  => $_46_to_55,
        '_56_to_65'  => $_56_to_65,
        '_65_plus'   => $_65_plus,
    ];

    return $count;
}

/**
 * Counts diffrenet genders in employees
 *
 * @return array
 */
function erp_hr_get_gender_count( $department = null ) {
    global $wpdb;

    if ( null == $department ) {
        $all_user_id = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active'" );
    } else {
        $all_user_id = $wpdb->get_col( 
            $wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE department = %d AND status = %s",
                $department,
                'active'
            )
        );
    }

    if ( $all_user_id ) {
        // One meta query for the whole list instead of one per employee.
        update_meta_cache( 'user', array_map( 'intval', $all_user_id ) );

        foreach ( $all_user_id as $user_id ) {
            $gender_single = get_user_meta( $user_id, 'gender', true );

            if ( is_string( $gender_single ) ) {
                $gender_all[]  = $gender_single;
            }
        }

        $gender_counted = array_count_values( $gender_all );
    }

    $gender['male']   = isset( $gender_counted['male'] ) ? $gender_counted['male'] : 0;
    $gender['female'] = isset( $gender_counted['female'] ) ? $gender_counted['female'] : 0;
    $gender['other']  = isset( $gender_counted['other'] ) ? $gender_counted['other'] : 0;

    return $gender;
}

/**
 * Gets data for Employee Breakdown Table on ERP HR Reporting
 *
 * @return object
 */
function erp_hr_get_age_breakdown_data() {
    $employees          = new \WeDevs\ERP\HRM\Models\Employee();
    $departments        = erp_hr_get_departments();
    $age_breakdown_data = [];
    $tot_under18        = 0;
    $tot_18_to_25       = 0;
    $tot_26_to_35       = 0;
    $tot_36_to_45       = 0;
    $tot_46_to_55       = 0;
    $tot_56_to_65       = 0;
    $tot_65plus         = 0;

    foreach ( $departments as $department ) {
        $emp_by_dept      = $employees->where( [ 'department' => $department->id, 'status' => 'active' ] )->get();
        $emp_by_dept_data = get_employee_breakdown_by_age( $emp_by_dept );

        $tot_under18 += $emp_by_dept_data['_under_18'];
        $tot_18_to_25 += $emp_by_dept_data['_18_to_25'];
        $tot_26_to_35 += $emp_by_dept_data['_26_to_35'];
        $tot_36_to_45 += $emp_by_dept_data['_36_to_45'];
        $tot_46_to_55 += $emp_by_dept_data['_46_to_55'];
        $tot_56_to_65 += $emp_by_dept_data['_56_to_65'];
        $tot_65plus += $emp_by_dept_data['_65_plus'];

        $age_breakdown_data[] = [
            'department'  => $department->title,
            '_under18'    => $emp_by_dept_data['_under_18'],
            '_18_to_25'   => $emp_by_dept_data['_18_to_25'],
            '_26_to_35'   => $emp_by_dept_data['_26_to_35'],
            '_36_to_45'   => $emp_by_dept_data['_36_to_45'],
            '_46_to_55'   => $emp_by_dept_data['_46_to_55'],
            '_56_to_65'   => $emp_by_dept_data['_56_to_65'],
            '_65_plus'    => $emp_by_dept_data['_65_plus'],
        ];
    }

    $age_breakdown_data[] = [
        'department'  => 'Total',
        '_under18'    => $tot_under18,
        '_18_to_25'   => $tot_18_to_25,
        '_26_to_35'   => $tot_26_to_35,
        '_36_to_45'   => $tot_36_to_45,
        '_46_to_55'   => $tot_46_to_55,
        '_56_to_65'   => $tot_56_to_65,
        '_65_plus'    => $tot_65plus,
    ];

    $age_breakdown_data = erp_array_to_object( $age_breakdown_data );

    return $age_breakdown_data;
}

/**
 * Get count Employee Breakdown Table rows on ERP HR Reporting
 *
 * @return int
 */
function erp_hr_count_age_breakdown() {
    $count = count( erp_hr_get_departments() );

    return ++$count;
}

/**
 * Get data for Gender Ratio List Table
 *
 *@return array
 */
function erp_hr_get_gender_ratio_data() {
    $gender_count = erp_hr_get_gender_count();
    $gender_total = $gender_count['male'] + $gender_count['female'] + $gender_count['other'];

    if ( empty( $gender_total ) ) {
        return;
    }

    $gender_ratio_data = [
        'male' => [
            'gender'     => 'Male',
            'count'      => $gender_count['male'],
            'percentage' => number_format( ( $gender_count['male'] * 100 ) / $gender_total, 2 ) . '%',
        ],
        'female' => [
            'gender'     => 'Female',
            'count'      => $gender_count['female'],
            'percentage' => number_format( ( $gender_count['female'] * 100 ) / $gender_total, 2 ) . '%',
        ],
        'other' => [
            'gender'     => 'Unspecified',
            'count'      => $gender_count['other'],
            'percentage' => number_format( ( $gender_count['other'] * 100 ) / $gender_total, 2 ) . '%',
        ],
        'total' => [
            'gender'     => 'Total',
            'count'      => $gender_total,
            'percentage' => '100%',
        ],
    ];

    $gender_ratio_data = erp_array_to_object( $gender_ratio_data );

    return $gender_ratio_data;
}

/**
 * Returns Employee headcount by date/month
 *
 * @return number
 */
function erp_hr_get_headcount( $date = '', $dept = '', $query_type = '' ) {
    global $wpdb;

    $count = 0;

    // Optimized month headcount: one COUNT instead of a per-employee DatePeriod
    // loop. The dashboard calls this 12× (one per chart month); the old loop was
    // O(employees × months-since-hire) and caused 504 gateway timeouts.
    if ( 'month' === $query_type ) {
        $month_start = $date . '-01';
        $month_end   = gmdate( 'Y-m-t', strtotime( $month_start ) );
        $dept_sql    = $dept ? $wpdb->prepare( ' AND department = %d', (int) $dept ) : '';

        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}erp_hr_employees
             WHERE hiring_date IS NOT NULL AND hiring_date <> '0000-00-00'
               AND hiring_date <= '" . esc_sql( $month_end ) . "'
               AND ( termination_date IS NULL OR termination_date = '0000-00-00' OR termination_date >= '" . esc_sql( $month_start ) . "' )
               {$dept_sql}"
        );
    }

    $all_user_data = $wpdb->get_results( "SELECT user_id, department, hiring_date, termination_date, status FROM {$wpdb->prefix}erp_hr_employees ", ARRAY_A );

    if ( 'date' == $query_type ) {
        $date = strtotime( $date );

        foreach ( $all_user_data as $user_data ) {
            $date_start = strtotime( $user_data['hiring_date'] );
            $date_last  = '0000-00-00' == $user_data['termination_date'] ? strtotime( 'now' ) : strtotime( $user_data['termination_date'] );

            if ( $date >= $date_start && $date <= $date_last ) {
                $count++;
            }
        }
    }

    if ( 'month' == $query_type ) {
        foreach ( $all_user_data as $user_data ) {
            if ( isset( $user_data['status'] ) && 'terminated' == $user_data['status'] && gmdate( 'Y-m', strtotime( $user_data['termination_date'] ) ) > $date ) {
                $count++;
                continue;
            }

            if ( '0000-00-00' == $user_data['hiring_date'] ) {
                continue;
            }

            if ( $dept && $dept != $user_data['department'] ) {
                continue;
            }

            $date_start = $user_data['hiring_date'];
            $date_last  = '0000-00-00' == $user_data['termination_date'] ? current_time( 'Y-m-d' ) : $user_data['termination_date'];

            $start    = ( new DateTime( $date_start ) )->modify( 'first day of this month' );
            $end      = ( new DateTime( $date_last ) )->modify( 'last day of this month' );
            $interval = DateInterval::createFromDateString( '1 month' );
            $period   = new DatePeriod( $start, $interval, $end );

            foreach ( $period as $months ) {
                if ( $date == $months->format( 'Y-m' ) ) {
                    $count++;
                    break;
                }
            }
        }
    }

    return $count;
}

/**
 * Display fields for a set of employees, loaded in a fixed number of queries.
 *
 * The reports, the org chart and the department / designation lists only need a
 * name, an avatar and a few titles per row. Building one
 * `\WeDevs\ERP\HRM\Employee` per row costs several queries each, which adds up
 * to thousands per request on a few hundred employees. Every value here matches
 * what the `Employee` accessor of the same name returns.
 *
 * Nothing is hidden per viewer (the `erp_hr_employee_restricted_data` filter is
 * not applied), so callers must gate the fields they expose.
 *
 * @since 1.18.0
 *
 * @param int[] $user_ids    Employee WP user ids.
 * @param int   $avatar_size Avatar size in pixels.
 *
 * @return array Keyed by the requested id. `user_id` is 0 when the WP user no
 *               longer exists.
 */
function erp_hr_get_employee_display_data( $user_ids, $avatar_size = 32 ) {
    global $wpdb;

    $user_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $user_ids ) ) ) );

    if ( empty( $user_ids ) ) {
        return [];
    }

    // Users and their meta in two queries.
    cache_users( $user_ids );

    $in   = implode( ',', $user_ids );
    $rows = [];

    // Soft-deleted rows included, as `Employee` loads them `withTrashed()`.
    $results = $wpdb->get_results(
        "SELECT user_id, employee_id, designation, department, location, reporting_to, hiring_date, status
         FROM {$wpdb->prefix}erp_hr_employees
         WHERE user_id IN ( {$in} )
         ORDER BY id DESC"
    );

    foreach ( (array) $results as $row ) {
        $rows[ (int) $row->user_id ] = $row;
    }

    $titles = [
        'designation' => [ 'table' => "{$wpdb->prefix}erp_hr_designations", 'column' => 'title', 'map' => [] ],
        'department'  => [ 'table' => "{$wpdb->prefix}erp_hr_depts", 'column' => 'title', 'map' => [] ],
        'location'    => [ 'table' => "{$wpdb->prefix}erp_company_locations", 'column' => 'name', 'map' => [] ],
    ];

    foreach ( $titles as $field => $lookup ) {
        $ids = array_values( array_unique( array_filter( array_map( 'absint', wp_list_pluck( $rows, $field ) ) ) ) );

        if ( empty( $ids ) ) {
            continue;
        }

        $found = $wpdb->get_results( "SELECT id, {$lookup['column']} AS label FROM {$lookup['table']} WHERE id IN ( " . implode( ',', $ids ) . ' )' );

        foreach ( (array) $found as $item ) {
            $titles[ $field ]['map'][ (int) $item->id ] = stripslashes( (string) $item->label );
        }
    }

    $users     = [];
    $photo_ids = [];

    foreach ( $user_ids as $user_id ) {
        $user = get_userdata( $user_id );

        if ( ! $user ) {
            continue;
        }

        $users[ $user_id ] = $user;

        if ( isset( $user->photo_id ) && (int) $user->photo_id ) {
            $photo_ids[] = (int) $user->photo_id;
        }
    }

    if ( $photo_ids ) {
        _prime_post_caches( array_unique( $photo_ids ), false, true );
    }

    $data = [];

    foreach ( $user_ids as $user_id ) {
        $item = [
            'user_id'           => 0,
            'full_name'         => '',
            'display_name'      => null,
            'email'             => '',
            'avatar'            => '',
            'employee_id'       => null,
            'designation_title' => null,
            'department_title'  => null,
            'location_name'     => null,
            'reporting_to'      => 0,
            'hiring_date'       => null,
            'status'            => null,
        ];

        if ( ! isset( $users[ $user_id ] ) ) {
            $item['avatar']   = get_avatar_url( 0, [ 'size' => $avatar_size ] );
            $data[ $user_id ] = $item;
            continue;
        }

        $user = $users[ $user_id ];
        $row  = isset( $rows[ $user_id ] ) ? $rows[ $user_id ] : null;
        $name = [];

        foreach ( [ 'first_name', 'middle_name', 'last_name' ] as $key ) {
            $part = isset( $user->$key ) ? stripslashes( (string) $user->$key ) : '';

            if ( $part ) {
                $name[] = $part;
            }
        }

        $full_name = implode( ' ', $name );

        if ( '' === $full_name ) {
            foreach ( [ $user->display_name, $user->user_login, $user->user_email ] as $fallback ) {
                if ( '' !== (string) $fallback ) {
                    $full_name = (string) $fallback;
                    break;
                }
            }
        }

        $photo_id = isset( $user->photo_id ) ? (int) $user->photo_id : 0;

        $item['user_id']      = $user_id;
        $item['full_name']    = $full_name;
        $item['display_name'] = stripslashes( (string) $user->display_name );
        $item['email']        = $user->user_email;
        $item['avatar']       = $photo_id ? wp_get_attachment_url( $photo_id ) : get_avatar_url( $user_id, [ 'size' => $avatar_size ] );

        if ( isset( $user->employee_id ) ) {
            $item['employee_id'] = stripslashes( (string) $user->employee_id );
        }

        if ( $row ) {
            if ( null !== $row->employee_id ) {
                $item['employee_id'] = stripslashes( (string) $row->employee_id );
            }

            foreach ( [ 'designation' => 'designation_title', 'department' => 'department_title', 'location' => 'location_name' ] as $field => $key ) {
                if ( isset( $titles[ $field ]['map'][ (int) $row->$field ] ) ) {
                    $item[ $key ] = $titles[ $field ]['map'][ (int) $row->$field ];
                }
            }

            $item['reporting_to'] = (int) $row->reporting_to;
            $item['status']       = $row->status;

            if ( erp_is_valid_date( $row->hiring_date ) && '0000-00-00' !== $row->hiring_date ) {
                $item['hiring_date'] = $row->hiring_date;
            }
        }

        $data[ $user_id ] = $item;
    }

    return $data;
}

if ( ! function_exists( 'is_valid_date' ) ) :
    /**
     * Check if a string is valid date
     *
     * @since 0.1
     *
     * @deprecated 1.10.1
     *
     * @return bool
     */
    function is_valid_date( $str ) {
        try {
            $dt = new DateTime( trim( $str ) );
        } catch ( Exception $e ) {
            return false;
        }

        $month = $dt->format( 'm' );
        $day   = $dt->format( 'd' );
        $year  = $dt->format( 'Y' );

        if ( checkdate( $month, $day, $year ) ) {
            return true;
        } else {
            return false;
        }
    }
endif;
