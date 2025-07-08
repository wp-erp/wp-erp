<?php

use WeDevs\ERP\HRM\Models\Department;
use WeDevs\ERP\HRM\Models\Employee;

/**
 * Create a new department
 *
 * @param  array   arguments
 *
 * @return int|false
 */
function erp_hr_create_department( $args = [] ) {
    $defaults = [
        'id'          => 0,
        'title'       => '',
        'description' => '',
        'lead'        => 0,
        'parent'      => 0,
        'status'      => 1,
    ];

    $fields = wp_parse_args( $args, $defaults );

    // validation
    if ( empty( $fields['title'] ) ) {
        return new WP_Error( 'no-name', __( 'No department name provided.', 'erp' ) );
    }

    // unset the department id
    $dept_id = $fields['id'];
    unset( $fields['id'] );

    $department = new \WeDevs\ERP\HRM\Models\Department();

    if ( ! $dept_id ) {
        $dept = $department->create( $fields );

        do_action( 'erp_hr_dept_new', $dept->id, $fields );
        erp_hrm_purge_cache( [ 'list' => 'department', 'department_id' => $dept->id ] );

        return $dept->id;
    } else {
        do_action( 'erp_hr_dept_before_updated', $dept_id, $fields );

        $department->find( $dept_id )->update( $fields );

        do_action( 'erp_hr_dept_after_updated', $dept_id, $fields );
        erp_hrm_purge_cache( [ 'list' => 'department', 'department_id' => $dept_id ] );

        return $dept_id;
    }

    return false;
}

/**
 * Get all the departments of a company
 *
 * @param  int  the company id
 *
 * @return array list of departments
 */
function erp_hr_get_departments( $args = [] ) {
    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'asc',
        'no_object'  => false,
    ];

    $args  = wp_parse_args( $args, $defaults );

    $last_changed  = erp_cache_get_last_changed( 'hrm', 'department' );
    $cache_key     = 'erp-get-departments-' . md5( serialize( $args ) ) . " : $last_changed";
    $departments   = wp_cache_get( $cache_key, 'erp' );

    if ( false === $departments ) {
        $department = new \WeDevs\ERP\HRM\Models\Department();

        if ( ! empty( $args['s'] ) ) {
            if ( isset( $_GET['s'] ) ) {
                $s = sanitize_text_field( wp_unslash( $_GET['s'] ) );
            } else {
                $s = sanitize_text_field( wp_unslash( $args['s'] ) );
            }

            $results = $department
                ->where( 'title', 'LIKE', '%' . $s . '%' )
                ->get()
                ->toArray();
        } else {
            $results = $department->get()->toArray();
        }

        $results = erp_array_to_object( $results );
        $results = erp_parent_sort( $results );

        $departments = [];

        if ( $results ) {
            foreach ( $results as $row ) {
                if ( true === $args['no_object'] ) {
                    $departments[] = $row;
                } else {
                    $departments[] = new WeDevs\ERP\HRM\Department( intval( $row->id ) );
                }
            }
        }

        wp_cache_set( $cache_key, $departments, 'erp' );
    }

    return $departments;
}

/**
 * Get all department from a company
 *
 * @param int  $company_id company id
 * @param bool $no_object  if set true, Department object will be
 *                         returned as array. $wpdb rows otherwise
 *
 * @return array the department
 */
function erp_hr_count_departments() {
    return \WeDevs\ERP\HRM\Models\Department::count();
}

/**
 * Delete a department
 *
 * @param  int  department id
 *
 * @return bool
 */
function erp_hr_delete_department( $department_id ) {
    if ( is_array( $department_id ) ) {
        $exist_employee     = [];
        $not_exist_employee = [];

        foreach ( $department_id as $key => $department ) {
            $dept = new \WeDevs\ERP\HRM\Department( intval( $department ) );

            if ( $dept->num_of_employees() ) {
                $exist_employee[] = $department;
            } else {
                do_action( 'erp_hr_dept_delete', $dept );
                $not_exist_employee[] = $department;
            }
        }

        if ( $not_exist_employee ) {
            \WeDevs\ERP\HRM\Models\Department::destroy( $not_exist_employee );
        }

        return $exist_employee;
    }

    $department = new \WeDevs\ERP\HRM\Department( intval( $department_id ) );

    if ( $department->num_of_employees() ) {
        return new WP_Error( 'not-empty', __( 'You can not delete this department because it contains employees.', 'erp' ) );
    }

    do_action( 'erp_hr_dept_delete', $department_id );

    $parent_id = \WeDevs\ERP\HRM\Models\Department::where( 'id', '=', $department_id )->pluck( 'parent' )[0];

    if ( $parent_id ) {
        \WeDevs\ERP\HRM\Models\Department::where( 'parent', '=', $department_id )->update( ['parent' => $parent_id ] );
    } else {
        \WeDevs\ERP\HRM\Models\Department::where( 'parent', '=', $department_id )->update( ['parent' => 0 ] );
    }

    $resp = \WeDevs\ERP\HRM\Models\Department::find( $department_id )->delete();

    erp_hrm_purge_cache( [ 'list' => 'department', 'department_id' => $department_id ] );

    return $resp;
}

/**
 * Get the raw departments dropdown
 *
 * @param  int  company id
 * @param string $select_label pass any string to be as the first element
 *
 * @return array the key-value paired departments
 */
function erp_hr_get_departments_dropdown_raw( $select_label = null ) {
    $departments = erp_hr_get_departments();
    $dropdown    = [ '-1' => __( '- Select Department -', 'erp' ) ];

    if ( $select_label ) {
        $dropdown    = [ '-1' => $select_label ];
    }

    if ( $departments ) {
        foreach ( $departments as $key => $department ) {
            $dropdown[$department->id] = stripslashes( $department->title );
        }
    }

    return $dropdown;
}

/**
 * Get company departments dropdown
 *
 * @param  string  selected department
 *
 * @return string the dropdown
 */
function erp_hr_get_departments_dropdown( $selected = '' ) {
    $departments = erp_hr_get_departments_dropdown_raw();
    $dropdown    = '';

    if ( $departments ) {
        foreach ( $departments as $key => $title ) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}

/**
 * Get employee's department lead by employee's user ID
 *
 * @param $user_id
 *
 * @return int
 */
function erp_hr_get_department_lead_by_user( $user_id ) {
    $employee = new \WeDevs\ERP\HRM\Employee( $user_id );

    if ( $employee->get_department() ) {
        $department      = new \WeDevs\ERP\HRM\Department( intval( $employee->get_department() ) );
        $department_lead = $department->get_lead();
    }

    return empty( $department_lead ) ? 0 : $department_lead->id;
}

/**
 * Get department employees
 *
 * @since 1.6.0
 *
 * @param $lead_id
 *
 * @return array
 */
function erp_hr_get_dept_lead_subordinate_employees( $lead_id ) {
    $ret = [];

    $depts_id = Department::select( 'id' )->where( 'lead', absint( $lead_id ) );

    if ( $depts_id->count() ) {
        $depts_id = $depts_id->pluck( 'id' )->toArray();

        $users_id = Employee::select( 'user_id' )->whereIn( 'department', $depts_id );

        if ( $users_id->count() ) {
            $ret = $users_id->pluck( 'user_id' )->toArray();
        }
    }

    return $ret;
}

/**
 * Check if this user_id's department lead is the current logged_in user
 *
 * @since 1.6.0
 *
 * @return bool
 */
function erp_hr_match_user_dept_lead_with_current_user( $user_id ) {
    $employee       = Employee::select( 'department' )->where( 'user_id', $user_id )->first();
    $emp_department = absint( $employee->department );

    if ( $emp_department ) {
        $department = Department::find( $emp_department );

        if ( get_current_user_id() === absint( $department->lead ) ) {
            return true;
        }
    }

    return false;
}


/**
 * Get all departments with title and id
 * example output: [1 => 'Department 1', 2 => 'Department 2']
 * @return array
 */
function erp_hr_get_departments_fresh() {
    $departments = \WeDevs\ERP\HRM\Models\Department::all(['id', 'title']);
    return array_column( $departments->toArray(), 'title', 'id');

}
