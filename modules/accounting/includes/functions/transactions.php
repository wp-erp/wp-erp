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

    $where .= " (voucher.type = 'sales_invoice' OR voucher.type = 'payment')";

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( $args['number'] != '-1' ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = "SELECT";

    if ( $args['count'] ) {
        $sql .= " COUNT( DISTINCT voucher.id ) AS total_number";
    } else {
        $sql .= " voucher.id,
            voucher.type,
            invoice.customer_name,
            invoice.trn_date AS invoice_tran_date,
            invoice_receipt.trn_date AS payment_trn_date,
            invoice.due_date,
            invoice.trn_date,
            (invoice.amount + invoice.tax) - invoice.discount AS sales_amount,
            invoice_receipt.amount as payment_amount,
            status_type.invoice_type AS status,
            invoice_acc_detail.trn_no";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        LEFT JOIN {$wpdb->prefix}erp_acct_invoices AS invoice ON invoice.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_details AS invoice_detail ON invoice_detail.trn_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_receipts AS invoice_receipt ON invoice_receipt.voucher_no = voucher.id
        LEFT JOIN wp_erp_acct_trn_status_types AS status_type ON status_type.id = invoice.status
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details AS invoice_acc_detail ON invoice_acc_detail.trn_no = voucher.id {$where} 
        GROUP BY voucher.id ORDER BY invoice.{$args['orderby']} {$args['order']} {$limit}";

    if ( $args['count'] ) {
        // error_log(print_r($sql, true));
        $wpdb->get_results($sql);
        return $wpdb->num_rows;
    }

    // error_log(print_r($sql, true));
    return $wpdb->get_results( $sql, ARRAY_A );
}
