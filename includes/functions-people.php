<?php
/**
 * This is a file for peoples API
 *
 * Across the WP-ERP ecosystem, we will need various type of users who are not
 * actually WordPress users. In CRM, Accounting and many other parts, we will
 * need customers, clients, companies, vendors and many such type of users. This
 * is basically a unified way of letting every other components to use the same
 * functionality.
 *
 * Also we are taking advantange of WordPress metadata API to handle various
 * unknown types of data using the meta_key/meta_value system.
 */

/**
 * Get all peoples
 *
 * @since 1.0
 *
 * @param $args array
 *
 * @return array
 */
function erp_get_peoples( $args = [] ) {
    global $wpdb;

    $defaults = [
        'type'       => 'all',
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'trashed'    => false,
        'meta_query' => [],
        'count'      => false,
        'include'    => [],
        'exclude'    => [],
        's'          => '',
        'no_object'  => false
    ];

    $args        = wp_parse_args( $args, $defaults );
    $people_type = is_array( $args['type'] ) ? implode( '-', $args['type'] ) : $args['type'];
    $cache_key   = 'erp-people-' . $people_type . '-' . md5( serialize( $args ) );
    $items       = wp_cache_get( $cache_key, 'erp' );

    $pep_tb      = $wpdb->prefix . 'erp_peoples';
    $pepmeta_tb  = $wpdb->prefix . 'erp_peoplemeta';
    $types_tb    = $wpdb->prefix . 'erp_people_types';
    $type_rel_tb = $wpdb->prefix . 'erp_people_type_relations';
    $users_tb    = $wpdb->users;
    $usermeta_tb = $wpdb->usermeta;

    $pep_fileds  = [ 'first_name', 'last_name', 'company', 'phone', 'mobile',
            'other', 'fax', 'notes', 'street_1', 'street_2', 'city', 'state', 'postal_code', 'country',
            'currency', 'created' ];

    if ( false === $items ) {
        extract( $args );

        $sql         = [];
        $type_sql    = ( $type != 'all' ) ? "and `name` = '" . $type ."'" : '';
        $trashed_sql = $trashed ? "`deleted_at` is not null" : "`deleted_at` is null";

        $sql['select'][] = "SELECT people.id, people.user_id, people.company,COALESCE( people.email, users.user_email ) AS email,
                COALESCE( people.website, users.user_url ) AS website,";

        $sql['join'][] = "LEFT JOIN $users_tb AS users ON people.user_id = users.ID";

        foreach ( $pep_fileds as $key => $field ) {
            $sql['select'][] = "COALESCE( people.$field, $field.meta_value ) AS $field,";
            $sql['join'][]   = "LEFT JOIN $usermeta_tb AS $field ON people.user_id = $field.user_id AND $field.meta_key = '$field'";
        }

        $sql['select'][] = "GROUP_CONCAT( t.name SEPARATOR ',') AS types";
        $sql['join'][]   = "LEFT JOIN $type_rel_tb AS r ON people.id = r.people_id LEFT JOIN $types_tb AS t ON r.people_types_id = t.id";
        $sql_from_tb     = "FROM $pep_tb AS people";

        $sql['where'][] = "where ( select count(*) from $types_tb
                    inner join  $type_rel_tb
                        on $types_tb.`id` = $type_rel_tb.`people_types_id`
                        where $type_rel_tb.`people_id` = people.`id` $type_sql and $trashed_sql
                  ) >= 1";

        $sql_group_by = "GROUP BY `people`.`id`";
        $sql_order_by = "ORDER BY $orderby $order";

        // Check if want all data without any pagination
        $sql_limit = ( $number != '-1' && !$count ) ? "LIMIT $number OFFSET $offset" : '';

        if ( $meta_query ) {
            $sql['join'][] = "LEFT JOIN $pepmeta_tb as people_meta on people.id = people_meta.`erp_people_id`";

            $meta_key      = isset( $meta_query['meta_key'] ) ? $meta_query['meta_key'] : '';
            $meta_value    = isset( $meta_query['meta_value'] ) ? $meta_query['meta_value'] : '';
            $compare       = isset( $meta_query['compare'] ) ? $meta_query['compare'] : '=';

            $sql['where'][] = "AND people_meta.meta_key='$meta_key' and people_meta.meta_value='$meta_value'";
        }

        // Check is the row want to search
        if ( ! empty( $s ) ) {
            $sql['where'][] = "AND ( first_name.meta_value LIKE '%$s%' OR people.first_name LIKE '%$s%')";
            $sql['where'][] = "OR ( last_name.meta_value LIKE '%$s%' OR people.last_name LIKE '%$s%')";
            $sql['where'][] = "OR ( people.company LIKE '%$s%')";
        }

        // Check if args count true, then return total count customer according to above filter
        if ( $count ) {
            unset( $sql['select'] );
            $sql_group_by = '';
            $sql['select'][] = 'SELECT COUNT( DISTINCT people.id ) as total_number';
        }

        $sql         = apply_filters( 'erp_people_pre_query', $sql, $args );
        $final_query = implode( ' ', $sql['select'] ) . ' ' . $sql_from_tb . ' ' . implode( ' ', $sql['join'] ) . ' ' . implode( ' ', $sql['where'] ) . ' ' . $sql_group_by . ' ' . $sql_order_by . ' ' . $sql_limit;

        if ( $count ) {
            // Only filtered total count of people
            $items = $wpdb->get_var( apply_filters( 'erp_people_total_count_query', $final_query, $args ) );
        } else {
            // Fetch results from people table
            $results = $wpdb->get_results( apply_filters( 'erp_people_total_query', $final_query, $args ), ARRAY_A );

            array_walk( $results, function( &$results ) {
                $results['types'] = explode(',', $results['types'] );
            });

            $items = ( $no_object ) ? $results : erp_array_to_object( $results );
        }

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

/**
 * People data delete
 *
 * @since 1.0
 *
 * @param  array  $data
 *
 * @return void
 */
function erp_delete_people( $data = [] ) {

    if ( empty( $data['id'] ) ) {
        return new WP_Error( 'not-ids', __( 'No data found', 'erp' ) );
    }

    if ( empty( $data['type'] ) ) {
        return new WP_Error( 'not-types', __( 'No type found', 'erp' ) );
    }

    $people_ids = [];

    if ( is_array( $data['id'] ) ) {
        foreach ( $data['id'] as $key => $id ) {
            $people_ids[] = $id;
        }
    } else if ( is_int( $data['id'] ) ) {
        $people_ids[] = $data['id'];
    }

    // still do we have any ids to delete?
    if ( ! $people_ids ) {
        return;
    }

    // seems like we got some
    foreach ( $people_ids as $people_id ) {

        do_action( 'erp_before_delete_people', $people_id, $data );

        if ( $data['hard'] ) {
            $people   = \WeDevs\ERP\Framework\Models\People::find( $people_id );
            $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $data['type'] )->first();
            $people->removeType( $type_obj );

            $types  = wp_list_pluck( $people->types->toArray(), 'name' );

            if ( empty( $types ) ) {
                $people->delete();
                \WeDevs\ERP\Framework\Models\Peoplemeta::where( 'erp_people_id', $people_id )->delete();
            }

        } else {
            $people   = \WeDevs\ERP\Framework\Models\People::with('types')->find( $people_id );
            $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $data['type'] )->first();
            $people->softDeleteType( $type_obj );
        }

        do_action( 'erp_after_delete_people', $people_id, $data );
    }
}

/**
 * People Restore
 *
 * @since 1.0
 *
 * @param  array $data
 *
 * @return void
 */
function erp_restore_people( $data ) {

    if ( empty( $data['id'] ) ) {
        return new WP_Error( 'not-ids', __( 'No data found', 'erp' ) );
    }

    if ( empty( $data['type'] ) ) {
        return new WP_Error( 'not-types', __( 'No type found', 'erp' ) );
    }

    $people_ids = [];

    if ( is_array( $data['id'] ) ) {
        foreach ( $data['id'] as $key => $id ) {
            $people_ids[] = $id;
        }
    } else if ( is_int( $data['id'] ) ) {
        $people_ids[] = $data['id'];
    }

    // still do we have any ids to delete?
    if ( ! $people_ids ) {
        return;
    }

    // seems like we got some
    foreach ( $people_ids as $people_id ) {

        do_action( 'erp_before_restoring_people', $people_id, $data );

        $people   = \WeDevs\ERP\Framework\Models\People::with('types')->find( $people_id );
        $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $data['type'] )->first();
        $people->restore( $type_obj );

        do_action( 'erp_after_restoring_people', $people_id, $data );
    }
}

/**
 * Get peoples by a given field
 *
 * @since 1.0
 *
 * @param  string $field
 * @param  mixed  $value
 *
 * @return array
 */
function erp_get_peoples_by( $field, $value ) {
    if ( is_array( $value ) ) {
        $peoples = WeDevs\ERP\Framework\Models\People::whereIn( $field, $value )->get();
    } else {
        $peoples = WeDevs\ERP\Framework\Models\People::where( $field, $value )->get();
    }

    return erp_array_to_object( $peoples->toArray() );
}

/**
 * Get users as array
 *
 * @since 1.0
 *
 * @param  array   $args
 *
 * @return array
 */
function erp_get_peoples_array( $args = [] ) {
    $users   = [];
    $peoples = erp_get_peoples( $args );

    foreach ( $peoples as $user ) {
        $users[ $user->id ] = ( in_array( 'company', $user->types ) ) ? $user->company : $user->first_name . ' ' . $user->last_name;
    }

    return $users;
}

/**
 * Fetch people count from database
 *
 * @since 1.0
 *
 * @param string $type
 *
 * @return int
 */
function erp_get_peoples_count( $type = 'contact' ) {
    $cache_key = 'erp-people-count-' . $type;
    $count     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $count ) {
        $count = WeDevs\ERP\Framework\Models\People::type( $type )->count();

        wp_cache_set( $cache_key, $count, 'erp' );
    }

    return intval( $count );
}

/**
 * Fetch a single people from database
 *
 * @since 1.0
 *
 * @param int   $id
 *
 * @return array
 */
function erp_get_people( $id = 0 ) {
    return erp_get_people_by( 'id', $id );
}

/**
 * Retrieve people info by a given field
 *
 * @param  string  $field
 * @param  mixed  $value
 *
 * @return object
 */
function erp_get_people_by( $field, $value ) {

    if ( ! in_array( $field, [ 'id', 'email'] ) ) {
        return new WP_Error( 'not-valid-field', __( 'No valid type provided', 'erp' ) );
    }

    $cache_key = 'erp-people-single-' . $value;
    $people    = wp_cache_get( $cache_key, 'erp' );

    if ( false === $people ) {

        if ( 'id' == $field ) {
            $peep = WeDevs\ERP\Framework\Models\People::with('types')->find( intval( $value ) );

        } elseif ( 'email' == $field ) {
            $peep = WeDevs\ERP\Framework\Models\People::with('types')->whereEmail( $value )->first();
        }

        if ( NULL !== $peep ) {
            $people                = (object) $peep->toArray();
            $people->types         = wp_list_pluck( $peep->types->toArray(), 'name' );

            // include meta fields
            $people->date_of_birth = erp_people_get_meta( $peep->id, 'date_of_birth', true );
            $people->source        = erp_people_get_meta( $peep->id, 'source', true );

            wp_cache_set( $cache_key, $people, 'erp' );
        }
    }

    return $people;
}

/**
 * Insert a new people
 *
 * @param array $args
 *
 * @return mixed integer on success, false otherwise
 */
function erp_insert_people( $args = array() ) {

    $defaults = array(
        'id'          => null,
        'first_name'  => '',
        'last_name'   => '',
        'email'       => '',
        'company'     => '',
        'phone'       => '',
        'mobile'      => '',
        'other'       => '',
        'website'     => '',
        'fax'         => '',
        'notes'       => '',
        'street_1'    => '',
        'street_2'    => '',
        'city'        => '',
        'state'       => '',
        'postal_code' => '',
        'country'     => '',
        'currency'    => '',
        'type'        => '',
        'user_id'     => 0,
    );

    $args        = wp_parse_args( $args, $defaults );
    $people_type = $args['type'];

    unset( $args['type'] );

    if ( 'contact' == $people_type ) {
        if ( empty( $args['user_id'] ) ) {
            // some basic validation
            // Check if contact first name and last name provide or not
            if ( empty( $args['first_name'] ) ) {
                return new WP_Error( 'no-first_name', __( 'No First Name provided.', 'erp' ) );
            }
            if ( empty( $args['last_name'] ) ) {
                return new WP_Error( 'no-last_name', __( 'No Last Name provided.', 'erp' ) );
            }
        }
    }

    // Check if company name provide or not
    if ( 'company' == $people_type ) {
        if ( empty( $args['company'] ) ) {
            return new WP_Error( 'no-company', __( 'No Company Name provided.', 'erp' ) );
        }
    }

    // Check if not empty and valid email
    if ( ! empty( $args['email'] ) && ! is_email( $args['email'] ) ) {
        return new WP_Error( 'invalid-email', __( 'Please provide a valid email address', 'erp' ) );
    }

    // remove row id to determine if new or update
    $row_id = (int) $args['id'];
    unset( $args['id'] );

    if ( ! $row_id ) {

        $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $people_type )->first();

        // check if a valid people type exists in the database
        if ( null === $type_obj ) {
            return new WP_Error( 'no-type_found', __( 'The people type is invalid.', 'erp' ) );
        }

        // if an empty type provided
        if ( '' == $people_type ) {
            return new WP_Error( 'no-type', __( 'No user type provided.', 'erp' ) );
        }

        if ( $args['user_id'] ) {
             $user = \get_user_by( 'id', $args['user_id'] );
        } else {
             $user = \get_user_by( 'email', $args['email'] );
        }

        //check for duplicate user
        if ( $user ) {
            $people_obj = \WeDevs\ERP\Framework\Models\People::where( 'user_id', $user->ID )->first();

            // Check if exist in wp user table but not people table
            if ( null == $people_obj ) {
                $new_people = \WeDevs\ERP\Framework\Models\People::create( [ 'user_id' => $user->ID, 'created' => current_time('mysql') ] );
                $new_people->assignType( $type_obj );
                return $new_people->id;
            } else {
                return $people_obj->id;
            }
        } else {
            $people_obj = \WeDevs\ERP\Framework\Models\People::whereEmail( $args['email'] )->first();

            // Check already email exist in contact table
            if ( null !== $people_obj ) {

                // Check if person found, then check is same type person or not
                if ( $people_obj->hasType( $people_type ) ) {
                    return new WP_Error( 'type-exist', sprintf( __( 'This %s already exists.', 'erp' ), $people_type ) );
                } else {
                    $people_obj->assignType( $people_type );
                    return $people_obj->id;
                }
            }
        }

        $args['created'] = current_time( 'mysql' );

        // insert a new
        $people = WeDevs\ERP\Framework\Models\People::create( $args );
        $people->assignType( $type_obj );

        do_action( 'erp_create_new_people', $people->id, $args );

        if ( $people->id ) {
            return $people->id;
        }

    } else {

        // do update method here
        WeDevs\ERP\Framework\Models\People::find( $row_id )->update( $args );

        do_action( 'erp_update_people', $row_id, $args );

        return $row_id;
    }

    return false;
}

/**
 * Add meta data field to a people.
 *
 * @since 1.0
 *
 * @param int    $people_id  People id.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added.
 *                           Default false.
 *
 * @return int|false Meta id on success, false on failure.
 */
function erp_people_add_meta( $people_id, $meta_key, $meta_value, $unique = false ) {
    return add_metadata( 'erp_people', $people_id, $meta_key, $meta_value, $unique);
}

/**
 * Retrieve people meta field for a people.
 *
 * @since 1.0
 *
 * @param int    $people_id People id.
 * @param string $key     Optional. The meta key to retrieve. By default, returns
 *                        data for all keys. Default empty.
 * @param bool   $single  Optional. Whether to return a single value. Default false.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function erp_people_get_meta( $people_id, $key = '', $single = false ) {
    return get_metadata( 'erp_people', $people_id, $key, $single);
}

/**
 * Update people meta field based on people id.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and people id.
 *
 * If the meta field for the people does not exist, it will be added.
 *
 * @since 1.0
 *
 * @param int    $people_id  People id.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *                           Default empty.
 *
 * @return int|bool Meta id if the key didn't exist, true on successful update,
 *                  false on failure.
 */
function erp_people_update_meta( $people_id, $meta_key, $meta_value, $prev_value = '' ) {
    return update_metadata( 'erp_people', $people_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Remove metadata matching criteria from a people.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @since 1.0
 *
 * @param int    $people_id  People id.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function erp_people_delete_meta( $people_id, $meta_key, $meta_value = '' ) {
    return delete_metadata( 'erp_people', $people_id, $meta_key, $meta_value);
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
