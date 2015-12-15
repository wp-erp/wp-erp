<div class="crm-customer-assing-company-wrap">

    <# if ( data.type == 'assign_company' ) { #>

        <div class="row">
            <?php erp_html_form_input( array(
                'label'       => __( 'Company Name', 'wp-erp' ),
                'name'        => 'erp_assign_company_id',
                'type'        => 'select',
                'id'          => 'erp-select-customer-company',
                'class'       => 'erp-crm-select2-add-more erp-crm-customer-company-dropdown',
                'custom_attr' => ['data-id' => 'erp-contact-new', 'data-type' => 'company', 'data-single' => 1 ],
                'required'    => true,
                'options'     => [ '' => __( '--Select a Company--', 'wp-erp' ) ] + erp_get_peoples_array( [ 'type' => 'company', 'number' => -1 ] )
            ) ); ?>
        </div>

    <# } else if ( data.type == 'assign_customer' ) { #>

        <div class="row">
            <?php erp_html_form_input( array(
                'label'       => __( 'Customer Name', 'wp-erp' ),
                'name'        => 'erp_assign_customer_id',
                'type'        => 'select',
                'id'          => 'erp-select-customer-company',
                'class'       => 'erp-crm-select2-add-more erp-crm-customer-company-dropdown',
                'custom_attr' => ['data-id' => 'erp-contact-new', 'data-type' => 'customer', 'data-single' => 1 ],
                'required'    => true,
                'options'     => [ '' => __( '--Select a Customer--', 'wp-erp' ) ] + erp_get_peoples_array( [ 'type' => 'customer', 'number' => -1 ] )
            ) ); ?>
        </div>

    <# } #>

    <?php wp_nonce_field( 'wp-erp-crm-assign-customer-company-nonce' ); ?>

    <input type="hidden" name="action" value="erp-crm-customer-add-company">
    <input type="hidden" name="id" value="{{ data.id }}">
    <input type="hidden" name="assign_type" value="{{ data.type }}">
</div>