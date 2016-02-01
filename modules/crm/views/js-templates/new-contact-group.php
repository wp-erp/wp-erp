<div class="erp-crm-contact-group-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Name', 'wp-erp' ),
            'name'        => 'group_name',
            'type'        => 'text',
            'id'          => 'erp-crm-contact-group-name',
            'required'    => true,
            'value'       => '{{ data.group_name }}'
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Description', 'wp-erp' ),
            'name'        => 'group_description',
            'type'        => 'textarea',
            'id'          => 'erp-crm-contact-group-description',
            'value'       => '{{ data.group_name }}',
            'placeholder' => __( 'Optional', 'wp-erp' )
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'wp-erp-crm-contact-group' ); ?>

    <input type="hidden" name="action" value="erp-crm-contact-group">
    <input type="hidden" name="id" value="{{ data.id }}">
</div>