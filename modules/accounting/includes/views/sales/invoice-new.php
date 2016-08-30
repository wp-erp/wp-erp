<?php
$customer_id    = isset( $_GET['customer'] ) && $_GET['customer'] == 'true' ? intval( $_GET['id'] ) : false;
$transaction_id = isset( $_GET['transaction_id'] ) ? intval( $_GET['transaction_id'] ) : false;
$transaction    = [];
$jor_itms       = [];
$cancel_url     = erp_ac_get_sales_url();

if ( $transaction_id ) {
    $transaction = erp_ac_get_all_transaction([
        'id'        => $transaction_id,
        'form_type' => 'invoice',
        'status'    => [ 'in' => ['draft', 'pending', 'awaiting_payment'] ],
        'join'      => ['journals', 'items'],
        'type'      => ['sales'],
        'output_by' => 'array'
    ]);

    $transaction = reset( $transaction );
    $customer_id = isset( $transaction['user_id'] ) ? intval( $transaction['user_id'] ) : false;

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
$tax_labels    = erp_ac_get_trans_unit_tax_rate( $items_for_tax );
?>

<div class="wrap erp-ac-form-wrap">
    <h2><?php _e( 'New Invoice', 'erp' ); ?></h2>

    <?php
    $accounts_receivable_id = WeDevs\ERP\Accounting\Model\Ledger::code('120')->first()->id;

    $dropdown = erp_ac_get_chart_dropdown([
        'exclude'  => [1, 2, 3, 5],

    ] );

    $filter_option = reset( $dropdown );
    $options       = wp_list_pluck( $filter_option['options'], 'code' );
    $code_key      = array_search( '475', $options );

    unset( $dropdown[4]['options'][$code_key] );

    $dropdown_html = erp_ac_render_account_dropdown_html( $dropdown, array(
        'name'     => 'line_account[]',
        'selected' => isset( $journal['ledger_id'] ) ? $journal['ledger_id'] : false,
        'class'    => 'erp-select2'
    ) );
     ?>

    <form action="" method="post" class="erp-form erp-ac-transaction-form erp-ac-invoice-form">

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
                                'type'        => 'select',
                                'required'    => true,
                                'class'       => 'erp-select2 erp-ac-customer-drop erp-ac-not-found-in-drop',
                                'value'       => $customer_id ? $customer_id : '',
                                'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_get_peoples_array( ['type' => 'customer', 'number' => 100 ] ),
                                'custom_attr' => [
                                    'data-content' => 'erp-ac-new-customer-content-pop',
                                ],
                            ) );

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
                            'custom_attr' => [
                                'data-old_val' => isset( $transaction['invoice_number']  ) ? erp_ac_get_invoice_number( $transaction['invoice_number'], $transaction['invoice_format'] ) : erp_ac_get_auto_generated_invoice( 'invoice' ),
                            ],
                            'class'    => 'erp-ac-check-invoice-number',
                            'value'    => isset( $transaction['invoice_number']  ) ? erp_ac_get_invoice_number( $transaction['invoice_number'], $transaction['invoice_format'] ) : erp_ac_get_auto_generated_invoice( 'invoice' )
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
                            'label'       => __( 'Invoice Date', 'erp' ),
                            'name'        => 'issue_date',
                            'placeholder' => date( 'Y-m-d' ),
                            'type'        => 'text',
                            'required' => true,
                            'class'       => 'erp-date-field',
                            'value' => isset( $transaction['issue_date'] ) ? $transaction['issue_date'] : ''
                        ) );
                        ?>
                    </li>

                    <li class="erp-form-field">
                        <?php
                        erp_html_form_input( array(
                            'label'       => __( 'Due Date', 'erp' ),
                            'name'        => 'due_date',
                            'placeholder' => date( 'Y-m-d' ),
                            'type'        => 'text',
                            'required'    => true,
                            'class'       => 'erp-due-date-field',
                            'value' => isset( $transaction['due_date'] ) ? $transaction['due_date'] : ''
                        ) );
                        ?>
                    </li>
                </ul>
            </li>

            <li class="erp-form-field">
                <?php
                erp_html_form_input( array(
                    'label'       => __( 'Billing Address', 'erp' ),
                    'name'        => 'billing_address',
                    'placeholder' => '',
                    'type'        => 'textarea',
                    'custom_attr' => [
                        'rows' => 3,
                        'cols' => 30
                    ],
                    'value' => isset( $transaction['billing_address'] ) ? $transaction['billing_address'] : ''
                ) );
                ?>
            </li>

        </ul>

        <?php include dirname( dirname( __FILE__ ) ) . '/common/transaction-table.php'; ?>
        <?php include dirname( dirname( __FILE__ ) ) . '/common/memo.php'; ?>

        <input type="hidden" name="field_id" value="0">
        <input type="hidden" name="account_id" value="<?php echo $accounts_receivable_id; ?>">
        <input type="hidden" name="status" value="awaiting_payment">
        <input type="hidden" name="type" value="sales">
        <input type="hidden" name="form_type" value="invoice">
        <input type="hidden" name="page" value="erp-accounting-sales">
        <input type="hidden" name="erp-action" value="ac-new-invoice">

        <?php erp_html_form_input( array(
            'name'        => 'id',
            'type'        => 'hidden',
            'value'       => $transaction_id
        ) ); ?>

        <?php wp_nonce_field( 'erp-ac-trans-new' ); ?>

        <input type="submit" name="submit_erp_ac_trans" style="display: none;">
        <input type="hidden" id="erp-ac-btn-status" name="btn_status" value="">
        <input type="hidden" id="erp-ac-redirect" name="redirect" value="0">

        <div class="erp-ac-btn-group-wrap">

            <div class="erp-button-bar-left">
                <?php
                    if ( isset( $transaction['status'] ) && $transaction['status'] == 'pending' ) {
                        ?>
                        <button type="button" data-redirect="0" data-btn_status="save_and_submit_for_approval" class="button erp-ac-trns-form-submit-btn">
                            <?php _e( 'Save', 'erp' ); ?>
                        </button>
                        <?php
                    } else if ( isset( $transaction['status'] ) && $transaction['status'] == 'awaiting_payment' ) {

                    } else {
                        ?>
                        <div class="button-group erp-button-group">
                             <button type="button" data-redirect="0" data-btn_status="save_and_draft" class="button erp-ac-trns-form-submit-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php _e( 'Save as Draft', 'erp' ); ?>
                            </button>
                            <button type="button" class="button erp-dropdown-toggle" data-toggle="erp-dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="erp-dropdown-menu">
                                <li><a class="erp-ac-trns-form-submit-btn" data-redirect="0" data-btn_status="save_and_draft" href="#"><?php _e( 'Save as Draft', 'erp' ); ?></a></li>
                                <li><a class="erp-ac-trns-form-submit-btn" data-redirect="0" data-btn_status="save_and_submit_for_approval" href="#"><?php _e( 'Save & submit for approval', 'erp' ); ?></a></li>
                                <li><a class="erp-ac-trns-form-submit-btn" data-redirect="same_page" data-btn_status="save_and_add_another" href="#"><?php _e( 'Save & add another', 'erp' ); ?></a></li>
                            </ul>

                        </div>
                        <?php
                    }
                ?>
            </div>

            <div class="erp-button-bar-right">
                <?php
                if ( isset( $transaction['status'] ) && $transaction['status'] == 'awaiting_payment' ) {
                    ?>
                    <button  data-redirect="single_page" data-btn_status="approve" type="button" class="button button-primary erp-ac-trns-form-submit-btn">
                        <?php _e( 'Update', 'erp' ); ?>
                    </button>
                    <?php
                } else {
                    ?>
                    <div class="button-group erp-button-group">
                        <button  data-redirect="single_page" data-btn_status="approve" type="button" class="button button-primary erp-ac-trns-form-submit-btn">
                            <?php _e( 'Approve', 'erp' ); ?>
                        </button>
                        <button type="button" class="button button-primary erp-dropdown-toggle" data-toggle="erp-dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                        </button>
                        <ul class="erp-dropdown-menu">
                            <li><a class="erp-ac-trns-form-submit-btn" data-redirect="single_page" data-btn_status="approve" href="#"><?php _e( 'Approve', 'erp' ); ?></a></li>
                            <li><a class="erp-ac-trns-form-submit-btn" data-redirect="same_page" data-btn_status="approve_and_add_another" href="#"><?php _e( 'Approve & add another', 'erp' ); ?></a></li>
                        </ul>
                    </div>
                    <?php
                }
                ?>

                <a href="<?php echo esc_url( $cancel_url ); ?>" class="button"><?php _e( 'Cancel', 'erp' ); ?></a>
            </div>

        </div>

    </form>
</div>
