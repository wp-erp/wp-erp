<?php

/**
 * Get all transaction
 *
 * @param $args array
 *
 * @return array
 */
function erp_ac_get_all_transaction( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'type'       => 'expense',
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'issue_date',
        'order'      => 'DESC',
        'output_by'  => 'object'
    );

    $args            = wp_parse_args( $args, $defaults );
    $cache_key       = 'erp-ac-transaction-all-' . md5( serialize( $args ) );
    $items           = wp_cache_get( $cache_key, 'erp' );
    $financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    $financial_end   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

    if ( false === $items ) {
        $transaction = new WeDevs\ERP\Accounting\Model\Transaction();
        $db          = new \WeDevs\ORM\Eloquent\Database();

        if ( isset( $args['select'] ) && count( $args['select'] ) ) {
            //demo [ '*', $db->raw( 'MONTHNAME( issue_date ) as month' ) ]
            $transaction = $transaction->select( $args['select'] );
        }

        if ( isset( $args['join'] ) && count( $args['join'] ) ) {

            $transaction = $transaction->with( $args['join'] );
        }

        if ( isset( $args['with_ledger'] ) && $args['with_ledger'] ) {
            $transaction = $transaction->with( ['journals' => function( $q ) {
                return $q->with('ledger');
            }] );
        }

        if ( isset( $args['user_id'] ) &&  is_array( $args['user_id'] ) && array_key_exists( 'in', $args['user_id'] ) ) {
            $transaction = $transaction->whereIn( 'user_id', $args['user_id']['in'] );
        } else if ( isset( $args['user_id'] ) &&  is_array( $args['user_id'] ) && array_key_exists( 'not_in', $args['user_id'] ) ) {
            $transaction = $transaction->whereNotIn( 'user_id', [$args['user_id']['not_in']] );
        } else if ( isset( $args['user_id'] ) &&  ! is_array( $args['user_id'] ) ) {
            $transaction = $transaction->where( 'user_id', '=', $args['user_id'] );
        }

        if ( isset( $args['created_by'] ) &&  is_array( $args['created_by'] ) && array_key_exists( 'in', $args['created_by'] ) ) {
            $transaction = $transaction->whereIn( 'created_by', $args['created_by']['in'] );
        } else if ( isset( $args['created_by'] ) &&  is_array( $args['created_by'] ) && array_key_exists( 'not_in', $args['created_by'] ) ) {
            $transaction = $transaction->whereNotIn( 'created_by', [$args['created_by']['not_in']] );
        } else if ( isset( $args['created_by'] ) &&  ! is_array( $args['created_by'] ) ) {
            $transaction = $transaction->where( 'created_by', '=', $args['created_by'] );
        }

        if ( isset( $args['start_date'] ) && ! empty( $args['start_date'] ) ) {
            $transaction = $transaction->where( 'issue_date', '>=', $args['start_date'] );
        } else {
            $transaction = $transaction->where( 'issue_date', '>=', $financial_start );
        }

        if ( isset( $args['end_date'] ) && ! empty( $args['end_date'] ) ) {
            $transaction = $transaction->where( 'issue_date', '<=', $args['end_date'] );
        } else {
            $transaction = $transaction->where( 'issue_date', '<=', $financial_end );
        }

        if ( isset( $args['start_due'] ) && ! empty( $args['start_due'] ) ) {
            $transaction = $transaction->where( 'due_date', '>=', $args['start_due'] );
        }

        if ( isset( $args['end_due'] ) && ! empty( $args['end_due'] ) ) {
            $transaction = $transaction->where( 'due_date', '<=', $args['end_due'] );
        }

        if ( isset( $args['ref'] ) && ! empty( $args['ref'] ) ) {
            $transaction = $transaction->where( 'ref', '=', $args['ref'] );
        }

        if ( isset( $args['status'] ) && $args['status'] == 'deleted' ) {
            //$transaction = $transaction->where( 'status', '==', 'deleted' );
        } else {
            $transaction = $transaction->where( 'status', '!=', 'deleted' );
        }

        if ( isset( $args['status'] ) &&  is_array( $args['status'] ) && array_key_exists( 'in', $args['status'] ) ) {
            $transaction = $transaction->whereIn( 'status', $args['status']['in'] );
        } else if ( isset( $args['status'] ) &&  is_array( $args['status'] ) && array_key_exists( 'not_in', $args['status'] ) ) {
            $transaction = $transaction->whereNotIn( 'status', [$args['status']['not_in']] );
        } else if ( isset( $args['status'] ) &&  ! is_array( $args['status'] ) ) {
            $transaction = $transaction->where( 'status', '=', $args['status'] );
        }

        if ( isset( $args['type'] ) && ( ( is_array( $args['type'] ) && in_array( 'journal', $args['type'] ) ) || $args['type'] == 'journal' )  ) {
            $transaction = $transaction->orWhereNull( 'status' );
        }

        if ( isset( $args['form_type'] ) &&  is_array( $args['form_type'] ) && array_key_exists( 'in', $args['form_type'] ) ) {
            $transaction = $transaction->whereIn( 'form_type', $args['form_type']['in'] );
        } else if ( isset( $args['form_type'] ) &&  is_array( $args['form_type'] ) && array_key_exists( 'not_in', $args['form_type'] ) ) {
            $transaction = $transaction->whereNotIn( 'form_type', [$args['form_type']['not_in']] );
        } else if ( isset( $args['form_type'] ) &&  ! is_array( $args['form_type'] ) ) {
            $transaction = $transaction->where( 'form_type', '=', $args['form_type'] );
        }

        if ( isset( $args['wherein'] ) && is_array( $args['wherein'] ) ) {
            foreach ( $args['wherein'] as $field => $value ) {
                $transaction = $transaction->whereIn( $field, $value );
            }
        }

        if ( isset( $args['parent'] ) ) {
            $transaction = $transaction->where( 'parent', '=', $args['parent'] );
        }

        if ( isset( $args['id'] ) && ! empty( $args['id'] ) ) {
            $transaction = $transaction->where( 'id', '=', $args['id'] );
        }

        if ( isset( $args['groupby'] ) && ! empty( $args['groupby'] ) ) {

            if ( $args['number'] != -1 ) {
                $transaction = $transaction->skip( $args['offset'] )->take( $args['number'] );
            }

            $items = $transaction->type( $args['type'] )
                ->orderBy( $args['orderby'], $args['order'] )
                ->orderBy( 'created_at', $args['order'] )
                ->get()
                ->groupBy( $args['groupby'] )
                ->toArray();
        } else {

            if ( $args['number'] != -1 ) {
                $transaction = $transaction->skip( $args['offset'] )->take( $args['number'] );
            }

            $items = $transaction->type( $args['type'] )
                ->orderBy( $args['orderby'], $args['order'] )
                ->orderBy( 'created_at', $args['order'] )
                ->get()
                ->toArray();

        }

        if ( $args['output_by'] == 'object' ) {
            $items = erp_array_to_object( $items );
        }

        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

/**
 * Fetch all transaction from database
 *
 * @return array
 */
function erp_ac_get_transaction_count( $type = 'expense', $user_id = 0 ) {
    $cache_key = 'erp-ac-' . $type . '-' . $user_id . '-count';
    $count     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $count ) {
        $trans = new WeDevs\ERP\Accounting\Model\Transaction();

        if ( $user_id ) {
            $trans = $trans->where( 'user_id', '=', $user_id );
        }

        $count = $trans->type( $type )->count();
    }

    return (int) $count;
}

/**
 * Fetch a single transaction from database
 *
 * @param int   $id
 *
 * @return array
 */
function erp_ac_get_transaction( $id = 0 ) {
    $cache_key   = 'erp-ac-transaction' . $id;
    $transaction = wp_cache_get( $cache_key, 'erp' );
    $results     = [];

    if ( false === $transaction ) {
        $transaction = WeDevs\ERP\Accounting\Model\Transaction::find( $id ); //->toArray();

        if ( ! empty( $transaction ) ) {
            $results = $transaction->toArray();
        }
    }

    return $results;
}

/**
 * Chck from DB is invoice number unique or not
 *
 * @param  string  $invoice_number
 * @param  stirng  $form_type
 * @param  boolean $is_update
 * @param  mixed $trns_id
 *
 * @return boolean
 */
function erp_ac_check_invoice_number_unique( $invoice, $form_type, $is_update = false, $trns_id = false ) {
    $invoice_format = erp_ac_get_invoice_format( $form_type );
    $invoice_number = erp_ac_get_invoice_num_fromat_from_submit_invoice( $invoice, $invoice_format );

    if ( $is_update ) {
        $trans = new \WeDevs\ERP\Accounting\Model\Transaction();
         if ( $invoice_number == 0 ) {
            $trans = $trans->where( 'invoice_format', '=', $invoice )
                ->where( 'form_type', '=', $form_type )
                ->where( 'id', '!=', $trns_id )
                ->get()
                ->toArray();
        } else {
            $trans = $trans->where( 'invoice_number', '=', $invoice_number )
                ->where( 'form_type', '=', $form_type )
                ->where( 'id', '!=', $trns_id )
                ->get()
                ->toArray();
        }

    } else {

        $trans = new \WeDevs\ERP\Accounting\Model\Transaction();
        if ( $invoice_number == 0 ) {
            $trans = $trans->where( 'invoice_format', '=', $invoice )
                ->where( 'form_type', '=', $form_type )
                ->get()
                ->toArray();
        } else {
            $trans = $trans->where( 'invoice_number', '=', $invoice_number )
                ->where( 'form_type', '=', $form_type )
                ->get()
                ->toArray();
        }
    }

    if ( $trans ) {
        return false;
    }

    return true;
}

function er_ac_insert_transaction_permiss( $args, $is_update ) {

    if ( $args['form_type'] == 'invoice' || $args['form_type'] == 'vendor_credit' ) {
        if( strtotime( $args['issue_date'] ) > strtotime( $args['due_date'] ) ) {
            return new WP_Error( 'error', __( 'Due date should be greater than issue date', 'erp' ) );
        }
    }

    if ( $args['type'] == 'sales' && $args['form_type'] == 'payment' && $args['status'] == 'draft' ) {
        if ( ! erp_ac_create_sales_payment() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }
    }

    if ( $args['type'] == 'sales' && $args['form_type'] == 'payment' && $args['status'] == 'closed' ) {
        if ( ! erp_ac_publish_sales_payment() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }
    }

    if ( $args['type'] == 'sales' && $args['form_type'] == 'invoice' && $args['status'] == 'awaiting_payment' ) {
        if ( ! erp_ac_publish_sales_invoice() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }
    }

    if ( $args['type'] == 'sales' && $args['form_type'] == 'invoice' && $args['status'] == 'draft' ) {
        if ( ! erp_ac_create_sales_invoice() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }
    }

    if ( $args['type'] == 'expense' && $args['form_type'] == 'payment_voucher' && $args['status'] == 'paid' ) {
        if ( ! erp_ac_publish_expenses_voucher() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }
    }

    if ( $args['type'] == 'expense' && $args['form_type'] == 'payment_voucher' && $args['status'] == 'draft' ) {
        if ( ! erp_ac_create_expenses_voucher() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }
    }

    if ( $args['type'] == 'expense' && $args['form_type'] == 'vendor_credit' && $args['status'] == 'awaiting_payment' ) {
        if ( ! erp_ac_publish_expenses_credit() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }
    }

    if ( $args['type'] == 'expense' && $args['form_type'] == 'vendor_credit' && $args['status'] == 'draft' ) {
        if ( ! erp_ac_create_expenses_credit() ) {
            return new WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }
    }

    if ( ( $args['form_type'] == 'invoice' || $args['form_type'] == 'payment' ) && ! erp_ac_check_invoice_number_unique( $args['invoice_number'], $args['form_type'], $is_update, $args['id'] ) ) {
        return new WP_Error( 'error', __( 'Invoice already exists. Please use an unique number', 'erp' ) );
    }

    if ( ! intval( $args['user_id'] ) ) {
        return new WP_Error( 'error', __( 'User ID (Customer or Vendor) requird', 'erp' ) );
    }
}

/**
 * Insert a new transaction
 *
 * @param array $args
 */
function erp_ac_insert_transaction( $args = [], $items = [] ) {
    global $wpdb;

    if ( ! $items ) {
        return new WP_Error( 'no-items', __( 'No transaction items found', 'erp' ) );
    }

    $defaults = array(
        'id'              => null,
        'type'            => '',
        'form_type'       => '',
        'account_id'      => '',
        'status'          => '',
        'user_id'         => '',
        'billing_address' => '',
        'ref'             => '',
        'issue_date'      => '',
        'summary'         => '',
        'total'           => '',
        'sub_total'       => '0.00',
        'invoice_number'  => erp_ac_get_auto_generated_invoice( $args['form_type'] ),
        'invoice_format'  => erp_ac_get_invoice_format( $args['form_type'] ),
        'files'           => '',
        'currency'        => '',
        'created_by'      => get_current_user_id(),
        'created_at'      => current_time( 'mysql' )
    );

    $args = wp_parse_args( $args, $defaults ); //strpos($mystring, $findme);

    $is_update  = $args['id'] && ! is_array( $args['id'] ) ? true : false;

    $permission = er_ac_insert_transaction_permiss( $args, $is_update );

    if ( is_wp_error( $permission ) ) {
        return $permission;
    }

    $invoice = erp_ac_get_invoice_num_fromat_from_submit_invoice( $args['invoice_number'], $args['invoice_format'] );

    if ( $invoice == 0 ) {
        $args['invoice_format'] = $args['invoice_number'];
        $args['invoice_number'] = 0;
    } else {
        $args['invoice_number'] = $invoice;
    }

    $table_name = $wpdb->prefix . 'erp_ac_transactions';

    // get valid transaction type and form type
    if ( ! in_array( $args['type'], [ 'expense', 'sales', 'transfer' ] ) ) {
        return new WP_Error( 'invalid-trans-type', __( 'Error: Invalid transaction type.', 'erp' ) );
    }

    $form_types = ( $args['type'] == 'expense' ) ? erp_ac_get_expense_form_types() : erp_ac_get_sales_form_types();

    if ( $args['type'] == 'expense' ) {
        $form_types = erp_ac_get_expense_form_types();
    } else if ( $args['type'] == 'transfer' ) {
        $form_types = erp_ac_get_bank_form_types();
    } else {
        $form_types = erp_ac_get_sales_form_types();
    }

    if ( ! array_key_exists( $args['form_type'], $form_types ) ) {
        return new WP_Error( 'invalid-form-type', __( 'Error: Invalid form type', 'erp' ) );
    }

    $form_type = $form_types[ $args['form_type'] ];

    // some basic validation
    if ( empty( $args['issue_date'] ) ) {
        return new WP_Error( 'no-issue_date', __( 'No Issue Date provided.', 'erp' ) );
    }
    if ( empty( $args['total'] ) ) {
        return new WP_Error( 'no-total', __( 'No Total provided.', 'erp' ) );
    }

    // remove row id to determine if new or update
    $row_id          = (int) $args['id'];
    $main_account_id = (int) $args['account_id'];

    //unset( $args['id'] );
    unset( $args['account_id'] );

    // BEGIN INSERTION
    try {
        $wpdb->query( 'START TRANSACTION' );

        if ( $is_update ) {

            $trans = WeDevs\ERP\Accounting\Model\Transaction::find( $args['id'] )->update( $args );
            $trans_id    = $trans ? $args['id'] : false;
            erp_ac_update_invoice_number( $args['form_type'] );
        } else {

            $trans    = WeDevs\ERP\Accounting\Model\Transaction::create( $args );
            $trans_id = $trans->id;
            if ( $trans->id ) {
                erp_ac_update_invoice_number( $args['form_type'] );
            }
        }

        if ( ! $trans_id ) {
            throw new Exception( __( 'Could not create transaction', 'erp' ) );
        }


        if ( $is_update ) {
            $main_journal = WeDevs\ERP\Accounting\Model\Journal::where( 'transaction_id', '=', $args['id'] )
                ->where( 'type', '=', 'main' )
                ->first()->update([
                        'ledger_id'        => $main_account_id,
                        $form_type['type'] => $args['total']
                    ]);

        } else {
            // create the main journal entry
            $main_journal = WeDevs\ERP\Accounting\Model\Journal::create([
                'ledger_id'        => $main_account_id,
                'transaction_id'   => $trans_id,
                'type'             => 'main',
                $form_type['type'] => $args['total']
            ]);
        }

        if ( ! $main_journal ) {
            throw new Exception( __( 'Could not insert main journal item', 'erp' ) );
        }

        // enter the transaction items
        $order           = 1;
        $item_entry_type = ( $form_type['type'] == 'credit' ) ? 'debit' : 'credit';

        $jor_db_items = [];

        if ( $is_update ) {
            $get_journals_line_item = WeDevs\ERP\Accounting\Model\Journal::where( 'transaction_id', '=', $args['id'] )->where('type', '=', 'line_item' )->get()->toArray();
            $jor_prev_ids  = wp_list_pluck( $get_journals_line_item, 'id' );
        }

        foreach ($items as $key => $item) {

            $journal_id = erp_ac_journal_update( $item, $item_entry_type, $args, $trans_id );

            if ( ! $journal_id ) {
                throw new Exception( __( 'Could not insert journal item', 'erp' ) );
            }

            $tax_id  = erp_ac_tax_update( $item, $item_entry_type, $args, $trans_id );

            $item_id = erp_ac_item_update( $item, $args, $trans_id, $journal_id, $tax_id, $order );


            if ( ! $item_id ) {
                throw new Exception( __( 'Could not insert transaction item', 'erp' ) );
            }

            $order++;
        }

        if ( $is_update ) {

            $tax_jor_id = wp_list_pluck( $items, 'tax_journal' );

            foreach ( $jor_prev_ids as $key => $jor_prev_id ) {
                if ( in_array( $jor_prev_id, $tax_jor_id ) ) {
                    unset( $jor_prev_ids[$key] );
                }
            }

            $remove_jours = $remove_items = array_diff( $jor_prev_ids, $args['journals_id'] );

            $tax_journal_ids = WeDevs\ERP\Accounting\Model\Transaction_Items::select('tax_journal')->whereIn( 'journal_id', $remove_jours )->get()->toArray();
            $tax_journal_ids = wp_list_pluck( $tax_journal_ids, 'tax_journal' );
            $remove_jours    = array_merge( $remove_jours, $tax_journal_ids );

            WeDevs\ERP\Accounting\Model\Transaction_Items::whereIn( 'journal_id', $remove_items )->delete();
            WeDevs\ERP\Accounting\Model\Journal::whereIn( 'id', $remove_jours )->delete();
        }

        $wpdb->query( 'COMMIT' );

        //for partial payment
        if ( $args['form_type'] == 'payment' || $args['form_type'] == 'payment_voucher' ) {

            $transaction_ids = $args['partial_id'];

            foreach ( $transaction_ids as $key => $id ) {
                $line_total  = isset( $args['line_total'][$key] ) ? $args['line_total'][$key] : 0;
                $transaction = erp_ac_get_transaction( $id );
                $due         = $transaction['due'];

                if ( $line_total > $due ) {
                    continue;
                }

                $new_due = $due - $line_total;

                if ( $new_due <= 0  ) {
                    $update_field['status'] = 'paid';
                } else {
                    $update_field['status'] = 'partial';
                }

                $update_field['due'] = $new_due;

                \WeDevs\ERP\Accounting\Model\Transaction::find( $id )->update( $update_field );
                \WeDevs\ERP\Accounting\Model\Payment::create( array(
                    'transaction_id' => $trans_id,
                    'parent'         => 0,
                    'child'          => $id
                ) );
            }
        }

        do_action( 'erp_ac_new_transaction', $trans_id, $args, $items );

        // Transaction type hook eg: erp_ac_new_transaction_sales
        do_action( "erp_ac_new_transaction_{$args['type']}", $trans_id, $args, $items );

        return $trans_id;

    } catch (Exception $e) {
        $wpdb->query( 'ROLLBACK' );
        return new WP_error( 'final-exception', $e->getMessage() );
    }
    return false;
}

function erp_ac_item_update( $item, $args, $trans_id, $journal_id, $tax_journal, $order ) {

    if ( intval( $item['item_id'] ) ) {
        $trans_item = WeDevs\ERP\Accounting\Model\Transaction_Items::where( 'id', '=', $item['item_id'] )
            ->update([
                'product_id'  => isset( $item['product_id'] ) ? $item['product_id'] : '',
                'description' => $item['description'],
                'qty'         => $item['qty'],
                'unit_price'  => $item['unit_price'],
                'discount'    => $item['discount'],
                'tax'         => isset( $item['tax'] ) ? $item['tax'] : 0,
                'tax_rate'    => isset( $item['tax_rate'] ) ? $item['tax_rate'] : '0.00',
                'line_total'  => $item['line_total'],
                'order'       => $order,
                'tax_journal' => $tax_journal
            ]);

        $trans_item_id = intval( $item['item_id'] );

    } else {
        $trans_item = WeDevs\ERP\Accounting\Model\Transaction_Items::create([
            'journal_id'     => $journal_id,
            'product_id'     => isset( $item['product_id'] ) ? $item['product_id'] : '',
            'transaction_id' => $trans_id,
            'description'    => $item['description'],
            'qty'            => $item['qty'],
            'unit_price'     => $item['unit_price'],
            'discount'       => $item['discount'],
            'tax'            => isset( $item['tax'] ) ? $item['tax'] : 0,
            'tax_rate'       => isset( $item['tax_rate'] ) ? $item['tax_rate'] : '0.00',
            'line_total'     => $item['line_total'],
            'order'          => $order,
            'tax_journal'    => $tax_journal
        ]);

        $trans_item_id = $trans_item ? $trans_item->id : false;
    }

    return $trans_item_id;
}

function erp_ac_journal_update( $item, $item_entry_type, $args, $trans_id ) {

    if ( intval( $item['journal_id'] ) ) {

        $line_item_update = WeDevs\ERP\Accounting\Model\Journal::where( 'id', '=', $item['journal_id'] )
            ->update([
                'ledger_id'      => $item['account_id'],
                'type'           => 'line_item',
                $item_entry_type => $item['line_total']
            ]);

        $journal_id = intval( $item['journal_id'] );

    } else {
        $journal = WeDevs\ERP\Accounting\Model\Journal::create([
            'transaction_id' => $trans_id,
            'ledger_id'      => $item['account_id'],
            'type'           => 'line_item',
            $item_entry_type => $item['line_total']
        ]);

        $journal_id = $journal ? $journal->id : false;
    }

    return $journal_id;
}

function erp_ac_tax_update( $item, $item_entry_type, $args, $trans_id ) {

    $tax_account_id = erp_ac_get_tax_account_from_tax_id( $item['tax'], $args['type'] );

    if ( isset( $item['tax_journal'] ) && intval( $item['tax_journal'] ) ) {

        if ( intval( $tax_account_id ) ) {
            $tax_journal = WeDevs\ERP\Accounting\Model\Journal::where( 'id', '=', $item['tax_journal'] )->update([
                'ledger_id'      => $tax_account_id,
                $item_entry_type => ( $item['unit_price'] * $item['tax_rate'] ) / 100
            ]);

            $tax_journal_id =  intval( $item['tax_journal'] );
        } else {
            WeDevs\ERP\Accounting\Model\Journal::where( 'id', $item['tax_journal'] )->delete();
        }

    } else {
        if ( intval( $tax_account_id ) ) {
            $tax_journal = WeDevs\ERP\Accounting\Model\Journal::create([
                'transaction_id' => $trans_id,
                'ledger_id'      => $tax_account_id,
                'type'           => 'line_item',
                $item_entry_type => ( $item['unit_price'] * $item['tax_rate'] ) / 100
            ]);

            $tax_journal_id = $tax_journal ? $tax_journal->id : false;
        }
    }

    return isset( $tax_journal_id ) ? $tax_journal_id : false;
}

function erp_ac_create_items_after_transaction( $trans, $journal_id, $item, $order ) {
    //echo 'create';  die();
    $trans_item = $trans->items()->create([
        'journal_id'  => $journal_id,
        'product_id'  => '',
        'description' => $item['description'],
        'qty'         => $item['qty'],
        'unit_price'  => $item['unit_price'],
        'discount'    => $item['discount'],
        'line_total'  => $item['line_total'],
        'order'       => $order,
    ]);

    return $trans_item;
}


/**
 * Get transactions for a ledger
 *
 * @param  int  $ledger_id
 * @param  array   $args
 *
 * @return array
 */
function erp_ac_get_ledger_transactions( $ledger_id, $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'issue_date',
        'order'      => 'DESC',
    ];

    $args = wp_parse_args( $args, $defaults );
    $financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
    $financial_end   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

    $cache_key = 'erp-ac-ledger-transactions-' . md5( serialize( $args ) );
    $items     = wp_cache_get( $cache_key, 'erp' );

    if ( false === $items ) {
        $where = sprintf( 'WHERE jour.ledger_id = %d', absint( $ledger_id ) );
        $limit = ( $args['number'] == '-1' ) ? '' : sprintf( 'LIMIT %d, %d', $args['offset'], $args['number'] );

        if ( isset( $args['start_date'] ) && ! empty( $args['start_date'] ) ) {
            $where .= " AND trans.issue_date >= '{$args['start_date']}' ";
        } else {
            $where .= " AND trans.issue_date >= '{$financial_start}' ";
        }

        if ( isset( $args['end_date'] ) && ! empty( $args['end_date'] ) ) {
            $where .= " AND trans.issue_date <= '{$args['end_date']}' ";
        } else {
            $where .= " AND trans.issue_date <= '{$financial_end}' ";
        }

        if ( isset( $args['type'] ) && ! empty( $args['type'] ) ) {
            $where .= " AND trans.type = '{$args['type']}' ";
        }

        if ( isset( $args['form_type'] ) && ! empty( $args['form_type'] ) ) {
            $where .= " AND trans.form_type = '{$args['form_type']}' ";
        }

        $sql = "SELECT * FROM {$wpdb->prefix}erp_ac_journals as jour
            LEFT JOIN {$wpdb->prefix}erp_ac_transactions as trans ON trans.id = jour.transaction_id
            $where
            ORDER BY {$args['orderby']} {$args['order']}
            $limit";

        $items = $wpdb->get_results( $sql );
        wp_cache_set( $cache_key, $items, 'erp' );
    }

    return $items;
}

function erp_ac_toltip_per_transaction_ledgers( $transaction ) {
    $journals = isset( $transaction['journals'] ) ? $transaction['journals'] : [];
    ob_start();
    ?>
    <table class='erp-ac-toltip-table wp-list-table widefat fixed striped' cellspacing='0'>
        <thead>
            <tr>
                <th><?php _e( 'Ledger', 'erp' ); ?></th>
                <th><?php _e( 'Debit', 'erp' ); ?></th>
                <th><?php _e( 'Credit', 'erp' ); ?></th>
            </tr>
        </thead>
        <tbody>
    <?php
    foreach ( $journals as $key => $journal ) {
        ?>
        <tr>
            <td><?php echo $journal['ledger']['name']; ?></td>
            <td><?php echo erp_ac_get_price( $journal['debit'] ); ?></td>
            <td><?php echo erp_ac_get_price( $journal['credit'] ); ?></td>
        </tr>
        <?php
    }
    ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
}

function erp_ac_tran_from_header() {
    $header = [
        'account'     => __( 'Account', 'erp' ),
        'description' => __( 'Description', 'erp' ),
        'qty'         => __( 'Qty', 'erp' ),
        'unit_price'  => __( 'Unit Price', 'erp' ),
        'discount'    => __( 'Discount', 'erp' ),
        'tax'         => __( 'Tax(%)', 'erp' ),
        'tax_amount'  => __( 'Tax Amount', 'erp' ),
        'amount'      => __( 'Amount', 'erp' ),
        'action'      => '&nbsp;'
    ];

    return apply_filters( 'erp_ac_trans_form_header', $header );
}

function erp_ac_get_btn_status( $postdata ) {

    if ( $postdata['form_type'] == 'payment' ) {
        return erp_ac_get_status_according_with_btn( $postdata['btn_status'] );
    } else if ( $postdata['form_type'] == 'invoice' || $postdata['form_type'] == 'vendor_credit' ) {
        return erp_ac_get_status_invoice_according_with_btn( $postdata['btn_status'] );
    } else if ( $postdata['form_type'] == 'payment_voucher' ) {
        return erp_ac_get_voucher_status_according_with_btn( $postdata['btn_status'] );
    }
}

/**
 * Get transaction submit data status for payment voucher
 * @param  string $btn
 * @return string
 */
function erp_ac_get_voucher_status_according_with_btn( $btn ) {
    $button = [
        'payment'                 => 'paid',
        'payment_and_add_another' => 'paid'
    ];

    return $button[$btn];
}

/**
 * Get transaction submit data status for payment
 * @param  string $btn
 * @return string
 */
function erp_ac_get_status_according_with_btn( $btn ) {
    $button = [
        'payment'                 => 'closed',
        'payment_and_add_another' => 'closed'
    ];

    return $button[$btn];
}

/**
 * Get transaction submit data status for payment invoice and vendor credit
 * @param  string $btn
 * @return string
 */
function erp_ac_get_status_invoice_according_with_btn( $btn ) {
    $button = [
        'save_and_draft'               => 'draft',
        'save_and_submit_for_approval' => 'pending',
        'save_and_add_another'         => 'draft',
        'approve'                      => 'awaiting_payment',
        'approve_and_add_another'      => 'awaiting_payment'
    ];

    return $button[$btn];
}

/**
 * Update transaction
 *
 * @param  int $id
 * @param  array $args
 *
 * @since  1.1.1
 *
 * @return  boolen
 */
function erp_ac_update_transaction( $id, $args ) {
    return \WeDevs\ERP\Accounting\Model\Transaction::find( $id )->update( $args );
}

/**
 * Remove transaction. Only for draft and pending
 *
 * @param  int $id
 *
 * @since  1.1.1
 *
 * @return  boolen
 */
function erp_ac_remove_transaction( $id ) {
    $delete = \WeDevs\ERP\Accounting\Model\Transaction::where( 'id', '=', $id )->delete();
    \WeDevs\ERP\Accounting\Model\Transaction_Items::where( 'transaction_id', '=', $id )->delete();
    \WeDevs\ERP\Accounting\Model\Journal::where( 'transaction_id', '=', $id )->delete();
    return $delete;
}

/**
 * Vendor lists
 *
 * @since  1.1.1
 *
 * @return  array
 */
function erp_ac_get_vendors() {
    $users = [];
    $vendors = erp_get_peoples( ['type' => 'vendor', 'number' => 100 ] );
    foreach ( $vendors as $user ) {
        if ( in_array( 'vendor', $user->types ) ) {
            $users[$user->id] = empty( $user->company ) ? __( 'No Title', 'erp' ) : $user->company;
        }
    }

    return $users;
}


/**
 * Send pdf after new invoice
 *
 * @param $transaction_id
 * @param string $output_method
 *
 * @since 1.1.4
 *
 * @return void
 */
function erp_ac_send_invoice_pdf( $transaction_id, $output_method = 'D' ) {
    $transaction    = \WeDevs\ERP\Accounting\Model\Transaction::find( $transaction_id );
    $file_name      = sprintf( '%s_%s.pdf', $transaction->invoice_number, $transaction->issue_date );

    if ( $transaction ) {
        include WPERP_ACCOUNTING_VIEWS . '/pdf/invoice.php';
    }
}

/**
 * Send pdf invoice with email
 *
 * @param $transaction_id
 * @param array $args
 *
 * @since 1.1.4
 * @return void
 */
function erp_ac_send_email_with_pdf_attached( $transaction_id, $args = [] ) {

    $type       = isset( $args['type'] ) ? sanitize_text_field( $args['type'] ) : '';
    $sender     = isset( $args['email-from'] ) ? sanitize_text_field( $args['email-from'] ) : '';
    $receiver   = isset( $args['email-to'] ) ? $args['email-to'] : '';
    $subject    = isset( $args['email-subject'] ) ? sanitize_text_field( $args['email-subject'] ) : '';
    $body       = isset( $args['email-body'] ) ? sanitize_text_field( $args['email-body'] ) : '';
    $attach_pdf = isset( $args['attachment'] ) && 'on' == $args['attachment'] ? true : false;

    $transaction = \WeDevs\ERP\Accounting\Model\Transaction::find( $transaction_id );

    $file_name     = erp_ac_pdf_link_generator( $transaction );
    $invoice_email = new \WeDevs\ERP\Accounting\Emails\Accounting_Invoice_Email();
    $file_name     = $attach_pdf ? $file_name : '';

    $invoice_email->trigger( $receiver, $subject, $body, $file_name );

    unlink( $file_name );
}

/**
 * Get invoice pdf link
 *
 * @param $transaction
 * @param string $type
 * @return string
 *
 * @since 1.1.4
 * @return string
 */
function erp_ac_pdf_link_generator( $transaction, $type = 'invoice' ) {
    $upload_path    = wp_upload_dir();
    $include_file   = 'invoice' == $type ? 'invoice' : 'payment';
    $file_name      = sprintf( '%s/%s_%s.pdf', $upload_path['basedir'], $transaction->invoice_number, $transaction->issue_date );
    $output_method  = 'F';
    include WPERP_ACCOUNTING_VIEWS . '/pdf/' . $include_file . '.php';
    return $file_name;
}




