<?php
$account        = isset( $_GET['spend_money'] ) && $_GET['spend_money'] == 'true' ? true : false;
$account_id     = $account && isset( $_GET['bank'] ) ? intval( $_GET['bank'] ) : false;
$banks          = erp_ac_get_bank_account();
$bank_details   = wp_list_pluck( $banks, 'bank_details' );
$banks_id       = wp_list_pluck( $bank_details, 'ledger_id' );
$transaction_id = isset( $_GET['transaction_id'] ) ? intval( $_GET['transaction_id'] ) : false;
$transaction    = [];
$jor_itms       = [];
$cancel_url     = erp_ac_get_expense_url();
$partial = false;

if ( $transaction_id ) {
    $transaction = erp_ac_get_all_transaction([
        'id'     => $transaction_id,
        'status' => ['in' => ['awaiting_payment', 'partial']],
        'join'   => ['journals', 'items'],
        'type'   => ['expense'],
        'output_by' => 'array'
    ]);

    $transaction = reset( $transaction );
    if ( $transaction ) {
        $partial = true;
    }

    foreach ( $transaction['journals'] as $key => $journal ) {

        $journal_id = $journal['id'];

        if ( $journal['type'] == 'main' ) {
            $account_id       = $main_ledger_id  = $journal['ledger_id'];
            $jor_itms['main'] = $journal;

        } else {
            $jor_itms['journal'][] = $journal;
        }
    }

    foreach ( $transaction['items'] as $key => $item ) {
        $journal_id         = $item['journal_id'];
        $jor_itms['item'][] = $item;
    }
}

if ( $transaction_id ) {
    $selected_vendor = isset( $transaction['user_id'] ) ? $transaction['user_id'] : '';
} else {
    $selected_vendor = isset( $_GET['vendor_id'] ) ? intval( $_GET['vendor_id'] ) : '';
}

$items_for_tax = isset( $transaction['items'] ) ? $transaction['items'] : [];
$tax_labels    = erp_ac_get_trans_unit_tax_rate( $items_for_tax );

?>
<div class="wrap erp-ac-form-wrap">
    <h2><?php _e( 'Payment Voucher', 'erp' ); ?></h2>
    <?php erp_ac_view_error_message(); ?>
    <?php
    $selected_account_id = isset( $_GET['account_id'] ) ? intval( $_GET['account_id'] ) : 0;
    $dropdown = erp_ac_get_chart_dropdown([
        'exclude'  => [1, 2, 4, 5],

    ] );


    $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
        'name'     => 'line_account[]',
        'selected' => isset( $journal['ledger_id'] ) ? $journal['ledger_id'] : false,
        'class'    => 'erp-select2'
    ) );

    ?>
    <form action="" method="post" class="erp-form erp-ac-transaction-form erp-ac-payment_voucher-form">

        <ul class="form-fields block" style="width:100%;">

            <li>
                <ul class="erp-form-fields two-col block">
                    <li class="erp-form-field erp-ac-replace-wrap">
                        <div class="erp-ac-replace-content">
                            <?php
                            erp_html_form_input( array(
                                'label'       => __( 'Vendor', 'erp' ),
                                'name'        => 'user_id',
                                'type'        => 'select',
                                'value'       => $selected_vendor,
                                'id'          => 'erp-ac-select-user-for-assign-contact',
                                'required'    => true,
                                //'class'       => 'erp-select2 erp-ac-vendor-drop erp-ac-not-found-in-drop',
                                'options'     => [ '' => __( 'Search by vendor', 'erp' ) ] + erp_ac_get_vendors(),
                                'custom_attr' => [
                                    'data-placeholder' => __( 'Search by vendor', 'erp' ),
                                    'data-content' => 'erp-ac-new-vendor-content-pop',
                                    'data-type' => 'vendor'
                                ],

                            ) );
                            ?>
                            <div><a href="#" data-content="erp-ac-new-vendor-content-pop" class="erp-ac-not-found-btn-in-drop erp-ac-more-customer"><?php _e( 'Create New', 'erp' ); ?></a></div>
                        </div>
                    </li>

                    <li class="cols erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label'       => __( 'From Account', 'erp' ),
                            'name'        => 'account_id',
                            'placeholder' => __( 'Select an Account', 'erp' ),
                            'type'        => 'select',
                            'required'    => true,
                            'class'       => 'erp-select2 erp-ac-voucher-bank',
                            'value'       => $account_id ? $account_id : $selected_account_id,
                            'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_ac_get_bank_dropdown()
                        ) );
                        ?>

                        <span class="balance-wrap">
                            <strong><?php _e( 'Balance: ', 'erp' ); ?><span class="erp-ac-bank-amount">0</span></strong>
                        </span>
                    </li>
                </ul>
            </li>

            <li>
                <ul class="erp-form-fields two-col block clearfix">
                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label'       => __( 'Payment Date', 'erp' ),
                            'name'        => 'issue_date',
                            'placeholder' => date( 'Y-m-d' ),
                            'type'        => 'text',
                            'value'       => isset( $transaction['issue_date'] ) ? $transaction['issue_date'] : date( 'Y-m-d', strtotime( current_time( 'mysql' ) ) ),
                            'required' => true,
                            'class'       => 'erp-date-field',
                        ) );
                        ?>
                    </li>

                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Reference', 'erp' ),
                            'name'  => 'ref',
                            'type'  => 'text',
                            'class' => 'erp-ac-reference-field',
                            'value'       => isset( $transaction['ref'] ) ? $transaction['ref'] : '',
                            'addon' => '#',
                        ) );
                        ?>
                    </li>
                </ul>
            </li>

        </ul>
        <div class="erp-ac-voucher-table-wrap">
        <?php
            if ( $transaction_id ) {
                $transactions = erp_ac_get_all_transaction([
                    'user_id'     => $selected_vendor,
                    'type'        => 'expense',
                    //'form_type'   => $form_type,
                    'status'      => array( 'in' => array( 'awaiting_payment', 'partial' ) ),
                    'join'        => ['journals'],
                    'with_ledger' => true,
                    'output_by'   => 'array'
                ]);
                include_once dirname( dirname( __FILE__ ) ) . '/expense/payment-voucher-invoice.php';
            } else {
                include dirname( dirname( __FILE__ ) ) . '/common/transaction-table.php';
            }
        ?>
        </div>
        <?php include dirname( dirname( __FILE__ ) ) . '/common/memo.php'; ?>

        <input type="hidden" name="field_id" value="0">
        <input type="hidden" name="type" value="expense">
        <input type="hidden" name="status" value="paid">
        <input type="hidden" name="form_type" value="payment_voucher">
        <input type="hidden" name="page" value="erp-accounting-expense">
        <input type="hidden" name="erp-action" value="ac-new-payment-voucher">
        <?php
            erp_html_form_input( array(
                'name'  => 'id',
                'type'  => 'hidden',
                'value' => ! $partial ? $transaction_id : ''
            ) );
        ?>


        <?php wp_nonce_field( 'erp-ac-trans-new' ); ?>

    <!--     <?php
        if ( erp_ac_publish_expenses_voucher() ) {
            ?>
            <input type="submit" name="submit_erp_ac_trans" id="submit_erp_ac_trans" class="button button-primary" value="Create Voucher">
            <?php
        }
        ?>

        <input type="submit" name="submit_erp_ac_trans_draft" id="submit_erp_ac_trans_draft" class="button button-secondary" value="Save as Draft">
 -->

        <input type="submit" name="submit_erp_ac_trans" style="display: none;">
        <input type="hidden" id="erp-ac-btn-status" name="btn_status" value="">
        <input type="hidden" id="erp-ac-redirect" name="redirect" value="0">

        <div class="erp-ac-btn-group-wrap">
             <div class="erp-button-bar-left">
                <a href="<?php echo esc_url( $cancel_url ); ?>" class="button"><?php _e( 'Cancel', 'erp' ); ?></a>
            </div>

            <div class="erp-button-bar-right">
                <div class="button-group erp-button-group">
                    <button  data-redirect="single_page" data-btn_status="payment" type="button" class="button button-primary erp-ac-trns-form-submit-btn">
                        <?php _e( 'Pay', 'erp' ); ?>
                    </button>
                    <button type="button" class="button button-primary erp-dropdown-toggle" data-toggle="erp-dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                    </button>
                    <ul class="erp-dropdown-menu">
                        <li><a class="erp-ac-trns-form-submit-btn" data-redirect="single_page" data-btn_status="payment" href="#"><?php _e( 'Pay', 'erp' ); ?></a></li>
                        <li><a class="erp-ac-trns-form-submit-btn" data-redirect="same_page" data-btn_status="payment_and_add_another" href="#"><?php _e( 'Pay & add another', 'erp' ); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>

    </form>

    <div class="erp-ac-voucher-table-wrap-clone" id="erp-ac-new-payment-voucher" style="display: none;">

    <?php
        $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
            'name'     => 'line_account[]',
            'class'    => 'erp-select2 erp-ac-selece-custom'
        ) );
        $jor_itms = [];

        include dirname( dirname( __FILE__ ) ) . '/common/transaction-table.php';?>
    </div>
</div>


