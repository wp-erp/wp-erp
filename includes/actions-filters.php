<?php

/** Actions *******************************************************************/

// process erp actions on admin_init
add_action( 'admin_init', 'erp_process_actions' );
add_action( 'admin_init', 'erp_process_csv_export' );
add_action( 'admin_init', 'erp_import_export_download_sample' );

/* Filters *******************************************************************/

add_filter( 'map_meta_cap', 'erp_map_meta_caps', 10, 4 );
add_filter( 'cron_schedules', 'erp_cron_intervals', 10, 1 );
add_filter( 'ajax_query_attachments_args', 'erp_show_users_own_attachments', 1, 1 );

//login redirect hook
add_filter( 'login_redirect', 'erp_login_redirect_manager', 10, 3 );

// Enable/Disable section of pregenerated emails
add_action( 'erp_email_setting_column_is_enable', 'add_enable_disable_section_to_email_column' );
add_action( 'admin_init', 'add_enable_disable_option_save' );
add_filter( 'erp_email_setting_columns', 'erp_email_setting_columns_add_enable_disable' );
add_filter( 'erp_settings_email_section_fields', 'add_checkbox_hidden_field', 10, 2 );
add_filter( 'creating_email_instance', 'filter_enabled_email' );