<?php

/** Actions *******************************************************************/

add_action( 'user_register', 'erp_hr_employee_on_initialize' );
add_action( 'delete_user', 'erp_hr_employee_on_delete' );

/** Filters *******************************************************************/

add_filter( 'erp_map_meta_caps', 'erp_hr_map_meta_caps', 10, 4 );