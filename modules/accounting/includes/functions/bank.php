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
function erp_acct_get_banks() {
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
    $updated_at = date("Y-m-d");
    $updated_by = $created_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'       => 'transfer_voucher',
            'created_at' => date("Y-m-d"),
            'created_by' => $created_by,
            'updated_at' => $updated_at,
            'updated_by' => $updated_by,
        ) );

        $voucher_no = $wpdb->insert_id;

        // Inset transfer amount in ledger_details
        $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
            'ledger_id'   => $item['from_account_id'],
            'trn_no'      => $voucher_no,
            'particulars' => $item['remarks'],
            'debit'       => $item['amount'],
            'credit'      => 0,
            'trn_date'    => $item['date'],
            'created_at'  => date("Y/m/d"),
            'created_by'  => $created_by,
            'updated_at'  => '',
            'updated_by'  => '',
        ) );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
            'ledger_id'   => $item['to_account_id'],
            'trn_no'      => $voucher_no,
            'particulars' => $item['remarks'],
            'debit'       => 0,
            'credit'      => $item['amount'],
            'trn_date'    => $item['date'],
            'created_at'  => date("Y/m/d"),
            'created_by'  => $created_by,
            'updated_at'  => '',
            'updated_by'  => '',
        ) );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'transfer-exception', $e->getMessage() );
    }

}

/**
 * Get transferrable accounts
 */
function erp_acct_get_transfer_accounts( $show_balance = false ) {
    global $wpdb;

    $ledgers = $wpdb->prefix.'erp_acct_ledgers';
    $chart_id = 107;

    if ( !$show_balance ) {
        $query = $wpdb->prepare( "Select * FROM $ledgers WHERE chart_id = %d", $chart_id );
        $results = $wpdb->get_results( $query, ARRAY_A );
        return $results;
    }

    $sub_query = $wpdb->prepare( "Select id FROM $ledgers WHERE chart_id = %d", $chart_id );
    $ledger_details = $wpdb->prefix.'erp_acct_ledger_details';
    $query = "Select ld.ledger_id, l.name, SUM(ld.debit - ld.credit) as balance 
              From $ledger_details as ld
              LEFT JOIN $ledgers as l ON l.id = ld.ledger_id
              Where ld.ledger_id IN ($sub_query) 
              Group BY ld.ledger_id";
    $results = $wpdb->get_results( $query, ARRAY_A );

    return $results;
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
