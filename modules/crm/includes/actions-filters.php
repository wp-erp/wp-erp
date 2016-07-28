<?php

// Actions *****************************************************************/
add_action( 'user_register', 'erp_crm_new_admin_as_manager' );
add_action( 'erp_per_minute_scheduled_events', 'erp_crm_customer_schedule_notification' );
add_action( 'wp_ajax_nopriv_erp_crm_track_email_opened', 'erp_crm_track_email_opened' );
add_action( 'erp_crm_dashboard_widgets_right', 'erp_crm_dashboard_right_widgets_area' );
add_action( 'erp_crm_dashboard_widgets_left', 'erp_crm_dashboard_left_widgets_area' );
add_action( 'plugins_loaded', 'erp_crm_contact_forms' );
add_action( 'erp_settings_pages', 'erp_settings_pages_contact_forms' );
add_action( 'erp_settings_pages', 'erp_crm_settings_pages' );
add_action( 'erp_hr_permission_management', 'erp_crm_permission_management_field' );
add_action( 'admin_footer-users.php', 'erp_user_bulk_actions' );
add_action( 'load-users.php', 'erp_handle_user_bulk_actions' );
add_action( 'admin_notices', 'erp_user_bulk_actions_notices' );
add_action( 'user_register', 'erp_create_contact_from_created_user' );
add_action( 'erp_crm_inbound_email_scheduled_events', 'erp_crm_check_new_inbound_emails' );

// Filters *****************************************************************/
add_filter( 'erp_map_meta_caps', 'erp_crm_map_meta_caps', 10, 4 );
add_filter( 'erp_get_people_pre_where_join', 'erp_crm_contact_advance_filter', 10, 2 );
add_filter( 'erp_get_people_pre_query', 'erp_crm_is_people_belongs_to_saved_search', 10, 2 );

