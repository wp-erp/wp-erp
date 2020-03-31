<?php
/**
 * CRM related necessary helper function
 */

/**
 * Get an avatar
 *
 * @param  integer  avatar size in pixels
 *
 * @return string  image with HTML tag
 */
function erp_crm_get_avatar( $id, $email = '', $user_id = 0, $size = 32 ) {

    if ( $id ) {

        $user_photo_id = ( $user_id ) ? get_user_meta( $user_id, 'photo_id', true ) : erp_people_get_meta( $id, 'photo_id', true );

        if ( ! empty( $user_photo_id ) ) {
            $image = wp_get_attachment_thumb_url( $user_photo_id );

            return sprintf( '<img src="%1$s" alt="" class="avatar avatar-%2$s photo" height="auto" width="%2$s" />', $image, $size );
        }
    }

    $avatar = get_avatar( $email, $size );

    if ( ! $avatar ) {
        $image  = WPERP_ASSETS . '/images/mystery-person.png';
        $avatar = sprintf( '<img src="%1$s" alt="" class="avatar avatar-%2$s photo" height="auto" width="%2$s" />', $image, $size );
    }

    return $avatar;
}

/**
 * Get an avatar url for people
 *
 * @param  integer  avatar size in pixels
 *
 * @return string  image with HTML tag
 */
function erp_crm_get_avatar_url( $id, $email = '', $user_id = 0, $size = 32 ) {

    $user_photo_id = ( $user_id ) ? get_user_meta( $user_id, 'photo_id', true ) : erp_people_get_meta( $id, 'photo_id', true );

    if ( $id ) {

        if ( ! empty( $user_photo_id ) ) {
            return wp_get_attachment_thumb_url( $user_photo_id );
        }
    }

    if ( ! $email ) {
        return WPERP_ASSETS . '/images/mystery-person.png';
    }

    return get_avatar_url( $email, $size );
}

/**
 * Get employees in CRM
 *
 * @since 1.0
 *
 * @param  array $args
 *
 * @return object
 */
function erp_crm_get_employees( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'    => 20,
        'offset'    => 0,
        'orderby'   => 'hiring_date',
        'order'     => 'DESC',
        'no_object' => false,
        'status'    => 'active'
    ];

    $args  = wp_parse_args( $args, $defaults );
    $where = array();

    $employee        = new \WeDevs\ERP\HRM\Models\Employee();
    $employee_result = $employee->leftjoin( $wpdb->users, 'user_id', '=', $wpdb->users . '.ID' )->select( array(
        'user_id',
        'display_name'
    ) );

    $cache_key = 'erp-crm-get-employees-' . md5( serialize( $args ) );
    $results   = wp_cache_get( $cache_key, 'erp' );
    $users     = array();

    $employee_result = $employee_result->where( 'status', $args['status'] );

    // Check if want all data without any pagination
    if ( $args['number'] != '-1' ) {
        $employee_result = $employee_result->skip( $args['offset'] )->take( $args['number'] );
    }

    if ( false === $results ) {
        $results = $employee_result
            ->orderBy( $args['orderby'], $args['order'] )
            ->get()
            ->toArray();

        $results = erp_array_to_object( $results );
        wp_cache_set( $cache_key, $results, 'erp', HOUR_IN_SECONDS );
    }

    if ( $results ) {
        foreach ( $results as $key => $row ) {

            if ( true === $args['no_object'] ) {
                $users[] = $row;
            } else {
                $users[] = new \WeDevs\ERP\HRM\Employee( intval( $row->user_id ) );
            }
        }
    }

    return $users;
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
function erp_crm_get_crm_user_html_dropdown( $selected = '' ) {
    $crm_users = erp_crm_get_crm_user();
    $dropdown  = '';

    if ( $crm_users ) {
        foreach ( $crm_users as $key => $user ) {
            if ( $user->ID == get_current_user_id() ) {
                $title = sprintf( '%s ( %s )', __( 'Me', 'erp' ), $user->display_name );
            } else {
                $title = $user->display_name;
            }

            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $user->ID, selected( $selected, $user->ID, false ), $title );
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

        if ( in_array( 'contact', $type ) ) {
            return add_query_arg( ['page' => 'erp-crm', 'section' => 'contacts', 'action' => 'view' , 'id' => $id ], admin_url('admin.php') );
        }

        if ( in_array( 'company', $type ) ) {
            return add_query_arg( ['page' => 'erp-crm', 'section' => 'companies', 'action' => 'view' , 'id' => $id ], admin_url('admin.php') );
        }
    }

    return admin_url( 'admin.php' );
}

/**
 * Get CRM life statges
 *
 * @since 1.0
 * @since 1.1.16 Append extra `label` after the filter applied
 *
 * @param array $label
 *
 * @return array
 */
function erp_crm_get_life_stages_dropdown_raw( $label = [], $counts = [] ) {

    $counts = wp_parse_args( $counts, [
        'customer'    => 0,
        'lead'        => 0,
        'opportunity' => 0,
        'subscriber'  => 0
    ] );

    $life_stages = [
        'customer'    => _n( 'Customer', 'Customers', $counts['customer'], 'erp' ),
        'lead'        => _n( 'Lead', 'Leads', $counts['lead'], 'erp' ),
        'opportunity' => _n( 'Opportunity', 'Opportunities',  $counts['opportunity'], 'erp' ),
        'subscriber'  => _n( 'Subscriber', 'Subscribers', $counts['subscriber'], 'erp' )
    ];

    $life_stages = apply_filters( 'erp_crm_life_stages', $life_stages );

    if ( $label ) {
        $life_stages = $label + $life_stages;
    }

    return $life_stages;
}

/**
 * Get customer type
 *
 * @since 1.0
 *
 * @param  array $label
 *
 * @return array
 */
function erp_crm_get_customer_type( $label = [] ) {

    $type = [
        'customer' => __( 'Customer', 'erp' ),
        'company'  => __( 'Company', 'erp' ),
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
function erp_crm_get_life_stages_dropdown( $label = [], $selected = '' ) {

    $life_stages = erp_crm_get_life_stages_dropdown_raw( $label );
    $dropdown    = '';

    if ( $life_stages ) {
        foreach ( $life_stages as $key => $title ) {
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
 * @param  array $label
 *
 * @return array | list of all contact with copmany
 */
function erp_crm_get_contact_dropdown( $label = [] ) {
    $contacts = erp_get_peoples( [ 'number' => '-1', 'type' => [ 'contact', 'company' ] ] );
    $list     = [];

    foreach ( $contacts as $key => $contact ) {
        $contact_obj              = new \WeDevs\ERP\CRM\Contact( intval( $contact->id ) );
        $list[ $contact_obj->id ] = $contact_obj->get_full_name() . '( ' . $contact_obj->get_email() . ' ) ';
    }

    if ( $label ) {
        $list = $label + $list;
    }

    return $list;
}

/**
 * Get customer life statges status count
 *
 * @since 1.0
 * @since 1.2.2 Change query to fix count for `All` status
 *
 * @return array
 */
function erp_crm_customer_get_status_count( $type = null ) {
    global $wpdb;

    $cache_key = 'erp-crm-customer-status-counts-' . $type;
    $results   = wp_cache_get( $cache_key, 'erp' );

    if ( false == $results ) {
        $people_tbl = $wpdb->prefix . 'erp_peoples';
        $rel_tbl    = $wpdb->prefix . 'erp_people_type_relations';
        $type_tbl   = $wpdb->prefix . 'erp_people_types';

        $sql     = "select life_stage as status, count(*) as count"
                   . " from {$people_tbl}"
                   . " left join {$rel_tbl} on {$people_tbl}.id = {$rel_tbl}.people_id"
                   . " left join {$type_tbl} on {$rel_tbl}.people_types_id = {$type_tbl}.id"
                   . " WHERE {$type_tbl}.name = %s AND {$rel_tbl}.deleted_at IS NULL";

        if ( current_user_can( 'erp_crm_agent' ) ) {
            $current_user_id = get_current_user_id();
            $sql .= " AND {$people_tbl}.contact_owner = {$current_user_id}";
        }
        $sql    .= " group by life_stage";
        $results = $wpdb->get_results( $wpdb->prepare( $sql, $type ) );

        wp_cache_set( $cache_key, $results, 'erp' );
    }

    $status_counts = wp_list_pluck( $results, 'count', 'status' );

    $statuses = erp_crm_get_life_stages_dropdown_raw( [ 'all' => __( 'All', 'erp' ) ], $status_counts );

    $counts = array();

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    foreach ( $results as $result ) {
        $count = absint( $result->count );

        if ( array_key_exists( $result->status, $counts ) ) {
            $counts[ $result->status ]['count'] = $count;
        }

        $counts['all']['count'] += $count;
    }

    $counts['trash'] = [
        'count' => erp_crm_count_trashed_customers( $type ),
        'label' => __( 'Trash', 'erp' )
    ];

    return $counts;
}

/**
 * Count trash customer
 *
 * @since 1.0
 *
 * @return integer [no of trash customer]
 */
function erp_crm_count_trashed_customers( $type = null ) {
    return \WeDevs\ERP\Framework\Models\People::trashed( $type )->count();
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
    ) );
}

/**
 * Get all the companies for a single customer
 *
 * @since 1.1.0
 *
 * @param $postdata array
 *
 * @return array
 */
function erp_crm_customer_get_company( $postdata ) {
    global $wpdb;
    $results = [];

    if ( isset( $postdata['id'] ) && empty( $postdata['id'] ) ) {
        return new WP_Error( 'no-ids', __( 'No contact found', 'erp' ) );
    }

    $sql = "SELECT com.* FROM " . $wpdb->prefix . "erp_crm_customer_companies AS com
            LEFT JOIN " . $wpdb->prefix . "erp_peoples AS peop ON peop.id = com.company_id
            WHERE com.customer_id = " . $postdata['id'];

    $data = $wpdb->get_results( $sql, ARRAY_A );

    if ( $data ) {
        foreach ( $data as $key => $value ) {
            $company                                       = new \WeDevs\ERP\CRM\Contact( intval( $value['company_id'] ) );
            $results[ $key ]                               = $value;
            $results[ $key ]['contact_details']            = $company->to_array();
            $country                                       = $results[ $key ]['contact_details']['country'];
            $results[ $key ]['contact_details']['country'] = erp_get_country_name( $country );
            $results[ $key ]['contact_details']['state']   = erp_get_state_name( $country, $results[ $key ]['contact_details']['state'] );
        }
    }

    return $results;
}

/**
 * Get all the companies for a single customer
 *
 * @since 1.0
 *
 * @param array $postdata
 *
 * @return array
 */
function erp_crm_company_get_customers( $postdata ) {
    global $wpdb;
    $results = [];

    if ( isset( $postdata['id'] ) && empty( $postdata['id'] ) ) {
        return new WP_Error( 'no-ids', __( 'No comapany found', 'erp' ) );
    }

    $sql = "SELECT  com.* FROM " . $wpdb->prefix . "erp_crm_customer_companies AS com
            LEFT JOIN " . $wpdb->prefix . "erp_peoples AS peop ON peop.id = com.customer_id
            WHERE com.company_id = " . $postdata['id'];

    $data = $wpdb->get_results( $sql, ARRAY_A );

    if ( $data ) {
        foreach ( $data as $key => $value ) {
            $customer                                      = new \WeDevs\ERP\CRM\Contact( intval( $value['customer_id'] ) );
            $results[ $key ]                               = $value;
            $results[ $key ]['contact_details']            = $customer->to_array();
            $country                                       = $results[ $key ]['contact_details']['country'];
            $results[ $key ]['contact_details']['country'] = erp_get_country_name( $country );
            $results[ $key ]['contact_details']['state']   = erp_get_state_name( $country, $results[ $key ]['contact_details']['state'] );
        }
    }

    return $results;
}

/**
 * Get contact details url
 *
 * @since 1.0
 *
 * @param  integer $id
 *
 * @return string admin url
 */
function erp_crm_get_customer_details_url( $id ) {
    return add_query_arg( ['page' => 'erp-crm', 'section' => 'contacts', 'action' => 'view' , 'id' => $id ], admin_url('admin.php') );
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
    $wpdb->update( $wpdb->prefix . "erp_crm_customer_companies", [ 'company_id' => $company_id ], [ 'id' => $row_id ] );
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
    $wpdb->delete( $wpdb->prefix . 'erp_crm_customer_companies', [ 'id' => $id ] );
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
            'title' => __( 'Facebook', 'erp' ),
            'icon'  => '<i class="fa fa-facebook-square"></i>',
        ],

        'twitter' => [
            'title' => __( 'Twitter', 'erp' ),
            'icon'  => '<i class="fa fa-twitter-square"></i>',
        ],

        'googleplus' => [
            'title' => __( 'Google Plus', 'erp' ),
            'icon'  => '<i class="fa fa-google-plus-square"></i>',
        ],

        'linkedin' => [
            'title' => __( 'Linkedin', 'erp' ),
            'icon'  => '<i class="fa fa-linkedin-square"></i>',
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
            'title' => __( 'New Note', 'erp' ),
            'icon'  => '<i class="fa fa-file-text-o"></i>'
        ],

        'email' => [
            'title' => __( 'Email', 'erp' ),
            'icon'  => '<i class="fa fa-envelope-o"></i>'
        ],

        'log_activity' => [
            'title' => __( 'Log Activity', 'erp' ),
            'icon'  => '<i class="fa fa-list"></i>'
        ],

        'schedule' => [
            'title' => __( 'Schedule', 'erp' ),
            'icon'  => '<i class="fa fa-calendar-check-o"></i>'
        ],

        'tasks' => [
            'title' => __( 'Tasks', 'erp' ),
            'icon'  => '<i class="fa fa-check-square-o"></i>'
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
    if ( empty( $postdata ) ) {
        return;
    }

    $extra_data = [
        'schedule_title'     => ( isset( $postdata['schedule_title'] ) && ! empty( $postdata['schedule_title'] ) ) ? $postdata['schedule_title'] : '',
        'all_day'            => isset( $postdata['all_day'] ) ? (string) $postdata['all_day'] : 'false',
        'allow_notification' => isset( $postdata['allow_notification'] ) ? (string) $postdata['allow_notification'] : 'false',
        'invite_contact'     => ( isset( $postdata['invite_contact'] ) && ! empty( $postdata['invite_contact'] ) ) ? $postdata['invite_contact'] : []
    ];

    $extra_data['notification_via']           = ( isset( $postdata['notification_via'] ) && $extra_data['allow_notification'] == 'true' ) ? $postdata['notification_via'] : '';
    $extra_data['notification_time']          = ( isset( $postdata['notification_time'] ) && $extra_data['allow_notification'] == 'true' ) ? $postdata['notification_time'] : '';
    $extra_data['notification_time_interval'] = ( isset( $postdata['notification_time_interval'] ) && $extra_data['allow_notification'] == 'true' ) ? $postdata['notification_time_interval'] : '';

    $start_time = ( isset( $postdata['start_time'] ) && $extra_data['all_day'] == 'false' ) ? $postdata['start_time'] : '00:00:00';
    $end_time   = ( isset( $postdata['end_time'] ) && $extra_data['all_day'] == 'false' ) ? $postdata['end_time'] : '00:00:00';

    if ( $extra_data['allow_notification'] == 'true' ) {
        $notify_date = new \DateTime( $postdata['start_date'] . $start_time );
        $notify_date->modify( '-' . $extra_data['notification_time_interval'] . ' ' . $extra_data['notification_time'] );
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
        'log_type'   => ( isset( $postdata['schedule_type'] ) && ! empty( $postdata['schedule_type'] ) ) ? $postdata['schedule_type'] : '',
        'start_date' => date( 'Y-m-d H:i:s', strtotime( $postdata['start_date'] . $start_time ) ),
        'end_date'   => date( 'Y-m-d H:i:s', strtotime( $postdata['end_date'] . $end_time ) ),
        'extra'      => base64_encode( json_encode( $extra_data ) )
    ];

    return $save_data;
}

/**
 * Format activity feeds message when feed display in activity streams
 *
 * @since 1.1.2
 *
 * @param  string $message
 * @param  array $activity
 *
 * @return string
 */
function erp_crm_format_activity_feed_message( $message, $activity ) {
    return apply_filters( 'erp_crm_format_activity_feed_message', stripslashes( $message ), $activity );
}

/**
 * Get all customer feeds
 *
 * @since 1.0
 * @since 1.1.13 Add activity 'type' filtering
 *               For tasks type activity return activities depends on assgined to users
 *
 * @param  integer $customer_id
 *
 * @return array
 */
function erp_crm_get_feed_activity( $postdata ) {
    global $wpdb;
    $feeds = [];
    $db    = new \WeDevs\ORM\Eloquent\Database();

    $results = \WeDevs\ERP\CRM\Models\Activity::select( [
        '*',
        $db->raw( 'MONTHNAME(`created_at`) as feed_month, YEAR( `created_at` ) as feed_year' )
    ] )
      ->with( [
          'contact'    => function ( $query ) {
              $query->with( 'types' );
          },
          'created_by' => function ( $query1 ) {
              $query1->select( 'ID', 'user_nicename', 'user_email', 'user_url', 'display_name' );
          }
      ] );

    if ( isset( $postdata['customer_id'] ) && ! empty( $postdata['customer_id'] ) ) {
        $results = $results->where( 'user_id', $postdata['customer_id'] );
    }

    if ( current_user_can( 'erp_crm_agent' ) ) {
        $contact_owner = get_current_user_id();
        $people_ids = array_keys( $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}erp_peoples WHERE contact_owner = {$contact_owner}", OBJECT_K ) );

        $results = $results->whereIn( 'user_id', $people_ids );
    }

    if ( isset( $postdata['created_by'] ) && ! empty( $postdata['created_by'] ) ) {
        $results = $results->where( 'created_by', $postdata['created_by'] );
    }

    if ( isset( $postdata['type'] ) && ! empty( $postdata['type'] ) ) {

        if ( $postdata['type'] == 'schedule' ) {
            $results = $results->where( 'type', 'log_activity' )->where( 'start_date', '>', current_time( 'mysql' ) );
        } else if ( $postdata['type'] == 'logs' ) {
            $results = $results->where( 'type', 'log_activity' )->where( 'start_date', '<', current_time( 'mysql' ) );
        } else {
            if ( is_array( $postdata['type'] ) ) {
                $results = $results->whereIn( 'type', $postdata['type'] );
            } else {
                $results = $results->where( 'type', $postdata['type'] );
            }
        }
    }

    if ( isset( $postdata['created_at'] ) && ! empty( $postdata['created_at'] ) ) {
        $results = $results->where( $db->raw( "DATE_FORMAT( `created_at`, '%Y-%m-%d' )" ), $postdata['created_at'] );
    }

    $results = $results->orderBy( 'created_at', 'DESC' );

    if ( isset( $postdata['limit'] ) && $postdata['limit'] != - 1 ) {
        $results = $results->skip( $postdata['offset'] )->take( $postdata['limit'] );
    }

    if ( isset( $postdata['count'] ) && $postdata['count'] ) {
        return $results->count();
    }

    $results = $results->get()->toArray();

    foreach ( $results as $key => $value ) {
        $value['extra'] = json_decode( base64_decode( $value['extra'] ), true );

        if ( isset( $value['extra']['invite_contact'] ) && ! empty( $postdata['assigned_to'] ) ) {
            if ( ! in_array( $postdata['assigned_to'], $value['extra']['invite_contact'] ) ) {
                continue;
            }
        }

        if ( isset( $value['extra']['invite_contact'] ) && count( $value['extra']['invite_contact'] ) > 0 ) {
            foreach ( $value['extra']['invite_contact'] as $user_id ) {
                $value['extra']['invited_user'][] = [
                    'id'   => $user_id,
                    'name' => get_the_author_meta( 'display_name', $user_id )
                ];
            }
        } else {
            $value['extra']['invited_user'] = [];
        }

        if ( $value['contact']['user_id'] ) {
            $value['contact']['first_name'] = get_user_meta( $value['contact']['user_id'], 'first_name', true );
            $value['contact']['last_name']  = get_user_meta( $value['contact']['user_id'], 'last_name', true );
        }

        if ( ! empty( $value['contact']['types'] ) ) {
            $value['contact']['types'] = wp_list_pluck( $value['contact']['types'], 'name' );
        } else {
            $value['contact']['types'] = [];
        }

        if ( isset( $value['extra']['attachments'] ) ) {
            $value['extra']['attachments'] = erp_crm_process_attachment_data( $value['extra']['attachments'] );
        }

        unset( $value['extra']['invite_contact'] );
        $value['message']               = erp_crm_format_activity_feed_message( $value['message'], $value );
        $value['created_by']['avatar']  = get_avatar_url( $value['created_by']['ID'] );
        $value['created_date']          = date( 'Y-m-d', strtotime( $value['created_at'] ) );
        $value['created_timeline_date'] = date( 'Y-m-01', strtotime( $value['created_at'] ) );
        // $value['component'] = 'timeline-item';
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

    if ( isset( $data['id'] ) && ! empty( $data['id'] ) ) {
        $saved_activity    = WeDevs\ERP\CRM\Models\Activity::find( $data['id'] )->update( $data );
        $saved_activity_id = $data['id'];
    } else {
        $saved_activity    = WeDevs\ERP\CRM\Models\Activity::create( $data );
        $saved_activity_id = $saved_activity->id;
    }

    $activity = WeDevs\ERP\CRM\Models\Activity::
    with( [
        'contact'    => function ( $query ) {
            $query->with( 'types' );
        },
        'created_by' => function ( $query ) {
            $query->select( 'ID', 'user_nicename', 'user_email', 'user_url', 'display_name' );
        }
    ] )
                                              ->find( $saved_activity_id )
                                              ->toArray();

    $activity['extra'] = json_decode( base64_decode( $activity['extra'] ), true );

    if ( isset( $activity['extra']['invite_contact'] ) && count( $activity['extra']['invite_contact'] ) > 0 ) {
        foreach ( $activity['extra']['invite_contact'] as $user_id ) {
            $activity['extra']['invited_user'][] = [
                'id'   => $user_id,
                'name' => get_the_author_meta( 'display_name', $user_id )
            ];
        }
    } else {
        $activity['extra']['invited_user'] = [];
    }

    if ( $activity['contact']['user_id'] ) {
        $activity['contact']['first_name'] = get_user_meta( $activity['contact']['user_id'], 'first_name', true );
        $activity['contact']['last_name']  = get_user_meta( $activity['contact']['user_id'], 'last_name', true );
    }

    unset( $activity['extra']['invite_contact'] );

    $activity['contact']['types']      = wp_list_pluck( $activity['contact']['types'], 'name' );
    $activity['message']               = erp_crm_format_activity_feed_message( $activity['message'], $activity );
    $activity['created_by']['avatar']  = get_avatar_url( $activity['created_by']['ID'] );
    $activity['created_date']          = date( 'Y-m-d', strtotime( $activity['created_at'] ) );
    $activity['created_timeline_date'] = date( 'Y-m-01', strtotime( $activity['created_at'] ) );

    if ( isset( $activity['extra']['attachments'] ) ) {
        $activity['extra']['attachments'] = erp_crm_process_attachment_data( $activity['extra']['attachments'] );
    }

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
    $data    = WeDevs\ERP\CRM\Models\Activity::with( [
        'contact'    => function ( $query ) {
            $query->with( 'types' );
        },
        'created_by' => function ( $query1 ) {
            $query1->select( 'ID', 'user_nicename', 'user_email', 'user_url', 'display_name' );
        }
    ] )
                                             ->find( $feed_id )->toArray();

    if ( ! $data ) {
        return;
    }

    $data['extra'] = json_decode( base64_decode( $data['extra'] ), true );

    if ( isset( $data['extra']['invite_contact'] ) && count( $data['extra']['invite_contact'] ) > 0 ) {
        foreach ( $data['extra']['invite_contact'] as $user_id ) {
            $data['extra']['invited_user'][] = [
                'id'   => $user_id,
                'name' => get_the_author_meta( 'display_name', $user_id )
            ];
        }
    } else {
        $data['extra']['invited_user'] = [];
    }

    if ( $data['contact']['user_id'] ) {
        $data['contact']['first_name'] = get_user_meta( $data['contact']['user_id'], 'first_name', true );
        $data['contact']['last_name']  = get_user_meta( $data['contact']['user_id'], 'last_name', true );
    }

    $data['contact']['types'] = wp_list_pluck( $data['contact']['types'], 'name' );
    $data['message']          = stripslashes( $data['message'] );

    if ( isset( $data['extra']['attachments'] ) ) {
        $data['extra']['attachments'] = erp_crm_process_attachment_data( $data['extra']['attachments'] );
    }

    return $data;
}

/**
 * Process attachment data to generate URL
 *
 * @param $attachments
 *
 * @return mixed
 */
function erp_crm_process_attachment_data( $attachments ) {
    $subdir      = apply_filters( 'crm_attachmet_directory', 'crm-attachments' );
    $upload_dir  = wp_upload_dir();
    foreach ( $attachments as $key => $item ) {
        $attachments[$key]['url'] = $upload_dir['baseurl'] . '/' . $subdir . '/' . $item['slug'];
    }

    return $attachments;
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
    $activity = WeDevs\ERP\CRM\Models\Activity::find( $feed_id );

    if ( $activity->type == 'tasks' ) {
        WeDevs\ERP\CRM\Models\ActivityUser::where( 'activity_id', $activity->id )->delete();
    }

    return $activity->delete( $feed_id );
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

    if ( empty( $schedules ) ) {
        return;
    }

    foreach ( $schedules as $key => $activity ) {
        $extra = json_decode( base64_decode( $activity['extra'] ), true );
        if ( isset ( $extra['allow_notification'] ) && $extra['allow_notification'] == 'true' ) {
            if ( ( current_time( 'mysql' ) >= $extra['notification_datetime'] ) && ( $activity['start_date'] >= current_time( 'mysql' ) ) ) {
                if ( ! $activity['sent_notification'] ) {
                    erp_crm_send_schedule_notification( $activity, $extra );
                }
            }
        }
    }
}

/**
 * Sending Customer schedule notification
 *
 * @since 1.0
 *
 * @param  object $activity
 * @param  boolean $extra
 *
 * @return void
 */
function erp_crm_send_schedule_notification( $activity, $extra = false ) {
    if ( ! $extra ) {
        return;
    }

    switch ( $extra['notification_via'] ) {
        case 'email':
            $users = [];

            foreach ( $extra['invite_contact'] as $contact ) {
                $users[] = get_the_author_meta( 'user_email', $contact );
            }

            $created_user = get_the_author_meta( 'user_email', $activity['created_by'] );
            array_push( $users, $created_user );

            foreach ( $users as $key => $user ) {
                $body = sprintf( __( 'You have a schedule after %s %s at %s', 'erp' ), $extra['notification_time_interval'], $extra['notification_time'], date( 'F j, Y, g:i a', strtotime( $activity['start_date'] ) ) );
                erp_mail( $user, __( 'ERP Schedule', 'erp' ), $body );
            }
            erp_crm_update_schedule_notification_flag( $activity['id'], true );
            break;

        default:
            do_action( 'erp_crm_send_schedule_notification', $activity, $extra );
            break;
    }
}

/**
 * Update notification flag in customer activity feeds
 *
 * @since 1.1.1
 *
 * @param  integer $activity_id
 * @param  boolean $flag
 *
 * @return void
 */
function erp_crm_update_schedule_notification_flag( $activity_id, $flag ) {
    if ( ! $activity_id ) {
        return;
    }
    \WeDevs\ERP\CRM\Models\Activity::find( $activity_id )->update( [ 'sent_notification' => $flag ] );
}

/**
 * Assign task to user
 *
 * When task is created from activity
 * feeds, user needs to see their task. This function
 * data map with task activity and assign users
 *
 * @since 1.0
 *
 * @param  array $data
 *
 * @return void
 */
function erp_crm_assign_task_to_users( $data, $save_data ) {
    if ( $save_data['id'] ) {
        \WeDevs\ERP\CRM\Models\ActivityUser::where( 'activity_id', $save_data['id'] )->delete();
    }

    $user_ids = [];

    if ( isset( $data['extra']['invited_user'] ) && count( $data['extra']['invited_user'] ) > 0 ) {
        foreach ( $data['extra']['invited_user'] as $key => $user ) {
            $res = \WeDevs\ERP\CRM\Models\ActivityUser::create( [
                'activity_id' => $data['id'],
                'user_id'     => $user['id']
            ] );

            $user_ids[] = $user['id'];

            do_action( 'erp_crm_after_assign_task_to_user', $data, $save_data );
        }

        $assigned_task = wperp()->emailer->get_email( 'New_Task_Assigned' );

        if ( is_a( $assigned_task, '\WeDevs\ERP\Email' ) ) {
            $assigned_task->trigger( [ 'activity_id' => $data['id'], 'user_ids' => $user_ids ] );
        }
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
    if ( ! empty ( $data['id'] ) ) {
        $result = WeDevs\ERP\CRM\Models\ContactGroup::find( $data['id'] )->update( $data );
	    do_action( 'erp_crm_update_contact_group', $result );
    } else {
        $result = WeDevs\ERP\CRM\Models\ContactGroup::create( $data );
	    do_action( 'erp_crm_create_contact_group', $result );
    }

    return $result;
}

/**
 * Get all contact group
 *
 * @since 1.0
 * @since 1.2.0 Add `unconfirmed` count
 *
 * @return object
 */
function erp_crm_get_contact_groups( $args = [] ) {

    $defaults  = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
    ];
    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'erp-crm-contact-group-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        // Check if args count true, then return total count customer according to above filter
        if ( $args['count'] ) {
            $result = WeDevs\ERP\CRM\Models\ContactGroup::count();
            wp_cache_set( $cache_key, $result, 'erp' );

            return $result;
        }

        $results       = [];
        $contact_group = new WeDevs\ERP\CRM\Models\ContactGroup();

        $contact_group = $contact_group->with( 'contact_subscriber' );

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' && ! $args['count'] ) {
            $contact_group = $contact_group->skip( $args['offset'] )->take( $args['number'] );
        }

        // Check is the row want to search
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s         = $args['s'];
            $contact_group = $contact_group->where( 'name', 'LIKE', "%$arg_s%" )
                                           ->orWhere( 'description', 'LIKE', "%$arg_s%" );
        }

        // Render all collection of data according to above filter (Main query)
        $results = $contact_group->orderBy( $args['orderby'], $args['order'] )
                                 ->get()
                                 ->toArray();

        foreach ( $results as $key => $group ) {

            if ( ! current_user_can( 'erp_crm_create_groups' ) ) {
                $contact_subscriber = $group['contact_subscriber'];
                $agent_subscriber   = [];
                foreach ( $contact_subscriber as $cs ) {
                    $obj = erp_get_people( $cs['user_id'] );
                    if ( $obj->contact_owner == get_current_user_id() ) {
                        $agent_subscriber[] = $cs;
                    }
                }
                $group['contact_subscriber'] = $agent_subscriber;
            }

            $subscribers = array_filter( $group['contact_subscriber'], function ( $subscriber ) {
                return 'subscribe' === $subscriber['status'];
            } );

            $unconfirmed = array_filter( $group['contact_subscriber'], function ( $subscriber ) {
                return 'unconfirmed' === $subscriber['status'];
            } );

            $unsubscribers = array_filter( $group['contact_subscriber'], function ( $subscriber ) {
                return $subscriber['unsubscribe_at'];
            } );

            unset( $group['contact_subscriber'] );

            $items[ $key ]                 = $group;
            $items[ $key ]['subscriber']   = count( $subscribers );
            $items[ $key ]['unconfirmed']  = count( $unconfirmed );
            $items[ $key ]['unsubscriber'] = count( $unsubscribers );
        }

        $items = erp_array_to_object( $items );


        wp_cache_set( $cache_key, $items, 'erp' );
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
 * @since 1.0.0
 *
 * @param $id
 *
 * @throws \Exception
 */
function erp_crm_contact_group_delete( $id ) {
    if ( is_array( $id ) ) {
        WeDevs\ERP\CRM\Models\ContactGroup::destroy( $id );
    } else {
        WeDevs\ERP\CRM\Models\ContactGroup::find( $id )->delete();
    }

	do_action( 'erp_crm_delete_contact_group', $id );
}

/**
 * Get subscriber contact
 *
 * @since 1.0
 *
 * @param  array $args
 *
 * @return array|object
 */
function erp_crm_get_subscriber_contact( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
    ];

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'erp-crm-subscriber-contact-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $converted_data       = [];
        $contact_subscribe_tb = $wpdb->prefix . 'erp_crm_contact_subscriber';
        $contact_group_tb     = $wpdb->prefix . 'erp_crm_contact_group';
        $contact_tags         = $wpdb->prefix . 'erp_crm_contact_tag';

        $contact_subscribers = WeDevs\ERP\CRM\Models\ContactSubscriber::leftjoin( $contact_group_tb, $contact_group_tb . '.id', '=', $contact_subscribe_tb . '.group_id' );

//        $contact_subscribers = $contact_subscribers::leftjoin('')
        if( ! current_user_can( 'erp_crm_create_groups' ) ) {
            $erp_peoples         = $wpdb->prefix . 'erp_peoples';
            $contact_subscribers = $contact_subscribers->leftJoin( $erp_peoples, $erp_peoples . '.id', '=', $contact_subscribe_tb . '.user_id' )->addSelect( $contact_subscribe_tb . '.*', $contact_group_tb . '.*', $erp_peoples . '.contact_owner' )->where( $erp_peoples . '.contact_owner', '=', get_current_user_id() );
        }

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' && ! $args['count'] ) {
            $contact_subscribers = $contact_subscribers->skip( $args['offset'] )->take( $args['number'] );
        }

        if ( isset( $args['group_id'] ) && ! empty( $args['group_id'] ) ) {
            $contact_subscribers = $contact_subscribers->where( $contact_group_tb . '.id', '=', $args['group_id'] );
        }

        // Check is the row want to search
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s               = $args['s'];
            $contact_subscribers = $contact_subscribers->where( 'name', 'LIKE', "%$arg_s%" )
                                                       ->orWhere( 'description', 'LIKE', "%$arg_s%" );
        }

        // Render all collection of data according to above filter (Main query)
        $results = $contact_subscribers
            ->get()
            ->groupBy( 'user_id' )
            ->toArray();

        foreach ( $results as $user_id => $value ) {
            $converted_data[] = [
                'user_id' => $user_id,
                'data'    => $value
            ];
        }

        $items = erp_array_to_object( $converted_data );

        // Check if args count true, then return total count customer according to above filter
        if ( $args['count'] ) {
            $items = count( $items );
        }
        /*if ( $args['count'] ) {
            if ( ! empty( $args['group_id'] ) ) {
                $items = WeDevs\ERP\CRM\Models\ContactSubscriber::leftjoin( $contact_group_tb, $contact_group_tb . '.id', '=', $contact_subscribe_tb . '.group_id' )->where( $contact_subscribe_tb . '.group_id', $args['group_id'] )->count();
            } else {
                $items = WeDevs\ERP\CRM\Models\ContactSubscriber::leftjoin( $contact_group_tb, $contact_group_tb . '.id', '=', $contact_subscribe_tb . '.group_id' )->count();
            }
        }*/

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

/**
 * Get contact gorup dropdown
 *
 * @since 1.0
 *
 * @param  array $label
 *
 * @return array
 */
function erp_crm_get_contact_group_dropdown( $label = [] ) {
    $groups = erp_crm_get_contact_groups_list();

    $list             = [];
    $unsubscribe_text = '';

    foreach ( $groups as $key => $group ) {
        $list[ $key ] = '<span class="group-name">' . $group . '</span>';
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
    $data = \WeDevs\ERP\CRM\Models\ContactSubscriber::select( 'user_id' )->distinct()->get()->toArray();

    return wp_list_pluck( $data, 'user_id' );
}

/**
 * Create Contact subscriber
 *
 * @since 1.0
 * @since 1.1.17 Return $subscriber object. Previously it was returning
 *               do_action function's returned data, but do_action
 *               returns void
 * @since 1.2.2  Insert people hash key if not exists one
 *
 * @param  array $data
 *
 * @return return object ContactSubscriber model or WP_Error
 */
function erp_crm_create_new_contact_subscriber( $args = [] ) {
    $defaults = array(
        'status'         => 'subscribe', // @TODO: Set a settings for that
        'subscribe_at'   => current_time( 'mysql' ),
        'unsubscribe_at' => null
    );

    $args = wp_parse_args( $args, $defaults );

    if ( empty( $args['group_id'] ) ) {
        return new WP_Error( 'no-group', __( 'No group selected', 'erp' ) );
    }

    if ( empty( $args['user_id'] ) ) {
        return new WP_Error( 'user-id', __( 'No contact founds', 'erp' ) );
    }

    $subscriber = \WeDevs\ERP\CRM\Models\ContactSubscriber::create( $args );

    $contact = new \WeDevs\ERP\CRM\Contact( $subscriber->user_id );
    $hash_id = sha1( microtime() . 'erp-subscription' . $args['group_id'] . $args['user_id'] );

    if ( ! $contact->hash ) {
        $contact->update_contact_hash( $hash_id );
    }

    do_action( 'erp_crm_create_contact_subscriber', $subscriber, $hash_id );

    return $subscriber;
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
    if ( ! $user_id ) {
        return new WP_Error( 'no-user-id', __( 'No contact found', 'erp' ) );
    }

    $data = \WeDevs\ERP\CRM\Models\ContactSubscriber::with( 'groups' )
                                                    ->where( [
                                                        'user_id' => $user_id,
                                                        'status'  => 'subscribe'
                                                    ] )
                                                    ->whereNotNull( 'subscribe_at' )
                                                    ->distinct()->get()->toArray();

    return $data;
}

/**
 * Delete Contact alreays subscribed
 *
 * @since 1.0
 *
 * @param  integer $id
 * @param  integer $group_id
 *
 * @return boolean
 */
function erp_crm_contact_subscriber_delete( $id, $group_id ) {
    if ( empty( $id ) || empty( $group_id ) ) {
        return false;
    }

    do_action( 'erp_crm_pre_unsubscribed_contact', $id, $group_id );

    if ( is_array( $id ) ) {
        return \WeDevs\ERP\CRM\Models\ContactSubscriber::whereIn( 'user_id', $id )->where( 'group_id', $group_id )->delete();
    } else {
        return \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $id )->where( 'group_id', $group_id )->delete();
    }
}

/**
 * Edit contact subscriber
 *
 * Delete if uncheck and if new then
 * create new one.
 *
 * @since 1.0
 * @since 1.2.2 Add hash in case of new subscriber
 * @since 1.2.3 Add hook after subscriber confirmation
 * @since 1.3.13 Add hook after unsubscribed from a group
 *
 * @param  array $groups
 * @param  integer $user_id
 *
 * @return void
 */
function erp_crm_edit_contact_subscriber( $groups, $user_id ) {
    $data = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )->distinct()->get()->toArray();

    $db                         = wp_list_pluck( $data, 'group_id' );
    $existing_group_with_status = wp_list_pluck( $data, 'status', 'group_id' );
    $existing_group             = $new_group = $del_group = $unsubscribe_group = [];

    if ( ! empty( $groups ) ) {
        foreach ( $groups as $group ) {
            if ( in_array( $group, $db ) ) {
                $existing_group[] = $group;

                if ( $existing_group_with_status[ $group ] == 'unsubscribe' ) {
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
            $updated = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )
                                                               ->where( 'group_id', $unsubscribe_group_id )
                                                               ->update( [
                                                                   'status'         => 'subscribe',
                                                                   'subscribe_at'   => current_time( 'mysql' ),
                                                                   'unsubscribe_at' => null
                                                               ] );

            if ( $updated ) {
                $subscriber = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )
                                                                      ->where( 'group_id', $unsubscribe_group_id )
                                                                      ->where( 'status', 'subscribe' )
                                                                      ->first();

                do_action( 'erp_crm_edit_contact_subscriber', $subscriber );
            }
        }
    }

    if ( ! empty( $new_group ) ) {

        foreach ( $new_group as $new_group_key => $new_group_id ) {
            $data = [
                'user_id'  => $user_id,
                'group_id' => $new_group_id,
                'hash'     => sha1( microtime() . 'erp-subscription' . $new_group_id . $user_id )
            ];

            erp_crm_create_new_contact_subscriber( $data );
        }

    }

    if ( ! empty( $del_group ) ) {
        foreach ( $del_group as $del_group_key => $del_group_id ) {
            $subscriber = \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $user_id )
                                                    ->where( 'group_id', $del_group_id )
                                                    ->where( 'status', 'subscribe' )
                                                    ->update( [
                                                        'status'         => 'unsubscribe',
                                                        'subscribe_at'   => null,
                                                        'unsubscribe_at' => current_time( 'mysql' )
                                                    ] );

            do_action( 'erp_crm_delete_contact_subscriber', $subscriber );
        }
    }
}

/**
 * Contact Group subscription statuses
 *
 * @since 1.1.17
 *
 * @return array
 */
function erp_crm_get_subscription_statuses() {
    return apply_filters( 'erp_crm_get_subscription_statuses', [
        'subscribe'   => __( 'Subscribed', 'erp' ),
        'unsubscribe' => __( 'Unsubscribe', 'erp' ),
        'unconfirmed' => __( 'Unconfirmed', 'erp' ),
    ] );
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
        'number'    => 20,
        'offset'    => 0,
        'orderby'   => 'created_at',
        'order'     => 'DESC',
        'count'     => false,
        'withgroup' => true
    ];

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'erp-crm-campaign-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $campaigns = new \WeDevs\ERP\CRM\Models\Campaign();

        if ( $args['withgroup'] ) {
            $campaigns = $campaigns->with( 'groups' );
        }

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' && ! $args['count'] ) {
            $campaigns = $campaigns->skip( $args['offset'] )->take( $args['number'] );
        }

        // Check is the row want to search
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s     = $args['s'];
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

        wp_cache_set( $cache_key, $items, 'erp' );
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
            'title'     => __( 'Email', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
            ]
        ],

        'phone' => [
            'title'     => __( 'Phone', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '%'  => __( 'has', 'erp' ),
                '!%' => __( 'has not', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
            ]
        ],

        'mobile' => [
            'title'     => __( 'Mobile', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '%'  => __( 'has', 'erp' ),
                '!%' => __( 'has not', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
            ]
        ],

        'website' => [
            'title'     => __( 'Website', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '%'  => __( 'has', 'erp' ),
                '!%' => __( 'has not', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
            ]
        ],

        'city' => [
            'title'     => __( 'City', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                ''   => __( 'from', 'erp' ),
                '!'  => __( 'not from', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' )
            ]
        ],

        'street_1' => [
            'title'     => __( 'Street 1', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '%'  => __( 'has', 'erp' ),
                '!%' => __( 'has not', 'erp' ),
                ''   => __( 'from', 'erp' ),
                '!'  => __( 'not from', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' )
            ]
        ],

        'street_2' => [
            'title'     => __( 'Street 2', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '%'  => __( 'has', 'erp' ),
                '!%' => __( 'has not', 'erp' ),
                ''   => __( 'from', 'erp' ),
                '!'  => __( 'not from', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' )
            ]
        ],

        'country_state' => [
            'title'     => __( 'Country/State', 'erp' ),
            'type'      => 'dropdown',
            'text'      => '',
            'condition' => [
                ''  => __( 'from', 'erp' ),
                '!' => __( 'not from', 'erp' )
            ],
            'options'   => \WeDevs\ERP\Countries::instance()->country_dropdown_options(),
        ],

        'postal_code' => [
            'title'     => __( 'Postal Code', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '%'  => __( 'has', 'erp' ),
                '!%' => __( 'has not', 'erp' ),
                ''   => __( 'from', 'erp' ),
                '!'  => __( 'not from', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
            ]
        ],

        'notes' => [
            'title'     => __( 'Notes', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '%'  => __( 'has', 'erp' ),
                '!%' => __( 'has not', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
            ]
        ],

        'tags'        => [
            'title'     => __( 'Tags', 'erp' ),
            'type'      => 'dropdown',
            'text'      => '',
            'condition' => [
                '~'  => __( 'contains', 'erp' ),
            ],
            'options'   => erp_html_generate_dropdown(erp_crm_get_contact_tags())
        ],

        'contact_group' => [
            'title'     => __( 'Contact Group', 'erp' ),
            'type'      => 'dropdown',
            'text'      => '',
            'condition' => [
                ''   => __( 'in group', 'erp' ),
                '!'  => __( 'not in group', 'erp' ),
                '!~' => __( 'unsubscribed from' ),
            ],
            'options'   => erp_html_generate_dropdown( wp_list_pluck( \WeDevs\ERP\CRM\Models\ContactGroup::select( 'id', 'name' )->get()->keyBy( 'id' )->toArray(), 'name' ) )
        ],

        'other' => [
            'title'     => __( 'Others Fields', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                '%'  => __( 'has', 'erp' ),
                '!%' => __( 'has not', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
            ]
        ],

        'life_stage' => [
            'title'     => __( 'Life Stage', 'erp' ),
            'type'      => 'dropdown',
            'text'      => '',
            'condition' => [
                ''  => __( 'is', 'erp' ),
                '!' => __( 'is not', 'erp' )
            ],
            'options'   => erp_crm_get_life_stages_dropdown()
        ],

        'source' => [
            'title'     => __( 'Contact Source', 'erp' ),
            'type'      => 'dropdown',
            'text'      => '',
            'condition' => [
                ''  => __( 'is', 'erp' ),
                '!' => __( 'is not', 'erp' )
            ],
            'options'   => erp_crm_contact_source_dropdown()
        ],

        'contact_age' => [
            'title'     => __( 'Contact age', 'erp' ),
            'type'      => 'number_range',
            'text'      => '',
            'condition' => [
                ''   => __( 'exactly', 'erp' ),
                '>'  => __( 'grater', 'erp' ),
                '<'  => __( 'less', 'erp' ),
                '<>' => __( 'Between', 'erp' ),
            ]
        ],
    ];

    if ( 'contact' == $type ) {
        $fields = erp_crm_get_customer_serach_key() + $fields;
    }

    if ( 'company' == $type ) {
        $fields = erp_crm_get_company_serach_key() + $fields;
    }

    return apply_filters( 'erp_crm_global_serach_fields', $fields, $type );
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
            'title'     => __( 'First Name', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                ''   => __( 'is', 'erp' ),
                '!'  => __( 'is not', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
            ]
        ],

        'last_name' => [
            'title'     => __( 'Last Name', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                ''   => __( 'is', 'erp' ),
                '!'  => __( 'is not', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
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
            'title'     => __( 'Company Name', 'erp' ),
            'type'      => 'text',
            'text'      => '',
            'condition' => [
                ''   => __( 'is', 'erp' ),
                '!'  => __( 'is not', 'erp' ),
                '~'  => __( 'contains', 'erp' ),
                '!~' => __( 'not contains', 'erp' ),
                '^'  => __( 'begins with', 'erp' ),
                '$'  => __( 'ends with', 'erp' ),
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
        foreach ( $values as $value ) {
            if ( preg_match( '/^!(?!~)/', $value ) ) {
                $result[ preg_replace( '/^!(?!~)/', '', $value ) ] = '!=';
            } elseif ( preg_match( '/^~/', $value ) ) {
                $result[ '%' . preg_replace( '/^~/', '', $value ) . '%' ] = 'LIKE';
            } elseif ( preg_match( '/^!~/', $value ) ) {
                $result[ '%' . preg_replace( '/^!~/', '', $value ) . '%' ] = 'NOT LIKE';
            } elseif ( preg_match( '/^\^/', $value ) ) {
                $result[ preg_replace( '/^\^/', '', $value ) . '%' ] = 'LIKE';
            } elseif ( preg_match( '/^\$/', $value ) ) {
                $result[ '%' . preg_replace( '/^\$/', '', $value ) ] = 'LIKE';
            } elseif ( preg_match( '/^<(?!>)/', $value ) ) {
                $result[ preg_replace( '/^<(?!>)/', '', $value ) ] = '<';
            } elseif ( preg_match( '/^>(?!>)/', $value ) ) {
                $result[ preg_replace( '/^>(?!>)/', '', $value ) ] = '>';
            } elseif ( preg_match( '/^<>(?!>)/', $value ) ) {
                $result[ preg_replace( '/^<>(?!>)/', '', $value ) ] = 'BETWEEN';
            } else {
                $result[ $value ] = '=';
            }
        }
    } else {
        if ( preg_match( '/^!(?!~)/', $values ) ) {
            $result[ preg_replace( '/^!(?!~)/', '', $values ) ] = '!=';
        } elseif ( preg_match( '/^~/', $values ) ) {
            $result[ '%' . preg_replace( '/^~/', '', $values ) . '%' ] = 'LIKE';
        } elseif ( preg_match( '/^!~/', $values ) ) {
            $result[ '%' . preg_replace( '/^!~/', '', $values ) . '%' ] = 'NOT LIKE';
        } elseif ( preg_match( '/^\^/', $values ) ) {
            $result[ preg_replace( '/^\^/', '', $values ) . '%' ] = 'LIKE';
        } elseif ( preg_match( '/^\$/', $values ) ) {
            $result[ '%' . preg_replace( '/^\$/', '', $values ) ] = 'LIKE';
        } elseif ( preg_match( '/^<(?!>)/', $values ) ) {
            $result[ preg_replace( '/^<(?!>)/', '', $values ) ] = '<';
        } elseif ( preg_match( '/^>(?!>)/', $values ) ) {
            $result[ preg_replace( '/^>(?!>)/', '', $values ) ] = '>';
        } elseif ( preg_match( '/^<>(?!>)/', $values ) ) {
            $result[ preg_replace( '/^<>(?!>)/', '', $values ) ] = 'BETWEEN';
        } else {
            $result[ $values ] = '=';
        }
    }

    return apply_filters( 'erp_crm_get_save_search_regx', $result, $values );
}

/**
 * Check if already segment name exists
 *
 * @since 1.3.5
 *
 * @param  string $name
 *
 * @return bool
 */
function erp_crm_check_segment_exists( $name ) {
    $exists = WeDevs\ERP\CRM\Models\SaveSearch::where( 'search_name', $name )->first();

    return $exists ? true : false;
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
function erp_crm_insert_save_search( $data ) {
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
 * @since 1.1.16 Make sure returned array remains array to use in JS
 *
 * @param  array $args
 *
 * @return array
 */
function erp_crm_get_save_search_item( $args = [] ) {

    $defaults = [
        'id'         => 0,
        'user_id'    => get_current_user_id(),
        'type'       => '',
        'groupby'    => 'global',
        'option_key' => 'id'
    ];

    $args = wp_parse_args( $args, $defaults );

    if ( $args['id'] ) {
        return WeDevs\ERP\CRM\Models\SaveSearch::find( $args['id'] )->toArray();
    }

    $results     = [];
    $search_keys = WeDevs\ERP\CRM\Models\SaveSearch::where( 'user_id', '=', $args['user_id'] )
                                                    ->orWhere( 'global', '=', 1 );

    if ( isset( $args['type'] ) && ! empty( $args['type'] ) ) {
        $search_keys = $search_keys->where( 'type', $args['type'] );
    }

    $search_keys = $search_keys->get()
                               ->groupBy( $args['groupby'] )
                               ->toArray();

    foreach ( $search_keys as $key => $search_values ) {
        $item = [];

        if ( $key == 0 ) {
            $item = [
                'id'      => __( 'own_search', 'erp' ),
                'name'    => __( 'Own Search', 'erp' ),
                'options' => []
            ];

        } else {
            $item = [
                'id'      => __( 'global_search', 'erp' ),
                'name'    => __( 'Global Search', 'erp' ),
                'options' => []
            ];
        }

        foreach ( $search_values as $index => $value ) {
            $item['options'][] = [
                'id'    => $value['id'],
                'text'  => $value['search_name'],
                'value' => $value['search_val']
            ];
        }

        array_push( $results, $item );
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
function erp_crm_delete_save_search_item( $id ) {
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
 * Advance filter for contact and company
 *
 * @since 1.1.0
 *
 * @param  array $custom_sql
 * @param  array $args
 *
 * @return array
 */
function erp_crm_contact_advance_filter( $custom_sql, $args ) {
    global $wpdb;

    $pep_fileds         = [
        'first_name',
        'last_name',
        'email',
        'website',
        'company',
        'phone',
        'mobile',
        'other',
        'fax',
        'notes',
        'street_1',
        'street_2',
        'city',
        'postal_code',
        'currency',
        'contact_owner',
        'created_by',
        'life_stage'
    ];
    $people_meta_fields = erp_crm_get_contact_meta_fields();

    if ( ! isset( $args['erpadvancefilter'] ) || empty( $args['erpadvancefilter'] ) ) {
        return $custom_sql;
    }

    $or_query   = explode( '&or&', $args['erpadvancefilter'] );
    $allowed    = erp_crm_get_serach_key( $args['type'] );
    $query_data = [];

    if ( $or_query ) {
        foreach ( $or_query as $or_q ) {
            parse_str( $or_q, $output );
            $serach_array = array_intersect_key( $output, array_flip( array_keys( $allowed ) ) );
            $query_data[] = $serach_array;
        }
    }

    if ( $query_data ) {
        $is_contact_group_joined = false;
        $table_alias             = 1;
        $tag_table_joined        = 0;

        foreach ( $query_data as $key => $or_query ) {
            if ( $or_query ) {
                $i                     = 0;
                $custom_sql['where'][] = ( $key == 0 ) ? "AND (" : 'OR (';

                foreach ( $or_query as $field => $value ) {
                    if ( in_array( $field, $pep_fileds ) ) {
                        if ( $value ) {
                            $val                   = erp_crm_get_save_search_regx( $value );
                            $custom_sql['where'][] = "(";
                            $j                     = 0;

                            foreach ( $val as $search_val => $search_condition ) {
                                $addOr = ( $j == count( $val ) - 1 ) ? '' : " OR ";

                                if ( 'has_not' == $search_val ) {
                                    $custom_sql['where'][] = "( $field is null OR $field = '' ) $addOr";
                                } else if ( 'if_has' == $search_val ) {
                                    $custom_sql['where'][] = "( $field is not null AND $field != '' ) $addOr";
                                } else {
                                    $custom_sql['where'][] = "$field $search_condition '$search_val' $addOr";
                                }

                                $j ++;
                            }

                            $custom_sql['where'][] = ( $i == count( $or_query ) - 1 ) ? ")" : " ) AND";
                        }
                    } else if ( $field == 'country_state' ) {
                        $custom_sql['where'][] = "(";
                        $j                     = 0;

                        foreach ( $value as $key => $search_value ) {
                            $search_condition_regx = erp_crm_get_save_search_regx( $search_value );
                            $condition             = array_shift( $search_condition_regx );
                            $key_value             = explode( ':', $search_value ); // seperate BAN:DHA to an array [ 0=>BAN, 1=>DHA]
                            $addOr                 = ( $j == count( $value ) - 1 ) ? '' : " OR ";

                            if ( count( $key_value ) > 1 ) {
                                $custom_sql['where'][] = "( country $condition '$key_value[0]' AND state $condition '$key_value[1]')$addOr";
                            } else {
                                $custom_sql['where'][] = "(country $condition '$key_value[0]')$addOr";
                            }

                            $j ++;
                        }
                        $custom_sql['where'][] = ( $i == count( $or_query ) - 1 ) ? ")" : " ) AND";
                    } else if ( $field == 'contact_group' ) {
                        if ( ! $is_contact_group_joined ) {
                            $custom_sql['join'][] = "LEFT JOIN {$wpdb->prefix}erp_crm_contact_subscriber as subscriber ON people.id = subscriber.user_id";

                            if ( ! $args['count'] ) {
                                $custom_sql['group_by'][] = 'people.id';
                            }

                            $is_contact_group_joined = true;
                        }

                        $custom_sql['where'][] = "(";

                        $and_clause = [];
                        foreach ( $value as $j => $search ) {
                            $addOr                 = ( $j == count( $value ) - 1 ) ? '' : " OR ";
                            $search_condition_regx = erp_crm_get_save_search_regx( $search );
                            $condition             = array_shift( $search_condition_regx );

                            switch ( $condition ) {
                                case 'NOT LIKE':
                                    $search       = str_replace( '!~', '', $search );
                                    $and_clause[] = "( subscriber.group_id = {$search} AND subscriber.unsubscribe_at IS NOT NULL )";
                                    break;

                                case '!=':
                                    $search       = str_replace( '!', '', $search );
                                    $and_clause[] = "subscriber.group_id != {$search}";
                                    break;

                                default:
                                    $and_clause[] = "( subscriber.group_id = {$search} AND subscriber.unsubscribe_at IS NULL )";
                                    break;
                            }
                        }

                        if ( ! empty( $and_clause ) ) {
                            $custom_sql['where'][] = implode( " OR ", $and_clause );
                        } else {
                            $custom_sql['where'][] = "1=1";
                        }

                        $custom_sql['where'][] = ( $i == count( $or_query ) - 1 ) ? ")" : " ) AND";
                    }else if( $field == 'tags' ){
                        if( ! $tag_table_joined ){
                            $custom_sql['join'][] = "INNER JOIN {$wpdb->prefix}term_relationships as term_relation on (people.id = term_relation.object_id)";
                            $custom_sql['join'][] = "INNER JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy ON (term_relation.term_taxonomy_id = term_taxonomy.term_taxonomy_id)";
                            $custom_sql['join'][] = "INNER JOIN {$wpdb->prefix}terms AS term ON (term.term_id = term_taxonomy.term_id)";
                            $tag_table_joined = 1;
                        }

                        $custom_sql['where'][] = "(";
                        $and_clause = [];
                        foreach ( $value as $j => $search ) {
                            $addOr                 = ( $j == count( $value ) - 1 ) ? '' : " OR ";
                            $search_condition_regx = erp_crm_get_save_search_regx( $search );
                            $condition             = array_shift( $search_condition_regx );

                            switch ( $condition ) {
                                case 'NOT LIKE':
                                    $search       = str_replace( '!~', '', $search );
                                    $and_clause[] = "term.term_id != {$search}";
                                    break;

                                default:
                                    $search       = str_replace( '~', '', $search );
                                    $and_clause[] = "term.term_id = {$search}";
                                    break;
                            }
                        }


                        if ( ! empty( $and_clause ) ) {
                            $custom_sql['where'][] = implode( " OR ", $and_clause );
                        } else {
                            $custom_sql['where'][] = "1=1";
                        }

                        $custom_sql['where'][] = "AND term_taxonomy.taxonomy = 'erp_crm_tag'";
                        $custom_sql['where'][] = ( $i == count( $or_query ) - 1 ) ? ")" : " ) AND";

                    } else if ( in_array( $field, $people_meta_fields ) ) {
                        $pepmeta_tb           = $wpdb->prefix . 'erp_peoplemeta';
                        $name                 = "people_meta_" . ( $table_alias ) . "_" . ( $i + 1 );
                        $custom_sql['join'][] = "LEFT JOIN $pepmeta_tb as $name on people.id = $name.`erp_people_id`";

                        if ( $value ) {
                            $val = erp_crm_get_save_search_regx( $value );

                            $custom_sql['where'][] = "(";
                            $j                     = 0;

                            foreach ( $val as $search_val => $search_condition ) {

                                $addOr = ( $j == count( $val ) - 1 ) ? '' : " OR ";

                                if ( 'has_not' == $search_val ) {
                                    $custom_sql['where'][] = "( $name.meta_key='$field' AND ( $name.meta_value is null OR $name.meta_value = '' ) ) $addOr";
                                } else if ( 'if_has' == $search_val ) {
                                    $custom_sql['where'][] = "( $name.meta_key='$field' AND ( $name.meta_value is not null AND $name.meta_value != '' ) ) $addOr";
                                } else if ( 'BETWEEN' == $search_condition ) {
                                    $formatted_val         = explode( ',', $search_val );
                                    $custom_sql['where'][] = "( $name.meta_key='$field' AND ( $name.meta_value >= '$formatted_val[0]' AND $name.meta_value <= '$formatted_val[1]' ) ) $addOr";
                                } else {
                                    $custom_sql['where'][] = "( $name.meta_key='$field' AND $name.meta_value $search_condition '$search_val' ) $addOr";
                                }

                                $j ++;
                            }
                            $custom_sql['where'][] = ( $i == count( $or_query ) - 1 ) ? ")" : " ) AND";
                        }
                    } else {
                        $custom_sql = apply_filters( 'erp_crm_customer_segmentation_sql', $custom_sql, $field, $value, $or_query, $i, $table_alias );
                    }

                    $i ++;
                }

                $custom_sql['where'][] = ")";
            }

            $table_alias ++;
        }

    }

    return $custom_sql;
}

/**
 * SQL filter to check if a people id is belongs to a saved search
 *
 * @since 1.1.1
 *
 * @param array $sql
 * @param array $args
 *
 * @return array
 */
function erp_crm_is_people_belongs_to_saved_search( $sql, $args ) {
    if ( empty( $args['erpadvancefilter'] ) || empty( $args['test_user'] ) ) {
        return $sql;
    }

    $sql['post_where_queries'][] = "AND people.id = " . $args['test_user'];

    return $sql;
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

    $res = \WeDevs\ERP\CRM\Models\Activity::with( [
        'contact' => function ( $query ) {
            $query->with( 'types' );
        }
    ] )->where( 'type', '=', 'log_activity' )
                                          ->where( 'created_by', $user_id )
                                          ->where( $db->raw( "DATE_FORMAT( `start_date`, '%Y %m %d' )" ), \Carbon\Carbon::today()->format( 'Y m d' ) )
                                          ->take( 7 )
                                          ->get()
                                          ->toArray();

    foreach ( $res as $key => $result ) {
        $results[ $key ]                     = $result;
        $results[ $key ]['contact']['types'] = wp_list_pluck( $results[ $key ]['contact']['types'], 'name' );
        $results[ $key ]['extra']            = json_decode( base64_decode( $result['extra'] ), true );
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

    $res = \WeDevs\ERP\CRM\Models\Activity::with( [
        'contact' => function ( $query ) {
            $query->with( 'types' );
        }
    ] )->where( 'type', '=', 'log_activity' )
                                          ->where( 'created_by', $user_id )
                                          ->where( $db->raw( "DATE_FORMAT( `start_date`, '%Y %m %d' )" ), '>=', \Carbon\Carbon::tomorrow()->format( 'Y m d' ) )
                                          ->where( $db->raw( "DATE_FORMAT( `start_date`, '%Y %m %d' )" ), '<=', \Carbon\Carbon::tomorrow()->addDays( 7 )->format( 'Y m d' ) )
                                          ->take( 7 )
                                          ->get()
                                          ->toArray();

    foreach ( $res as $key => $result ) {
        $results[ $key ]                     = $result;
        $results[ $key ]['contact']['types'] = wp_list_pluck( $results[ $key ]['contact']['types'], 'name' );
        $results[ $key ]['extra']            = json_decode( base64_decode( $result['extra'] ), true );
    }

    return $results;
}

/**
 * Save email activity & send to contact owner
 *
 * @param  array $email
 * @param  string $inbound_email_address
 *
 * @return array erp_crm_save_customer_feed_data
 */
function erp_crm_save_email_activity( $email, $inbound_email_address ) {
    $extra_data = [ 'replied' => 1 ];

    if ( isset( $email['attachments'] ) ) {
        $extra_data['attachments'] = $email['attachments'];
    }

    $save_data = [
        'user_id'       => $email['cid'],
        'created_by'    => $email['sid'],
        'message'       => $email['body'],
        'type'          => 'email',
        'email_subject' => $email['subject'],
        'extra'         => base64_encode( json_encode( $extra_data ) ),
    ];

    $customer_feed_data = erp_crm_save_customer_feed_data( $save_data );

    $contact_id = (int) $save_data['user_id'];
    $sender_id  = $save_data['created_by'];

    $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

    $contact_owner_id = $contact->get_contact_owner();
    $contact_owner    = get_userdata( $contact_owner_id );

    // Send an email to contact owner
    if ( isset( $contact_owner_id ) ) {
        $to_email = $contact_owner->user_email;

        $headers = "";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";

        $server_host = isset( $_SERVER['HTTP_HOST'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
        $message_id = md5( uniqid( time() ) ) . '.' . $contact_id . '.' . $contact_owner_id . '.r2@' . $server_host;

        $custom_headers = [
            "Message-ID"  => "<{$message_id}>",
            "In-Reply-To" => "<{$message_id}>",
            "References"  => "<{$message_id}>",
        ];

        $reply_to = $inbound_email_address;
        $headers  .= "Reply-To: WP ERP <$reply_to>" . "\r\n";

        $mail_attachments = [];

        if ( isset( $email['attachments'] ) && !empty( $email['attachments'] ) ) {
            $mail_attachments = wp_list_pluck( $email['attachments'], 'path' );
        }

        if ( wperp()->google_auth->is_active() ){
            //send using gmail api
            $sent = erp_mail_send_via_gmail( $to_email, $email['subject'], $email['body'], $headers, $mail_attachments, $custom_headers  );
        } else {
            // Send email at contact
            $sent = erp_mail( $to_email, $email['subject'], $email['body'], $headers, $mail_attachments, $custom_headers );
        }

    }

    // Update email counter
    update_option( 'wp_erp_inbound_email_count', get_option( 'wp_erp_inbound_email_count', 0 ) + 1 );

    return $customer_feed_data;
}

/**
 * Save email activity by contact owner & send to contact
 *
 * @param  array $email
 * @param  string $inbound_email_address
 *
 * @return array customer_feed_data
 */
function erp_crm_save_contact_owner_email_activity( $email, $inbound_email_address ) {

    $extra_data = [ 'replied' => 1 ];

    if ( isset( $email['attachments'] ) ) {
        $extra_data['attachments'] = $email['attachments'];
    }

    $save_data = [
        'user_id'       => $email['cid'],
        'created_by'    => $email['sid'],
        'message'       => $email['body'],
        'type'          => 'email',
        'email_subject' => $email['subject'],
        'extra'         => base64_encode( json_encode( $extra_data ) ),
    ];

    $customer_feed_data = erp_crm_save_customer_feed_data( $save_data );

    $contact_id = intval( $save_data['user_id'] );

    $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

    $headers = "";
    $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";

    $server_host = isset( $_SERVER['HTTP_HOST'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
    $message_id = md5( uniqid( time() ) ) . '.' . $save_data['user_id'] . '.' . $save_data['created_by'] . '.r1@' . $server_host;

    $custom_headers = [
        "Message-ID"  => "<{$message_id}>",
        "In-Reply-To" => "<{$message_id}>",
        "References"  => "<{$message_id}>",
    ];

    $reply_to = $inbound_email_address;
    $headers  .= "Reply-To: WP ERP <$reply_to>" . "\r\n";

    $owner      = $contact->get_contact_owner();
    $owner_info = get_userdata($owner);

    $mail_attachments = [];

    if ( isset( $email['attachments'] ) && !empty( $email['attachments'] ) ) {
        $mail_attachments = wp_list_pluck( $email['attachments'], 'path' );
    }

    if ( wperp()->google_auth->is_active() ){
        //send using gmail api
        $sent = erp_mail_send_via_gmail( $owner_info->user_email, $email['subject'], $email['body'], $headers, $mail_attachments, $custom_headers  );
    } else {
        // Send email at contact
        $sent =  erp_mail( $owner_info->user_email, $email['subject'], $email['body'], $headers, $mail_attachments, $custom_headers );
    }

    // Update email counter
    update_option( 'wp_erp_inbound_email_count', get_option( 'wp_erp_inbound_email_count', 0 ) + 1 );

    return $customer_feed_data;
}

/**
 * Prepare schedule data for calendar
 *
 * @since 1.0
 * @since 1.1.13 Display tasks title beside datetime
 *
 * @param  array $schedule
 *
 * @return array
 */
function erp_crm_prepare_calendar_schedule_data( $schedules ) {
    $schedules_data = [];

    if ( $schedules ) {
        foreach ( $schedules as $key => $schedule ) {
            $start_date = date( 'Y-m-d', strtotime( $schedule['start_date'] ) );
            $end_date   = ( $schedule['end_date'] ) ? date( 'Y-m-d', strtotime( $schedule['end_date'] . '+1 day' ) ) : date( 'Y-m-d', strtotime( $schedule['start_date'] . '+1 day' ) );        // $end_date = $schedule['end_date'];

            if ( $schedule['start_date'] < current_time( 'mysql' ) ) {
                $time = date( 'g:i a', strtotime( $schedule['start_date'] ) );
            } else {
                if ( date( 'g:i a', strtotime( $schedule['start_date'] ) ) == date( 'g:i a', strtotime( $schedule['end_date'] ) )  || ! $schedule['end_date'] ) {
                    $time = date( 'g:i a', strtotime( $schedule['start_date'] ) );
                } else {
                    $time = date( 'g:i a', strtotime( $schedule['start_date'] ) ) . ' to ' . date( 'g:i a', strtotime( $schedule['end_date'] ) );
                }
            }

            if ( 'tasks' === $schedule['type'] && ! empty( $schedule['extra']['task_title'] ) ) {
                $title = $time . ' | ' . $schedule['extra']['task_title'];
            } else {
                $title = $time . ' ' . ucfirst( $schedule['log_type'] );
            }

            $color = $schedule['start_date'] < current_time( 'mysql' ) ? '#f05050' : '#03c756';

            $schedules_data[] = [
                'schedule' => $schedule,
                'title'    => $title,
                'color'    => $color,
                'start'    => $start_date,
                'end'      => $end_date
            ];
        }
    }

    return $schedules_data;
}

/**
 * Get schedule data in schedule page
 *
 * @since 1.0
 * @since 1.1.13 i) Fetch tasks activities also. ii) Display data based on permission and current tab
 *
 * @return array
 */
function erp_crm_get_schedule_data( $tab = '' ) {
    $args = [
        'number' => - 1,
        'type'   => [ 'log_activity', 'tasks' ]
    ];

    /**
     * If user is not a CRM Manager then he/she should always see only activities assigned to him/her.
     * For CRM Managers, in "My Schedules" tab should only show the activities assigned to him/her.
     * "All Schedules" should show all activities
     */
    if ( ! current_user_can( erp_crm_get_manager_role() ) || 'own' === $tab ) {
        $args['assigned_to'] = get_current_user_id();
    }

    $schedules      = erp_crm_get_feed_activity( $args );
    $schedules_data = erp_crm_prepare_calendar_schedule_data( $schedules );

    return $schedules_data;
}

/**
 * Get CRM email from address.
 *
 * @since 1.0
 *
 * @return string
 */
function erp_crm_get_email_from_address() {
    $settings = get_option( 'erp_settings_erp-email_general', [] );

    if ( array_key_exists( 'from_email', $settings ) ) {
        return sanitize_email( $settings['from_email'] );
    }

    return get_option( 'admin_email' );
}

/**
 * Get CRM email from name.
 *
 * @since 1.0
 *
 * @return string
 */
function erp_crm_get_email_from_name() {
    global $current_user;

    return $current_user->display_name;
}

/**
 * Track email opened.
 *
 * @since 1.0
 *
 * @return void
 */
function erp_crm_track_email_opened() {
    if ( isset( $_GET['aid'] ) ) {
        $activity = \WeDevs\ERP\CRM\Models\Activity::find( sanitize_text_field( wp_unslash( $_GET['aid'] ) ) );
        $extra    = json_decode( base64_decode( $activity->extra ), true );

        if ( isset( $extra['email_opened_at'] ) && ! is_array( $extra['email_opened_at'] ) ) {
            $tmp                        = $extra['email_opened_at'];
            $extra['email_opened_at']   = [];
            $extra['email_opened_at'][] = $tmp;
        }

        $extra['email_opened_at'][] = current_time( 'mysql' );

        $data = [
            'extra' => base64_encode( json_encode( $extra ) )
        ];


        $activity->update( $data );
    }

    header( 'Cache-Control: no-store, no-cache, must-revalidate' );
    header( 'Cache-Control: post-check=0, pre-check=0', false );
    header( 'Pragma: no-cache' );
    header( 'Content-type: image/png' );

    $image  = WPERP_PATH . '/assets/images/one-by-one-pixel.png';
    $handle = fopen( $image, 'r' );

    if ( ! $handle ) {
        exit;
    }

    $contents = fread( $handle, filesize( $image ) );
    fclose( $handle );
    echo wp_kses_post( $contents );

    exit;
}

/**
 * Contact_Forms_Integration class instance using erp_crm_loaded hook
 *
 * @since  1.0
 *
 * @return void
 */
function erp_crm_contact_forms() {
    // do not proceed if CRM is not active
    if ( ! wperp()->modules->is_module_active( 'crm' ) ) {
        return;
    }

    new \WeDevs\ERP\CRM\ContactForms\CF7();
    new \WeDevs\ERP\CRM\ContactForms\Ninja_Forms();
    \WeDevs\ERP\CRM\ContactForms\Contact_Forms_Integration::init();
}

/**
 * Add a new ERP settings tab with erp_settings_pages hook
 *
 * @since  1.0
 *
 * @param array $settings ERP settings tabs
 *
 * @return array
 */
function erp_settings_pages_contact_forms( $settings ) {
    if ( erp_crm_is_current_user_manager() ) {
        $settings[] = \WeDevs\ERP\CRM\ContactForms\ERP_Settings_Contact_Forms::init();
    }

    return $settings;
}

function erp_crm_settings_pages( $settings ) {
    if ( erp_crm_is_current_user_manager() ) {
        $settings[] = new \WeDevs\ERP\CRM\CRM_Settings();
    }

    return $settings;
}

/**
 * Get CRM users with different params
 *
 * @since 1.0
 *
 * @param  array $args
 *
 * @return array
 */
function erp_crm_get_crm_user( $args = [] ) {
    global $wp_version;

    $crm_users = [];
    $defaults  = [
        's'          => false,
        'number'     => -1,
        'orderby'    => 'display_name',
        'order'      => 'ASC',
        'fields'     => 'all', // If needs to selected fileds then set those fields as an array
        'meta_query' => [],
        'include'    => [],
        'exclude'    => []
    ];

    $args = wp_parse_args( $args, $defaults );

    $user_query_args = [
        'fields'   => $args['fields'],
        'role__in' => [ 'erp_crm_manager', 'erp_crm_agent' ],
        'orderby'  => $args['orderby'],
        'order'    => $args['order'],
    ];

    if ( $args['number'] != - 1 ) {
        $user_query_args['number'] = $args['number'];
    }

    if ( ! empty( $args['meta_query'] ) ) {
        $user_query_args['meta_query'] = $args['meta_query'];
    }

    if ( ! empty( $args['include'] ) ) {
        $user_query_args['include'] = $args['include'];
    }

    if ( ! empty( $args['exclude'] ) ) {
        $user_query_args['exclude'] = $args['exclude'];
    }

    if ( $args['s'] ) {
        $user_query_args['search']     = '*' . $args['s'] . '*';
        $user_query_args['meta_query'] = [
            'relation' => 'OR',
            [
                'key'     => 'first_name',
                'value'   => $args['s'],
                'compare' => 'LIKE'
            ],
            [
                'key'     => 'last_name',
                'value'   => $args['s'],
                'compare' => 'LIKE'
            ],
            [
                'key'     => 'nickname',
                'value'   => $args['s'],
                'compare' => 'LIKE'
            ]
        ];
    }

    $crm_user_query = new \WP_User_Query( apply_filters( 'erp_crm_get_crm_user_query', $user_query_args, $args ) );

    $crm_users = $crm_user_query->get_results();

    return $crm_users;
}

/**
 * Get crm user for dropdown
 *
 * @since 1.0
 *
 * @param  array $label
 *
 * @return array
 */
function erp_crm_get_crm_user_dropdown( $label = [] ) {
    $users = erp_crm_get_crm_user();
    $list  = [];

    foreach ( $users as $key => $user ) {
        $list[ $user->ID ] = esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')';
    }

    if ( $label ) {
        $list = $label + $list;
    }

    return $list;
}

/**
 * Get schedule notification type
 *
 * @since 1.0
 *
 * @return array
 */
function erp_crm_activity_schedule_notification_type() {
    return apply_filters( 'erp_crm_activity_schedule_notification_type', [
        'email' => __( 'Email', 'erp' )
    ] );
}

/**
 * Insert and update save replies
 *
 * @since 1.0
 *
 * @param  array $data
 *
 * @return boolean
 */
function erp_crm_insert_save_replies( $args = [] ) {
    if ( ! $args ) {
        return new WP_Error( 'no-data', __( 'Template name and body content are required', 'erp' ) );
    }

    if ( empty( $args['id'] ) ) {
        $args['id'] = 0;
    }

    $save_replies = WeDevs\ERP\CRM\Models\Save_Replies::firstOrNew( [ 'id' => $args['id'] ] );

    $current_data = [
        'name'     => $save_replies->name,
        'subject'  => $save_replies->subject,
        'template' => $save_replies->template,
    ];

    $args = wp_parse_args( $args, $current_data );

    // validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'Template name is required', 'erp' ) );
    }

    if ( empty( $args['template'] ) ) {
        return new WP_Error( 'no-template', __( 'Template body is required', 'erp' ) );
    }

    // update or insert new
    if ( $save_replies->exists ) {
        $save_replies->update( $args );

        $old_value  = base64_encode( maybe_serialize( $current_data ) );
        $new_value  = base64_encode( maybe_serialize( $args ) );
        $message    = sprintf( __( '<strong>%s</strong> has been updated', 'erp' ), $current_data['name'] );
        $changetype = 'edit';

    } else {
        $save_replies->setRawAttributes( $args, true );
        $save_replies->save();

        $old_value  = '';
        $new_value  = '';
        $message    = sprintf( __( '<strong>%s</strong> has been created', 'erp' ), $args['name'] );
        $changetype = 'add';
    }

    // audit log
    erp_log()->add( [
        'component'     => 'CRM',
        'sub_component' => 'Saved Replies',
        'old_value'     => $old_value,
        'new_value'     => $new_value,
        'message'       => $message,
        'changetype'    => $changetype,
        'created_by'    => get_current_user_id()

    ] );

    return $save_replies;
}

function erp_crm_get_save_replies_shortcodes() {
    return apply_filters( 'erp_crm_get_save_replies_shortcodes', [
        '{first_name}'  => [
            'title'   => __( 'First Name', 'erp' ),
            'key'     => 'first_name',
            'is_meta' => false
        ],
        '{last_name}'   => [
            'title'   => __( 'Last Name', 'erp' ),
            'key'     => 'last_name',
            'is_meta' => false
        ],
        '{company}'     => [
            'title'   => __( 'Company Name', 'erp' ),
            'key'     => 'company',
            'is_meta' => false
        ],
        '{email}'       => [
            'title'   => __( 'Email', 'erp' ),
            'key'     => 'email',
            'is_meta' => false
        ],
        '{phone}'       => [
            'title'   => __( 'Phone', 'erp' ),
            'key'     => 'phone',
            'is_meta' => false
        ],
        '{mobile}'      => [
            'title'   => __( 'Mobile', 'erp' ),
            'key'     => 'mobile',
            'is_meta' => false
        ],
        '{website}'     => [
            'title'   => __( 'Website', 'erp' ),
            'key'     => 'website',
            'is_meta' => false
        ],
        '{fax}'         => [
            'title'   => __( 'Fax', 'erp' ),
            'key'     => 'fax',
            'is_meta' => false
        ],
        '{street_1}'    => [
            'title'   => __( 'Street 1', 'erp' ),
            'key'     => 'street_1',
            'is_meta' => false
        ],
        '{street_2}'    => [
            'title'   => __( 'Street 2', 'erp' ),
            'key'     => 'street_2',
            'is_meta' => false
        ],
        '{country}'     => [
            'title'   => __( 'Country', 'erp' ),
            'key'     => 'country',
            'is_meta' => false
        ],
        '{state}'       => [
            'title'   => __( 'State', 'erp' ),
            'key'     => 'state',
            'is_meta' => false
        ],
        '{postal_code}' => [
            'title'   => __( 'Postal Code', 'erp' ),
            'key'     => 'postal_code',
            'is_meta' => false
        ]
    ] );
}

/**
 * Get all email save replies
 *
 * @since 1.0
 *
 * @param  array $args
 *
 * @return object
 */
function erp_crm_get_save_replies( $args = [] ) {
    $defaults = [
        'number'  => - 1,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
    ];

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'erp-crm-save-replies-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $results      = [];
        $save_replies = new WeDevs\ERP\CRM\Models\Save_Replies();

        // Check if want all data without any pagination
        if ( $args['number'] != '-1' && ! $args['count'] ) {
            $save_replies = $save_replies->skip( $args['offset'] )->take( $args['number'] );
        }

        // Render all collection of data according to above filter (Main query)
        $results = $save_replies->orderBy( $args['orderby'], $args['order'] )
                                ->get()
                                ->toArray();

        $items = erp_array_to_object( $results );

        // Check if args count true, then return total count customer according to above filter
        if ( $args['count'] ) {
            $items = WeDevs\ERP\CRM\Models\Save_Replies::count();
        }

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

/**
 * Get single save replies by id
 *
 * @since 1.0
 *
 * @param  integer $id
 *
 * @return array|object
 */
function erp_crm_get_save_replies_by_id( $id ) {
    if ( empty( $id ) ) {
        return new WP_Error( 'no-record', __( 'No record found', 'erp' ) );
    }

    if ( is_array( $id ) ) {
        return WeDevs\ERP\CRM\Models\Save_Replies::whereIn( 'id', $id )->get()->toArray();
    } else {
        return WeDevs\ERP\CRM\Models\Save_Replies::find( $id )->toArray();
    }
}

/**
 * Delete save replies
 *
 * @param  integer|array $id
 *
 * @return boolean
 */
function erp_crm_save_replies_delete( $id ) {
    if ( empty( $id ) ) {
        return new WP_Error( 'no-record', __( 'No record found', 'erp' ) );
    }

    if ( is_array( $id ) ) {
        return WeDevs\ERP\CRM\Models\Save_Replies::destroy( $id );
    } else {
        return WeDevs\ERP\CRM\Models\Save_Replies::find( $id )->delete();
    }
}

/**
 * Render save replies with parsing body
 *
 * @since 1.0
 *
 * @param  integer $template_id
 * @param  integer $contact_id
 *
 * @return array
 */
function erp_crm_render_save_replies( $template_id, $contact_id ) {
    if ( empty( $template_id ) ) {
        return new WP_Error( 'no-template', __( 'No template found', 'erp' ) );
    }

    if ( empty( $contact_id ) ) {
        return new WP_Error( 'no-contact', __( 'No contact found', 'erp' ) );
    }

    $contacts       = new \WeDevs\ERP\CRM\Contact( $contact_id );
    $templates      = (object) erp_crm_get_save_replies_by_id( $template_id );
    $shortcodes     = erp_crm_get_save_replies_shortcodes();
    $contacts_info  = $contacts->data ;
    $data = [];

    foreach ( $shortcodes as $shortcode => $shortcode_val ) {
        if ( $shortcode_val['is_meta'] ) {
            $data[] = erp_people_get_meta( $contact_id, $shortcode_val['key'], true );
        } else {
            if ( property_exists( $contacts_info, $shortcode_val['key'] ) ) {
                if ($shortcode == '%country%') {
                    $data[] = erp_get_country_name($contacts_info->{$shortcode_val['key']});
                } elseif ($shortcode == '%state%') {
                    $data[] = erp_get_state_name($contacts_info->country, $contacts_info->{$shortcode_val['key']});
                } else {
                    $data[] = $contacts_info->{$shortcode_val['key']};
                }
            }
        }
    }

    $find    = array_keys( $shortcodes );
    $replace = apply_filters( 'erp_crm_filter_contact_data_via_shortcodes', $data, $contacts );
    $body    = str_replace( $find, $replace, $templates->template );

    return [
        'subject'  => $templates->subject,
        'template' => $body
    ];
}

/**
 * Display the user bulk actions.
 *
 * @since 1.0
 *
 * @return void
 */
function erp_user_bulk_actions() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('<option>').val('crm_contact').text('<?php esc_html_e( 'Import into CRM', 'erp' )?>').appendTo("select[name='action']");
            $('<option>').val('crm_contact').text('<?php esc_html_e( 'Import into CRM', 'erp' )?>').appendTo("select[name='action2']");
        });
    </script>
    <?php
}

/**
 * Handle the user bulk actions.
 *
 * @since 1.0
 *
 * @return void
 */
function erp_handle_user_bulk_actions() {
    $wp_list_table = _get_list_table( 'WP_Users_List_Table' );
    $action        = $wp_list_table->current_action();

    if ( ! in_array( $action, [ 'crm_contact', 'process_crm_contact' ] ) ) {
        return;
    }

    switch ( $action ) {
        case 'crm_contact':
            // security check
            check_admin_referer( 'bulk-users' );

            if ( empty( $_REQUEST['users'] ) ) {
                return;
            }

            include( ABSPATH . 'wp-admin/admin-header.php' );
            include( WPERP_CRM_VIEWS . '/import-user-to-crm.php' );
            include( ABSPATH . 'wp-admin/admin-footer.php' );

            exit;

            break;

        case 'process_crm_contact':
            if ( isset( $_REQUEST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'erp_create_contact_from_user' ) ) {
                exit;
            }

            if ( empty( $_REQUEST['users'] ) ) {
                return;
            }

            $created       = 0;
            $users         = [];
            $user_ids      = isset( $_REQUEST['users'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['users'] ) ) : [];
            $life_stage    = isset( $_POST['life_stage'] ) ? sanitize_text_field( wp_unslash( $_POST['life_stage'] ) ) : [];
            $contact_owner = isset( $_POST['contact_owner'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_owner'] ) ) : [];

            $contacts = erp_get_people_by( 'user_id', $user_ids );

            if ( ! empty( $contacts ) ) {
                $contact_ids = wp_list_pluck( $contacts, 'user_id' );
                $user_ids    = array_diff( $user_ids, $contact_ids );
            }

            foreach ( $user_ids as $user_id ) {
                $wp_user     = get_user_by( 'id', $user_id );
                $phone       = get_user_meta( $user_id, 'phone', true );
                $street_1    = get_user_meta( $user_id, 'street_1', true );
                $street_2    = get_user_meta( $user_id, 'street_2', true );
                $city        = get_user_meta( $user_id, 'city', true );
                $state       = get_user_meta( $user_id, 'state', true );
                $postal_code = get_user_meta( $user_id, 'postal_code', true );
                $country     = get_user_meta( $user_id, 'country', true );

                $data = [
                    'type'          => 'contact',
                    'user_id'       => absint( $user_id ),
                    'first_name'    => $wp_user->first_name,
                    'last_name'     => $wp_user->last_name,
                    'email'         => $wp_user->user_email,
                    'phone'         => $phone,
                    'street_1'      => $street_1,
                    'street_2'      => $street_2,
                    'city'          => $city,
                    'state'         => $state,
                    'postal_code'   => $postal_code,
                    'country'       => $country,
                    'contact_owner' => $contact_owner,
                    'life_stage'    => $life_stage,
                ];

                $contact_id = erp_insert_people( $data );

                if ( is_wp_error( $contact_id ) ) {
                    continue;
                } else {
                    erp_crm_update_contact_owner( $user_id, $contact_owner );
                }

                $created ++;
            }

            // build the redirect url
            $sendback = admin_url( 'users.php' );
            $sendback = add_query_arg( [ 'created' => $created ], $sendback );
            wp_redirect( $sendback );
            exit;

            break;

        default:
            return;
    }
}

/**
 * Display the user bulk actions notice.
 *
 * @since 1.0
 *
 * @return void
 */
function erp_user_bulk_actions_notices() {
    global $pagenow;

    if ( $pagenow == 'users.php' && isset( $_REQUEST['created'] ) && (int) $_REQUEST['created'] ) {
        $message = wp_kses_post( sprintf( __( '%s contacts created.', 'erp' ), number_format_i18n( sanitize_text_field( wp_unslash( $_REQUEST['created'] ) ) ) ) );
        echo wp_kses_post( "<div class='updated'><p>{$message}</p></div>" );
    }
}

/**
 * Create contact from created user.
 *
 * @since 1.0
 * @since 1.2.8 erp_crm_contact_created action
 *
 * @param  int $user_id
 *
 * @return void
 */
function erp_create_contact_from_created_user( $user_id ) {
    $user_auto_import = (int) erp_get_option( 'user_auto_import', 'erp_settings_erp-crm_contacts', 0 );

    if ( ! $user_auto_import ) {
        return;
    }

    $default_roles = erp_get_option( 'user_roles', 'erp_settings_erp-crm_contacts', [] );
    $user          = get_userdata( $user_id );

    $matched_roles = array_intersect( $user->roles, $default_roles );
    if ( empty ( $matched_roles ) ) {
        return;
    }

    $people = erp_get_people_by( 'email', $user->user_email );
    if ( false !== $people ) {
        return;
    }

    $contact_owner = erp_get_option( 'contact_owner', 'erp_settings_erp-crm_contacts', null );
    $contact_owner = ( $contact_owner ) ? $contact_owner : get_current_user_id();
    $life_stage    = erp_get_option( 'life_stage', 'erp_settings_erp-crm_contacts', 'opportunity' );

    $data = [];

    $data['type']          = 'contact';
    $data['user_id']       = $user_id;
    $data['first_name']    = $user->first_name;
    $data['last_name']     = $user->last_name;
    $data['email']         = $user->user_email;
    $data['website']       = $user->user_url;
    $data['contact_owner'] = $contact_owner;
    $data['life_stage']    = $life_stage;
    $contact_id            = erp_insert_people( $data );

    do_action( 'erp_crm_contact_created', $contact_id, $data );

    return;
}

/**
 * Check new inbound emails
 *
 * @return void
 */
function erp_crm_check_new_inbound_emails() {
    $is_imap_active = erp_is_imap_active();

    if ( ! $is_imap_active ) {
        return;
    }

    $imap_options = get_option( 'erp_settings_erp-crm_email_connect_imap', [] );

    $mail_server    = $imap_options['mail_server'];
    $username       = $imap_options['username'];
    $password       = $imap_options['password'];
    $protocol       = $imap_options['protocol'];
    $port           = isset( $imap_options['port'] ) ? $imap_options['port'] : 993;
    $authentication = isset( $imap_options['authentication'] ) ? $imap_options['authentication'] : 'ssl';

    try {
        $imap = new \WeDevs\ERP\Imap( $mail_server, $port, $protocol, $username, $password, $authentication );

        $last_checked = get_option( 'erp_crm_inbound_emails_last_checked', date( "d M Y" ) );

        if ( isset( $imap_options['schedule'] ) && $imap_options['schedule'] == 'monthly' ) {
            $date = date( "d M Y", strtotime( "{$last_checked} -1 month" ) );
        } else if ( isset( $imap_options['schedule'] ) && $imap_options['schedule'] == 'weekly' ) {
            $date = date( "d M Y", strtotime( "{$last_checked} -1 week" ) );
        } else {
            $date = date( "d M Y", strtotime( "{$last_checked} -1 days" ) );
        }

        update_option( 'erp_crm_inbound_emails_last_checked', date( "d M Y" ) );

        $emails = $imap->get_emails( "Inbox", "UNSEEN SINCE \"$date\"" );

        do_action( 'erp_crm_new_inbound_emails', $emails );

        $server_host  = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
        $email_regexp = '([a-z0-9]+[.][0-9]+[.][0-9]+[.][r][1|2])@' . $server_host;

        $filtered_emails = [];
        foreach ( $emails as $email ) {
            if ( isset( $email['headers']['References'] ) && preg_match( '/<' . $email_regexp . '>/', $email['headers']['References'], $matches ) ) {

                $filtered_emails[] = $email;

                $message_id       = $matches[1];
                $message_id_parts = explode( '.', $message_id );

                $email['hash'] = $message_id_parts[0];
                $email['cid']  = $message_id_parts[1];
                $email['sid']  = $message_id_parts[2];

                $email['attachments'] = array_map( function( $items ) {
                    $current_item           = [];
                    $current_item['name']   = $items['filename'];
                    $current_item['data']   = $items['attachment'];
                    return $current_item;

                }, $email['attachments'] );
                /*** Save uploaded files start *****/
                $g_uploader           = new \WeDevs\ERP\CRM\Gmail_Sync();
                $email['attachments'] = $g_uploader->save_attachments( $email['attachments'] );
                /*** Save uploaded files end *****/
                // Save & sent the email
                switch ( $message_id_parts[3] ) {
                    case 'r1':
                        $customer_feed_data = erp_crm_save_email_activity( $email, $imap_options['username'] );
                        break;
                    case 'r2':
                        $customer_feed_data = erp_crm_save_contact_owner_email_activity( $email, $imap_options['username'] );
                        break;
                }

                $type          = ( $message_id_parts[3] == 'r2' ) ? 'owner_to_contact' : 'contact_to_owner';
                $email['type'] = $type;

                do_action( 'erp_crm_contact_inbound_email', $email, $customer_feed_data );
            }
        }

        $email_ids = wp_list_pluck( $filtered_emails, 'id' );
        // Mark the emails as seen
        $imap->mark_seen_emails( $email_ids );

    } catch ( \Exception $e ) {
        // $e->getMessage();
    }
}

function erp_crm_poll_gmail() {
    if ( !wperp()->google_auth->is_active() ){
        return;
    }

    wperp()->google_sync->sync();

}

/**
 * Get the contact sources
 *
 * @return array
 */
function erp_crm_contact_sources() {
    $sources = array(
        'advert'             => __( 'Advertisement', 'erp' ),
        'chat'               => __( 'Chat', 'erp' ),
        'contact_form'       => __( 'Contact Form', 'erp' ),
        'employee_referral'  => __( 'Employee Referral', 'erp' ),
        'external_referral'  => __( 'External Referral', 'erp' ),
        'marketing_campaign' => __( 'Marketing campaign', 'erp' ),
        'newsletter'         => __( 'Newsletter', 'erp' ),
        'online_store'       => __( 'OnlineStore', 'erp' ),
        'optin_form'         => __( 'Optin Forms', 'erp' ),
        'partner'            => __( 'Partner', 'erp' ),
        'phone'              => __( 'Phone Call', 'erp' ),
        'public_relations'   => __( 'Public Relations', 'erp' ),
        'sales_mail_alias'   => __( 'Sales Mail Alias', 'erp' ),
        'search_engine'      => __( 'Search Engine', 'erp' ),
        'seminar_internal'   => __( 'Seminar-Internal', 'erp' ),
        'seminar_partner'    => __( 'Seminar Partner', 'erp' ),
        'social_media'       => __( 'Social Media', 'erp' ),
        'trade_show'         => __( 'Trade Show', 'erp' ),
        'web_download'       => __( 'Web Download', 'erp' ),
        'web_research'       => __( 'Web Research', 'erp' ),
    );

    return apply_filters( 'erp_crm_contact_sources', $sources );
}

/**
 * Get contact source dropdown
 *
 * @since 1.0.0
 *
 * @return void
 **/
function erp_crm_contact_source_dropdown( $selected = '' ) {
    $sources  = erp_crm_contact_sources();
    $dropdown = '';

    if ( $sources ) {
        foreach ( $sources as $key => $title ) {
            $dropdown .= sprintf( "<option value='%s'%s>%s</option>\n", $key, selected( $selected, $key, false ), $title );
        }
    }

    return $dropdown;
}

/**
 * Get contact all meta fields
 *
 * @since 1.1.7
 *
 * @return array
 */
function erp_crm_get_contact_meta_fields() {
    // core meta keys
    $core_fields = [
        'life_stage',
        'contact_owner',
        'date_of_birth',
        'contact_age',
        'source'
    ];

    $social_fields = array_keys( erp_crm_get_social_field() );

    return apply_filters( 'erp_crm_contact_meta_fields', array_merge( $core_fields, $social_fields ) );
}

/**
 * Instant sync peoplemeta with wp usermetadata when matches any
 * meta keys of people metakeys
 *
 * @since 1.1.7
 *
 * @param  integer $meta_id
 * @param  integer $object_id
 * @param  string $meta_key
 * @param  array|string $_meta_value
 *
 * @return void
 */
function erp_crm_sync_people_meta_data( $meta_id, $object_id, $meta_key, $_meta_value ) {

    $cache_key          = 'erp_people_id_user_' . $object_id;
    $people_id          = wp_cache_get( $cache_key, 'erp' );
    $people_field       = erp_get_people_main_field();
    $people_meta_fields = erp_crm_get_contact_meta_fields();

    if ( 'not_found' == $people_id ) {
        return;
    }

    if ( false === $people_id ) {
        $people = \WeDevs\ERP\Framework\Models\People::whereUserId( $object_id )->first();

        if ( null == $people ) {
            wp_cache_set( $cache_key, 'not_found', 'erp' );
        } else {
            $people_id = $people->id;
            wp_cache_set( $cache_key, $people_id, 'erp' );
        }
    }

    if ( ! $people_id ) {
        return;
    }

    if ( in_array( $meta_key, $people_field ) ) {
        \WeDevs\ERP\Framework\Models\People::find( $people_id )->update( [ $meta_key => $_meta_value ] );
    }

    if ( in_array( $meta_key, $people_meta_fields ) ) {
        erp_people_update_meta( $people_id, $meta_key, $_meta_value );
    }
}

/**
 * Make crm contact to wp user
 *
 * @since 1.1.7
 * @since 1.1.18 Check if current user has permission to create wp user
 *
 * @param integer $customer_id
 * @param array $args Optional parameter
 *
 * @return void
 **/
function erp_crm_make_wp_user( $customer_id, $args = [] ) {

    if ( ! erp_crm_current_user_can_make_wp_user() ) {
        return new WP_Error( 'invalid-permission', __( 'You do not have permission to make WP User', 'erp' ) );
    }

    if ( ! $customer_id ) {
        return new WP_Error( 'no-ids', __( 'No contact found', 'erp' ) );
    }

    $people = (array) erp_get_people_by( 'id', intval( $customer_id ) );

    $email = ! empty( $people['email'] ) ? $people['email'] : $args['email'];
    $role  = ! empty( $args['role'] ) ? $args['role'] : 'subscriber';
    $type  = ! empty( $args['type'] ) ? $args['type'] : '';

    if ( empty( $email ) ) {
        return new WP_Error( 'no-email', __( 'No email found for creating wp user', 'erp' ) );
    }

    // attempt to create the user
    $userdata = array(
        'user_login'   => $email,
        'user_email'   => $email,
        'first_name'   => ( 'company' == $type ) ? $people['company'] : $people['first_name'],
        'last_name'    => ( 'company' == $type ) ? '' : $people['last_name'],
        'user_url'     => $people['website'],
        'display_name' => ( 'company' == $type ) ? $people['company'] : $people['first_name'] . ' ' . $people['last_name'],
    );

    $userdata['user_pass'] = wp_generate_password( 12 );
    $userdata['role']      = $role;

    $userdata = apply_filters( 'erp_crm_make_wpuser_args', $userdata );
    $user_id  = wp_insert_user( $userdata );

    if ( is_wp_error( $user_id ) ) {
        return $user_id;
    }

    if ( isset( $args['notify_email'] ) && $args['notify_email'] ) {
        wp_send_new_user_notifications( $user_id );
    }

    $people_meta = \WeDevs\ERP\Framework\Models\Peoplemeta::where( 'erp_people_id', $customer_id )->get()->toArray();
    $meta_array  = wp_list_pluck( $people_meta, 'meta_value', 'meta_key' );

    unset( $people['id'], $people['user_id'], $people['website'], $people['email'], $people['created'], $people['types'], $people['first_name'], $people['last_name'], $people['life_stage'], $people['contact_owner'] );
    $people_array = array_merge( $people, $meta_array );

    if ( $people_array ) {
        foreach ( $people_array as $key => $value ) {
            update_user_meta( $user_id, $key, $value );
        }
    }

    \WeDevs\ERP\Framework\Models\People::find( $customer_id )->update( [ 'user_id' => $user_id, 'email' => $email ] );

    return true;
}

/**
 * WP user on delete update contact user id
 *
 * @since 1.1.7
 *
 * @return void
 **/
function erp_crm_contact_on_delete( $user_id, $hard = 0 ) {
    $people = \WeDevs\ERP\Framework\Models\People::where( 'user_id', $user_id )->first();

    if ( ! empty( $people->id ) ) {
        \WeDevs\ERP\Framework\Models\People::find( $people->id )->update( [ 'user_id' => null ] );
    }
}

/**
 * Get default contact owner
 *
 * @since 1.1.17
 *
 * @return int
 */
function erp_crm_get_default_contact_owner() {
    $contact_owner = erp_get_option( 'contact_owner', 'erp_settings_erp-crm_contacts', 0 );

    if ( empty( $contact_owner ) ) {
        $args = [
            'role'    => 'Administrator',
            'fields'  => [ 'ID' ],
            'orderby' => 'ID',
            'order'   => 'ASC',
            'number'  => 1
        ];

        $user_query = new WP_User_Query( $args );

        // User Loop
        if ( ! empty( $user_query->results ) ) {
            foreach ( $user_query->results as $user ) {
                $contact_owner = $user->ID;
            }

        } else {
            $contact_owner = 0;
        }
    }

    return absint( $contact_owner );
}

/**
 * Prevent redirect to woocommerce my account page
 *
 * @param boolean $prevent_access
 *
 * @since 1.1.18
 *
 * @return boolean
 */
function erp_crm_wc_prevent_admin_access( $prevent_access ) {
    if ( current_user_can( erp_crm_get_manager_role() ) || current_user_can( erp_crm_get_agent_role() ) ) {
        return false;
    }

    return $prevent_access;
}

/**
 * Print out option html elements for role selectors.
 *
 * @since 1.2.4
 *
 * @param string $selected
 */
function erp_dropdown_roles( $selected = '' ) {
    $r = '';

    $editable_roles = array_reverse( erp_get_editable_roles() );

    foreach ( $editable_roles as $role => $details ) {
        $name = translate_user_role( $details['name'] );
        // preselect specified role
        if ( $selected == $role ) {
            $r .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
        } else {
            $r .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
        }
    }

    echo wp_kses( $r, array(
        'option' => array(
            'value'    => array(),
            'selected' => array()
        ),
    ) );
}

/**
 * Redirect crm role based user to their page
 *
 * @since 1.2.5
 *
 * @param $redirect_to
 * @param $roles
 *
 * @return string
 */
function erp_crm_login_redirect( $redirect_to, $roles ) {
    $crm_manager = erp_crm_get_manager_role();
    $crm_agent   = erp_crm_get_agent_role();

    if ( in_array( $crm_manager, $roles ) || in_array( $crm_agent, $roles ) ) {
        $redirect_to = get_admin_url( null, 'admin.php?page=erp-crm' );
    }

    return $redirect_to;
}

/**
 * Get customer life stage
 *
 * @since 1.2.7
 *
 * @param $contact_id
 *
 * @return WP_Error | string
 */
function erp_crm_get_life_stage( $contact_id ) {
    $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

    if ( empty( $contact ) ) {
        return new \WP_Error( 'no-erp-people', __( 'People not exists', 'erp' ) );
    }

    return $contact->get_life_stage();

}

/**
 * Update life stage of a customer
 *
 * @since 1.2.7
 *
 * @param $contact_id
 * @param $stage
 *
 * @return bool|string|WP_Error
 */
function erp_crm_update_life_stage( $contact_id, $stage ) {
    $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

    if ( empty( $contact ) ) {
        return new \WP_Error( 'no-erp-people', __( 'People not exists', 'erp' ) );
    }

    return $contact->update_life_stage( $stage );
}

/**
 * Get contact owner
 *
 * @since 1.2.7
 *
 * @param $contact_id
 *
 * @return string|WP_Error
 */
function erp_crm_get_contact_owner( $contact_id ) {
    $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

    if ( empty( $contact ) ) {
        return new \WP_Error( 'no-erp-people', __( 'People not exists', 'erp' ) );
    }

    return $contact->get_contact_owner();
}

/**
 * Update contact owner
 *
 * @since 1.2.7
 *
 * @param $contact_id
 * @param $owner_id
 *
 * @return WP_Error|void
 */
function erp_crm_update_contact_owner( $contact_id, $owner_id, $field_type = 'user_id' ) {
    $people = erp_get_people_by( $field_type, $contact_id );

    if ( empty( $people ) ) {
        return new \WP_Error( 'no-erp-people', __( 'People not exists', 'erp' ) );
    }

    $contact = new \WeDevs\ERP\CRM\Contact( $people->id );

    $contact->update_contact_owner( $owner_id );
}

/**
 * Get all contact groups
 *
 * @since 1.2.7
 * @return array
 */
function erp_crm_get_contact_groups_list() {
    $groups         = \WeDevs\ERP\CRM\Models\ContactGroup::select( 'id', 'name' )->get();
    $contact_groups = apply_filters( 'erp_crm_get_contact_group_list', $groups );

    $list = [];

    foreach ( $contact_groups as $group ) {
        $list[ $group->id ] = $group->name;
    }

    return $list;
}

/*
 * Get contact hash
 *
 * @since 1.2.7
 *
 * @param $contact_id
 *
 * @return string|WP_Error
 */
function erp_crm_get_contact_hash( $contact_id ) {
    $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

    if ( empty( $contact ) ) {
        return new \WP_Error( 'no-erp-people', __( 'People not exists', 'erp' ) );
    }

    return $contact->get_contact_hash();
}

/**
 * Update contact hash
 *
 * @since 1.2.7
 *
 * @param $contact_id
 * @param $hash
 *
 * @return WP_Error|void
 */
function erp_crm_update_contact_hash( $contact_id, $hash ) {
    $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );

    if ( empty( $contact ) ) {
        return new \WP_Error( 'no-erp-people', __( 'People not exists', 'erp' ) );
    }

    $contact->update_contact_hash( $hash );
}

/**
 * Get contact tags
 *
 * @since 1.3.6
 *
 * @param bool $list
 *
 * @return array|int|\WP_Error
 *
 */
function erp_crm_get_contact_tags( $list = true ) {
    $options = [];
    $terms   = get_terms( array(
        'taxonomy'   => 'erp_crm_tag',
        'hide_empty' => false,
    ) );

    if ( is_wp_error( $terms ) ) {
        return $options;
    }

    if ( $list ) {
        foreach ( $terms as $term ) {
            $options[ $term->term_id ] = $term->name;
        }

        return $options;
    }

    return $terms;
}

/**
 * Add crm taxonomy
 * @since 1.3.6
 *
 * @return void
 */
function erp_crm_add_tag_taxonomy(){
    new \WeDevs\ERP\CRM\Contact_Taxonomy( 'erp_crm_tag', 'erp_crm_tag', array(
        'singular' => __( 'Tag',  'erp' ),
        'plural'   => __( 'Tags', 'erp' ),
        'show_ui'   => false,
    ) );
}

/**
 * Check if Inbound Email sync is configured
 *
 * @since 1.14.0
 *
 * @return bool
 */
function erp_crm_sync_is_active() {
    if ( wperp()->google_auth->is_active() ) {
        return true;
    }

    if ( erp_is_imap_active() ) {
        return true;
    }

    return false;
}

/**
 * Send birthday greetings to contact
 *
 * @return void
 */
function erp_crm_send_birthday_greetings() {
        $email =  new WeDevs\ERP\CRM\Emails\Birthday_Greetings();
        $email->trigger();
}

/**
 * Check if contact and company has relation
 *
 * @return void
 */
function erp_crm_check_company_contact_relations($id, $id_type) {
    global $wpdb;

    if ( isset( $id ) && isset( $id_type ) ) {
        if ( !empty( $id_type ) ) {
            if ( $id_type == 'contact') {
                $id_type = 'customer' ;
            }
            $rel_count = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}erp_crm_customer_companies WHERE {$id_type}_id = {$id}" );
            return $rel_count ;
        }
    }
}
