<div class="invoice-send-email-popup">

    <div class="row row-email-to">
        <?php erp_html_form_input( array(
            'label'       => __( 'To', 'erp' ),
            'name'        => 'email-to[]',
            'value'       => '{{data.receiver}}',
            'id'          => 'erp-ac-email-to',
            'type'        => 'email',
            'required'    => true,
        ) ); ?>
        &nbsp;
        <a class="invoice-email-new-receiver" title="<?php _e( 'Add Another Recipient', 'erp' ); ?>" style="cursor:pointer"><i class="fa fa-plus fa-lg"></i></a>
    </div>

    <div class="row subject">
        <?php erp_html_form_input( array(
            'label'       => __( 'Subject', 'erp' ),
            'name'        => 'email-subject',
            'value'       => '{{data.subject}}',
            'id'          => 'erp-ac-email-subject',
            'required'    => true,
//            'custom_attr' => ['disabled' => true]
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Message', 'erp' ),
            'name'        => 'email-body',
            'id'          => 'erp-ac-email-body',
            'type'        => 'textarea',
            'placeholder' => 'Enter your message...',
            'required'    => true
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Attachment', 'erp' ),
            'name'        => 'attachment',
            'value'       => 'on',
            'id'          => 'erp-ac-email-body',
            'type'        => 'checkbox',
            'help'        => __( 'Attach the invoice as PDF', 'erp' )
        ) ); ?>
    </div>
</div>

<input type="hidden" name="action" value="erp-ac-invoice-send-email">
<input type="hidden" name="type" value="{{data.type}}">
<input type="hidden" name="transaction_id" value="{{data.transactionId}}">
<?php wp_nonce_field( 'erp-ac-invoice-send-email' ) ?>