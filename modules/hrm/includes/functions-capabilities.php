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

    $hr_manager_role = erp_hr_get_manager_role();

    // What capability is being checked?
    switch ( $cap ) {

        /** Employees **********************************************************/

        case 'read_employee':
        case 'edit_employee':
            $employee_id = $args[0];

            if ( $user_id == $employee_id ) {
                $caps[] = 'employee';
            } else {

                // HR manager can read any employee
                if ( user_can( $user_id, $hr_manager_role ) ) {
                    $caps = array( $hr_manager_role );
                }
            }

            break;

        case 'list_employee':
        case 'manage_employee':

            if ( user_can( $user_id, $hr_manager_role ) ) {
                $caps = array( $hr_manager_role );
            }

            break;

        /** Departments **********************************************************/

        case 'manage_department':
            if ( user_can( $user_id, $hr_manager_role ) ) {
                $caps = array( $hr_manager_role );
            }

            break;

        /** Designations **********************************************************/

        case 'manage_designations':
            if ( user_can( $user_id, $hr_manager_role ) ) {
                $caps = array( $hr_manager_role );
            }

            break;

        /** Leave and Holidays ****************************************************/

        case 'leave_list_policies':
        case 'leave_manage_policies':
        case 'leave_manage_requests':
        case 'manage_holiday':

            if ( user_can( $user_id, $hr_manager_role ) ) {
                $caps = array( $hr_manager_role );
            }

            break;

        case 'list_holiday':

            if ( user_can( $user_id, 'employee' ) || $is_manager ) {
                $caps = array( $hr_manager_role );
            }

            break;
    }

    return apply_filters( 'erp_hr_map_meta_caps', $caps, $cap, $user_id, $args );
}

