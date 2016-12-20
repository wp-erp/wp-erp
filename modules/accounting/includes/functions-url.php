<?php
/**
 * Get url for journal invoice
 *
 * @param  int $transaction_id
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_journal_invoice_url( $transaction_id ) {
	$url_args = [
		'page'   => 'erp-accounting-journal',
		'action' => 'view',
		'id'     => $transaction_id
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'erp_ac_journal_invoice_url', $url, $transaction_id );
}

/**
 * Get url for journal
 *
 * @param  str $content
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_journal_url( $content = false ) {

	$url_args = [
		'page'   => 'erp-accounting-journal',
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'erp_ac_journal_url', $url, $content );
}

/**
 * Get url for sales payment
 *
 * @param  int $transaction_id
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_slaes_payment_invoice_url( $transaction_id ) {
	$url_args = [
		'page'   => 'erp-accounting-sales',
		'action' => 'view',
		'id'     => $transaction_id
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'slaes_payment_invoice_url', $url, $transaction_id );
}

/**
 * Get url for sales payment
 *
 * @param  int $transaction_id
 *
 * @since 1.1.6
 *
 * @return str
 */
function erp_ac_get_slaes_payment_url( $transaction_id = false ) {
    $url_args = [
        'page'   => 'erp-accounting-sales',
        'action' => 'new',
        'type'   => 'payment',
        'transaction_id'     => $transaction_id ? $transaction_id : 0
    ];

    $url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

    return apply_filters( 'slaes_payment_url', $url, $transaction_id );
}

/**
 * Get url for bank transfer invoice
 *
 * @param  int $transaction_id
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_bank_transfer_invoice_url( $transaction_id ) {
	$url_args = [
		'page'   => 'erp-accounting-bank',
		'action' => 'view',
		'id'     => $transaction_id
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

	return apply_filters( 'bank_transfer_invoice_url', $url, $transaction_id );
}

/**
 * Get url for customer edit
 *
 * @param  int $customer_id
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_customer_edit_url( $customer_id ) {
	$url_args = [
		'page'   => 'erp-accounting-customers',
		'action' => 'edit',
		'id'     => $customer_id
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_customer_edit_url', $url, $customer_id );
}

/**
 * Get url for vendor edit
 *
 * @param  int $vendor_id
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_vendor_edit_url( $vendor_id ) {
	$url_args = [
		'page'   => 'erp-accounting-vendors',
		'action' => 'edit',
		'id'     => $vendor_id
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_vendor_edit_url', $url, $vendor_id );
}

/**
 * Get url for individual account
 *
 * @param  int $account_id
 * @param  str $content
 *
 * @since 1.1.0
 *
 * @return str
 */
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

/**
 * Get url for individual tax report
 *
 * @param  int $tax_id
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_singe_tax_report_url( $tax_id, $end = '' ) {
	$url_args = [
		'page'   => 'erp-accounting-reports',
		'type'   => 'sales-tax',
		'action' => 'view',
		'id'     => $tax_id,
        'end'    => $end
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_get_singe_tax_report_url', $url, $tax_id );
}

/**
 * Get url for sales tax report
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_sales_tax_report_url() {
	$url_args = [
		'page'   => 'erp-accounting-reports',
		'type'   => 'sales-tax',
	];

	$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
	return apply_filters( 'erp_ac_get_sales_tax_report_url', $url );
}

/**
 * Get url for sales menu
 *
 * @param  str $content
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_sales_url( $content = false ) {
	if ( ! current_user_can( 'erp_ac_view_sale' ) ) {
		return apply_filters( 'erp_ac_get_sales_url', $content );
	}

	if ( $content ) {
		$url = sprintf( '<a href="%s">%s</a>', $_SERVER['REQUEST_URI'], $content );
	} else {
		$url = $_SERVER['REQUEST_URI'];
	}

	return apply_filters( 'erp_ac_get_sales_url', $url, $content );
}

/**
 * Get url for section sales menu
 *
 * @param  str $slag
 *
 * @since 1.1.6
 *
 * @return str
 */
function erp_ac_get_section_sales_url( $slag = false ) {
	$url = erp_ac_get_sales_url();

	if ( $slag && $slag != 'all' ) {
		$slag = str_replace( '_', '-', $slag );
		$url  = add_query_arg( array( 'section' => $slag ), $url );
	} else {
		$url = remove_query_arg( array( 'section' ), $url );
	}

	return apply_filters( 'erp_ac_get_section_sales_url', $url, $slag );
}

/**
 * Get url for sales
 *
 * @param  str $content
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_sales_menu_url( $content = false ) {
    if ( ! current_user_can( 'erp_ac_view_sale' ) ) {
        return apply_filters( 'erp_ac_get_sales_url', $content );
    }

    if ( $content ) {
        $url = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => 'erp-accounting-sales' ), admin_url('admin.php') ), $content );
    } else {
        $url = add_query_arg( array( 'page' => 'erp-accounting-sales' ), admin_url('admin.php') );
    }

    return apply_filters( 'erp_ac_get_sales_menu_url', $url, $content );
}

/**
 * Get url for expense
 *
 * @param  str $content
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_expense_url( $content = false ) {

	if ( ! current_user_can( 'erp_ac_view_expense' ) ) {
		return apply_filters( 'erp_ac_get_expense_url', $content );
	}

	if ( $content ) {
		$url = sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => 'erp-accounting-expense' ), admin_url('admin.php') ), $content );
	} else {
		$url = add_query_arg( array( 'page' => 'erp-accounting-expense' ), admin_url('admin.php') );
	}


	return apply_filters( 'erp_ac_get_expense_url', $url, $content );
}

/**
 * Get url for expense voucher
 *
 * @param  int $transaction_id
 *
 * @since 1.1.0
 *
 * @return str
 */
function erp_ac_get_expense_voucher_url( $transaction_id ) {
    $url_args = [
        'page'   => 'erp-accounting-expense',
        'action' => 'view',
        'id'     => $transaction_id
    ];

    $url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

    return apply_filters( 'erp_ac_get_expense_voucher_url', $url, $transaction_id );
}

/**
 * voucher payment url
 *
 * @param  int $id
 *
 * @return string
 */
function erp_ac_get_vendor_credit_payment_url( $id ) {
    $url_args = [
        'page'   => 'erp-accounting-expense',
        'action' => 'new',
        'type'   => 'payment_voucher',
        'transaction_id' => $id
    ];

    $url = add_query_arg( $url_args, admin_url( 'admin.php' ) );
    return apply_filters( 'erp_ac_get_vendor_credit_payment_url', $url );
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
