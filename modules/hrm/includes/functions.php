<?php

/**
 * [erp_hr_url_single_employee description]
 *
 * @param  int  employee id
 *
 * @return string  url of the employee details page
 */
function erp_hr_url_single_employee( $employee_id ) {
    $url = admin_url( 'admin.php?page=erp-hr-employee&action=view&id=' . $employee_id );

    return apply_filters( 'erp_hr_url_single_employee', $url, $employee_id );
}

/**
 * [erp_hr_employee_single_tab_general description]
 *
 * @return void
 */
function erp_hr_employee_single_tab_general() {
    include WPERP_HRM_VIEWS . '/employee/tab-general.php';
}
