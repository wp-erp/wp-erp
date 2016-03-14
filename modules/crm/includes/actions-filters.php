<?php

// Actions *****************************************************************/

add_action( 'admin_init', 'erp_crm_customer_schedule_notification' );
add_action( 'erp_crm_dashboard_widgets_right', 'erp_crm_dashboard_right_widgets_area' );

// Filters *****************************************************************/
add_filter( 'erp_people_query_object', 'erp_crm_save_search_query_filter' );
