<?php

/** Callbacks *****************************/
function erp_hr_dashboard_widget_birthday_callback() {
    erp_admin_dash_metabox( __( '<i class="fa fa-birthday-cake"></i> Celebrations', 'erp' ), 'erp_hr_dashboard_widget_birthday' );
    erp_admin_dash_metabox( __( '<i class="fa fa-paper-plane"></i> Who is out', 'erp' ), 'erp_hr_dashboard_widget_whoisout' );

    if ( current_user_can( 'erp_hr_manager' ) ) {
        erp_admin_dash_metabox( __( '<i class="fa fa-paper-plane"></i> About to end ', 'erp' ) . erp_help_tip( esc_html__( 'List of contractual & trainee employees whose job periods are about to end.', 'erp' ) ), 'erp_hr_dashboard_widget_about_to_end' );
    }
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

    if ( $todays_birthday ) { ?>

        <h4><?php esc_html_e( 'Today\'s Birthday', 'erp' ); ?></h4>
        <span class="wait"><?php esc_html_e( 'please wait ...', 'erp' ); ?></span>

        <ul class="erp-list list-inline">
            <?php

            foreach ( $todays_birthday as $user ) {
                $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) ); ?>
                <li>
                    <a href="<?php echo esc_url( $employee->get_details_url() ); ?>" class="erp-tips" title="<?php echo esc_attr( $employee->get_full_name() ); ?>" style="display: inline-block; height: 32px;">
                        <?php echo wp_kses_post( $employee->get_avatar( 32 ) ); ?>
                        <span style="margin: 0 8px; padding: 5px;">
                            <?php echo esc_html( $employee->get_full_name() ); ?>
                        </span>

                         <!-- show user name -->
                    </a>
                </li>
            <?php
            } ?>
        </ul>

        <?php
    } ?>

    <?php if ( $upcoming_birtday ) { ?>

        <h4><?php esc_html_e( 'Upcoming Birthdays', 'erp' ); ?></h4>

        <ul class="erp-list list-two-side list-sep">

            <?php foreach ( $upcoming_birtday as $key => $user ) { ?>

                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) ); ?>

                <li>
                    <a href="<?php echo esc_url( $employee->get_details_url() ); ?>"><?php echo esc_html( $employee->get_full_name() ); ?></a>
                    <span><?php echo esc_html( erp_format_date( $user->date_of_birth, 'M, d' ) ); ?></span>
                </li>

            <?php } ?>

        </ul>
        <?php
    }

    if ( ! $todays_birthday && ! $upcoming_birtday ) {
        esc_html_e( 'No one has birthdays this week!', 'erp' );
    } ?>
    <style>
        span.wait {
            display: none;
            float: right;
        }
        .erp-list .send-wish {
            box-shadow: none;
        }
        .erp-list .send-wish i {
            color: #fbc02d;
        }
    </style>
    <?php
}

/**
 * About to end widget
 *
 * @return void
 */
function erp_hr_dashboard_widget_about_to_end() {
    $c_t_employees  = erp_hr_get_contractual_employee();
    $current_date   =  current_time( 'Y-m-d' );
    $trainee        = [];
    $contract       = [];

    foreach ( $c_t_employees as $key => $user ) {
        $date1          = date_create( $current_date );
        $end_date       = get_user_meta( $user->user_id, 'end_date', true );
        $date2          = date_create( $end_date );
        $diff           = date_diff( $date1, $date2 );

        if ( $diff->days > 0 && $diff->days < 21 ) {
            $user->end_date = $end_date;

            if ( $user->type == 'contract' ) {
                $contract[] = $user;
            }

            if ( $user->type == 'trainee' ) {
                $trainee[] = $user;
            }
        }
    }
    usort( $contract, function ( $a, $b ) {
        return $a->end_date > $b->end_date;
    } );
    usort( $trainee, function ( $a, $b ) {
        return $a->end_date > $b->end_date;
    } ); ?>
    <h4><?php esc_html_e( 'Contractual Employees', 'erp' ); ?></h4>
    <span class="wait"><?php esc_html_e( 'please wait ...', 'erp' ); ?></span>

    <ul class="erp-list list-two-side list-sep">

        <?php foreach ( $contract as $key => $user ) {
        $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) ); ?>
                <li>
                    <a href="<?php echo esc_url( $employee->get_details_url() ); ?>"><?php echo esc_html( $employee->get_full_name() ); ?></a>
                    <span><?php echo esc_html( erp_format_date( $user->end_date, 'M, d' ) ); ?></span>
                </li>
        <?php
    }

    if ( empty( $contract ) ) {
        ?>
                <li><?php esc_html_e( 'No employee found', 'erp' ); ?></li>
                <?php
    } ?>
    </ul>

    <h4><?php esc_html_e( 'Trainee Employees', 'erp' ); ?></h4>
    <span class="wait"><?php esc_html_e( 'please wait ...', 'erp' ); ?></span>

    <ul class="erp-list list-two-side list-sep">

        <?php foreach ( $trainee as $key => $user ) {
        $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) ); ?>
            <li>
                <a href="<?php echo esc_url( $employee->get_details_url() ); ?>"><?php echo esc_html( $employee->get_full_name() ); ?></a>
                <span><?php echo esc_html( erp_format_date( $user->end_date, 'M, d' ) ); ?></span>
            </li>
        <?php
    }

    if ( empty( $trainee ) ) {
        ?>
                <li><?php esc_html_e( 'No trainee found', 'erp' ); ?></li>
                <?php
    } ?>
    </ul>

    <style>
        span.wait {
            display: none;
            float: right;
        }
        .erp-list .send-wish {
            box-shadow: none;
        }
        .erp-list .send-wish i {
            color: #fbc02d;
        }
    </style>
    <?php
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
    if ( current_user_can( erp_hr_get_manager_role() ) ) {
        $query = new WP_Query( [
            'post_type'      => 'erp_hr_announcement',
            'posts_per_page' => '5',
            'order'          => 'DESC',
        ] );
        $announcements = $query->get_posts();
    } else {
        $announcements = erp_hr_employee_dashboard_announcement( get_current_user_id() );
    }

    if ( $announcements ) {
        ?>
    <ul class="erp-list erp-dashboard-announcement">
        <?php
        $i = 0;

        foreach ( $announcements as $announcement ) { ?>
            <li class="<?php echo ( $announcement->status !== 'read' ) ? 'unread' : 'read'; ?>">
                <div class="announcement-title">
                    <a href="#" <?php echo ( $announcement->status == 'read' ) ? 'class="read"' : ''; ?>>
                        <?php echo esc_html( $announcement->post_title ); ?>
                    </a> | <span class="announcement-date"><?php echo esc_html( erp_format_date( $announcement->post_date ) ); ?></span>
                </div >

                <?php
                    $pcontent =  ( 0 == $i ) ? '<p>' . wp_trim_words( $announcement->post_content, 50 ) . '</p>' : '';
                    echo wp_kses_post( $pcontent );
                 ?>

                <div class="announcement-row-actions">
                    <?php if ( ! current_user_can( erp_hr_get_manager_role() ) ) { ?>
                        <a href="#" class="mark-read erp-tips <?php echo ( $announcement->status == 'read' ) ? 'erp-hide' : ''; ?>" title="<?php esc_attr_e( 'Mark as Read', 'erp' ); ?>" data-row_id="<?php echo esc_attr( $announcement->id ); ?>"><i class="dashicons dashicons-yes"></i></a>
                    <?php } ?>
                    <a href="#" class="view-full erp-tips" title="<?php esc_html_e( 'View full announcement', 'erp' ); ?>" data-row_id="<?php echo esc_attr( $announcement->ID ); ?>"><i class="dashicons dashicons-editor-expand"></i></a>
                </div>
            </li>
        <?php $i++;
        } ?>
    </ul>
    <?php
    } else {
        esc_html_e( 'No announcement found', 'erp' );
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
    $leave_requests_nextmonth = erp_hr_get_next_month_leave_list(); ?>

    <?php if ( $leave_requests ) : ?>
        <h4><?php esc_html_e( 'This Month', 'erp' ); ?></h4>

        <ul class="erp-list list-two-side list-sep">
            <?php foreach ( $leave_requests as $key => $leave ) : ?>
                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $leave->user_id ) ); ?>
                <li>
                    <a href="<?php echo esc_url( $employee->get_details_url() ); ?>"><?php echo esc_html( $employee->get_full_name() ); ?></a>
                    <?php
                    if ( $leave->day_status_id != '1' ) :
                        $days = erp_hr_leave_request_get_day_statuses( $leave->day_status_id );

                        if ( $leave->day_status_id == '2' ) :
                            ?>&nbsp;<img src='<?php echo esc_url( WPERP_URL . '/assets/images/Morning.svg' ); ?>' height='20' title='<?php echo esc_attr( $days ); ?>'/><?php
                        elseif ( $leave->day_status_id == '3' ) :
                            ?>&nbsp; <img src='<?php echo esc_url( WPERP_URL . '/assets/images/Afternoon.svg' ); ?>' height='18' title='<?php echo esc_attr( $days ); ?>'/><?php
                        endif;
                    endif;
                    ?>
                    <span><i class="fa fa-calendar"></i> <?php echo esc_html( erp_format_date( $leave->start_date, 'M d' ) ) . ' - ' . esc_html( erp_format_date( $leave->end_date, 'M d' ) ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ( $leave_requests_nextmonth ) : ?>
        <h4><?php esc_html_e( 'Next Month', 'erp' ); ?></h4>

        <ul class="erp-list list-two-side list-sep">
            <?php foreach ( $leave_requests_nextmonth as $key => $leave ) : ?>
                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $leave->user_id ) ); ?>
                <li>
                    <a href="<?php echo esc_url( $employee->get_details_url() ); ?>">
                        <?php echo esc_html( $employee->get_full_name() ); ?>
                    </a>
                    <?php
                    if ( $leave->day_status_id != '1' ) :
                        $days = erp_hr_leave_request_get_day_statuses( $leave->day_status_id );

                        if ( $leave->day_status_id == '2' ) :
                            ?>&nbsp;<img src='<?php echo esc_url( WPERP_URL . '/assets/images/Morning.svg' ); ?>' height='20' title='<?php echo esc_attr( $days ); ?>'/><?php
                        elseif ( $leave->day_status_id == '3' ) :
                            ?>&nbsp; <img src='<?php echo esc_url( WPERP_URL . '/assets/images/Afternoon.svg' ); ?>' height='18' title='<?php echo esc_attr( $days ); ?>'/><?php
                        endif;
                    endif;
                    ?>
                    <span><i class="fa fa-calendar"></i> <?php echo esc_html( erp_format_date( $leave->start_date, 'M d' ) ) . ' - ' . esc_html( erp_format_date( $leave->end_date, 'M d' ) ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php
    if ( ! $leave_requests && ! $leave_requests_nextmonth ) :
        esc_html_e( 'No one is on vacation on this or next month', 'erp' );
    endif;
}

/**
 * ERP dashboard leave calendar widget
 *
 * @since 0.1
 *
 * @return void
 */
function erp_hr_dashboard_widget_leave_calendar() {
    $user_id = get_current_user_id();
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
        #view_hr_dashboard_leave_modal .button-primary{
            display: none;
        }
        #view_hr_dashboard_leave_modal table tr td,
        #view_hr_dashboard_leave_modal table tr th{
            text-align: center;
        }
        #hdlp_action a{
            display: inline-block !important;
        }
    </style>

    <div class="leave-calendar-actions-wrap">
        <?php if ( erp_hr_get_assign_policy_from_entitlement( $user_id ) ) { ?>
            <div class="erp-hr-new-leave-request-wrap">
                <a href="#" class="button button-primary" id="erp-hr-new-leave-req"><?php esc_html_e( 'Take a Leave', 'erp' ); ?></a>
            </div>
        <?php } ?>

        <?php do_action( 'erp_hr_leave_calendar_actions' ); ?>
    </div>

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
            firstDay: <?php echo esc_html( get_option( 'start_of_week', 1 ) );?>,
            eventRender: function(event, element, calEvent) {
                if ( event.holiday ) {
                    element.find('.fc-content').find('.fc-title').css({ 'top':'0px', 'left' : '3px', 'fontSize' : '13px', 'padding':'2px' });
                };
            },
            viewRender: function(view, element) {
                wp.ajax.send( 'erp-hr-get-leave-by-date', {
                    data: {
                        start : view.start.format('L'),
                        end : view.end.format('L'),
                        _wpnonce: wpErpHr.nonce
                    },
                    success: function(response) {
                        $('#erp-hr-calendar').fullCalendar('removeEvents');
                        $('#erp-hr-calendar').fullCalendar('addEventSource', response);
                        $('#erp-hr-calendar').fullCalendar('rerenderEvents' );
                    }
                });
            },
            eventClick: function(calEvent, jsEvent, view) {
                jQuery.erpPopup({
                    title: "Leave/Holiday",
                    id: "view_hr_dashboard_leave_modal",
                    extraClass: 'erp_att_hr_dashboard_leave_popup',
                    content: jQuery('#hr_dashboard_leave_popup').html(),
                    onReady: function () {
                        var modal = this;
                        jQuery('header', modal).after(jQuery('<div class="loader"></div>').show());
                        jQuery('#hdlp_policy').html( calEvent.title );
                        jQuery('#hdlp_startdate').html( calEvent.start.format('LL') );
                        jQuery('#hdlp_enddate').html( calEvent.end.format('LL') );
                        jQuery('#hdlp_reason').html( calEvent.reason );
                        jQuery('#hdlp_action a').attr( 'href', calEvent.go_to );

                        if( calEvent.holiday ) {
                            jQuery('.conditional_action_wrap').hide();
                        } else {
                            jQuery('.conditional_action_wrap').show()
                        }

                        jQuery('.loader', modal).remove();
                    }
                });



            }
        });
    });

    </script>

    <!-- Individual log edit start -->

    <script type="text/html" id="hr_dashboard_leave_popup">
        <div>
            <table class="wp-list-table widefat fixed striped">
                <tr>
                    <th><strong><?php esc_html_e( 'Policy', 'erp' ) ;?></strong></th>
                    <td id="hdlp_policy"></td>
                </tr>
                <tr>
                    <th><strong><?php esc_html_e( 'Start date', 'erp' ) ;?></strong></th>
                    <td id="hdlp_startdate"></td>
                </tr>
                <tr>
                    <th><strong><?php esc_html_e( 'End date', 'erp' ) ;?></strong></th>
                    <td id="hdlp_enddate"></td>
                </tr>
                <tr class="conditional_action_wrap">
                    <th><strong><?php esc_html_e( 'Reason', 'erp' ) ;?></strong></th>
                    <td id="hdlp_reason"></td>
                </tr>
                <tr class="conditional_action_wrap">
                    <th><strong><?php esc_html_e( 'Action', 'erp' ) ;?></strong></th>
                    <td id="hdlp_action"><a target="_blank" class="button button-primary" href="#">Go to</a></td>
                </tr>
            </table>
        </div>
    </script>

    <!-- Individual log edit end -->

    <?php
}

/**
 * Employee list url
 *
 * @since  1.1.10
 *
 * @return string
 */
function erp_hr_employee_list_url() {
    $args = [
        'page'        => 'erp-hr',
        'section'     => 'people',
        'sub-section' => 'employee',
    ];

    $url = add_query_arg( $args, admin_url( 'admin.php' ) );
    $url = apply_filters( 'erp_hr_employee_list_url', $url, $args );

    return $url;
}
