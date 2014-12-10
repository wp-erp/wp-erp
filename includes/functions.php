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
            'modules' => apply_filters( 'erp_hr_modules', array() )
        ),
        'crm' => array(
            'title'   => __( 'CRM', 'wp-erp' ),
            'slug'    => 'erp-crm',
            'modules' => apply_filters( 'erp_crm_modules', array() )
        ),
        'accounting' => array(
            'title'   => __( 'Accounting', 'wp-erp' ),
            'slug'    => 'erp-accounting',
            'modules' => apply_filters( 'erp_accounting_modules', array() )
        ),
        'pm' => array(
            'title'   => __( 'Project Management', 'wp-erp' ),
            'slug'    => 'erp-pm',
            'modules' => apply_filters( 'erp_pm_modules', array() )
        ),
        'inventory' => array(
            'title'   => __( 'Inventory Management', 'wp-erp' ),
            'slug'    => 'erp-inventory',
            'modules' => apply_filters( 'erp_inventory_modules', array() )
        ),
        'warehouse' => array(
            'title'   => __( 'Warehouse Management', 'wp-erp' ),
            'slug'    => 'erp-warehouse',
            'modules' => apply_filters( 'erp_warehouse_modules', array() )
        ),
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
 * @return [type] [description]
 */
function erp_admin_get_current_mode() {
    global $current_user;

    $modules = erp_get_modules();
    $mode    = get_user_meta( $current_user->ID, '_erp_mode', true );

    if ( !empty( $mode ) && array_key_exists( $mode, $modules ) ) {
        return $modules[$mode];
    }

    return erp_get_default_module();
}