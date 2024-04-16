<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all bank accounts
 *
 * @param $data
 *
 * @return mixed
 */
function erp_acct_get_banks( $show_balance = false, $with_cash = false, $no_bank = false ) {
    global $wpdb;

    $args               = [];
    $args['start_date'] = gmdate( 'Y-m-d' );

    $closest_fy_date    = erp_acct_get_closest_fn_year_date( $args['start_date'] );
    $args['start_date'] = $closest_fy_date['start_date'];
    $args['end_date']   = $closest_fy_date['end_date'];

    $ledgers   = $wpdb->prefix . 'erp_acct_ledgers';
    $show_all  = false;
    $cash_only = false;
    $bank_only = false;

    $chart_id    = 7;
    $cash_ledger = '';
    $where       = '';

    if ( $with_cash && ! $no_bank ) {
        $where       = $wpdb->prepare( " WHERE chart_id = %d", $chart_id );
        $cash_ledger = " OR slug = 'cash' ";
        $show_all    = true;
    }

    if ( $with_cash && $no_bank ) {
        $where       = ' WHERE';
        $cash_ledger = " slug = 'cash' ";
        $cash_only   = true;
    }

    if ( ! $with_cash && ! $no_bank ) {
        $where       = $wpdb->prepare(  " WHERE chart_id = %d", $chart_id );
        $cash_ledger = '';
        $bank_only   = true;
    }

    if ( ! $show_balance ) {
        $query   = "SELECT * FROM $ledgers" . $where . $cash_ledger;
        $results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        return $results;
    }

    $sub_query      = "SELECT id FROM $ledgers" . $where . $cash_ledger;
    $ledger_details = $wpdb->prefix . 'erp_acct_ledger_details';
    $query          = "Select l.id, ld.ledger_id, l.code, l.name, SUM(ld.debit - ld.credit) as balance
              From $ledger_details as ld
              LEFT JOIN $ledgers as l ON l.id = ld.ledger_id
              Where ld.ledger_id IN ($sub_query)
              Group BY ld.ledger_id";

    $temp_accts = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    if ( $with_cash ) {
        // little hack to solve -> opening_balance cash entry with no ledger_details cash entry
        $cash_ledger = '7';
        $no_cash     = true;

        foreach ( $temp_accts as $temp_acct ) {
            if ( $temp_acct['ledger_id'] === $cash_ledger ) {
                $no_cash = false;
                break;
            }
        }

        if ( $no_cash ) {
            $temp_accts[] = [ 'id' => 7 ];
        }
    }

    $accts      = [];
    $bank_accts = [];
    $uniq_accts = [];

    $ledger_map = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();
    $ledger_id  = $ledger_map->get_ledger_id_by_slug( 'cash' );

    $c_balance = get_ledger_balance_with_opening_balance( $ledger_id, $args['start_date'], $args['end_date'] );
    $balance   = isset( $c_balance->balance ) ? $c_balance->balance : 0;

    foreach ( $temp_accts as $temp_acct ) {
        $bank_accts[] = get_ledger_balance_with_opening_balance( $temp_acct['id'], $args['start_date'], $args['end_date'] );
    }

    if ( $cash_only && ! empty( $accts ) ) {
        return $accts;
    }

    $banks = erp_acct_get_ledgers_by_chart_id( 7 );

    if ( $bank_only && empty( $banks ) ) {
        return new WP_Error( 'rest_empty_accounts', __( 'Bank accounts are empty.' ), [ 'status' => 204 ] );
    }

    foreach ( $banks as $bank ) {
        $bank_accts[] = get_ledger_balance_with_opening_balance( $bank['id'], $args['start_date'], $args['end_date'] );
    }

    $results = array_merge( $accts, $bank_accts );

    foreach ( $results as $index => $result ) {
        if ( ! empty( $uniq_accts ) && in_array( $result['id'], $uniq_accts, true ) ) {
            unset( $results[ $index ] );
            continue;
        }
        $uniq_accts[] = $result['id'];
    }

    return $results;
}

/**
 * Get all accounts to show in dashboard
 *
 * @param $data
 *
 * @return mixed
 */
function erp_acct_get_dashboard_banks() {
    $args               = [];
    $args['start_date'] = gmdate( 'Y-m-d' );

    $closest_fy_date    = erp_acct_get_closest_fn_year_date( $args['start_date'] );
    $args['start_date'] = $closest_fy_date['start_date'];
    $args['end_date']   = $closest_fy_date['end_date'];

    $results = [];

    $ledger_map = \WeDevs\ERP\Accounting\Classes\LedgerMap::get_instance();
    $ledger_id  = $ledger_map->get_ledger_id_by_slug( 'cash' );

    $c_balance = get_ledger_balance_with_opening_balance( $ledger_id, $args['start_date'], $args['end_date'] );

    $results[] = [
        'name'    => __( 'Cash', 'erp' ),
        'balance' => isset( $c_balance['balance'] ) ? $c_balance['balance'] : 0,
    ];

    $results[] = [
        'name'       => __( 'Cash at Bank', 'erp' ),
        'balance'    => erp_acct_cash_at_bank( $args, 'balance' ),
        'additional' => erp_acct_bank_balance( $args, 'balance' ),
    ];

    $results[] = [
        'name'       => __( 'Bank Loan', 'erp' ),
        'balance'    => erp_acct_cash_at_bank( $args, 'loan' ),
        'additional' => erp_acct_bank_balance( $args, 'loan' ),
    ];

    return $results;
}

/**
 * Get a single bank account
 *
 * @param $bank_no
 *
 * @return mixed
 */
function erp_acct_get_bank( $bank_no ) {
    global $wpdb;

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_acct_cash_at_banks WHERE ledger_id = %d", $bank_no ), ARRAY_A );

    return $row;
}

/**
 * Insert a bank account
 *
 * @param $data
 * @param $bank_id
 *
 * @return int
 */
function erp_acct_insert_bank( $data ) {
    global $wpdb;

    $bank_data = erp_acct_get_formatted_bank_data( $data );

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_cash_at_banks',
            [
                'ledger_id' => $bank_data['ledger_id'],
            ]
        );

        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );

        return new WP_error( 'bank-account-exception', $e->getMessage() );
    }

    return $bank_data['ledger_id'];
}

/**
 * Delete a bank account
 *
 * @param $id
 *
 * @return int
 */
function erp_acct_delete_bank( $id ) {
    global $wpdb;

    try {
        $wpdb->query( 'START TRANSACTION' );
        $wpdb->delete( $wpdb->prefix . 'erp_acct_cash_at_banks', [ 'ledger_id' => $id ] );
        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
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
function erp_acct_get_formatted_bank_data( $bank_data ) {
    $bank_data['ledger_id'] = ! empty( $bank_data['ledger_id'] ) ? $bank_data['ledger_id'] : 0;

    return $bank_data;
}

/**
 * Get balance of a single account
 *
 * @param $ledger_id
 */
function erp_acct_get_single_account_balance( $ledger_id ) {
    global $wpdb;

    $result = $wpdb->get_row( $wpdb->prepare( "SELECT ledger_id, SUM(credit) - SUM(debit) AS 'balance' FROM {$wpdb->prefix}erp_acct_ledger_details WHERE ledger_id = %d", $ledger_id ), ARRAY_A );

    return $result;
}

/**
 * @param $ledger_id
 *
 * @return array
 */
function erp_acct_get_account_debit_credit( $ledger_id ) {
    global $wpdb;
    $dr_cr = [];

    $dr_cr['debit']  = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(debit) FROM {$wpdb->prefix}erp_acct_ledger_details WHERE ledger_id = %d", $ledger_id ) );
    $dr_cr['credit'] = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(credit) FROM {$wpdb->prefix}erp_acct_ledger_details WHERE ledger_id = %d", $ledger_id ) );

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
    $created_at = gmdate( 'Y-m-d' );
    $updated_at = gmdate( 'Y-m-d' );
    $updated_by = $created_by;
    $currency   = erp_get_currency( true );

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            [
                'type'       => 'transfer_voucher',
                'currency'   => $currency,
                'created_at' => $created_at,
                'created_by' => $created_by,
                'updated_at' => $updated_at,
                'updated_by' => $updated_by,
            ]
        );

        $voucher_no = $wpdb->insert_id;

        // Inset transfer amount in ledger_details
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
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
            ]
        );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_ledger_details',
            [
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
            ]
        );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_transfer_voucher',
            [
                'voucher_no'  => $voucher_no,
                'amount'      => $item['amount'],
                'ac_from'     => $item['from_account_id'],
                'ac_to'       => $item['to_account_id'],
                'particulars' => $item['particulars'],
                'trn_date'    => $item['date'],
                'created_at'  => $created_at,
                'created_by'  => $created_by,
                'updated_at'  => $updated_at,
                'updated_by'  => $updated_by,
            ]
        );

        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
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
        $wpdb->update(
            $wpdb->prefix . 'erp_acct_cash_at_banks',
            [
                'balance' => $account['balance'],
            ],
            [
                'ledger_id' => $account['ledger_id'],
            ]
        );
    }
}

/**
 * Get transferrable accounts
 */
function erp_acct_get_transfer_accounts( $show_balance = false ) {
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
        'number'   => 20,
        'offset'   => 0,
        'order_by' => 'id',
        'order'    => 'DESC',
        'count'    => false,
        's'        => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    if ( -1 !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_acct_transfer_voucher ORDER BY %s %s %s", $args['order_by'], $args['order'], $limit ), ARRAY_A );

    return $result;
}

/**
 * Get single voucher
 *
 * @param int $id Voucher id
 *
 * @return object Single voucher
 */
function erp_acct_get_single_voucher( $id ) {
    global $wpdb;

    if ( ! $id ) {
        return;
    }

    $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_acct_transfer_voucher WHERE id = %d", $id ) );

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
    global $wpdb;
    $table_name = $wpdb->prefix . 'erp_acct_ledger_details';

    // Generate placeholders based on the number of elements in the $id array
    $placeholders = array_fill( 0, count( $id ), '%s' );
    $placeholder_string = implode( ',', $placeholders );

    $result = $wpdb->get_results( $wpdb->prepare(
        "SELECT ld.ledger_id, SUM(ld.debit - ld.credit) AS balance 
        FROM $table_name AS ld 
        WHERE ld.ledger_id IN ($placeholder_string) 
        GROUP BY ld.ledger_id",
        $id
    ), ARRAY_A );

    return $result;
}

/**
 * Get bank accounts dropdown with cash
 *
 * @param $id array
 *
 * @return array
 */
function erp_acct_get_bank_dropdown() {
    $accounts = [];
    $banks    = erp_acct_get_banks( true, true, false );

    if ( $banks ) {
        foreach ( $banks as $bank ) {
            $accounts[ $bank['id'] ] = sprintf( '%s', $bank['name'] );
        }
    }

    return $accounts;
}
