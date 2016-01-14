<?php
/**
 * Customer related necessary helper function
 */

/**
 * Get an avatar avatar
 *
 * @param  integer  avatar size in pixels
 *
 * @return string  image with HTML tag
 */
function erp_crm_get_avatar( $id, $size = 32, $user = false ) {

    if ( $id ) {

        if ( $user ) {
            return get_avatar( $id, $size );
        }

        $user_photo_id = erp_people_get_meta( $id, 'photo_id', true );

        if ( ! empty( $user_photo_id ) ) {
            $image = wp_get_attachment_thumb_url( $user_photo_id );
            return sprintf( '<img src="%1$s" alt="" class="avatar avatar-%2$s photo" height="auto" width="%2$s" />', $image, $size );
        }
    }

    return get_avatar( $id, $size );
}

function erp_crm_get_emplyees( $selected = '' ) {
    $employees = erp_hr_get_employees_dropdown_raw( get_current_user_id() );
    $dropdown     = '';
    unset( $employees[0] );

    if ( $employees ) {
        foreach ( $employees as $key => $title ) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}



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
        'customer'    => __( 'Customer', 'wp-erp' ),
        'lead'        => __( 'Lead', 'wp-erp' ),
        'opportunity' => __( 'Opportunity', 'wp-erp' )
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
function erp_crm_customer_get_status_count( $type = null ) {
    global $wpdb;

    $statuses = erp_crm_get_life_statges_dropdown_raw( [ 'all' => __( 'All', 'wp-erp' ) ] ) ;
    $counts   = array();

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    $cache_key = 'erp-crm-customer-status-counts';
    $results = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $results ) {

        $people           = new \WeDevs\ERP\Framework\Models\People();
        $db               = new \WeDevs\ORM\Eloquent\Database();
        $people_table     = $wpdb->prefix . 'erp_peoples';
        $peoplemeta_table = $wpdb->prefix . 'erp_peoplemeta';

        $results = $people->select( array( $db->raw( $peoplemeta_table . '.meta_value as `status`, COUNT( ' . $people_table . '.id ) as `num`') ) )
                    ->leftjoin( $peoplemeta_table, $peoplemeta_table . '.erp_people_id', '=', $people_table . '.id')
                    ->where( $peoplemeta_table . '.meta_key', '=', 'life_stage' )
                    ->where( $people_table . '.type', '=', $type )
                    ->groupBy( $peoplemeta_table. '.meta_value')
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
 * Get all the companies for a single costomer
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_company_get_customers( $company_id ) {

    global $wpdb;

    $sql = "SELECT  peop.*, com.id as com_cus_id, com.customer_id
            FROM " . $wpdb->prefix . "erp_crm_customer_companies AS com
            LEFT JOIN " . $wpdb->prefix . "erp_peoples AS peop ON peop.id = com.customer_id
            WHERE com.company_id = ". $company_id;

    return $wpdb->get_results( $sql );
}


function erp_crm_get_customer_details_url( $id ) {
    return admin_url( 'admin.php?page=erp-sales-customers&action=view&id=' . $id );
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

function erp_crm_get_customer_feeds_nav() {
    return apply_filters( 'erp_crm_customer_feeds_nav', [

        'new_note' => [
            'title' => __( 'New Note', 'wp-erp' ),
            'icon'  => '<i class="fa fa-file-text-o"></i>'
        ],

        'email' => [
            'title' => __( 'Email', 'wp-erp' ),
            'icon'  => '<i class="fa fa-envelope-o"></i>'
        ],

        'log_activity' => [
            'title' => __( 'Log Activity', 'wp-erp' ),
            'icon'  => '<i class="fa fa-list"></i>'
        ],

        'call' => [
            'title' => __( 'Call', 'wp-erp' ),
            'icon'  => '<i class="fa fa-phone"></i>'
        ],

        'schedule' => [
            'title' => __( 'Schedule', 'wp-erp' ),
            'icon'  => '<i class="fa fa-calendar-check-o"></i>'
        ]

    ] );
}

/**
 * Check if customer assign already exist
 *
 * @since 1.0
 *
 * @param  integer $customer_id
 * @param  integer $company_id
 *
 * @return null|array
 */
function erp_crm_check_customer_exist_company( $customer_id, $company_id ) {
    global $wpdb;

    $sql = "SELECT `id` FROM {$wpdb->prefix}erp_crm_customer_companies WHERE `customer_id` = '$customer_id' AND `company_id` = '$company_id'";
    return $wpdb->get_row( $sql, ARRAY_A );
}

/**
 * Get all customer feeds
 *
 * @since 1.0
 *
 * @param  integer $customer_id
 *
 * @return array
 */
function erp_crm_get_customer_activity( $customer_id = null ) {
    $feeds = [];
    $db = new \WeDevs\ORM\Eloquent\Database();

    $results = \WeDevs\ERP\CRM\Models\Activity::select( [ '*', $db->raw('MONTHNAME(`created_at`) as feed_month, YEAR( `created_at` ) as feed_year' ) ] )
               ->where( 'user_id', $customer_id )
               ->with( [ 'contact',
                        'created_by' => function( $query ) {
                            $query->select( 'ID', 'user_nicename', 'user_email', 'user_url', 'display_name' );
                        }
                    ] )
               ->orderBy( 'created_at', 'DESC' )
               ->get()
               ->toArray();

    foreach ( $results as $key => $value ) {
        $value['extra'] = json_decode( base64_decode( $value['extra'] ), true );

        if ( isset( $value['extra']['invite_contact'] ) && count( $value['extra']['invite_contact'] ) > 0 ) {
            foreach ( $value['extra']['invite_contact'] as $user_id ) {
                $value['extra']['invited_user'][] = [
                    'id' => $user_id,
                    'name' => get_the_author_meta( 'display_name', $user_id )
                ];
            }
        } else {
            $value['extra']['invited_user'] = [];
        }

        unset( $value['extra']['invite_contact'] );
        $value['created_by']['avatar']  = get_avatar_url( $value['created_by']['ID'] );
        $value['created_date']          = date( 'Y-m-d', strtotime( $value['created_at'] ) );
        $value['created_timeline_date'] = date( 'Y-m', strtotime( $value['created_at'] ) );
        $feeds[] = $value;
    }

    return $feeds;
}

/**
 * Save customer activity feeds
 *
 * @since 1.0
 *
 * @param  array $data
 *
 * @return array
 */
function erp_crm_save_customer_feed_data( $data ) {

    if ( isset( $data['id'] ) && !empty( $data['id'] ) ) {
        $saved_activity = WeDevs\ERP\CRM\Models\Activity::find( $data['id'] )->update( $data );
        $saved_activity_id = $data['id'];
    } else {
        $saved_activity = WeDevs\ERP\CRM\Models\Activity::create( $data );
        $saved_activity_id = $saved_activity->id;
    }

    $activity   = WeDevs\ERP\CRM\Models\Activity::
                with( [ 'contact',
                        'created_by' => function( $query ) {
                            $query->select( 'ID', 'user_nicename', 'user_email', 'user_url', 'display_name' );
                        }
                    ] )
                ->find( $saved_activity_id )
                ->toArray();

    $activity['extra'] = json_decode( base64_decode( $activity['extra'] ), true );

    if ( isset( $value['extra']['invite_contact'] ) && count( $value['extra']['invite_contact'] ) > 0 ) {
        foreach ( $value['extra']['invite_contact'] as $user_id ) {
            $value['extra']['invited_user'][] = [
                'id' => $user_id,
                'name' => get_the_author_meta( 'display_name', $user_id )
            ];
        }

        unset( $value['extra']['invite_contact'] );
    }

    $activity['created_by']['avatar'] = get_avatar_url( $activity['created_by']['ID'] );
    $activity['created_date'] = date( 'Y-m-d', strtotime( $activity['created_at'] ) );
    $activity['created_timeline_date'] = date( 'Y-m', strtotime( $activity['created_at'] ) );

    return $activity;
}

/**
 * Delete customer activity feeds
 *
 * @since 1.0
 *
 * @param  integer $feed_id
 *
 * @return collection
 */
function erp_crm_customer_delete_activity_feed( $feed_id ) {
    return WeDevs\ERP\CRM\Models\Activity::find( $feed_id )->delete( $feed_id );
}