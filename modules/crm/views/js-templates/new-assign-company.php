<div class="crm-customer-assing-company-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Company Name', 'wp-erp' ),
            'name'        => 'company_name',
            'type'        => 'select',
            'id'          => 'erp-select-customer-company',
            'class'       => 'erp-hrm-select2-add-more select2',
            'custom_attr' => array( 'data-id' => 'erp-contact-new', 'data-type' => 'company' ),
            'required'    => true,
            'options'     => [ '' => '--Select a Company--' ] + erp_get_peoples_array( [ 'type' => 'company', 'number' => -1 ] )
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'wp-erp-crm-assign-customer-company-nonce' ); ?>

    <input type="hidden" name="action" value="erp-add-customer-company">
    <input type="hidden" name="customer_id" value="{{ data.customer_id }}">
</div>