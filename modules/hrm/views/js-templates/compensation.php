<div class="compensation-form-wrap">
    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Date', 'wp-erp' ),
            'name'     => 'date',
            'value'    => date( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field'
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Pay Rate', 'wp-erp' ),
            'name'     => 'pay_rate',
            'value'    => '{{ data.work.pay_rate }}',
            'required' => true,
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Pay Type', 'wp-erp' ),
            'name'    => 'pay_type',
            'value'   => '',
            'type'    => 'select',
            'options' => array( 0 => __( '- Select -', 'wp-erp' ) ) + erp_hr_get_pay_type()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Change Reason', 'wp-erp' ),
            'name'    => 'change-reason',
            'value'   => '',
            'type'    => 'select',
            'options' => array( 0 => __( '- Select -', 'wp-erp' ) ) + erp_hr_get_pay_change_reasons()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Comment', 'wp-erp' ),
            'name'        => 'comment',
            'value'       => '',
            'placeholder' => __( 'Optional comment', 'wp-erp' ),
            'type'        => 'textarea',
            'custom_attr' => array( 'rows' => 4, 'cols' => 25 )
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_compensation' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp-hr-emp-update-comp">
    <input type="hidden" name="employee_id" id="emp-id" value="{{ data.id }}">
</div>