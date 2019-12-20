<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Get all sales transactions
 *
 * @param array $args
 * @return mixed
 */
function erp_acct_get_sales_transactions( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'      => 20,
        'offset'      => 0,
        'order'       => 'DESC',
        'count'       => false,
        'customer_id' => false,
        's'           => '',
        'status'      => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    $where = "WHERE (voucher.type = 'invoice' OR voucher.type = 'payment')";

    if ( ! empty( $args['customer_id'] ) ) {
        $where .= " AND invoice.customer_id = {$args['customer_id']} OR invoice_receipt.customer_id = {$args['customer_id']} ";
    }
    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}' OR invoice_receipt.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }
    if ( empty( $args['status'] ) ) {
        $where .= '';
    } else {
        if ( ! empty( $args['status'] ) ) {
            $where .= " AND invoice.status={$args['status']} OR invoice_receipt.status={$args['status']} ";
        }
    }
    if ( -1 !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = 'SELECT';

    if ( $args['count'] ) {
        $sql .= ' COUNT( DISTINCT voucher.id ) AS total_number';
    } else {
        $sql .= ' voucher.id,
            voucher.type,
            voucher.editable,
            invoice.customer_id AS inv_cus_id,
            invoice.customer_name AS inv_cus_name,
            invoice_receipt.customer_name AS pay_cus_name,
            invoice.trn_date AS invoice_trn_date,
            invoice_receipt.trn_date AS payment_trn_date,
            invoice_receipt.ref,
            invoice.due_date,
            invoice.estimate,
            (invoice.amount + invoice.tax) - invoice.discount AS sales_amount,
            SUM(invoice_account_detail.debit - invoice_account_detail.credit) AS due,
            invoice_receipt.amount AS payment_amount,
            invoice.status as inv_status,
            invoice_receipt.status as pay_status';
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        LEFT JOIN {$wpdb->prefix}erp_acct_invoices AS invoice ON invoice.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_receipts AS invoice_receipt ON invoice_receipt.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details AS invoice_account_detail ON invoice_account_detail.invoice_no = invoice.voucher_no
        {$where} GROUP BY voucher.id ORDER BY voucher.id {$args['order']} {$limit}";

    if ( $args['count'] ) {
        $wpdb->get_results( $sql );
        return $wpdb->num_rows;
    }

    // error_log(print_r($sql, true));
    return $wpdb->get_results( $sql, ARRAY_A );
}


/**
 * Get sales chart status
 * @param array $args
 * @return array|object|null
 */
function erp_acct_get_sales_chart_status( $args = [] ) {
    global $wpdb;

    $where = 'WHERE invoice.estimate<>1';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND invoice.customer_id = {$args['people_id']} ";
    }

    $sql = "SELECT COUNT(invoice.status) AS sub_total, status_type.type_name
            FROM {$wpdb->prefix}erp_acct_trn_status_types AS status_type
            LEFT JOIN {$wpdb->prefix}erp_acct_invoices AS invoice ON invoice.status = status_type.id {$where}
            GROUP BY status_type.id HAVING COUNT(invoice.status) > 0 ORDER BY status_type.type_name ASC";

    // error_log(print_r($sql, true));
    return $wpdb->get_results( $sql, ARRAY_A );
}


/**
 * Get sales chart payment
 * @param array $args
 * @return array|object|void|null
 */
function erp_acct_get_sales_chart_payment( $args = [] ) {
    global $wpdb;

    $where = ' WHERE invoice.estimate<>1 AND invoice.status<>1';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND invoice.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND invoice.customer_id = {$args['people_id']} ";
    }

    $sql = "SELECT SUM(credit) as received, SUM(balance) AS outstanding
        FROM ( SELECT invoice.voucher_no, SUM(invoice_acc_detail.credit) AS credit, SUM( invoice_acc_detail.debit - invoice_acc_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_invoices AS invoice
        LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details AS invoice_acc_detail ON invoice.voucher_no = invoice_acc_detail.invoice_no {$where}
        GROUP BY invoice.voucher_no) AS get_amount";

    // error_log(print_r($sql, true));
    return $wpdb->get_row( $sql, ARRAY_A );
}


/**
 * Get bill chart data
 *
 * @param array $args
 *
 * @return array|null|object
 */
function erp_acct_get_bill_chart_data( $args = [] ) {
    global $wpdb;

    $where = ' WHERE bill.status != 1';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND bill.vendor_id = {$args['people_id']} ";
    }

    $sql = "SELECT SUM(debit) as paid, ABS(SUM(balance)) AS payable
        FROM ( SELECT bill.voucher_no, SUM(bill_acc_detail.debit) AS debit, SUM( bill_acc_detail.debit - bill_acc_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_bills AS bill
        LEFT JOIN {$wpdb->prefix}erp_acct_bill_account_details AS bill_acc_detail ON bill.voucher_no = bill_acc_detail.bill_no {$where}
        GROUP BY bill.voucher_no) AS get_amount";

    return $wpdb->get_row( $sql, ARRAY_A );
}


/**
 * Get bill chart status
 *
 * @param array $args
 *
 * @return array|null|object
 */
function erp_acct_get_bill_chart_status( $args = [] ) {
    global $wpdb;

    $where = '';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= "WHERE bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND bill.vendor_id = {$args['people_id']} ";
    }

    $sql = "SELECT status_type.type_name, COUNT(bill.status) AS sub_total
            FROM {$wpdb->prefix}erp_acct_trn_status_types AS status_type
            LEFT JOIN {$wpdb->prefix}erp_acct_bills AS bill ON bill.status = status_type.id {$where}
            GROUP BY status_type.id
            HAVING sub_total > 0
            ORDER BY status_type.type_name ASC";

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

    $where = ' WHERE purchase.purchase_order<>1 AND purchase.status<>1';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND purchase.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND purchase.vendor_id = {$args['people_id']} ";
    }

    $sql = "SELECT SUM(debit) as paid, ABS(SUM(balance)) AS payable
        FROM ( SELECT purchase.voucher_no, SUM(purchase_acc_detail.debit) AS debit, SUM( purchase_acc_detail.debit - purchase_acc_detail.credit) AS balance
        FROM {$wpdb->prefix}erp_acct_purchase AS purchase
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase_account_details AS purchase_acc_detail ON purchase.voucher_no = purchase_acc_detail.purchase_no {$where}
        GROUP BY purchase.voucher_no) AS get_amount";

    $result = $wpdb->get_row( $sql, ARRAY_A );

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

    $where = 'WHERE purchase.purchase_order<>1';

    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND purchase.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND purchase.vendor_id = {$args['people_id']} ";
    }

    $sql = "SELECT status_type.type_name, COUNT(purchase.status) AS sub_total
            FROM {$wpdb->prefix}erp_acct_trn_status_types AS status_type
            LEFT JOIN {$wpdb->prefix}erp_acct_purchase AS purchase ON purchase.status = status_type.id {$where}
            GROUP BY status_type.id
            HAVING sub_total > 0
            ORDER BY status_type.type_name ASC";

    $result = $wpdb->get_results( $sql, ARRAY_A );

    return $result;
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

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND bill.people_id = {$args['people_id']} ";
    }

    $sql = "SELECT SUM(balance) as paid, 0 AS payable
        FROM ( SELECT bill.voucher_no, bill_acc_detail.amount AS balance
        FROM {$wpdb->prefix}erp_acct_expenses AS bill
        LEFT JOIN {$wpdb->prefix}erp_acct_expense_details AS bill_acc_detail ON bill.voucher_no = bill_acc_detail.trn_no {$where} HAVING balance > 0 ) AS get_amount";

    return $wpdb->get_row( $sql, ARRAY_A );
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

    if ( ! empty( $args['people_id'] ) ) {
        $where .= " AND bill.people_id = {$args['people_id']} ";
    }

    $sql = "SELECT status_type.type_name, COUNT(bill.status) AS sub_total
            FROM {$wpdb->prefix}erp_acct_trn_status_types AS status_type
            LEFT JOIN {$wpdb->prefix}erp_acct_expenses AS bill ON bill.status = status_type.id {$where}
            GROUP BY status_type.id
            HAVING sub_total > 0
            ORDER BY status_type.type_name ASC";

    return $wpdb->get_row( $sql, ARRAY_A );
}


/**
 * Get Income Expense Chart data for dashbaord
 *
 * @return array|null|object
 */
function erp_acct_get_income_expense_chart_data() {

    $income_chart_id  = 4; //Default db value
    $expense_chart_id = 5; //Default db value

    //Generate current month data

    $incomes          = erp_acct_get_daily_balance_by_chart_id( $income_chart_id, 'current' );
    $incomes_monthly  = erp_acct_format_daily_data_to_yearly_data( $incomes );
    $expenses         = erp_acct_get_daily_balance_by_chart_id( $expense_chart_id, 'current' );
    $expenses_monthly = erp_acct_format_daily_data_to_yearly_data( $expenses );

    $this_month = [
        'labels'  => array_keys( $incomes_monthly ),
		'income'  => array_values( $incomes_monthly ),
		'expense' => array_values( $expenses_monthly ),
    ];

    //Generate last month data

    $incomes          = erp_acct_get_daily_balance_by_chart_id( $income_chart_id, 'last' );
    $incomes_monthly  = erp_acct_format_daily_data_to_yearly_data( $incomes );
    $expenses         = erp_acct_get_daily_balance_by_chart_id( $expense_chart_id, 'last' );
    $expenses_monthly = erp_acct_format_daily_data_to_yearly_data( $expenses );

    $last_month = [
        'labels'  => array_keys( $incomes_monthly ),
		'income'  => array_values( $incomes_monthly ),
		'expense' => array_values( $expenses_monthly ),
    ];

    $current_year = date( 'Y' );
    $start_date   = $current_year . '-01-01';
    $end_date     = $current_year . '-12-31';

    $incomes     = erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $income_chart_id );
    $income_data = erp_acct_format_monthly_data_to_yearly_data( $incomes );

    $expenses     = erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $expense_chart_id );
    $expense_data = erp_acct_format_monthly_data_to_yearly_data( $expenses );

    $this_year = [
        'labels'  => array_keys( $income_data ),
		'income'  => array_values( $income_data ),
		'expense' => array_values( $expense_data ),
    ];

    //Generate last year data
    $last_year  = $current_year - 1;
    $start_date = $last_year . '-01-01';
    $end_date   = $last_year . '-12-31';

    $incomes     = erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $income_chart_id );
    $income_data = erp_acct_format_monthly_data_to_yearly_data( $incomes );

    $expenses     = erp_acct_get_monthly_balance_by_chart_id( $start_date, $end_date, $expense_chart_id );
    $expense_data = erp_acct_format_monthly_data_to_yearly_data( $expenses );
    $last_yr      = [
        'labels'  => array_keys( $income_data ),
		'income'  => array_values( $income_data ),
		'expense' => array_values( $expense_data ),
    ];

    return [
		'thisMonth' => $this_month,
		'lastMonth' => $last_month,
		'thisYear'  => $this_year,
		'lastYear'  => $last_yr,
	];
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
    $ledgers        = $wpdb->prefix . 'erp_acct_ledgers';
    $chart_of_accs  = $wpdb->prefix . 'erp_acct_chart_of_accounts';

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

    $result = array_map(
        function( $item ) {
				$item['month']   = date( 'M', mktime( 0, 0, 0, $item['month'] ) );
				$item['balance'] = abs( $item['balance'] );
				return $item;
		},
        $result
    );

    $labels  = wp_list_pluck( $result, 'month' );
    $balance = wp_list_pluck( $result, 'balance' );

    $this_yr_data = array_combine( $labels, $balance );

    $this_yr_data = wp_parse_args( $this_yr_data, $default_year_data );

    return $this_yr_data;
}

/**
 * Get Balance amount for given chart of account in time range
 *
 * @param $chart_id
 *
 * @param string $month
 * @return array|null|object
 */
function erp_acct_get_daily_balance_by_chart_id( $chart_id, $month = 'current' ) {
    global $wpdb;
    $start_date = null;
    $end_date   = null;

    switch ( $month ) {
        case 'current':
            $start_date = date( 'Y-m-d', strtotime( 'first day of this month' ) );
            $end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
            break;
        case 'last':
            $start_date = date( 'Y-m-d', strtotime( 'first day of previous month' ) );
            $end_date   = date( 'Y-m-d', strtotime( 'last day of previous month' ) );
            break;
        default:
            break;
    }

    $ledger_details = $wpdb->prefix . 'erp_acct_ledger_details';
    $ledgers        = $wpdb->prefix . 'erp_acct_ledgers';
    $chart_of_accs  = $wpdb->prefix . 'erp_acct_chart_of_accounts';

    $query = "Select ld.trn_date as day, SUM( ld.debit-ld.credit ) as balance
              From $ledger_details as ld
              Inner Join $ledgers as al on al.id = ld.ledger_id
              Inner Join $chart_of_accs as ca on ca.id = al.chart_id
              Where ca.id = %d
              AND ld.trn_date BETWEEN %s AND %s
              Group By ld.trn_date";

    $results = $wpdb->get_results( $wpdb->prepare( $query, $chart_id, $start_date, $end_date ), ARRAY_A );
    return $results;
}

/**
 * Format Daily result to Yearly data
 *
 * @param $result
 *
 * @return array
 */
function erp_acct_format_daily_data_to_yearly_data( $result ) {
    $result = array_map(
        function( $item ) {
				$item['day']     = date( 'd-m', strtotime( $item['day'] ) );
				$item['balance'] = abs( $item['balance'] );
				return $item;
		},
        $result
    );

    $labels  = wp_list_pluck( $result, 'day' );
    $balance = wp_list_pluck( $result, 'balance' );

    $monthly_data = array_combine( $labels, $balance );

    return $monthly_data;
}

/**
 * Get all Expenses
 *
 * @param array $args
 * @return mixed
 */
function erp_acct_get_expense_transactions( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'    => 20,
        'offset'    => 0,
        'order'     => 'DESC',
        'count'     => false,
        'vendor_id' => false,
        's'         => '',
        'status'    => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    $where = "WHERE (voucher.type = 'pay_bill' OR voucher.type = 'bill' OR voucher.type = 'expense' OR voucher.type = 'check' ) ";

    if ( ! empty( $args['vendor_id'] ) ) {
        $where .= " AND bill.vendor_id = {$args['vendor_id']} OR pay_bill.vendor_id = {$args['vendor_id']} ";
    }
    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}' OR pay_bill.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }
    if ( 0 === $args['status'] ) {
        $where .= '';
    } else {
        if ( ! empty( $args['status'] ) ) {
            $where .= " AND bill.status={$args['status']} OR pay_bill.status={$args['status']} OR expense.status={$args['status']} ";
        }
    }
    if ( -1 !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = 'SELECT';

    if ( $args['count'] ) {
        $sql .= ' COUNT( DISTINCT voucher.id ) AS total_number';
    } else {
        $sql .= ' voucher.id,
            voucher.type,
            bill.vendor_id AS vendor_id,
            bill.vendor_name AS vendor_name,
            pay_bill.vendor_name AS pay_bill_vendor_name,
            expense.people_name AS expense_people_name,
            bill.trn_date AS bill_trn_date,
            pay_bill.trn_date AS pay_bill_trn_date,
            expense.trn_date AS expense_trn_date,
            bill.due_date,
            bill.amount,
            bill.ref,
            expense.ref AS exp_ref,
            pay_bill.amount as pay_bill_amount,
            expense.amount as expense_amount,
            SUM(bill_acct_details.debit - bill_acct_details.credit) AS due,
            bill.status as bill_status,
            pay_bill.status as pay_bill_status,
            expense.status as expense_status';
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        LEFT JOIN {$wpdb->prefix}erp_acct_bills AS bill ON bill.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_pay_bill AS pay_bill ON pay_bill.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_bill_account_details AS bill_acct_details ON bill_acct_details.bill_no = bill.voucher_no
        LEFT JOIN {$wpdb->prefix}erp_acct_expenses AS expense ON expense.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_expense_checks AS cheque ON cheque.trn_no = voucher.id
        {$where}
        GROUP BY voucher.id
        ORDER BY voucher.id {$args['order']} {$limit}";

    if ( $args['count'] ) {
        $wpdb->get_results( $sql );
        return $wpdb->num_rows;
    }

    // error_log(print_r($sql, true));
    return $wpdb->get_results( $sql, ARRAY_A );
}


/**
 * Get all Purchases
 *
 * @param array $args
 * @return mixed
 */
function erp_acct_get_purchase_transactions( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'    => 20,
        'offset'    => 0,
        'order'     => 'DESC',
        'count'     => false,
        'vendor_id' => false,
        's'         => '',
        'status'    => '',
    ];

    $args = wp_parse_args( $args, $defaults );

    $limit = '';

    $where = "WHERE (voucher.type = 'pay_purchase' OR voucher.type = 'purchase')";

    if ( ! empty( $args['vendor_id'] ) ) {
        $where .= " AND purchase.vendor_id = {$args['vendor_id']} OR pay_purchase.vendor_id = {$args['vendor_id']} ";
    }
    if ( ! empty( $args['start_date'] ) ) {
        $where .= " AND purchase.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}' OR pay_purchase.trn_date BETWEEN '{$args['start_date']}' AND '{$args['end_date']}'";
    }
    if ( empty( $args['status'] ) ) {
        $where .= '';
    } else {
        if ( ! empty( $args['status'] ) ) {
            $where .= " AND purchase.status={$args['status']} OR pay_purchase.status={$args['status']} ";
        }
    }
    if ( -1 !== $args['number'] ) {
        $limit = "LIMIT {$args['number']} OFFSET {$args['offset']}";
    }

    $sql = 'SELECT';

    if ( $args['count'] ) {
        $sql .= ' COUNT( DISTINCT voucher.id ) AS total_number';
    } else {
        $sql .= ' voucher.id,
            voucher.type,
            purchase.vendor_id as vendor_id,
            purchase.vendor_name AS vendor_name,
            pay_purchase.vendor_name AS pay_bill_vendor_name,
            purchase.trn_date AS bill_trn_date,
            pay_purchase.trn_date AS pay_bill_trn_date,
            purchase.due_date,
            purchase.amount,
            purchase.ref,
            purchase.purchase_order,
            pay_purchase.amount as pay_bill_amount,
            ABS(SUM(purchase_acct_details.debit - purchase_acct_details.credit)) AS due,
            purchase.status AS purchase_status,
            pay_purchase.status AS pay_purchase_status';
    }

    $sql .= " FROM {$wpdb->prefix}erp_acct_voucher_no AS voucher
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase AS purchase ON purchase.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_pay_purchase AS pay_purchase ON pay_purchase.voucher_no = voucher.id
        LEFT JOIN {$wpdb->prefix}erp_acct_purchase_account_details AS purchase_acct_details ON purchase_acct_details.purchase_no = purchase.voucher_no
        {$where} GROUP BY voucher.id ORDER BY voucher.id {$args['order']} {$limit}";

    if ( $args['count'] ) {
        $wpdb->get_results( $sql );
        return $wpdb->num_rows;
    }

    // error_log(print_r($sql, true));
    return $wpdb->get_results( $sql, ARRAY_A );
}

/**
 * Generate transaction pdf by voucher_no
 *
 * @return void
 */
function erp_acct_generate_transaction_pdf( $voucher_no ) {
    $transaction = erp_acct_get_transaction( $voucher_no );
    $filename    = erp_acct_get_pdf_filename( $voucher_no );

    erp_acct_generate_pdf( [], $transaction, $filename, 'F' );
}

/**
 * Generate all transaction pdfs
 *
 * @return void
 */
function erp_acct_generate_transaction_pdfs() {
    global $wpdb;

    $voucher_nos = $wpdb->get_results( "SELECT id, type FROM {$wpdb->prefix}erp_acct_voucher_no", ARRAY_A );

    for ( $i = 0; $i < count( $voucher_nos ); $i++ ) {

        if ( 'journal' === $voucher_nos[ $i ]['type'] ) {
            continue;
        }

        $transaction = erp_acct_get_transaction( $voucher_nos[ $i ]['id'] );
        $filename    = erp_acct_get_pdf_filename( $voucher_nos[ $i ]['id'] );
        erp_acct_generate_pdf( [], $transaction, $filename, 'F' );
    }
}

/**
 * Generate pdf
 *
 * @param $request
 * @param $transaction
 * @param string $file_name
 * @param string $output_method
 *
 * @return boolean
 */
function erp_acct_generate_pdf( $request, $transaction, $file_name = '', $output_method = 'D' ) {

    if ( ! is_plugin_active( 'erp-pdf-invoice/wp-erp-pdf.php' ) ) {
        return;
    }

    if ( is_array( $transaction ) ) {
        $transaction = (object) $transaction;
    }

    $company     = new \WeDevs\ERP\Company();
    $theme_color = erp_get_option( 'erp_ac_pdf_theme_color', false, '#9e9e9e' );

    $user_id = null;
    $trn_id  = null;
    $type    = erp_acct_get_transaction_type( $transaction->voucher_no );

    if ( ! empty( $request ) ) {
        $receiver   = isset( $request['receiver'] ) ? $request['receiver'] : $transaction->email;
        $subject    = isset( $request['subject'] ) ? $request['subject'] : $transaction->subject;
        $body       = isset( $request['message'] ) ? $request['message'] : $request['body'];
        $attach_pdf = isset( $request['attachment'] ) && 'on' === $request['attachment'] ? true : false;
    }

    if ( ! empty( $transaction->customer_id ) ) {
        $user_id = $transaction->customer_id;
    }
    if ( ! empty( $transaction->vendor_id ) ) {
        $user_id = $transaction->vendor_id;
    }
    if ( ! empty( $transaction->people_id ) ) {
        $user_id = $transaction->people_id;
    }
    $user = new \WeDevs\ERP\People( intval( $user_id ) );

    if ( ! defined( 'WPERP_PDF_VERSION' ) ) {
        wp_die( esc_html__( 'ERP PDF extension is not installed. Please install the extension for PDF support', 'erp' ) );
    }

    //Create a new instance
    $trn_pdf = new \WeDevs\ERP_PDF\PDF_Invoicer( 'A4', '$', 'en' );

    //Set theme color
    $trn_pdf->set_theme_color( $theme_color );

    //Set your logo
    $logo_id = (int) $company->logo;

    if ( $logo_id ) {
        $image = wp_get_attachment_image_src( $logo_id, 'medium' );
        $url   = $image[0];
        $trn_pdf->set_logo( $url );
    }

    if ( ! empty( $transaction->voucher_no ) ) {
        $trn_id = $transaction->voucher_no;
    } elseif ( ! empty( $transaction->trn_no ) ) {
        $trn_id = $transaction->trn_no;
    }

    //Set type
    $trn_pdf->set_type( erp_acct_get_transaction_type( $trn_id ) );

    // Set barcode
    if ( $trn_id ) {
        $trn_pdf->set_barcode( $trn_id );
    }

    // Set reference
    if ( $trn_id ) {
        $trn_pdf->set_reference( $trn_id, __( 'Transaction Number', 'erp' ) );
    }

    // Set Issue Date
    $date = ! empty( $transaction->trn_date ) ? $transaction->trn_date : $transaction->date;
    $trn_pdf->set_reference( erp_format_date( $date ), __( 'Transaction Date', 'erp' ) );

    // Set from Address
    $from_address = explode( '<br/>', $company->get_formatted_address() );
    array_unshift( $from_address, $company->name );

    $trn_pdf->set_from_title( __( 'FROM', 'erp' ) );
    $trn_pdf->set_from( $from_address );

    // Set to Address
    $to_address = array_values( erp_acct_get_people_address( $user_id ) );
    if ( empty( $to_address ) ) {
        $to_address = erp_get_people( $user_id )->email;
    }
    array_unshift( $to_address, $user->get_full_name() );

    $trn_pdf->set_to_title( __( 'TO', 'erp' ) );
    $trn_pdf->set_to_address( $to_address );

    /* Customize columns based on transaction type */
    if ( 'invoice' == $type ) {
        // Set Date Due
        $trn_pdf->set_reference( erp_format_date( $transaction->due_date ), __( 'Due Date', 'erp' ) );

        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'PRODUCT', 'erp' ), __( 'QUANTITY', 'erp' ), __( 'UNIT PRICE', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        // Add Table Items
        foreach ( $transaction->line_items as $line ) {
            $trn_pdf->add_item( [ $line['name'], $line['qty'], erp_acct_get_price( $line['unit_price'] ), erp_acct_get_price( $line['item_total'] )  ] );
        }

        $trn_pdf->add_badge( __( 'PENDING', 'erp' ) );
        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), erp_acct_get_price( $transaction->amount ) );
        $trn_pdf->add_total( __( 'DISCOUNT', 'erp' ), erp_acct_get_price( $transaction->discount ) );
        $trn_pdf->add_total( __( 'TAX', 'erp' ), erp_acct_get_price( $transaction->tax ) );
        $trn_pdf->add_total( __( 'TOTAL DUE', 'erp' ), erp_acct_get_price( $transaction->amount + $transaction->tax - $transaction->discount ) );

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }
    }

    if ( 'payment' === $type ) {
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'INNVOICE NO', 'erp' ), __( 'TRN DATE', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        // Add Table Items
        foreach ( $transaction->line_items as $line ) {
            $trn_pdf->add_item( [ $line['invoice_no'], $transaction->trn_date, $line['amount'] ] );
        }

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }

        $trn_pdf->add_badge( __( 'PAID', 'erp' ) );
        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->amount );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->amount );
    }

    if ( 'bill' === $type ) {
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'BILL NO', 'erp' ), __( 'BILL DATE', 'erp' ), __( 'DUE DATE', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        // Add Table Items
        foreach ( $transaction->bill_details as $line ) {
            $trn_pdf->add_item( [ $line['id'], $transaction->trn_date, $transaction->due_date, $line['amount'] ] );
        }

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }

        $trn_pdf->add_badge( __( 'PENDING', 'erp' ) );
        $trn_pdf->add_total( __( 'DUE', 'erp' ), erp_acct_get_bill_due( $transaction->voucher_no ) );
        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->amount );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->amount );
    }

    if ( 'pay_bill' === $type ) {
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'BILL NO', 'erp' ), __( 'DUE DATE', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        // Add Table Items
        foreach ( $transaction->bill_details as $line ) {
            $trn_pdf->add_item( [ $line['bill_no'], $transaction->trn_date, $line['amount'] ] );
        }

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }

        $trn_pdf->add_badge( __( 'PAID', 'erp' ) );
        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->amount );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->amount );
    }

    if ( 'purchase' === $type ) {
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'PRODUCT', 'erp' ), __( 'QUANTITY', 'erp' ), __( 'COST PRICE', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        // Add Table Items
        foreach ( $transaction->line_items as $line ) {
            $trn_pdf->add_item( [ $line['name'], $line['qty'], $line['cost_price'], $line['amount'] ] );
        }

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }

        $trn_pdf->add_badge( __( 'PENDING', 'erp' ) );
        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->amount );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->amount );
    }

    if ( 'pay_purchase' === $type ) {
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'PURCHASE NO', 'erp' ), __( 'DUE DATE', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        // Add Table Items
        foreach ( $transaction->purchase_details as $line ) {
            $trn_pdf->add_item( [ $line['purchase_no'], $transaction->due_date, $line['amount'] ] );
        }

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }

        $trn_pdf->add_badge( __( 'PAID', 'erp' ) );
        $trn_pdf->add_total( __( 'DUE', 'erp' ), $transaction->due );
        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->amount );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->amount );
    }

    if ( 'expense' === $type ) {
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'EXPENSE NO', 'erp' ), __( 'EXPENSE DATE', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        // Add Table Items
        foreach ( $transaction->bill_details as $line ) {
            $trn_pdf->add_item( [ $line['trn_no'], $transaction->trn_date, $line['amount'] ] );
        }

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }

        $trn_pdf->add_badge( __( 'PAID', 'erp' ) );
        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->amount );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->amount );
    }

    if ( 'check' === $type ) {
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'CHECK NO', 'erp' ), __( 'CHECK DATE', 'erp' ), __( 'PAY TO', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        // Add Table Items
        foreach ( $transaction->bill_details as $line ) {
            $trn_pdf->add_item( [ $line['check_no'], $transaction->trn_date, $transaction->pay_to, $line['amount'] ] );
        }

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }

        $trn_pdf->add_badge( __( 'PAID', 'erp' ) );
        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->total );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->total );
    }

    if ( 'transfer_voucher' === $type ) {
        $type = __( 'Transfer Voucher', 'erp' );
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'VOUCHER NO', 'erp' ), __( 'ACCOUNT FROM', 'erp' ), __( 'AMOUNT', 'erp' ), __( 'ACCOUNT TO', 'erp' ) ] );

        $trn_pdf->add_item( [ $transaction->voucher_no, $transaction->ac_from, $transaction->amount, $transaction->ac_to ] );

        // Add particulars
        if ( $transaction->particulars ) {
            $trn_pdf->add_title( __( 'Notes', 'erp' ) );
            $trn_pdf->add_paragraph($transaction->particulars );
        }

        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->balance );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->balance );
    }

    if ( 'people_trn' === $type ) {
        $type = __( 'People Transaction', 'erp' );
        // Set Column Headers
        $trn_pdf->set_table_headers( [ __( 'VOUCHER NO', 'erp' ), __( 'PARTICULARS', 'erp' ), __( 'AMOUNT', 'erp' ) ] );

        $trn_pdf->add_item( [ $transaction->voucher_no, $transaction->particulars, $transaction->balance ] );

        $trn_pdf->add_total( __( 'SUB TOTAL', 'erp' ), $transaction->balance );
        $trn_pdf->add_total( __( 'TOTAL', 'erp' ), $transaction->balance );
    }

    $trn_pdf->render( $file_name, $output_method );
    $file_name = isset( $attach_pdf ) ? $file_name : '';

    return $file_name;
}

/**
 * Generate and send pdf
 *
 * @param $request
 * @param $transaction
 * @param $file_name
 * @param string $output_method
 *
 * @return boolean
 */
function erp_acct_send_email_with_pdf_attached( $request, $transaction, $file_name, $output_method = 'D' ) {
    if ( ! is_plugin_active( 'erp-pdf-invoice/wp-erp-pdf.php' ) ) {
        return;
    }

    $trn_email = new \WeDevs\ERP\Accounting\Includes\Classes\Send_Email();
    $user_id   = null;
    $trn_id    = null;
    $result    = [];

    $type     = isset( $request['type'] ) ? $request['type'] : erp_acct_get_transaction_type( $transaction->voucher_no );
    $receiver = isset( $request['receiver'] ) ? $request['receiver'] : [];
    // translators: %s: type
    $subject = isset( $request['subject'] ) ? $request['subject'] : sprintf( __( 'Transaction alert for %s', 'erp' ), $request['type'] );
    $body    = isset( $request['message'] ) ? $request['message'] : __( 'Thank you for the transaction', 'erp' );
    // $attach_pdf = isset( $request['attachment'] ) && 'on' === $request['attachment'] ? true : false;

    $pdf_file = erp_acct_generate_pdf( $request, $transaction, $file_name, 'F' );

    if ( $pdf_file ) {
        $result = $trn_email->trigger( $receiver, $subject, $body, $pdf_file );
    } else {
        wp_die( esc_html__( 'PDF not generated!', 'erp' ) );
    }

    return $result;
}


/**
 * Send pdf on transaction
 */
add_action( 'erp_acct_new_transaction_sales', 'erp_acct_send_email_on_transaction', 10, 2 );
add_action( 'erp_acct_new_transaction_payment', 'erp_acct_send_email_on_transaction', 10, 2 );
add_action( 'erp_acct_new_transaction_bill', 'erp_acct_send_email_on_transaction', 10, 2 );
add_action( 'erp_acct_new_transaction_pay_bill', 'erp_acct_send_email_on_transaction', 10, 2 );
add_action( 'erp_acct_new_transaction_purchase', 'erp_acct_send_email_on_transaction', 10, 2 );
add_action( 'erp_acct_new_transaction_pay_purchase', 'erp_acct_send_email_on_transaction', 10, 2 );
add_action( 'erp_acct_new_transaction_expense', 'erp_acct_send_email_on_transaction', 10, 2 );
add_action( 'erp_acct_new_transaction_estimate', 'erp_acct_send_email_on_transaction', 10, 2 );
add_action( 'erp_acct_new_transaction_purchase_order', 'erp_acct_send_email_on_transaction', 10, 2 );

/**
 * Send pdf on transaction
 *
 * @param $voucher_no
 * @param $transaction
 * @return boolean
 */
function erp_acct_send_email_on_transaction( $voucher_no, $transaction ) {
    if ( ! is_plugin_active( 'erp-pdf-invoice/wp-erp-pdf.php' ) ) {
        return;
    }

    $trn_email = new \WeDevs\ERP\Accounting\Includes\Classes\Send_Email();
    $user_id   = null;
    $trn_id    = null;
    $request   = [];
    $result    = [];

    $request['type']       = ! empty( $transaction['type'] ) ? $transaction['type'] : erp_acct_get_transaction_type( $voucher_no );
    $request['receiver'][] = ! empty( $transaction['email'] ) ? $transaction['email'] : [];
    // translators: %s: type
    $request['subject']    = sprintf( __( 'Transaction alert for %s', 'erp' ), $request['type'] );
    $request['body']       = __( 'Thank you for the transaction', 'erp' );
    $request['attachment'] = true;
    $attach_pdf            = true;

    $file_name = erp_acct_get_pdf_filename( $voucher_no );
    $pdf_file  = erp_acct_generate_pdf( $request, $transaction, $file_name, 'F' );

    if ( $pdf_file ) {
        $result = $trn_email->trigger( $request['receiver'], $request['subject'], $request['body'], $request['attachment'] );
    } else {
        wp_die( esc_html__( 'PDF not generated!', 'erp' ) );
    }

    return $result;
}

/**
 * Get voucher type by id
 * @param $voucher_no
 * @return string|null
 */
function erp_acct_get_transaction_type( $voucher_no ) {
    global $wpdb;

    return $wpdb->get_var( $wpdb->prepare( "SELECT type FROM {$wpdb->prefix}erp_acct_voucher_no WHERE id = %d", $voucher_no ) );
}

/**
 * @param $transaction_id
 *
 * @return mixed
 */
function erp_acct_get_transaction( $transaction_id ) {

    $transaction = [];

    $transaction_type = erp_acct_get_transaction_type( $transaction_id );
    $link_hash        = erp_acct_get_invoice_link_hash( $transaction_id, $transaction_type );
    $readonly_url     = add_query_arg(
        [
			'query'    => 'readonly_invoice',
			'trans_id' => $transaction_id,
			'auth'     => $link_hash,
		],
        site_url()
    );

    switch ( $transaction_type ) {
        case 'invoice':
            $transaction = erp_acct_get_invoice( $transaction_id );
            break;
        case 'payment':
            $transaction = erp_acct_get_payment( $transaction_id );
            break;
        case 'bill':
            $transaction = erp_acct_get_bill( $transaction_id );
            break;
        case 'pay_bill':
            $transaction = erp_acct_get_pay_bill( $transaction_id );
            break;
        case 'purchase':
            $transaction = erp_acct_get_purchase( $transaction_id );
            break;
        case 'pay_purchase':
            $transaction = erp_acct_get_pay_purchase( $transaction_id );
            break;
        case 'expense':
        case 'check':
            $transaction = erp_acct_get_expense( $transaction_id );
            break;
        case 'transfer_voucher':
            $transaction = erp_acct_get_single_voucher( $transaction_id );
            break;
        default:
            break;
    }

    $transaction['type']         = $transaction_type;
    $transaction['readonly_url'] = $readonly_url;

    return $transaction;

}

/**
 * Varify transaction hash
 *
 * @param $transaction_id
 * @param string $transaction_type
 * @param string $hash_to_verify
 * @param string $algo
 *
 * @return bool
 */
function erp_acct_verify_invoice_link_hash( $transaction_id, $transaction_type, $hash_to_verify = '', $algo = 'sha256' ) {

    if ( $transaction_id && $transaction_type && $hash_to_verify ) {

        $to_hash       = $transaction_id . $transaction_type;
        $hash_original = hash( $algo, $to_hash );

        if ( $hash_original === $hash_to_verify ) {
            return true;
        }
    }

    return false;
}

/**
 * Get unique transaction hash for sharing
 *
 * @param $transaction_id
 * @param string $transaction_type
 * @param string $algo
 *
 * @return string
 */
function erp_acct_get_invoice_link_hash( $transaction_id, $transaction_type, $algo = 'sha256' ) {
    $hash_string = '';

    if ( $transaction_id && $transaction_type ) {
        $to_hash     = $transaction_id . $transaction_type;
        $hash_string = hash( $algo, $to_hash );
    }

    return $hash_string;
}

/**
 * Get pdf file name
 *
 * @param $voucher_no
 * @return string
 */
function erp_acct_get_pdf_filename( $voucher_no ) {
    $inv_dir = WP_CONTENT_DIR . '/uploads/erp-pdfs/';

    if ( ! file_exists( $inv_dir ) ) {
        mkdir( $inv_dir, 0777, true );
    }

    $pdf_file = $inv_dir . "voucher_{$voucher_no}.pdf";

    return $pdf_file;
}


/**
 * Insert data into `erp_acct_people_trn_details` table
 *
 * @param $voucher_no
 * @param $transaction
 */
function erp_acct_insert_data_into_people_trn_details( $transaction, $voucher_no ) {
    global $wpdb;

    $data = [];

    if ( ! empty( $transaction['customer_id'] ) ) {
        $people_id = $transaction['customer_id'];
    } else {
        if ( ! empty( $transaction['vendor_id'] ) ) {
            $people_id = $transaction['vendor_id'];
        } else {
            $people_id = $transaction['people_id'];
        }
    }

    $date = ! empty( $transaction['trn_date'] ) ? $transaction['trn_date'] : $transaction['date'];

    $wpdb->insert(
        $wpdb->prefix . 'erp_acct_people_trn_details',
        array(
			'people_id'   => $people_id,
			'voucher_no'  => $voucher_no,
			'debit'       => $transaction['dr'],
			'credit'      => $transaction['cr'],
			'trn_date'    => $date,
			'particulars' => $transaction['particulars'],
			'created_at'  => $transaction['created_at'],
			'created_by'  => $transaction['created_by'],
			'updated_at'  => $transaction['updated_at'],
			'updated_by'  => $transaction['updated_by'],
        )
    );
}

/**
 * Update data into `erp_acct_people_trn_details` table
 *
 * @param $transaction
 * @param $voucher_no
 */
function erp_acct_update_data_into_people_trn_details( $transaction, $voucher_no ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'erp_acct_people_trn_details', array( 'voucher_no' => $voucher_no ) );

}

/**
 * Return url from a absolute path
 *
 * @param $voucher_no
 * @return string
 */
function erp_acct_pdf_abs_path_to_url( $voucher_no ) {
    $upload_url = wp_upload_dir();
    $url        = $upload_url['baseurl'] . '/erp-pdfs/' . "voucher_{$voucher_no}.pdf";
    return esc_url_raw( $url );
}

