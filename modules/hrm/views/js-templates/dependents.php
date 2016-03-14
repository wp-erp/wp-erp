<div class="work-exp-form-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Name', 'wp-erp' ),
            'name'     => 'name',
            'value'    => '{{ data.name }}',
            'required' => true,
            'placeholder' => __( 'Name of the person', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Relationship', 'wp-erp' ),
            'name'        => 'relation',
            'value'       => '{{ data.relation }}',
            'required'    => true,
            'placeholder' => __( 'Father', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Date of Birth', 'wp-erp' ),
            'name'        => 'dob',
            'value'       => '{{ data.dob }}',
            'class'       => 'erp-date-field',
            'placeholder' => __( '1970-01-20', 'wp-erp' )
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'erp-hr-dependent-form' ); ?>

    <input type="hidden" name="action" value="erp-hr-create-dependent">
    <input type="hidden" name="dep_id" value="{{ data.id }}">
    <input type="hidden" name="employee_id" value="{{ data.employee_id }}">
</div>