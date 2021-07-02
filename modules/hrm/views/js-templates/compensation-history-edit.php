<div class="compensation-form-wrap">
    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Date', 'erp' ),
            'name'        => 'date',
            'value'       => '{{ data.date }}',
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
            'name'     => 'type',
            'value'    => '{{ data.type }}',
            'required' => true,
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.category }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Pay Type', 'erp' ),
            'name'     => 'category',
            'value'    => '{{ data.category }}',
            'type'     => 'select',
            'required' => true,
            'class'    => 'erp-hrm-select2',
            'options'  => [ 0 => __( '- Select -', 'erp' ) ] + erp_hr_get_pay_type(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.data }}">
        <?php erp_html_form_input( [
            'label'   => __( 'Change Reason', 'erp' ),
            'name'    => 'data',
            'value'   => '{{ data.data }}',
            'type'    => 'select',
            'class'   => 'erp-hrm-select2',
            'options' => [ 0 => __( '- Select -', 'erp' ) ] + erp_hr_get_pay_change_reasons(),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Comment', 'erp' ),
            'name'        => 'comment',
            'value'       => '{{ data.comment }}',
            'placeholder' => __( 'Optional comment', 'erp' ),
            'type'        => 'textarea',
            'custom_attr' => [ 'rows' => 4, 'cols' => 25 ],
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'wp-erp-hr-nonce' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp_hr_emp_update_job_history">
    <input type="hidden" name="history_id" id="history-id" value="{{ data.id }}">
    <input type="hidden" name="user_id" id="employee-id" value="{{ data.user_id }}">
    <input type="hidden" name="module" id="module" value="{{ data.module }}">
</div>