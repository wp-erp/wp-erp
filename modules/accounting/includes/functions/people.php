<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Insert employee data as people
 *
 * @param $data
 * @param $update
 *
 * @return int
 */
function erp_acct_add_employee_as_people( $data, $update = false ) {
    global $wpdb;
    $people_id = null;

    if ( erp_acct_is_employee_people( $data['user_id'] ) ) {
        return;
    }

    $company = new \WeDevs\ERP\Company();

    if ( $update ) {
        $wpdb->update( $wpdb->prefix . 'erp_peoples', array(
            'first_name'    => $data['personal']['first_name'],
            'last_name'     => $data['personal']['last_name'],
            'company'       => $company->name,
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
            'created'       => '',
        ), array(
            'user_id' => $data['user_id']
        ) );
    } else {
        $wpdb->insert( $wpdb->prefix . 'erp_peoples', array(
            'user_id'       => $data['user_id'],
            'first_name'    => $data['personal']['first_name'],
            'last_name'     => $data['personal']['last_name'],
            'company'       => $company->name,
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
            'created'       => '',
        ) );

        $people_id = $wpdb->insert_id;
    }

    return $people_id;
}

/**
 * Get transaction by date
 *
 * @param integer $people_id
 * @param array $args
 * @return array
 */
function erp_people_filter_transaction( $people_id, $args = [] ) {
    global $wpdb;
    $start_date = isset( $args['start_date'] ) ? $args['start_date'] : '';
    $end_date   = isset( $args['end_date'] ) ? $args['start_date'] : '';

    $rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}erp_acct_people_account_details WHERE trn_date >= '{$start_date}' AND trn_date <= '{$end_date}' AND people_id = {$people_id}", ARRAY_A );
    return $rows;
}

/**
 * Get address of a people
 *
 * @param $people_id
 * @return mixed
 */
function erp_acct_get_people_address( $people_id ) {
    global $wpdb;
    $row = [];

    $sql = $wpdb->prepare( "SELECT

        street_1,
        street_2,
        city,
        state,
        postal_code,
        country

    FROM {$wpdb->prefix}erp_peoples
    WHERE id = %d", $people_id );

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
}

/**
 * Format people address
 */
function erp_acct_format_people_address( $address = [] ) {
    $add = '';

    $keys   = array_keys( $address );
    $values = array_values( $address );

    for ( $idx = 0; $idx < count( $address ); $idx++ ) {
        $add .= $keys[$idx] . ': ' . $values[$idx] . '; ';
    }

    return $add;
}

/**
 * Get all transactions
 *
 * @return mixed
 */
function erp_acct_get_people_transactions( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number' => 20,
        'offset' => 0,
        'order'  => 'ASC',
        'count'  => false,
        's'      => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    $where = '';

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND people.people_id = {$args['people_id']} ";
    }
    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND people.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    } else {
        $args['start_date'] = date( 'Y-m-d', strtotime( 'first day of this month' ) );
        $args['end_date']   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
        $where              .= " AND people.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }
    if ( empty( $args['end_date'] ) ) {
        $args['end_date'] = date( 'Y-m-d', strtotime( 'last day of this month' ) );
    }
    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = "SELECT";

    if ( $args['count'] ) {
        $sql .= " COUNT( DISTINCT people.voucher_no ) AS total_number";
    } else {
        $sql .= "
            voucher.id as voucher_no,
            people.people_id,
            people.trn_no,
            people.trn_date,
            people.debit,
            people.credit,
            people.particulars,
            people.created_at";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        INNER JOIN {$wpdb->prefix}erp_acct_people_account_details AS people ON voucher.id = people.trn_no
        {$where} ORDER BY people.trn_date {$args['order']} {$limit}";

    if ( $args['count'] ) {
        $wpdb->get_results( $sql );
        return $wpdb->num_rows;
    }

    $results = $wpdb->get_results( $sql, ARRAY_A );

    $total    = $o_balance = erp_acct_get_people_opening_balance( $args );
    $dr_total = $cr_total = 0;
    if ( $o_balance > 0 ) {
        $dr_total = (float) $o_balance;
        $temp     = $o_balance . ' Dr';
    } else {
        $cr_total = (float) $o_balance;
        $temp     = $o_balance . ' Cr';
    }

    array_unshift( $results, [
        "voucher_no"  => null,
        "particulars" => "Opening Balance",
        "people_id"   => null,
        "trn_no"      => null,
        "trn_date"    => null,
        "created_at"  => null,
        "debit"       => null,
        "credit"      => null,
        "balance"     => $o_balance
    ] );

    for ( $idx = 0; $idx < count( $results ); $idx++ ) {
        if ( $idx == 0 ) {
            continue;
        }
        $dr_total += (float) $results[$idx]['debit'];
        $cr_total += (float) $results[$idx]['credit'];
        $balance  = (float) $results[$idx - 1]['balance'] + (float) $results[$idx]['debit'] - (float) $results[$idx]['credit'];
        if ( $balance >= 0 ) {
            $results[$idx]['balance'] = (float) $balance . ' Dr';
        } else {
            $results[$idx]['balance'] = (float) $balance . ' Cr';
        }
        $total = $balance;
    }

    $results[0]['balance'] = $temp;

    array_push( $results, [
        "voucher_no"  => null,
        "particulars" => 'Total',
        "people_id"   => null,
        "trn_no"      => null,
        "trn_date"    => null,
        "created_at"  => null,
        "debit"       => $dr_total,
        "credit"      => $cr_total,
        "balance"     => null
    ] );

    return $results;
}

/**
 * Get opening balance
 *
 * @param array $args
 *
 * @return mixed
 */
function erp_acct_get_people_opening_balance( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number' => 20,
        'offset' => 0,
        'order'  => 'ASC',
        'count'  => false,
        's'      => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $where = '';

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " WHERE people_id = {$args['people_id']} ";
    }
    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND trn_date < '{$args['start_date']}'";
    } else {
        $args['start_date'] = date( 'Y-m-d', strtotime( 'first day of january this year' ) );
        $where              .= " AND trn_date < '{$args['start_date']}'";
    }

    $sql = "SELECT SUM(debit - credit) AS opening_balance FROM {$wpdb->prefix}erp_acct_people_account_details {$where}";

    $result = $wpdb->get_row( $sql, ARRAY_A );

    return isset( $result['opening_balance'] ) ? $result['opening_balance'] : 0;
}

/**
 * Get People type by people id
 *
 * @param $people_id
 * @return mixed
 */
function erp_acct_get_people_type_by_id( $people_id ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT people_types_id FROM {$wpdb->prefix}erp_people_type_relations WHERE people_id = {$people_id} LIMIT 1" );

    return erp_acct_get_people_type_by_type_id( $row->people_types_id );
}

/**
 * Get people type by type id
 *
 * @param $type_id
 * @return mixed
 */
function erp_acct_get_people_type_by_type_id( $type_id ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT name FROM {$wpdb->prefix}erp_people_types WHERE id = {$type_id} LIMIT 1" );

    return $row->name;
}

/**
 * Get people id by user id
 *
 * @return mixed
 */
function erp_acct_get_people_id_by_user_id( $user_id ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT id FROM {$wpdb->prefix}erp_peoples WHERE user_id = {$user_id} LIMIT 1" );

    return $row->id;
}

/**
 * Get people id by people_id
 *
 * @return mixed
 */
function erp_acct_get_people_name_by_people_id( $people_id ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT first_name, last_name FROM {$wpdb->prefix}erp_peoples WHERE id = {$people_id} LIMIT 1" );

    return $row->first_name . ' ' . $row->last_name;
}

/**
 * Checks if an employee is people
 *
 * @param $user_id
 *
 * @return boolean
 */
function erp_acct_is_employee_people( $user_id ) {
    global $wpdb;

    if ( ! $user_id ) {
        return false;
    }

    $sql = "SELECT COUNT(1) FROM {$wpdb->prefix}erp_peoples WHERE user_id={$user_id}";

    $wpdb->get_var( $sql );

    $res = $wpdb->get_var( $sql );

    if ( $res == '1' ) {
        return true;
    }

    return false;

}
