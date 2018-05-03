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
 * Get dynamic roles for HR
 *
 * @return array
 */
function erp_hr_get_roles() {
    $roles = [
        erp_hr_get_manager_role() => [
            'name'         => __( 'HR Manager', 'erp' ),
            'public'       => false,
            'capabilities' => erp_hr_get_caps_for_role( erp_hr_get_manager_role() )
        ],

        erp_hr_get_employee_role() => [
            'name'         => __( 'Employee', 'erp' ),
            'public'       => true,
            'capabilities' => erp_hr_get_caps_for_role( erp_hr_get_employee_role() )
        ]
    ];

    return apply_filters( 'erp_hr_get_roles', $roles );
}

/**
 * Returns an array of capabilities based on the role that is being requested.
 *
 * @param  string $role
 *
 * @return array
 */
function erp_hr_get_caps_for_role( $role = '' ) {
    $caps = [];

    // Which role are we looking for?
    switch ( $role ) {

        case erp_hr_get_manager_role():
            $caps = [
                'read' => true,

                'upload_files'             => true,
                'erp_view_list'            => true,

                // employee
                'erp_list_employee'        => true,
                'erp_create_employee'      => true,
                'erp_view_employee'        => true,
                'erp_edit_employee'        => true,
                'erp_delete_employee'      => true,

                // review (note, permission, performance)
                'erp_create_review'        => true,
                'erp_delete_review'        => true,
                'erp_manage_review'        => true,

                // announcement
                'erp_crate_announcement'   => true,
                'erp_view_announcement'    => true,
                'erp_manage_announcement'  => true,

                // job
                'erp_manage_jobinfo'       => true,
                'erp_view_jobinfo'         => true,

                // department
                'erp_manage_department'    => true,

                // designation
                'erp_manage_designation'   => true,

                // leave and holidays
                'erp_leave_create_request' => true,
                'erp_leave_manage'         => true,

                'erp_manage_hr_settings' => true,

                // @since 1.3.2

                // experience
                'erp_create_experience'  => true,
                'erp_edit_experience'    => true,
                'erp_view_experience'    => true,
                'erp_delete_experience'  => true,

                // education
                'erp_create_education'   => true,
                'erp_edit_education'     => true,
                'erp_view_education'     => true,
                'erp_delete_education'   => true,

                'erp_can_terminate'     => true,

                // dependent
                'erp_create_dependent'  => true,
                'erp_edit_dependent'    => true,
                'erp_view_dependent'    => true,
                'erp_delete_dependent'  => true,

                // document
                'erp_create_document'   => true,
                'erp_edit_document'     => true,
                'erp_view_document'     => true,
                'erp_delete_document'   => true,

                // attendance
                'erp_create_attendance' => true,
                'erp_edit_attendance'   => true,
                'erp_view_attendance'   => true,
                'erp_delete_attendance' => true,

            ];
            break;

        case erp_hr_get_employee_role():

            $caps = [
                'read' => true,

                'upload_files'             => true,

                //cap for listing any resources
                'erp_view_list'            => true,

                // employee
                'erp_list_employee'        => true,
                'erp_view_employee'        => true,
                'erp_edit_employee'        => true,

                // job
                'erp_view_jobinfo'         => true,

                // leave
                'erp_leave_create_request' => true,

                // @since 1.3.2

                // announcement
                'erp_view_announcement'    => true,

                // experience
                'erp_create_experience'    => true,
                'erp_edit_experience'      => true,
                'erp_view_experience'      => true,
                'erp_delete_experience'    => true,

                // education
                'erp_create_education'     => true,
                'erp_edit_education'       => true,
                'erp_view_education'       => true,
                'erp_delete_education'     => true,

                // dependent
                'erp_create_dependent'     => true,
                'erp_edit_dependent'       => true,
                'erp_view_dependent'       => true,
                'erp_delete_dependent'     => true,

                // document
                'erp_create_document'      => true,
                'erp_edit_document'        => true,
                'erp_view_document'        => true,
                'erp_delete_document'      => true,

                // attendance
                'erp_view_attendance'      => true
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

        // employee and hr manager
        case 'erp_list_employee':
            if ( user_can( $user_id, 'employee' ) ) {
                $caps = [ $cap ];
            }
            break;
        case 'erp_view_list':
            if ( user_can( $user_id, 'employee' ) ) {
                $caps = [ $cap ];
            }
            break;
        case 'erp_view_employee':
        case 'erp_edit_employee':

        case 'erp_view_announcement':

        case 'erp_view_jobinfo':

        case 'erp_leave_create_request':

        case 'erp_create_experience':
        case 'erp_view_experience':
        case 'erp_edit_experience':
        case 'erp_delete_experience':

        case 'erp_create_education':
        case 'erp_view_education':
        case 'erp_edit_education':
        case 'erp_delete_education':

        case 'erp_create_dependent':
        case 'erp_view_dependent':
        case 'erp_edit_dependent':
        case 'erp_delete_dependent':

        case 'erp_create_document':
        case 'erp_view_document':
        case 'erp_edit_document':
        case 'erp_delete_document':

        case 'erp_view_attendance':
            $employee_id = isset( $args[0] ) ? $args[0] : false;

            if ( $user_id == $employee_id ) {
                $caps = [ $cap ];
            } else {
                $hr_manager_role = erp_hr_get_manager_role();
                // HR manager can read any employee
                if ( user_can( $user_id, $hr_manager_role ) ) {
                    $caps = array( $hr_manager_role );
                } else {
                    $caps = [ 'do_not_allow' ];
                }
            }

            break;

        // only hr manager
        case 'erp_create_employee':
        case 'erp_delete_employee':
        case 'erp_can_terminate':

        case 'erp_crate_announcement':
        case 'erp_manage_announcement':

        case 'erp_manage_jobinfo':

        case 'erp_manage_department':

        case 'erp_manage_designation':

        case 'erp_leave_manage':

        case 'erp_manage_hr_settings':

        case 'erp_create_attendance':
        case 'erp_edit_attendance':
        case 'erp_delete_attendance':
            $hr_manager_role = erp_hr_get_manager_role();

            if ( user_can( $user_id, $hr_manager_role ) ) {
                $caps = array( $hr_manager_role );
            } else {
                $caps = [ 'do_not_allow' ];
            }

            break;

        // review (note, permission, performance)
        case 'erp_create_review':
        case 'erp_manage_review':
        case 'erp_delete_review':
            $employee_id = isset( $args[0] ) ? $args[0] : false;
            $employee    = new \WeDevs\ERP\HRM\Employee( $employee_id );

            if ( $employee->get_reporting_to() && $employee->get_reporting_to() == $user_id ) {
                $caps = [ 'employee' ];
            } else {
                $caps = [ $cap ];
            }

            break;
    }

    return apply_filters( 'erp_hr_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Removes the non-public HR roles from the editable roles array
 *
 * @param array $all_roles All registered roles
 *
 * @return array
 */
function erp_hr_filter_editable_roles( $all_roles = [] ) {
    $roles = erp_hr_get_roles();

    foreach ( $roles as $hr_role_key => $hr_role ) {

        if ( isset( $hr_role['public'] ) && $hr_role['public'] === false ) {

            // Loop through WordPress roles
            foreach ( array_keys( $all_roles ) as $wp_role ) {

                // If keys match, unset
                if ( $wp_role === $hr_role_key ) {
                    unset( $all_roles[ $wp_role ] );
                }
            }
        }

    }

    return $all_roles;
}

/**
 * Return a user's HR role
 *
 * @param int $user_id
 *
 * @return string
 */
function erp_hr_get_user_role( $user_id = 0 ) {

    // Validate user id
    $user = get_userdata( $user_id );
    $role = false;

    // User has roles so look for a HR one
    if ( ! empty( $user->roles ) ) {

        // Look for a HR role
        $roles = array_intersect(
            array_values( $user->roles ),
            array_keys( erp_hr_get_roles() )
        );

        // If there's a role in the array, use the first one. This isn't very
        // smart, but since roles aren't exactly hierarchical, and HR
        // does not yet have a UI for multiple user roles, it's fine for now.
        if ( ! empty( $roles ) ) {
            $role = array_shift( $roles );
        }
    }

    return apply_filters( 'erp_hr_get_user_role', $role, $user_id, $user );
}

/**
 * Create a new employee when a user role is changed to employee
 *
 * @param  int $user_id
 * @param  string $role
 *
 * @return void
 */
function erp_hr_existing_role_to_employee( $user_id, $role ) {
    if ( 'employee' != $role ) {
        return;
    }

    // check if a employee of that ID exists, otherwise create one
    $employee = new \WeDevs\ERP\HRM\Models\Employee();
    $exists   = $employee->where( 'user_id', '=', $user_id )->get()->first();

    if ( null === $exists ) {
        $employee->create( [
            'user_id'     => $user_id,
            'designation' => 0,
            'department'  => 0,
            'status'      => 'active'
        ] );
    }
}

/**
 * When a new administrator is created, make him HR Manager by default
 *
 * @param  int $user_id
 *
 * @return void
 */
function erp_hr_new_admin_as_manager( $user_id ) {
    $user = get_user_by( 'id', $user_id );

    if ( $user && in_array( 'administrator', $user->roles ) ) {
        $user->add_role( erp_hr_get_manager_role() );
    }
}
