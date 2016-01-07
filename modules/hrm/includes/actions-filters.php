<?php

// Actions *****************************************************************/

add_action( 'user_register', 'erp_hr_employee_on_initialize' );
add_action( 'user_register', 'erp_hr_new_admin_as_manager' );
add_action( 'delete_user', 'erp_hr_employee_on_delete' );
add_action( 'set_user_role', 'erp_hr_existing_role_to_employee', 10, 2 );


// After create employee apply leave policy
add_action( 'erp_hr_employee_new', 'erp_hr_apply_new_employee_policy', 10, 1 );
add_action( 'erp_hr_policy_schedule', 'erp_hr_apply_policy_schedule' );

add_action( 'admin_init', 'erp_hr_apply_entitlement_yearly' );

add_action( 'erp_hr_leave_policy_new', 'erp_hr_apply_policy_existance_employee', 10, 2 );

// Filters *****************************************************************/
add_filter( 'erp_map_meta_caps', 'erp_hr_map_meta_caps', 10, 4 );
add_filter( 'erp_people_types', 'erp_hr_people_types' );