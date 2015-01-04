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

/**
 * Get the registered employee statuses
 *
 * @return array the employee statuses
 */
function erp_hr_get_employee_statuses() {
    $statuses = array(
        'active'     => __( 'Active', 'wp-erp' ),
        'terminated' => __( 'Terminated', 'wp-erp' ),
        'deceased'   => __( 'Deceased', 'wp-erp' ),
        'resigned'   => __( 'Resigned', 'wp-erp' )
    );

    return apply_filters( 'erp_hr_employee_statuses', $statuses );
}

/**
 * Get the registered employee statuses
 *
 * @return array the employee statuses
 */
function erp_hr_get_employee_types() {
    $types = array(
        'permanent' => __( 'Permanent', 'wp-erp' ),
        'contract'  => __( 'On Contract', 'wp-erp' ),
        'temporary' => __( 'Temporary', 'wp-erp' ),
        'trainee'   => __( 'Trainee', 'wp-erp' )
    );

    return apply_filters( 'erp_hr_employee_types', $types );
}

/**
 * Get the registered employee hire sources
 *
 * @return array the employee hire sources
 */
function erp_hr_get_employee_sources() {
    $sources = array(
        'direct'        => __( 'Direct', 'wp-erp' ),
        'referral'      => __( 'Referral', 'wp-erp' ),
        'web'           => __( 'Web', 'wp-erp' ),
        'newspaper'     => __( 'Newspaper', 'wp-erp' ),
        'advertisement' => __( 'Advertisement', 'wp-erp' ),
        'social'        => __( 'Social Network', 'wp-erp' ),
        'other'         => __( 'Other', 'wp-erp' ),
    );

    return apply_filters( 'erp_hr_employee_sources', $sources );
}

/**
 * Get marital statuses
 *
 * @return array all the statuses
 */
function erp_hr_get_marital_statuses() {
    $statuses = array(
        'single'  => __( 'Single', 'wp-erp' ),
        'married' => __( 'Married', 'wp-erp' ),
        'widowed' => __( 'Widowed', 'wp-erp' )
    );

    return apply_filters( 'erp_hr_marital_statuses', $statuses );
}

/**
 * Get marital statuses
 *
 * @return array all the statuses
 */
function erp_hr_get_genders() {
    $genders = array(
        'male'   => __( 'Male', 'wp-erp' ),
        'female' => __( 'Female', 'wp-erp' ),
        'other'  => __( 'Other', 'wp-erp' )
    );

    return apply_filters( 'erp_hr_genders', $genders );
}
