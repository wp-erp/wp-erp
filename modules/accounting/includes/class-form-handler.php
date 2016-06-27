<?php
namespace WeDevs\ERP\Accounting;

/**
 * Handle the form submissions
 *
 * @package Package
 * @subpackage Sub Package
 */
class Form_Handler {

    /**
     * Hook 'em all
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_customer_form' ) );
        add_action( 'admin_init', array( $this, 'chart_form' ) );
        add_action( 'erp_action_ac-new-payment-voucher', array( $this, 'transaction_form' ) );
        add_action( 'erp_action_ac-new-invoice', array( $this, 'transaction_form' ) );
        add_action( 'erp_action_ac-new-sales-payment', array( $this, 'transaction_form' ) );
        add_action( 'erp_action_ac-new-journal-entry', array( $this, 'journal_entry' ) );

        add_action( 'load-accounting_page_erp-accounting-customers', array( $this, 'customer_bulk_action') );
        add_action( 'load-accounting_page_erp-accounting-vendors', array( $this, 'vendor_bulk_action') );

        add_action( 'erp_hr_after_employee_permission_set', array( $this, 'employee_permission_set'), 10, 2 );
    }

    function employee_permission_set( $post, $user ) {
        $user_profile = new \WeDevs\ERP\Accounting\User_Profile();
        $post['ac_manager'] = isset( $post['ac_manager'] ) && $post['ac_manager'] == 'on' ? erp_ac_get_manager_role() : false;
        $user_profile->update_user( $user->ID, $post );
    }

    function customer_bulk_action() {

        if ( ! $this->verify_current_page_screen( 'erp-accounting-customers', 'bulk-customers' ) ) {
            return;
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == '-1' ) {
            $this->bulk_search();
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'trash' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data = [
                'id'   => $customer_id,
                'hard' => 0,
                'type' => 'customer'
            ];
            erp_ac_customer_delete( $data );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'restore' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data = [
                'id'   => $customer_id,
                'type' => 'customer'
            ];
            erp_restore_people( $data );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'delete' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data = [
                'id'   => $customer_id,
                'hard' => 1,
                'type' => 'customer'
            ];
            erp_ac_customer_delete( $data );
            //erp_delete_people( $data );
        }
    }


    function vendor_bulk_action() {

        if ( ! $this->verify_current_page_screen( 'erp-accounting-vendors', 'bulk-customers' ) ) {
            return;
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == '-1' ) {
            $this->bulk_search();
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'trash' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data = [
                'id'   => $customer_id,
                'hard' => 0,
                'type' => 'vendor'
            ];

            erp_ac_vendor_delete( $data );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'restore' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data = [
                'id'   => $customer_id,
                'type' => 'vendor'
            ];
            erp_restore_people( $data );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'delete' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data = [
                'id'   => $customer_id,
                'hard' => 1,
                'type' => 'vendor'
            ];
            erp_ac_vendor_delete( $data );
        }
    }

    function bulk_search() {
        $redirect_to = add_query_arg( array( 's' => $_POST['s'] ), $_POST['_wp_http_referer'] );
        wp_redirect( $redirect_to );
        exit();
    }

    /**
     * Check is current page actions
     *
     * @since 0.1
     *
     * @param  integer $page_id
     * @param  integer $bulk_action
     *
     * @return boolean
     */
    public function verify_current_page_screen( $page_id, $bulk_action ) {

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return false;
        }

        if ( $_GET['page'] != $page_id ) {
            return false;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $bulk_action ) ) {
            return false;
        }

        return true;
    }


    /**
     * Handle the customer new and edit form
     *
     * @return void
     */
    public function handle_customer_form() {
        if ( ! isset( $_POST['submit_erp_ac_customer'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-ac-customer' ) ) {
            die( __( 'Are you cheating?', 'erp' ) );
        }

        if ( ! current_user_can( 'read' ) ) {
            wp_die( __( 'Permission Denied!', 'erp' ) );
        }

        $insert_id = erp_ac_new_customer( $_POST );
        $message   = __( 'new', 'erp' );

        if ( $_POST['type'] == 'customer' ) {
            $page_url    = admin_url( 'admin.php?page=erp-accounting-customers' );
        } else {
            $page_url    = admin_url( 'admin.php?page=erp-accounting-vendors' );
        }

        if ( is_wp_error( $insert_id ) ) {
            $redirect_to = add_query_arg( array( 'msg' => 'error' ), $page_url );
        } else {
            $redirect_to = add_query_arg( array( 'msg' => $message ), $page_url );
        }

        wp_safe_redirect( $redirect_to );
        exit;
    }

    /**
     * Handle the chart new and edit form
     *
     * @return void
     */
    public function chart_form() {
        if ( ! isset( $_POST['submit_erp_ac_chart'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-ac-chart' ) ) {
            die( __( 'Are you cheating?', 'erp' ) );
        }

        if ( ! current_user_can( 'read' ) ) {
            wp_die( __( 'Permission Denied!', 'erp' ) );
        }

        $message  = 'new';
        $errors   = array();
        $page_url = admin_url( 'admin.php?page=erp-accounting-charts' );
        $field_id = isset( $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0;

        $name            = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $account_type_id = isset( $_POST['account_type_id'] ) ? sanitize_text_field( $_POST['account_type_id'] ) : '';
        $code            = isset( $_POST['code'] ) ? intval( $_POST['code'] ) : '';
        $description     = isset( $_POST['description'] ) ? sanitize_text_field( $_POST['description'] ) : 1;
        $active          = isset( $_POST['active'] ) ? intval( $_POST['active'] ) : 1;

        // some basic validation
        if ( ! $field_id && Model\Ledger::code( $code )->get()->first() !== null ) {
            $errors[] = __( 'Error: The account code is already exists.', 'erp' );
        }

        // some basic validation
        if ( $field_id && Model\Ledger::code( $code )->where( 'id', '!=', $field_id )->get()->first() !== null ) {
            $errors[] = __( 'Error: The account code is already exists.', 'erp' );
        }

        if ( ! $name ) {
            $errors[] = __( 'Error: Name is required.', 'erp' );
        }

        // bail out if error found
        if ( $errors ) {
            $first_error = reset( $errors );
            $redirect_to = add_query_arg( array( 'error' => $first_error ), $page_url );
            wp_safe_redirect( $redirect_to );
            exit;
        }

        $fields = array(
            'code'        => $code,
            'name'        => $name,
            'description' => $description,
            'type_id'     => $account_type_id,
            'active'      => $active,
        );

        // bank account
        if ( $account_type_id == 6 ) {
            $fields['cash_account'] = 1;
            $fields['reconcile']    = 1;
        }

        // New or edit?
        if ( ! $field_id ) {

            $insert_id = erp_ac_insert_chart( $fields );

            if ( $insert_id && $account_type_id == 6 ) {

                $ledger = Model\Ledger::find( $insert_id );
                $ledger->bank_details()->create([
                    'account_number' => sanitize_text_field( $_POST['bank']['account_number'] ),
                    'bank_name'      => sanitize_text_field( $_POST['bank']['bank_name'] )
                ]);
            }

        } else {

            $fields['id'] = $field_id;
            $message      = 'update';
            $insert_id    = erp_ac_insert_chart( $fields );

            $ledger = Model\Ledger::find( $field_id );
            $ledger->bank_details()->update([
                'account_number' => sanitize_text_field( $_POST['bank']['account_number'] ),
                'bank_name'      => sanitize_text_field( $_POST['bank']['bank_name'] )
            ]);
        }

        if ( is_wp_error( $insert_id ) ) {
            $redirect_to = add_query_arg( array( 'msg' => 'error' ), $page_url );
        } else {
            $redirect_to = add_query_arg( array( 'msg' => $message ), $page_url );
        }

        wp_safe_redirect( $redirect_to );
        exit;
    }

    /**
     * Handle the transaction new and edit form
     *
     * @return void
     */
    public function transaction_form() {
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-ac-trans-new' ) ) {
            die( __( 'Are you cheating?', 'erp' ) );
        }

        if ( ! current_user_can( 'read' ) ) {
            wp_die( __( 'Permission Denied!', 'erp' ) );
        }

        $errors          = array();
        $field_id        = isset( $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0;
        $page            = isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : '';
        $type            = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
        $form_type       = isset( $_POST['form_type'] ) ? sanitize_text_field( $_POST['form_type'] ) : '';
        $account_id      = isset( $_POST['account_id'] ) ? intval( $_POST['account_id'] ) : 0;
        $status          = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'closed';
        $user_id         = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $billing_address = isset( $_POST['billing_address'] ) ? wp_kses_post( $_POST['billing_address'] ) : '';
        $ref             = isset( $_POST['ref'] ) ? sanitize_text_field( $_POST['ref'] ) : '';
        $issue_date      = isset( $_POST['issue_date'] ) ? sanitize_text_field( $_POST['issue_date'] ) : '';
        $due_date        = isset( $_POST['due_date'] ) ? sanitize_text_field( $_POST['due_date'] ) : '';
        $summary         = isset( $_POST['summary'] ) ? wp_kses_post( $_POST['summary'] ) : '';
        $total           = isset( $_POST['price_total'] ) ? sanitize_text_field( erp_ac_format_decimal( $_POST['price_total'] ) ) : '';
        $files           = isset( $_POST['files'] ) ? maybe_serialize( $_POST['files'] ) : '';
        $currency        = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : 'USD';
        $transaction_id  = isset( $_POST['id'] ) ? $_POST['id'] : false;
        $line_account    = isset( $_POST['line_account'] ) ? $_POST['line_account'] : array();
        $page_url        = admin_url( 'admin.php?page=' . $page );
        $items_id        = isset( $_POST['items_id'] ) ? $_POST['items_id'] : [];
        $journals_id     = isset( $_POST['journals_id'] ) ? $_POST['journals_id'] : [];
        $partial_id      = isset( $_POST['partial_id'] ) ? $_POST['partial_id'] : [];
        $sub_total       = isset( $_POST['sub_total'] ) ? $_POST['sub_total'] : '0.00';
        $invoice         = isset( $_POST['invoice'] ) ? $_POST['invoice'] : erp_ac_generate_invoice_id( $form_type );

        //for draft
        $status = isset( $_POST['submit_erp_ac_trans_draft'] ) ? 'draft' : $status;

        // some basic validation
        if ( ! $issue_date ) {
            $errors[] = __( 'Error: Issue Date is required', 'erp' );
        }

        if ( ! $account_id ) {
            $errors[] = __( 'Error: Account ID is required', 'erp' );
        }

        if ( ! $total ) {
            $errors[] = __( 'Error: Total is required', 'erp' );
        }

        // bail out if error found
        if ( $errors ) {
            $first_error = reset( $errors );
            $redirect_to = add_query_arg( array( 'error' => $first_error ), $page_url );
            wp_safe_redirect( $redirect_to );
            exit;
        }

        $fields = [
            'id'              => $transaction_id,
            'partial_id'      => $partial_id,
            'items_id'        => $items_id,
            'journals_id'     => $journals_id,
            'type'            => $type,
            'form_type'       => $form_type,
            'account_id'      => $account_id,
            'status'          => $status,
            'user_id'         => $user_id,
            'billing_address' => $billing_address,
            'ref'             => $ref,
            'invoice_number'  => $invoice,
            'issue_date'      => $issue_date,
            'due_date'        => $due_date,
            'summary'         => $summary,
            'total'           => $total,
            'sub_total'       => $sub_total,
            'trans_total'     => $total,
            'files'           => $files,
            'currency'        => $currency,
            'line_total'      => isset( $_POST['line_total'] ) ? $_POST['line_total'] : array()
        ];

        // set invoice and vendor credit due to full amount
        if ( in_array( $form_type, [ 'invoice', 'vendor_credit' ] ) ) {
            $fields['due'] = $total;
        }

        $items = [];
        foreach ( $line_account as $key => $acc_id) {
            $line_total = erp_ac_format_decimal( $_POST['line_total'][ $key ] );

            if ( ! $acc_id || ! $line_total ) {
                continue;
            }

            $items[] = apply_filters( 'erp_ac_transaction_lines', [
                'item_id'     => isset( $_POST['items_id'][$key] ) ? $_POST['items_id'][$key] : [],
                'journal_id'  => isset( $_POST['journals_id'][$key] ) ? $_POST['journals_id'][$key] : [],
                'account_id'  => (int) $acc_id,
                'description' => sanitize_text_field( $_POST['line_desc'][ $key ] ),
                'qty'         => intval( $_POST['line_qty'][ $key ] ),
                'unit_price'  => erp_ac_format_decimal( $_POST['line_unit_price'][ $key ] ),
                'discount'    => erp_ac_format_decimal( $_POST['line_discount'][ $key ] ),
                'tax'         => isset( $_POST['line_tax'][$key] ) ? $_POST['line_tax'][$key] : 0,
                'tax_rate'    => isset( $_POST['tax_rate'][$key] ) ? $_POST['tax_rate'][$key] : 0,
                'line_total'  => erp_ac_format_decimal( $line_total ),
                'tax_journal' => isset( $_POST['tax_journal'][$key] ) ? $_POST['tax_journal'][$key] : 0
            ], $key, $_POST );
        }

        // New or edit?
        if ( ! $field_id ) {
            $insert_id = erp_ac_insert_transaction( $fields, $items );
        }

        if ( is_wp_error( $insert_id ) ) {
            $redirect_to = add_query_arg( array( 'msg' => $insert_id->get_error_message() ), $page_url );
        } else {
            $redirect_to = add_query_arg( array( 'msg' => 'success' ), $page_url );
        }

        wp_safe_redirect( $redirect_to );
        exit;
    }

    public function journal_entry() {

        if ( ! erp_ac_create_journal() ) {
            return new \WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }

        if ( empty( $_POST['invoice'] ) ) {
            return new \WP_Error( 'error', __( 'Invoice number required', 'erp' ) );
        }

        $invoice = isset( $_POST['invoice'] ) ? $_POST['invoice'] : '';
        $trans   = new \WeDevs\ERP\Accounting\Model\Transaction();
        $trans   = $trans->where( 'invoice_number', '=', $invoice )->get()->toArray();

        if ( $trans ) {
            return new \WP_Error( 'error', __( 'Please insert unique invoice number', 'erp' ) );
        }

        global $wpdb;

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-ac-journal-entry' ) ) {
            die( __( 'Are you cheating?', 'erp' ) );
        }

        $ref          = isset( $_POST['ref'] ) ? sanitize_text_field( $_POST['ref'] ) : '';
        $issue_date   = isset( $_POST['issue_date'] ) ? sanitize_text_field( $_POST['issue_date'] ) : '';
        $summary      = isset( $_POST['summary'] ) ? sanitize_text_field( $_POST['summary'] ) : '';
        $debit_total  = isset( $_POST['debit_total'] ) ? floatval( $_POST['debit_total'] ) : 0.00;
        $credit_total = isset( $_POST['credit_total'] ) ? floatval( $_POST['credit_total'] ) : 0.00;
        $invoice      = isset( $_POST['invoice'] ) ? $_POST['invoice'] : erp_ac_generate_invoice_id( $form_type );

        if ( $debit_total < 0 || $credit_total < 0 ) {
            wp_die( __( 'Value can not be negative', 'erp' ) );
        }

        if ( $debit_total != $credit_total ) {
            wp_die( __( 'Debit and credit total did not match.', 'erp' ) );
        }

        $args = [
            'type'            => 'journal',
            'ref'             => $ref,
            'summary'         => $summary,
            'issue_date'      => $issue_date,
            'total'           => $debit_total,
            'conversion_rate' => 1,
            'trans_total'     => $debit_total,
            'invoice_number'  => $invoice,
            'created_by'      => get_current_user_id(),
            'created_at'      => current_time( 'mysql' )
        ];

        try {
            $wpdb->query( 'START TRANSACTION' );

            $transaction = new \WeDevs\ERP\Accounting\Model\Transaction();
            $trans = $transaction->create( $args );

            if ( $trans->id ) {
                erp_ac_update_invoice_number( 'journal' );
            }

            if ( ! $trans->id ) {
                throw new \Exception( __( 'Could not create transaction', 'erp' ) );
            }

            // insert items
            $order = 1;
            foreach ( $_POST['journal_account'] as $key => $account_id) {
                $debit  = floatval( $_POST['line_debit'][ $key ] );
                $credit = floatval( $_POST['line_credit'][ $key ] );

                if ( $debit ) {
                    $type   = 'debit';
                    $amount = $debit;
                } else {
                    $type   = 'credit';
                    $amount = $credit;
                }

                $journal = $trans->journals()->create([
                    'ledger_id' => $account_id,
                    'type'      => 'line_item',
                    $type       => $amount
                ]);

                if ( ! $journal->id ) {
                    throw new \Exception( __( 'Could not insert journal item', 'erp' ) );
                }

                $item = [
                    'journal_id'  => $journal->id,
                    'product_id'  => '',
                    'description' => sanitize_text_field( $_POST['line_desc'][ $key ] ),
                    'qty'         => 1,
                    'unit_price'  => $amount,
                    'discount'    => 0,
                    'line_total'  => $amount,
                    'order'       => $order,
                ];

                $trans_item = $trans->items()->create( $item );

                if ( ! $trans_item->id ) {
                    throw new \Exception( __( 'Could not insert transaction item', 'erp' ) );
                }

                $order++;
            }

            $wpdb->query( 'COMMIT' );

        } catch (Exception $e) {
            $wpdb->query( 'ROLLBACK' );

            wp_die( $e->getMessage() );
        }

        do_action( 'erp_ac_new_journal', $trans->id, $args, $_POST );

        $location = admin_url( 'admin.php?page=erp-accounting-journal&msg=success' );
        wp_redirect( $location );
    }
}
