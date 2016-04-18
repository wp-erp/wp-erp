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
        'order'      => 'ASC',
        'trashed'    => false,
        'meta_query' => [],
        'count'      => false,
        'include'    => [],
        'exclude'    => []
    ];

    $args        = wp_parse_args( $args, $defaults );
    $people_type = is_array( $args['type'] ) ? implode( '-', $args['type'] ) : $args['type'];
    $cache_key   = 'erp-people-' . $people_type . '-' . md5( serialize( $args ) );
    $items       = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $people = new WeDevs\ERP\Framework\Models\People();

        // Check if want all data without any pagination
        if ( ( $args['number'] != '-1' ) && ! $args['count'] ) {
            $people = $people->skip( $args['offset'] )->take( $args['number'] );
        }

        // Check if meta query apply
        if ( ! empty( $args['meta_query'] ) ) {

            $people_tb     = $wpdb->prefix . 'erp_peoples';
            $peoplemeta_tb = $wpdb->prefix . 'erp_peoplemeta';

            $meta_key      = isset( $args['meta_query']['meta_key'] ) ? $args['meta_query']['meta_key'] : '';
            $meta_value    = isset( $args['meta_query']['meta_value'] ) ? $args['meta_query']['meta_value'] : '';
            $compare       = isset( $args['meta_query']['compare'] ) ? $args['meta_query']['compare'] : '=';

            $people = $people->leftjoin( $peoplemeta_tb, $people_tb . '.id', '=', $peoplemeta_tb . '.erp_people_id' )->select( array( $people_tb . '.*', $peoplemeta_tb . '.meta_key', $peoplemeta_tb . '.meta_value' ) )
                        ->where( $peoplemeta_tb . '.meta_key', $meta_key )
                        ->where( $peoplemeta_tb . '.meta_value', $compare, $meta_value );
        }

        // Check if render only soft deleted row
        if ( $args['trashed'] ) {
            $people = $people->onlyTrashed();
        }

        // Check is the row want to search
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $arg_s = $args['s'];
            $people = $people->where( 'first_name', 'LIKE', "%$arg_s%" )
                    ->orWhere( 'last_name', 'LIKE', "%$arg_s%" )
                    ->orWhere( 'company', 'LIKE', "%$arg_s%" );
        }

        $people = apply_filters( 'erp_people_query_object', $people );

        // Render all collection of data according to above filter (Main query)
        $items = $people->with('types')
                ->type( $args['type'] )
                ->orderBy( $args['orderby'], $args['order'] )
                ->get()
                ->toArray();

        $results = [];

        if ( $items ) {
            foreach ( $items as $key => $item ) {
                $item['types'] = wp_list_pluck( $item['types'], 'name' );
                $results[$key] = $item;
            }
        }

        $items = erp_array_to_object( $results );

        // Check if args count true, then return total count customer according to above filter
        if ( $args['count'] ) {
            $items = $people->type( $args['type'] )->count();
        }

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
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
            $peep = WeDevs\ERP\Framework\Models\People::withTrashed()->with('types')->find( intval( $value ) );

        } elseif ( 'email' == $field ) {
            $peep = WeDevs\ERP\Framework\Models\People::withTrashed()->with('types')->whereEmail( $value )->first();
        }

        if ( $peep->id ) {
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

        // if an empty type provided
        if ( '' == $people_type ) {
            return new WP_Error( 'no-type', __( 'No user type provided.', 'erp' ) );
        }

        //check for duplicate user
        if ( $user = get_user_by( 'email', $args['email'] ) ) {
            $people_obj = \WeDevs\ERP\Framework\Models\People::whereUserId( $user->ID )->first();

            // Check if exist in wp user table but not people table
            if ( null == $people_obj ) {
                return \WeDevs\ERP\Framework\Models\People::create( [ 'user_id' => $user->ID ] );
            } else {
                return $people_obj->id;
            }
        } else {
            $people_obj = \WeDevs\ERP\Framework\Models\People::whereEmail( $args['email'] )->first();

            // Check already email exist in contact table
            if ( null !== $people_obj ) {

                // Check if person found, then check is same type person or not
                if ( $people_obj->hasType( $args['type'] ) ) {
                    return new WP_Error( 'type-exist', sprintf( __( 'This %s already exists.', 'erp' ), $args['type'] ) );
                } else {
                    $people_obj->assignType( $args['type'] );
                    return $people_obj->id;
                }
            }
        }

        // check if a valid people type exists in the database
        $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $people_type )->first();

        if ( null === $type_obj ) {
            return new WP_Error( 'no-type_found', __( 'The people type is invalid.', 'erp' ) );
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
