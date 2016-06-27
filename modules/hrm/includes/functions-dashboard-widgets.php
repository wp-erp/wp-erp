<?php

/** Callbacks *****************************/

function erp_hr_dashboard_widget_birthday_callback() {
    erp_admin_dash_metabox( __( '<i class="fa fa-birthday-cake"></i> Birthday Buddies', 'erp' ), 'erp_hr_dashboard_widget_birthday' );
    erp_admin_dash_metabox( __( '<i class="fa fa-paper-plane"></i> Who is out', 'erp' ), 'erp_hr_dashboard_widget_whoisout' );
}

function erp_hr_dashboard_widget_announcement_callback() {
    erp_admin_dash_metabox( __( '<i class="fa fa-microphone"></i> Latest Announcement', 'erp' ), 'erp_hr_dashboard_widget_latest_announcement' );
    erp_admin_dash_metabox( __( '<i class="fa fa-calendar-o"></i> My Leave Calendar', 'erp' ), 'erp_hr_dashboard_widget_leave_calendar' );
}


add_action( 'erp_hr_dashboard_widgets_right', 'erp_hr_dashboard_widget_birthday_callback' );
add_action( 'erp_hr_dashboard_widgets_left', 'erp_hr_dashboard_widget_announcement_callback' );

/** Widgets *****************************/

/**
 * Birthday widget
 *
 * @return void
 */
function erp_hr_dashboard_widget_birthday() {
    $todays_birthday  = erp_hr_get_todays_birthday();
    $upcoming_birtday = erp_hr_get_next_seven_days_birthday();
    ?>
    <?php if ( $todays_birthday ) { ?>

        <h4><?php _e( 'Today\'s Birthday', 'erp' ); ?></h4>

        <ul class="erp-list list-inline">
            <?php
            foreach ( $todays_birthday as $key => $user ) {
                $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) );
                ?>
                <li><a href="<?php echo $employee->get_details_url(); ?>" class="erp-tips" title="<?php echo $employee->get_full_name(); ?>"><?php echo $employee->get_avatar( 32 ); ?></a></li>
            <?php } ?>
        </ul>

        <?php
    }
    ?>

    <?php if ( $upcoming_birtday ) { ?>

        <h4><?php _e( 'Upcoming Birthdays', 'erp' ); ?></h4>

        <ul class="erp-list list-two-side list-sep">

            <?php foreach ( $upcoming_birtday as $key => $user ): ?>

                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) ); ?>

                <li>
                    <a href="<?php echo $employee->get_details_url(); ?>"><?php echo $employee->get_full_name(); ?></a>
                    <span><?php echo erp_format_date( $user->date_of_birth, 'M, d' ); ?></span>
                </li>

            <?php endforeach; ?>

        </ul>
        <?php
    }

    if ( ! $todays_birthday && ! $upcoming_birtday ) {
        _e( 'No one has birthdays this week!', 'erp' );
    }
}

/**
 * Latest Announcement Widget
 *
 * @since 0.1
 *
 * @return void
 */
function erp_hr_dashboard_widget_latest_announcement() {

    //if user is admin then show latest 5 announcements
    if ( current_user_can( 'manage_options' ) ) {
        $query = new WP_Query( array(
            'post_type'      => 'erp_hr_announcement',
            'posts_per_page' => '5',
            'order'          => 'DESC'
        ) );
        $announcements = $query->get_posts();
    } else {
        $announcements = erp_hr_employee_dashboard_announcement( get_current_user_id() );
    }

    if ( $announcements ) {
    ?>
    <ul class="erp-list erp-dashboard-announcement">
        <?php
        $i = 0;
        foreach ( $announcements as $key => $announcement ): ?>
            <li>
                <div class="announcement-title">
                    <a href="#" <?php echo ( $announcement->status == 'read' ) ? 'class="read"' : ''; ?>><?php echo $announcement->post_title; ?></a>
                    | <span class="announcement-date"><?php echo erp_format_date( $announcement->post_date ); ?></span>
                </div >
                <?php echo ( 0 == $i ) ? '<p>' . wp_trim_words( $announcement->post_content, 50 ) . '</p>' : ''; ?>
                <div class="announcement-row-actions">
                    <a href="#" class="mark-read erp-tips <?php echo ( $announcement->status == 'read' ) ? 'erp-hide' : ''; ?>" title="<?php _e( 'Mark as Read', 'erp' ); ?>" data-row_id="<?php echo $announcement->id; ?>"><i class="fa fa-circle-o-notch"></i></a>
                    <a href="#" class="view-full erp-tips" title="<?php _e( 'View full announcement', 'erp' ); ?>" data-row_id="<?php echo $announcement->ID; ?>"><i class="fa fa-book"></i></a>
                </div>
            </li>
        <?php $i++;
        endforeach ?>
    </ul>
    <?php
    } else {
        _e( 'No announcement found', 'erp' );
    }
}

/**
 * Erp dashboard who is out widget
 *
 * @since 0.1
 *
 * @return void
 */
function erp_hr_dashboard_widget_whoisout() {
    $leave_requests           = erp_hr_get_current_month_leave_list();
    $leave_requests_nextmonth = erp_hr_get_next_month_leave_list();
    ?>
    <?php if ( $leave_requests ) { ?>

        <h4><?php _e( 'This Month', 'erp' ); ?></h4>

        <ul class="erp-list list-two-side list-sep">
            <?php foreach ( $leave_requests as $key => $leave ): ?>
                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $leave->user_id ) ); ?>
                <li>
                    <a href="<?php echo $employee->get_details_url(); ?>"><?php echo $employee->get_full_name(); ?></a>
                    <span><i class="fa fa-calendar"></i> <?php echo erp_format_date( $leave->start_date, 'M d' ) . ' - '. erp_format_date( $leave->end_date, 'M d' ); ?></span>
                </li>
            <?php endforeach ?>
        </ul>
    <?php } ?>

    <?php if ( $leave_requests_nextmonth ) { ?>
        <h4><?php _e( 'Next Month', 'erp' ); ?></h4>

        <ul class="erp-list list-two-side list-sep">
            <?php foreach ( $leave_requests_nextmonth as $key => $leave ): ?>
                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $leave->user_id ) ); ?>
                <li>
                    <a href="<?php echo $employee->get_details_url(); ?>"><?php echo $employee->get_full_name(); ?></a>
                    <span><i class="fa fa-calendar"></i> <?php echo erp_format_date( $leave->start_date, 'M d' ) . ' - '. erp_format_date( $leave->end_date, 'M d' ); ?></span>
                </li>
            <?php endforeach ?>
        </ul>

    <?php } ?>

    <?php if ( ! $leave_requests && ! $leave_requests_nextmonth ) { ?>

        <?php _e( 'No one is on vacation on this or next month', 'erp' ); ?>

    <?php } ?>

    <?php
}

/**
 * ERP dashboard leave calendar widget
 *
 * @since 0.1
 *
 * @return void
 */
function erp_hr_dashboard_widget_leave_calendar() {

    $user_id        = get_current_user_id();
    $leave_requests = erp_hr_get_calendar_leave_events( false, $user_id, false );
    $holidays       = erp_array_to_object( \WeDevs\ERP\HRM\Models\Leave_Holiday::all()->toArray() );
    $events         = [];
    $holiday_events = [];
    $event_data     = [];

    foreach ( $leave_requests as $key => $leave_request ) {
        //if status pending
        $policy = erp_hr_leave_get_policy( $leave_request->policy_id );
        $event_label = $policy->name;
        if ( 2 == $leave_request->status ) {
            $policy = erp_hr_leave_get_policy( $leave_request->policy_id );
            $event_label .= sprintf( ' ( %s ) ', __( 'Pending', 'erp' ) );
        }
        $events[] = array(
            'id'        => $leave_request->id,
            'title'     => $event_label,
            'start'     => $leave_request->start_date,
            'end'       => erp_fullcalendar_end_date( $leave_request->end_date ),
            'url'       => erp_hr_url_single_employee( $leave_request->user_id, 'leave' ),
            'color'     => $leave_request->color,
        );
    }

    foreach ( $holidays as $key => $holiday ) {
        $holiday_events[] = [
            'id'        => $holiday->id,
            'title'     => $holiday->title,
            'start'     => $holiday->start,
            'end'       => erp_fullcalendar_end_date( $holiday->end ),
            'color'     => '#FF5354',
            'img'       => '',
            'holiday'   => true
        ];
    }

    $event_data = array_merge( $events, $holiday_events );

    ?>
    <style>
        .fc-time {
            display:none;
        }
        .erp-leave-avatar img {
            border-radius: 50%;
            margin: 3px 7px 0 0;

        }
        .erp-calendar-filter {
            margin: 15px 0px;
        }
        .fc-title {
            position: relative;
        }
    </style>

    <?php if ( erp_hr_get_assign_policy_from_entitlement( $user_id ) ): ?>
        <div class="erp-hr-new-leave-request-wrap">
            <a href="#" class="button button-primary" id="erp-hr-new-leave-req"><?php _e( 'Take a Leave', 'erp' ); ?></a>
        </div>
    <?php endif ?>

    <div id="erp-hr-calendar"></div>

    <script>
    ;jQuery(document).ready(function($) {

        $('#erp-hr-calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            editable: false,
            eventLimit: true,
            events: <?php echo json_encode( $event_data ); ?>,
            eventRender: function(event, element, calEvent) {
                if ( event.holiday ) {
                    element.find('.fc-content').find('.fc-title').css({ 'top':'0px', 'left' : '3px', 'fontSize' : '13px', 'padding':'2px' });
                };
            },
        });
    });

    </script>
    <?php
}
