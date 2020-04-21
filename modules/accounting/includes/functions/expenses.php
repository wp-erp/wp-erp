<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all expenses
 *
 * @param $data
 * @return mixed
 */
function erp_acct_get_expenses( $args = [] ) {
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

    if ( '-1' === $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql  = 'SELECT';
    $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
    $sql .= "FROM {$wpdb->prefix}erp_acct_expenses WHERE `trn_by_ledger_id` IS NOT NULL ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql );
    }

    $rows = $wpdb->get_results( $sql, ARRAY_A );

    return $rows;
}

/**
 * Get a single expense
 *
 * @param $expense_no
 * @return mixed
 */
function erp_acct_get_expense( $expense_no ) {
    global $wpdb;

    $sql = "SELECT

    expense.id,
    expense.voucher_no,
    expense.people_id,
    expense.people_name,
    expense.address,
    expense.trn_date,
    expense.amount,
    expense.ref,
    expense.check_no,
    expense.particulars,
    expense.status,
    expense.trn_by_ledger_id,
    expense.trn_by,
    expense.attachments,
    expense.created_at,
    expense.created_by,
    expense.updated_at,
    expense.updated_by

    FROM {$wpdb->prefix}erp_acct_expenses AS expense WHERE expense.voucher_no = {$expense_no}";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['bill_details'] = erp_acct_format_expense_line_items( $expense_no );

    $check_data = erp_acct_get_check_data_of_expense( $expense_no );

    if ( ! empty( $check_data ) ) {
        $row['check_data'] = $check_data;
    }

    return $row;
}

/**
 * Get a single check
 *
 * @param $expense_no
 * @return mixed
 */
function erp_acct_get_check( $expense_no ) {
    global $wpdb;

    $sql = "SELECT

    expense.id,
    expense.voucher_no,
    expense.people_id,
    expense.people_name,
    expense.address,
    expense.trn_date,
    expense.amount,
    expense.particulars,
    expense.status,
    expense.trn_by_ledger_id,
    expense.trn_by,
    expense.check_no,
    expense.attachments,
    expense.created_at,
    expense.created_by,
    expense.updated_at,
    expense.updated_by,

    ledg_detail.debit,
    ledg_detail.credit

    FROM {$wpdb->prefix}erp_acct_expenses AS expense

    LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledg_detail ON expense.voucher_no = ledg_detail.trn_no

    WHERE expense.voucher_no = {$expense_no} AND expense.trn_by = 3";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['bill_details'] = erp_acct_format_check_line_items( $expense_no );

    return $row;
}

/**
 * Format check line items
 */
function erp_acct_format_check_line_items( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
        expense.id,
        expense.voucher_no,
        expense.status,
        expense.trn_by,
        expense.trn_date,

        expense_detail.ledger_id,
        expense_detail.particulars,
        expense_detail.amount,

        ledger.name AS ledger_name

        FROM {$wpdb->prefix}erp_acct_expenses AS expense
        LEFT JOIN {$wpdb->prefix}erp_acct_expense_details AS expense_detail ON expense_detail.trn_no = expense.voucher_no
        LEFT JOIN {$wpdb->prefix}erp_acct_ledgers AS ledger ON expense_detail.ledger_id = ledger.id

        WHERE expense.voucher_no={$voucher_no} AND expense.trn_by = 3",
        $voucher_no
    );

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Format expense line items
 */
function erp_acct_format_expense_line_items( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
        expense_detail.id,
        expense_detail.ledger_id,
        ledger.name AS ledger_name,
        expense_detail.trn_no,
        expense_detail.particulars,
        expense_detail.amount

        FROM {$wpdb->prefix}erp_acct_expenses AS expense
        LEFT JOIN {$wpdb->prefix}erp_acct_expense_details AS expense_detail ON expense.voucher_no = expense_detail.trn_no LEFT JOIN {$wpdb->prefix}erp_acct_ledgers AS ledger ON expense_detail.ledger_id = ledger.id WHERE expense.voucher_no = %d", $voucher_no
    );

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Insert a expense
 *
 * @param $data
 * @return mixed
 */
function erp_acct_insert_expense( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $data['created_at'] = date( 'Y-m-d H:i:s' );
    $data['created_by'] = $created_by;
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $created_by;

    $voucher_no = null;
    $currency   = erp_get_currency(true);

    try {
        $wpdb->query( 'START TRANSACTION' );

        $type = 'expense';
        if ( isset( $data['voucher_type'] ) && 'check' === $data['voucher_type'] ) {
            $type = 'check';
        }

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            array(
				'type'       => $type,
				'currency'   => $currency,
				'created_at' => $data['created_at'],
				'created_by' => $data['created_by'],
				'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
				'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : '',
            )
        );

        $voucher_no = $wpdb->insert_id;

        $expense_data = erp_acct_get_formatted_expense_data( $data, $voucher_no );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_expenses',
            array(
				'voucher_no'       => $expense_data['voucher_no'],
				'people_id'        => $expense_data['people_id'],
				'people_name'      => $expense_data['people_name'],
				'address'          => $expense_data['billing_address'],
				'trn_date'         => $expense_data['trn_date'],
				'amount'           => $expense_data['amount'],
				'ref'              => $expense_data['ref'],
				'check_no'         => $expense_data['check_no'],
				'particulars'      => $expense_data['particulars'],
				'status'           => $expense_data['status'],
				'trn_by'           => $expense_data['trn_by'],
				'trn_by_ledger_id' => $expense_data['trn_by_ledger_id'],
				'attachments'      => $expense_data['attachments'],
				'created_at'       => $expense_data['created_at'],
				'created_by'       => $expense_data['created_by'],
				'updated_at'       => $expense_data['updated_at'],
				'updated_by'       => $expense_data['updated_by'],
            )
        );

        $items = $expense_data['bill_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_expense_details',
                array(
					'trn_no'      => $voucher_no,
					'ledger_id'   => $item['ledger_id'],
					'particulars' => ! empty( $item['particulars'] ) ? $item['particulars'] : '',
					'amount'      => $item['amount'],
					'created_at'  => $expense_data['created_at'],
					'created_by'  => $expense_data['created_by'],
					'updated_at'  => $expense_data['updated_at'],
					'updated_by'  => $expense_data['updated_by'],
                )
            );

            erp_acct_insert_expense_data_into_ledger( $expense_data, $item );
        }

        if ( 1 === $expense_data['status'] ) {
            $wpdb->query( 'COMMIT' );
            if ( 'check' === $type ) {
                return erp_acct_get_check( $voucher_no );
            }

            return erp_acct_get_expense( $voucher_no );
        }

        $check = 3;

        if ( $check == $expense_data['trn_by'] ) {
            erp_acct_insert_check_data( $expense_data );
        } elseif ( 'check' === $type ) {
            erp_acct_insert_check_data( $expense_data );
        }

        if ( 'check' === $type ) {
            erp_acct_insert_source_expense_data_into_ledger( $expense_data );
        } elseif ( isset( $expense_data['trn_by'] ) && 4 === $expense_data['trn_by'] ) {
            do_action( 'erp_acct_expense_people_transaction', $expense_data, $voucher_no );
        } else {
            //Insert into Ledger for source account
            erp_acct_insert_source_expense_data_into_ledger( $expense_data );
        }

        $data['dr'] = 0;
        $data['cr'] = $expense_data['amount'];
        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        do_action( 'erp_acct_after_expense_create', $expense_data, $voucher_no );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'expense-exception', $e->getMessage() );
    }

    $email = erp_get_people_email( $expense_data['people_id'] );

    if ( 'check' === $type ) {
        $check          = erp_acct_get_check( $voucher_no );
        $check['email'] = $email;

        do_action( 'erp_acct_new_transaction_check', $voucher_no, $check );

        return $check;
    }

    $expense          = erp_acct_get_expense( $voucher_no );
    $expense['email'] = $email;

    do_action( 'erp_acct_new_transaction_expense', $voucher_no, $expense );

    return $expense;
}

/**
 * Update a expense
 *
 * @param $data
 * @param $expense_id
 *
 * @return mixed
 */
function erp_acct_update_expense( $data, $expense_id ) {
    global $wpdb;

    if ( $data['convert'] ) {
        erp_acct_convert_draft_to_expense( $data, $expense_id );

        return;
    }

    $updated_by         = get_current_user_id();
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $expense_data = erp_acct_get_formatted_expense_data( $data, $expense_id );

        $wpdb->update(
            $wpdb->prefix . 'erp_acct_expenses',
            array(
				'people_id'        => $expense_data['people_id'],
				'people_name'      => $expense_data['people_name'],
				'address'          => $expense_data['billing_address'],
				'trn_date'         => $expense_data['trn_date'],
				'amount'           => $expense_data['amount'],
				'ref'              => $expense_data['ref'],
				'check_no'         => $expense_data['check_no'],
				'particulars'      => $expense_data['particulars'],
				'trn_by'           => $expense_data['trn_by'],
				'trn_by_ledger_id' => $expense_data['trn_by_ledger_id'],
				'attachments'      => $expense_data['attachments'],
				'updated_at'       => $expense_data['updated_at'],
				'updated_by'       => $expense_data['updated_by'],
            ),
            array(
				'voucher_no' => $expense_id,
            )
        );

        /**
         *? We can't update `expense_details` directly
         *? suppose there were 5 detail rows previously
         *? but on update there may be 2 detail rows
         *? that's why we can't update because the foreach will iterate only 2 times, not 5 times
         *? so, remove previous rows and insert new rows
         */
        $prev_detail_ids = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_acct_expense_details WHERE trn_no = %d", $expense_id ), ARRAY_A );
        $prev_detail_ids = implode( ',', array_map( 'absint', $prev_detail_ids ) );

        $wpdb->delete( $wpdb->prefix . 'erp_acct_expense_details', [ 'trn_no' => $expense_id ] );

        $items = $expense_data['bill_details'];

        foreach ( $items as $key => $item ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_expense_details',
                array(
					'ledger_id'   => $item['ledger_id'],
					'particulars' => $item['particulars'],
					'amount'      => $item['amount'],
					'created_at'  => $expense_data['created_at'],
					'created_by'  => $expense_data['created_by'],
					'updated_at'  => $expense_data['updated_at'],
					'updated_by'  => $expense_data['updated_by'],
                )
            );
        }

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'expense-exception', $e->getMessage() );
    }

    return $expense_id;

}

/**
 * Convert draft to expense
 *
 * @param array $data
 * @param int $expense_id
 *
 * @return array
 */
function erp_acct_convert_draft_to_expense( $data, $expense_id ) {
    global $wpdb;

    $updated_by         = get_current_user_id();
    $data['updated_at'] = date( 'Y-m-d H:i:s' );
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $type = 'expense';
        if ( isset( $data['voucher_type'] ) && 'check' === $data['voucher_type'] ) {
            $type = 'check';
        }

        $expense_data = erp_acct_get_formatted_expense_data( $data, $expense_id );

        $wpdb->update(
            $wpdb->prefix . 'erp_acct_expenses',
            array(
				'people_id'        => $expense_data['people_id'],
				'people_name'      => $expense_data['people_name'],
				'address'          => $expense_data['billing_address'],
				'trn_date'         => $expense_data['trn_date'],
				'amount'           => $expense_data['amount'],
				'ref'              => $expense_data['ref'],
				'check_no'         => $expense_data['check_no'],
				'status'           => $expense_data['status'],
				'particulars'      => $expense_data['particulars'],
				'trn_by'           => $expense_data['trn_by'],
				'trn_by_ledger_id' => $expense_data['trn_by_ledger_id'],
				'attachments'      => $expense_data['attachments'],
				'updated_at'       => $expense_data['updated_at'],
				'updated_by'       => $expense_data['updated_by'],
            ),
            array(
				'voucher_no' => $expense_id,
            )
        );

        /**
         *? We can't update `expense_details` directly
         *? suppose there were 5 detail rows previously
         *? but on update there may be 2 detail rows
         *? that's why we can't update because the foreach will iterate only 2 times, not 5 times
         *? so, remove previous rows and insert new rows
         */
        $prev_detail_ids = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_acct_expense_details WHERE trn_no = %d", $expense_id ), ARRAY_A );
        $prev_detail_ids = implode( ',', array_map( 'absint', $prev_detail_ids ) );

        $wpdb->delete( $wpdb->prefix . 'erp_acct_expense_details', [ 'trn_no' => $expense_id ] );

        $items = $expense_data['bill_details'];

        foreach ( $items as $item ) {
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_expense_details',
                array(
					'ledger_id'   => $item['ledger_id'],
					'particulars' => $item['particulars'],
					'trn_no'      => $expense_id,
					'amount'      => $item['amount'],
					'created_at'  => $expense_data['created_at'],
					'created_by'  => $expense_data['created_by'],
					'updated_at'  => $expense_data['updated_at'],
					'updated_by'  => $expense_data['updated_by'],
                )
            );

            erp_acct_insert_expense_data_into_ledger( $expense_data, $item );
        }

        $check = 3;

        if ( $check == $expense_data['trn_by'] ) {
            erp_acct_insert_check_data( $expense_data );
        } elseif ( 'check' === $type ) {
            erp_acct_insert_check_data( $expense_data );
        }

        if ( 'check' === $type ) {
            erp_acct_insert_source_expense_data_into_ledger( $expense_data );
        } elseif ( isset( $expense_data['trn_by'] ) && 4 === $expense_data['trn_by'] ) {
            do_action( 'erp_acct_expense_people_transaction', $expense_data, $expense_id );
        } else {
            //Insert into Ledger for source account
            erp_acct_insert_source_expense_data_into_ledger( $expense_data );
        }

        $data['dr'] = 0;
        $data['cr'] = $expense_data['amount'];
        erp_acct_insert_data_into_people_trn_details( $data, $expense_id );

        do_action( 'erp_acct_after_expense_create', $expense_data, $expense_id );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'expense-exception', $e->getMessage() );
    }

    $email = erp_get_people_email( $expense_data['people_id'] );

    if ( 'check' === $type ) {
        $check          = erp_acct_get_check( $expense_id );
        $check['email'] = $email;

        do_action( 'erp_acct_new_transaction_check', $expense_id, $check );

        return $check;
    }

    $expense          = erp_acct_get_expense( $expense_id );
    $expense['email'] = $email;

    do_action( 'erp_acct_new_transaction_expense', $expense_id, $expense );

    return $expense;
}

/**
 * Void a expense
 *
 * @param $id
 * @return void
 */
function erp_acct_void_expense( $id ) {
    global $wpdb;

    if ( ! $id ) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_expenses',
        array(
            'status' => 8,
        ),
        array( 'voucher_no' => $id )
    );

    $wpdb->delete( $wpdb->prefix . 'erp_acct_ledger_details', array( 'trn_no' => $id ) );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_expense_details', array( 'trn_no' => $id ) );
}

/**
 * Get formatted expense data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_expense_data( $data, $voucher_no ) {
    $expense_data = [];

    $people  = erp_get_people( $data['people_id'] );
    $company = new \WeDevs\ERP\Company();

    $expense_data['voucher_no']      = ! empty( $voucher_no ) ? $voucher_no : 0;
    $expense_data['people_id']       = isset( $data['people_id'] ) ? $data['people_id'] : get_current_user_id();
    $expense_data['people_name']     = isset( $people ) ? $people->first_name . ' ' . $people->last_name : '';
    $expense_data['billing_address'] = isset( $data['billing_address'] ) ? $data['billing_address'] : '';
    $expense_data['trn_date']        = isset( $data['trn_date'] ) ? $data['trn_date'] : date( 'Y-m-d' );
    $expense_data['amount']          = isset( $data['amount'] ) ? $data['amount'] : 0;
    $expense_data['attachments']     = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $expense_data['ref']             = isset( $data['ref'] ) ? $data['ref'] : '';
    $expense_data['check_no']        = isset( $data['check_no'] ) ? $data['check_no'] : null;
    // translators: %s: voucher_no
    $expense_data['particulars']      = ! empty( $data['particulars'] ) ? $data['particulars'] : sprintf( __( 'Expense created with voucher no %s', 'erp' ), $voucher_no );
    $expense_data['bill_details']     = isset( $data['bill_details'] ) ? $data['bill_details'] : '';
    $expense_data['status']           = isset( $data['status'] ) ? $data['status'] : 1;
    $expense_data['trn_by_ledger_id'] = isset( $data['trn_by_ledger_id'] ) ? $data['trn_by_ledger_id'] : null;
    $expense_data['trn_by']           = isset( $data['trn_by'] ) ? $data['trn_by'] : null;
    $expense_data['created_at']       = date( 'Y-m-d' );
    $expense_data['created_by']       = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $expense_data['updated_at']       = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $expense_data['updated_by']       = isset( $data['updated_by'] ) ? $data['updated_by'] : '';
    $expense_data['pay_to']           = isset( $people ) ? $people->first_name . ' ' . $people->last_name : '';
    $expense_data['name']             = isset( $data['name'] ) ? $data['name'] : $company->name;
    $expense_data['bank']             = isset( $data['bank'] ) ? $data['bank'] : '';
    $expense_data['voucher_type']     = isset( $data['voucher_type'] ) ? $data['voucher_type'] : '';

    return $expense_data;
}


/**
 * Insert expense/s data into ledger
 *
 * @param array $expense_data
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_insert_expense_data_into_ledger( $expense_data, $item_data = [] ) {
    global $wpdb;

    $draft  = 1;
    $people = '4'; // from reimbursement

    if ( $draft === $expense_data['status'] || $people === $expense_data['trn_by'] ) {
        return;
    }

    // Insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $item_data['ledger_id'],
			'trn_no'      => $expense_data['voucher_no'],
			'particulars' => $expense_data['particulars'],
			'debit'       => $item_data['amount'],
			'credit'      => 0,
			'trn_date'    => $expense_data['trn_date'],
			'created_at'  => $expense_data['created_at'],
			'created_by'  => $expense_data['created_by'],
			'updated_at'  => $expense_data['updated_at'],
			'updated_by'  => $expense_data['updated_by'],
        )
    );

}

/**
 * Update expense/s data into ledger
 *
 * @param array $expense_data
 * @param array $expense_no
 * @param array $item_data
 *
 * @return mixed
 */
function erp_acct_update_expense_data_into_ledger( $expense_data, $expense_no, $item_data = [] ) {
    global $wpdb;

    if ( 1 === $expense_data['status'] && ( isset( $expense_data['trn_by'] ) && 4 === $expense_data['trn_by'] ) ) {
        return;
    }

    // Update amount in ledger_details
    $wpdb->update(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $item_data['ledger_id'],
			'particulars' => $expense_data['particulars'],
			'debit'       => $item_data['amount'],
			'credit'      => 0,
			'trn_date'    => $expense_data['trn_date'],
			'created_at'  => $expense_data['created_at'],
			'created_by'  => $expense_data['created_by'],
			'updated_at'  => $expense_data['updated_at'],
			'updated_by'  => $expense_data['updated_by'],
        ),
        array(
			'trn_no' => $expense_no,
        )
    );

}

/**
 * Insert Expense from account data into ledger
 *
 * @param array $expense_data
 *
 * @return void
 */
function erp_acct_insert_source_expense_data_into_ledger( $expense_data ) {
    global $wpdb;

    if ( 1 === $expense_data['status'] && ( isset( $expense_data['trn_by'] ) && 4 === $expense_data['trn_by'] ) ) {
        return;
    }

    // Insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $expense_data['trn_by_ledger_id'],
			'trn_no'      => $expense_data['voucher_no'],
			'particulars' => $expense_data['particulars'],
			'debit'       => 0,
			'credit'      => $expense_data['amount'],
			'trn_date'    => $expense_data['trn_date'],
			'created_at'  => $expense_data['created_at'],
			'created_by'  => $expense_data['created_by'],
			'updated_at'  => $expense_data['updated_at'],
			'updated_by'  => $expense_data['updated_by'],
        )
    );
}

/**
 * Get check data of a expense
 *
 * @param $expense_no
 * @return mixed
 */
function erp_acct_get_check_data_of_expense( $expense_no ) {
    global $wpdb;

    $sql = "SELECT
    cheque.bank,
    cheque.check_no,
    cheque.trn_no,
    cheque.name,
    cheque.pay_to,
    cheque.bank,
    cheque.amount,

    ledg_detail.debit,
    ledg_detail.credit

    FROM {$wpdb->prefix}erp_acct_expense_checks AS cheque
    LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledg_detail ON cheque.trn_no = ledg_detail.trn_no

    WHERE cheque.trn_no = {$expense_no}";

    $row = $wpdb->get_row( $sql, ARRAY_A );

    return $row;
}


