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

    $count         = 0;
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
