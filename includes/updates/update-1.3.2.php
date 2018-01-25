<?php

/**
 * Update CRM new roles and capabilities
 *
 * @since 1.3.2
 *
 * @return void
 */
function wperp_update_1_3_2_set_role() {
    remove_role( 'erp_hr_manager' );
    remove_role( 'employee' );

    $installer = new \WeDevs_ERP_Installer();
    $installer->create_roles();
}
wperp_update_1_3_2_set_role();

