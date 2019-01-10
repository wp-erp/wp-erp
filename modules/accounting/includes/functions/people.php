<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Insert employee data as people
 *
 * @param $data
 *
 * @return int
 */
function erp_acct_add_employee_as_people( $data ) {
    global $wpdb;

    $update = false; $people_id = 0;

    if ( isset( $data['id'] ) ) {
        $update = true;
        $people_id = $data['id'];
    }

    if ( $update ) {
        $wpdb->update( $wpdb->prefix . 'erp_peoples', array(
            'user_id'       => $data['user_id'],
            'first_name'    => $data['personal']['first_name'],
            'last_name'     => $data['personal']['last_name'],
            'company'       => $data['company'],
            'email'         => $data['user_email'],
            'phone'         => $data['personal']['phone'],
            'mobile'        => $data['personal']['mobile'],
            'other'         => '',
            'website'       => '',
            'fax'           => '',
            'notes'         => $data['personal']['description'],
            'street_1'      => $data['personal']['street_1'],
            'street_2'      => $data['personal']['street_2'],
            'city'          => $data['personal']['city'],
            'state'         => $data['personal']['state'],
            'postal_code'   => $data['personal']['postal_code'],
            'country'       => $data['personal']['country'],
            'currency'      => '',
            'life_stage'    => '',
            'contact_owner' => '',
            'hash'          => '',
            'created_by'    => get_current_user_id(),
            'created'         => '',
        ), array(
            'id' => $data['id']
        ) );
    } else {
        $wpdb->insert( $wpdb->prefix . 'erp_peoples', array(
            'user_id'       => $data['user_id'],
            'first_name'    => $data['personal']['first_name'],
            'last_name'     => $data['personal']['last_name'],
            'company'       => $data['company'],
            'email'         => $data['user_email'],
            'phone'         => $data['personal']['phone'],
            'mobile'        => $data['personal']['mobile'],
            'other'         => '',
            'website'       => '',
            'fax'           => '',
            'notes'         => $data['personal']['description'],
            'street_1'      => $data['personal']['street_1'],
            'street_2'      => $data['personal']['street_2'],
            'city'          => $data['personal']['city'],
            'state'         => $data['personal']['state'],
            'postal_code'   => $data['personal']['postal_code'],
            'country'       => $data['personal']['country'],
            'currency'      => '',
            'life_stage'    => '',
            'contact_owner' => '',
            'hash'          => '',
            'created_by'    => get_current_user_id(),
            'created'         => '',
        ) );

        $people_id = $wpdb->insert_id;
    }

    return $people_id;
}

/**
 * Get transactions of a people
 *
 * @return mixed
 */

function erp_acct_get_people_transactions( $people_id ) {
    global $wpdb;

    $row = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_people_trn WHERE people_id = {$people_id}", ARRAY_A );
    return $row;
}

/**
 * Get transaction by date
 *
 * @param  integer $people_id
 * @param  array  $args
 * @return array
 */
function erp_people_filter_transaction( $people_id, $args = [] ) {
    global $wpdb;
    error_log( print_r( $people_id,true ) );
    $start_date = isset( $args['start_date'] ) ? $args['start_date'] : '';
    $end_date = isset( $args['end_date'] ) ? $args['start_date'] : '';

    $rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_acct_people_trn WHERE trn_date >= '{$start_date}' AND trn_date <= '{$end_date}' AND people_id = {$people_id}", ARRAY_A );
    return $rows;
}

/**
 * Insert transaction data of a people
 *
 * @param $trn_data
 * @param $people_id
 * @param $voucher_type
 *
 * @return mixed
 */

function erp_acct_insert_people_trn_data( $trn_data, $people_id, $voucher_type ) {
    global $wpdb;

    $wpdb->insert( $wpdb->prefix . 'erp_acct_people_trn', array(
        'people_id'    => $people_id,
        'voucher_no'   => $trn_data['voucher_no'],
        'amount'       => $trn_data['amount'],
        'trn_date'     => $trn_data['trn_date'],
        'trn_by'       => $trn_data['trn_by'],
        'particulars'  => $trn_data['particulars'],
        'voucher_type' => $voucher_type,
        'created_at'   => $trn_data['created_at'],
        'created_by'   => $trn_data['created_by'],
        'updated_at'   => $trn_data['updated_at'],
        'updated_by'   => $trn_data['updated_by'],
    ) );

}
