<?php

/**
 * Register metabox for crm dashbaord
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
                    if ( $schedule['log_type'] == 'meeting' ) {
                        $users_text   = '';
                        $invite_users = isset( $schedule['extra']['invite_contact'] ) ? $schedule['extra']['invite_contact'] : [];
                        $contact_user = $schedule['contact']['first_name'] . ' ' . $schedule['contact']['last_name'];

                        array_walk( $invite_users, function( &$val ) {
                            $val = get_the_author_meta( 'display_name', $val );
                        });

                        // var_dump( $invite_users);
                    }
                ?>

                <?php if ( $schedule['log_type'] == 'call' ): ?>
                    <p>Call to USER and 13 others at 12:00 PM </p>
                <?php endif ?>
            </li>
        <?php endforeach ?>
        <!-- <li>
            <p>Call to USER at 12:00 PM <a href="#">Details</a></p>
        </li>
        <li>
            <p>Meeting with USER at 12:00 PM <a href="#">Details</a></p>
        </li>
        <li>
            <p>Call to USER and 13 others at 12:00 PM <a href="#">Details</a></p>
        </li> -->
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

}
