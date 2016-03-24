<?php

/**
 * The manager role for CRM user
 *
 * @since 1.0
 *
 * @return string
 */
function erp_crm_get_manager_role() {
    return apply_filters( 'erp_crm_get_manager_role', 'erp_crm_manager' );
}

/**
 * The Crm Agent role for CRM user
 *
 * @since 1.0
 *
 * @return string
 */
function erp_crm_get_agent_role() {
    return apply_filters( 'erp_crm_get_agent_role', 'erp_crm_agent' );
}

/**
 * When a new administrator is created,
 * make him CRM Manager by default
 *
 * @since 1.0
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
 * Return a user's CRM roles
 *
 * @since 1.0
 *
 * @param int $user_id
 *
 * @return string
 */
function erp_crm_get_user_role( $user_id = 0 ) {

    // if user_id is not set or 0, then the user is current user
    if ( ! $user_id ) {
        global $current_user;
        $user = $current_user;
    } else {
        $user = get_userdata( $user_id );
    }

    $role = false;

    // User has roles so look for a HR one
    if ( ! empty( $user->roles ) ) {

        // Look for a ac role
        $roles = array_intersect(
            array_values( $user->roles ),
            array_keys( erp_crm_get_roles() )
        );

        if ( !empty( $roles ) ) {
            $role = array_shift( $roles );
        }
    }

    return apply_filters( 'erp_crm_get_user_role', $role, $user_id, $user );
}

/**
 * Get dynamic roles for CRM
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_roles() {
    $roles = [
        erp_crm_get_manager_role() => [
            'name'         => __( 'CRM Manager', 'wp-erp' ),
            'public'       => false,
            'capabilities' => erp_crm_get_caps_for_role( erp_crm_get_manager_role() )
        ],

        erp_crm_get_agent_role() => [
            'name'         => __( 'CRM Agent', 'wp-erp' ),
            'public'       => false,
            'capabilities' => erp_crm_get_caps_for_role( erp_crm_get_agent_role() )
        ],

    ];

    return apply_filters( 'erp_crm_get_roles', $roles );
}

/**
 * Get caps for individual Roles
 *
 * @since 1.0
 *
 * @param  string $role
 *
 * @return array
 */
function erp_crm_get_caps_for_role( $role = '' ) {
	$caps = [];

    // Which role are we looking for?
    switch ( $role ) {

        case erp_crm_get_manager_role():
            $caps = [ 'read' => true ];
            break;

        case erp_crm_get_agent_role():
            $caps = [ 'read' => true ];
            break;
    }

    return apply_filters( 'erp_crm_get_caps_for_role', $caps, $role );
}

/**
 * Check is current user is manager
 *
 * @since 1.0
 *
 * @return boolean
 */
function erp_crm_is_current_user_manager() {
    $current_user_role = erp_crm_get_user_role( get_current_user_id() );

    if ( erp_crm_get_manager_role() !=  $current_user_role ) {
        return false;
    }

    return true;
}

/**
 * Check is current user is CRM Agent
 *
 * @since 1.0
 *
 * @return boolean
 */
function erp_crm_is_current_user_crm_agent() {
    $current_user_role = erp_crm_get_user_role( get_current_user_id() );

    if ( erp_crm_get_agent_role() !=  $current_user_role ) {
        return false;
    }

    return true;
}

/**
 * Check crm permission for users
 *
 * @since 1.0
 *
 * @param  object $employee
 *
 * @return void
 */
function erp_crm_permission_management_field( $employee ) {

    if ( ! erp_crm_is_current_user_manager() ) {
        return;
    }

    $is_manager = user_can( $employee->id, erp_crm_get_manager_role() ) ? 'on' : 'off';
    $is_agent   = user_can( $employee->id, erp_crm_get_agent_role() ) ? 'on' : 'off';

    erp_html_form_input( array(
        'label' => __( 'CRM Manager', 'wp-erp' ),
        'name'  => 'crm_manager',
        'type'  => 'checkbox',
        'tag'   => 'div',
        'value' => $is_manager,
        'help'  => __( 'This Employee is Manager', 'wp-erp'  )
    ) );

    erp_html_form_input( array(
        'label' => __( 'CRM Agent', 'wp-erp' ),
        'name'  => 'crm_agent',
        'type'  => 'checkbox',
        'tag'   => 'div',
        'value' => $is_agent,
        'help'  => __( 'This Employee is CRM agent', 'wp-erp'  )
    ) );
}

