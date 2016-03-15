<div class="crm-customer-edit-company-wrap">

    <div class="row" data-selected="{{data.company_id}}">
        <?php erp_html_form_input( array(
            'label'       => __( 'Company Name', 'wp-erp' ),
            'name'        => 'company_id',
            'type'        => 'select',
            'id'          => 'erp-select-customer-company',
            'class'       => 'erp-crm-select2-add-more erp-crm-customer-company-dropdown',
            'custom_attr' => array( 'data-id' => 'erp-contact-new', 'data-type' => 'company' ),
            'required'    => true,
            'options'     => [ '' => __( '--Select a Company--', 'wp-erp' ) ] + erp_get_peoples_array( [ 'type' => 'company', 'number' => -1 ] )
        ) ); ?>
    </div>

    <input type="hidden" name="action" value="erp-crm-customer-update-company">
    <input type="hidden" name="row_id" value="{{ data.id }}">
    <?php wp_nonce_field( 'wp-erp-crm-customer-update-company-nonce' ); ?>

</div>