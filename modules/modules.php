<?php

/**
 * Get the default ERP modules
 *
 * @return array main and their sub-modules
 */
function erp_get_modules() {
    $modules = array(
        'hrm' => array(
            'title'   => __( 'HR Management', 'wp-erp' ),
            'slug'    => 'erp-hrm',
            'callback' => '\WeDevs\ERP\Human_Resource',
            'modules' => apply_filters( 'erp_hr_modules', array() )
        ),
        'crm' => array(
            'title'   => __( 'CRM', 'wp-erp' ),
            'slug'    => 'erp-crm',
            'modules' => apply_filters( 'erp_crm_modules', array() )
        ),
        // 'accounting' => array(
        //     'title'   => __( 'Accounting', 'wp-erp' ),
        //     'slug'    => 'erp-accounting',
        //     'modules' => apply_filters( 'erp_accounting_modules', array() )
        // ),
        // 'pm' => array(
        //     'title'   => __( 'Project Management', 'wp-erp' ),
        //     'slug'    => 'erp-pm',
        //     'modules' => apply_filters( 'erp_pm_modules', array() )
        // ),
        // 'inventory' => array(
        //     'title'   => __( 'Inventory Management', 'wp-erp' ),
        //     'slug'    => 'erp-inventory',
        //     'modules' => apply_filters( 'erp_inventory_modules', array() )
        // ),
        // 'warehouse' => array(
        //     'title'   => __( 'Warehouse Management', 'wp-erp' ),
        //     'slug'    => 'erp-warehouse',
        //     'modules' => apply_filters( 'erp_warehouse_modules', array() )
        // ),
    );

    return apply_filters( 'erp_get_modules', $modules );
}

/**
 * The default ERP module for mode switching
 *
 * @return string the slug of the module
 */
function erp_get_default_module() {
    $mode = array(
        'title' => __( 'HR Management', 'wp-erp' ),
        'slug'  => 'erp-hrm',
    );

    return apply_filters( 'erp_get_default_module', $mode );
}

/**
 * Get the current mode of the admin UI
 *
 * @return array
 */
function erp_get_current_module() {
    global $current_user;

    $modules = erp_get_modules();
    $mode    = get_user_meta( $current_user->ID, '_erp_mode', true );

    if ( !empty( $mode ) && array_key_exists( $mode, $modules ) ) {
        return $modules[$mode];
    }

    return erp_get_default_module();
}

/**
 * Load the HRM module
 *
 * @return void
 */
function erp_module_load_hrm() {
    require_once WPERP_MODULES . '/hrm/hrm.php';
}

add_action( 'wp-erp-load-module_erp-hrm', 'erp_module_load_hrm' );

/**
 * Load the CRM module
 *
 * @return void
 */
function erp_module_load_crm() {
    require_once WPERP_MODULES . '/crm/crm.php';
}

add_action( 'wp-erp-load-module_erp-crm', 'erp_module_load_crm' );

/**
 * Redirect to the CRM/HRM dashboard page when switching from admin menu bar
 *
 * @param  string  redirect url
 * @param  string  new mode slug
 *
 * @return string  new url to redirect to
 */
function erp_switch_redirect( $url, $new_mode ) {
    if ( 'crm' == $new_mode ) {
        return admin_url( 'admin.php?page=erp-sales' );
    } elseif ( 'hrm' == $new_mode ) {
        return admin_url( 'admin.php?page=erp-hr' );
    }

    return $url;
}

add_filter( 'erp_switch_redirect_to', 'erp_switch_redirect', 10, 2 );