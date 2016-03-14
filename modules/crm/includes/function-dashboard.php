<?php

function erp_crm_dashboard_right_widgets_area() {
    erp_admin_dash_metabox( __( '<i class="fa fa-calendar-check-o"></i> Upcoming Schedules', 'wp-erp' ), 'erp_hr_dashboard_widget_upcoming_schedules' );
}

function erp_hr_dashboard_widget_upcoming_schedules() {
    echo 'Ayy hayy';
}
