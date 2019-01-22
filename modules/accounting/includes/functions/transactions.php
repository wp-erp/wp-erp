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
            status_type.type_name AS status,
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

/**
 * Get sales chart status
 */
function erp_acct_get_sales_chart_status( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $sql = "SELECT COUNT(invoice.status) AS sub_total, status_type.type_name
            FROM {$wpdb->prefix}erp_acct_trn_status_types AS status_type
            LEFT JOIN {$wpdb->prefix}erp_acct_invoices AS invoice ON invoice.status = status_type.id {$where} 
            GROUP BY status_type.id ORDER BY status_type.type_name ASC";

    // error_log(print_r($sql, true));
    return $wpdb->get_results($sql, ARRAY_A);
}

/**
 * Get sales chart payment
 */
function erp_acct_get_sales_chart_payment( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $sql = "SELECT SUM(credit) as received, SUM(balance) AS outstanding
        FROM ( SELECT invoice.voucher_no, SUM(invoice_acc_detail.credit) AS credit, SUM( invoice_acc_detail.debit - invoice_acc_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_invoices AS invoice
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details AS invoice_acc_detail ON invoice.voucher_no = invoice_acc_detail.invoice_no {$where}
        GROUP BY invoice.voucher_no HAVING balance > 0 ) AS get_amount";

    // error_log(print_r($sql, true));
    return $wpdb->get_row($sql, ARRAY_A);
}

/**
 * Get Income Expense Chart data for dashbaord
 *
 * @return array|null|object
 */
function erp_acct_get_income_expense_chart_data(){
    global $wpdb;

    $ledger_details = $wpdb->prefix . 'erp_acct_ledger_details';
    $ledgers = $wpdb->prefix . 'erp_acct_ledgers';
    $chart_of_accs = $wpdb->prefix . 'erp_acct_chart_of_accounts';

    $chart_id = 103;

    $current_year = date('Y');
    $start_date = $current_year. '-01-01';
    $end_date = $current_year. '-12-31';

    $query = "Select Month(ld.trn_date) as month, SUM( ld.debit-ld.credit ) as balance
              From $ledger_details as ld
              Inner Join $ledgers as al on al.id = ld.ledger_id
              Inner Join $chart_of_accs as ca on ca.id = al.chart_id
              Where ca.id = %d
              AND ld.trn_date BETWEEN %s AND %s
              Group By Month(ld.trn_date)";

    $results = $wpdb->get_results( $wpdb->prepare( $query, $chart_id, $start_date, $end_date ), ARRAY_A );
    $default_year_data = [
        'Jan' => 0,
        'Feb' => 0,
        'Mar' => 0,
        'Apr' => 0,
        'May' => 0,
        'Jun' => 0,
        'Jul' => 0,
        'Aug' => 0,
        'Sep' => 0,
        'Oct' => 0,
        'Nov' => 0,
        'Dec' => 0,
    ];

    if ( !empty( $results ) ) {
        $results = array_map( function ( $item ) {
            $item['month'] = date( "M", mktime( 0, 0, 0, $item['month'] ) );
            $item['balance'] = abs( $item['balance'] );
            return $item;
        }, $results );
    }

    $labels  = wp_list_pluck( $results, 'month' );
    $incomes = wp_list_pluck( $results, 'balance' );

    $this_yr_data = array_combine( $labels, $incomes );

    $this_yr_data = wp_parse_args( $this_yr_data, $default_year_data );
    //TODO expense query
    $this_year = [
        'labels' => array_keys($this_yr_data), 'income' => array_values($this_yr_data), 'expense' =>  array_values($this_yr_data)
    ];

    $last_year = $current_year-1;
    $start_date = $last_year. '-01-01';
    $end_date = $last_year. '-12-31';

    $last_year_query = "Select Month(ld.trn_date) as month, SUM( ld.debit-ld.credit ) as balance
              From $ledger_details as ld
              Inner Join $ledgers as al on al.id = ld.ledger_id
              Inner Join $chart_of_accs as ca on ca.id = al.chart_id
              Where ca.id = %d
              AND ld.trn_date BETWEEN %s AND %s
              Group By Month(ld.trn_date)";

    $last_results = $wpdb->get_results( $wpdb->prepare( $last_year_query, $chart_id, $start_date, $end_date ), ARRAY_A );

    if ( !empty( $last_results ) ) {
        $last_results = array_map( function ( $item ) {
            $item['month'] = date( "M", mktime( 0, 0, 0, $item['month'] ) );
            $item['balance'] = abs( $item['balance'] );
            return $item;
        }, $last_results );
    }

    $labels  = wp_list_pluck( $last_results, 'month' );
    $incomes = wp_list_pluck( $last_results, 'balance' );

    $last_yr_data = array_combine( $labels, $incomes );
    $last_yr_data = wp_parse_args( $last_yr_data, $default_year_data );
    //TODO expense query
    $last_yr = [
        'labels' => array_keys($last_yr_data), 'income' => array_values($last_yr_data), 'expense' => array_values($last_yr_data)
    ];

    return [ 'thisYear' => $this_year, 'lastYear' => $last_yr ];
}
