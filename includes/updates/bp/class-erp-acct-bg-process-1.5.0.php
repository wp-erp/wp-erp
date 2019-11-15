<?php
namespace WeDevs\ERP\Updates\BP;

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	require_once WPERP_INCLUDES . '/lib/bgprocess/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	require_once WPERP_INCLUDES . '/lib/bgprocess/wp-background-process.php';
}

class ERP_ACCT_BG_Process extends \WP_Background_Process {

	/**
	 * @var string
	 */
    protected $action = 'erp_update_1_5_0_process';

    /**
     * @var array
     */
    protected $currencies = [];

	/**
	 * Task
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $trn_id ) {
        global $wpdb;

        $exists = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}erp_acct_voucher_no WHERE id = %d", $trn_id );

        if ( $wpdb->get_var( $exists ) ) {
            return false;
        }

        $trn = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_ac_transactions WHERE id = %d", $trn_id ),
            ARRAY_A );

        $product_ids = $wpdb->get_results(
            $wpdb->prepare( "SELECT product_id FROM  {$wpdb->prefix}erp_ac_transaction_items where transaction_id = %d AND product_id <> 0", $trn_id ),
        ARRAY_A );

        $has_inventory = ! empty( $product_ids ) ? true : false;

        switch ( $trn['status'] ) {
            case 'awaiting_approval':
                $status = 'awaiting_payment';
                break;
            case 'partial':
                $status = 'partially_paid';
                break;
            default:
                $status = $trn['status'];
        }

        $status = erp_acct_trn_status_by_id($status);
        $people = erp_get_people( $trn['user_id'] );

        // Start from here

        if ( 'invoice' === $trn['form_type'] ) {

            $wpdb->insert(
                // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'id'         => $trn_id,
                    'type'       => 'invoice',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $wpdb->insert(
                // `erp_acct_invoices`
                "{$wpdb->prefix}erp_acct_invoices", [
                    'voucher_no'      => $trn_id,
                    'customer_id'     => $trn['user_id'],
                    'customer_name'   => $people->first_name . ' ' . $people->last_name,
                    'trn_date'        => $trn['issue_date'],
                    'due_date'        => $trn['due_date'],
                    'billing_address' => $trn['billing_address'],
                    'amount'          => $trn['sub_total'],
                    'discount'        => 0,
                    'discount_type'   => 'discount-value',
                    'tax'             => 0,
                    'estimate'        => 0,
                    'attachments'     => $trn['files'],
                    'status'          => $status,
                    'particulars'     => $trn['summary'],
                    'created_at'      => $this->get_created_at( $trn['created_at'] ),
                    'created_by'      => $trn['created_by']
                ]
            );

            $this->_helper_invoice_account_details_migration($trn, $trn_id);
            $this->_helper_invoice_ledger_details_migration($trn, $trn_id);
            $this->_helper_invoice_details_migration($trn_id);
        } // invoice

        elseif ( 'payment' === $trn['form_type'] ) {
            $wpdb->insert(
                // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'id'         => $trn_id,
                    'type'       => 'payment',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $wpdb->insert(
                // `erp_acct_invoice_receipts`
                "{$wpdb->prefix}erp_acct_invoice_receipts", [
                    'voucher_no'       => $trn_id,
                    'customer_id'      => $trn['user_id'],
                    'customer_name'    => $people->first_name . ' ' . $people->last_name,
                    'trn_date'         => $trn['issue_date'],
                    'amount'           => $trn['total'],
                    'particulars'      => $trn['summary'],
                    'attachments'      => $trn['files'],
                    'status'           => erp_acct_trn_status_by_id('closed'),
                    'trn_by'           => 1,
                    'trn_by_ledger_id' => 1,
                    'created_at'       => $this->get_created_at( $trn['created_at'] ),
                    'created_by'       => $trn['created_by']
                ]
            );

            $this->_helper_invoice_receipts_account_details_migration($trn, $trn_id);
            $this->_helper_invoice_receipts_ledger_details_migration($trn, $trn_id);
            $this->_helper_invoice_receipts_details_migration( $trn_id );
        } // payment

        elseif ( 'payment_voucher' === $trn['form_type'] ) {
            $wpdb->insert(
                // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'id'         => $trn_id,
                    'type'       => 'pay_purchase',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $wpdb->insert(
                // `erp_acct_pay_purchase`
                "{$wpdb->prefix}erp_acct_pay_purchase", [
                    'voucher_no'       => $trn_id,
                    'vendor_id'        => $trn['user_id'],
                    'vendor_name'      => $people->first_name . ' ' . $people->last_name,
                    'trn_date'         => $trn['issue_date'],
                    'amount'           => $trn['total'],
                    'particulars'      => $trn['summary'],
                    'attachments'      => $trn['files'],
                    'status'           => erp_acct_trn_status_by_id('closed'),
                    'trn_by'           => 1,
                    'trn_by_ledger_id' => 1,
                    'created_at'       => $this->get_created_at( $trn['created_at'] ),
                    'created_by'       => $trn['created_by']
                ]
            );

            $this->_helper_payment_voucher_pay_purchase_account_details_migration($trn, $trn_id);
            $this->_helper_payment_voucher_pay_purchase_ledger_details_migration($trn, $trn_id);
            $this->_helper_payment_voucher_pay_purchase_details_migration( $trn_id );
        } // payment_voucher

        elseif ( 'vendor_credit' === $trn['form_type'] ) {
            $wpdb->insert(
                // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'id'         => $trn_id,
                    'type'       => 'purchase',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $wpdb->insert(
                // `erp_acct_purchase`
                "{$wpdb->prefix}erp_acct_purchase", [
                    'voucher_no'     => $trn_id,
                    'vendor_id'      => $trn['user_id'],
                    'vendor_name'    => $people->first_name . ' ' . $people->last_name,
                    'trn_date'       => $trn['issue_date'],
                    'due_date'       => $trn['due_date'],
                    'ref'            => $trn['ref'],
                    'amount'         => $trn['trans_total'],
                    'particulars'    => $trn['summary'],
                    'attachments'    => $trn['files'],
                    'status'         => $status,
                    'purchase_order' => 0,
                    'created_at'     => $this->get_created_at( $trn['created_at'] ),
                    'created_by'     => $trn['created_by']
                ]
            );

            $this->_helper_vendor_credit_purchase_account_details_migration($trn, $trn_id);
            $this->_helper_vendor_credit_purchase_ledger_details_migration($trn, $trn_id);
            $this->_helper_vendor_credit_purchase_details_migration( $trn_id );
        } // vendor_credit

        elseif ( 'bank' === $trn['form_type'] ) {
            $wpdb->insert(
                // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'id'         => $trn_id,
                    'type'       => 'transfer_voucher',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $this->_helper_bank_transfers_migration($trn, $trn_id);
        } // transfer

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
        parent::complete();
    }

    /**
     * Get formatted created at
     */
    protected function get_created_at( $created_at ) {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $created_at)->format('Y-m-d');
    }

    /**
     * ===========================================================================
     * Begin the hard work ...
     * ====================================================================
     */

    /**
     * Get currency name
     */
    protected function get_currecny_id( $name ) {
        global $wpdb;

        if ( empty( $this->currencies ) ) {
            //=============================
            // get currencies info (new)
            //=============================
            $this->currencies = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}erp_acct_currency_info", ARRAY_A);
        }

        $currency = array_filter($this->currencies, function( $currency ) use ($name) {
            return $currency['name'] === $name;
        });

        if ( empty( $currency ) ) {
            return false;;
        }

        return (int) reset($currency)['id'];
    }





    /*===---------=====---======----***************************----===========
    *
    * INVOICE ( START )
    *
    *===///=====================================================================*/

    /**
     * Helper of invoice account details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_invoice_account_details_migration( $trn, $trn_no ) {
        if ( 'draft' === $trn['status'] || 'void' === $trn['status'] ) return;

        global $wpdb;

        $wpdb->insert(
            // `erp_acct_invoice_account_details`
            "{$wpdb->prefix}erp_acct_invoice_account_details", [
                'invoice_no'  => $trn_no,
                'trn_no'      => $trn_no,
                'trn_date'    => $trn['issue_date'],
                'particulars' => $trn['summary'],
                'debit'       => 0,
                'credit'      => 0,
                'created_at'  => $this->get_created_at( $trn['created_at'] ),
                'created_by'  => $trn['created_by']
            ]
        );
    }

    /**
     * Helper of invoice ledger details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_invoice_ledger_details_migration( $trn, $trn_no ) {
        if ( 'draft' === $trn['status'] || 'void' === $trn['status'] ) return;

        global $wpdb;

        $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::get_instance();

        $sales_ledger_id          = $ledger_map->get_ledger_id_by_slug('sales_revenue');
        $sales_discount_ledger_id = $ledger_map->get_ledger_id_by_slug('sales_discount');

        $wpdb->insert(
            // `erp_acct_ledger_details`
            "{$wpdb->prefix}erp_acct_ledger_details", [
                'ledger_id'   => $sales_ledger_id,
                'trn_no'      => $trn_no,
                'trn_date'    => $trn['issue_date'],
                'particulars' => $trn['summary'],
                'debit'       => 0,
                'credit'      => $trn['total'],
                'created_at'  => $this->get_created_at( $trn['created_at'] ),
                'created_by'  => $trn['created_by']
            ]
        );

        $wpdb->insert(
            // `erp_acct_ledger_details`
            "{$wpdb->prefix}erp_acct_ledger_details", [
                'ledger_id'   => $sales_discount_ledger_id,
                'trn_no'      => $trn_no,
                'trn_date'    => $trn['issue_date'],
                'particulars' => $trn['summary'],
                'debit'       => 0,
                'credit'      => 0,
                'created_at'  => $this->get_created_at( $trn['created_at'] ),
                'created_by'  => $trn['created_by']
            ]
        );
    }

    /**
     * Helper of invoice details migration
     *
     * @param array $invoices
     *
     * @return void
     */
    protected function _helper_invoice_details_migration( $id ) {
        global $wpdb;

        //=============================
        // get transaction items (old)
        //=============================
        $sql = "SELECT tran.created_at, tran.created_by, tran_item.* FROM {$wpdb->prefix}erp_ac_transactions AS tran
                LEFT JOIN {$wpdb->prefix}erp_ac_transaction_items AS tran_item ON tran.id = tran_item.transaction_id
                WHERE tran.id = %d";

        $transaction_items = $wpdb->get_results( $wpdb->prepare( $sql, $id ), ARRAY_A);

        for ( $i = 0; $i < count($transaction_items); $i++ ) {
            $trn_item = $transaction_items[$i];

            $amount     = (float) $trn_item['unit_price'] * (int) $trn_item['qty'];
            $discount   = ( $amount * (int) $trn_item['discount'] ) / 100;
            $item_total = $amount - $discount;
            $tax        = ( $item_total * (float) $trn_item['tax_rate'] ) / 100;

            $wpdb->insert(
                // `erp_acct_invoice_details`
                "{$wpdb->prefix}erp_acct_invoice_details", [
                    'trn_no'      => $id,
                    'product_id'  => $trn_item['product_id'],
                    'qty'         => (int) $trn_item['qty'],
                    'unit_price'  => $trn_item['unit_price'],
                    'discount'    => $discount,
                    'tax'         => $tax,
                    'item_total'  => $amount,
                    'created_at'  => $this->get_created_at( $trn_item['created_at'] ),
                    'created_by'  => $trn_item['created_by']
                ]
            );

            $sqls = [
                "UPDATE {$wpdb->prefix}erp_acct_invoices SET amount = amount + {$discount}, discount = discount + {$discount}, tax = tax + {$tax} WHERE voucher_no = %d",
                "UPDATE {$wpdb->prefix}erp_acct_invoice_account_details SET debit = debit + {$amount} + {$tax} - {$discount} WHERE trn_no = %d",
                "UPDATE {$wpdb->prefix}erp_acct_ledger_details SET debit = debit + {$discount} WHERE credit = 0.00 AND trn_no = %d",
                "UPDATE {$wpdb->prefix}erp_acct_ledger_details SET credit = credit + {$discount} WHERE debit = 0.00 AND trn_no = %d"
            ];

            foreach ( $sqls as $sql ) {
                $wpdb->query( $wpdb->prepare( $sql, $id) );
            }
        }
    }

    /*===---------=====---======----***************************----===========
    *
    * INVOICE ( END )
    *
    *===///=====================================================================*/





    /*===---------=====---======----***************************----===========
    *
    * INVOICE PAYMENT ( START )
    *
    *===///=====================================================================*/

    /**
     * Helper of invoice receipts account details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_invoice_receipts_account_details_migration( $trn, $trn_no ) {
        if ( 'draft' === $trn['status'] || 'void' === $trn['status'] ) return;

        global $wpdb;

        $sql1 = $wpdb->prepare( "SELECT child FROM {$wpdb->prefix}erp_ac_payments
            WHERE transaction_id = %d", $trn_no );
        $res1 = $wpdb->get_results( $sql1, ARRAY_A );

        $sql2 = $wpdb->prepare( "SELECT credit FROM {$wpdb->prefix}erp_ac_journals
            WHERE `type` = 'line_item' AND transaction_id = %d", $trn_no );
        $res2 = $wpdb->get_results( $sql2, ARRAY_A );

        $temp = [];

        if ( empty( $res1 ) ) { // it's direct payment transaction
            // erp_acct_people_account_details
            $wpdb->insert( $wpdb->prefix . 'erp_acct_people_account_details', array(
                'people_id'    => $trn['user_id'],
                'trn_no'       => $trn_no,
                'trn_date'     => $trn['issue_date'],
                'trn_by'       => 1,
                'particulars'  => $trn['summary'],
                'voucher_type' => 'credit',
                'debit'        => 0,
                'credit'       => $trn['total'],
                'created_at'   => $this->get_created_at( $trn['created_at'] ),
                'created_by'   => $trn['created_by']
            ) );
        } else {
            for ( $i = 0; $i < count( $res1 ); $i++ ) {
                // erp_acct_invoice_account_details
                $wpdb->insert( $wpdb->prefix . 'erp_acct_invoice_account_details', array(
                    'invoice_no'  => $res1[$i]['child'],
                    'trn_no'      => $trn_no,
                    'trn_date'    => $trn['issue_date'],
                    'particulars' => $trn['summary'],
                    'debit'       => 0,
                    'credit'      => $res2[$i]['credit'],
                    'created_at'  => $this->get_created_at( $trn['created_at'] ),
                    'created_by'  => $trn['created_by']
                ) );
            }
        }
    }

    /**
     * Helper of invoice receipts ledger details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_invoice_receipts_ledger_details_migration( $trn, $trn_no ) {
        if ( 'draft' === $trn['status'] || 'void' === $trn['status'] ) return;

        global $wpdb;

        $ledger_id = $wpdb->get_var( $wpdb->prepare( "SELECT ledger_id FROM {$wpdb->prefix}erp_ac_journals
            WHERE transaction_id = %d AND `type` = 'main'", $trn_no ) );

        $wpdb->insert(
            // `erp_acct_ledger_details`
            "{$wpdb->prefix}erp_acct_ledger_details", [
                'ledger_id'   => $ledger_id,
                'trn_no'      => $trn_no,
                'trn_date'    => $trn['issue_date'],
                'particulars' => $trn['summary'],
                'debit'       => $trn['total'],
                'credit'      => 0,
                'created_at'  => $this->get_created_at( $trn['created_at'] ),
                'created_by'  => $trn['created_by']
            ]
        );
    }

    /**
     * Helper of invoice receipts details migration
     *
     * @param array $invoices
     *
     * @return void
     */
    protected function _helper_invoice_receipts_details_migration( $id ) {
        global $wpdb;

        //=============================
        // get transaction items (old)
        //=============================
        $sql = "SELECT tran.created_at, tran.created_by, payment.child, tran_item.* FROM {$wpdb->prefix}erp_ac_transactions AS tran
                LEFT JOIN {$wpdb->prefix}erp_ac_transaction_items AS tran_item ON tran.id = tran_item.transaction_id
                LEFT JOIN {$wpdb->prefix}erp_ac_payments AS payment ON tran.id = payment.transaction_id
                WHERE tran.id = %d";

        $transaction_items = $wpdb->get_results( $wpdb->prepare( $sql, $id ), ARRAY_A );

        for ( $i = 0; $i < count($transaction_items); $i++ ) {
            $trn_item = $transaction_items[$i];

            $wpdb->insert(
                // `erp_acct_invoice_receipts_details`
                "{$wpdb->prefix}erp_acct_invoice_receipts_details", [
                    'voucher_no' => $id,
                    'invoice_no' => ! empty( $trn_item['child'] ) ? $trn_item['child'] : null,
                    'amount'     => $trn_item['line_total'],
                    'created_at' => $this->get_created_at( $trn_item['created_at'] ),
                    'created_by' => $trn_item['created_by']
                ]
            );
        }
    }

    /*===---------=====---======----***************************----===========
    *
    * INVOICE PAYMENT ( END )
    *
    *===///=====================================================================*/





    /*===---------=====---======----***************************----===========
    *
    * PAY PURCHASE ( START )
    *
    *===///=====================================================================*/


    /**
     * Helper of payment voucher details migration
     *
     * @param array $pay_purchases
     *
     * @return void
     */
    protected function _helper_payment_voucher_pay_purchase_details_migration( $id ) {
        global $wpdb;

        //=============================
        // get transaction items (old)
        //=============================
        $sql = "SELECT tran.created_at, tran.created_by, tran.summary, tran.total, payment.child, tran_item.* FROM
            {$wpdb->prefix}erp_ac_transactions AS tran LEFT JOIN {$wpdb->prefix}erp_ac_transaction_items AS tran_item ON tran.id = tran_item.transaction_id LEFT JOIN {$wpdb->prefix}erp_ac_payments AS payment ON tran.id = payment.transaction_id WHERE tran.id = %d";

        $transaction_items = $wpdb->get_results( $wpdb->prepare( $sql, $id ), ARRAY_A );

        for ( $i = 0; $i < count($transaction_items); $i++ ) {
            $trn_item = $transaction_items[$i];

            $wpdb->insert(
                // `erp_acct_pay_purchase_details`
                "{$wpdb->prefix}erp_acct_pay_purchase_details", [
                    'voucher_no'  => $id,
                    'purchase_no' => ! empty( $trn_item['child'] ) ? $trn_item['child'] : null,
                    'amount'      => $trn_item['line_total'],
                    'created_at'  => $this->get_created_at( $trn_item['created_at'] ),
                    'created_by'  => $trn_item['created_by']
                ]
            );
        }
    }

    /**
     * Helper of payment voucher account details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_payment_voucher_pay_purchase_account_details_migration( $trn, $trn_no ) {
        if ( 'draft' === $trn['status'] || 'void' === $trn['status'] ) return;

        global $wpdb;

        $sql1 = $wpdb->prepare( "SELECT child FROM {$wpdb->prefix}erp_ac_payments
            WHERE transaction_id = %d", $trn_no );
        $res1 = $wpdb->get_results( $sql1, ARRAY_A );

        $sql2 = $wpdb->prepare( "SELECT debit FROM {$wpdb->prefix}erp_ac_journals
            WHERE `type` = 'line_item' AND transaction_id = %d", $trn_no );
        $res2 = $wpdb->get_results( $sql2, ARRAY_A );

        $temp = [];

        if ( empty( $res1 ) ) { // it's direct payment transaction
            // erp_acct_people_account_details
            $wpdb->insert( $wpdb->prefix . 'erp_acct_people_account_details', array(
                'people_id'    => $trn['user_id'],
                'trn_no'       => $trn_no,
                'trn_date'     => $trn['issue_date'],
                'trn_by'       => 1,
                'particulars'  => $trn['summary'],
                'voucher_type' => 'debit',
                'debit'        => $trn['total'],
                'credit'       => 0,
                'created_at'   => $this->get_created_at( $trn['created_at'] ),
                'created_by'   => $trn['created_by']
            ) );
        } else {
            for ( $i = 0; $i < count( $res1 ); $i++ ) {
                // erp_acct_purchase_account_details
                $wpdb->insert( $wpdb->prefix . 'erp_acct_purchase_account_details', array(
                    'purchase_no'  => $res1[$i]['child'],
                    'trn_no'      => $trn_no,
                    'trn_date'    => $trn['issue_date'],
                    'particulars' => $trn['summary'],
                    'debit'       => $res2[$i]['debit'],
                    'credit'      => 0,
                    'created_at'  => $this->get_created_at( $trn['created_at'] ),
                    'created_by'  => $trn['created_by']
                ) );
            }
        }
    }

    /**
     * Helper of payment voucher ledger details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_payment_voucher_pay_purchase_ledger_details_migration( $trn, $trn_no ) {
        if ( 'draft' === $trn['status'] || 'void' === $trn['status'] ) return;

        global $wpdb;

        $ledger_id = $wpdb->get_var( $wpdb->prepare( "SELECT ledger_id FROM {$wpdb->prefix}erp_ac_journals
            WHERE transaction_id = %d AND `type` = 'main'", $trn_no ) );

        $wpdb->insert(
            // `erp_acct_ledger_details`
            "{$wpdb->prefix}erp_acct_ledger_details", [
                'ledger_id'   => $ledger_id,
                'trn_no'      => $trn_no,
                'trn_date'    => $trn['issue_date'],
                'particulars' => $trn['summary'],
                'debit'       => 0,
                'credit'      => $trn['total'],
                'created_at'  => $this->get_created_at( $trn['created_at'] ),
                'created_by'  => $trn['created_by']
            ]
        );
    }

    /*===---------=====---======----***************************----===========
    *
    * PAY PURCHASE ( END )
    *
    *===///=====================================================================*/




    /*===---------=====---======----***************************----===========
    *
    * PURCHASE ( START )
    *
    *===///=====================================================================*/

    /**
     * Helper of vendor credit purchase ledger details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_vendor_credit_purchase_ledger_details_migration( $trn, $trn_no ) {
        if ( 'draft' === $trn['status'] || 'void' === $trn['status'] ) return;

        global $wpdb;

        $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::get_instance();

        $purchase_ledger_id = $ledger_map->get_ledger_id_by_slug('purchase');

        $wpdb->insert(
            // `erp_acct_ledger_details`
            "{$wpdb->prefix}erp_acct_ledger_details", [
                'ledger_id'   => $purchase_ledger_id,
                'trn_no'      => $trn_no,
                'trn_date'    => $trn['issue_date'],
                'particulars' => $trn['summary'],
                'debit'       => $trn['total'],
                'credit'      => 0,
                'created_at'  => $this->get_created_at( $trn['created_at'] ),
                'created_by'  => $trn['created_by']
            ]
        );
    }

    /**
     * Helper of payment voucher details migration
     *
     * @param array $purchases
     *
     * @return void
     */
    protected function _helper_vendor_credit_purchase_details_migration( $id ) {
        global $wpdb;

        //=============================
        // get transaction items (old)
        //=============================
        $sql = "SELECT tran.created_at, tran.created_by, tran_item.* FROM {$wpdb->prefix}erp_ac_transactions AS tran
                LEFT JOIN {$wpdb->prefix}erp_ac_transaction_items AS tran_item ON tran.id = tran_item.transaction_id
                WHERE tran.id = %d";

        $transaction_items = $wpdb->get_results( $wpdb->prepare( $sql, $id ), ARRAY_A );

        for ( $i = 0; $i < count($transaction_items); $i++ ) {
            $trn_item = $transaction_items[$i];

            $wpdb->insert(
                // `erp_acct_expense_details`
                "{$wpdb->prefix}erp_acct_purchase_details", [
                    'trn_no'     => $id,
                    'product_id' => $trn_item['product_id'],
                    'qty'        => (int) $trn_item['qty'],
                    'price'      => $trn_item['unit_price'],
                    'amount'     => $trn_item['line_total'],
                    'created_at' => $this->get_created_at( $trn_item['created_at'] ),
                    'created_by' => $trn_item['created_by']
                ]
            );
        }
    }

    /**
     * Helper of purchase account ledger details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_vendor_credit_purchase_account_details_migration( $trn, $trn_no ) {
        if ( 'draft' === $trn['status'] || 'void' === $trn['status'] ) return;

        global $wpdb;

        $wpdb->insert(
            // `erp_acct_purchase_account_details`
            "{$wpdb->prefix}erp_acct_purchase_account_details", [
                'purchase_no' => $trn_no,
                'trn_no'      => $trn_no,
                'trn_date'    => $trn['issue_date'],
                'particulars' => $trn['summary'],
                'debit'       => 0,
                'credit'      => $trn['total'],
                'created_at'  => $this->get_created_at( $trn['created_at'] ),
                'created_by'  => $trn['created_by']
            ]
        );
    }


    /*===---------=====---======----***************************----===========
    *
    * PURCHASE ( END )
    *
    *===///=====================================================================*/




    /*===---------=====---======----***************************----===========
    *
    * BANK TRANSFER ( START )
    *
    *===///=====================================================================*/

    /**
     * Helper of bank ledger details migration
     *
     * @param array $trn
     * @param int $voucher_no
     *
     * @return void
     */
    protected function _helper_bank_transfers_migration( $transfer, $trn_id ) {
        global $wpdb;

        $trns = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_ac_journals WHERE transaction_id = %d AND NOT(debit IS NULL AND credit IS NULL)", $trn_id),
            ARRAY_A );

        foreach ( $trns as $trn ) {
            if ( 'main' == $trn['type'] ) {
                $transfer['from_account_id'] = $trn['ledger_id'];

                $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
                    'ledger_id'   => $trn['ledger_id'],
                    'trn_no'      => $trn_id,
                    'particulars' => $transfer['summary'],
                    'debit'       => 0,
                    'credit'      => $transfer['total'],
                    'trn_date'    => $transfer['issue_date'],
                    'created_at'  => $this->get_created_at( $transfer['created_at'] ),
                    'created_by'  => get_current_user_id(),
                ) );
            }

            if ( 'line_item' == $trn['type'] ) {
                $transfer['to_account_id'] = $trn['ledger_id'];

                $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
                    'ledger_id'   => $trn['ledger_id'],
                    'trn_no'      => $trn_id,
                    'particulars' => $transfer['summary'],
                    'debit'       => $transfer['total'],
                    'credit'      => 0,
                    'trn_date'    => $transfer['issue_date'],
                    'created_at'  => $this->get_created_at( $transfer['created_at'] ),
                    'created_by'  => get_current_user_id(),
                ) );
            }
        }

        $wpdb->insert( $wpdb->prefix . 'erp_acct_transfer_voucher', array(
            'voucher_no' => $trn_id,
            'amount'     => $transfer['total'],
            'ac_from'    => $transfer['from_account_id'],
            'ac_to'      => $transfer['to_account_id'],
            'particulars'=> $transfer['summary'],
            'trn_date'   => $transfer['issue_date'],
            'created_at' => $this->get_created_at( $transfer['created_at'] ),
            'created_by' => get_current_user_id(),
        ) );
    }

}
