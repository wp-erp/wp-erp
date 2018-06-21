<?php

namespace WeDevs\ERP\Accounting;

/**
 * Handle the form submissions
 *
 * @package Package
 * @subpackage Sub Package
 */
class Form_Handler {
    public static $errors;

    /**
     * Hook 'em all
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_customer_form' ) );
        add_action( 'admin_init', array( $this, 'chart_form' ) );
        add_action( 'admin_init', array( $this, 'report_filter' ) );
        add_action( 'erp_action_ac-new-payment-voucher', array( $this, 'transaction_form' ) );
        add_action( 'erp_action_ac-new-invoice', array( $this, 'transaction_form' ) );
        add_action( 'erp_action_ac-new-sales-payment', array( $this, 'transaction_form' ) );
        add_action( 'erp_action_ac-new-journal-entry', array( $this, 'journal_entry' ) );

        $accounting = sanitize_title( __( 'Accounting', 'erp' ) );
        add_action( "load-{$accounting}_page_erp-accounting-customers", array( $this, 'customer_bulk_action' ) );
        add_action( "load-{$accounting}_page_erp-accounting-vendors", array( $this, 'vendor_bulk_action' ) );
        add_action( "load-{$accounting}_page_erp-accounting-sales", array( $this, 'sales_bulk_action' ) );
        add_action( "load-{$accounting}_page_erp-accounting-expense", array( $this, 'expense_bulk_action' ) );
        add_action( 'load-{$accounting}_page_erp-accounting-journal', array( $this, 'journal_bulk_action' ) );

        add_action( 'erp_hr_after_employee_permission_set', array( $this, 'employee_permission_set' ), 10, 2 );
    }

    /**
     * Journal bulk action
     *
     * @since  1.1.6
     *
     * @return  void
     */
    function journal_bulk_action() {
        if ( ! $this->verify_current_page_screen( 'erp-accounting-journal', 'bulk-journals' ) ) {
            return;
        }

        $url = add_query_arg( array(
            'start_date' => isset( $_REQUEST['start_date'] ) ? $_REQUEST['start_date'] : '',
            'end_date'   => isset( $_REQUEST['end_date'] ) ? $_REQUEST['end_date'] : '',
            'ref'        => isset( $_REQUEST['ref'] ) ? $_REQUEST['ref'] : '',
        ), erp_ac_get_journal_url() );

        wp_safe_redirect( $url );
        exit();
    }

    /**
     * Filter report by date range
     *
     * @since  1.1.6
     *
     * @return  void
     */
    function report_filter() {
        if ( ! isset( $_POST['erp_ac_report_filter'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp_ac_nonce_report' ) ) {
            return new \WP_Error( 'nonce', __( 'Error: Are you cheating!', 'erp' ) );
        }

        $date = [];

        if ( isset( $_POST['start'] ) ) {
            $date['start'] = $_POST['start'];
        }

        if ( isset( $_POST['end'] ) ) {
            $date['end'] = $_POST['end'];
        }

        $url = add_query_arg( $date, $_POST['_wp_http_referer'] );
        wp_safe_redirect( $url );
        exit();
    }

    function expense_bulk_action() {
        if ( ! $this->verify_current_page_screen( 'erp-accounting-expense', 'bulk-expenses' ) ) {
            return;
        }

        if ( ! isset( $_REQUEST['transaction_id'] ) || ! isset( $_REQUEST['submit_sales_bulk_action'] ) ) {
            return;
        }

        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

        foreach ( $_REQUEST['transaction_id'] as $key => $trans_id ) {
            switch ( $action ) {
                case 'delete':
                    erp_ac_remove_transaction( $trans_id );
                    break;
                case 'void':
                    erp_ac_update_transaction_to_void( $trans_id );
                    break;

                default:
                    erp_ac_update_transaction( $trans_id, [ 'status' => $action ] );
                    break;
            }
        }

        wp_safe_redirect( $_REQUEST['_wp_http_referer'] );
    }

    /**
     * Bulk action for sales list table
     *
     * @since  1.1.0
     *
     * @return void
     */
    function sales_bulk_action() {

        if ( ! $this->verify_current_page_screen( 'erp-accounting-sales', 'bulk-sales' ) ) {
            return;
        }

        if ( ! isset( $_REQUEST['transaction_id'] ) || ! isset( $_REQUEST['submit_sales_bulk_action'] ) ) {
            return;
        }

        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

        foreach ( $_REQUEST['transaction_id'] as $key => $trans_id ) {
            switch ( $action ) {
                case 'delete':
                    erp_ac_remove_transaction( $trans_id );
                    break;

                case 'void':
                    erp_ac_update_transaction_to_void( $trans_id );
                    break;

                default:
                    erp_ac_update_transaction( $trans_id, [ 'status' => $action ] );
                    break;
            }
        }

        wp_safe_redirect( $_REQUEST['_wp_http_referer'] );
    }

    /**
     * Update employee role as accounting manager
     *
     * @param  array $post
     * @param  object $user
     *
     * @since  1.1.0
     *
     * @return void
     */
    public static function employee_permission_set( $post, $user ) {
        $enable_ac_manager = isset( $post['ac_manager'] ) ? filter_var( $post['ac_manager'], FILTER_VALIDATE_BOOLEAN ) : false;
        $ac_manager_role   = erp_ac_get_manager_role();

        // TODO::We are duplicating \WeDevs\ERP\Accounting\User_Profile->update_user() process here,
        // which we shouldn't. We should update above method and use that.
        if ( current_user_can( $ac_manager_role ) ) {
            if ( $enable_ac_manager ) {
                $user->add_role( $ac_manager_role );
            } else {
                $user->remove_role( $ac_manager_role );
            }
        }
    }

    /**
     * Bulk action for customer list table
     *
     * @since  1.1.0
     *
     * @return void
     */
    function customer_bulk_action() {

        if ( ! $this->verify_current_page_screen( 'erp-accounting-customers', 'bulk-customers' ) ) {
            return;
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == '-1' ) {
            $this->bulk_search();
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'trash' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data        = [
                'id'   => $customer_id,
                'hard' => 0,
                'type' => 'customer'
            ];
            erp_ac_customer_delete( $data );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'restore' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data        = [
                'id'   => $customer_id,
                'type' => 'customer'
            ];
            erp_restore_people( $data );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'delete' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data        = [
                'id'   => $customer_id,
                'hard' => 1,
                'type' => 'customer'
            ];
            erp_ac_customer_delete( $data );
            //erp_delete_people( $data );
        }
    }

    /**
     * Bulk action for vendor list table
     *
     * @since  1.1.0
     *
     * @return void
     */
    function vendor_bulk_action() {

        if ( ! $this->verify_current_page_screen( 'erp-accounting-vendors', 'bulk-customers' ) ) {
            return;
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == '-1' ) {
            $this->bulk_search();
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'trash' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data        = [
                'id'   => $customer_id,
                'hard' => 0,
                'type' => 'vendor'
            ];

            erp_ac_vendor_delete( $data );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'restore' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data        = [
                'id'   => $customer_id,
                'type' => 'vendor'
            ];
            erp_restore_people( $data );
        }

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'delete' ) {
            $customer_id = isset( $_POST['customer_id'] ) && is_array( $_POST['customer_id'] ) ? $_POST['customer_id'] : false;
            $data        = [
                'id'   => $customer_id,
                'hard' => 1,
                'type' => 'vendor'
            ];
            erp_ac_vendor_delete( $data );
        }
    }

    /**
     * Redirect after transaction list table submit for search
     *
     * @since  1.1.0
     *
     * @return void
     */
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

        if ( $_POST['field_id'] ) {
            $message = __( 'update', 'erp' );
        }

        if ( $_POST['type'] == 'customer' ) {
            $page_url = admin_url( 'admin.php?page=erp-accounting-customers' );
        } else {
            $page_url = admin_url( 'admin.php?page=erp-accounting-vendors' );
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
                $ledger->bank_details()->create( [
                    'account_number' => sanitize_text_field( $_POST['bank']['account_number'] ),
                    'bank_name'      => sanitize_text_field( $_POST['bank']['bank_name'] )
                ] );
            }

        } else {

            $fields['id'] = $field_id;
            $message      = 'update';
            $insert_id    = erp_ac_insert_chart( $fields );

            $ledger = Model\Ledger::find( $field_id );
            $ledger->bank_details()->update( [
                'account_number' => sanitize_text_field( $_POST['bank']['account_number'] ),
                'bank_name'      => sanitize_text_field( $_POST['bank']['bank_name'] )
            ] );
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
     * Transaction form data
     *
     * @since  1.1.0
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

        $insert_id = $this->transaction_data_process( $_POST );
        $page_url  = isset( $_POST['_wp_http_referer'] ) ? $_POST['_wp_http_referer'] : '';

        if ( is_wp_error( $insert_id ) ) {
            self::$errors = $insert_id->get_error_message();
            $redirect_to  = add_query_arg( array( 'message' => $insert_id ), $page_url );
            wp_safe_redirect( $redirect_to );
            exit;

        } else {
            $redirect_to = add_query_arg( array( 'msg' => 'success' ), $page_url );
        }

        if ( $_POST['redirect'] == 'same_page' ) {
            $redirect_to = remove_query_arg( [ 'transaction_id' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );

        } else if ( $_POST['redirect'] == 'single_page' ) {

            if ( $_POST['type'] == 'sales' ) {
                $redirect_to = erp_ac_get_slaes_payment_invoice_url( $insert_id );

            } else if ( $_POST['type'] == 'expense' ) {
                $redirect_to = erp_ac_get_expense_voucher_url( $insert_id );
            }
        }

        $redirect_to = apply_filters( 'erp_ac_redirect_after_transaction', $redirect_to, $insert_id, $_POST );
        wp_safe_redirect( $redirect_to );
        exit;
    }

    /**
     * Handle the transaction new and edit form
     *
     * @return void
     */
    public function transaction_data_process( $postdata ) {
        $status          = erp_ac_get_btn_status( $postdata );
        $errors          = array();
        $insert_id       = 0;
        $field_id        = isset( $postdata['field_id'] ) ? intval( $postdata['field_id'] ) : 0;
        $page            = isset( $postdata['page'] ) ? sanitize_text_field( $postdata['page'] ) : '';
        $type            = isset( $postdata['type'] ) ? sanitize_text_field( $postdata['type'] ) : '';
        $form_type       = isset( $postdata['form_type'] ) ? sanitize_text_field( $postdata['form_type'] ) : '';
        $account_id      = isset( $postdata['account_id'] ) ? intval( $postdata['account_id'] ) : 0;
        $status          = isset( $postdata['status'] ) ? sanitize_text_field( $postdata['status'] ) : 'closed';
        $user_id         = isset( $postdata['user_id'] ) ? intval( $postdata['user_id'] ) : 0;
        $billing_address = isset( $postdata['billing_address'] ) ? wp_kses_post( $postdata['billing_address'] ) : '';
        $ref             = isset( $postdata['ref'] ) ? sanitize_text_field( $postdata['ref'] ) : '';
        $issue_date      = isset( $postdata['issue_date'] ) ? sanitize_text_field( $postdata['issue_date'] ) : '';
        $due_date        = isset( $postdata['due_date'] ) ? sanitize_text_field( $postdata['due_date'] ) : '';
        $summary         = isset( $postdata['summary'] ) ? wp_kses_post( $postdata['summary'] ) : '';
        $total           = isset( $postdata['price_total'] ) ? sanitize_text_field( $postdata['price_total'] ) : '';
        $files           = isset( $postdata['files'] ) ? maybe_serialize( $postdata['files'] ) : '';
        $currency        = isset( $postdata['currency'] ) ? sanitize_text_field( $postdata['currency'] ) : 'USD';
        $transaction_id  = isset( $postdata['id'] ) ? $postdata['id'] : false;
        $line_account    = isset( $postdata['line_account'] ) ? $postdata['line_account'] : array();
        $page_url        = admin_url( 'admin.php?page=' . $page );
        $items_id        = isset( $postdata['items_id'] ) ? $postdata['items_id'] : [];
        $journals_id     = isset( $postdata['journals_id'] ) ? $postdata['journals_id'] : [];
        $partial_id      = isset( $postdata['partial_id'] ) ? $postdata['partial_id'] : [];
        $sub_total       = isset( $postdata['sub_total'] ) ? $postdata['sub_total'] : '0.00';
        $invoice         = isset( $postdata['invoice'] ) ? $postdata['invoice'] : 0;

        //clean price data and format as default setup
        $total     = erp_ac_format_decimal( erp_ac_remove_thousand_sep( $total ), 2 );
        $sub_total = erp_ac_format_decimal( erp_ac_remove_thousand_sep( $sub_total ), 2 );

        // some basic validation
        if ( ! $issue_date ) {
            return new \WP_Error( 'required_issue_date', __( 'Error: Issue Date is required', 'erp' ) );
        }

        if ( ! $account_id ) {
            return new \WP_Error( 'required_account_id', __( 'Error: Account ID is required', 'erp' ) );
        }

        if ( ! $total ) {
            return new \WP_Error( 'required_total_amount', __( 'Error: Total is required', 'erp' ) );
        }

        if ( $total < 1 ) {
            return new \WP_Error( 'ntotal_amount_greater_than_zero', __( 'Error: Total amount should be greater than zero', 'erp' ) );
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
            'issue_date'      => $issue_date,
            'due_date'        => $due_date,
            'summary'         => $summary,
            'total'           => $total,
            'sub_total'       => $sub_total,
            'invoice_number'  => $invoice,
            'trans_total'     => $total,
            'files'           => $files,
            'currency'        => $currency,
            'line_total'      => isset( $postdata['line_total'] ) ? array_map( 'erp_ac_remove_thousand_sep', $postdata['line_total'] ) : array()
        ];

        // set invoice and vendor credit for due to full amount
        if ( $this->is_due_trans( $form_type, $postdata ) ) { //in_array( $form_type, [ 'invoice', 'vendor_credit' ] ) ) {
            $fields['due'] = $total;
        }

        $items = [];
        foreach ( $line_account as $key => $acc_id ) {
            $line_total = erp_ac_format_decimal( erp_ac_remove_thousand_sep( $postdata['line_total'][ $key ] ), 2 );
            if ( ! $acc_id || ! $line_total ) {
                continue;
            }

            $unit_price    = erp_ac_format_decimal( erp_ac_remove_thousand_sep( $postdata['line_unit_price'][ $key ] ), 2 );
            $tax_rate      = 0;
            $line_tax      = 0;
            $tax_amount    = 0;

            if ( isset( $postdata['tax_rate'] ) ) {
                $tax_rate  = erp_ac_format_decimal( erp_ac_remove_thousand_sep( $postdata['tax_rate'][ $key ] ), 2 );
            }

            if ( isset( $postdata['line_tax'] )) {
                $lien_tax  = erp_ac_format_decimal( erp_ac_remove_thousand_sep( $postdata['line_tax'][ $key ] ), 2 );
            }

            if ( isset( $postdata['tax_amount'] )) {
                $lien_tax  = erp_ac_format_decimal( erp_ac_remove_thousand_sep( $postdata['tax_amount'][ $key ] ), 2 );
            }

            $line_discount = erp_ac_format_decimal( erp_ac_remove_thousand_sep( $postdata['line_discount'][ $key ] ), 2 );

            $items[] = apply_filters( 'erp_ac_transaction_lines', [
                'item_id'     => isset( $postdata['items_id'][ $key ] ) ? $postdata['items_id'][ $key ] : [],
                'journal_id'  => isset( $postdata['journals_id'][ $key ] ) ? $postdata['journals_id'][ $key ] : [],
                'account_id'  => (int) $acc_id,
                'description' => sanitize_text_field( $postdata['line_desc'][ $key ] ),
                'qty'         => $postdata['line_qty'][ $key ],
                'unit_price'  => $unit_price,
                'discount'    => $line_discount,
                'tax'         => isset( $postdata['line_tax'][ $key ] ) ? $line_tax : 0,
                'tax_rate'    => isset( $postdata['tax_rate'][ $key ] ) ? $tax_rate : 0,
                'tax_amount'  => isset( $postdata['tax_amount'][ $key ] ) ? $tax_amount : 0,
                'line_total'  => $line_total,
                'tax_journal' => isset( $postdata['tax_journal'][ $key ] ) ? $postdata['tax_journal'][ $key ] : 0
            ], $key, $postdata );
        }

        // New or edit?
        if ( ! $field_id ) {
            $insert_id = erp_ac_insert_transaction( $fields, $items );
        }

        return $insert_id;
    }

    /**
     * Check is the payment type partial or not
     *
     * @param  array $trans
     *
     * @return  boolen
     */
    function is_due_trans( $form_type, $postdata ) {
        $due = apply_filters( 'erp_ac_is_due_trans', [ 'invoice', 'vendor_credit' ], $postdata );

        if ( in_array( $form_type, $due ) ) {
            return true;
        }

        return false;
    }

    /**
     * New journal
     *
     * @since 1.1.0
     *
     * @return  boolen
     */
    public function journal_entry() {
        if ( ! erp_ac_create_journal() ) {
            return new \WP_Error( 'error', __( 'You do not have sufficient permissions', 'erp' ) );
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'erp-ac-journal-entry' ) ) {
            die( __( 'Are you cheating?', 'erp' ) );
        }

        $thousand_seperator = erp_ac_get_price_thousand_separator();
        $ref                = isset( $_POST['ref'] ) ? sanitize_text_field( $_POST['ref'] ) : '';
        $issue_date         = isset( $_POST['issue_date'] ) ? sanitize_text_field( $_POST['issue_date'] ) : '';
        $summary            = isset( $_POST['summary'] ) ? sanitize_text_field( $_POST['summary'] ) : '';
        $debit_total        = isset( $_POST['debit_total'] ) ? str_replace( $thousand_seperator, '', $_POST['debit_total'] ) : 0.00;
        $credit_total       = isset( $_POST['credit_total'] ) ? str_replace( $thousand_seperator, '', $_POST['credit_total'] ) : 0.00;

        if ( $debit_total < 0 || $credit_total < 0 ) {
            wp_die( __( 'Value can not be negative', 'erp' ) );
        }

        if ( $debit_total != $credit_total ) {
            wp_die( __( 'Debit and credit total did not match.', 'erp' ) );
        }

        $args = [
            'id'         => isset( $_POST['id'] ) ? intval( $_POST['id'] ) : false,
            'type'       => 'journal',
            'ref'        => $ref,
            'summary'    => $summary,
            'issue_date' => $issue_date,
        ];

        $items = [];
        foreach ( $_POST['journal_account'] as $key => $account_id ) {
            $debit  = floatval( $_POST['line_debit'][ $key ] );
            $credit = floatval( $_POST['line_credit'][ $key ] );
            $des    = isset( $_POST['line_desc'][ $key ] ) ? $_POST['line_desc'][ $key ] : '';

            if ( $debit ) {
                $type   = 'debit';
                $amount = $debit;
            } else {
                $type   = 'credit';
                $amount = $credit;
            }

            $items[] = [
                'item_id'     => isset( $_POST['item_id'][ $key ] ) ? intval( $_POST['item_id'][ $key ] ) : false,
                'journal_id'  => isset( $_POST['journal_id'][ $key ] ) ? intval( $_POST['journal_id'][ $key ] ) : false,
                'ledger_id'   => (int) $account_id,
                $type         => $amount,
                'description' => $des
            ];
        }

        erp_ac_new_journal( $args, $items );

        $location = admin_url( 'admin.php?page=erp-accounting-journal&msg=success' );
        wp_redirect( $location );
    }
}
