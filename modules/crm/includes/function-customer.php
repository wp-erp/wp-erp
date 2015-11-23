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
function erp_crm_get_life_statges_dropdown_raw( $label = '' ) {

    $life_statges = [
        'lead'        => __( 'Lead', 'wp-erp' ),
        'opportunity' => __( 'Opportunity', 'wp-erp' ),
        'customer'    => __( 'Customer', 'wp-erp' )
    ];

    if ( $label ) {
        $life_statges = [ '' => $label ] + $life_statges;
    }

    return apply_filters( 'erp_crm_life_statges', $life_statges );
}

/**
 * Get life stages as a select option dropdown
 *
 * @since 1.0
 *
 * @param  string $selected
 *
 * @return html
 */
function erp_crm_get_life_statges_dropdown( $selected = '' ) {

    $life_statges = erp_crm_get_life_statges_dropdown_raw();
    $dropdown     = '';

    if ( $life_statges ) {
        foreach ( $life_statges as $key => $title ) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}

/**
 * Delete Customer data
 *
 * @since 1.0
 *
 * @param  mixed  $customer_ids [array|integer]
 * @param  boolean $hard
 *
 * @return void
 */
function erp_crm_customer_delete( $customer_ids, $hard = false ) {

    if ( empty( $customer_ids ) ) {
        return;
    }

    do_action( 'erp_crm_delete_customer', $customer_ids );

    if ( is_array( $customer_ids ) ) {
        foreach ( $customer_ids as $key => $user_id ) {

            if ( $hard ) {
                WeDevs\ERP\Framework\Models\People::withTrashed()->find( $user_id )->forceDelete();
                WeDevs\ERP\Framework\Models\Peoplemeta::where( 'erp_people_id', $user_id )->delete();
            } else {
                WeDevs\ERP\Framework\Models\People::find( $user_id )->delete();
            }
        }
    }

    if ( is_int( $customer_ids ) ) {

        if ( $hard ) {
            WeDevs\ERP\Framework\Models\People::withTrashed()->find( $customer_ids )->forceDelete();
            WeDevs\ERP\Framework\Models\Peoplemeta::where( 'erp_people_id', $customer_ids )->delete();
        } else {
            WeDevs\ERP\Framework\Models\People::find( $customer_ids )->delete();
        }
    }
}

/**
 * Get customer life statges status count
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_customer_get_status_count() {
    global $wpdb;

    $statuses = array( 'all' => __( 'All', 'wp-erp' ) ) + erp_crm_get_life_statges_dropdown_raw();
    $counts   = array();

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    $cache_key = 'erp-crm-customer-status-counts';
    $results = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $results ) {

        $people        = new \WeDevs\ERP\Framework\Models\People();
        $db            = new \WeDevs\ORM\Eloquent\Database();

        $peoplemeta_tb = $wpdb->prefix . 'erp_peoplemeta';
        $people_tb     = $wpdb->prefix . 'erp_peoples';

        $results = $people->leftjoin( $peoplemeta_tb, $people_tb.'.id', '=', $peoplemeta_tb.'.erp_people_id' )
                    ->select( $db->raw( $peoplemeta_tb.'.meta_value as status, COUNT('.$people_tb.'.id) as num') )
                    ->where( $peoplemeta_tb.'.meta_key', 'life_stage' )
                    ->groupBy( $peoplemeta_tb.'.meta_value' )
                    ->get()
                    ->toArray();

        wp_cache_set( $cache_key, $results, 'wp-erp' );
    }

    foreach ( $results as $row ) {
        if ( array_key_exists( $row['status'], $counts ) ) {
            $counts[ $row['status'] ]['count'] = (int) $row['num'];
        }

        $counts['all']['count'] += (int) $row['num'];
    }

    return $counts;
}

/**
 * Count trash customer
 *
 * @since 1.0
 *
 * @return integer [no of trash customer]
 */
function erp_crm_count_trashed_customers() {
    $customer = new \WeDevs\ERP\Framework\Models\People();
    return $customer->onlyTrashed()->count();
}
