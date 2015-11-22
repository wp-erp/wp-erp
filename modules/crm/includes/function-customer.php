<?php
/**
 * Customer related necessary helper function
 */

/**
 * Get CRM life statges
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_life_statges_dropdown_raw( $select = null ) {

    $life_statges = [
        'lead'        => __( 'Lead', 'wp-erp' ),
        'opportunity' => __( 'Opportunity', 'wp-erp' ),
        'customer'    => __( 'Customer', 'wp-erp' )
    ];

    if ( $select ) {
        return isset( $life_statges[$select] ) ? $life_statges[$select] : '';
    }

    return apply_filters( 'erp_crm_life_statges', $life_statges );
}

function erp_crm_get_life_statges_dropdown( $select) {

}