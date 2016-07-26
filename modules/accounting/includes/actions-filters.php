<?php

// accounting dashboard widgets
add_action( 'erp_ac_dashboard_widgets_left', 'erp_ac_dashboard_left_column' );
add_action( 'erp_ac_dashboard_widgets_right', 'erp_ac_dashboard_right_column' );
add_action( 'user_register', 'erp_ac_new_admin_as_manager' );
add_action( 'erp_hr_permission_management', 'erp_ac_permission_management_field' );

// Read Only Invoice template
add_action( 'template_redirect', 'erp_ac_readonly_invoice_template' );
