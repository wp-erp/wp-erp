<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all purchases
 *
 * @param array $args
 * @return mixed
 */
function erp_acct_get_purchases( $args = [] ) {
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
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql  = 'SELECT';
    $sql .= $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';
    $sql .= "FROM {$wpdb->prefix}erp_acct_purchase ORDER BY {$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var( $sql );
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get a purchase
 *
 * @param $purchase_no
 * @return mixed
 */
function erp_acct_get_purchase( $purchase_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT

    voucher.editable,
    purchase.id,
    purchase.voucher_no,
    purchase.vendor_id,
    purchase.trn_date,
    purchase.due_date,
    purchase.amount,
    purchase.vendor_name,
    purchase.ref,
    purchase.status,
    purchase.purchase_order,
    purchase.attachments,
    purchase.particulars,
    purchase.created_at,

    purchase_acc_detail.purchase_no,
    purchase_acc_detail.debit,
    purchase_acc_detail.credit

    FROM {$wpdb->prefix}erp_acct_purchase AS purchase
    LEFT JOIN {$wpdb->prefix}erp_acct_voucher_no as voucher ON purchase.voucher_no = voucher.id
    LEFT JOIN {$wpdb->prefix}erp_acct_purchase_account_details AS purchase_acc_detail ON purchase.voucher_no = purchase_acc_detail.trn_no
    WHERE purchase.voucher_no = %d",
        $purchase_no
    );

    $row                = $wpdb->get_row( $sql, ARRAY_A );
    $row['line_items']  = erp_acct_format_purchase_line_items( $purchase_no );
    $row['attachments'] = unserialize( $row['attachments'] );
    $row['total_due']   = $row['credit'] - $row['debit'];

    return $row;
}

/**
 * Purchase items detail
 * @param $voucher_no
 * @return array|object|null
 */
function erp_acct_format_purchase_line_items( $voucher_no ) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT
        purchase_detail.product_id,
        purchase_detail.qty,
        purchase_detail.price,
        purchase_detail.amount,

        product.name,
        product.product_type_id,
        product.category_id,
        product.vendor,
        product.cost_price,
        product.sale_price as unit_price

        FROM {$wpdb->prefix}erp_acct_purchase AS purchase
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase_details AS purchase_detail ON purchase.voucher_no = purchase_detail.trn_no
        LEFT JOIN {$wpdb->prefix}erp_acct_products AS product ON purchase_detail.product_id = product.id
        WHERE purchase.voucher_no = %d",
        $voucher_no
    );

    $results = $wpdb->get_results( $sql, ARRAY_A );

    // calculate every line total
    foreach ( $results as $key => $value ) {
        $results[ $key ]['line_total'] = $value['amount'];
    }

    return $results;
}

/**
 * Insert a purchase
 *
 * @param $data
 * @return mixed
 */
function erp_acct_insert_purchase( $data ) {
    global $wpdb;

    $created_by         = get_current_user_id();
    $voucher_no         = null;
    $data['created_at'] = date( 'Y-m-d' );
    $data['created_by'] = $created_by;
    $data['updated_at'] = date( 'Y-m-d' );
    $data['updated_by'] = $created_by;

    $purchase_type_order = 1;
    $draft               = 1;
    $currency            = erp_get_currency(true);

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_voucher_no',
            array(
				'type'       => 'purchase',
				'currency'   => $currency,
				'editable'   => 1,
				'created_at' => $data['created_at'],
				'created_by' => $created_by,
				'updated_at' => isset( $data['updated_at'] ) ? $data['updated_at'] : '',
				'updated_by' => isset( $data['updated_by'] ) ? $data['updated_by'] : '',
            )
        );

        $voucher_no  = $wpdb->insert_id;
        $purchase_no = $voucher_no;

        $purchase_data = erp_acct_get_formatted_purchase_data( $data, $voucher_no );

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_purchase',
            array(
				'voucher_no'      => $voucher_no,
				'vendor_id'       => $purchase_data['vendor_id'],
				'vendor_name'     => $purchase_data['vendor_name'],
				'billing_address' => $purchase_data['billing_address'],
				'trn_date'        => $purchase_data['trn_date'],
				'due_date'        => $purchase_data['due_date'],
				'amount'          => $purchase_data['amount'],
				'ref'             => $purchase_data['ref'],
				'status'          => $purchase_data['status'],
				'purchase_order'  => $purchase_data['purchase_order'],
				'attachments'     => $purchase_data['attachments'],
				'particulars'     => $purchase_data['particulars'],
				'created_at'      => $purchase_data['created_at'],
				'created_by'      => $created_by,
				'updated_at'      => $purchase_data['updated_at'],
				'updated_by'      => $purchase_data['updated_by'],
            )
        );

        $items = $data['line_items'];

        foreach ( $items as $item ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_details', array(
                'trn_no'     => $voucher_no,
                'product_id' => $item['product_id'],
                'qty'        => $item['qty'],
                'price'      => $item['unit_price'],
                'amount'     => $item['item_total'],
                'created_at' => $purchase_data['created_at'],
                'created_by' => $created_by,
                'updated_at' => $purchase_data['updated_at'],
                'updated_by' => $purchase_data['updated_by']
            ) );
        }

        do_action( 'erp_acct_after_purchase_create', $data, $voucher_no );

        $email = erp_get_people_email( $purchase_data['vendor_id'] );

        if ( $purchase_type_order === $purchase_data['purchase_order'] || $draft === $purchase_data['status'] ) {
            $wpdb->query( 'COMMIT' );
            $purchase_order          = erp_acct_get_purchase( $voucher_no );
            $purchase_order['email'] = $email;
            do_action( 'erp_acct_new_transaction_purchase_order', $voucher_no, $purchase_order );
            return $purchase_order;
        }

        $wpdb->insert(
            $wpdb->prefix . 'erp_acct_purchase_account_details',
            array(
				'purchase_no' => $purchase_no,
				'trn_no'      => $voucher_no,
				'trn_date'    => $purchase_data['trn_date'],
				'particulars' => $purchase_data['particulars'],
				'debit'       => 0,
				'credit'      => $purchase_data['amount'],
				'created_at'  => $purchase_data['created_at'],
				'created_by'  => $created_by,
				'updated_at'  => $purchase_data['updated_at'],
				'updated_by'  => $purchase_data['updated_by'],
            )
        );

        erp_acct_insert_purchase_data_into_ledger( $purchase_data );

        $data['dr'] = 0;
        $data['cr'] = $purchase_data['amount'];
        erp_acct_insert_data_into_people_trn_details( $data, $voucher_no );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'purchase-exception', $e->getMessage() );
    }

    $purchase = erp_acct_get_purchase( $purchase_no );

    $purchase['email'] = $email;

    do_action( 'erp_acct_new_transaction_purchase', $voucher_no, $purchase );

    return $purchase;
}

/**
 * Update a purchase
 *
 * @param $purchase_data
 * @param $purchase_id
 * @return mixed
 */
function erp_acct_update_purchase( $purchase_data, $purchase_id ) {
    global $wpdb;

    if ( 1 === $purchase_data['purchase_order'] && $purchase_data['convert'] ) {
        erp_acct_convert_order_to_purchase( $purchase_data, $purchase_id );

        return;
    }

    $user_id             = get_current_user_id();
    $purchase_type_order = 1;
    $draft               = 1;
    $voucher_no          = null;

    $data['created_at'] = date( 'Y-m-d' );
    $data['created_by'] = $user_id;
    $data['updated_at'] = date( 'Y-m-d' );
    $data['updated_by'] = $user_id;
    $currency           = erp_get_currency(true);

    try {
        $wpdb->query( 'START TRANSACTION' );

        if ( $purchase_type_order === $purchase_data['purchase_order'] || $draft === $purchase_data['status'] ) {
            $purchase_data = erp_acct_get_formatted_purchase_data( $purchase_data, $purchase_id );

            $wpdb->update(
                $wpdb->prefix . 'erp_acct_purchase',
                array(
					'vendor_id'      => $purchase_data['vendor_id'],
					'vendor_name'    => $purchase_data['vendor_name'],
					'trn_date'       => $purchase_data['trn_date'],
					'due_date'       => $purchase_data['due_date'],
					'amount'         => $purchase_data['amount'],
					'ref'            => $purchase_data['ref'],
					'status'         => $purchase_data['status'],
					'purchase_order' => $purchase_data['purchase_order'],
					'attachments'    => $purchase_data['attachments'],
					'particulars'    => $purchase_data['particulars'],
					'created_at'     => $purchase_data['created_at'],
					'created_by'     => $purchase_data['created_by'],
					'updated_at'     => $purchase_data['updated_at'],
					'updated_by'     => $purchase_data['updated_by'],
                ),
                array(
					'voucher_no' => $purchase_id,
                )
            );

            /**
             *? We can't update `purchase_details` directly
             *? suppose there were 5 detail rows previously
             *? but on update there may be 2 detail rows
             *? that's why we can't update because the foreach will iterate only 2 times, not 5 times
             *? so, remove previous rows and insert new rows
             */
            $prev_detail_ids = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_acct_purchase_details WHERE trn_no = %s", $purchase_id ), ARRAY_A );

            $prev_detail_ids = implode( ',', array_map( 'absint', $prev_detail_ids ) );

            $wpdb->delete( $wpdb->prefix . 'erp_acct_purchase_details', [ 'trn_no' => $purchase_id ] );

            $items = $purchase_data['purchase_details'];

            foreach ( $items as $key => $item ) {
                $wpdb->update(
                    $wpdb->prefix . 'erp_acct_purchase_details',
                    [
						'product_id' => $item['product_id'],
						'qty'        => $item['qty'],
						'price'      => $item['unit_price'],
						'amount'     => $item['item_total'],
						'created_at' => $purchase_data['created_at'],
						'created_by' => $purchase_data['created_by'],
						'updated_at' => $purchase_data['updated_at'],
						'updated_by' => $purchase_data['updated_by'],
                    ],
                    [
						'trn_no' => $purchase_id,
					]
                );
            }

            $wpdb->query( 'COMMIT' );
            return erp_acct_get_purchase( $purchase_id );
        } else {
            // disable editing on old bill
            $wpdb->update( $wpdb->prefix . 'erp_acct_voucher_no', [ 'editable' => 0 ], [ 'id' => $purchase_id ] );

            // insert contra voucher
            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_voucher_no',
                array(
					'type'       => 'purchase',
					'currency'   => $currency,
					'editable'   => 0,
					'created_at' => $data['created_at'],
					'created_by' => $data['created_by'],
					'updated_at' => $data['updated_at'],
					'updated_by' => $data['updated_by'],
                )
            );

            $voucher_no = $wpdb->insert_id;

            $old_purchase = erp_acct_get_purchase( $purchase_id );

            // insert contra `erp_acct_purchase` (basically a duplication of row)
            $wpdb->query( $wpdb->prepare( "CREATE TEMPORARY TABLE acct_tmptable SELECT * FROM {$wpdb->prefix}erp_acct_purchase WHERE voucher_no = %d", $purchase_id ) );
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE acct_tmptable SET id = %d, voucher_no = %d, particulars = 'Contra entry for voucher no \#%d', created_at = '%s'",
                    0,
                    $voucher_no,
                    $purchase_id,
                    $data['created_at']
                )
            );
            $wpdb->query( "INSERT INTO {$wpdb->prefix}erp_acct_purchase SELECT * FROM acct_tmptable" );
            $wpdb->query( 'DROP TABLE acct_tmptable' );

            // change purchase status and other things
            $status_closed = 7;
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}erp_acct_purchase SET status = %d, updated_at ='%s', updated_by = %d WHERE voucher_no IN (%d, %d)",
                    $status_closed,
                    $data['updated_at'],
                    $user_id,
                    $purchase_id,
                    $voucher_no
                )
            );

            $items = $old_purchase['line_items'];

            foreach ( $items as $key => $item ) {
                $wpdb->insert(
                    $wpdb->prefix . 'erp_acct_purchase_details',
                    array(
						'trn_no'     => $voucher_no,
						'product_id' => $item['product_id'],
						'qty'        => $item['qty'],
						'price'      => $item['unit_price'],
						'amount'     => (float) $item['qty'] * (float) $item['unit_price'],
						'updated_at' => date( 'Y-m-d H:i:s' ),
						'updated_by' => $user_id,
                    )
                );
            }

            $wpdb->insert(
                $wpdb->prefix . 'erp_acct_purchase_account_details',
                array(
					'purchase_no' => $purchase_id,
					'trn_no'      => $voucher_no,
					'trn_date'    => $purchase_data['trn_date'],
					'particulars' => $purchase_data['particulars'],
					'debit'       => $old_purchase['amount'],
					'updated_at'  => date( 'Y-m-d H:i:s' ),
					'updated_by'  => $user_id,
                )
            );

            do_action( 'erp_acct_after_purchase_update', $purchase_data, $purchase_id );

            erp_acct_update_purchase_data_into_ledger( $items, $purchase_id );

            $new_purchase = erp_acct_insert_purchase( $purchase_data );

            $data['dr'] = 0;
            $data['cr'] = $purchase_data['amount'];
            erp_acct_update_data_into_people_trn_details( $data, $old_purchase['voucher_no'] );
        }

        $wpdb->query( 'COMMIT' );
    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'purchase-exception', $e->getMessage() );
    }

    return erp_acct_get_purchase( $new_purchase['voucher_no'] );
}

/**
 * Convert order to purchase
 *
 * @param array $purchase_data
 * @param int $purchase_id
 *
 * @return array
 */
function erp_acct_convert_order_to_purchase( $purchase_data, $purchase_id ) {
    global $wpdb;

    $user_id = get_current_user_id();

    try {
        $wpdb->query( 'START TRANSACTION' );

        $purchase_data = erp_acct_get_formatted_purchase_data( $purchase_data, $purchase_id );

        // erp_acct_purchase
        $wpdb->update(
            $wpdb->prefix . 'erp_acct_purchase',
            [
                'vendor_id'      => $purchase_data['vendor_id'],
                'vendor_name'    => $purchase_data['vendor_name'],
                'trn_date'       => $purchase_data['trn_date'],
                'due_date'       => $purchase_data['due_date'],
                'amount'         => $purchase_data['amount'],
                'ref'            => $purchase_data['ref'],
                'status'         => 2,
                'purchase_order' => false,
                'attachments'    => $purchase_data['attachments'],
                'particulars'    => $purchase_data['particulars'],
                'created_at'     => $purchase_data['created_at'],
                'created_by'     => $purchase_data['created_by'],
                'updated_at'     => $purchase_data['updated_at'],
                'updated_by'     => $purchase_data['updated_by'],
            ],
            [ 'voucher_no' => $purchase_id ]
        );

        // remove data from erp_acct_purchase_details
        $wpdb->delete( $wpdb->prefix . 'erp_acct_purchase_details', [ 'trn_no' => $purchase_id ] );

        foreach ( $purchase_data['line_items'] as $item ) {
            $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_details', array(
                'trn_no'     => $purchase_id,
                'product_id' => $item['product_id'],
                'qty'        => $item['qty'],
                'price'      => $item['unit_price'],
                'amount'     => (float) $item['qty'] * (float) $item['unit_price'],
                'updated_at' => date( 'Y-m-d' ),
                'updated_by' => $user_id
            ) );
        }

        $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
            'purchase_no' => $purchase_id,
            'trn_no'      => $purchase_id,
            'trn_date'    => $purchase_data['trn_date'],
            'particulars' => $purchase_data['particulars'],
            'credit'      => $purchase_data['amount'],
            'updated_at'  => date( 'Y-m-d' ),
            'updated_by'  => $user_id
        ) );

        erp_acct_insert_purchase_data_into_ledger( $purchase_data );

        $data['dr']        = 0;
        $data['cr']        = $purchase_data['amount'];
        $data['vendor_id'] = $purchase_data['vendor_id'];
        erp_acct_insert_data_into_people_trn_details( $data, $purchase_id );

        $wpdb->query( 'COMMIT' );

    } catch ( Exception $e ) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'purchase-exception', $e->getMessage() );
    }

    $purchase = erp_acct_get_purchase( $purchase_id );

    $purchase['email'] = erp_get_people_email( $purchase['vendor_id'] );

    do_action( 'erp_acct_new_transaction_purchase', $purchase_id, $purchase );

    return $purchase;
}

/**
 * Void a purchase
 *
 * @param $id
 * @return void
 */
function erp_acct_void_purchase( $id ) {
    global $wpdb;

    if ( ! $id ) {
        return;
    }

    $wpdb->update(
        $wpdb->prefix . 'erp_acct_purchase',
        array(
            'status' => 8,
        ),
        array( 'voucher_no' => $id )
    );

    $wpdb->delete( $wpdb->prefix . 'erp_acct_ledger_details', array( 'trn_no' => $id ) );
    $wpdb->delete( $wpdb->prefix . 'erp_acct_purchase_account_details', array( 'purchase_no' => $id ) );
}

/**
 * Get formatted purchase data
 *
 * @param $data
 * @param $voucher_no
 *
 * @return mixed
 */
function erp_acct_get_formatted_purchase_data( $data, $voucher_no ) {
    $user_info = erp_get_people( $data['vendor_id'] );

    $purchase_data['voucher_no']      = isset( $data['voucher_no'] ) ? $data['voucher_no'] : $voucher_no;
    $purchase_data['vendor_id']       = isset( $data['vendor_id'] ) ? $data['vendor_id'] : 0;
    $purchase_data['vendor_name']     = $user_info->first_name . ' ' . $user_info->last_name;
    $purchase_data['billing_address'] = isset( $data['billing_address'] ) ? $data['billing_address'] : '';
    $purchase_data['trn_date']        = isset( $data['trn_date'] ) ? $data['trn_date'] : date( 'Y-m-d' );
    $purchase_data['due_date']        = isset( $data['due_date'] ) ? $data['due_date'] : date( 'Y-m-d' );
    $purchase_data['amount']          = isset( $data['amount'] ) ? floatval( $data['amount'] ) : 0;
    $purchase_data['attachments']     = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $purchase_data['status']          = isset( $data['status'] ) ? intval( $data['status'] ) : '';
    $purchase_data['line_items']      = isset( $data['line_items'] ) ? $data['line_items'] : [];
    $purchase_data['purchase_order']  = isset( $data['purchase_order'] ) ? intval( $data['purchase_order'] ) : '';
    $purchase_data['ref']             = isset( $data['ref'] ) ? $data['ref'] : '';
    // translators: %s: voucher_no
    $purchase_data['particulars'] = ! empty( $data['particulars'] ) ? $data['particulars'] : sprintf( __( 'Purchase created with voucher no %s', 'erp' ), $voucher_no );
    $purchase_data['created_at']  = date( 'Y-m-d' );
    $purchase_data['created_by']  = isset( $data['created_by'] ) ? $data['created_by'] : '';
    $purchase_data['updated_at']  = isset( $data['updated_at'] ) ? $data['updated_at'] : '';
    $purchase_data['updated_by']  = isset( $data['updated_by'] ) ? $data['updated_by'] : '';

    if ( ! empty( $data['purchase_order'] ) ) {
        $purchase_data['status'] = 3;
    }

    return $purchase_data;
}

/**
 * Insert purchase/s data into ledger
 *
 * @param array $purchase_data
 *
 * @return mixed
 */
function erp_acct_insert_purchase_data_into_ledger( $purchase_data ) {
    global $wpdb;

    $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::get_instance();
    $ledger_id  = $ledger_map->get_ledger_id_by_slug( 'purchase' );

    if ( ! $ledger_id ) {
        return new WP_Error( 505, 'Ledger ID not found for purchase', $purchase_data );
    }
    // Insert amount in ledger_details
    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_ledger_details',
        array(
			'ledger_id'   => $ledger_id,
			'trn_no'      => $purchase_data['voucher_no'],
			'particulars' => $purchase_data['particulars'],
			'debit'       => $purchase_data['amount'],
			'credit'      => 0,
			'trn_date'    => $purchase_data['trn_date'],
			'created_at'  => $purchase_data['created_at'],
			'created_by'  => $purchase_data['created_by'],
			'updated_at'  => $purchase_data['updated_at'],
			'updated_by'  => $purchase_data['updated_by'],
        )
    );

}

/**
 * Update purchase/s data into ledger
 *
 * @param array $purchase_data
 * @param array $purchase_no
 *
 * @return mixed
 */
function erp_acct_update_purchase_data_into_ledger( $purchase_data, $purchase_no ) {
    global $wpdb;

    $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::get_instance();
    $ledger_id  = $ledger_map->get_ledger_id_by_slug( 'purchase' );

    if ( ! $ledger_id ) {
        return new WP_Error( 505, 'Ledger ID not found for purchase', $purchase_data );
    }

    // insert contra `erp_acct_ledger_details`
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => $ledger_id,
        'particulars' => ! empty( $purchase_data['particulars'] ) ? $purchase_data['particulars'] : '',
        'credit'      => $purchase_data['amount'],
        'trn_date'    => $purchase_data['trn_date'],
        'created_at'  => $purchase_data['created_at'],
        'created_by'  => $purchase_data['created_by'],
        'updated_at'  => $purchase_data['updated_at'],
        'updated_by'  => $purchase_data['updated_by']
    ), array(
        'trn_no'      => $purchase_no
    ) );
}

/**
 * Get Purchases count
 *
 * @return int
 */
function erp_acct_get_purchase_count() {
    global $wpdb;

    $row = $wpdb->get_row( 'SELECT COUNT(*) as count FROM ' . $wpdb->prefix . 'erp_acct_purchase' );

    return $row->count;
}


/**
 * Get due purchases by vendor
 *
 * @return mixed
 */
function erp_acct_get_due_purchases_by_vendor( $args ) {
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

    if ( '-1' !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $purchases            = "{$wpdb->prefix}erp_acct_purchase";
    $purchase_act_details = "{$wpdb->prefix}erp_acct_purchase_account_details";
    $items                = $args['count'] ? ' COUNT( id ) as total_number ' : ' * ';

    $query = $wpdb->prepare(
        "SELECT $items FROM $purchases as purchase INNER JOIN
        (
            SELECT purchase_no, ABS(SUM( pa.debit - pa.credit)) as due
            FROM $purchase_act_details as pa
            GROUP BY pa.purchase_no
            HAVING due > 0
        ) as ps
        ON purchase.voucher_no = ps.purchase_no
        WHERE purchase.vendor_id = %d AND purchase.status != 1 AND purchase.purchase_order != 1
        ORDER BY %s %s $limit",
        $args['vendor_id'],
        $args['orderby'],
        $args['order']
    );

    if ( $args['count'] ) {
        return $wpdb->get_var( $query );
    }

    return $wpdb->get_results( $query, ARRAY_A );
}


/**
 * Get due of a purchase
 *
 * @param $purchase_no
 * @return int
 */
function erp_acct_get_purchase_due( $purchase_no ) {
    global $wpdb;

    $result = $wpdb->get_row( $wpdb->prepare( "SELECT purchase_no, SUM( debit - credit) as due FROM {$wpdb->prefix}erp_acct_purchase_account_details WHERE purchase_no = %d GROUP BY purchase_no", $purchase_no ), ARRAY_A );

    return $result['due'];
}

