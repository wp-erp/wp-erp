<?php
namespace WeDevs\ERP\Accounting;

use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax Class
 *
 * @package WP-ERP
 * @subpackage Accounting
 */
class Ajax_Handler {

    use Ajax;
    use Hooker;

    /**
     * Initial action for this class
     *
     * @return void
     */
    function __construct() {
        $this->action( 'wp_ajax_erp_ac_ledger_check_code', 'check_ledger_code' );
        $this->action( 'wp_ajax_erp_ac_payment_receive', 'receive_payment' );
        $this->action( 'wp_ajax_ac_transfer_money', 'transfer_money' );
        $this->action( 'wp_ajax_ac_bank_balance', 'bank_balance' );
        $this->action( 'wp_ajax_erp_ac_vendor_voucher', 'erp_ac_vendor_voucher' );
        $this->action( 'wp_ajax_erp_ac_customer_address', 'customer_address' );
        $this->action( 'wp_ajax_erp_ac_vendor_address', 'vendor_address' );
        $this->action( 'wp_ajax_erp_ac_individual_invoice_payment', 'receive_individual_invoice' );
        //$this->action( 'wp_ajax_erp_ac_vendoer_credit_payment', 'receive_individual_vendoer_credit' );
        $this->action( 'wp_ajax_erp-ac-customer-delete', 'customer_remove' );
        $this->action( 'wp_ajax_erp-ac-customer-restore', 'customer_restore' );
        $this->action( 'wp_ajax_erp-ac-user-delete-status', 'user_delete_status' );
        $this->action( 'wp_ajax_erp-ac-reference', 'check_unique_reference' );
        $this->action( 'wp_ajax_erp-ac-check-invoice-number', 'check_unique_invioce' );
        $this->action( 'wp_ajax_erp-ac-new-customer-vendor', 'new_vendor_customer' );
        $this->action( 'wp_ajax_erp-ac-transaction-report', 'transaction_report' );
        $this->action( 'wp_ajax_erp_people_convert', 'convert_user' );
        $this->action( 'wp_ajax_erp-ac-new-tax', 'new_tax' );
        $this->action( 'wp_ajax_erp-ac-delete-tax', 'delete_tax' );
        $this->action( 'wp_ajax_erp-ac-remove-account', 'remove_account' );
        $this->action( 'wp_ajax_erp-ac-sales-invoice-export', 'sales_invoice_export' );
        $this->action( 'wp_ajax_erp-ac-sales-payment-export', 'sales_payment_export' );
        $this->action( 'wp_ajax_erp-ac-invoice-send-email', 'sales_invoice_send_email' );
        $this->action( 'wp_ajax_erp-ac-get-invoice-number', 'popup_get_invoice_number' );
        $this->action( 'wp_ajax_erp_ac_trans_form_submit', 'transaction_form_submit' );
        $this->action( 'wp_ajax_erp-ac-row-bulk-action', 'transaction_row_bulk_action' );
        $this->action( 'wp_ajax_erp-ac-trns-restore', 'transaction_restore' );
        $this->action( 'wp_ajax_erp-ac-trns-void', 'transaction_void' );
        $this->action( 'wp_ajax_erp-ac-trns-redo', 'transaction_redo' );
        $this->action( 'wp_ajax_erp-search-ac-user', 'search_user' );
        $this->action( 'wp_ajax_erp-check-vendor-existance', 'check_vendor_existance' );
        $this->action( 'wp_ajax_get-customer-address', 'get_customer_formatted_address' );
    }

    /**
     * Search accounting users
     *
     * @since 1.1.9
     *
     * @return void
     */
    public function search_user() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $peoples = [];


        $term = ! empty( $_REQUEST['q'] ) ? stripslashes( $_REQUEST['q'] ) : false;
        $type = ! empty( $_REQUEST['type'] ) ? $_REQUEST['type'] : false;



        if ( ! $term || ! $type ) {
            $this->send_error( __( 'User not found', 'erp' )  );
        }

        $args = [
            'type'   => $type,
            'number' => '-1',
            's'      => $term
        ];

        $users = erp_get_peoples( $args );

        foreach ( $users as $key => $user ) {
            $peoples[$user->id] = in_array( 'vendor', $user->types ) ? $user->company : $user->first_name . ' ' . $user->last_name;
        }

        $this->send_success( $peoples );
    }

    /**
     * Change transaction status paid or closed to awating payment
     *
     * @since  1.1.1
     *
     * @return void
     */
    public function transaction_redo() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $trns_id = isset( $_POST['id'] ) ? $_POST['id'] : false;
        $update  = false;

        if ( $trns_id ) {
            $update = erp_ac_update_transaction( $trns_id, ['status' => 'awaiting_payment'] );
        }

        if ( $update ) {
            $this->send_success( array( 'success' => __( 'Transaction status has been changed successfully', 'erp' ) ) );
        } else {
            $this->send_error( array( 'error' => __( 'Unknown Error', 'erp' ) ) );
        }
    }

    /**
     * Change transaction status awating for payment to void
     *
     * @since  1.1.1
     *
     * @return void
     */
    public function transaction_void() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $trns_id = isset( $_POST['id'] ) ? $_POST['id'] : false;
        $delete = false;
        if ( $trns_id ) {
            $delete = erp_ac_update_transaction( $trns_id, ['status' => 'void'] );
        }

        if ( $delete ) {
            $this->send_success( array( 'success' => __( 'Transaction has been void successfully', 'erp' ) ) );
        } else {
            $this->send_error( array( 'error' => __( 'Unknown Error', 'erp' ) ) );
        }
    }

    /**
     * Transaction restore functionality
     *
     * @since 1.0
     *
     * @return void
     */
    public function transaction_restore() {
        $this->verify_nonce( 'erp-ac-nonce' );
        parse_str( $_POST['data'], $postdata );

        $trns_id = isset( $_POST['id'] ) ? $_POST['id'] : false;
        $type    = $_POST['type'];
        $delete  = false;

        if ( $trns_id ) {
            $delete = erp_ac_update_transaction( $trns_id, ['status' => $postdata['status']] );
        }

        if ( $type == 'journal' ) {
            $url = erp_ac_get_journal_url();
        } else if ( $type == 'expense' ) {
            $url = erp_ac_get_expense_url();
        } else {
            $url = erp_ac_get_sales_url();
        }

        if ( $delete ) {
            $this->send_success( array( 'url' => $url, 'success' => __( 'Transaction status has been changed successfully', 'erp' ) ) );
        } else {
            $this->send_error( array( 'error' => __( 'Unknown Error', 'erp' ) ) );
        }
    }

    /**
     * Delete transaction
     *
     * @since  1.1.1
     *
     * @return void
     */
    public function transaction_row_bulk_action() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $trns_id = isset( $_POST['id'] ) ? $_POST['id'] : false;
        $status  = isset( $_POST['status'] ) ? $_POST['status'] : false;

        switch ( $status ) {
            case 'delete':
                $update = erp_ac_remove_transaction( $trns_id );
                break;

            case 'void':
                erp_ac_update_transaction_to_void( $trns_id, ['status' => $status] );
                break;

            default:
                $update = erp_ac_update_transaction( $trns_id, ['status' => $status] );
                break;
        }

        $this->send_success( array( 'success' => __( 'Transaction has been updated successfully', 'erp' ) ) );

        //$this->send_error( array( 'error' => __( 'Unknown Error', 'erp' ) ) );
    }

    /**
     * Transaction form submission
     *
     * @since  1.0
     *
     * @return void
     */
    public function transaction_form_submit() {
        $this->verify_nonce( 'erp-ac-nonce' );
        parse_str( $_POST['form_data'], $postdata );
        $postdata['status']  = erp_ac_get_status_according_with_btn( $_POST['btn_status'] );

        $transaction = \WeDevs\ERP\Accounting\Form_Handler::transaction_data_process( $postdata );
        $return_url = '';

        if ( $postdata['type'] == 'sales' ) {
            $return_url = erp_ac_get_sales_url( false );
        } else if ( $postdata['type'] == 'expense' ) {
            $return_url = erp_ac_get_sales_url( false );
        } else if ( $postdata['type'] == 'journal' ) {
            $return_url = erp_ac_get_journal_url( false );
        }

        if ( is_wp_error( $transaction ) ) {
            wp_send_json_error( array( 'message' => $transaction->get_error_message() ) );
        } else {
            wp_send_json_success( array( 'return_url' => $return_url, 'message' => __( 'Transaction has been created successfully', 'erp' ) ) );
        }
    }

    /**
     * Get invoice number from popup
     *
     * @since  1.0
     *
     * @return void
     */
    public function popup_get_invoice_number() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $type = isset( $_POST['type'] ) ? $_POST['type'] : false;

        if ( ! $type ) {
            $this->send_error( array( 'error' => __( 'Type required', 'erp' ) ) );
        }

        if ( ( 'payment' === $type ) || ( 'payment_voucher' === $type ) ) {
            $invoice_number = erp_ac_get_auto_generated_invoice( $type );
        }

        $this->send_success( array( 'invoice_number' => $invoice_number ) );
    }

    /**
     * Remove an account chart
     *
     * @since  1.0
     *
     * @return void
     */
    public function remove_account() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $chart_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : false;
        $delete = '';

        if ( $chart_id ) {
            $delete = erp_ac_delete_chart( $chart_id );
        }

        if ( is_wp_error( $delete ) ) {
            $this->send_error( array( 'error' => $delete->get_error_message() ) );
        }

        $this->send_success( array( 'success' => __( 'Account record has been deleted successfully', 'erp' ) ) );
    }

    /**
     * Convert a user into customer or vendor
     *
     * @since  1.0
     *
     * @return void
     */
    public function convert_user() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $type       = isset( $_POST['type'] ) ? $_POST['type'] : '';
        $people_id  = isset( $_POST['people_id'] ) ? absint( $_POST['people_id'] ) : 0;
        $is_wp_user = isset( $_POST['is_wp_user'] ) ? filter_var( $_POST['is_wp_user'], FILTER_VALIDATE_BOOLEAN ) : false;
        $wp_user_id = isset( $_POST['wp_user_id'] ) ? absint( $_POST['wp_user_id'] ) : 0;

        $args = [
            'type'       => $type,
            'is_wp_user' => $is_wp_user,
            'wp_user_id' => $wp_user_id,
            'people_id'  => $people_id
        ];

        $people_id = erp_convert_to_people( $args );

        if ( is_wp_error( $people_id ) ) {
            $this->send_error( $people_id->get_error_message() );
        }

        if ( $type == 'customer' ) {
            $redirect = erp_ac_customer_edit_url( $people_id ) . '&status=new';
        } else {
            $redirect = erp_ac_vendor_edit_url( $people_id ) . '&status=new';
        }

        $this->send_success( array( 'redirect' => $redirect ) );
    }

    /**
     * Delete tax
     *
     * @since  1.1.0
     *
     * @return void
     */
    public function delete_tax() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $tax_id = isset( $_POST['tax_id'] ) ? intval( $_POST['tax_id'] ) : false;
        $delete = '';

        if ( $tax_id ) {
            $delete = erp_ac_delete_tax( $tax_id );
        }

        if ( is_wp_error( $delete ) ) {
            $this->send_error( $delete->get_error_message() );
        }

        $this->send_success();
    }

    /**
     * Create a new tax
     *
     * @since  1.1.0
     *
     * @return void
     */
    public function new_tax() {
        $this->verify_nonce( 'erp-ac-nonce' );
        parse_str( $_POST['post'], $postdata );

        $new_tax = erp_ac_new_tax( $postdata );
        $tax_id  = $postdata['id'] ? $postdata['id'] : $new_tax->id;

        if ( $tax_id ) {
            $items   = erp_ac_update_tax_items( $postdata, $tax_id );
            $account = erp_ac_new_tax_account( $postdata, $tax_id );
            $this->send_success();
        }

        $this->send_error();
    }

    /**
     * Show transaction report
     *
     * @since  1.0
     *
     * @return void
     */
    public function transaction_report() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $transaction_id = isset( $_POST['transaction_id'] ) ? intval( $_POST['transaction_id'] ) : false;
        $transaction    = Model\Transaction::find( $transaction_id );
        $popup_status   = false;

        ob_start();

        if ( $transaction->type == 'sales' ) {
        if( $transaction->form_type == 'invoice' ) {
        require_once WPERP_ACCOUNTING_VIEWS . '/sales/invoice-single.php';
        }
        else {
        require_once WPERP_ACCOUNTING_VIEWS . '/sales/payment-single.php';
        }

        } else if ( $transaction->type == 'expense' ) {
            require_once WPERP_ACCOUNTING_VIEWS . '/expense/payment-voucher-single.php';
        } else if ( $transaction->type == 'journal' ) {
            require_once WPERP_ACCOUNTING_VIEWS . '/journal/single.php';
        } else if ( $transaction->type == 'transfer' ) {
            require_once WPERP_ACCOUNTING_VIEWS . '/bank/invoice.php';
        }

        $this->send_success( [ 'content' => ob_get_clean()] );
    }

    /**
     * Create new vendor or customer
     *
     * @since  1.0
     *
     * @return void
     */
    public function new_vendor_customer() {
        $this->verify_nonce( 'erp-ac-nonce' );
        parse_str( $_POST['post'], $postdata );
        $insert_id = erp_ac_new_customer( $postdata );

        if ( $insert_id ) {
            $this->send_success( [ 'id' => $insert_id] );
        } else {
            $this->send_error( __( 'Required unique value!', 'erp' ) );
        }
    }

    /**
     * Check unique reference in transaction
     *
     * @since  1.0
     *
     * @return void
     */
    public function check_unique_reference() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $ref   = isset( $_POST['reference'] ) ? $_POST['reference'] : '';
        $trans = new \WeDevs\ERP\Accounting\Model\Transaction();
        $trans = $trans->where( 'ref', '=', $ref )->get()->toArray();

        if ( $trans ) {
            $this->send_error( __( 'Reference already exists. Please use an unique number', 'erp' ) );
        } else {
            $this->send_success();
        }
    }

    /**
     * Check unique invoice number
     *
     * @since  1.1.0
     *
     * @return void
     */
    public function check_unique_invioce() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $invoice   = isset( $_POST['invoice'] ) ? $_POST['invoice'] : '';
        $form_type = $_POST['form_type'];
        $trans     = erp_ac_check_invoice_number_unique( $invoice, $form_type );

        if ( ! $trans ) {
            $this->send_error( __( 'Invoice already exists. Please use an unique number', 'erp' ) );
        } else {
            $this->send_success();
        }
    }

    /**
     * Delete user status
     *
     * @since  1.0
     *
     * @return void
     */
    public function user_delete_status() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $transactions = erp_ac_get_all_transaction( [
            'type' => ['expense', 'sales'],
            'user_id' => isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0,
        ] );

        if ( $transactions ) {
            $this->send_error( __( 'You can not remove this user', 'erp' ) );
        } else {
            $this->send_success();
        }
    }

    /**
     * Restore customer from trash
     *
     * @since 1.0
     *
     * @return json
     */
    public function customer_restore() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $customer_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( ! $customer_id ) {
            $this->send_error( __( 'No Customer found', 'erp' ) );
        }

        $type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '';

        $data = [
            'id'   => $customer_id,
            'type' => $type
        ];

        // @TODO: check permission
        erp_restore_people( $data );

        $this->send_success( __( 'Customer has been removed successfully', 'erp' ) );
    }


    /**
     * Delete customer data with meta
     *
     * @since 1.0
     *
     * @return json
     */
    public function customer_remove() {

        $this->verify_nonce( 'erp-ac-nonce' );

        $customer_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
        $hard        = isset( $_REQUEST['hard'] ) ? intval( $_REQUEST['hard'] ) : 0;
        $type        = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : 0;

        if ( ! $customer_id ) {
            $this->send_error( __( 'No Customer found', 'erp' ) );
        }

        $data = array(
            'id'   => $customer_id,
            'hard' => $hard,
            'type' => $type,
        );

        erp_ac_customer_delete( $data );

        // @TODO: check permission
        $this->send_success( __( 'Customer has been removed successfully', 'erp' ) );
    }


    /**
     * Recieve individual invoice
     *
     * @since  1.0
     *
     * @return void
     */
    public function receive_individual_invoice() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $args = [
            'id'          => '',
            'partial_id'  => $_POST['partial_id'],
            'items_id'    => [],
            'type'        => $_POST['type'],
            'form_type'   => $_POST['form_type'],
            'account_id'  => $_POST['account_id'],
            'status'      => $_POST['type'] == 'sales' ? 'closed' : 'paid',
            'user_id'     => $_POST['user_id'],
            'ref'         => $_POST['ref'],
            'issue_date'  => $_POST['issue_date'],
            'summary'     => $_POST['summary'],
            'total'       => reset( $_POST['line_total'] ),
            'trans_total' => reset( $_POST['line_total'] ),
            'files'       => isset( $_POST['files'] ) ? maybe_serialize( $_POST['files'] ) : '',
            'currency'    => erp_ac_get_currency(),
            'line_total'  => $_POST['line_total'],
            'journals_id' => [],
        ];

        $items[] = [
            'item_id'     => [],
            'journal_id'  => [],
            'account_id'  => 1,
            'description' => '',
            'qty'         => 1,
            'unit_price'  => 0,
            'discount'    => 0,
            'line_total'  => reset( $_POST['line_total'] ),
            'tax'         => 0,
            'tax_rate'    => '0.00',
            'tax_journal' => 0
        ];

        $transaction = erp_ac_insert_transaction( $args, $items );

        if ( $transaction ) {
            $this->send_success();
        }

    }

    /**
     * Get vendor address fields
     *
     * @since  1.0
     *
     * @return void
     */
    public function vendor_address() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $user_id = intval( $_POST['vendor_id'] );

        if ( ! $user_id ) {
            $this->send_error();
        }

        $people = erp_get_people( $user_id );

        $args = [
            'address_1' => isset( $people->street_1 ) ? $people->street_1 ."\n" : '',
            'address_2' => isset( $people->street_2 ) ? $people->street_2 ."\n" : '',
            'city'      => isset( $people->city ) ? $people->city ."\n" : '',
            'state'     => isset( $people->country ) && isset( $people->state ) ? erp_get_state_name( $people->country, $people->state ) ."\n" : '',
            'postcode'  => isset( $people->postal_code ) ? $people->postal_code ."\n" : '',
            'country'   => isset( $people->country ) ? erp_get_country_name( $people->country ) ."\n" : '',

        ];

        $this->send_success( implode('', $args));
    }

    /**
     * Get customer address fields
     *
     * @since  1.0
     *
     * @return void
     */
    public function customer_address() {
        $this->verify_nonce( 'erp-ac-nonce' );
        $customer_id = intval( $_POST['customer_id'] );

        if ( ! $customer_id ) {
            $this->send_error();
        }

        $people = erp_get_people( $customer_id );

        $args = [
            'address_1' => $people->street_1 ."\n",
            'address_2' => $people->street_2 ."\n",
            'city'      => $people->city ."\n",
            'state'     => erp_get_state_name( $people->country, $people->state ) ."\n",
            'postcode'  => $people->postal_code ."\n",
            'country'   => erp_get_country_name( $people->country ) ."\n",

        ];

        $this->send_success( implode('', $args));
    }

    /**
     * Payment voucher invoice
     *
     * @since  1.0
     *
     * @return void
     */
    public function erp_ac_vendor_voucher() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $vendor    = intval( $_POST['vendor'] );
        $type      = $_POST['type'];
        $form_type = $_POST['form_type'];

        if ( ! $vendor ) {
            $this->send_error();
        }

        $transactions = erp_ac_get_all_transaction([
            'user_id'     => $vendor,
            'type'        => $type,
            //'form_type'   => $form_type,
            'status'      => array( 'in' => array( 'awaiting_payment', 'partial' ) ),
            'join'        => ['journals'],
            'with_ledger' => true,
            'output_by'   => 'array'
        ]);

        if ( ! $transactions ) {
            $this->send_error();
        }

        ob_start();
        include_once WPERP_ACCOUNTING_VIEWS . '/expense/payment-voucher-invoice.php';
        $this->send_success( ob_get_clean() );
    }

    /**
     * Get individual bank balance
     *
     * @since  1.0
     *
     * @return void
     */
    public function bank_balance() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $bank_id      = isset( $_POST['bank_id'] ) ? intval( $_POST['bank_id'] ) : 0;
        $total_amount = erp_ac_get_individual_bank_balance( $bank_id );

        $this->send_success( array( 'total_amount' => $total_amount ) );
    }

    /**
     * Transfer money to acc
     *
     * @since  1.0
     *
     * @return void
     */
    public function transfer_money() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $from   = intval( $_POST['form_account_id'] );
        $to     = intval( $_POST['to_account_id'] );
        $amount = floatval( $_POST['amount'] );

        $debit_credit  = erp_ac_bank_credit_total_amount( $from );
        $ledger_amount = abs( $debit_credit['debit'] - $debit_credit['credit'] );

        if ( $ledger_amount < $amount ) {
            $this->send_error( __( 'No enough money from your transfer account', 'wp-account' ) );
        }

        $args = array(
            'type'            => 'transfer',
            'form_type'       => 'bank',
            'status'          => 'closed',
            'account_id'      => $from,
            'user_id'         => get_current_user_id(),
            'billing_address' => '',
            'ref'             => '',
            'issue_date'      => $_POST['date'],
            'summary'         => sanitize_text_field( $_POST['memo'] ),
            'total'           => $amount,
            'trans_total'     => $amount,
            'currency'        => erp_ac_get_currency(),
            'created_by'      => get_current_user_id(),
            'created_at'      => current_time( 'mysql' )

        );

        $items[] = array(
            'account_id'  => $to,
            'type'        => 'line_item',
            'line_total'  => $amount,
            'description' => '',
            'qty'         => 1,
            'unit_price'  => $amount,
            'discount'    => 0
        );

        $transaction_id = erp_ac_insert_transaction( $args, $items );

        if ( $transaction_id ) {
            $this->send_success();
        }

        $this->send_error();
    }

    /**
     * Receive a payment
     *
     * @since  1.1.1
     *
     * @return void
     */
    public function receive_payment() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $user_id    = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : false;
        $account_id = isset( $_POST['account_id'] ) ? intval( $_POST['account_id'] ) : false;

        if ( ! $user_id ) {
            $this->send_error();
        }

        $results = erp_ac_get_all_transaction([
            //'form_type'   => 'invoice',
            'status'      => ['in' => ['awaiting_payment', 'partial']],
            'user_id'     => $user_id,
            'parent'      => 0,
            'type'        => 'sales',
            'join'        => ['journals'],
            'with_ledger' => true,
            'output_by'   => 'array'
        ]);

        if ( ! $results ) {
            $this->send_error();
        }

        ob_start();
        include_once WPERP_ACCOUNTING_VIEWS . '/sales/bank-transfer-form.php';
        $this->send_success( ob_get_clean() );
    }

    /**
     * Check ledget code
     *
     * @since 1.0
     *
     * @return void
     */
    public function check_ledger_code() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $code = isset( $_POST['code'] ) ? intval( $_POST['code'] ) : '';

        if ( Model\Ledger::code( $code )->get()->first() === null ) {
            $this->send_success();
        }

        $this->send_error();
    }

    /**
     * Accounting Sales Invoice Export
     *
     * @since 1.0
     *
     * @return void
     */
    public function sales_invoice_export() {
        check_ajax_referer( 'accounting-invoice-export' );

        $transaction_id = isset( $_REQUEST['transaction_id'] ) ? $_REQUEST['transaction_id'] : 0;

        erp_ac_send_invoice_pdf( $transaction_id );
        exit;
    }

    /**
     * Accounting Payment Invoice Export as pdf
     *
     * @since 1.0
     *
     * @return void
     */
    public function sales_payment_export() {
        check_ajax_referer( 'accounting-payment-export' );

        $transaction_id = isset( $_REQUEST['transaction_id'] ) ? $_REQUEST['transaction_id'] : 0;
        $transaction    = Model\Transaction::find( $transaction_id );
        $file_name      = sprintf( '%s_%s.pdf', $transaction->invoice_number, $transaction->issue_date );
        $output_method  = 'D';

        if ( $transaction_id ) {
            include WPERP_ACCOUNTING_VIEWS . '/pdf/payment.php';
        }
        exit;
    }

    /**
     * Send Invoice via Email
     *
     * @since 1.0
     *
     * @return void
     */
    public function sales_invoice_send_email() {
        $this->verify_nonce( 'erp-ac-invoice-send-email' );

        $transaction_id = isset( $_REQUEST['transaction_id'] ) ? intval( $_REQUEST['transaction_id'] ) : 0;

        erp_ac_send_email_with_pdf_attached( $transaction_id, $_REQUEST );

        wp_send_json_success();
    }

    /**
     * Check vendor existance
     *
     * @since 1.3.9
     *
     * @return bool
     */

     public function check_vendor_existance() {
        $vendor = sanitize_text_field( $_REQUEST['vendor'] );

        if ( isset( $_REQUEST['field_id'] ) ) {
            $people = \WeDevs\ERP\Framework\Models\People::where( 'company', $vendor )
                        ->where( 'id', '!=', intval( $_REQUEST['field_id'] ) )->first();
        } else {
            $people = erp_get_people_by( 'company', $vendor );
        }

        $this->send_success( $people );
     }

     /**
      * Get customer address for billing
      * @return string
      */
     public function get_customer_formatted_address() {
        $this->verify_nonce( 'erp-ac-nonce' );

        $customer = erp_get_people( $_REQUEST['id'] );
        $customer_address = [];

        // Street
        $street = '';
        $street .= !empty($customer->street_1) ? $customer->street_1 . ', ' : '';
        $street .= !empty($customer->street_2) ? $customer->street_2 : '';

        if ($street) {
            $street .= "\n";
        }
        array_push($customer_address, $street);

        // City
        $city = !empty($customer->city) ? $customer->city . "\n" : '';
        array_push($customer_address, $city);

        // State
        $state = ($customer->state !== '-1') ? erp_get_state_name( $customer->country, $customer->state ) . "\n" : '';
        array_push($customer_address, $state);

        // Postal code
        $postal_code = !empty($customer->postal_code) ? $customer->postal_code . "\n" : '';
        array_push($customer_address, $postal_code);

        // Country
        $country = ($customer->country !== '-1') ? erp_get_country_name( $customer->country) : '';
        array_push($customer_address, $country);

        $this->send_success( $customer_address );
     }
}
