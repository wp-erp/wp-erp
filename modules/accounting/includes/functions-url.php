<?php

function erp_ac_get_journal_invoice_url( $transaction_id ) {
	$url_args = [
		'page'   => 'erp-accounting-journal',
		'action' => 'view',
		'id'     => $transaction_id
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'erp_ac_journal_invoice_url', $url, $transaction_id );
}

function erp_ac_get_journal_url( $content = false ) {
	
	$url_args = [
		'page'   => 'erp-accounting-journal',
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'erp_ac_journal_url', $url, $content );
}

function erp_ac_get_slaes_payment_invoice_url( $transaction_id ) {
	$url_args = [
		'page'   => 'erp-accounting-sales',
		'action' => 'view',
		'id'     => $transaction_id
	];
	
	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'slaes_payment_invoice_url', $url, $transaction_id );
}

function erp_ac_get_bank_transfer_invoice_url( $transaction_id ) {
	$url_args = [
		'page'   => 'erp-accounting-bank',
		'action' => 'view',
		'id'     => $transaction_id
	];
	
	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'bank_transfer_invoice_url', $url, $transaction_id );
}

function erp_ac_get_expense_voucher_url( $transaction_id ) {
	$url_args = [
		'page'   => 'erp-accounting-expense',
		'action' => 'view',
		'id'     => $transaction_id
	];
	
	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'erp_ac_get_expense_voucher_url', $url, $transaction_id );
}

function erp_ac_customer_edit_url( $customer_id ) {
	$url_args = [
		'page'   => 'erp-accounting-customers',
		'action' => 'edit',
		'id'     => $customer_id
	];
	
	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_customer_edit_url', $url, $customer_id );
}
function erp_ac_vendor_edit_url( $vendor_id ) {
	$url_args = [
		'page'   => 'erp-accounting-vendors',
		'action' => 'edit',
		'id'     => $vendor_id
	];
	
	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_vendor_edit_url', $url, $vendor_id );
}

function erp_ac_get_account_url( $account_id, $content = '' ) {
	$url = add_query_arg(array(
			'page'   => 'erp-accounting-charts',
			'action' => 'view',
			'id'     => $account_id
		), 
		admin_url( 'admin.php' ) 
	);

	if ( erp_ac_view_single_account() ) {
		$url = sprintf( '<a href="%s">%s</a>', $url, $content );
		return apply_filters( 'erp_ac_get_account_url', $url, $account_id, $content );		
	}

	return apply_filters( 'erp_ac_get_account_url', $url, $account_id, $content );
}
function erp_ac_get_singe_tax_report_url( $tax_id ) {
	$url_args = [
		'page'   => 'erp-accounting-reports',
		'type'   => 'sales-tax',
		'action' => 'view',
		'id'     => $tax_id
	];
	
	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_get_singe_tax_report_url', $url, $tax_id );
}
function erp_ac_get_sales_tax_report_url() {
	$url_args = [
		'page'   => 'erp-accounting-reports',
		'type'   => 'sales-tax',
	];
	
	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_get_sales_tax_report_url', $url );
}

function erp_ac_get_sales_url( $content = false ) {
	if ( ! current_user_can( 'erp_ac_view_sale' ) ) {
		return apply_filters( 'erp_ac_get_sales_url', $content );
	}

	if ( $content ) {
		$url = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => 'erp-accounting-sales' ), admin_url('admin.php') ), $content );
	} else {
		$url = add_query_arg( array( 'page' => 'erp-accounting-sales' ), admin_url('admin.php') );
	}
	
	
	return apply_filters( 'erp_ac_get_sales_url', $url, $content );
}

function erp_ac_get_expense_url( $content = false ) {
	
	if ( ! current_user_can( 'erp_ac_view_expense' ) ) {
		return apply_filters( 'erp_ac_get_sales_url', $content );
	}

	if ( $content ) {
		$url = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => 'erp-accounting-expense' ), admin_url('admin.php') ), $content );
	} else {
		$url = add_query_arg( array( 'page' => 'erp-accounting-expense' ), admin_url('admin.php') );
	}
	
	
	return apply_filters( 'erp_ac_get_expense_url', $url, $content );
}

/**
 * vendor credit edit url
 *
 * @param  int $id
 * 
 * @return string
 */
function erp_ac_get_vendor_credit_edit_url( $id ) {
	$url_args = [
		'page'   => 'erp-accounting-expense',
		'action' => 'new',
		'type'   => 'vendor_credit',
		'transaction_id' => $id
	];
	
	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_get_vendor_credit_edit_url', $url );
}

