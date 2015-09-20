<?php


// Actions *****************************************************************/

// process erp actions on admin_init
add_action( 'admin_init', 'erp_process_actions' );


// Filters *****************************************************************/

add_filter( 'map_meta_cap', 'erp_map_meta_caps', 10, 4 );


//After create employee apply leave policy
add_action( 'erp_hr_employee_new', 'erp_hr_apply_new_employee_policy', 10, 2 ); 
add_action( 'erp_hr_policy_schedule', 'erp_hr_apply_policy_schedule' );

