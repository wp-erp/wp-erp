<?php

/** Callbacks *****************************/

function erp_hr_dashboard_widget_birthday_callback() {
    erp_admin_dash_metabox( __( '<i class="fa fa-birthday-cake"></i> Birthday Buddies', 'wp-erp' ), 'erp_hr_dashboard_widget_birthday' );
    erp_admin_dash_metabox( __( '<i class="fa fa-paper-plane"></i> Who is out', 'wp-erp' ), 'erp_hr_dashboard_widget_whoisout' );
}

function erp_hr_dashboard_widget_announcement_callback() {
    erp_admin_dash_metabox( __( '<i class="fa fa-microphone"></i> Latest Announcement', 'wp-erp' ), 'erp_hr_dashboard_widget_latest_announcement' );
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
    <h4><?php _e( 'Today\'s Birthday', 'wp-erp' ); ?></h4>

    <?php if ( $todays_birthday ) { ?>
        <ul class="erp-list list-inline">
            <?php
            foreach ( $todays_birthday as $key => $user ) {
                $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) );
                ?>
                    <li><a href="<?php echo $employee->get_details_url(); ?>" class="erp-tips" title="<?php echo $employee->get_full_name(); ?>"><?php echo $employee->get_avatar( 32 ); ?></a></li>
            <?php } ?>
        </ul>

        <?php
    } else {
        _e( 'No one has birthday today!', 'wp-erp' );
    }
    ?>

    <h4><?php _e( 'Upcoming Birthday', 'wp-erp' ); ?></h4>

    <?php if ( $upcoming_birtday ) { ?>

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
    } else {
        _e( 'No one has birthdays this week!', 'wp-erp' );
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
    $announcements = erp_hr_employee_dashboard_announcement( get_current_user_id() );
    
    if ( $announcements ) {
    ?>
      <ul class="erp-list erp-dashboard-announcement">
        <?php foreach ( $announcements as $key => $announcement ): ?>
            <li <?php echo ( $announcement->status == 'unread' ) ? 'class="unread"' : ''; ?>>
                <h4>
                    <?php echo $announcement->post_title; ?>
                    | <span class="announcement-date"><?php echo erp_format_date( $announcement->post_date ); ?></span>
                </h4>
                <p><?php echo wp_trim_words( $announcement->post_content, 40 ); ?></p>
                <div class="announcement-row-actions">
                    <a href="#" class="mark-read erp-tips" title="<?php _e( 'Mark as Read', 'wp-erp' ); ?>" data-row_id="<?php echo $announcement->id; ?>"><i class="fa fa-circle-o-notch"></i></a>
                    <a href="#" class="view-full erp-tips" title="<?php _e( 'View full announcement', 'wp-erp' ); ?>" data-row_id="<?php echo $announcement->post_id; ?>"><i class="fa fa-book"></i></a>
                </div>           
            </li>        
        <?php endforeach ?>    
    </ul>  
    <?php
    } else {
        _e( 'No announcement found', 'wp-erp' );
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
    <h4><?php _e( 'This Month', 'wp-erp' ); ?></h4>
    <?php if ( $leave_requests ): ?>
        <ul class="erp-list list-two-side list-sep">
            <?php foreach ( $leave_requests as $key => $leave ): ?>
                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $leave->user_id ) ); ?>
                <li>
                    <a href="<?php echo $employee->get_details_url(); ?>"><?php echo $employee->get_full_name(); ?></a>
                    <span><i class="fa fa-calendar"></i> <?php echo erp_format_date( $leave->start_date, 'M d,y' ) . ' - '. erp_format_date( $leave->end_date, 'M d,y' ); ?></span>
                </li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <?php _e( 'No one is in vacation', 'wp-erp' ); ?>
    <?php endif ?>
    <hr>
    <h4><?php _e( 'Next Month', 'wp-erp' ); ?></h4>
    <?php if ( $leave_requests_nextmonth ): ?>
        <ul class="erp-list list-two-side list-sep">
            <?php foreach ( $leave_requests_nextmonth as $key => $leave ): ?>
                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $leave->user_id ) ); ?>
                <li>
                    <a href="<?php echo $employee->get_details_url(); ?>"><?php echo $employee->get_full_name(); ?></a>
                    <span><i class="fa fa-calendar"></i> <?php echo erp_format_date( $leave->start_date, 'M d,y' ) . ' - '. erp_format_date( $leave->end_date, 'M d,y' ); ?></span>
                </li>
            <?php endforeach ?>
        </ul>
    <?php else: ?>
        <?php _e( 'No one is vacation on next month', 'wp-erp' ); ?>
    <?php endif ?>

    <?php
}





