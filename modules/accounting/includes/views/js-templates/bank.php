<div class="erp-ac-transfer-money-js-temp-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Date', 'erp' ),
            'name'        => 'date',
            'id'          => 'erp-ac-date',
            'class'       => 'erp-date-field',
            'required'    => true,
        ) ); ?>
    </div>

    <div class="row">
        <?php

            erp_html_form_input( array(
                'label'       => __( 'From Account', 'erp' ),
                'name'        => 'form_account_id',
                'placeholder' => __( 'Select an Account', 'erp' ),
                'type'        => 'select',
                'class'       => 'erp-select2 erp-ac-bank-ac-drpdwn erp-ac-bank-ac-drpdwn-frm',
                'required'    => true,
                'value'       => '',
                'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_ac_get_bank_dropdown()
            ) );

         ?>
        <span class="balance-wrap-from">
            <strong><?php _e( 'Balance:', 'erp' ); ?> <span class="erp-ac-bank-amount">0</span></strong>
        </span>
    </div>


    <div class="row">
        <?php

            erp_html_form_input( array(
                'label'       => __( 'To Account', 'erp' ),
                'name'        => 'to_account_id',
                'placeholder' => __( 'Select an Account', 'erp' ),
                'type'        => 'select',
                'class'       => 'erp-select2 erp-ac-bank-ac-drpdwn erp-ac-bank-ac-drpdwn-to',
                'required'    => true,
                'value'       => '',
                'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_ac_get_bank_dropdown()
            ) );

         ?>

         <span class="balance-wrap-to">
            <strong><?php _e( 'Balance:', 'erp' ); ?> <span class="erp-ac-bank-amount">0</span></strong>
        </span>
    </div>



    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Amount', 'erp' ),
            'type'        => 'number',
            'name'        => 'amount',
            'id'          => 'erp-ac-amount',
            'value'       => '',
            'required'    => true,
            'custom_attr' => array( 'min' => '0' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Memo', 'erp' ),
            'name'        => 'memo',
            'type'        => 'textarea',
            'id'          => 'erp-ac-memo',
            'value'       => '',
        ) ); ?>
    </div>
        <?php erp_html_form_input( array(
            'name'        => 'action',
            'type'        => 'hidden',
            'id'          => 'erp-ac-memo',
            'value'       => 'ac_transfer_money',
        ) ); ?>

        <?php //wp_nonce_field( 'ac_transfer_money', 'ac_transfer_mone_nonce' ); ?>
</div>
