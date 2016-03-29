<div class="work-exp-form-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Name', 'erp' ),
            'name'     => 'name',
            'value'    => '{{ data.name }}',
            'required' => true,
            'placeholder' => __( 'Name of the person', 'erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Relationship', 'erp' ),
            'name'        => 'relation',
            'value'       => '{{ data.relation }}',
            'required'    => true,
            'placeholder' => __( 'Father', 'erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Date of Birth', 'erp' ),
            'name'        => 'dob',
            'value'       => '{{ data.dob }}',
            'class'       => 'erp-date-field',
            'placeholder' => '1988-03-18'
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'erp-hr-dependent-form' ); ?>

    <input type="hidden" name="action" value="erp-hr-create-dependent">
    <input type="hidden" name="dep_id" value="{{ data.id }}">
    <input type="hidden" name="employee_id" value="{{ data.employee_id }}">
</div>