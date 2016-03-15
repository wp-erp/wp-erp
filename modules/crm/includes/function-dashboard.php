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
    erp_admin_dash_metabox( __( '<i class="fa fa-users"></i> Recently Added', 'wp-erp' ), 'erp_hr_dashboard_widget_latest_contact' );
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
    <?php if ( $todays_schedules ): ?>

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
     <?php else : ?>
        <?php _e( 'No schedules found', 'wp-erp' ); ?>
    <?php endif;
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

    <?php if ( $upcoming_schedules ): ?>
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
    <?php else : ?>
        <?php _e( 'No schedules found', 'wp-erp' ); ?>
    <?php endif;
}

/**
 * Show all schedules in calendar
 *
 * @since 1.0
 *
 * @return void
 */
function erp_hr_dashboard_widget_my_schedules() {
    $user_id        = get_current_user_id();
    $schedules_data = [];
    $args           = [
        'created_by' => $user_id,
        'number'     => -1,
        'type'       => 'log_activity'
    ];

    $schedules = erp_crm_get_feed_activity( $args );

    foreach ( $schedules as $key => $schedule ) {
        $start_date = $schedule['start_date'];
        $end_date = ( $schedule['end_date'] ) ? date( 'Y-m-d h:i:s', strtotime( $schedule['end_date'] . '+1 day') ) : date( 'Y-m-d 00:00:00', strtotime( $schedule['start_date'] . '+1 day') );        // $end_date = $schedule['end_date'];

        if ( date( 'Y-m-d', strtotime( $start_date ) ) == date( 'Y-m-d', strtotime( $end_date ) ) ) {
            $time = date( 'g:ia', strtotime( $start_date ) );
        } else {
            $time = date( 'g:ia', strtotime( $start_date ) ) . ' to ' . date( 'g:ia', strtotime( $end_date ) );
        }

        $title = $time . ' ' .ucfirst( $schedule['log_type'] );
        $color = $schedule['start_date'] < current_time( 'mysql' ) ? "#f05050" : '#03c756';

        $schedules_data[] = [
            'id' => $schedule['id'],
            'title' => $title,
            'color' => $color,
            'start' => $start_date,
            'end'  => $end_date
        ];
    }

    ?>
    <style>
        .fc-time {
            display:none;
        }
    </style>

    <div id="erp-crm-calendar"></div>
    <script>
        ;jQuery(document).ready(function($) {
            $('#erp-crm-calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                editable: false,
                eventLimit: true,
                events: <?php echo json_encode( $schedules_data ); ?>,
                eventRender: function(event, element, calEvent) {
                },
            });
        });
    </script>
    <?php
}


function erp_hr_dashboard_widget_latest_contact() {
    $contacts  = erp_get_peoples( [ 'type' => 'contact', 'orderby' => 'created', 'order' => 'DESC', 'number' => 5 ] );
    $companies = erp_get_peoples( [ 'type' => 'company', 'orderby' => 'created', 'order' => 'DESC', 'number' => 5 ] );
    ?>

    <h4><?php _e( 'Contact Lists', 'wp-erp' ); ?></h4>

    <?php if ( $contacts ) { ?>

        <ul class="erp-list erp-latest-contact-list">
            <?php foreach ( $contacts as $contact ) : ?>
                <li>
                    <div class="avatar">
                        <?php echo erp_crm_get_avatar( $contact->id, '28' ); ?>
                    </div>
                    <div class="details">
                        <p class="contact-name"><a href="<?php echo erp_crm_get_details_url( $contact->id, 'contact' ); ?>"><?php echo $contact->first_name . ' ' . $contact->last_name; ?></a></p>
                        <p class="contact-stage"><?php echo erp_people_get_meta( $contact->id, 'life_stage', true ); ?></p>
                    </div>
                    <span class="contact-created-time erp-tips" title="<?php echo sprintf( '%s %s', __( 'Created on', 'wp-erp' ), erp_format_date( $contact->created ) )  ?>"><i class="fa fa-clock-o"></i></span>
                </li>
            <?php endforeach ?>
        </ul>

    <?php } else { ?>
        <?php _e( 'No contacts found', 'wp-erp' ); ?>
    <?php } ?>

    <hr>

    <h4><?php _e( 'Company Lists', 'wp-erp' ); ?></h4>

    <?php if ( $companies ) { ?>
        <ul class="erp-list erp-latest-contact-list">
            <?php foreach ( $companies as $company ) : ?>
                <li>
                    <div class="avatar">
                        <?php echo erp_crm_get_avatar( $company->id, '28' ); ?>
                    </div>

                    <div class="details">
                        <p class="contact-name"><a href="<?php echo erp_crm_get_details_url( $company->id, 'company' ); ?>"><?php echo $company->company; ?></a></p>
                        <p class="contact-stage"><?php echo erp_people_get_meta( $company->id, 'life_stage', true ); ?></p>
                    </div>
                    <span class="contact-created-time erp-tips" title="<?php echo sprintf( '%s %s', __( 'Created on', 'wp-erp' ), erp_format_date( $company->created ) )  ?>"><i class="fa fa-clock-o"></i></span>
                </li>
            <?php endforeach ?>
        </ul>
    <?php
    } else {
        _e( 'No companies found', 'wp-erp' );
    }

}





















