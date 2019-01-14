<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Get all invoices
 *
 * @return mixed
 */

function erp_acct_get_sales_transactions( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'trn_date',
        'order'      => 'DESC',
        'count'      => false,
        'people_id'  => false,
        's'          => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $where = "WHERE";
    $limit = '';

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " invoice.people_id = {$args['people_id']} AND";
    }

    $where .= " voucher.type = 'sales_invoice' OR voucher.type = 'payment'";

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " ledger_detail.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = "SELECT";

    if ( $args['count'] ) {
        $sql .= " COUNT( DISTINCT voucher.id ) as total_number";
    } else {
        $sql .= " voucher.id, voucher.type,

            invoice.customer_name,
            invoice.due_date,
            invoice.status,
            invoice.amount as sales_amount,
            invoice_receipt.amount as payment_amount,
            
            ledger_detail.trn_date,
            ledger_detail.trn_no,
            SUM(ledger_detail.credit) - SUM(ledger_detail.debit) as due";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        LEFT JOIN {$wpdb->prefix}erp_acct_invoices AS invoice ON invoice.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_details AS invoice_detail ON invoice_detail.trn_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_receipts AS invoice_receipt ON invoice_receipt.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_ledger_details AS ledger_detail ON ledger_detail.trn_no = voucher.id {$where} GROUP BY ledger_detail.trn_no ORDER BY ledger_detail.{$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        $wpdb->get_results($sql);
        return $wpdb->num_rows;
    }

    return $wpdb->get_results( $sql, ARRAY_A );
}
