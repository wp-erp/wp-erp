<?php
/**
 * Customer related necessary helper function
 */

/**
 * Get CRM life statges
 *
 * @since 1.0
 *
 * @param array $label
 *
 * @return array
 */
function erp_crm_get_life_statges_dropdown_raw( $label = [] ) {

    $life_statges = [
        'lead'        => __( 'Lead', 'wp-erp' ),
        'opportunity' => __( 'Opportunity', 'wp-erp' ),
        'customer'    => __( 'Customer', 'wp-erp' )
    ];

    if ( $label ) {
        $life_statges = $label + $life_statges;
    }

    return apply_filters( 'erp_crm_life_statges', $life_statges );
}

/**
 * Get customer type
 *
 * @since 1.0
 *
 * @param  array  $label
 * @return array
 */
function erp_crm_get_customer_type( $label = [] ) {

    $type = [
        'customer' => __( 'Customer', 'wp-erp' ),
        'company'  => __( 'Company', 'wp-erp' ),
    ];

    if ( $label ) {
        $type = $label + $type;
    }

    return apply_filters( 'erp_crm_customer_type', $type );
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
function erp_crm_get_life_statges_dropdown( $label = [], $selected = '' ) {

    $life_statges = erp_crm_get_life_statges_dropdown_raw( $label );
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
 * Customer Restore from trash
 *
 * @since 1.0
 *
 * @param  array|int $customer_ids
 *
 * @return void
 */
function erp_crm_customer_restore( $customer_ids ) {
    if ( empty( $customer_ids ) ) {
        return;
    }

    if ( is_array( $customer_ids ) ) {
        foreach ( $customer_ids as $key => $user_id ) {
            WeDevs\ERP\Framework\Models\People::withTrashed()->find( $user_id )->restore();
        }
    }

    if ( is_int( $customer_ids ) ) {
        WeDevs\ERP\Framework\Models\People::withTrashed()->find( $customer_ids )->restore();
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

    $statuses = erp_crm_get_customer_type( [ 'all' => __( 'All', 'wp-erp' ) ] ) ;
    $counts   = array();

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    $cache_key = 'erp-crm-customer-status-counts';
    $results = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $results ) {

        $people        = new \WeDevs\ERP\Framework\Models\People();
        $db            = new \WeDevs\ORM\Eloquent\Database();

        $results = $people->select( array( $db->raw('type as status, COUNT(id) as num') ) )
                    ->where( 'type', '!=', '' )
                    ->groupBy('type')
                    ->get()->toArray();


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

/**
 *  Add Company to a Custom
 *
 * @since  1.0
 *
 * @return
 */
function erp_crm_customer_add_company( $customer_id, $company_id ) {

    global $wpdb;

    $wpdb->insert( $wpdb->prefix . 'erp_crm_customer_companies', array(
            'customer_id' => $customer_id,
            'company_id'  => $company_id
        ));
}

/**
 * Get all the companies for a single costomer
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_customer_get_company( $customer_id ) {

    global $wpdb;

    $sql = "SELECT  peop.*, com.id, com.company_id
            FROM " . $wpdb->prefix . "erp_crm_customer_companies AS com
            LEFT JOIN " . $wpdb->prefix . "erp_peoples AS peop ON peop.id = com.company_id
            WHERE com.customer_id = ". $customer_id;

    return $wpdb->get_results( $sql );
}

/**
 * Updates company info for a customer
 *
 * @since 1.0
 *
 * @return void
 */
function erp_crm_customer_update_company( $row_id, $company_id ) {
    global $wpdb;

    $wpdb->update( $wpdb->prefix . "erp_crm_customer_companies", ['company_id' => $company_id], ['id' => $row_id] );
}

/**
 * Remove company from Customer
 *
 * @since 1.0
 *
 * @return mixed
 */
function erp_crm_customer_remove_company( $id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_crm_customer_companies', ['id' => $id] );

}

/**
 * Get Customer Company by ID
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_customer_company_by_id( $query_id ) {
    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}erp_crm_customer_companies WHERE id = $query_id";
    return $wpdb->get_row( $sql, ARRAY_A );
}

/**
 * Get social fields
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_social_field() {

    $social_field = [
        'facebook' => [
            'title' => __( 'Facebook', 'wp-erp' ),
            'icon' => '<i class="fa fa-facebook-square"></i>',
        ],

        'twitter' => [
            'title' => __( 'Twitter', 'wp-erp' ),
            'icon' => '<i class="fa fa-twitter-square"></i>',
        ],

        'googleplus' => [
            'title' => __( 'Google+', 'wp-erp' ),
            'icon' => '<i class="fa fa-google-plus-square"></i>',
        ],

        'linkedin' => [
            'title' => __( 'Linkedin', 'wp-erp' ),
            'icon' => '<i class="fa fa-linkedin-square"></i>',
        ]
    ];

    return apply_filters( 'erp_crm_social_field', $social_field );
}
