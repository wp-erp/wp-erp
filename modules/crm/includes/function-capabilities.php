<?php

/**
 * The manager role for HR employees
 *
 * @return string
 */
function erp_crm_get_manager_role() {
    return apply_filters( 'erp_crm_get_manager_role', 'erp_crm_manager' );
}

/**
 * When a new administrator is created, make him CRM Manager by default
 *
 * @param  int  $user_id
 *
 * @return void
 */
function erp_crm_new_admin_as_manager( $user_id ) {
    $user = get_user_by( 'id', $user_id );
    $role = reset( $user->roles );

    if ( 'administrator' == $role ) {
        $user->add_role( erp_crm_get_manager_role() );
    }
}

/**
 * Return a user's HR role
 *
 * @param int $user_id
 *
 * @return string
 */
function erp_crm_get_user_role( $user_id = 0 ) {

    // Validate user id
    $user = get_userdata( $user_id );
    $role = false;

    // User has roles so look for a HR one
    if ( ! empty( $user->roles ) ) {

        // Look for a ac role
        $roles = array_intersect(
            array_values( $user->roles ),
            array_keys( erp_crm_get_roles() )
        );

        // If there's a role in the array, use the first one. This isn't very
        // smart, but since roles aren't exactly hierarchical, and HR
        // does not yet have a UI for multiple user roles, it's fine for now.
        if ( !empty( $roles ) ) {
            $role = array_shift( $roles );
        }
    }

    return apply_filters( 'erp_crm_get_user_role', $role, $user_id, $user );
}

/**
 * Get dynamic roles for HR
 *
 * @return array
 */
function erp_crm_get_roles() {
    $roles = [
        erp_crm_get_manager_role() => [
            'name'         => __( 'CRM Manager', 'wp-erp' ),
            'public'       => false,
            'capabilities' => erp_crm_get_caps_for_role( erp_crm_get_manager_role() )
        ]
    ];

    return apply_filters( 'erp_crm_get_roles', $roles );
}

function erp_crm_get_caps_for_role( $role = '' ) {
	$caps = [];

    // Which role are we looking for?
    switch ( $role ) {

        case erp_crm_get_manager_role():

            $caps = [ 'read' => true ];

            break;
    }

    return apply_filters( 'erp_crm_get_caps_for_role', $caps, $role );
}

function erp_crm_is_current_user_manager() {
    $current_user_role = erp_crm_get_user_role( get_current_user_id() ); 
    
    if ( erp_crm_get_manager_role() !=  $current_user_hr_role ) {
        return false;
    }

    return true;
}

function erp_crm_permission_management_field( $employee ) {

    if ( ! erp_crm_is_current_user_manager() ) {
        return;
    }

    $is_manager = user_can( $employee->id, erp_crm_get_manager_role() ) ? 'on' : 'off';

    erp_html_form_input( array(
        'label' => __( 'CRM Manager', 'wp-erp' ),
        'name'  => 'crm_manager',
        'type'  => 'checkbox',
        'tag'   => 'div',
        'value' => $is_manager,
        'help'  => __( 'This Employee is Manager', 'wp-erp'  )
    ) );
}

