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
 * Maps HR capabilities
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
            # code...
            break;

        case 'edit_employee':
            # code...
            break;

        case 'list_employee':
            # code...
            break;

        case 'manage_employee':
            # code...
            break;

        /** Departments **********************************************************/

        case 'manage_department':
            # code...
            break;

        /** Designations **********************************************************/

        case 'manage_designations':
            # code...
            break;

        /** Leave and Holidays ****************************************************/

        case 'leave_list_policies':
            # code...
            break;

        case 'leave_manage_policies':
            # code...
            break;

        case 'leave_manage_requests':
            # code...
            break;

        case 'list_holiday':
            # code...
            break;

        case 'manage_holiday':
            # code...
            break;

    }

    return apply_filters( 'erp_hr_map_meta_caps', $caps, $cap, $user_id, $args );
}

