<?php $profile_user_id = isset( $_GET['id'] ) ? intval( wp_unslash( $_GET['id'] ) ) : null; ?>

<div class="info-form-wrap">
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

    <div class="row" data-selected="{{ ( ! data.type || data.type == 0 ) ? '-1' : data.type }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Location', 'erp' ),
            'name'     => 'type',
            'value'    => '{{ data.type }}',
            'type'     => 'select',
            'class'    => 'erp-hrm-select2',
            'options'  => [ 0 => __( '- Select -', 'erp' ) ] + erp_company_get_location_dropdown_raw(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.category }}">
        <?php erp_html_form_input( [
            'label'   => __( 'Department', 'erp' ),
            'name'    => 'category',
            'value'   => '{{ data.category }}',
            'type'    => 'select',
            'class'   => 'erp-hrm-select2',
            'options' => erp_hr_get_departments_dropdown_raw(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.comment }}">
        <?php erp_html_form_input( [
            'label'   => __( 'Job Title', 'erp' ),
            'name'    => 'comment',
            'value'   => '{{ data.comment }}',
            'type'    => 'select',
            'class'   => 'erp-hrm-select2',
            'options' => erp_hr_get_designation_dropdown_raw(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.data }}">
        <?php erp_html_form_input( [
            'label'   => __( 'Reporting To', 'erp' ),
            'name'    => 'data',
            'value'   => '{{ data.data }}',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_reporting_to',
            'options' => erp_hr_get_employees_dropdown_raw( $profile_user_id ),
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'wp-erp-hr-nonce' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp_hr_emp_update_job_history">
    <input type="hidden" name="history_id" id="history-id" value="{{ data.id }}">
    <input type="hidden" name="user_id" id="employee-id" value="{{ data.user_id }}">
    <input type="hidden" name="module" id="module" value="{{ data.module }}">
</div>
