<?php
namespace WeDevs\ERP\Updates\BP;

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

        $trn = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_ac_transactions WHERE id = %d", $trn_id),
            ARRAY_A );

        $product_ids = $wpdb->get_results(
            $wpdb->prepare( "SELECT product_id FROM  {$wpdb->prefix}erp_ac_transaction_items where transaction_id = %d AND product_id <> 0", $trn_id),
        ARRAY_A );

        $has_inventory = ! empty( $product_ids ) ? true : false;
        $status        = erp_acct_trn_status_by_id('closed');

        // Keep various id`s
        $invoices  = [];
        $payments  = [];
        $expenses  = [];
        $transfers = [];

        $people = erp_get_people( $trn['user_id'] );

        if ( 'invoice' === $trn['form_type'] ) {

            $wpdb->insert(
                // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'type'       => 'invoice',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $voucher_no = $wpdb->insert_id;

            $wpdb->insert(
                // `erp_acct_invoices`
                "{$wpdb->prefix}erp_acct_invoices", [
                    'voucher_no'      => $voucher_no,
                    'customer_id'     => $trn['user_id'],
                    'customer_name'   => $people->first_name . ' ' . $people->last_name,
                    'trn_date'        => $trn['issue_date'],
                    'due_date'        => $trn['due_date'],
                    'billing_address' => $trn['billing_address'],
                    'amount'          => $trn['total'],
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

            $invoices[$voucher_no] = $wpdb->insert_id;
        } // invoice

        elseif ( 'payment' === $trn['form_type'] ) {
            $wpdb->insert(
                // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'type'       => 'payment',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $voucher_no = $wpdb->insert_id;

            $wpdb->insert(
                // `erp_acct_invoice_receipts`
                "{$wpdb->prefix}erp_acct_invoice_receipts", [
                    'voucher_no'       => $voucher_no,
                    'customer_id'      => $trn['user_id'],
                    'customer_name'    => $people->first_name . ' ' . $people->last_name,
                    'trn_date'         => $trn['issue_date'],
                    'amount'           => $trn['total'],
                    'particulars'      => $trn['summary'],
                    'attachments'      => $trn['files'],
                    'status'           => $status,
                    'trn_by'           => 1,
                    'trn_by_ledger_id' => 1,
                    'created_at'       => $this->get_created_at( $trn['created_at'] ),
                    'created_by'       => $trn['created_by']
                ]
            );

            $payments[$voucher_no] = $wpdb->insert_id;

        } // payment

        elseif ( 'payment_voucher' === $trn['form_type'] ) {
            $wpdb->insert(
                // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'type'       => 'invoice',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $voucher_no = $wpdb->insert_id;

            $wpdb->insert(
                // `erp_acct_expenses`
                "{$wpdb->prefix}erp_acct_expenses", [
                    'voucher_no'       => $voucher_no,
                    'people_id'        => $trn['user_id'],
                    'people_name'      => $people->first_name . ' ' . $people->last_name,
                    'trn_date'         => $trn['issue_date'],
                    'address'          => $trn['billing_address'],
                    'ref'              => $trn['ref'],
                    'check_no'         => null,
                    'amount'           => $trn['trans_total'],
                    'particulars'      => $trn['summary'],
                    'attachments'      => $trn['files'],
                    'status'           => 4,
                    'trn_by'           => 1,
                    'trn_by_ledger_id' => 1,
                    'created_at'       => $this->get_created_at( $trn['created_at'] ),
                    'created_by'       => $trn['created_by']
                ]
            );

            $expenses[$voucher_no] = $wpdb->insert_id;

            $this->_helper_payment_voucher_expense_people_details_migration($trn, $voucher_no);
            $this->_helper_payment_voucher_expense_ledger_details_migration($trn, $voucher_no);
        } // payment_voucher

        elseif ( 'vendor_credit' === $trn['form_type'] ) {
            
        } // vendor_credit

        elseif ( 'bank' === $trn['form_type'] && ! $has_inventory ) {
            $trn = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_ac_transactions WHERE id = %d", $trn_id),
                ARRAY_A );
            $wpdb->insert(
            // `erp_acct_voucher_no`
                "{$wpdb->prefix}erp_acct_voucher_no", [
                    'type'       => 'transfer_voucher',
                    'currency'   => $this->get_currecny_id( $trn['currency'] ),
                    'created_at' => $this->get_created_at( $trn['created_at'] ),
                    'created_by' => $trn['created_by']
                ]
            );

            $voucher_no = $wpdb->insert_id;

            $this->_helper_bank_transfers_migration($trn, $voucher_no, $trn_id);
        } // transfer

        if ( ! empty( $invoices ) ) {
            $this->_helper_invoice_details_migration($invoices);
        }

        if ( ! empty( $payments ) ) {
            $this->_helper_invoice_receipts_details_migration($payments);
        }

        if ( ! empty( $expenses ) ) {
            $this->_helper_payment_voucher_expense_details_migration($expenses);
        }

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

        $first = 0;

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
            return $first;
        }

        return (int) $currency[$first]['id'];
    }

    /**
     * Helper of payment voucher expense people details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_payment_voucher_expense_people_details_migration( $trn, $trn_no ) {
        global $wpdb;

        $wpdb->insert(
            // `erp_acct_people_details`
            "{$wpdb->prefix}erp_acct_people_details", [
                'people_id'    => $trn['user_id'],
                'trn_no'       => $trn_no,
                'particulars'  => $trn['summary'],
                'debit'        => 0,
                'credit'       => 0,
                'voucher_type' => 'invoice',
                'trn_date'     => $trn['issue_date'],
                'created_at'   => $this->get_created_at( $trn['created_at'] ),
                'created_by'   => $trn['created_by']
            ]
        );
    }

    /**
     * Helper of payment voucher expense ledger details migration
     *
     * @param array $trn
     * @param int $trn_no
     *
     * @return void
     */
    protected function _helper_payment_voucher_expense_ledger_details_migration( $trn, $trn_no ) {
        global $wpdb;

        $ledger_map = \WeDevs\ERP\Accounting\Includes\Classes\Ledger_Map::getInstance();

        $cash_ledger_id = $ledger_map->get_ledger_id_by_slug('cash');

        $wpdb->insert(
            // `erp_acct_ledger_details`
            "{$wpdb->prefix}erp_acct_ledger_details", [
                'ledger_id'   => $cash_ledger_id, // Please review me
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
     * Helper of invoice details migration
     *
     * @param array $invoices
     *
     * @return void
     */
    protected function _helper_invoice_details_migration( $invoices ) {
        global $wpdb;

        $ids = implode( ',', $invoices );

        //=============================
        // get transaction items (old)
        //=============================
        $sql1 = "SELECT tran.created_at, tran.created_by, tran_item.* FROM {$wpdb->prefix}erp_ac_transactions AS tran
                LEFT JOIN {$wpdb->prefix}erp_ac_transaction_items AS tran_item ON tran.id = tran_item.transaction_id
                WHERE tran.id IN ({$ids})";

        $transaction_items = $wpdb->get_results($sql1, ARRAY_A);

        for ( $i = 0; $i < count($transaction_items); $i++ ) {
            $trn_item = $transaction_items[$i];

            $trn_no     = array_search( (int) $trn_item['transaction_id'], $invoices );
            $amount     = (float) $trn_item['unit_price'] * (int) $trn_item['qty'];
            $discount   = ( $amount * (int) $trn_item['discount'] ) / 100;
            $item_total = $amount - $discount;
            $tax        = ( $item_total * (float) $trn_item['tax_rate'] ) / 100;

            $wpdb->insert(
                // `erp_acct_invoice_details`
                "{$wpdb->prefix}erp_acct_invoice_details", [
                    'trn_no'      => $trn_no,
                    'product_id'  => $trn_item['product_id'],
                    'qty'         => (int) $trn_item['qty'],
                    'unit_price'  => $trn_item['unit_price'],
                    'discount'    => $discount,
                    'tax'         => $tax,
                    'item_total'  => $item_total,
                    'created_at'  => $this->get_created_at( $trn_item['created_at'] ),
                    'created_by'  => $trn_item['created_by']
                ]
            );

            $sql = "UPDATE {$wpdb->prefix}erp_acct_invoices SET discount = discount + {$discount}, tax = tax + {$tax} WHERE voucher_no = %d";

            $wpdb->query( $wpdb->prepare( $sql, $trn_no) );
        }
    }

    /**
     * Helper of invoice receipts details migration
     *
     * @param array $invoices
     *
     * @return void
     */
    protected function _helper_invoice_receipts_details_migration( $payments ) {
        global $wpdb;

        $ids = implode( ',', $payments );

        //=============================
        // get transaction items (old)
        //=============================
        $sql1 = "SELECT tran.created_at, tran.created_by, tran.invoice_number, tran_item.* FROM {$wpdb->prefix}erp_ac_transactions AS tran
                LEFT JOIN {$wpdb->prefix}erp_ac_transaction_items AS tran_item ON tran.id = tran_item.transaction_id
                WHERE tran.id IN ({$ids})";

        $transaction_items = $wpdb->get_results($sql1, ARRAY_A);

        for ( $i = 0; $i < count($transaction_items); $i++ ) {
            $trn_item = $transaction_items[$i];

            $trn_no = array_search( (int) $trn_item['transaction_id'], $payments );

            $wpdb->insert(
                // `erp_acct_invoice_receipts_details`
                "{$wpdb->prefix}erp_acct_invoice_receipts_details", [
                    'voucher_no' => $trn_no,
                    'invoice_no' => $trn_item['invoice_number'],
                    'amount'     => $trn_item['line_total'],
                    'created_at' => $this->get_created_at( $trn_item['created_at'] ),
                    'created_by' => $trn_item['created_by']
                ]
            );
        }
    }

    /**
     * Helper of payment voucher details migration
     *
     * @param array $invoices
     *
     * @return void
     */
    protected function _helper_payment_voucher_expense_details_migration( $expenses ) {
        global $wpdb;

        $ids = implode( ',', $expenses );

        //=============================
        // get transaction items (old)
        //=============================
        $sql1 = "SELECT tran.created_at, tran.created_by, tran.invoice_number, journal.ledger_id, tran_item.* FROM {$wpdb->prefix}erp_ac_transactions AS tran
                LEFT JOIN {$wpdb->prefix}erp_ac_transaction_items AS tran_item ON tran.id = tran_item.transaction_id
                LEFT JOIN {$wpdb->prefix}erp_ac_journals AS journal ON journal.id = tran_item.journal_id
                WHERE tran.id IN ({$ids})";

        $transaction_items = $wpdb->get_results($sql1, ARRAY_A);

        for ( $i = 0; $i < count($transaction_items); $i++ ) {
            $trn_item = $transaction_items[$i];

            $trn_no = array_search( (int) $trn_item['transaction_id'], $expenses );

            $wpdb->insert(
                // `erp_acct_expense_details`
                "{$wpdb->prefix}erp_acct_expense_details", [
                    'trn_no'      => $trn_no,
                    'ledger_id'   => 2018 . $trn_item['ledger_id'],
                    'particulars' => $trn_item['description'],
                    'amount'      => $trn_item['line_total'],
                    'created_at'  => $this->get_created_at( $trn_item['created_at'] ),
                    'created_by'  => $trn_item['created_by']
                ]
            );

            $sqls = [
                "UPDATE {$wpdb->prefix}erp_acct_people_details SET debit = debit + {$trn_item['line_total']} WHERE trn_no = %d"
            ];

            foreach ( $sqls as $sql ) {
                $wpdb->query( $wpdb->prepare( $sql, $trn_no) );
            }
        }
    }

    /**
     * Helper of bank ledger details migration
     *
     * @param array $trn
     * @param int $voucher_no
     *
     * @return void
     */
    protected function _helper_bank_transfers_migration( $transfer, $voucher_no, $trn_id ) {
        global $wpdb;

        $trns = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}erp_ac_journals WHERE transaction_id = %d AND NOT(debit IS NULL AND credit IS NULL)", $trn_id),
            ARRAY_A );

        foreach ( $trns as $trn ) {
            if ( 'main' == $trn['type'] ) {
                $transfer['from_account_id'] = $trn['ledger_id'];

                $wpdb->insert( $wpdb->prefix . 'erp_acct_ledger_details', array(
                    'ledger_id'   => $trn['ledger_id'],
                    'trn_no'      => $voucher_no,
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
                    'trn_no'      => $voucher_no,
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
            'voucher_no' => $voucher_no,
            'amount'     => $transfer['total'],
            'ac_from'    => $transfer['from_account_id'],
            'ac_to'      => $transfer['to_account_id'],
            'particulars'=> $transfer['summary'],
            'trn_date'   => $transfer['issue_date'],
            'created_at' => $this->get_created_at( $transfer['created_at'] ),
            'created_by' => get_current_user_id(),
        ) );

        $transfers[$voucher_no] = $wpdb->insert_id;
    }

}
