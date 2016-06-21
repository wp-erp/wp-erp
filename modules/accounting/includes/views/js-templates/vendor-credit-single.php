<div class="erp-ac-transfer-money-js-temp-wrap">

    <div class="row">
            <?php
            erp_html_form_input( array(
                'label' => __( 'Reference', 'erp' ),
                'name'  => 'ref',
                'type'  => 'text',
                'addon' => '#',
                'value' => '{{ data.ref }}',
                'class' => 'erp-ac-reference-field'
            ) );
            ?>
    </div>
    <div class="row">
        <?php
            erp_html_form_input( array(
                'label'       => __( 'Invoice Number', 'erp' ),
                'name'        => 'invoice',
                'type'        => 'text',
                'required'    => true,
                'class'       => 'erp-ac-check-invoice-number',
                'custom_attr' => ['data-type' => 'vendor_credit'],
                'value'       => ''
            ) );
        ?>
    </div>
    <div class="row">
            <?php
            erp_html_form_input( array(
                'label'       => __( 'Payment Date', 'erp' ),
                'name'        => 'issue_date',
                'placeholder' => date( 'Y-m-d' ),
                'type'        => 'text',
                'required'    => true,
                'class'       => 'erp-date-field',
                'value'       => ''
            ) );
            ?>
    </div>
    <div class="row">
            <?php
                erp_html_form_input( array(
                    'label'       => __( 'From Account', 'erp' ),
                    'name'        => 'account_id',
                    'placeholder' => __( 'Select an Account', 'erp' ),
                    'type'        => 'select',
                    'class'       => 'erp-select2 erp-ac-bank-ac-drpdwn erp-ac-bank-ac-drpdwn-frm',
                    'value'       => isset( $main_ledger_id ) ? intval( $main_ledger_id ) : '',
                    'required'    => true,
                    'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_ac_get_bank_dropdown()
                ) );
            ?>
    </div>
    <div class="row">
            <?php
                erp_html_form_input( array(
                    'label'       => __( 'Due Amount', 'erp' ),
                   // 'name'        => 'line_total[]',
                    'type'        => 'text',
                    'custom_attr' => ['disabled' => 'disabled'],
                    'value'       => '{{ data.due_amount }}',
                ) );
            ?>
    </div>

    <div class="row">
            <?php
                erp_html_form_input( array(
                    'label'       => __( 'Payment Amount', 'erp' ),
                    'type'        => 'number',
                    'name'        => 'line_total[]',
                    'required'    => true,
                    'value'       => '',
                ) );
            ?>
    </div>

    <div class="row">
            <?php
            erp_html_form_input( array(
                'label'       => __( 'Memo', 'erp' ),
                'name'        => 'summary',
                'placeholder' => __( 'Internal information', 'erp' ),
                'type'        => 'textarea',
                'value'       => isset( $transaction['summary'] ) ? $transaction['summary'] : '',
                'custom_attr' => [
                    'rows' => 3,
                    'cols' => 45
                ]
            ) );
            ?>
    </div>

        <?php
            erp_html_form_input( array(
                'name'        => 'user_id',
                'type'        => 'hidden',
                'value'       => '{{ data.customer_id }}',
            ) );
        ?>

        <?php
            erp_html_form_input( array(
                'name'        => 'action',
                'type'        => 'hidden',
                'value'       => 'erp_ac_vendoer_credit_payment',
            ) );
        ?>



        <?php
            erp_html_form_input( array(
                'name'        => 'partial_id[]',
                'type'        => 'hidden',
                'value'       => '{{ data.partial_id }}',
            ) );
        ?>

    <div class="row">
            <?php
            erp_html_form_input( array(
                'label'       => __( 'Attachment', 'erp' ),
                'name'        => 'file',
                'id'          => 'erp-ac-upload-file',
                'type'        => 'file',
                'custom_attr' => [ 'transaction_id' => isset( $transaction['id'] ) ? intval( $transaction['id'] ) : 0 ],
                //'callback'    => [ 'after_uploaded' => 'test.afterImageUpload' ],
                'value'       => isset( $transaction['files'] ) ?  maybe_unserialize( $transaction['files'] ) : [],
            ) );
            ?>

    </div>

</div>
