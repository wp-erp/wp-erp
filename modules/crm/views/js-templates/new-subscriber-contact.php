<div class="erp-crm-contact-subscriber-wrap">

    <# if ( ! data.group_id ) { #>

        <div class="row" data-selected = "'{{ data.user_id }}'">
            <?php erp_html_form_input( array(
                'label'       => __( 'Contact', 'erp' ),
                'name'        => 'user_id',
                'type'        => 'select',
                'class'        => 'select2',
                'id'          => 'erp-crm-contact-subscriber-user',
                'required'    => true,
                'options'     => erp_crm_get_contact_dropdown( [ '' => __( '--Select a contact--', 'erp' ) ] )
            ) ); ?>
        </div>

    <# } #>

    <?php $contact_groups = erp_crm_get_contact_group_dropdown(); ?>

    <?php if( count( $contact_groups ) > 0 ) : ?>
        <div class="row" id="erp-crm-contact-subscriber-group-checkbox" data-checked = "{{ data.group_id }}">
            <?php erp_html_form_input( array(
                'label'       => __( 'Assign Group', 'erp' ),
                'name'        => 'group_id[]',
                'type'        => 'multicheckbox',
                'id'          => 'erp-crm-contact-group-id',
                'class'       => 'erp-crm-contact-group-class',
                'options'     => $contact_groups
            ) ); ?>
        </div>
    <?php else : ?>
        <p><?php echo sprintf( '%s <a href="%s">%s</a>', __( 'No group founds. Please add group first', 'erp' ), add_query_arg( [ 'page' => 'erp-sales-contact-groups' ], admin_url( 'admin.php' ) ), __( 'Add New Group', 'erp' ) ); ?></p>
    <?php endif; ?>

    <?php wp_nonce_field( 'wp-erp-crm-contact-subscriber' ); ?>

    <# if ( ! data.group_id ) { #>
        <input type="hidden" name="action" value="erp-crm-contact-subscriber">
    <# } else { #>
        <input type="hidden" name="user_id" value="{{ data.user_id }}">
        <input type="hidden" name="action" value="erp-crm-contact-subscriber-edit">
    <# } #>

</div>
