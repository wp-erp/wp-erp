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
function erp_ac_customer_delete( $customer_ids, $hard = false ) {

    if ( empty( $customer_ids ) ) {
        return;
    }

    do_action( 'erp_ac_delete_customer', $customer_ids );

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
    if ( ! $first_name ) {
        $errors[] = __( 'Error: First Name is required', 'accounting' );
    }

    if ( ! $last_name ) {
        $errors[] = __( 'Error: Last Name is required', 'accounting' );
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

        $insert_id = erp_insert_people( $fields );
        do_action( 'erp_ac_after_new_customer', $insert_id, $fields );

    } else {

        $fields['id'] = $field_id;
        $message      = 'update';
        do_action( 'erp_ac_before_update_customer', $fields );
        $insert_id    = erp_insert_people( $fields );

    }

    return $insert_id;
}

