<?php $user_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null; ?>

<div class="terminate-form-wrap">
    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Termination Date', 'erp' ),
            'name'        => 'terminate_date',
            'value'       => '{{ data.date }}',
            'required'    => true,
            'custom_attr' => [ 'autocomplete' => 'off' ],
            'class'       => 'erp-date-field',
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.termination_type }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Termination Type', 'erp' ),
            'name'     => 'termination_type',
            'value'    => '',
            'class'    => 'erp-hrm-select2',
            'type'     => 'select',
            'required' => true,
            'id'       => 'termination_type',
            'options'  => [ '' => __( '- Select -', 'erp' ) ] + erp_hr_get_terminate_type(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.termination_reason }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Termination Reason', 'erp' ),
            'name'     => 'termination_reason',
            'value'    => '',
            'class'    => 'erp-hrm-select2',
            'required' => true,
            'type'     => 'select',
            'id'       => 'termination_reason',
            'options'  => [ '' => __( '- Select -', 'erp' ) ] + erp_hr_get_terminate_reason(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.eligible_for_rehire }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Eligible for Rehire', 'erp' ),
            'name'     => 'eligible_for_rehire',
            'value'    => '',
            'class'    => 'erp-hrm-select2',
            'required' => true,
            'type'     => 'select',
            'id'       => 'eligible_for_rehire',
            'options'  => [ '' => __( '- Select -', 'erp' ) ] + erp_hr_get_terminate_rehire_options(),
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_terminate' ); ?>
    <input type="hidden" name="action" id="employee-terminate-action" value="erp-hr-emp-update-terminate-reason">
    <input type="hidden" name="user_id" id="emp-id" value="<?php echo esc_attr( $user_id ); ?>">
</div>
