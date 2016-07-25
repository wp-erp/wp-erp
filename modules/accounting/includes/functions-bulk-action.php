<?php
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
function erp_ac_customer_delete( $data ) {

    if( is_array( $data['id'] ) ) {
        $ids = $data['id'];
    } else {
        $ids = array( intval( $data['id'] ) );
    }

    foreach ( $ids as $key => $user_id ) {
        $people = erp_get_people($user_id);
        if ( ! erp_ac_current_user_can_delete_customer( $people->created_by ) ) {
           unset( $ids[ $key] );
        }
    }

    if ( ! $ids ) {
        return;
    }

    $data['id'] = $ids;

    do_action( 'erp_ac_delete_customer', $data );
    erp_delete_people( $data );

}

function erp_ac_vendor_delete( $data ) {

    if( is_array( $data['id'] ) ) {
        $ids = $data['id'];
    } else {
        $ids = array( intval( $data['id'] ) );
    }

    foreach ( $ids as $key => $user_id ) {
        $people = erp_get_people($user_id);
        if ( ! erp_ac_current_user_can_delete_vendor( $people->created_by ) ) {
           unset( $ids[ $key] );
        }
    }

    if ( ! $ids ) {
        return;
    }

    $data['id'] = $ids;

    do_action( 'erp_ac_delete_vendor', $data );
    erp_delete_people( $data );

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
function erp_ac_customer_restore( $customer_ids ) {
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

function erp_ac_new_customer( $postdata ) {

    $errors      = array();
    $field_id    = isset( $postdata['field_id'] ) ? intval( $postdata['field_id'] ) : 0;

    $first_name  = isset( $postdata['first_name'] ) ? sanitize_text_field( $postdata['first_name'] ) : '';
    $last_name   = isset( $postdata['last_name'] ) ? sanitize_text_field( $postdata['last_name'] ) : '';
    $email       = isset( $postdata['email'] ) ? sanitize_text_field( $postdata['email'] ) : '';
    $company     = isset( $postdata['company'] ) ? sanitize_text_field( $postdata['company'] ) : '';
    $phone       = isset( $postdata['phone'] ) ? sanitize_text_field( $postdata['phone'] ) : '';
    $mobile      = isset( $postdata['mobile'] ) ? sanitize_text_field( $postdata['mobile'] ) : '';
    $other       = isset( $postdata['other'] ) ? sanitize_text_field( $postdata['other'] ) : '';
    $website     = isset( $postdata['website'] ) ? sanitize_text_field( $postdata['website'] ) : '';
    $fax         = isset( $postdata['fax'] ) ? sanitize_text_field( $postdata['fax'] ) : '';
    $notes       = isset( $postdata['notes'] ) ? wp_kses_post( $postdata['notes'] ) : '';
    $street_1    = isset( $postdata['street_1'] ) ? sanitize_text_field( $postdata['street_1'] ) : '';
    $street_2    = isset( $postdata['street_2'] ) ? sanitize_text_field( $postdata['street_2'] ) : '';
    $city        = isset( $postdata['city'] ) ? sanitize_text_field( $postdata['city'] ) : '';
    $state       = isset( $postdata['state'] ) ? sanitize_text_field( $postdata['state'] ) : '';
    $postal_code = isset( $postdata['postal_code'] ) ? sanitize_text_field( $postdata['postal_code'] ) : '';
    $country     = isset( $postdata['country'] ) ? sanitize_text_field( $postdata['country'] ) : '';
    $currency    = isset( $postdata['currency'] ) ? sanitize_text_field( $postdata['currency'] ) : '';
    $type        = isset( $postdata['type'] ) ? sanitize_text_field( $postdata['type'] ) : 'customer';

    if ( $type == 'customer' ) {
        $page_url    = admin_url( 'admin.php?page=erp-accounting-customers' );
    } else {
        $page_url    = admin_url( 'admin.php?page=erp-accounting-vendors' );
    }

    // some basic validation
    if ( $type == 'customer' && ! $first_name ) {
        $errors[] = __( 'Error: First Name is required', 'erp' );
    }

    if ( $type == 'customer' && ! $last_name ) {
        $errors[] = __( 'Error: Last Name is required', 'erp' );
    }

    if ( $type == 'vendor' && ! $company ) {
        $errors[] = __( 'Error: Company Name is required', 'erp' );
    }

    // bail out if error found
    if ( $errors ) {
        $first_error = reset( $errors );
        $redirect_to = add_query_arg( array( 'error' => $first_error ), $page_url );
        wp_safe_redirect( $redirect_to );
        exit;
    }

    $fields = array(
        'first_name'  => $first_name,
        'last_name'   => $last_name,
        'email'       => $email,
        'company'     => $company,
        'phone'       => $phone,
        'mobile'      => $mobile,
        'other'       => $other,
        'website'     => $website,
        'fax'         => $fax,
        'notes'       => $notes,
        'street_1'    => $street_1,
        'street_2'    => $street_2,
        'city'        => $city,
        'state'       => $state,
        'postal_code' => $postal_code,
        'country'     => $country,
        'currency'    => $currency,
        'type'        => $type,
    );

    // New or edit?
    if ( ! $field_id ) {
        if ( $fields['type'] == 'customer' && erp_ac_create_customer() ) {
            $insert_id = erp_insert_people( $fields );
            if ( ! is_wp_error( $insert_id ) ) {
                do_action( 'erp_ac_after_new_customer', $insert_id, $fields );
            }

        } else if ( $fields['type'] == 'vendor' && erp_ac_create_vendor() ) {
            $insert_id = erp_insert_people( $fields );

            if ( ! is_wp_error( $insert_id ) ) {
                do_action( 'erp_ac_after_new_vendor', $insert_id, $fields );
            }

        } else {
            $insert_id = false;
        }

    } else {
        $customer = new \WeDevs\ERP\People( $field_id );

        if ( $fields['type'] == 'customer' && erp_ac_current_user_can_edit_customer( $customer->created_by ) ) {
            $fields['id'] = $field_id;
            $message      = 'update';
            do_action( 'erp_ac_before_update_customer', $fields );
            $insert_id    = erp_insert_people( $fields );
        } else if ( $fields['type'] == 'vendor' && erp_ac_current_user_can_edit_vendor( $customer->created_by ) ) {
            $fields['id'] = $field_id;
            $message      = 'update';
            do_action( 'erp_ac_before_update_vendor', $fields );
            $insert_id    = erp_insert_people( $fields );
        } else {
            $insert_id    = false;
        }
    }

    return $insert_id;
}

