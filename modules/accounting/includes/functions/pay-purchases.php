<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all pay_purchases
 *
 * @param $data
 *
 * @return mixed
 */
function erp_acct_get_pay_purchases( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'DESC',
        'count'   => false,
        's'       => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    if ( -1 !== $args['number'] ) {
        $limit = $wpdb->prepare( "LIMIT %d OFFSET %d", $args['number'], $args['offset'] );
    }

    $sql  = 'SELECT';
    $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
    $sql .= $wpdb->prepare( "FROM {$wpdb->prefix}erp_acct_pay_purchase where %d=%d ORDER BY {$args['orderby']} {$args['order']} %s", 1, 1, $limit );

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/**
 * Get a pay_purchase
 *
 * @param $purchase_no
 *
 * @return mixed
 */
function erp_acct_get_pay_purchase( $purchase_no ) {
    global $wpdb;

    erp_disable_mysql_strict_mode();

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT
                pay_purchase.id,
                pay_purchase.voucher_no,
                pay_purchase.vendor_id,
                pay_purchase.vendor_name,
                pay_purchase.trn_date,
                pay_purchase.amount,
                pay_purchase.transaction_charge,
                pay_purchase.ref,
                pay_purchase.trn_by,
                pay_purchase.status,
                pay_purchase.particulars,
                pay_purchase.created_at,
                pay_purchase.attachments,
                pay_purchase.trn_by_ledger_id
            FROM {$wpdb->prefix}erp_acct_pay_purchase AS pay_purchase
            WHERE pay_purchase.voucher_no = %d",
            $purchase_no
        ),
        ARRAY_A
    );

    $row['purchase_details'] = erp_acct_format_pay_purchase_line_items( $purchase_no );
    $row['pdf_link']    = erp_acct_pdf_abs_path_to_url( $purchase_no );

    return $row;
}

/**
 * Format pay purchase line items
 */
function erp_acct_format_pay_purchase_line_items( $voucher_no ) {
    global $wpdb;

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}erp_acct_pay_purchase AS pay_purchase
            LEFT JOIN {$wpdb->prefix}erp_acct_pay_purchase_details AS pay_purchase_detail
            ON pay_purchase.voucher_no = pay_purchase_detail.voucher_no
            LEFT JOIN {$wpdb->prefix}erp_acct_voucher_no AS voucher
            ON pay_purchase_detail.voucher_no = voucher.id
            WHERE pay_purchase.voucher_no = %d",
            $voucher_no
        ),
        ARRAY_A
    );
}

/**
 * Insert a pay_purchase
 *
 * @param $data
 * @param $pay_purchase_id
 * @param $due
 *
 * @return mixed
 */
function erp_acct_insert_pay_purchase( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;
    $data['updated_at'] = gmdate( 'Y-m-d H:i:s' );
    $data['updated_by'] = $created_by;
    $voucher_no         = null;
    $currency           = erp_get_currency( true );

    try {
        $wpdb->query( 'START TRANSACTION' );

        if ( floatval( $data['amount'] ) < 0 ) {
            $trn_type = 'pay_purchase';
        } else {
            $trn_type = 'receive_pay_purchase';
        }

        //create voucher
        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            [
                'type'       => $trn_type,
                'currency'   => $currency,
                'created_at' => $data['created_at'],
                'created_by' => $data['created_by'],
                'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : gmdate( 'Y-m-d' ),
                'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : get_current_user_id(),
            ]
        );

        $voucher_no = $wpdb->insert_id;

        $pay_purchase_data = erp_acct_get_formatted_pay_purchase_data( $data, $voucher_no );

        // check transaction charge
        $transaction_charge = 0;

        if ( isset( $pay_purchase_data['bank_trn_charge'] ) && 0 < (float)$pay_purchase_data['bank_trn_charge'] && 2 === (int)$pay_purchase_data['trn_by'] ) {
            $transaction_charge = (float)$pay_purchase_data['bank_trn_charge'];
        }

        $wpdb->insert(
        $wpdb->prefix . 'erp_acct_pay_purchase',
            [
            'voucher_no'         => $voucher_no,
            'vendor_id'          => $pay_purchase_data['vendor_id'],
            'vendor_name'        => $pay_purchase_data['vendor_name'],
            'trn_date'           => $pay_purchase_data['trn_date'],
            'ref'                => $pay_purchase_data['ref'],
            'amount'             => abs( floatval( $pay_purchase_data['amount'] ) ),
            'trn_by'             => $pay_purchase_data['trn_by'],
            'transaction_charge' => $transaction_charge,
            'trn_by_ledger_id'   => $pay_purchase_data['trn_by_ledger_id'],
            'particulars'        => $pay_purchase_data['particulars'],
            'attachments'        => $pay_purchase_data['attachments'],
            'status'             => $pay_purchase_data['status'],
            'created_at'         => $pay_purchase_data['created_at'],
            'created_by'         => $created_by,
            'updated_at'         => $pay_purchase_data['updated_at'],
            'updated_by'         => $pay_purchase_data['updated_by'],
           ]
        );

        $items = $pay_purchase_data['purchase_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_pay_purchase_details',
                [
                    'voucher_no'  => $voucher_no,
                    'purchase_no' => $item['voucher_no'],
                    'amount'      => abs( floatval( $item['line_total'] ) ),
                    'created_at'  => $pay_purchase_data['created_at'],
                    'created_by'  => $created_by,
                    'updated_at'  => $pay_purchase_data['updated_at'],
                    'updated_by'  => $pay_purchase_data['updated_by'],
                ]
            );

            erp_acct_insert_pay_purchase_data_into_ledger( $pay_purchase_data, $item );
        }

        // add transaction charge entry to ledger
        if ( $transaction_charge ) {
            erp_acct_insert_bank_transaction_charge_into_ledger( $pay_purchase_data );
        }


        if ( 1 === $pay_purchase_data['status'] ) {
            $wpdb->query( 'COMMIT' );

            return erp_acct_get_pay_purchase( $voucher_no );
        }

        foreach ( $items as $key => $item ) {
            $debit  = 0;
            $credit = 0;

            if ( floatval( $item['line_total'] ) < 0 ) {
                $debit = abs( floatval( $item['line_total'] ) );
            } else {
                $credit  = floatval( $item['line_total'] );
            }

            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_purchase_account_details',
                [
                    'purchase_no' => $item['voucher_no'],
                    'trn_no'      => $voucher_no,
                    'particulars' => $pay_purchase_data['particulars'],
                    'trn_date'    => $pay_purchase_data['trn_date'],
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'created_at'  => $pay_purchase_data['created_at'],
                    'created_by'  => $created_by,
                    'updated_at'  => $pay_purchase_data['updated_at'],
                    'updated_by'  => $pay_purchase_data['updated_by'],
                ]
            );
        }

        if ( isset( $pay_purchase_data['trn_by'] ) && 3 === $pay_purchase_data['trn_by'] ) {
            erp_acct_insert_check_data( $pay_purchase_data );
        }

        $data['dr'] = 0;
        $data['cr'] = 0;

        if ( floatval( $pay_purchase_data['amount'] ) < 0 ) {
            $data['dr'] = abs( floatval( $pay_purchase_data['amount'] ) );
        } else {
            $data['cr'] = $pay_purchase_data['amount'];
        }

        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        do_action( 'erp_acct_after_pay_purchase_create', $pay_purchase_data, $voucher_no );

        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );

        return new WP_error( 'pay-purchase-exception', $e->getMessage() );
    }

    foreach ( $items as $item ) {
        erp_acct_change_purchase_status( $item['voucher_no'] );
    }

    $pay_purchase = erp_acct_get_pay_purchase( $voucher_no );

    $pay_purchase['email'] = erp_get_people_email( $data['vendor_id'] );

    do_action( 'erp_acct_new_transaction_pay_purchase', $voucher_no, $pay_purchase );

    erp_acct_purge_cache( [ 'list' => 'purchase_transaction' ] );

    return $pay_purchase;
}

/**
 * Update a pay_purchase
 *
 * @param $data
 * @param $pay_purchase_id
 * @param $due
 *
 * @return mixed
 */
function erp_acct_update_pay_purchase( $data, $pay_purchase_id ) {
    global $wpdb;

    $created_by = get_current_user_id();

    try {
        $wpdb->query( 'START TRANSACTION' );

        $pay_purchase_data = erp_acct_get_formatted_pay_purchase_data( $data, $pay_purchase_id );

        $wpdb->update(
            $wpdb->prefix . 'erp_acct_pay_purchase',
            [
                'trn_date'    => $pay_purchase_data['trn_date'],
                'amount'      => abs( $pay_purchase_data['amount'] ),
                'trn_by'      => $pay_purchase_data['trn_by'],
                'particulars' => $pay_purchase_data['particulars'],
                'attachments' => $pay_purchase_data['attachments'],
                'created_at'  => $pay_purchase_data['created_at'],
                'created_by'  => $created_by,
                'updated_at'  => $pay_purchase_data['updated_at'],
                'updated_by'  => $pay_purchase_data['updated_by'],
            ],
            [
                'voucher_no' => $pay_purchase_id,
            ]
        );

        $items = $pay_purchase_data['purchase_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->update(
                $wpdb->prefix . 'erp_acct_pay_purchase_details',
                [
                    'purchase_no' => $item['voucher_no'],
                    'amount'      => abs( $item['amount'] ),
                    'created_at'  => $pay_purchase_data['created_at'],
                    'created_by'  => $created_by,
                    'updated_at'  => $pay_purchase_data['updated_at'],
                    'updated_by'  => $pay_purchase_data['updated_by'],
                ],
                [
                    'voucher_no' => $pay_purchase_id,
                ]
            );

            erp_acct_update_pay_purchase_data_into_ledger( $pay_purchase_data, $pay_purchase_id, $item );
        }

        if ( 1 === $pay_purchase_data['status'] ) {
            $wpdb->query( 'COMMIT' );

            return erp_acct_get_pay_purchase( $pay_purchase_id );
        }

        foreach ( $items as $key => $item ) {
            $debit  = 0;
            $credit = 0;

            if ( floatval( $item['line_total'] ) < 0 ) {
                $debit = abs( floatval( $item['amount'] ) );
            } else {
                $credit  = floatval( $item['amount'] );
            }

            $wpdb->update(
                $wpdb->prefix . 'erp_acct_purchase_account_details',
                [
                    'trn_no'      => $pay_purchase_id,
                    'particulars' => $pay_purchase_data['particulars'],
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'trn_date'    => $pay_purchase_data['trn_date'],
                    'created_at'  => $pay_purchase_data['created_at'],
                    'created_by'  => $created_by,
                    'updated_at'  => $pay_purchase_data['updated_at'],
                    'updated_by'  => $pay_purchase_data['updated_by'],
                ],
                [
                    'purchase_no' => $item['voucher_no'],
                ]
            );
        }

        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );

        return new WP_error( 'pay-purchase-exception', $e->getMessage() );
    }

    foreach ( $items as $item ) {
        erp_acct_change_purchase_status( $item['voucher_no'] );
    }

    erp_acct_purge_cache( [ 'list' => 'purchase_transaction' ] );

    return erp_acct_get_pay_purchase( $pay_purchase_id );
}

/**
 * Void a pay_purchase
 *
 * @param $id
 *
 * @return void
 */
function erp_acct_void_pay_purchase( $id ) {
    global $wpdb;

    if ( ! $id ) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_pay_purchase',
        [
            'status' => 8,
        ],
        [ 'voucher_no' => $id ]
    );

    $wpdb->delete( $wpdb->prefix . 'erp_acct_ledger_details', [ 'trn_no' => $id ] );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_purchase_account_details', [ 'trn_no' => $id ] );

    erp_acct_purge_cache( [ 'list' => 'purchase_transaction' ] );
}

/**
 * Get formatted pay_purchase data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_pay_purchase_data( $data, $voucher_no ) {
    $pay_purchase_data = [];

    $user_data = erp_get_people( $data['vendor_id'] );
    $company   = new \WeDevs\ERP\Company();

    $pay_purchase_data['voucher_no']       = ! empty( $voucher_no ) ? $voucher_no : 0;
    $pay_purchase_data['order_no']         = isset( $data['order_no'] ) ? $data['order_no'] : 1;
    $pay_purchase_data['vendor_id']        = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 1;
    $pay_purchase_data['vendor_name']      = $user_data->first_name . ' ' . $user_data->last_name;
    $pay_purchase_data['purchase_details'] = isset( $data['purchase_details'] ) ? $data['purchase_details'] : '';
    $pay_purchase_data['trn_date']         = isset( $data['trn_date'] ) ? $data['trn_date'] : gmdate( 'Y-m-d' );
    $pay_purchase_data['amount']           = isset( $data['amount'] ) ? $data['amount'] : 0;
    $pay_purchase_data['trn_by']           = isset( $data['trn_by'] ) ? $data['trn_by'] : '';
    $pay_purchase_data['bank_trn_charge']  = isset( $data['bank_trn_charge'] ) ? $data['bank_trn_charge'] : '';
    $pay_purchase_data['attachments']      = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $pay_purchase_data['ref']              = isset( $data['ref'] ) ? $data['ref'] : '';
    $pay_purchase_data['particulars']      = ! empty( $data['particulars'] ) ? $data['particulars'] : sprintf( __( 'Purchase payment created with voucher no %s', 'erp' ), $voucher_no );
    $pay_purchase_data['status']           = isset( $data['status'] ) ? $data['status'] : '';
    $pay_purchase_data['trn_by_ledger_id'] = isset( $data['deposit_to'] ) ? $data['deposit_to'] : null;
    $pay_purchase_data['check_no']         = isset( $data['check_no'] ) ? $data['check_no'] : 0;
    $pay_purchase_data['pay_to']           = isset( $user_data ) ? $user_data->first_name . ' ' . $user_data->last_name : '';
    $pay_purchase_data['name']             = isset( $data['name'] ) ? $data['name'] : $company->name;
    $pay_purchase_data['bank']             = isset( $data['bank'] ) ? $data['bank'] : '';
    $pay_purchase_data['voucher_type']     = isset( $data['voucher_type'] ) ? $data['voucher_type'] : '';
    $pay_purchase_data['created_at']       = gmdate( 'Y-m-d' );
    $pay_purchase_data['created_by']       = isset( $data['created_by'] ) ? $data['created_by'] : get_current_user_id();
    $pay_purchase_data['updated_at']       = isset( $data['updated_at'] ) ? $data['updated_at'] : gmdate( 'Y-m-d' );
    $pay_purchase_data['updated_by']       = isset( $data['updated_by'] ) ? $data['updated_by'] : get_current_user_id();

    return $pay_purchase_data;
}

/**
 * Insert pay_purchase/s data into ledger
 *
 * @param array $pay_purchase_data
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_insert_pay_purchase_data_into_ledger( $pay_purchase_data, $item_data ) {
    global $wpdb;

    if ( 1 === $pay_purchase_data['status'] || ( isset( $pay_purchase_data['trn_by'] ) && 4 === $pay_purchase_data['trn_by'] ) ) {
        return;
    }

    $debit  = 0;
    $credit = 0;

    if ( floatval( $item_data['line_total'] ) < 0 ) {
        $credit = abs( floatval( $item_data['line_total'] ) );
    } else {
        $debit = floatval( $item_data['line_total'] );
    }

    // Insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        [
            'ledger_id'   => $pay_purchase_data['trn_by_ledger_id'],
            'trn_no'      => $pay_purchase_data['voucher_no'],
            'particulars' => $pay_purchase_data['particulars'],
            'debit'       => $debit,
            'credit'      => $credit,
            'trn_date'    => $pay_purchase_data['trn_date'],
            'created_at'  => $pay_purchase_data['created_at'],
            'created_by'  => $pay_purchase_data['created_by'],
            'updated_at'  => $pay_purchase_data['updated_at'],
            'updated_by'  => $pay_purchase_data['updated_by'],
        ]
    );
}

/**
 * Update pay_purchase/s data into ledger
 *
 * @param array $pay_purchase_data
 *                                 * @param array $pay_purchase_no
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_update_pay_purchase_data_into_ledger( $pay_purchase_data, $pay_purchase_no, $item_data ) {
    global $wpdb;

    if ( 1 === $pay_purchase_data['status'] || ( isset( $pay_purchase_data['trn_by'] ) && 4 === $pay_purchase_data['trn_by'] ) ) {
        return;
    }

    $debit  = 0;
    $credit = 0;

    if ( floatval( $item_data['line_total'] ) < 0 ) {
        $credit  = abs( floatval( $item_data['line_total'] ) );
    } else {
        $debit = floatval( $item_data['line_total'] );
    }

    // Update amount in ledger_details
    $wpdb->update(
        $wpdb->prefix . 'erp_acct_ledger_details',
        [
            'ledger_id'   => $pay_purchase_data['trn_by_ledger_id'],
            'particulars' => $pay_purchase_data['particulars'],
            'debit'       => $debit,
            'credit'      => $credit,
            'trn_date'    => $pay_purchase_data['trn_date'],
            'created_at'  => $pay_purchase_data['created_at'],
            'created_by'  => $pay_purchase_data['created_by'],
            'updated_at'  => $pay_purchase_data['updated_at'],
            'updated_by'  => $pay_purchase_data['updated_by'],
        ],
        [
            'trn_no' => $pay_purchase_no,
        ]
    );
}

/**
 * Get Pay purchases count
 *
 * @return int
 */
function erp_acct_get_pay_purchase_count() {
    global $wpdb;

    $row = $wpdb->get_row( 'SELECT COUNT(*) as count FROM ' . $wpdb->prefix . 'erp_acct_pay_purchase' );

    return $row->count;
}

/**
 * Update purchase status after a payment
 *
 * @param $purchase_no
 *
 * @return void
 */
function erp_acct_change_purchase_status( $purchase_no ) {
    global $wpdb;

    $due = erp_acct_get_purchase_due( $purchase_no );

    if ( 0 == $due ) {
        $wpdb->update(
            $wpdb->prefix . 'erp_acct_purchase',
            [
                'status' => 4,
            ],
            [ 'voucher_no' => $purchase_no ]
        );
    } else {
        $wpdb->update(
            $wpdb->prefix . 'erp_acct_purchase',
            [
                'status' => 5,
            ],
            [ 'voucher_no' => $purchase_no ]
        );
    }
}
