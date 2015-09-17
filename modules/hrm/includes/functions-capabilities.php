<?php

/**
 * HR Capabilities
 *
 * The functions in this file are used primarily as convenient wrappers for
 * capability output in user profiles. This includes mapping capabilities and
 * groups to human readable strings,
 *
 * @package WP ERP
 * @subpackage HR
 */

/**
 * The manager role for HR employees
 *
 * @return string
 */
function erp_hr_get_manager_role() {
    return apply_filters( 'erp_hr_get_manager_role', 'erp_hr_manager' );
}

/**
 * The manager role for HR employees
 *
 * @return string
 */
function erp_hr_get_employee_role() {
    return apply_filters( 'erp_hr_get_employee_role', 'employee' );
}

/**
 * Get restricted roles
 *
 * These are the roles we don't want to assign users publicly
 *
 * @return array
 */
function erp_hr_get_restricted_roles() {
    $roles = [ erp_hr_get_manager_role() ];

    return apply_filters( 'erp_hr_get_restricted_roles', $roles );
}

/**
 * Returns an array of capabilities based on the role that is being requested.
 *
 * @param  string  $role
 *
 * @return array
 */
function erp_hr_get_caps_for_role( $role = '' ) {
    $caps = [];

    // Which role are we looking for?
    switch ( $role ) {

        case erp_hr_get_manager_role():
            $caps = [
                'read'                     => true,

                // employee
                'erp_list_employee'        => true,
                'erp_create_employee'      => true,
                'erp_view_employee'        => true,
                'erp_edit_employee'        => true,

                'erp_create_review'        => true,
                'erp_delete_review'        => true,
                'erp_manage_review'        => true,

                'erp_manage_announcement'  => true,

                'erp_manage_jobinfo'       => true,
                'erp_view_jobinfo'         => true,

                // department
                'erp_manage_department'    => true,

                // designation
                'erp_manage_designation'   => true,

                // leave and holidays
                'erp_leave_create_request' => true,
                'erp_leave_manage'         => true,
            ];
            break;

        case erp_hr_get_employee_role():

            $caps = [
                'read'                     => true,
                'erp_list_employee'        => true,
                'erp_view_employee'        => true,
                'erp_edit_employee'        => true,
                'erp_view_jobinfo'         => true,
                'erp_leave_create_request' => true,
            ];

            break;
    }

    return apply_filters( 'erp_hr_get_caps_for_role', $caps, $role );
}

/**
 * Maps HR capabilities to employee or HR manager
 *
 * @param array $caps Capabilities for meta capability
 * @param string $cap Capability name
 * @param int $user_id User id
 * @param mixed $args Arguments
 *
 * @return array Actual capabilities for meta capability
 */
function erp_hr_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

    // What capability is being checked?
    switch ( $cap ) {

        case 'erp_view_employee':
        case 'erp_edit_employee':
            $employee_id = isset( $args[0] ) ? $args[0] : false;

            if ( $user_id == $employee_id ) {
                $caps = [ $cap ];
            } else {

                $hr_manager_role = erp_hr_get_manager_role();

                // HR manager can read any employee
                if ( user_can( $user_id, $hr_manager_role ) ) {
                    $caps = array( $hr_manager_role );
                }
            }

            break;

        case 'erp_create_review':
            $employee_id = isset( $args[0] ) ? $args[0] : false;
            $employee    = new Employee( $employee_id );

            if ( $employee->get_reporting_to() && $employee->get_reporting_to()->ID == $user_id ) {
                $caps = [ 'employee' ];
            } else {
                $caps = [ $cap ];
            }

            break;
    }

    return apply_filters( 'erp_hr_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Removes the HR roles from the editable roles array
 *
 * @param array $all_roles All registered roles
 *
 * @return array
 */
function erp_hr_filter_editable_roles( $all_roles = [] ) {
    $blocked = erp_hr_get_restricted_roles();

    foreach ($blocked as $hr_role) {

        // Loop through WordPress roles
        foreach ( array_keys( $all_roles ) as $wp_role ) {

            // If keys match, unset
            if ( $wp_role === $hr_role ) {
                unset( $all_roles[$wp_role] );
            }
        }
    }

    return $all_roles;
}
