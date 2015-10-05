<?php

// Actions *****************************************************************/

add_action( 'user_register', 'erp_hr_employee_on_initialize' );
add_action( 'delete_user', 'erp_hr_employee_on_delete' );

//After create employee apply leave policy
add_action( 'erp_hr_employee_new', 'erp_hr_apply_new_employee_policy', 10, 2 );
add_action( 'erp_hr_policy_schedule', 'erp_hr_apply_policy_schedule' );
add_action( 'admin_init', 'erp_hr_apply_policy_schedule' );
add_action( 'erp_hr_leave_policy_new', 'erp_hr_apply_policy_existance_employee', 10, 2 );

// Filters *****************************************************************/
