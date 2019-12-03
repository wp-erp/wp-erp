<?php
namespace WeDevs\ERP\Updates\BP;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	require_once WPERP_INCLUDES . '/lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	require_once WPERP_INCLUDES . '/lib/bgprocess/wp-background-process.php';
}

class ERP_ACCT_BG_Process_People_Trn extends \WP_Background_Process {

	/**
	 * @var string
	 */
    protected $action = 'erp_update_1_5_2_process';

	/**
	 * Task
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $voucher ) {
        global $wpdb;

        $voucher_no   = $voucher['id'];
        $voucher_type = $voucher['type'];

        $exists = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_acct_people_trn_details WHERE voucher_no = %d", $voucher_no );

        if ( $wpdb->get_var( $exists ) ) {
            return false;
        }

        $draft = 1;
        $void  = 8;

        // Start from here

        if ( 'invoice' === $voucher_type ) {
            $invoice = $wpdb->get_row(
                $wpdb->prepare("SELECT invoice.customer_id, invoice.trn_date, invoice.particulars, invoice.created_at, invoice_account.debit
                    FROM {$wpdb->prefix}erp_acct_invoices AS invoice
                    LEFT JOIN {$wpdb->prefix}erp_acct_invoice_account_details AS invoice_account
                    ON invoice.voucher_no = invoice_account.trn_no
                    WHERE invoice.voucher_no = %d AND (invoice.status <> %d AND invoice.status <> %d)",
                $voucher_no, $draft, $void), ARRAY_A
            );

            $wpdb->insert(
                // `erp_acct_people_trn_details`
                "{$wpdb->prefix}erp_acct_people_trn_details", [
                    'people_id'   => $invoice['customer_id'],
                    'voucher_no'  => $voucher_no,
                    'trn_date'    => $invoice['trn_date'],
                    'debit'       => $invoice['debit'],
                    'particulars' => $invoice['particulars'],
                    'created_at'  => $invoice['created_at']
                ]
            );
        } // invoice

        elseif ( 'payment' === $voucher_type ) {
            $payment = $wpdb->get_row(
                $wpdb->prepare("SELECT payment.customer_id, payment.trn_date, payment.amount, payment.particulars, payment.created_at FROM {$wpdb->prefix}erp_acct_invoice_receipts AS payment WHERE payment.voucher_no = %d AND (payment.status <> %d AND payment.status <> %d)",
                $voucher_no, $draft, $void), ARRAY_A
            );

            $wpdb->insert(
                // `erp_acct_people_trn_details`
                "{$wpdb->prefix}erp_acct_people_trn_details", [
                    'people_id'   => $payment['customer_id'],
                    'voucher_no'  => $voucher_no,
                    'trn_date'    => $payment['trn_date'],
                    'credit'      => $payment['amount'],
                    'particulars' => $payment['particulars'],
                    'created_at'  => $payment['created_at']
                ]
            );
        } // payment

        elseif ( 'expense' === $voucher_type || 'check' === $voucher_type ) {
            $expense = $wpdb->get_row(
                $wpdb->prepare("SELECT expense.people_id, expense.trn_date, expense.amount, expense.particulars, expense.created_at FROM {$wpdb->prefix}erp_acct_expenses AS expense WHERE expense.voucher_no = %d AND (expense.status <> %d AND expense.status <> %d)",
                $voucher_no, $draft, $void), ARRAY_A
            );

            $wpdb->insert(
                // `erp_acct_people_trn_details`
                "{$wpdb->prefix}erp_acct_people_trn_details", [
                    'people_id'   => $expense['people_id'],
                    'voucher_no'  => $voucher_no,
                    'trn_date'    => $expense['trn_date'],
                    'credit'      => $expense['amount'],
                    'particulars' => $expense['particulars'],
                    'created_at'  => $expense['created_at']
                ]
            );
        } // expense

        elseif ( 'bill' === $voucher_type ) {
            $bill = $wpdb->get_row(
                $wpdb->prepare("SELECT bill.vendor_id, bill.trn_date, bill.amount, bill.particulars, bill.created_at FROM {$wpdb->prefix}erp_acct_bills AS bill WHERE bill.voucher_no = %d AND (bill.status <> %d AND bill.status <> %d)",
                $voucher_no, $draft, $void), ARRAY_A
            );

            $wpdb->insert(
                // `erp_acct_people_trn_details`
                "{$wpdb->prefix}erp_acct_people_trn_details", [
                    'people_id'   => $bill['vendor_id'],
                    'voucher_no'  => $voucher_no,
                    'trn_date'    => $bill['trn_date'],
                    'credit'      => $bill['amount'],
                    'particulars' => $bill['particulars'],
                    'created_at'  => $bill['created_at']
                ]
            );
        } // bill

        elseif ( 'pay_bill' === $voucher_type ) {
            $pay_bill = $wpdb->get_row(
                $wpdb->prepare("SELECT pay_bill.vendor_id, pay_bill.trn_date, pay_bill.amount, pay_bill.particulars, pay_bill.created_at FROM {$wpdb->prefix}erp_acct_pay_bill AS pay_bill WHERE pay_bill.voucher_no = %d AND (pay_bill.status <> %d AND pay_bill.status <> %d)",
                $voucher_no, $draft, $void), ARRAY_A
            );

            $wpdb->insert(
                // `erp_acct_people_trn_details`
                "{$wpdb->prefix}erp_acct_people_trn_details", [
                    'people_id'   => $pay_bill['vendor_id'],
                    'voucher_no'  => $voucher_no,
                    'trn_date'    => $pay_bill['trn_date'],
                    'debit'       => $pay_bill['amount'],
                    'particulars' => $pay_bill['particulars'],
                    'created_at'  => $pay_bill['created_at']
                ]
            );
        } // pay_bill

        elseif ( 'purchase' === $voucher_type ) {
            $purchase = $wpdb->get_row(
                $wpdb->prepare("SELECT purchase.vendor_id, purchase.trn_date, purchase.amount, purchase.particulars, purchase.created_at FROM {$wpdb->prefix}erp_acct_purchase AS purchase WHERE purchase.voucher_no = %d AND (purchase.status <> %d AND purchase.status <> %d)",
                $voucher_no, $draft, $void), ARRAY_A
            );

            $wpdb->insert(
                // `erp_acct_people_trn_details`
                "{$wpdb->prefix}erp_acct_people_trn_details", [
                    'people_id'   => $purchase['vendor_id'],
                    'voucher_no'  => $voucher_no,
                    'trn_date'    => $purchase['trn_date'],
                    'credit'      => $purchase['amount'],
                    'particulars' => $purchase['particulars'],
                    'created_at'  => $purchase['created_at']
                ]
            );
        } // purchase

        elseif ( 'pay_purchase' === $voucher_type ) {
            $pay_purchase = $wpdb->get_row(
                $wpdb->prepare("SELECT pay_purchase.vendor_id, pay_purchase.trn_date, pay_purchase.amount, pay_purchase.particulars, pay_purchase.created_at FROM {$wpdb->prefix}erp_acct_pay_purchase AS pay_purchase WHERE pay_purchase.voucher_no = %d AND (pay_purchase.status <> %d AND pay_purchase.status <> %d)",
                $voucher_no, $draft, $void), ARRAY_A
            );

            $wpdb->insert(
                // `erp_acct_people_trn_details`
                "{$wpdb->prefix}erp_acct_people_trn_details", [
                    'people_id'   => $pay_purchase['vendor_id'],
                    'voucher_no'  => $voucher_no,
                    'trn_date'    => $pay_purchase['trn_date'],
                    'debit'       => $pay_purchase['amount'],
                    'particulars' => $pay_purchase['particulars'],
                    'created_at'  => $pay_purchase['created_at']
                ]
            );
        } // pay_purchase

        elseif ( 'people_trn' === $voucher_type ) {
            $people_trn = $wpdb->get_row(
                $wpdb->prepare("SELECT people_trn.people_id, people_trn.trn_date, people_trn.amount, people_trn.particulars, people_trn.created_at, people_trn.voucher_type FROM {$wpdb->prefix}erp_acct_people_trn AS people_trn WHERE people_trn.voucher_no = %d",
                $voucher_no ), ARRAY_A
            );

            if ( 'debit' === $people_trn['voucher_type'] ) {
                $debit  = $people_trn['amount'];
                $credit = 0;
            } elseif ( 'credit' === $people_trn['voucher_type'] )  {
                $debit  = 0;
                $credit = $people_trn['amount'];
            }

            $wpdb->insert(
                // `erp_acct_people_trn_details`
                "{$wpdb->prefix}erp_acct_people_trn_details", [
                    'people_id'   => $people_trn['people_id'],
                    'voucher_no'  => $voucher_no,
                    'trn_date'    => $people_trn['trn_date'],
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'particulars' => $people_trn['particulars'],
                    'created_at'  => $people_trn['created_at']
                ]
            );
        } // people_trn

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
        parent::complete();
    }

}
