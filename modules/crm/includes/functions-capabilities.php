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

    if ( $user && in_array('administrator', $user->roles) ) {
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
            'name'         => __( 'CRM Manager', 'erp' ),
            'public'       => false,
            'capabilities' => erp_crm_get_caps_for_role( erp_crm_get_manager_role() )
        ],

        erp_crm_get_agent_role() => [
            'name'         => __( 'CRM Agent', 'erp' ),
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
            $caps = [
                'read'                     => true,
                'upload_files'             => true,
                'erp_crm_list_contact'     => true,
                'erp_crm_add_contact'      => true,
                'erp_crm_edit_contact'     => true,
                'erp_crm_delete_contact'   => true,
                'erp_crm_manage_activites' => true,
                'erp_crm_manage_dashboard' => true,
                'erp_crm_manage_schedules' => true,
                'erp_crm_manage_groups'    => true,
                'erp_crm_create_groups'    => true,
                'erp_crm_edit_groups'      => true,
                'erp_crm_delete_groups'    => true,

                // 'erp_crm_view_reports'     => true,
            ];

            break;

        case erp_crm_get_agent_role():
            $caps = [
                'read'                     => true,
                'upload_files'             => true,
                'erp_crm_list_contact'     => true,
                'erp_crm_add_contact'      => true,
                'erp_crm_edit_contact'     => true,
                'erp_crm_delete_contact'   => true,
                'erp_crm_manage_activites' => true,
                'erp_crm_manage_dashboard' => true,
                'erp_crm_manage_schedules' => true,
                'erp_crm_manage_groups'    => true,
            ];
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
        'label' => __( 'CRM Manager', 'erp' ),
        'name'  => 'crm_manager',
        'type'  => 'checkbox',
        'tag'   => 'div',
        'value' => $is_manager,
        'help'  => __( 'This Employee is CRM Manager', 'erp'  )
    ) );

    erp_html_form_input( array(
        'label' => __( 'CRM Agent', 'erp' ),
        'name'  => 'crm_agent',
        'type'  => 'checkbox',
        'tag'   => 'div',
        'value' => $is_agent,
        'help'  => __( 'This Employee is CRM agent', 'erp'  )
    ) );
}

/**
 * Dynamically Map CRM capabilities
 *
 * @since 1.0
 *
 * @param  array   $caps
 * @param  string  $cap
 * @param  integer $user_id
 * @param  array   $args
 *
 * @return array
 */
function erp_crm_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
    switch ( $cap ) {

        /**
         * CRM Manager -> can soft+hard delete own and others contacts
         * CRM Manager && CRM Agent -> can soft+hard delete own and others contacts
         * CRM Agent -> can only soft delete own contacts
         * None -> cannot delete
         */
        case 'erp_crm_edit_contact':
        case 'erp_crm_delete_contact':
            $contact_id      = isset( $args[0] ) ? $args[0] : false;
            $data_hard       = isset( $args[1] ) ? $args[1] : false;

            $crm_manager_role = erp_crm_get_manager_role();
            $crm_agent_role   = erp_crm_get_agent_role();

            if ( ! user_can( $user_id, $crm_manager_role ) && user_can( $user_id, $crm_agent_role ) ) {
                $contact_user_id = \WeDevs\ERP\Framework\Models\People::select('user_id')->where( 'id', $contact_id )->first();

                if ( isset( $contact_user_id->user_id ) && $contact_user_id->user_id ) {
                    $assign_id = get_user_meta( $contact_user_id->user_id, 'contact_owner', true );
                } else {
                    $assign_id = erp_people_get_meta( $contact_id, 'contact_owner', true );
                }

                if ( $assign_id != $user_id ) {
                    $caps = ['do_not_allow'];
                } else {
                    if ( $data_hard ) {
                        $caps = ['do_not_allow'];
                    }
                }

            } else if ( ! user_can( $user_id, $crm_manager_role ) ) {
                $caps = ['do_not_allow'];
            }

        break;
    }

    return apply_filters( 'erp_crm_map_meta_caps', $caps, $cap, $user_id, $args );

}

/**
 * Check permission to make WordPress User
 *
 * @since 1.1.18
 *
 * @return boolean
 */
function erp_crm_current_user_can_make_wp_user() {
    $has_permission = false;

    if ( current_user_can( 'administrator' ) || erp_crm_is_current_user_manager() ) {
        $has_permission = true;

    } else if ( erp_crm_is_current_user_crm_agent() && apply_filters( 'erp_crm_agent_can_make_wp_user', true ) ) {
        $has_permission = true;
    }

    return $has_permission;
}


/**
 * Removes the non-public CRM roles from the editable roles array
 *
 * @param array $all_roles All registered roles
 *
 * @return array
 */
function erp_crm_filter_editable_roles( $all_roles = [] ) {
    $roles = erp_crm_get_roles();

    foreach ( $roles as $crm_role_key => $crm_role ) {

        if ( isset( $crm_role['public'] ) && $crm_role['public'] === false ) {

            // Loop through WordPress roles
            foreach ( array_keys( $all_roles ) as $wp_role ) {

                // If keys match, unset
                if ( $wp_role === $crm_role_key ) {
                    unset( $all_roles[ $wp_role ] );
                }
            }
        }

    }

    return $all_roles;
}
