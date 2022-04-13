<?php

// Actions *****************************************************************/
add_action( 'user_register', 'erp_crm_new_admin_as_manager' );
add_action( 'erp_per_minute_scheduled_events', 'erp_crm_customer_schedule_notification' );
add_action( 'wp_ajax_erp_crm_track_email_opened', 'erp_crm_track_email_opened' );
add_action( 'wp_ajax_nopriv_erp_crm_track_email_opened', 'erp_crm_track_email_opened' );
add_action( 'erp_crm_dashboard_widgets_right', 'erp_crm_dashboard_right_widgets_area' );
add_action( 'erp_crm_dashboard_widgets_left', 'erp_crm_dashboard_left_widgets_area' );
add_action( 'plugins_loaded', 'erp_crm_contact_forms' );
add_action( 'erp_settings_pages', 'erp_crm_settings_pages' );
add_action( 'load-wp-erp_page_erp-settings', 'erp_crm_contact_form_section' );
add_action( 'erp_hr_permission_management', 'erp_crm_permission_management_field' );
add_action( 'admin_footer-users.php', 'erp_user_bulk_actions' );
add_action( 'load-users.php', 'erp_handle_user_bulk_actions' );
add_action( 'admin_notices', 'erp_user_bulk_actions_notices' );
add_action( 'user_register', 'erp_create_contact_from_created_user' );
add_action( 'erp_update_option', 'erp_crm_schedule_inbound_email_cron' );
add_action( 'erp_crm_inbound_email_scheduled_events', 'erp_crm_check_new_inbound_emails' );
add_action( 'erp_crm_inbound_email_scheduled_events', 'erp_crm_poll_gmail' );
add_action( 'updated_user_meta', 'erp_crm_sync_people_meta_data', 10, 4 );
add_action( 'added_user_meta', 'erp_crm_sync_people_meta_data', 10, 4 );
add_action( 'delete_user', 'erp_crm_contact_on_delete' );
add_action( 'erp_daily_scheduled_events', 'erp_crm_send_birthday_greetings' );
add_action( 'erp_crm_contact_menu', 'erp_crm_get_contacts_menu_html' );
add_action( 'erp_crm_task_menu', 'erp_crm_get_tasks_menu_html' );

// Register the taxonomies
add_action( 'init', 'erp_crm_add_tag_taxonomy' );

// Filters *****************************************************************/
add_filter( 'erp_map_meta_caps', 'erp_crm_map_meta_caps', 10, 4 );
add_filter( 'erp_get_people_pre_query', 'erp_crm_contact_advance_filter', 10, 2 );
add_filter( 'erp_get_people_pre_query', 'erp_crm_is_people_belongs_to_saved_search', 10, 2 );
add_filter( 'woocommerce_prevent_admin_access', 'erp_crm_wc_prevent_admin_access' );
add_filter( 'erp_login_redirect', 'erp_crm_login_redirect', 10, 2 );
add_filter( 'editable_roles', 'erp_crm_filter_editable_roles' );
add_filter( 'crm_vue_customer_script', 'crm_vue_customer_script_dep' );
