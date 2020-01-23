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
 * @since 1.1.14 Add `post_where_queries`
 * @since 1.2.2  Use `erpadvancefilter` filter for $arg['s'] filter
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
        'life_stage' => '',
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

    if ( false === $items ) {
        extract( $args );

        $sql         = [];
        $trashed_sql = $trashed ? "`deleted_at` is not null" : "`deleted_at` is null";

        if ( is_array( $type ) ) {
            $type_sql = "and `name` IN ( '" . implode( "','", $type ) . "' )";
        } else {
            $type_sql = ( $type != 'all' ) ? "and `name` = '" . $type . "'" : '';
        }

        $wrapper_select = "SELECT people.*, ";

        $sql['select'][] = "GROUP_CONCAT( DISTINCT t.name SEPARATOR ',') AS types";
        $sql['join'][]   = "LEFT JOIN $type_rel_tb AS r ON people.id = r.people_id LEFT JOIN $types_tb AS t ON r.people_types_id = t.id";
        $sql_from_tb     = "FROM $pep_tb AS people";
        $sql_people_type = "where ( select count(*) from $types_tb
            inner join  $type_rel_tb
                on $types_tb.`id` = $type_rel_tb.`people_types_id`
                where $type_rel_tb.`people_id` = people.`id` $type_sql and $trashed_sql
          ) >= 1";
        $sql['where']    = [ '' ];

        $sql_group_by = "GROUP BY `people`.`id`";
        $sql_order_by = "ORDER BY $orderby $order";

        // Check if want all data without any pagination
        $sql_limit = ( $number != '-1' && ! $count ) ? "LIMIT $number OFFSET $offset" : '';

        if ( $meta_query ) {
            $sql['join'][] = "LEFT JOIN $pepmeta_tb as people_meta on people.id = people_meta.`erp_people_id`";

            $meta_key   = isset( $meta_query['meta_key'] ) ? $meta_query['meta_key'] : '';
            $meta_value = isset( $meta_query['meta_value'] ) ? $meta_query['meta_value'] : '';
            $compare    = isset( $meta_query['compare'] ) ? $meta_query['compare'] : '=';

            $sql['where'][] = "AND people_meta.meta_key='$meta_key' and people_meta.meta_value='$meta_value'";
        }

        if( !empty($life_stage) ){
            $sql['where'][] = "AND people.life_stage='$life_stage'";
        }

        if( !empty($contact_owner) ){
            $sql['where'][] = "AND people.contact_owner='$contact_owner'";
        }
        if ( current_user_can( 'erp_crm_agent' ) ) {
            $current_user_id = get_current_user_id();
            $sql['where'][] = "AND people.contact_owner='$current_user_id'";
        }

        // Check if the row want to search
        if ( ! empty( $s ) ) {
            $search_like = '%' . $wpdb->esc_like( $s ) . '%';
            $words       = explode( ' ', $s );

            if ( $type === 'contact' ) {
                $args['erpadvancefilter'] = 'first_name[]=~' . implode( '&or&first_name[]=~', $words )
                                            . '&or&last_name[]=~' . implode( '&or&last_name[]=~', $words )
                                            . '&or&email[]=~' . implode( '&or&email[]=~', $words );

            } elseif ( $type === 'company' ) {
                $args['erpadvancefilter'] = 'company[]=~' . implode( '&or&company[]=~', $words )
                                            . '&or&email[]=~' . implode( '&or&email[]=~', $words );

            } elseif ( is_array( $type ) ) {
                $sql['where'][] = $wpdb->prepare(
                    'AND ( people.first_name ) LIKE %s OR ' .
                    '( people.last_name ) LIKE %s',
                    array( $search_like, $search_like )
                );
            } elseif ( $type === 'customer' || $type === 'vendor' ) {
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                    if ( $type === 'customer' ) {
                        $sql['where'][] = $wpdb->prepare(
                            'AND ( people.first_name ) LIKE %s OR ' .
                            '( people.last_name ) LIKE %s',
                            array( $search_like, $search_like )
                        );

                    } else {
                        $sql['where'][] = $wpdb->prepare( 'AND ( people.company ) LIKE %s', array( $search_like ) );
                    }

                } else {
                    $sql['where'][] = $wpdb->prepare(
                        'AND ( people.first_name ) LIKE %s OR ' .
                        '( people.last_name ) LIKE %s OR ' .
                        '( people.email ) LIKE %s OR ' .
                        '( people.company ) LIKE %s',
                        array( $search_like, $search_like, $search_like, $search_like )
                    );
                }

            }
        }

        // Check if args count true, then return total count customer according to above filter
        if ( $count ) {
            $sql_order_by   = '';
            $sql_group_by   = '';
            $wrapper_select = 'SELECT COUNT( DISTINCT people.id ) as total_number';
            unset( $sql['select'][0] );
        }

        $sql = apply_filters( 'erp_get_people_pre_query', $sql, $args );

        $post_where_queries = '';
        if ( ! empty( $sql['post_where_queries'] ) ) {
            $post_where_queries = 'AND ( 1 = 1 '
                                  . implode( ' ', $sql['post_where_queries'] )
                                  . ' )';
        }

        $final_query = $wrapper_select . ' '
                       . implode( ' ', $sql['select'] ) . ' '
                       . $sql_from_tb . ' '
                       . implode( ' ', $sql['join'] ) . ' '
                       . $sql_people_type . ' '
                       . 'AND ( 1=1 '
                       . implode( ' ', $sql['where'] ) . ' '
                       . ' )'
                       . $post_where_queries
                       . $sql_group_by . ' '
                       . $sql_order_by . ' '
                       . $sql_limit;

        if ( $count ) {
            // Only filtered total count of people
            $items = $wpdb->get_var( apply_filters( 'erp_get_people_total_count_query', $final_query, $args ) );
        } else {
            // Fetch results from people table
            $results = $wpdb->get_results( apply_filters( 'erp_get_people_total_query', $final_query, $args ), ARRAY_A );
            array_walk( $results, function ( &$results ) {
                $results['types'] = explode( ',', $results['types'] );
            } );

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
 * @param  array $data
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

            $types = wp_list_pluck( $people->types->toArray(), 'name' );

            if ( empty( $types ) ) {
                $people->delete();
                \WeDevs\ERP\Framework\Models\Peoplemeta::where( 'erp_people_id', $people_id )->delete();
                \WeDevs\ERP\CRM\Models\ContactSubscriber::where( 'user_id', $people_id )->delete();
            }

        } else {
            $people   = \WeDevs\ERP\Framework\Models\People::with( 'types' )->find( $people_id );
            $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $data['type'] )->first();
            $people->softDeleteType( $type_obj );
        }

        do_action( 'erp_after_delete_people', $people_id, $data );
        // e.g.: erp_acct_delete_customer, erp_acct_delete_vendor
        do_action( "erp_acct_delete_{$data['type']}", $data );
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

        $people   = \WeDevs\ERP\Framework\Models\People::with( 'types' )->find( $people_id );
        $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $data['type'] )->first();
        $people->restore( $type_obj );

        do_action( 'erp_after_restoring_people', $people_id, $data );
    }
}

/**
 * Get users as array
 *
 * @since 1.0
 *
 * @param  array $args
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
 * @param int $id
 *
 * @return array
 */
function erp_get_people( $id = 0 ) {
    return erp_get_people_by( 'id', $id );
}

/**
 * Retrieve people info by a given field
 *
 * @param  string $field
 * @param  mixed $value
 *
 * @return object
 */
function erp_get_people_by( $field, $value ) {
    global $wpdb;

    if ( empty( $field ) ) {
        return new WP_Error( 'no-field', __( 'No field provided', 'erp' ) );
    }

    if ( empty( $value ) ) {
        return new WP_Error( 'no-value', __( 'No value provided', 'erp' ) );
    }

    $cache_key = 'erp-people-by-' . md5( serialize( $value ) );
    $people    = wp_cache_get( $cache_key, 'erp' );

    if ( false === $people ) {

        $sql = "SELECT people.*, ";
        $sql .= "GROUP_CONCAT(DISTINCT p_types.name) as types
        FROM {$wpdb->prefix}erp_peoples as people
        LEFT JOIN {$wpdb->prefix}erp_people_type_relations as p_types_rel on p_types_rel.people_id = people.id
        LEFT JOIN {$wpdb->prefix}erp_people_types as p_types on p_types.id = p_types_rel.people_types_id
        ";

        if ( is_array( $value ) ) {
            $separeted_values = "'" . implode( "','", $value ) . "'";
            $sql              .= " WHERE `people`.$field IN ( $separeted_values )";
        } else {
            $sql .= " WHERE `people`.$field = '$value'";
        }

        $sql .= " GROUP BY people.id ";

        $results = $wpdb->get_results( $sql );

        $results = array_map( function ( $item ) {
            $item->types = explode( ',', $item->types );

            return $item;
        }, $results );

        if ( is_array( $value ) ) {
            $people = erp_array_to_object( $results );
        } else {
            $people = ( ! empty( $results ) ) ? $results[0] : false;
        }

        wp_cache_set( $cache_key, $people, 'erp' );
    }

    return $people;
}

/**
 * Insert a new people
 *
 * @since 1.0.0
 * @since 1.2.2  Insert people hash key if not exists one
 * @since 1.2.7  contact_owner, life_stage, hash brought to main table
 * @since 1.2.7  Assign first name as company name for accounting customer search
 * @since 1.3.13 Pass $people_type in create and update people hooks
 *
 * @param array $args erp_insert_people
 *
 * @return mixed integer on success, false otherwise
 */
function erp_insert_people( $args = array(), $return_object = false ) {

    if ( empty( $args['id'] ) ) {
        $args['id'] = 0;
    }

    $existing_people = \WeDevs\ERP\Framework\Models\People::firstOrNew( [ 'id' => $args['id'] ] );

    $defaults = array(
        'id'            => $existing_people->id,
        'first_name'    => $existing_people->first_name,
        'last_name'     => $existing_people->last_name,
        'email'         => $existing_people->email,
        'company'       => $existing_people->company,
        'phone'         => $existing_people->phone,
        'mobile'        => $existing_people->mobile,
        'other'         => $existing_people->other,
        'website'       => $existing_people->website,
        'fax'           => $existing_people->fax,
        'notes'         => $existing_people->notes,
        'street_1'      => $existing_people->street_1,
        'street_2'      => $existing_people->street_2,
        'city'          => $existing_people->city,
        'state'         => $existing_people->state,
        'postal_code'   => $existing_people->postal_code,
        'country'       => $existing_people->country,
        'currency'      => $existing_people->currency,
        'user_id'       => $existing_people->user_id,
        'contact_owner' => $existing_people->contact_owner,
        'life_stage'    => $existing_people->life_stage,
        'hash'          => $existing_people->hash,
        'type'          => ''
    );

    $args           = wp_parse_args( $args, $defaults );


    $errors         = [];
    $unchanged_data = [];

    $people_type = $args['type'];
    unset( $args['type'], $args['created'] );

    //sensitization
    $args['email'] = strtolower( trim( $args['email'] ));

    // Assign first name as company name for accounting customer search
    if ( $people_type == 'company' ) {
        $args['first_name'] = $args['company'];
        $args['last_name'] = '(company)';
    }

    if ( ! $existing_people->id ) {
        // if an empty type provided
        if ( '' == $people_type ) {
            return new WP_Error( 'no-type', __( 'No user type provided.', 'erp' ) );
        }

        // Some validation
        $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $people_type )->first();

        // check if a valid people type exists in the database
        if ( null === $type_obj ) {
            return new WP_Error( 'no-type_found', __( 'The people type is invalid.', 'erp' ) );
        }
    }

    if ( 'contact' == $people_type ) {
        if ( empty( $args['user_id'] ) ) {
            // Check if contact first name or email or phone provided or not
            if ( empty( $args['first_name'] ) || empty( $args['email'] ) ) {
                return new WP_Error( 'no-basic-data', esc_attr__( 'You must need to fill up both first name and email fields', 'erp' ) );
            }
        }
    }

    // Check if company name provide or not
    if ( 'company' == $people_type ) {
        if ( empty( $args['company'] ) || empty( $args['email'] ) ) {
            return new WP_Error( 'no-company', esc_attr__( 'You must need to fill up both Company name and email fields', 'erp' ) );
        }
    }

    // Check if not empty and valid email
    if ( ! empty( $args['email'] ) && ! is_email( $args['email'] ) ) {
        return new WP_Error( 'invalid-email', __( 'Please provide a valid email address', 'erp' ) );
    }

    $errors = apply_filters( 'erp_people_validation_error', [], $args );

    if ( ! empty( $errors ) ) {
        return $errors;
    }

    if ( $args['user_id'] ) {
        $user = \get_user_by( 'id', $args['user_id'] );
    } else {
        $user = \get_user_by( 'email', $args['email'] );
    }

    if ( ! $existing_people->id ) {
        if ( ! $user ) {
            $user             = new stdClass();
            $user->ID         = 0;
            $user->user_url   = '';
            $user->user_email = '';
        }

        $args['created_by'] = get_current_user_id() ? get_current_user_id() : 1;
        $args['hash']       = sha1( microtime() . 'erp-unique-hash-id' . $args['email'] );

        $existing_people_by_email = \WeDevs\ERP\Framework\Models\People::where( 'email', $args['email'] )->first();

        if ( ! empty( $existing_people_by_email->email ) && $existing_people_by_email->hasType( $people_type ) ) {
            $is_existing_people = true;
            $people             = $existing_people_by_email;

        } else if ( ! empty( $existing_people_by_email->email ) && ! $existing_people_by_email->hasType( $people_type ) ) {
            $is_existing_people = true;
            $people             = $existing_people_by_email;

        } else {
            $people = \WeDevs\ERP\Framework\Models\People::create( [
                    'user_id'       => $user->ID,
                    'email'         => ! empty( $args['email'] ) ? $args['email'] : $user->user_email,
                    'website'       => ! empty( $args['website'] ) ? $args['website'] : $user->user_url,
                    'hash'          => $args['hash'],
                    'contact_owner' => $args['contact_owner'],
                    'created_by'    => $args['created_by'],
                    'created'       => current_time( 'mysql' )
                ]
            );
        }

        if ( ! $people->id ) {
            return new WP_Error( 'people-not-created', __( 'Something went wrong, please try again', 'erp' ) );
        }
    } else {
        $existing_people_by_email = \WeDevs\ERP\Framework\Models\People::type( $people_type )->where( 'email', $args['email'] )->first();

        if ( ! empty( $existing_people_by_email->email ) && $existing_people_by_email->id != $existing_people->id ) {
            $is_existing_people = true;
        }

        $people = $existing_people;
    }

    if ( isset( $user->ID ) && $user->ID ) {
        // Set data for updating record
        $user_id = wp_update_user( [
            'ID'         => $user->ID,
            'user_url'   => ! empty( $args['website'] ) ? $args['website'] : $user->user_url,
            'user_email' => ! empty( $args['email'] ) ? $args['email'] : $user->user_email
        ] );

        if ( is_wp_error( $user_id ) ) {
            return new WP_Error( 'update-user', $user_id->get_error_message() );
        } else {
            $people->update( [ 'user_id' => $user_id, 'email' => $args['email'], 'website' => $args['website'], 'contact_owner' => $args['contact_owner'] ] );

            unset( $args['id'], $args['user_id'], $args['email'], $args['website'], $args['contact_owner'], $args['created_by'], $args['hash'] );

            wp_cache_delete( 'erp_people_id_user_' . $user->ID, 'erp' );
            foreach ( $args as $key => $value ) {
                if ( ! update_user_meta( $user_id, $key, $value ) ) {
                    $unchanged_data[ $key ] = $value;
                }
            }
        }
    } else {
        $unchanged_data = $args;
    }

    $main_fields = [];
    $meta_fields = [];

    if ( $unchanged_data ) {
        foreach ( $unchanged_data as $key => $value ) {
            if ( array_key_exists( $key, $defaults ) ) {
                $main_fields[ $key ] = $value;
            } else {
                $meta_fields[ $key ] = $value;
            }
        }
    }

    if ( ! empty( $main_fields ) ) {
        $people->update( $main_fields );
    }

    if ( ! empty( $type_obj ) && ! $people->hasType( $type_obj ) && empty( $is_existing_people ) ) {
        $people->assignType( $type_obj );
    }

    //unset created_by from meta
    unset( $meta_fields['created_by'] );
    if ( ! empty( $meta_fields ) ) {
        $people_metada = array_keys( erp_people_get_meta( $people->id) );
        foreach ( $people_metada as $single_data ) {
            if ( ! array_key_exists( $single_data , $meta_fields ) ) {
                erp_people_delete_meta( $people->id, $single_data );
            }
        }
        foreach ( $meta_fields as $key => $value ) {
            if ( 'raw_data' !== $key ) {
                erp_people_update_meta( $people->id, $key, $value );
            }
        }
    }

    if ( ! $existing_people->id ) {
        do_action( 'erp_create_new_people', $people->id, $args, $people_type );
        // e.g.: erp_acct_after_new_customer, erp_acct_after_new_vendor
        do_action( "erp_acct_after_new_{$people_type}", $people->id, $args );
    } else {
        do_action( 'erp_update_people', $people->id, $args, $people_type );
    }

    if ( ! empty( $is_existing_people ) ) {
        $people->exists = true;
    }

    $hash = $people->hash;
    if ( empty( $hash ) ) {
        $hash_id = sha1( microtime() . 'erp-unique-hash-id' . $people->email );
        $people->update(['hash', $hash_id]);
    }

    return $return_object ? $people : $people->id;
}

/**
 * Add meta data field to a people.
 *
 * @since 1.0
 *
 * @param int $people_id People id.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool $unique Optional. Whether the same key should not be added.
 *                           Default false.
 *
 * @return int|false Meta id on success, false on failure.
 */
function erp_people_add_meta( $people_id, $meta_key, $meta_value, $unique = false ) {
    return add_metadata( 'erp_people', $people_id, $meta_key, $meta_value, $unique );
}

/**
 * Retrieve people meta field for a people.
 *
 * @since 1.0
 *
 * @param int $people_id People id.
 * @param string $key Optional. The meta key to retrieve. By default, returns
 *                        data for all keys. Default empty.
 * @param bool $single Optional. Whether to return a single value. Default false.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data
 *               field if $single is true.
 */
function erp_people_get_meta( $people_id, $key = '', $single = false ) {
    return get_metadata( 'erp_people', $people_id, $key, $single );
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
 * @param int $people_id People id.
 * @param string $meta_key Metadata key.
 * @param mixed $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed $prev_value Optional. Previous value to check before removing.
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
 * @param int $people_id People id.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Optional. Metadata value. Must be serializable if
 *                           non-scalar. Default empty.
 *
 * @return bool True on success, false on failure.
 */
function erp_people_delete_meta( $people_id, $meta_key, $meta_value = '' ) {
    return delete_metadata( 'erp_people', $people_id, $meta_key, $meta_value );
}

/**
 * Get people all main db fields
 *
 * @since 1.1.7
 *
 * @return array
 */
function erp_get_people_main_field() {
    return apply_filters( 'erp_get_people_main_field', [
        'user_id',
        'first_name',
        'last_name',
        'company',
        'email',
        'phone',
        'mobile',
        'other',
        'website',
        'fax',
        'notes',
        'street_1',
        'street_2',
        'city',
        'state',
        'postal_code',
        'country',
        'currency',
        'created_by',
        'life_stage',
        'created'
    ] );
}

/**
 * Convert to ERP People
 *
 * Convert one people type to another or convert wp user to erp people
 *
 * @since 1.1.12
 *
 * @param array $args
 *
 * @return int|object people_id on success and WP_Error object on fail
 */
function erp_convert_to_people( $args = [] ) {

    $type = ! empty( $args['type'] ) ? $args['type'] : 'contact';

    if ( $args['is_wp_user'] && $args['wp_user_id'] ) {
        $wp_user = \get_user_by( 'id', $args['wp_user_id'] );

        $params = [
            'first_name'  => $wp_user->first_name,
            'last_name'   => $wp_user->last_name,
            'email'       => $wp_user->user_email,
            'company'     => get_user_meta( $wp_user->ID, 'company', true ),
            'phone'       => get_user_meta( $wp_user->ID, 'phone', true ),
            'mobile'      => get_user_meta( $wp_user->ID, 'mobile', true ),
            'other'       => get_user_meta( $wp_user->ID, 'other', true ),
            'website'     => $wp_user->user_url,
            'fax'         => get_user_meta( $wp_user->ID, 'fax', true ),
            'notes'       => get_user_meta( $wp_user->ID, 'notes', true ),
            'street_1'    => get_user_meta( $wp_user->ID, 'street_1', true ),
            'street_2'    => get_user_meta( $wp_user->ID, 'street_2', true ),
            'city'        => get_user_meta( $wp_user->ID, 'city', true ),
            'state'       => get_user_meta( $wp_user->ID, 'state', true ),
            'postal_code' => get_user_meta( $wp_user->ID, 'postal_code', true ),
            'country'     => get_user_meta( $wp_user->ID, 'country', true ),
            'currency'    => get_user_meta( $wp_user->ID, 'currency', true ),
            'user_id'     => $wp_user->ID,
            'type'        => $type,
            'photo_id'    => get_user_meta( $wp_user->ID, 'photo_id', true )
        ];

        $people_id = erp_insert_people( $params );

        if ( is_wp_error( $people_id ) ) {
            return $people_id;
        }

    } else {
        $people_obj = \WeDevs\ERP\Framework\Models\People::find( $args['people_id'] );

        if ( empty( $people_obj ) ) {
            return new \WP_Error( 'no-erp-people', __( 'People not exists', 'erp' ) );
        }

        $type_obj = \WeDevs\ERP\Framework\Models\PeopleTypes::name( $type )->first();
        $people_obj->assignType( $type_obj );
        $people_id = $people_obj->id;
    }

    return $people_id;
}

/**
 * Get people email
 *
 * Get people email by id
 *
 * @since 1.4.7
 *
 * @param array $id
 *
 * @return string
 */
function erp_get_people_email( $id ) {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT email FROM {$wpdb->prefix}erp_peoples WHERE id = %d", absint($id) );

    return $wpdb->get_var( $sql );
}
