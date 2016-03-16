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

/**
 * Get Employee for CRM
 *
 * @since 1.0
 *
 * @param  string $selected
 *
 * @return html
 */
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
 * Get contact details url according to contact type
 *
 * @since 1.0
 *
 * @param integer $id
 * @param string $type
 *
 * @return string url
 */
function erp_crm_get_details_url( $id, $type ) {

    if ( $id ) {

        if ( $type == 'contact' ) {
            return admin_url( 'admin.php?page=erp-sales-customers&action=view&id=' . $id );
        }

        if ( $type == 'company' ) {
            return admin_url( 'admin.php?page=erp-sales-companies&action=view&id=' . $id );
        }
    }

    return admin_url( 'admin.php' );
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
 * Get contact dropdown list as array
 *
 * @since 1.0
 *
 * @param  array  $label
 *
 * @return array | list of all contact with copmany
 */
function erp_crm_get_contact_dropdown( $label = [] ) {
    $contacts = erp_get_peoples( [ 'number' => '-1', 'type' => [ 'contact', 'company' ] ] );
    $list = [];

    foreach ( $contacts as $key => $contact ) {
        $name = ( $contact->type == 'company' ) ? $contact->company : $contact->first_name . ' ' . $contact->last_name;
        $list[$contact->id] = $name . ' ( ' . ucfirst( $contact->type ) . ' ) ';
    }

    if ( $label ) {
        $list = $label + $list;
    }

    return $list;
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

    $cache_key = 'erp-crm-customer-status-counts-'. $type;
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
            'title' => __( 'Google Plus', 'wp-erp' ),
            'icon' => '<i class="fa fa-google-plus-square"></i>',
        ],

        'linkedin' => [
            'title' => __( 'Linkedin', 'wp-erp' ),
            'icon' => '<i class="fa fa-linkedin-square"></i>',
        ]
    ];

    return apply_filters( 'erp_crm_social_field', $social_field );
}

/**
 * Customer Activity navigation menu
 *
 * @since 1.0
 *
 * @return array
 */
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
 * Prepare Schedule data for save
 *
 * @since 1.0
 *
 * @param  array $postdata
 *
 * @return array
 */
function erp_crm_customer_prepare_schedule_postdata( $postdata ) {

    if ( !is_user_logged_in() ) {
        return;
    }

    if ( ! $postdata ) {
        return;
    }

    $extra_data = [
        'schedule_title'     => ( isset( $postdata['schedule_title'] ) && !empty( $postdata['schedule_title'] ) ) ? $postdata['schedule_title'] : '',
        'all_day'            => isset( $postdata['all_day'] ) ? (string)$postdata['all_day'] : 'false',
        'allow_notification' => isset( $postdata['allow_notification'] ) ? (string)$postdata['allow_notification'] : 'false',
        'invite_contact'     => ( isset( $postdata['invite_contact'] ) && ! empty( $postdata['invite_contact'] ) ) ? $postdata['invite_contact'] : []
    ];

    $extra_data['notification_via']           = ( isset( $postdata['notification_via'] ) && $extra_data['allow_notification'] == 'true' ) ? $postdata['notification_via'] : '';
    $extra_data['notification_time']          = ( isset( $postdata['notification_time'] ) && $extra_data['allow_notification'] == 'true' ) ? $postdata['notification_time'] : '';
    $extra_data['notification_time_interval'] = ( isset( $postdata['notification_time_interval'] ) && $extra_data['allow_notification'] == 'true' ) ? $postdata['notification_time_interval'] : '';

    $start_time = ( isset( $postdata['start_time'] ) && $extra_data['all_day'] == 'false' ) ? $postdata['start_time'] : '00:00:00';
    $end_time   = ( isset( $postdata['end_time'] ) && $extra_data['all_day'] == 'false' ) ? $postdata['end_time'] : '00:00:00';

    if ( $extra_data['allow_notification'] == 'true' ) {
        $notify_date = new \DateTime( $postdata['start_date'].$start_time );
        $notify_date->modify('-' . $extra_data['notification_time_interval'] . ' '. $extra_data['notification_time'] );
        $extra_data['notification_datetime'] = $notify_date->format( 'Y-m-d H:i:s' );
    } else {
        $extra_data['notification_datetime'] = '';
    }

    $save_data = [
        'id'         => ( isset( $postdata['id'] ) && ! empty( $postdata['id'] ) ) ? $postdata['id'] : '',
        'user_id'    => $postdata['user_id'],
        'created_by' => $postdata['created_by'],
        'message'    => $postdata['message'],
        'type'       => 'log_activity',
        'log_type'   => ( isset( $postdata['schedule_type'] ) && !empty( $postdata['schedule_type'] ) ) ?  $postdata['schedule_type'] : '',
        'start_date' => date( 'Y-m-d H:i:s', strtotime( $postdata['start_date'].$start_time ) ),
        'end_date'   => date( 'Y-m-d H:i:s', strtotime( $postdata['end_date'].$end_time ) ),
        'extra'      => base64_encode( json_encode( $extra_data ) )
    ];

    return $save_data;
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
function erp_crm_get_feed_activity( $postdata ) {
    global $wpdb;
    $feeds = [];
    $db = new \WeDevs\ORM\Eloquent\Database();

    $results =  \WeDevs\ERP\CRM\Models\Activity::select( [ '*', $db->raw('MONTHNAME(`created_at`) as feed_month, YEAR( `created_at` ) as feed_year' ) ] )
                ->with( [ 'contact',
                        'created_by' => function( $query ) {
                            $query->select( 'ID', 'user_nicename', 'user_email', 'user_url', 'display_name' );
                        }
                    ] );

    if ( isset( $postdata['customer_id'] ) && ! empty( $postdata['customer_id'] ) ) {
        $results = $results->where( 'user_id', $postdata['customer_id'] );
    }

    if ( isset( $postdata['created_by'] ) && !empty( $postdata['created_by'] ) ) {
        $results = $results->where( 'created_by', $postdata['created_by'] );
    }

    if ( isset( $postdata['type'] ) && !empty( $postdata['type'] ) ) {

        if ( $postdata['type'] == 'schedule' ) {
            $results = $results->where( 'type', 'log_activity' )->where( 'start_date', '>', current_time('mysql') );
        } else if ( $postdata['type'] == 'log_activity' ) {
            $results = $results->where( 'type', 'log_activity' )->where( 'start_date', '<', current_time('mysql') );
        } else {
            $results = $results->where( 'type', $postdata['type'] );
        }
    }

    if ( isset( $postdata['created_at'] ) && !empty( $postdata['created_at'] ) ) {
        $results = $results->where( $db->raw( "DATE_FORMAT( `created_at`, '%Y-%m-%d' )" ), $postdata['created_at'] );
    }

    $results = $results->orderBy( 'created_at', 'DESC' );

    if ( isset( $postdata['limit'] ) && $postdata['limit'] != -1 ) {
        $results = $results->skip( $postdata['offset'] )->take( $postdata['limit'] );
    }

    $results = $results->get()->toArray();

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

    if ( isset( $activity['extra']['invite_contact'] ) && count( $activity['extra']['invite_contact'] ) > 0 ) {
        foreach ( $activity['extra']['invite_contact'] as $user_id ) {
            $activity['extra']['invited_user'][] = [
                'id' => $user_id,
                'name' => get_the_author_meta( 'display_name', $user_id )
            ];
        }
    } else {
        $activity['extra']['invited_user'] = [];
    }

    $activity['created_by']['avatar'] = get_avatar_url( $activity['created_by']['ID'] );
    $activity['created_date'] = date( 'Y-m-d', strtotime( $activity['created_at'] ) );
    $activity['created_timeline_date'] = date( 'Y-m', strtotime( $activity['created_at'] ) );

    return $activity;
}

/**
 * Get customer single activity feeds
 *
 * @since 1.0
 *
 * @param  integer $feed_id
 *
 * @return collection
 */
function erp_crm_customer_get_single_activity_feed( $feed_id ) {

    if ( ! $feed_id ) {
        return;
    }

    $results = [];
    $data = WeDevs\ERP\CRM\Models\Activity::find( $feed_id )->toArray();

    if ( !$data ) {
        return;
    }

    $data['extra'] = json_decode( base64_decode( $data['extra'] ), true );

    return $data;
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

/**
 * Set schedule notification for customer
 *
 * @since 1.0
 *
 * @return void
 */
function erp_crm_customer_schedule_notification() {
    $schedules = \WeDevs\ERP\CRM\Models\Activity::schedules()->get()->toArray();

    foreach ( $schedules as $key => $activity ) {
        $extra = json_decode( base64_decode( $activity['extra'] ), true );

        if ( isset ( $extra['allow_notification'] ) && $extra['allow_notification'] == 'true' ) {
            if ( current_time('mysql') == $extra['notification_datetime'] ) {
                erp_crm_send_schedule_notification( $activity, $extra );
            }
        }
    }
}

/**
 * Sending Customer schedule notification
 *
 * @since 1.0
 *
 * @param  object  $activity
 * @param  boolean $extra
 *
 * @return void
 */
function erp_crm_send_schedule_notification( $activity, $extra = false ) {

    if ( ! is_user_logged_in() ) {
        return;
    }

    switch ( $extra['notification_via'] ) {
        case 'email':
            $users = [];

            foreach( $extra['invite_contact'] as $contact ) {
                $users[] = get_the_author_meta( 'user_email', $contact );
            }

            $created_user = get_the_author_meta('user_email', $activity['created_by'] );

            array_push( $users, $created_user );

            foreach ( $users as $key => $user ) {
                // @TODO: Add customer body template for seding email to user
                $body = 'You have a schedule after ' . $extra['notification_time_interval'] . $extra['notification_time'] . ' at ' . $activity['start_date'];
                wp_mail( $user, 'ERP Schedule', $body );
            }

            break;

        case 'sms':
            // @TODO: Add SMS notification for schedule meeting
            break;

        default:
            do_action( 'erp_crm_send_schedule_notification', $activity );
            break;
    }
}

/**
 * Create Contact group
 *
 * @param  array $data
 *
 * @return array
 */
function erp_crm_save_contact_group( $data ) {
    if ( $data['id'] ) {
        $result = WeDevs\ERP\CRM\Models\ContactGroup::find( $data['id'] )->update( $data );
    } else {
        $result = WeDevs\ERP\CRM\Models\ContactGroup::create( $data );
    }
    return $result;
}

/**
 * Get all contact group
 *
 * @since 1.0
 *
 * @return object
 */
function erp_crm_get_contact_groups( $args = [] ) {

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'count'      => false,
    ];

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'erp-crm-contact-group-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $items ) {
        $results               = [];
        $contact_group         = new WeDevs\ERP\CRM\Models\ContactGroup();

        $contact_group = $contact_group->with( 'contact_subscriber' );

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' ) {
            $contact_group = $contact_group->skip( $args['offset'] )->take( $args['number'] );
        }

        // Check is the row want to search
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s = $args['s'];
            $contact_group = $contact_group->where( 'name', 'LIKE', "%$arg_s%" )
                    ->orWhere( 'description', 'LIKE', "%$arg_s%" );
        }

        // Render all collection of data according to above filter (Main query)
        $results = $contact_group->orderBy( $args['orderby'], $args['order'] )
                ->get()
                ->toArray();

        foreach( $results as $key => $group ) {
            $subscriber = array_count_values( wp_list_pluck( $group['contact_subscriber'], 'status' ) );
            unset( $group['contact_subscriber'] );
            $items[$key] = $group;
            $items[$key]['subscriber'] = isset( $subscriber['subscribe'] ) ? $subscriber['subscribe'] : 0;
            $items[$key]['unsubscriber'] = isset( $subscriber['unsubscribe'] ) ? $subscriber['unsubscribe'] : 0;
        }

        $items = erp_array_to_object( $items );

        // Check if args count true, then return total count customer according to above filter
        if ( $args['count'] ) {
            $items = WeDevs\ERP\CRM\Models\ContactGroup::count();
        }

        wp_cache_set( $cache_key, $items, 'wp-erp' );
    }

    return $items;
}

/**
 * Get contact group by its primary key[id]
 *
 * @since 1.0
 *
 * @param  integer $id
 *
 * @return array
 */
function erp_crm_get_contact_group_by_id( $id ) {
    return WeDevs\ERP\CRM\Models\ContactGroup::find( $id )->toArray();
}

/**
 * Delete contact group
 *
 * @since 1.0
 *
 * @param  integer $id
 *
 * @return void
 */
function erp_crm_contact_group_delete( $id ) {
    WeDevs\ERP\CRM\Models\ContactGroup::find( $id )->delete();
}

/**
 * Get susbcriber contact
 *
 * @since 1.0
 *
 * @param  array  $args
 *
 * @return array|object
 */
function erp_crm_get_subscriber_contact( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'count'      => false,
    ];

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'erp-crm-subscriber-contact-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $items ) {
        $converted_data       = [];
        $contact_subscribe_tb = $wpdb->prefix . 'erp_crm_contact_subscriber';
        $contact_group_tb     = $wpdb->prefix . 'erp_crm_contact_group';

        $contact_subscribers = WeDevs\ERP\CRM\Models\ContactSubscriber::leftjoin( $contact_group_tb, $contact_group_tb . '.id', '=', $contact_subscribe_tb . '.group_id' );

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' ) {
            $contact_subscribers = $contact_subscribers->skip( $args['offset'] )->take( $args['number'] );
        }

        if ( isset( $args['group_id'] ) && ! empty( $args['group_id'] ) ) {
            $contact_subscribers = $contact_subscribers->where( $contact_group_tb . '.id', '=', $args['group_id'] );
        }

        // Check is the row want to search
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s = $args['s'];
            $contact_subscribers = $contact_subscribers->where( 'name', 'LIKE', "%$arg_s%" )
                    ->orWhere( 'description', 'LIKE', "%$arg_s%" );
        }

        // Render all collection of data according to above filter (Main query)
        $results = $contact_subscribers
                ->get()
                ->groupBy('user_id')
                ->toArray();

        foreach( $results as $user_id=>$value ) {
            $converted_data[] = [
                'user_id' => $user_id,
                'data' => $value
            ];
        }

        $items = erp_array_to_object( $converted_data );

        // Check if args count true, then return total count customer according to above filter
        if ( $args['count'] ) {
            $items = WeDevs\ERP\CRM\Models\ContactSubscriber::leftjoin( $contact_group_tb, $contact_group_tb . '.id', '=', $contact_subscribe_tb . '.group_id' )->groupBy('user_id')->count();
        }

        wp_cache_set( $cache_key, $items, 'wp-erp' );
    }

    return $items;
}

/**
 * Get contact gorup dropdown
 *
 * @since 1.0
 *
 * @param  array  $label
 *
 * @return array
 */
function erp_crm_get_contact_group_dropdown( $label = [] ) {
    $groups = erp_crm_get_contact_groups( [ 'number' => '-1' ] );
    $list   = [];
    $unsubscribe_text = '';

    foreach ( $groups as $key => $group ) {
        $list[$group->id] = '<span class="group-name">' . $group->name . '</span>';
        // $list[$group->id] = $group->name;
    }

    if ( $label ) {
        $list = $label + $list;
    }

    return $list;
}

/**
 * Get already subscirbed contact
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_assign_subscriber_contact() {
    $data = \WeDevs\ERP\CRM\Models\ContactSubscriber::select('user_id')->distinct()->get()->toArray();
    return wp_list_pluck( $data, 'user_id' );
}

/**
 * Create Contact subscriber
 *
 * @since 1.0
 *
 * @param  array $data
 *
 * @return return collection|obejct
 */
function erp_crm_create_new_contact_subscriber( $data ) {
    return \WeDevs\ERP\CRM\Models\ContactSubscriber::create( $data );
}

/**
 * Get already user assigned group id
 *
 * @since 1.0
 *
 * @param  integer $user_id
 *
 * @return array
 */
function erp_crm_get_editable_assign_contact( $user_id ) {
    $data = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )->distinct()->get()->toArray();
    return $data;
}

/**
 * Get already user assigned group id
 *
 * @since 1.0
 *
 * @param  integer $user_id
 *
 * @return array
 */
function erp_crm_get_user_assignable_groups( $user_id ) {
    $data = \WeDevs\ERP\CRM\Models\ContactSubscriber::with('groups')->where( 'user_id', $user_id )->distinct()->get()->toArray();
    return $data;
}

/**
 * Delete Contact alreays subscribed
 *
 * @since 1.0
 *
 * @param  integer $user_id
 *
 * @return boolean
 */
function erp_crm_contact_subscriber_delete( $user_id ) {
    if ( is_array( $user_id )) {
        return \WeDevs\ERP\CRM\Models\ContactSubscriber::whereIn( 'user_id', $user_id )->delete();
    } else {
        return \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )->delete();
    }
}

/**
 * Edit contact subscriber
 *
 * Delete if uncheck and if new then
 * create new one.
 *
 * @since 1.0
 *
 * @param  array $groups
 * @param  integer $user_id
 *
 * @return void
 */
function erp_crm_edit_contact_subscriber( $groups, $user_id ) {
    $data = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )->distinct()->get()->toArray();

    $db                         = wp_list_pluck( $data, 'group_id' );
    $existing_group_with_status =  wp_list_pluck( $data, 'status', 'group_id' );
    $existing_group             = $new_group = $del_group = $unsubscribe_group = [];

    if ( !empty( $groups ) ) {
        foreach( $groups as $group ) {
            if ( in_array( $group, $db ) ) {
                $existing_group[] = $group;

                if ( $existing_group_with_status[$group] == 'unsubscribe' ) {
                    $unsubscribe_group[] = $group;
                }
            } else {
                $new_group[] = $group;
            }
        }
    }

    $del_group = array_diff( $db, $existing_group );

    if ( ! empty( $unsubscribe_group ) ) {
        foreach ( $unsubscribe_group as $unsubscribe_group_key => $unsubscribe_group_id ) {
            \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )->where( 'group_id', $unsubscribe_group_id )->update( [ 'status' => 'subscribe' ] );
        }
    }

    if ( !empty( $new_group ) ) {

        foreach ( $new_group as $new_group_key => $new_group_id ) {
            $data = [
                'user_id'  => $user_id,
                'group_id' => $new_group_id,
                'status'   => 'subscribe', // @TODO: Set a settings for that
                'subscribe_at' => current_time('mysql'),
                'unsubscribe_at' => current_time('mysql')
            ];
            erp_crm_create_new_contact_subscriber( $data );
        }

    }

    if ( ! empty( $del_group ) ) {
        foreach ( $del_group as $del_group_key => $del_group_id ) {
            \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )->where( 'group_id', $del_group_id )->where( 'status', 'subscribe' )->delete();
        }
    }
}

/**
 * Get all campaign
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_campaigns( $args = [] ) {

    global $wpdb;

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'created_at',
        'order'      => 'DESC',
        'count'      => false,
        'withgroup'  => true
    ];

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'erp-crm-campaign-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'wp-erp' );

    if ( false === $items ) {
        $campaigns = new \WeDevs\ERP\CRM\Models\Campaign();

        if ( $args['withgroup'] ) {
            $campaigns = $campaigns->with( 'groups' );
        }

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' ) {
            $campaigns = $campaigns->skip( $args['offset'] )->take( $args['number'] );
        }

        // Check is the row want to search
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s = $args['s'];
            $campaigns = $campaigns->where( 'title', 'LIKE', "%$arg_s%" )
                    ->orWhere( 'description', 'LIKE', "%$arg_s%" );
        }

        // Render all collection of data according to above filter (Main query)
        $results = $campaigns
                ->get()
                ->toArray();

        $items = erp_array_to_object( $results );

        // Check if args count true, then return total count customer according to above filter
        if ( $args['count'] ) {
            $items = WeDevs\ERP\CRM\Models\Campaign::count();
        }

        wp_cache_set( $cache_key, $items, 'wp-erp' );
    }

    return $items;
}

/**
 * Get Global Search Fields
 *
 * @since 1.0
 *
 * @param  string $type
 *
 * @return array
 */
function erp_crm_get_serach_key( $type = '' ) {
    $fields = [

        'email' => [
            'title'       => __( 'Email', 'wp-erp' ),
            'type' => 'text',
            'text' => '',
            'condval' => '',
            'condition'   => [
                '~'  => __( 'contains', 'wp-erp' ),
                '!~' => __( 'not contains', 'wp-erp' ),
                '^'  => __( 'begins with', 'wp-erp' ),
                '$'  => __( 'ends with', 'wp-erp' ),
            ]
        ],

        'phone' => [
            'title'     => __( 'Phone', 'wp-erp' ),
            'type'      => 'text',
            'text'      => '',
            'condval'   => '',
            'condition' => [
                ''   => __( 'has', 'wp-erp' ),
                '~'  => __( 'contains', 'wp-erp' ),
                '!~' => __( 'not contains', 'wp-erp' ),
                '^'  => __( 'begins with', 'wp-erp' ),
                '$'  => __( 'ends with', 'wp-erp' ),
            ]
        ],

        'mobile' => [
            'title'     => __( 'Mobile', 'wp-erp' ),
            'type'      => 'text',
            'text'      => '',
            'condval'   => '',
            'condition' => [
                ''   => __( 'has', 'wp-erp' ),
                '~'  => __( 'contains', 'wp-erp' ),
                '!~' => __( 'not contains', 'wp-erp' ),
                '^'  => __( 'begins with', 'wp-erp' ),
                '$'  => __( 'ends with', 'wp-erp' ),
            ]
        ],

        'website' => [
            'title'     => __( 'Website', 'wp-erp' ),
            'type'      => 'text',
            'text'      => '',
            'condval'   => '',
            'condition' => [
                ''   => __( 'has', 'wp-erp' ),
                '~'  => __( 'contains', 'wp-erp' ),
                '!~' => __( 'not contains', 'wp-erp' ),
                '^'  => __( 'begins with', 'wp-erp' ),
                '$'  => __( 'ends with', 'wp-erp' ),
            ]
        ],

        'city' => [
            'title'     => __( 'City', 'wp-erp' ),
            'type'      => 'text',
            'text'      => '',
            'condval'   => '',
            'condition' => [
                ''   => __( 'from', 'wp-erp' ),
                '!'  => __( 'not from', 'wp-erp' ),
                '~'  => __( 'contains', 'wp-erp' ),
                '!~' => __( 'not contains', 'wp-erp' )
            ]
        ],

        'country_state' => [
            'title'     => __( 'Country/State', 'wp-erp' ),
            'type'      => 'dropdown',
            'text'      => '',
            'condval'   => '',
            'condition' => [
                ''   => __( 'from', 'wp-erp' ),
                '!'  => __( 'not from', 'wp-erp' )
            ],
            'options' => \WeDevs\ERP\Countries::instance()->country_dropdown_options(),
        ],

    ];

    if ( $type == 'crm_page_erp-sales-customers' ) {
        $fields = erp_crm_get_customer_serach_key() + $fields;
    }

    if ( $type == 'crm_page_erp-sales-companies' ) {
        $fields = erp_crm_get_company_serach_key() + $fields;
    }

    return apply_filters( 'erp_crm_global_serach_fields', $fields );
}

/**
 * Get extra search fields for customer
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_customer_serach_key() {
    return apply_filters( 'erp_crm_customer_search_fields', [
        'first_name' => [
            'title'       => __( 'First Name', 'wp-erp' ),
            'type' => 'text',
            'text' => '',
            'condval' => '',
            'condition'   => [
                ''   => __( 'is', 'wp-erp' ),
                '!'  => __( 'is not', 'wp-erp' ),
                '~'  => __( 'contains', 'wp-erp' ),
                '!~' => __( 'not contains', 'wp-erp' ),
                '^'  => __( 'begins with', 'wp-erp' ),
                '$'  => __( 'ends with', 'wp-erp' ),
            ]
        ],

        'last_name' => [
            'title'       => __( 'Last Name', 'wp-erp' ),
            'type' => 'text',
            'text' => '',
            'condval' => '',
            'condition'   => [
                ''   => __( 'is', 'wp-erp' ),
                '!'  => __( 'is not', 'wp-erp' ),
                '~'  => __( 'contains', 'wp-erp' ),
                '!~' => __( 'not contains', 'wp-erp' ),
                '^'  => __( 'begins with', 'wp-erp' ),
                '$'  => __( 'ends with', 'wp-erp' ),
            ]
        ],
    ] );
}

/**
 * Get extra serach fields for company
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_company_serach_key() {
    return apply_filters( 'erp_crm_company_search_fields', [
        'company' => [
            'title'       => __( 'Company Name', 'wp-erp' ),
            'type' => 'text',
            'text' => '',
            'condval' => '',
            'condition'   => [
                ''   => __( 'is', 'wp-erp' ),
                '!'  => __( 'is not', 'wp-erp' ),
                '~'  => __( 'contains', 'wp-erp' ),
                '!~' => __( 'not contains', 'wp-erp' ),
                '^'  => __( 'begins with', 'wp-erp' ),
                '$'  => __( 'ends with', 'wp-erp' ),
            ]
        ]
    ] );
}


/**
 * Build queries value according to regex
 *
 * @since 1.0
 *
 * @param  string $value
 *
 * @return array
 */
function erp_crm_get_save_search_regx( $values ) {
    $result = [];

    if ( is_array( $values ) ) {
        foreach ( $values as  $value) {
            if ( preg_match( '/^!(?!~)/', $value ) ) {
                $result[preg_replace( '/^!(?!~)/', '', $value )] = '!=' ;
            } elseif ( preg_match( '/^~/', $value ) ) {
                $result['%' . preg_replace( '/^~/', '', $value ) . '%'] ='LIKE';
            } elseif ( preg_match( '/^!~/', $value ) ) {
                $result['%' . preg_replace( '/^!~/', '', $value ) . '%'] = 'NOT LIKE';
            } elseif ( preg_match( '/^\^/', $value ) ) {
                $result[preg_replace( '/^\^/', '', $value ) . '%'] = 'LIKE';
            } elseif ( preg_match( '/^\$/', $value ) ) {
                $result['%' . preg_replace( '/^\$/', '', $value )] = 'LIKE';
            } else {
                $result[$value] = '=';
            }
        }
    } else {
        if ( preg_match( '/^!(?!~)/', $values ) ) {
            $result[preg_replace( '/^!(?!~)/', '', $values )] = '!=' ;
        } elseif ( preg_match( '/^~/', $values ) ) {
            $result['%' . preg_replace( '/^~/', '', $values ) . '%'] ='LIKE';
        } elseif ( preg_match( '/^!~/', $values ) ) {
            $result['%' . preg_replace( '/^!~/', '', $values ) . '%'] = 'NOT LIKE';
        } elseif ( preg_match( '/^\^/', $values ) ) {
            $result[preg_replace( '/^\^/', '', $values ) . '%'] = 'LIKE';
        } elseif ( preg_match( '/^\$/', $values ) ) {
            $result['%' . preg_replace( '/^\$/', '', $values )] = 'LIKE';
        } else {
            $result[$values] = '=';
        }
    }

    return apply_filters( 'erp_crm_get_save_search_regx', $result, $values );
}

/**
 * Save Search query filter
 *
 * @since 1.0
 *
 * @param  collection $people [object]
 *
 * @return collection
 */
function erp_crm_save_search_query_filter( $people ) {

    global $wpdb, $current_screen;

    $query_string = $_SERVER['QUERY_STRING'];

    $or_query   = explode( '&or&', $query_string );
    $page_id    = ( isset( $current_screen->base ) ) ? $current_screen->base : '';
    $allowed    = erp_crm_get_serach_key( $page_id );
    $query_data = [];
    $i          = 0;

    if ( $or_query ) {
        foreach( $or_query as $or_q ) {
            parse_str( $or_q, $output );
            $serach_array = array_intersect_key( $output, array_flip( array_keys( $allowed ) ) );
            $query_data[] = $serach_array;
        }
    }

    foreach( $query_data as $query_param ) {
        if ( $i == 0 ) {
            $people = $people->where( function( $query ) use( $query_param ) {
                foreach( $query_param as $key => $value ) {
                    if( is_array( $value ) ) {
                        $filter_value = erp_crm_get_save_search_regx( $value );
                        $j = 0;
                        if ( $key == 'country_state' ) {

                            $query->where( function( $query1 ) use( $filter_value, $j, $key ) {

                                foreach( $filter_value as $q_val => $q_key ) {
                                    if ( $j == 0 ) {
                                        $key_value = explode(':', $q_val); // seperate BAN:DHA to an array [ 0=>BAN, 1=>DHA]
                                        $keys = explode('_', $key); // seperate country_state to an array [ 0=>country, 1=>state]
                                        if ( count( $key_value ) > 1 ) {

                                            $query1->where( function($query2) use( $key_value, $keys, $q_key ){

                                                // Foreach those [ 0=>BAN, 1=>DHA ] value as nes_key = 0,1 and $nes_val = BAN,DHA
                                                foreach ( $key_value as $nes_key => $nes_val) {
                                                    $query2->where( $keys[$nes_key], $q_key, $nes_val );
                                                }
                                            });
                                            # code...
                                        } else {
                                            $query1->where( $keys[key( $key_value ) ], $q_key, $key_value[0] );
                                        }

                                    } else {
                                        $key_value = explode(':', $q_val); // seperate BAN:DHA to an array [ 0=>BAN, 1=>DHA]
                                        $keys = explode('_', $key); // seperate country_state to an array [ 0=>country, 1=>state]

                                        if ( count( $key_value ) > 1 ) {

                                            $query1->orWhere( function($query2) use( $key_value, $keys, $q_key ){

                                                // Foreach those [ 0=>BAN, 1=>DHA ] value as nes_key = 0,1 and $nes_val = BAN,DHA
                                                foreach ( $key_value as $nes_key => $nes_val) {
                                                    $query2->where( $keys[$nes_key], $q_key, $nes_val );
                                                }
                                            });
                                            # code...
                                        } else {
                                            $query1->orWhere( $keys[key( $key_value ) ], $q_key, $key_value[0] );
                                        }
                                    }
                                    $j++;
                                }
                            });
                        } else {
                            $query->where( function( $query1 ) use( $filter_value, $j, $key ) {
                                foreach( $filter_value as $q_val => $q_key ) {
                                    if ( $j == 0 ) {
                                        $query1->where( $key, $q_key, $q_val );
                                    } else {
                                        $query1->orWhere( $key, $q_key, $q_val );
                                    }
                                    $j++;
                                }
                            });
                        }
                    } else {
                        $filter_value = erp_crm_get_save_search_regx( $value );
                        $query->where( $key, $filter_value[key( $filter_value )], key( $filter_value ) );
                    }
                }
                return $query;
            } );
        } else {
            $people = $people->orWhere( function( $query ) use( $query_param ) {
            $filter_value = [];
                foreach( $query_param as $key => $value ) {
                    if( is_array( $value ) ) {
                        $filter_value = erp_crm_get_save_search_regx( $value );
                        $j = 0;
                        if ( $key == 'country_state' ) {
                            $query->where( function( $query1 ) use( $filter_value, $j, $key ) {
                                foreach( $filter_value as $q_val => $q_key ) {
                                    if ( $j == 0 ) {
                                        $key_value = explode(':', $q_val); // seperate BAN:DHA to an array [ 0=>BAN, 1=>DHA]
                                        $keys = explode('_', $key); // seperate country_state to an array [ 0=>country, 1=>state]
                                        if ( count( $key_value ) > 1 ) {

                                            $query1->where( function($query2) use( $key_value, $keys, $q_key ){

                                                // Foreach those [ 0=>BAN, 1=>DHA ] value as nes_key = 0,1 and $nes_val = BAN,DHA
                                                foreach ( $key_value as $nes_key => $nes_val) {
                                                    $query2->where( $keys[$nes_key], $q_key, $nes_val );
                                                }
                                            });
                                            # code...
                                        } else {
                                            $query1->where( $keys[key( $key_value ) ], $q_key, $key_value[0] );
                                        }

                                    } else {
                                        $key_value = explode(':', $q_val); // seperate BAN:DHA to an array [ 0=>BAN, 1=>DHA]
                                        $keys = explode('_', $key); // seperate country_state to an array [ 0=>country, 1=>state]

                                        if ( count( $key_value ) > 1 ) {

                                            $query1->orWhere( function($query2) use( $key_value, $keys, $q_key ){

                                                // Foreach those [ 0=>BAN, 1=>DHA ] value as nes_key = 0,1 and $nes_val = BAN,DHA
                                                foreach ( $key_value as $nes_key => $nes_val) {
                                                    $query2->where( $keys[$nes_key], $q_key, $nes_val );
                                                }
                                            });
                                            # code...
                                        } else {
                                            $query1->orWhere( $keys[key( $key_value ) ], $q_key, $key_value[0] );
                                        }
                                    }
                                    $j++;
                                }
                            });
                        } else {
                            $query->where( function( $query1 ) use( $filter_value, $j, $key ) {
                                foreach( $filter_value as $q_val => $q_key ) {
                                    if ( $j == 0 ) {
                                        $query1->where( $key, $q_key, $q_val );
                                    } else {
                                        $query1->orWhere( $key, $q_key, $q_val );
                                    }
                                    $j++;
                                }
                            });
                        }
                    } else {
                        $filter_value = erp_crm_get_save_search_regx( $value );
                        $query->where( $key, $filter_value[key( $filter_value )], key( $filter_value ) );
                    }
                }
                return $query;
            } );
        }
        $i++;
    }

    return $people;
}

/**
 * Get save serach query string
 *
 * @since 1.0
 *
 * @param  array $postdata
 *
 * @return string
 */
function erp_crm_get_save_search_query_string( $postdata ){

    $save_search          = ( isset( $postdata['save_search'] ) && !empty( $postdata['save_search'] ) ) ? $postdata['save_search'] : '';
    $search_string        = '';
    $search_string_arr    = [];

    if ( !$save_search ) {
        return $search_string;
    }

    foreach(  $save_search as $search_data ) {
        $search_pair = [];
        foreach( $search_data as $search_key=>$search_field ) {
            $values = array_map( function( $value ) use( $search_field ) {
                $condition = isset( $search_field['condition'] ) ? $search_field['condition'] : '';
                return $condition.$value;
            },  $search_field['value'] );

            $search_pair[$search_key] = $values;
        }
        $search_string[] = http_build_query( $search_pair );
    }

    $search_string = implode( '&or&', $search_string );

    return $search_string;
}

/**
 * Insert save search
 *
 * @since 1.0
 *
 * @param  array $data
 *
 * @return array
 */
function erp_insert_save_search( $data ) {
    if ( $data['id'] ) {
        $updated_item = WeDevs\ERP\CRM\Models\SaveSearch::find( $data['id'] );
        $updated_item->update( $data );
        return $updated_item;
    } else {
        return WeDevs\ERP\CRM\Models\SaveSearch::create( $data );
    }
}

/**
 * Get save search Item
 *
 * @since 1.0
 *
 * @param  array  $args
 *
 * @return array
 */
function erp_get_save_search_item( $args = [] ) {

    $defaults = [
        'id'      => 0,
        'user_id' => get_current_user_id(),
        'global' => 1,
        'groupby' => 'global'
    ];

    $args  = wp_parse_args( $args, $defaults );

    if ( $args['id'] ) {
        return WeDevs\ERP\CRM\Models\SaveSearch::find( $args['id'] )->toArray();
    }

    $results = [];
    $search_keys = WeDevs\ERP\CRM\Models\SaveSearch::where( 'user_id', '=',  $args['user_id'] )
                ->orWhere('global', '=', $args['global']  )
                ->get()
                ->groupBy( $args['groupby'] )
                ->toArray();

    foreach ( $search_keys as $key => $search_values ) {
        if ( $key == 0 ) {
            $results[$key]['name'] = __( 'Own Search', 'wp-erp' );
        } else {
            $results[$key]['name'] = __( 'Global Search', 'wp-erp' );
        }

        foreach ( $search_values as $index => $value ) {
            $results[$key]['options'][] = [
                'id' => $value['id'],
                'text' => $value['search_name'],
            ];
        }
    }

    return $results;
}

/**
 * Delete Save search
 *
 * @since 1.0
 *
 * @param  integer $id
 *
 * @return boolean
 */
function erp_delete_save_search_item( $id ) {
    return WeDevs\ERP\CRM\Models\SaveSearch::find( $id )->delete();
}

/**
 * Get save Search query string for db;
 *
 * @since 1.0
 *
 * @param  integer $save_search_id
 *
 * @return string
 */
function erp_crm_get_search_by_already_saved( $save_search_id ) {
    if ( ! $save_search_id ) {
        return '';
    }

    $data = WeDevs\ERP\CRM\Models\SaveSearch::find( $save_search_id );

    return $data->search_val;
}

/**
 * Get todays schedules activities
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_todays_schedules_activity( $user_id = '' ) {
    $results  = [];
    $db       = new \WeDevs\ORM\Eloquent\Database();
    $activity = new WeDevs\ERP\CRM\Models\Activity();

    $res = \WeDevs\ERP\CRM\Models\Activity::with( 'contact' )->where( 'type', '=', 'log_activity' )
            ->where( 'created_by', $user_id )
            ->where( $db->raw("DATE_FORMAT( `start_date`, '%Y %m %d' )" ), \Carbon\Carbon::today()->format('Y m d') )
            ->take(7)
            ->get()
            ->toArray();

    foreach( $res as $key=>$result ) {
        $results[$key] = $result;
        $results[$key]['extra'] = json_decode( base64_decode( $result['extra'] ), true );
    }

    return $results;
}

/**
 * Get todays schedules activities
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_get_next_seven_day_schedules_activities( $user_id = '' ) {
    global $wpdb;
    $results  = [];
    $db       = new \WeDevs\ORM\Eloquent\Database();
    $activity = new WeDevs\ERP\CRM\Models\Activity();

    $res = \WeDevs\ERP\CRM\Models\Activity::with( 'contact' )->where( 'type', '=', 'log_activity' )
            ->where( 'created_by', $user_id )
            ->where( $db->raw("DATE_FORMAT( `start_date`, '%Y %m %d' )" ), '>=', \Carbon\Carbon::tomorrow()->format('Y m d') )
            ->where( $db->raw("DATE_FORMAT( `start_date`, '%Y %m %d' )" ), '<=',\Carbon\Carbon::tomorrow()->addDays(7)->format('Y m d') )
            ->take(7)
            ->get()
            ->toArray();

    foreach( $res as $key=>$result ) {
        $results[$key] = $result;
        $results[$key]['extra'] = json_decode( base64_decode( $result['extra'] ), true );
    }

    return $results;
}

/**
 * Fetch & Save email activity from api server.
 *
 * @return void
 */
function erp_crm_save_email_activity() {
    header('Access-Control-Allow-Origin: *');

    $postdata   = $_POST;
    $api_key    = get_option( 'wp_erp_apikey' );

    $is_verified = erp_cloud_verify_request( $postdata, $api_key  );

    if ( $is_verified ) {
        unset($postdata['action']);

        $save_data = [
            'user_id'       => $postdata['user_id'],
            'created_by'    => $postdata['created_by'],
            'message'       => $postdata['message'],
            'type'          => $postdata['type'],
            'email_subject' => $postdata['email_subject'],
            'extra'         => serialize( [ 'replied' => 1 ] ),
        ];

        $data = erp_crm_save_customer_feed_data( $save_data );

        // Update email counter
        update_option( 'wp_erp_cloud_email_count', get_option( 'wp_erp_cloud_email_count', 0 ) + 1 );

        do_action( 'erp_crm_save_customer_email_feed', $save_data, $postdata );
    }
}
