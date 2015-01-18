<?php

/**
 * Insert a new leave policy
 *
 * @param array $args
 */
function erp_hr_leave_insert_policy( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'         => null,
        'company_id' => erp_get_current_company_id(),
        'name'       => '',
        'unit'       => 'day',
        'value'      => 0,
        'color'      => '',
        'created_on' => current_time( 'mysql' )
    );

    $args = wp_parse_args( $args, $defaults );

    // some validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No name provided.', 'wp-error' ) );
    }

    if ( ! intval( $args['value'] ) ) {
        return new WP_Error( 'no-value', __( 'No duration provided.', 'wp-error' ) );
    }

    $args['name'] = sanitize_text_field( $args['name'] );

    $policy_id = (int) $args['id'];
    unset( $args['id'] );

    if ( ! $policy_id ) {
        // it's new
        if ( $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_policies', $args ) ) {

            do_action( 'erp_hr_leave_policy_new', $wpdb->insert_id, $args );

            return $wpdb->insert_id;
        }

    } else {
        // do update method here
        unset( $args['created_on'] );
        $args['updated_on'] = current_time( 'mysql' );

        if ( $wpdb->update( $wpdb->prefix . 'erp_hr_leave_policies', $args, array( 'id' => $policy_id ) ) ) {

            do_action( 'erp_hr_leave_policy_updated', $policy_id, $args );

            return $policy_id;
        }
    }
}

/**
 * [erp_get_companies description]
 *
 * @return array
 */
function erp_hr_leave_get_policies( $company_id ) {
    global $wpdb;

    $cache_key = 'erp-leave-pol-' . $company_id;
    $policies = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $policies ) {
        $policies = $wpdb->get_results(
            $wpdb->prepare( "SELECT id, name, value, color FROM {$wpdb->prefix}erp_hr_leave_policies WHERE company_id = %d", $company_id )
        );

        wp_cache_set( $cache_key, $policies, 'wp-erp' );
    }

    return $policies;
}

/**
 * Delete a policy
 *
 * @param  int  policy id
 *
 * @return bool
 */
function erp_hr_leave_policy_delete( $policy_id ) {
    global $wpdb;

    do_action( 'erp_hr_leave_policy_delete', $policy_id );

    return $wpdb->delete( $wpdb->prefix . 'erp_hr_leave_policies', array( 'id' => $policy_id ) );
}

function erp_hr_leave_insert_entitlement( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'         => null,
        'user_id'    => 0,
        'policy_id'  => 0,
        'days'       => 0,
        'from_date'  => '',
        'to_date'    => '',
        'comments'   => '',
        'status'     => '',
        'created_by' => get_current_user_id(),
        'created_on' => current_time( 'mysql' )
    );

    $fields = wp_parse_args( $args, $defaults );

    $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_policies', $fields );
}

/**
 * Add a new leave request
 *
 * @param  array   $args
 *
 * @return int  request id
 */
function erp_hr_leave_insert_request( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'user_id'      => 0,
        'leave_policy' => 0,
        'start_date'   => current_time('mysql'),
        'end_date'     => current_time('mysql'),
        'comment'      => '',
        'status'       => 0
    );

    $args = wp_parse_args( $args, $defaults );
    extract( $args );

    if ( ! intval( $user_id ) ) {
        return new WP_Error( 'no-employee', __( 'No employee ID provided.', 'wp-error' ) );
    }

    if ( ! intval( $leave_policy ) ) {
        return new WP_Error( 'no-policy', __( 'No leave policy provided.', 'wp-error' ) );
    }

    $start_date = new DateTime( $start_date );
    $end_date   = new DateTime( $end_date );
    $end_date->modify( '+1 day' ); // to get proper days in duration
    $diff = $start_date->diff( $end_date );

    // we got a negative date
    if ( $diff->invert || ! $diff->days ) {
        return new WP_Error( 'invalid-date', __( 'Invalid date provided', 'wp-erp' ) );
    }

    $interval = DateInterval::createFromDateString( '1 day' );
    $period   = new DatePeriod( $start_date, $interval, $end_date );

    // prepare the periods
    $leaves = array();
    foreach ( $period as $dt ) {
        $leaves[] = array(
            'date'          => $dt->format( 'Y-m-d' ),
            'length_hours'  => '08:00:00',
            'length_days'   => '1.00',
            'start_time'    => '00:00:00',
            'end_time'      => '00:00:00',
            'duration_type' => 1
        );
    }

    if ( $leaves ) {

        $request = array(
            'user_id'    => $user_id,
            'policy_id'  => $leave_policy,
            'comments'   => wp_kses_post( $comment ),
            'status'     => 0,
            'created_by' => get_current_user_id(),
            'created_on' => current_time( 'mysql' ),
        );

        if ( $wpdb->insert( $wpdb->prefix . 'erp_hr_leave_requests', $request ) ) {
            $request_id = $wpdb->insert_id;

            foreach ($leaves as $leave) {
                $leave['request_id'] = $request_id;

                $wpdb->insert( $wpdb->prefix . 'erp_hr_leaves', $leave );
            }

            do_action( 'erp_hr_leave_new', $request_id, $request, $leaves );

            return $request_id;
        }
    }

    return false;
}