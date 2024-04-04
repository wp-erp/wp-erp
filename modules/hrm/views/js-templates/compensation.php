<div class="compensation-form-wrap">
    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Date', 'erp' ),
            'name'        => 'date',
            'value'       => gmdate( 'Y-m-d', current_time( 'timestamp' ) ),
            'required'    => true,
            'class'       => 'erp-date-field',
            'custom_attr' => [
                'autocomplete' => 'off',
            ],
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'    => __( 'Pay Rate', 'erp' ),
            'name'     => 'pay_rate',
            'value'    => '{{ data.work.pay_rate }}',
            'required' => true,
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.pay_type }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Pay Type', 'erp' ),
            'name'     => 'pay_type',
            'value'    => '{{ data.work.pay_type }}',
            'type'     => 'select',
            'required' => true,
            'class'    => 'erp-hrm-select2',
            'options'  => [ 0 => __( '- Select -', 'erp' ) ] + erp_hr_get_pay_type(),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Change Reason', 'erp' ),
            'name'    => 'change-reason',
            'value'   => '',
            'type'    => 'select',
            'class'   => 'erp-hrm-select2',
            'options' => [ 0 => __( '- Select -', 'erp' ) ] + erp_hr_get_pay_change_reasons(),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Comment', 'erp' ),
            'name'        => 'comment',
            'value'       => '',
            'placeholder' => __( 'Optional comment', 'erp' ),
            'type'        => 'textarea',
            'custom_attr' => [ 'rows' => 4, 'cols' => 25 ],
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_compensation' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp-hr-emp-update-comp">
    <input type="hidden" name="user_id" id="emp-id" value="{{ data.user_id }}">
</div>