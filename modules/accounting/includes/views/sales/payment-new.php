<?php
$account        = isset( $_GET['receive_payment'] ) && $_GET['receive_payment'] == 'true' ? true : false;
$account_id     = $account && isset( $_GET['bank'] ) ? intval( $_GET['bank'] ) : false;
$customer_class = $account_id ? 'erp-ac-payment-receive' : '';
$transaction_id = isset( $_GET['transaction_id'] ) ? intval( $_GET['transaction_id'] ) : false;
$cancel_url     = erp_ac_get_sales_url();
$transaction    = [];
$jor_itms       = [];
$main_ledger_id = '';

if ( $transaction_id ) {
    $transaction = erp_ac_get_all_transaction([
        'id'     => $transaction_id,
        'status' => [ 'in' => ['draft', 'pending']],
        'join'   => ['journals', 'items'],
        'type'   => ['sales'],
        'output_by' => 'array'
    ]);

    $transaction = reset( $transaction );

    foreach ( $transaction['journals'] as $key => $journal ) {

        $journal_id = $journal['id'];

        if ( $journal['type'] == 'main' ) {
            $main_ledger_id  = $journal['ledger_id'];
            $jor_itms['main'] = $journal;

        } else {
            $jor_itms['journal'][] = $journal;
        }
    }

    foreach ( $transaction['items'] as $key => $item ) {
        $journal_id = $item['journal_id'];
        $jor_itms['item'][] = $item;

    }
}

$items_for_tax = isset( $transaction['items'] ) ? $transaction['items'] : [];
$tax_labels = erp_ac_get_trans_unit_tax_rate( $items_for_tax );

$main_ledger_id = isset( $_GET['bank'] ) ? intval( $_GET['bank'] ) : $main_ledger_id;

?>
<div class="wrap erp-ac-form-wrap">

    <h2><?php _e( 'Receive Payment', '$domain' ); ?></h2>
    <?php
    $dropdown = erp_ac_get_chart_dropdown([
        'exclude'  => [1, 2, 3, 5],

    ] );

    $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
        'name'     => 'line_account[]',
        'selected' => isset( $journal['ledger_id'] ) ? $journal['ledger_id'] : false,
        'class'    => 'erp-select2'
    ) );

    ?>

    <form action="" method="post" class="erp-form erp-ac-transaction-form erp-ac-payment-form">

        <ul class="form-fields block" style="width:100%;">

            <li>
                <ul class="erp-form-fields two-col block">
                    <li class="erp-form-field erp-ac-replace-wrap">
                        <div class="erp-ac-replace-content">
                            <?php

                            erp_html_form_input( array(
                                'label'       => __( 'Customer', 'erp' ),
                                'name'        => 'user_id',
                                'placeholder' => __( 'Select a payee', 'erp' ),
                                'value'       => isset( $transaction['user_id'] ) ? $transaction['user_id'] : '',
                                'type'        => 'select',
                                'required'    => true,
                                'class'       => $transaction_id ? 'erp-select2 erp-ac-not-found-in-drop' : 'erp-select2 erp-ac-payment-receive erp-ac-not-found-in-drop',
                                'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_get_peoples_array( ['type' => 'customer', 'number' => 100 ] ),
                                'custom_attr' => [
                                    'data-content' => 'erp-ac-new-customer-content-pop',
                                ],
                            ) );
                            ?>
                            <?php
                            if ( erp_ac_create_customer() ) {
                                ?>
                                <div><a href="#" data-content="erp-ac-new-customer-content-pop" class="erp-ac-not-found-btn-in-drop erp-ac-more-customer"><?php _e( 'Create New', 'erp' ); ?></a></div>
                                <?php
                            }
                            ?>
                        </div>
                    </li>

                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label' => __( 'Reference', 'erp' ),
                            'name'  => 'ref',
                            'type'  => 'text',
                            'class' => 'erp-ac-reference-field',
                            'addon' => '#',
                            'value' => isset( $transaction['ref'] ) ? $transaction['ref'] : ''
                        ) );
                        ?>
                    </li>

                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label'    => __( 'Invoice Number', 'erp' ),
                            'name'     => 'invoice',
                            'type'     => 'text',
                            'required' => true,
                            'class'    => 'erp-ac-check-invoice-number',
                            'value'    => isset( $transaction['invoice_number']  ) ? erp_ac_get_invoice_number( $transaction['invoice_number'], $transaction['invoice_format'] ) : erp_ac_get_auto_generated_invoice( 'payment' )
                        ) );
                        ?>
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
                            'required'    => true,
                            'class'       => 'erp-date-field',
                            'value'       => isset( $transaction['issue_date'] ) ? $transaction['issue_date'] : ''
                        ) );
                        ?>
                    </li>

                    <li class="cols erp-form-field">
                        <?php
                            erp_html_form_input( array(
                                'label'       => __( 'Deposit To', 'erp' ),
                                'name'        => 'account_id',
                                'placeholder' => __( 'Select an Account', 'erp' ),
                                'type'        => 'select',
                                'class'       => 'erp-select2 erp-ac-deposit-dropdown',
                                'value'       => $main_ledger_id,
                                'required'    => true,
                                'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_ac_get_bank_dropdown()
                            ) );
                        ?>

                    </li>
                </ul>
            </li>

        </ul>


        <div class="erp-ac-receive-payment-table">
            <?php include dirname( dirname( __FILE__ ) ) . '/common/transaction-table.php';?>
        </div>

        <?php include dirname( dirname( __FILE__ ) ) . '/common/memo.php'; ?>

        <input type="hidden" name="field_id" value="0">
        <input type="hidden" name="status" value="closed">
        <input type="hidden" name="type" value="sales">
        <input type="hidden" name="form_type" value="payment">
        <input type="hidden" name="page" value="erp-accounting-sales">
        <input type="hidden" name="erp-action" value="ac-new-sales-payment">
        <?php
            erp_html_form_input( array(
                'name'        => 'id',
                'type'        => 'hidden',
                'value'       => $transaction_id
            ) );
        ?>

        <?php wp_nonce_field( 'erp-ac-trans-new' ); ?>

<!--         <?php
        if ( erp_ac_publish_sales_payment() ) {
            ?>
            <input type="submit" name="submit_erp_ac_trans" id="submit_erp_ac_trans" class="button button-primary" value="<?php _e( 'Receive Payment', 'erp' ); ?>">
            <?php
        }
        ?>
-->
        <input type="submit" name="submit_erp_ac_trans" style="display: none;">
        <input type="hidden" id="erp-ac-btn-status" name="btn_status" value="">
        <input type="hidden" id="erp-ac-redirect" name="redirect" value="0">


        <div class="erp-ac-btn-group-wrap">
             <div class="erp-button-bar-left">
                <div class="button-group erp-button-group">
                    <button  data-redirect="single_page" data-btn_status="payment" type="button" class="button button-primary erp-ac-trns-form-submit-btn">
                        <?php _e( 'Payment', 'erp' ); ?>
                    </button>
                    <button type="button" class="button button-primary erp-dropdown-toggle" data-toggle="erp-dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                    </button>
                    <ul class="erp-dropdown-menu">
                        <li><a class="erp-ac-trns-form-submit-btn" data-redirect="single_page" data-btn_status="payment" href="#"><?php _e( 'Payment', 'erp' ); ?></a></li>
                        <li><a class="erp-ac-trns-form-submit-btn" data-redirect="same_page" data-btn_status="payment_and_add_another" href="#"><?php _e( 'Payment & add another', 'erp' ); ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="erp-button-bar-right">


                <a href="<?php echo esc_url( $cancel_url ); ?>" class="button"><?php _e( 'Cancel', 'erp' ); ?></a>
            </div>
        </div>
    </form>

    <div class="erp-ac-receive-payment-table-clone"  id="erp-ac-hidden-new-payment" style="display: none;">

        <?php
        $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
            'name'     => 'line_account[]',
            'class'    => 'erp-select2'
        ) );

        $jor_itms = [];
        $hidden = false;
        include dirname( dirname( __FILE__ ) ) . '/common/transaction-table.php';?>
    </div>

</div>
