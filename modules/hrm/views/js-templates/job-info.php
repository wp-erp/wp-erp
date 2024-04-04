<?php $profile_user_id = isset( $_GET['id'] ) ? intval( wp_unslash( $_GET['id'] ) ) : null; ?>

<div class="info-form-wrap">
    <div class="row">
        <?php erp_html_form_input( [
            'label'    => __( 'Date', 'erp' ),
            'name'     => 'date',
            'value'    => gmdate( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field',
            'custom_attr' => [
                'autocomplete' => 'off',
            ],
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.location == 0 ? '-1' : data.work.location }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Location', 'erp' ),
            'name'     => 'location',
            'value'    => '{{ data.work.location }}',
            'type'     => 'select',
            'class'    => 'erp-hrm-select2',
            'options'  => [ 0 => __( '- Select -', 'erp' ) ] + erp_company_get_location_dropdown_raw(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.department }}">
        <?php erp_html_form_input( [
            'label'   => __( 'Department', 'erp' ),
            'name'    => 'department',
            'value'   => '{{ data.work.department }}',
            'type'    => 'select',
            'class'   => 'erp-hrm-select2',
            'options' => erp_hr_get_departments_dropdown_raw(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.designation }}">
        <?php erp_html_form_input( [
            'label'   => __( 'Job Title', 'erp' ),
            'name'    => 'designation',
            'value'   => '{{ data.work.designation }}',
            'type'    => 'select',
            'class'   => 'erp-hrm-select2',
            'options' => erp_hr_get_designation_dropdown_raw(),
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.reporting_to }}">
        <?php erp_html_form_input( [
            'label'   => __( 'Reporting To', 'erp' ),
            'name'    => 'reporting_to',
            'value'   => '{{ data.work.reporting_to }}',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_reporting_to',
            'options' => erp_hr_get_employees_dropdown_raw( $profile_user_id ),
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_jobinfo' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp-hr-emp-update-jobinfo">
    <input type="hidden" name="user_id" id="emp-id" value="{{ data.user_id }}">
</div>
