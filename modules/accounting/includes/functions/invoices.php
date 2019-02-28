<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get all invoices
 *
 * @return mixed
 */

function erp_acct_get_all_invoices( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'count'      => false,
        's'          => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $where = '';
    $limit = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = "SELECT";

    if ( $args['count'] ) {
        $sql .= " COUNT( DISTINCT invoice.id ) as total_number";
    } else {
        $sql .= " invoice.*, SUM(ledger_detail.credit) - SUM(ledger_detail.debit) as due";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_invoices AS invoice LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail";
    $sql .= " ON invoice.voucher_no = ledger_detail.trn_no {$where} GROUP BY invoice.voucher_no ORDER BY invoice.{$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        return $wpdb->get_var($sql);
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Get an single invoice
 *
 * @param $invoice_no
 *
 * @return mixed
 */

function erp_acct_get_invoice( $invoice_no ) {
    global $wpdb;

    $sql = $wpdb->prepare("Select

    invoice.id,
    invoice.voucher_no,
    invoice.customer_id,
    invoice.customer_name,
    invoice.trn_date,
    invoice.due_date,
    invoice.billing_address,
    invoice.amount,
    invoice.discount,
    invoice.discount_type,
    invoice.tax_rate_id,
    invoice.tax,
    invoice.estimate,
    invoice.attachments,
    invoice.status,
    invoice.particulars,

    inv_acc_detail.debit,
    inv_acc_detail.credit

    FROM {$wpdb->prefix}erp_acct_invoices as invoice
    LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details as inv_acc_detail ON invoice.voucher_no = inv_acc_detail.trn_no
    WHERE invoice.voucher_no = %d", $invoice_no);

    $row = $wpdb->get_row( $sql, ARRAY_A );

    $row['line_items'] = erp_acct_format_invoice_line_items( $invoice_no );

    // calculate every line total
    foreach ( $row['line_items'] as $key => $value ) {
        $total = ($value['item_total'] + $value['tax']) - $value['discount'];
        $row['line_items'][$key]['line_total'] = $total;
    }

    $row['attachments'] = unserialize( $row['attachments'] );
    $row['total_due'] = $row['debit'] - $row['credit'];

    return $row;
}

/**
 * Get formatted line items
 */
function erp_acct_format_invoice_line_items($voucher_no) {
    global $wpdb;

    $sql = $wpdb->prepare("SELECT
        inv_detail.product_id,
        inv_detail.qty,
        inv_detail.unit_price,
        inv_detail.discount,
        inv_detail.tax,
        inv_detail.item_total,
        inv_detail.tax_percent,

        product.name,
        product.product_type_id,
        product.category_id,
        product.vendor,
        product.cost_price,
        product.sale_price,
        product.tax_cat_id

        FROM wp_erp_acct_invoices as invoice
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_details as inv_detail ON invoice.voucher_no = inv_detail.trn_no
        LEFT JOIN {$wpdb->prefix}erp_acct_products as product ON inv_detail.product_id = product.id
        WHERE invoice.voucher_no = %d", $voucher_no);

    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Insert invoice data
 *
 * @param $data
 * @return int
 */
function erp_acct_insert_invoice( $data ) {
    global $wpdb;

    $created_by = get_current_user_id();
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['created_by'] = $created_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_voucher_no', array(
            'type'        => 'sales_invoice',
            'currency'    => '',
            'created_at'  => $data['created_at'],
            'created_by'  => $data['created_by']
        ) );

        $voucher_no = $wpdb->insert_id;

        $invoice_data = erp_acct_get_formatted_invoice_data( $data, $voucher_no );

        $wpdb->insert( $wpdb->prefix . 'erp_acct_invoices', array(
            'voucher_no'      => $invoice_data['voucher_no'],
            'customer_id'     => $invoice_data['customer_id'],
            'customer_name'   => $invoice_data['customer_name'],
            'trn_date'        => $invoice_data['trn_date'],
            'due_date'        => $invoice_data['due_date'],
            'billing_address' => $invoice_data['billing_address'],
            'amount'          => $invoice_data['amount'],
            'discount'        => $invoice_data['discount'],
            'discount_type'   => $invoice_data['discount_type'],
            'tax_rate_id'     => $invoice_data['tax_rate_id'],
            'tax'             => $invoice_data['tax'],
            'estimate'        => $invoice_data['estimate'],
            'attachments'     => $invoice_data['attachments'],
            'status'          => $invoice_data['status'],
            'particulars'     => $invoice_data['particulars'],
            'created_at'      => $invoice_data['created_at'],
            'created_by'      => $invoice_data['created_by']
        ) );

        $items = $invoice_data['line_items'];

        foreach ( $items as $key => $item ) {
            $sub_total = $item['qty'] * $item['unit_price'];

            $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_details', array(
                'trn_no'      => $voucher_no,
                'product_id'  => $item['product_id'],
                'qty'         => $item['qty'],
                'unit_price'  => $item['unit_price'],
                'discount'    => $item['discount'],
                'tax'         => $item['tax'],
                'tax_percent' => $item['tax_rate'],
                'item_total'  => $sub_total,
                'created_at'  => $invoice_data['created_at'],
                'created_by'  => $invoice_data['created_by']
            ) );

            // calculate tax for every related agency
            $tax_rate_agency = get_tax_rate_with_agency($invoice_data['tax_rate_id'], $item['tax_cat_id']);

            foreach ( $tax_rate_agency as $rate_agency ) {
                $tax_amount = ( (float) $item['tax'] * (float) $rate_agency['tax_rate'] ) / (float) $item['tax_rate'];

                $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_details_tax', [
                    'invoice_details_id' => $wpdb->insert_id,
                    'agency_id'          => $rate_agency['agency_id'],
                    'tax_rate'           => $rate_agency['tax_rate'],
                    'tax_amount'         => $tax_amount,
                    'created_at'         => $invoice_data['created_at'],
                    'created_by'         => $invoice_data['created_by']
                 ] );
            }
        }

        $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
            'invoice_no'  => $voucher_no,
            'trn_no'      => $voucher_no,
            'trn_date'    => $invoice_data['trn_date'],
            'particulars' => '',
            'debit'       => ( $invoice_data['amount'] - $invoice_data['discount'] ) + $invoice_data['tax'],
            'credit'      => 0.00,
            'created_at'  => $invoice_data['created_at'],
            'created_by'  => $invoice_data['created_by']
        ) );

        erp_acct_insert_invoice_data_into_ledger( $invoice_data );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'invoice-exception', $e->getMessage() );
    }

    return erp_acct_get_invoice( $voucher_no );

}

/**
 * Update invoice data
 *
 * @param $data
 * @return int
 */
function erp_acct_update_invoice( $data, $invoice_no ) {
    global $wpdb;

    $updated_by = get_current_user_id();
    $data['updated_at'] = date("Y-m-d H:i:s");
    $data['updated_by'] = $updated_by;

    try {
        $wpdb->query( 'START TRANSACTION' );

        $invoice_data = erp_acct_get_formatted_invoice_data( $data, $invoice_no );

        $wpdb->update( $wpdb->prefix . 'erp_acct_invoices', [
            'customer_id'     => $invoice_data['customer_id'],
            'customer_name'   => $invoice_data['customer_name'],
            'trn_date'        => $invoice_data['trn_date'],
            'due_date'        => $invoice_data['due_date'],
            'billing_address' => $invoice_data['billing_address'],
            'amount'          => $invoice_data['amount'],
            'discount'        => $invoice_data['discount'],
            'discount_type'   => $invoice_data['discount_type'],
            'tax_rate_id'     => $invoice_data['tax_rate_id'],
            'tax'             => $invoice_data['tax'],
            'estimate'        => $invoice_data['estimate'],
            'attachments'     => $invoice_data['attachments'],
            'status'          => $invoice_data['status'],
            'particulars'     => $invoice_data['particulars'],
            'updated_at'      => $invoice_data['updated_at'],
            'updated_by'      => $invoice_data['updated_by']
        ], [ 'voucher_no' => $invoice_no ] );

        /**
         *? We can't update `invoice_details` directly
         *? suppose there were 5 detail rows previously
         *? but on update there may be 2 detail rows
         *? that's why we can't update because the foreach will iterate only 2 times, not 5 times
         *? so, remove previous rows and insert new rows
         */
        $prev_detail_ids = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}erp_acct_invoice_details WHERE trn_no = {$invoice_no}", ARRAY_A);
        $prev_detail_ids = implode( ',', array_map( 'absint', $prev_detail_ids ) );

        // order matter
        $wpdb->query("DELETE FROM {$wpdb->prefix}erp_acct_invoice_details_tax WHERE invoice_details_id IN($prev_detail_ids)");
        $wpdb->delete( $wpdb->prefix . 'erp_acct_invoice_details', [ 'trn_no' => $invoice_no ] );

        $items = $invoice_data['line_items'];

        foreach ( $items as $key => $item ) {
            $sub_total = $item['qty'] * $item['unit_price'];

            $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_details', array(
                'trn_no'      => $invoice_no,
                'product_id'  => $item['product_id'],
                'qty'         => $item['qty'],
                'unit_price'  => $item['unit_price'],
                'discount'    => $item['discount'],
                'tax'         => $item['tax'],
                'tax_percent' => $item['tax_rate'],
                'item_total'  => $sub_total,
                'updated_at'  => $invoice_data['updated_at'],
                'updated_by'  => $invoice_data['updated_by'],
            ) );

            $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_details_tax', [
                'invoice_details_id' => $wpdb->insert_id,
                'agency_id'          => $item['agency_id'],
                'tax_rate'           => $item['tax_rate'],
                'tax_amount'         => $item['tax'],
                'updated_at'         => $invoice_data['updated_at'],
                'updated_by'         => $invoice_data['updated_by'],
             ] );
        }

        $wpdb->update( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
            'particulars' => $invoice_data['particulars'],
            'debit'       => ( $invoice_data['amount'] - $invoice_data['discount'] ) + $invoice_data['tax'],
            'updated_at'  => $invoice_data['updated_at'],
            'updated_by'  => $invoice_data['updated_by'],
        ), array(
            'trn_no' => $invoice_no,
            'credit' => 0.00
        ) );

        erp_acct_update_invoice_data_in_ledger( $invoice_data, $invoice_no );

        $wpdb->query( 'COMMIT' );

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'invoice-exception', $e->getMessage() );
    }

    return erp_acct_get_invoice( $invoice_no );
}

/**
 * Get formatted invoice data
 *
 * @param $data
 * @param $voucher_no
 * @return mixed
 */
function erp_acct_get_formatted_invoice_data( $data, $voucher_no ) {
    $invoice_data = [];

    // We can pass the name from view... to reduce DB query load
    $customer = erp_get_people( $data['customer_id'] );

    $invoice_data['voucher_no'] = !empty( $voucher_no ) ? $voucher_no : 0;
    $invoice_data['customer_id'] = isset( $data['customer_id'] ) ? $data['customer_id'] : null;
    $invoice_data['customer_name'] = $customer->first_name . ' ' . $customer->last_name;
    $invoice_data['trn_date']   = isset( $data['date'] ) ? $data['date'] : date("Y-m-d" );
    $invoice_data['due_date']   = isset( $data['due_date'] ) ? $data['due_date'] : date("Y-m-d" );
    $invoice_data['billing_address'] = isset( $data['billing_address'] ) ? maybe_serialize( $data['billing_address'] ) : '';
    $invoice_data['amount'] = isset( $data['amount'] ) ? $data['amount'] : 0;
    $invoice_data['discount'] = isset( $data['discount'] ) ? $data['discount'] : 0;
    $invoice_data['discount_type'] = isset( $data['discount_type'] ) ? $data['discount_type'] : 0;
    $invoice_data['tax_rate_id'] = isset( $data['tax_rate_id'] ) ? $data['tax_rate_id'] : 0;
    $invoice_data['line_items'] = isset( $data['line_items'] ) ? $data['line_items'] : array();
    $invoice_data['trn_by'] = isset( $data['trn_by'] ) ? $data['trn_by'] : '';
    $invoice_data['tax'] = isset( $data['tax'] ) ? $data['tax'] : 0;
    $invoice_data['attachments'] = isset( $data['attachments'] ) ? $data['attachments'] : '';
    $invoice_data['status'] = isset( $data['status'] ) ? $data['status'] : 1;
    $invoice_data['particulars'] = isset( $data['particulars'] ) ? $data['particulars'] : '';
    $invoice_data['estimate'] = isset( $data['estimate'] ) ? $data['estimate'] : 1;
    $invoice_data['created_at'] = isset( $data['created_at'] ) ? $data['created_at'] : null;
    $invoice_data['created_by'] = isset( $data['created_by'] ) ? $data['created_by'] : null;
    $invoice_data['updated_at'] = isset( $data['updated_at'] ) ? $data['updated_at'] : null;
    $invoice_data['updated_by'] = isset( $data['updated_by'] ) ? $data['updated_by'] : null;

    return $invoice_data;
}

/**
 * Delete an invoice
 *
 * @param $invoice_no
 *
 * @return void
 */

function erp_acct_delete_invoice( $invoice_no ) {
    global $wpdb;

    if ( !$invoice_no ) {
        return;
    }

    $wpdb->delete( $wpdb->prefix . 'erp_acct_invoices', array( 'voucher_no' => $invoice_no ) );
}

/**
 * Void an invoice
 *
 * @param $invoice_no
 *
 * @return void
 */

function erp_acct_void_invoice( $invoice_no ) {
    global $wpdb;

    if ( !$invoice_no ) {
        return;
    }

    $wpdb->update($wpdb->prefix . 'erp_acct_invoices',
        array(
            'status' => 'void',
        ),
        array( 'voucher_no' => $invoice_no )
    );
}

/**
 * Tax category with agency
 */
function get_tax_rate_with_agency($tax_id, $tax_cat_id) {
    global $wpdb;

    $sql = $wpdb->prepare(
        "SELECT agency_id, tax_rate FROM {$wpdb->prefix}erp_acct_tax_cat_agency where tax_id = %d and tax_cat_id = %d",
        absint( $tax_id ), absint( $tax_cat_id )
    );

    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Insert invoice/s data into ledger
 *
 * @param array $invoice_data
 *
 * @return mixed
 */
function erp_acct_insert_invoice_data_into_ledger( $invoice_data ) {
    global $wpdb;

    $ledger_map = \WeDevs\ERP\Accounting\Includes\Ledger_Map::getInstance();

    $sales_ledger_id = $ledger_map->get_ledger_id_by_slug('sales_revenue');
    $sales_tax_ledger_id = $ledger_map->get_ledger_id_by_slug('sales_tax_payable');
    $sales_discount_ledger_id = $ledger_map->get_ledger_id_by_slug('sales_discounts');

    // Insert amount in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => $sales_ledger_id,
        'trn_no'      => $invoice_data['voucher_no'],
        'particulars' => $invoice_data['particulars'],
        'debit'       => 0,
        'credit'      => $invoice_data['amount'],
        'trn_date'    => $invoice_data['trn_date'],
        'created_at'  => $invoice_data['created_at'],
        'created_by'  => $invoice_data['created_by'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by']
    ) );

    // Insert tax in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => $sales_tax_ledger_id,
        'trn_no'      => $invoice_data['voucher_no'],
        'particulars' => $invoice_data['particulars'],
        'debit'       => 0,
        'credit'      => $invoice_data['tax'],
        'trn_date'    => $invoice_data['trn_date'],
        'created_at'  => $invoice_data['created_at'],
        'created_by'  => $invoice_data['created_by'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by']
    ) );

    // Insert discount in ledger_details
    $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'ledger_id'   => $sales_discount_ledger_id,
        'trn_no'      => $invoice_data['voucher_no'],
        'particulars' => $invoice_data['particulars'],
        'debit'       => $invoice_data['discount'],
        'credit'      => 0,
        'trn_date'    => $invoice_data['trn_date'],
        'created_at'  => $invoice_data['created_at'],
        'created_by'  => $invoice_data['created_by'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by']
    ) );
}

/**
 * Update invoice/s data into ledger
 *
 * @param array $invoice_data
 *
 * @return mixed
 */
function erp_acct_update_invoice_data_in_ledger( $invoice_data, $invoice_no ) {
    global $wpdb;

    // Update amount in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'particulars' => $invoice_data['particulars'],
        'credit'      => $invoice_data['amount'],
        'trn_date'    => $invoice_data['trn_date'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by']
    ), [ 'trn_no' => $invoice_no ] );

    // Update tax in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'particulars' => $invoice_data['particulars'],
        'credit'      => $invoice_data['tax'],
        'trn_date'    => $invoice_data['trn_date'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by']
    ), [ 'trn_no' => $invoice_no ] );

    // Update discount in ledger_details
    $wpdb->update( $wpdb->prefix . 'erp_acct_ledger_details', array(
        'particulars' => $invoice_data['particulars'],
        'debit'       => $invoice_data['discount'],
        'trn_date'    => $invoice_data['trn_date'],
        'updated_at'  => $invoice_data['updated_at'],
        'updated_by'  => $invoice_data['updated_by']
    ), [ 'trn_no' => $invoice_no ] );
}

/**
 * Get Invoice count
 *
 * @return int
 */
function erp_acct_get_invoice_count() {
    global $wpdb;

    $row = $wpdb->get_row( "SELECT COUNT(*) as count FROM " . $wpdb->prefix . "erp_acct_invoices" );

    return $row->count;
}

/**
 * Receive payments with due from a customer
 *
 * @return mixed
 */

function erp_acct_receive_payments_from_customer( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'DESC',
        'count'      => false,
        's'          => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $invoices = "{$wpdb->prefix}erp_acct_invoices";
    $invoice_act_details = "{$wpdb->prefix}erp_acct_invoice_account_details";
    $items = $args['count'] ? " COUNT( id ) as total_number " : " id, voucher_no, due_date, (amount + tax - discount) as amount, invs.due as due ";

    $query = $wpdb->prepare( "SELECT $items FROM $invoices as invoice INNER JOIN
                                (
                                    SELECT invoice_no, SUM( ia.debit - ia.credit) as due
                                    FROM $invoice_act_details as ia
                                    GROUP BY ia.invoice_no
                                    HAVING due > 0
                                ) as invs
                                ON invoice.voucher_no = invs.invoice_no
                                WHERE invoice.customer_id = %d
                                ORDER BY %s %s $limit", $args['people_id'],$args['orderby'],$args['order']  );

    if ( $args['count'] ) {
        return $wpdb->get_var( $query );
    }

    // error_log(print_r($query, true));
    return $wpdb->get_results( $query, ARRAY_A );
}

/**
 * Get due of a bill
 *
 * @param $bill_no
 * @return int
 */
function erp_acct_get_due_payment( $invoice_no ) {
    global $wpdb;

    $result = $wpdb->get_row( "SELECT invoice_no, SUM( ia.debit - ia.credit) as due FROM {$wpdb->prefix}erp_acct_invoice_account_details as ia WHERE ia.invoice_no = {$invoice_no} GROUP BY ia.invoice_no", ARRAY_A );


    return $result['due'];
}

/**
 * Get recievables from given date
 *
 * @param $from String
 * @param $to   String
 *
 * @return array|null|object
 */
function erp_acct_get_recievables( $from, $to ) {
    global $wpdb;

    $from_date = date( "Y-m-d", strtotime( $from ) );
    $to_date   = date( "Y-m-d", strtotime( $to ) );

    $invoices = $wpdb->prefix . 'erp_acct_invoices';
    $invoices_acct_details = $wpdb->prefix . 'erp_acct_invoice_account_details';

    $query = $wpdb->prepare( "Select voucher_no, SUM(ad.debit - ad.credit) as due, due_date
                              FROM $invoices
                              LEFT JOIN $invoices_acct_details as ad
                              ON ad.invoice_no = voucher_no  where due_date
                              BETWEEN %s and %s
                              Group BY voucher_no Having due > 0 ", $from_date, $to_date );

    $results = $wpdb->get_results( $query, ARRAY_A );

    return $results;
}

/**
 * Get Dashboard Overview details
 */
function erp_acct_get_recievables_overview() {
    // get dates till coming 90 days
    $from_date = date( "Y-m-d" );
    $to_date   = date( "Y-m-d", strtotime("+90 day", strtotime( $from_date ) ));

    $data = [];
    $amount = [
        'first' => 0,
        'second' => 0,
        'third' => 0,
    ];

    $result = erp_acct_get_recievables( $from_date, $to_date );

    if ( !empty( $result ) ) {
        $from_date = new DateTime($from_date);

        foreach ( $result as $item_data ) {
            $item = (object) $item_data;
            $later = new DateTime($item->due_date);
            $diff = $later->diff($from_date)->format("%a");

            //segment by date difference
            switch ( $diff ) {

                case ( $diff === 0 ):
                    $data['first'][] = $item_data;
                    $amount['first'] = $amount['first'] + $item->due;
                    break;
                case ( $diff <= 30 ):
                    $data['first'][] = $item_data;
                    $amount['first'] = $amount['first'] + $item->due;
                    break;
                case ( $diff <= 60 ):
                    $data['second'][] = $item_data;
                    $amount['second'] = $amount['second'] + $item->due;
                    break;
                case ( $diff <= 90 ):
                    $data['third'][] = $item_data;
                    $amount['third'] = $amount['third'] + $item->due;
                    break;

                default:

            }
        }
    }

    return [ 'data' => $data, 'amount' => $amount ];
}

/**
 * Get due of an invoice
 *
 * @param $invoice_no
 * @return int
 */
function erp_acct_get_invoice_due( $invoice_no ) {
    global $wpdb;

    $result = $wpdb->get_row( "SELECT invoice_no, SUM( ia.debit - ia.credit) as due FROM {$wpdb->prefix}erp_acct_invoice_account_details as ia WHERE ia.invoice_no = {$invoice_no} GROUP BY ia.invoice_no", ARRAY_A );


    return $result['due'];
}
