<ul class="form-fields erp-list no-style">

    <li class="erp-form-field">
        <?php
        erp_html_form_input( array(
            'label'       => __( 'Memo', 'accounting' ),
            'name'        => 'summary',
            'placeholder' => __( 'Internal information', 'accounting' ),
            'type'        => 'textarea',
            'value'       => isset( $transaction['summary'] ) ? $transaction['summary'] : '',
            'custom_attr' => [
                'rows' => 3,
                'cols' => 45
            ]
        ) );
        ?>
    </li>

    <li class="erp-form-field">
        <?php
        erp_html_form_input( array(
            'label'       => __( 'Attachment', 'accounting' ),
            'name'        => 'file',
            'id'          => 'erp-ac-upload-file',
            'type'        => 'file',
            'custom_attr' => [ 'transaction_id' => isset( $transaction['id'] ) ? intval( $transaction['id'] ) : 0 ],
            //'callback'    => [ 'after_uploaded' => 'test.afterImageUpload' ],
            'value'       => isset( $transaction['files'] ) ?  maybe_unserialize( $transaction['files'] ) : [],
        ) );
        ?>
    </li>
</ul>
