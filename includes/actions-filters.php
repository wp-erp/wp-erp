<?php

/** Actions *******************************************************************/

// process erp actions on admin_init
add_action( 'admin_init', 'erp_process_actions' );
add_action( 'admin_notices', 'erp_activation_notice' );
add_action( 'admin_footer', 'erp_activation_notice_javascript' );
add_action( 'wp_ajax_nopriv_erp_api_mode_change', 'erp_api_mode_change' );
add_action( 'admin_footer-users.php', 'erp_user_bulk_actions' );
add_action( 'load-users.php', 'erp_handle_user_bulk_actions' );
add_action( 'admin_notices', 'erp_user_bulk_actions_notices' );
add_action( 'user_register', 'erp_create_contact_from_created_user' );

/** Filters *******************************************************************/

add_filter( 'map_meta_cap', 'erp_map_meta_caps', 10, 4 );
add_filter( 'editable_roles', 'erp_hr_filter_editable_roles' );
add_filter( 'cron_schedules', 'erp_cron_intervals', 10, 1 );
