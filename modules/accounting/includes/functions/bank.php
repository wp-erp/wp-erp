<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all bank accounts
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_banks( $show_balance = false, $with_cash = false, $no_bank = false ) {
    global $wpdb;

    $ledgers = $wpdb->prefix.'erp_acct_ledgers';

    $chart_id = 7; $cash_ledger = ''; $where = '';
    if ( $with_cash && !$no_bank ) {
        $where = " WHERE chart_id = {$chart_id}";
        $cash_ledger = " OR slug = 'cash' ";
    }

    if ( $with_cash && $no_bank ) {
        $where = " WHERE";
        $cash_ledger = " slug = 'cash' ";
    }

    if ( !$with_cash && !$no_bank ) {
        $where = " WHERE chart_id = {$chart_id}";
        $cash_ledger = "";
    }

    if ( !$show_balance ) {
        $query = "SELECT * FROM $ledgers" . $where . $cash_ledger;
        $results = $wpdb->get_results( $query, ARRAY_A );
        return $results;
    }

    $sub_query = "SELECT id FROM $ledgers" . $where . $cash_ledger;
    $ledger_details = $wpdb->prefix.'erp_acct_ledger_details';
    $query = "Select l.id, ld.ledger_id, l.name, SUM(ld.debit - ld.credit) as balance
              From $ledger_details as ld
              LEFT JOIN $ledgers as l ON l.id = ld.ledger_id
              Where ld.ledger_id IN ($sub_query)
              Group BY ld.ledger_id";

    $accts = $wpdb->get_results( $query, ARRAY_A );
    $banks = erp_acct_get_ledgers_by_chart_id( 7 );

    $temp1 = wp_list_pluck( $accts, 'id' );
    $temp2 = wp_list_pluck( $banks, 'id' );

    if ( !count(array_intersect( $temp1, $temp2 ) ) ) {
        $results = array_merge( $accts, $banks );
    } else {
        $results = $accts ;
    }

    return $results;
}

/**
 * Get all accounts to show in dashboard
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_dashboard_banks() {
    global $wpdb;

    $rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "erp_acct_cash_at_banks", ARRAY_A );

    return $rows;
}

/**
 * Get a single bank account
 *
 * @param $bank_no
 * @return mixed
 */
function erp_acct_get_bank( $bank_no ) {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "wp_erp_acct_cash_at_banks WHERE ledger_id = {$bank_no}", ARRAY_A );

    return $row;
}

/**
 * Insert a bank account
 *
 * @param $data
 * @param $bank_id
 * @return int
 */
function erp_acct_insert_bank( $data ) {
    global $wpdb;

    $bank_data = erp_acct_get_formatted_bank_data( $data );

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_cash_at_banks', array(
            'ledger_id' => $bank_data['ledger_id']
        ) );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'bank-account-exception', $e->getMessage() );
    }
    return $bank_data['ledger_id'];

}


/**
 * Delete a bank account
 *
 * @param $id
 * @return int
 */
function erp_acct_delete_bank( $id ) {
    global $wpdb;

    try {
        $wpdb->query( 'START TRANSACTION' );
        $wpdb->delete( $wpdb->prefix . 'erp_acct_cash_at_banks', array( 'ledger_id' => $id ) );
        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'bank-account-exception', $e->getMessage() );
    }

    return $id;
}


/**
 * Get formatted bank data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_bank_data( $data ) {

    $bank_data['ledger_id'] = !empty( $bank_data['ledger_id'] ) ? $bank_data['ledger_id'] : 0;

    return $bank_data;
}

/**
 * Get balance of a single account
 *
 * @param $ledger_id
 *
 */

function erp_acct_get_single_account_balance( $ledger_id ) {
    global $wpdb;

    $result = $wpdb->get_row("SELECT ledger_id, SUM(credit) - SUM(debit) AS 'balance' FROM " . $wpdb->prefix . "erp_acct_ledger_details WHERE ledger_id = {$ledger_id}", ARRAY_A );

    return $result;
}

/**
 * @param $ledger_id
 *
 * @return array
 */
function erp_acct_get_account_debit_credit( $ledger_id ) {
    global $wpdb; $dr_cr = [];

    $dr_cr['debit']  = $wpdb->get_var("SELECT SUM(debit) FROM " . $wpdb->prefix . "erp_acct_ledger_details WHERE ledger_id = {$ledger_id}" );
    $dr_cr['credit'] = $wpdb->get_var("SELECT SUM(credit) FROM " . $wpdb->prefix . "erp_acct_ledger_details WHERE ledger_id = {$ledger_id}" );

    return $dr_cr;

}

/**
 * Perform transfer amount between two account
 *
 * @param $item
 */
function erp_acct_perform_transfer( $item ) {
    global $wpdb;
    $created_by = get_current_user_id();
    $created_at = date("Y-m-d");
    $updated_at = date("Y-m-d");
    $updated_by = $created_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'       => 'transfer_voucher',
            'created_at' => $created_at,
            'created_by' => $created_by,
            'updated_at' => $updated_at,
            'updated_by' => $updated_by,
        ) );

        $voucher_no = $wpdb->insert_id;

        // Inset transfer amount in ledger_details
        $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
            'ledger_id'   => $item['from_account_id'],
            'trn_no'      => $voucher_no,
            'particulars' => $item['particulars'],
            'debit'       => 0,
            'credit'      => $item['amount'],
            'trn_date'    => $item['date'],
            'created_at'  => $created_at,
            'created_by'  => $created_by,
            'updated_at'  => $updated_at,
            'updated_by'  => $updated_by,
        ) );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
            'ledger_id'   => $item['to_account_id'],
            'trn_no'      => $voucher_no,
            'particulars' => $item['particulars'],
            'debit'       => $item['amount'],
            'credit'      => 0,
            'trn_date'    => $item['date'],
            'created_at'  => $created_at,
            'created_by'  => $created_by,
            'updated_at'  => $updated_at,
            'updated_by'  => $updated_by,
        ) );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_transfer_voucher', array(
            'voucher_no' => $voucher_no,
            'amount'     => $item['amount'],
            'ac_from'    => $item['from_account_id'],
            'ac_to'      => $item['to_account_id'],
            'trn_date'   => $item['date'],
            'created_at' => $created_at,
            'created_by' => $created_by,
            'updated_at' => $updated_at,
            'updated_by' => $updated_by,
        ) );

        erp_acct_sync_dashboard_accounts();

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'transfer-exception', $e->getMessage() );
    }

}

/**
 * Sync dashboard account on transfer
 */
function erp_acct_sync_dashboard_accounts() {
    global $wpdb;

    $accounts = erp_acct_get_banks( true, true, false );

    foreach ( $accounts as $account ) {
        $wpdb->update( $wpdb->prefix . 'erp_acct_cash_at_banks', array(
            'balance' => $account['balance'],
        ), array(
            'ledger_id' => $account['ledger_id']
        ));
    }

}

/**
 * Get transferrable accounts
 */
function erp_acct_get_transfer_accounts( $show_balance = false ) {
    /*
    global $wpdb;

    $ledger_map = \WeDevs\ERP\Accounting\Includes\Ledger_Map::getInstance();
    $cash_ledger = $ledger_map->get_ledger_details_by_slug( 'cash' );

    $ledgers = $wpdb->prefix.'erp_acct_ledgers';
    $chart_id = $cash_ledger->chart_id;

    if ( !$show_balance ) {
        $query = $wpdb->prepare( "Select * FROM $ledgers WHERE chart_id = %d", $chart_id );
        $results = $wpdb->get_results( $query, ARRAY_A );
        return $results;
    }

    $sub_query = $wpdb->prepare( "Select id FROM $ledgers WHERE chart_id = %d", $chart_id );
    $cash_ledger = $wpdb->prefix.'erp_acct_ledger_details';
    $query = "Select ld.ledger_id, l.name, SUM(ld.debit - ld.credit) as balance
              From $cash_ledger as ld
              LEFT JOIN $ledgers as l ON l.id = ld.ledger_id
              Where ld.ledger_id IN ($sub_query)
              Group BY ld.ledger_id";
    */

    $results = erp_acct_get_banks( true, true, false );

    return $results;
}

/**
 * Get created Transfer voucher list
 *
 * @param array $args
 *
 * @return array
 */
function erp_acct_get_transfer_vouchers( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'order_by'   => 'id',
        'order'      => 'DESC',
        'count'      => false,
        's'          => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $table = $wpdb->prefix . 'erp_acct_transfer_voucher';
    $query = "Select * From $table ORDER BY {$args['order_by']} {$args['order']} {$limit}";

    $result = $wpdb->get_results( $query, ARRAY_A );

    return $result;
}
/**
 * Get balance by Ledger ID
 *
 * @param $id array
 *
 * @return array
 */
function erp_acct_get_balance_by_ledger( $id ) {
    if ( is_array( $id ) ) {
        $id = "'" . implode( "','", $id ) . "'";
    }

    global $wpdb;
    $table_name = $wpdb->prefix.'erp_acct_ledger_details';
    $query = "Select ld.ledger_id,SUM(ld.debit - ld.credit) as balance From $table_name as ld Where ld.ledger_id IN ($id) Group BY ld.ledger_id ";
    $result = $wpdb->get_results( $query, ARRAY_A );

    return $result;
}
