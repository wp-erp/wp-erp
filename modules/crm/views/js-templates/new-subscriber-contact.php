<div class="erp-crm-contact-group-wrap">

    <# console.log( data ) #>

    <div class="row" data-selected = "'{{ data.user_id }}'">
        <?php erp_html_form_input( array(
            'label'       => __( 'Contact', 'wp-erp' ),
            'name'        => 'user_id',
            'type'        => 'select',
            'class'        => 'select2',
            'id'          => 'erp-crm-contact-subscriber-user',
            'required'    => true,
            'options'     => erp_crm_get_contact_dropdown( [ '' => __( '--Select a contact--', 'wp-erp' ) ] )
        ) ); ?>
    </div>

    <div class="row" data-selected = "'{{ data.user_id }}'">
        <?php erp_html_form_input( array(
            'label'       => __( 'Assign Group', 'wp-erp' ),
            'name'        => 'group_id[]',
            'type'        => 'multicheckbox',
            'id'          => 'erp-crm-contact-group-id',
            'options'     => erp_crm_get_contact_group_dropdown(),
        ) ); ?>
    </div>

<!--     <div class="row">
        <label for="erp-crm-contact-group-id">Assign Group</label>

        <input type="checkbox" name="">
    </div> -->

    <?php wp_nonce_field( 'wp-erp-crm-contact-subscriber' ); ?>

    <input type="hidden" name="action" value="erp-crm-contact-subscriber">
    <input type="hidden" name="id" value="{{ data.id }}">
</div>