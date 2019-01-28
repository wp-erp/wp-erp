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
        'number'      => 20,
        'offset'      => 0,
        'order'       => 'ASC',
        'count'       => false,
        'customer_id' => false,
        's'           => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    $where = "WHERE (voucher.type = 'sales_invoice' OR voucher.type = 'payment')";

    if ( ! empty( $args['customer_id'] ) ) {
        $where .= " AND invoice.customer_id = {$args['customer_id']} OR invoice_receipt.customer_id = {$args['customer_id']} ";
    }
    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}' OR invoice_receipt.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
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
            invoice.customer_name AS inv_cus_name,
            invoice_receipt.customer_name AS pay_cus_name,
            invoice.trn_date AS invoice_trn_date,
            invoice_receipt.trn_date AS payment_trn_date,
            invoice.due_date,
            (invoice.amount + invoice.tax) - invoice.discount AS sales_amount,
            SUM(invoice_account_detail.debit - invoice_account_detail.credit) AS due,
            invoice_receipt.amount AS payment_amount,
            status_type.type_name AS status";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        LEFT JOIN {$wpdb->prefix}erp_acct_invoices AS invoice ON invoice.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_receipts AS invoice_receipt ON invoice_receipt.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_trn_status_types AS status_type ON status_type.id = invoice.status
        LEFT JOIN wp_erp_acct_invoice_account_details AS invoice_account_detail ON invoice_account_detail.invoice_no = invoice.id
        {$where} GROUP BY voucher.id ORDER BY CONCAT(invoice.trn_date, invoice_receipt.trn_date) {$args['order']} {$limit}";

    if ( $args['count'] ) {
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
function erp_acct_get_income_expense_chart_data() {

    $income_chart_id = 3; //Default db value
    $expense_chart_id = 4; //Default db value

    $current_year = date( 'Y' );
    $start_date = $current_year . '-01-01';
    $end_date = $current_year . '-12-31';

    $incomes = erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $income_chart_id );
    $income_data = erp_acct_format_monthly_data_to_yearly_data( $incomes );

    $expenses = erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $expense_chart_id );
    $expense_data = erp_acct_format_monthly_data_to_yearly_data( $expenses );

    $this_year = [
        'labels' => array_keys( $income_data ), 'income' => array_values( $income_data ), 'expense' => array_values( $expense_data )
    ];

    //Generate last year data
    $last_year = $current_year - 1;
    $start_date = $last_year . '-01-01';
    $end_date = $last_year . '-12-31';

    $incomes = erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $income_chart_id );
    $income_data = erp_acct_format_monthly_data_to_yearly_data( $incomes );

    $expenses = erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $expense_chart_id );
    $expense_data = erp_acct_format_monthly_data_to_yearly_data( $expenses );
    $last_yr = [
        'labels' => array_keys( $income_data ), 'income' => array_values( $expense_data ), 'expense' => array_values( $expense_data )
    ];

    return [ 'thisYear' => $this_year, 'lastYear' => $last_yr ];
}

/**
 * Get Balance amount for given chart of account in time range
 *
 * @param $start_date
 * @param $end_date
 * @param $chart_id
 *
 * @return array|null|object
 */
function erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $chart_id ) {
    global $wpdb;

    $ledger_details = $wpdb->prefix . 'erp_acct_ledger_details';
    $ledgers = $wpdb->prefix . 'erp_acct_ledgers';
    $chart_of_accs = $wpdb->prefix . 'erp_acct_chart_of_accounts';

    $query = "Select Month(ld.trn_date) as month, SUM( ld.debit-ld.credit ) as balance
              From $ledger_details as ld
              Inner Join $ledgers as al on al.id = ld.ledger_id
              Inner Join $chart_of_accs as ca on ca.id = al.chart_id
              Where ca.id = %d
              AND ld.trn_date BETWEEN %s AND %s
              Group By Month(ld.trn_date)";

    $results = $wpdb->get_results( $wpdb->prepare( $query, $chart_id, $start_date, $end_date ), ARRAY_A );
    return $results;
}

/**
 * Format Monthly result to Yearly data
 *
 * @param $result
 *
 * @return array
 */
function erp_acct_format_monthly_data_to_yearly_data( $result ) {
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

    $result = array_map( function ( $item ) {
        $item['month'] = date( "M", mktime( 0, 0, 0, $item['month'] ) );
        $item['balance'] = abs( $item['balance'] );
        return $item;
    }, $result );

    $labels = wp_list_pluck( $result, 'month' );
    $balance = wp_list_pluck( $result, 'balance' );

    $this_yr_data = array_combine( $labels, $balance );

    $this_yr_data = wp_parse_args( $this_yr_data, $default_year_data );

    return $this_yr_data;
}


/**
 * Get expense chart data
 *
 * @param array $args
 *
 * @return array|null|object
 */
function erp_acct_get_expense_chart_data( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $sql = "SELECT SUM(debit) as paid, ABS(SUM(balance)) AS payable
        FROM ( SELECT bill.voucher_no, SUM(bill_acc_detail.debit) AS debit, SUM( bill_acc_detail.debit - bill_acc_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_bills AS bill
        LEFT JOIN {$wpdb->prefix}erp_acct_bill_account_details AS bill_acc_detail ON bill.voucher_no = bill_acc_detail.bill_no {$where}
        GROUP BY bill.voucher_no HAVING balance < 0 ) AS get_amount";

    return $wpdb->get_row($sql, ARRAY_A);
}

/**
 * Get expense chart status
 *
 * @param array $args
 *
 * @return array|null|object
 */
function erp_acct_get_expense_chart_status( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $sql = "SELECT status_type.type_name, COUNT(bill.status) AS sub_total
            FROM {$wpdb->prefix}erp_acct_trn_status_types AS status_type
            LEFT JOIN {$wpdb->prefix}erp_acct_bills AS bill ON bill.status = status_type.id {$where} 
            GROUP BY status_type.id 
            HAVING sub_total > 0
            ORDER BY status_type.type_name ASC";

    $result =  $wpdb->get_results($sql, ARRAY_A);

    return $result;
}


/**
 * Get all Expenses
 *
 * @return mixed
 */

function erp_acct_get_expense_transactions( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'      => 20,
        'offset'      => 0,
        'order'       => 'ASC',
        'count'       => false,
        'vendor_id'   => false,
        's'           => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    $where = "WHERE (voucher.type = 'pay_bill' OR voucher.type = 'bill')";

    if ( ! empty( $args['vendor_id'] ) ) {
        $where .= " AND bill.vendor_id = {$args['vendor_id']} OR pay_bill.vendor_id = {$args['vendor_id']} ";
    }
    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}' OR pay_bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
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
            bill.vendor_name AS vendor_name,
            bill.trn_date AS bill_trn_date,
            pay_bill.trn_date AS pay_bill_trn_date,
            bill.due_date,
            bill.amount,
            pay_bill.amount as pay_bill_amount,
            SUM(bill_acct_details.debit - bill_acct_details.credit) AS due,
            status_type.type_name AS status";
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        LEFT JOIN {$wpdb->prefix}erp_acct_bills AS bill ON bill.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_pay_bill AS pay_bill ON pay_bill.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_trn_status_types AS status_type ON status_type.id = bill.status
        LEFT JOIN {$wpdb->prefix}erp_acct_bill_account_details AS bill_acct_details ON bill_acct_details.bill_no = bill.id
        {$where} 
        GROUP BY voucher.id
        ORDER BY CONCAT(bill.trn_date, pay_bill.trn_date) {$args['order']} {$limit}";

    if ( $args['count'] ) {
        $wpdb->get_results($sql);
        return $wpdb->num_rows;
    }

//     error_log(print_r($sql, true));
    return $wpdb->get_results( $sql, ARRAY_A );
}


/**
 * Get expense chart data
 *
 * @param array $args
 *
 * @return array|null|object
 */
function erp_acct_get_purchase_chart_data( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $sql = "SELECT SUM(debit) as paid, ABS(SUM(balance)) AS payable
        FROM ( SELECT bill.voucher_no, SUM(bill_acc_detail.debit) AS debit, SUM( bill_acc_detail.debit - bill_acc_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_purchase AS bill
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase_account_details AS bill_acc_detail ON bill.voucher_no = bill_acc_detail.purchase_no {$where}
        GROUP BY bill.voucher_no HAVING balance < 0 ) AS get_amount";

    $result = $wpdb->get_row($sql, ARRAY_A);

    return $result;
}


/**
 * Get expense chart status
 *
 * @param array $args
 *
 * @return array|null|object
 */
function erp_acct_get_purchase_chart_status( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    $sql = "SELECT status_type.type_name, COUNT(bill.status) AS sub_total
            FROM {$wpdb->prefix}erp_acct_trn_status_types AS status_type
            LEFT JOIN {$wpdb->prefix}erp_acct_purchase AS bill ON bill.status = status_type.id {$where} 
            GROUP BY status_type.id 
            HAVING sub_total > 0
            ORDER BY status_type.type_name ASC";

    $result =  $wpdb->get_results($sql, ARRAY_A);

    return $result;
}
