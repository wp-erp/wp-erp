<?php

// Actions *****************************************************************/
add_action( 'user_register', 'erp_crm_new_admin_as_manager' );
add_action( 'admin_init', 'erp_crm_customer_schedule_notification' );
add_action( 'wp_ajax_nopriv_erp_crm_save_email_activity', 'erp_crm_save_email_activity' );
add_action( 'wp_ajax_nopriv_erp_crm_track_email_read', 'erp_crm_track_email_read' );
add_action( 'erp_crm_dashboard_widgets_right', 'erp_crm_dashboard_right_widgets_area' );
add_action( 'erp_crm_dashboard_widgets_left', 'erp_crm_dashboard_left_widgets_area' );
add_action( 'erp_crm_loaded', 'erp_crm_contact_forms' );
add_action( 'erp_settings_pages', 'erp_settings_pages_contact_forms' );

// Filters *****************************************************************/
add_filter( 'erp_people_query_object', 'erp_crm_save_search_query_filter' );

add_action( 'erp_hr_permission_management', 'erp_crm_permission_management_field' );

