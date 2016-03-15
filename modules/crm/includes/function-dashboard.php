<?php

/**
 * Register metabox widget in right side
 * for crm dashbaord
 *
 * @since 1.0
 *
 * @return void
 */
function erp_crm_dashboard_right_widgets_area() {
    erp_admin_dash_metabox( __( '<i class="fa fa-calendar-check-o"></i> Todays Schedules', 'wp-erp' ), 'erp_hr_dashboard_widget_todays_schedules' );
    erp_admin_dash_metabox( __( '<i class="fa fa-calendar-check-o"></i> Upcoming Schedules', 'wp-erp' ), 'erp_hr_dashboard_widget_upcoming_schedules' );
}

/**
 * Register metabox widget in left side
 * for crm dashboard
 *
 * @since 1.0
 *
 * @return void
 */
function erp_crm_dashboard_left_widgets_area() {
    erp_admin_dash_metabox( __( '<i class="fa fa-calendar"></i> My schedules', 'wp-erp' ), 'erp_hr_dashboard_widget_my_schedules' );
}

/**
 * CRM Dashboard Todays Schedules widgets
 *
 * @since 1.0
 *
 * @return void [html]
 */
function erp_hr_dashboard_widget_todays_schedules() {
    $todays_schedules = erp_crm_get_todays_schedules_activity( get_current_user_id() );
    ?>
    <ul class="erp-list list-two-side list-sep erp-crm-dashbaord-todays-schedules">
        <?php foreach ( $todays_schedules as $key => $schedule ) : ?>
            <li>
                <?php
                    $users_text   = '';
                    $invite_users = isset( $schedule['extra']['invite_contact'] ) ? $schedule['extra']['invite_contact'] : [];
                    $contact_user = $schedule['contact']['first_name'] . ' ' . $schedule['contact']['last_name'];

                    array_walk( $invite_users, function( &$val ) {
                        $val = get_the_author_meta( 'display_name', $val );
                    });

                    if ( count( $invite_users) == 1 ) {
                        $users_text = sprintf( '%s <span>%s</span>', __( 'and', 'wp-erp' ), reset( $invite_users ) );
                    } else if ( count( $invite_users) > 1 ) {
                        $users_text = sprintf( '%s <span class="erp-tips" title="%s">%d %s</span>', __( 'and', 'wp-erp' ), implode( '<br>', $invite_users ), count( $invite_users ), __( 'Others') );
                    }

                    if ( $schedule['log_type'] == 'meeting' ) {
                        echo sprintf( '%s <a href="%s">%s</a> %s %s %s', __( '<i class="fa fa-calendar"></i> Meeting with', 'wp-erp' ), erp_crm_get_details_url( $schedule['contact']['id'], $schedule['contact']['type'] ), $contact_user, $users_text, __( 'at', 'wp-erp' ), date( 'g:ia', strtotime( $schedule['start_date'] ) ) ) . " <a href='#' data-schedule_id=' " . $schedule['id'] . " ' data-title='" . $schedule['extra']['schedule_title'] . "' class='erp-crm-dashbaord-show-details-schedule'>" . __( 'Details &rarr;', 'wp-erp' ) . "</a>";
                    }

                    if ( $schedule['log_type'] == 'call' ) {
                        echo sprintf( '%s <a href="%s">%s</a> %s %s %s', __( '<i class="fa fa-phone"></i> Call to', 'wp-erp' ), erp_crm_get_details_url( $schedule['contact']['id'], $schedule['contact']['type'] ), $contact_user, $users_text, __( 'at', 'wp-erp' ), date( 'g:ia', strtotime( $schedule['start_date'] ) ) ) . " <a href='#' data-schedule_id=' " . $schedule['id'] . " ' data-title='" . $schedule['extra']['schedule_title'] . "' class='erp-crm-dashbaord-show-details-schedule'>" . __( 'Details &rarr;', 'wp-erp' ) . "</a>";
                    }
                ?>
            </li>
        <?php endforeach ?>
    </ul>
    <?php
}

/**
 * CRM Dashbaord upcoming schedules widgets
 *
 * @since 1.0
 *
 * @return void [html]
 */
function erp_hr_dashboard_widget_upcoming_schedules() {
    $upcoming_schedules = erp_crm_get_next_seven_day_schedules_activities( get_current_user_id() );
    ?>
    <ul class="erp-list list-two-side list-sep erp-crm-dashbaord-upcoming-schedules">
        <?php foreach ( $upcoming_schedules as $key => $schedule ) : ?>
            <li>
                <?php
                    $users_text   = '';
                    $invite_users = isset( $schedule['extra']['invite_contact'] ) ? $schedule['extra']['invite_contact'] : [];
                    $contact_user = $schedule['contact']['first_name'] . ' ' . $schedule['contact']['last_name'];

                    array_walk( $invite_users, function( &$val ) {
                        $val = get_the_author_meta( 'display_name', $val );
                    });

                    if ( count( $invite_users) == 1 ) {
                        $users_text = sprintf( '%s <span>%s</span>', __( 'and', 'wp-erp' ), reset( $invite_users ) );
                    } else if ( count( $invite_users) > 1 ) {
                        $users_text = sprintf( '%s <span class="erp-tips" title="%s">%d %s</span>', __( 'and', 'wp-erp' ), implode( '<br>', $invite_users ), count( $invite_users ), __( 'Others') );
                    }

                    if ( $schedule['log_type'] == 'meeting' ) {
                        echo sprintf( '%s <a href="%s">%s</a> %s %s %s %s %s', __( '<i class="fa fa-calendar"></i> Meeting with', 'wp-erp' ), erp_crm_get_details_url( $schedule['contact']['id'], $schedule['contact']['type'] ), $contact_user, $users_text, __( 'on', 'wp-erp' ), erp_format_date( strtotime( $schedule['start_date'] ) ), __( 'at', 'wp-erp' ), date( 'g:ia', strtotime( $schedule['start_date'] ) ) ) . " <a href='#' data-schedule_id=' " . $schedule['id'] . " ' data-title='" . $schedule['extra']['schedule_title'] . "' class='erp-crm-dashbaord-show-details-schedule'>" . __( 'Details &rarr;', 'wp-erp' ) . "</a>";
                    }

                    if ( $schedule['log_type'] == 'call' ) {
                        echo sprintf( '%s <a href="%s">%s</a> %s %s %s %s %s', __( '<i class="fa fa-phone"></i> Call to', 'wp-erp' ), erp_crm_get_details_url( $schedule['contact']['id'], $schedule['contact']['type'] ), $contact_user, $users_text, __( 'on', 'wp-erp' ), erp_format_date( strtotime( $schedule['start_date'] ) ), __( 'at', 'wp-erp' ), date( 'g:ia', strtotime( $schedule['start_date'] ) ) ) . " <a href='#' data-schedule_id=' " . $schedule['id'] . " ' data-title='" . $schedule['extra']['schedule_title'] . "' class='erp-crm-dashbaord-show-details-schedule'>" . __( 'Details &rarr;', 'wp-erp' ) . "</a>";
                    }
                ?>
            </li>
        <?php endforeach ?>
    </ul>
    <?php
}

/**
 * Show all schedules in calendar
 *
 * @since 1.0
 *
 * @return void
 */
function erp_hr_dashboard_widget_my_schedules() {
    echo 'Hi calender';
}
