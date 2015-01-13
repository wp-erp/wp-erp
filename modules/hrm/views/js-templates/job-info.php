<?php $employee_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null; ?>

<div class="info-form-wrap">
    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Date', 'wp-erp' ),
            'name'     => 'date',
            'value'    => date( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field'
        ) ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.location }}">
        <?php erp_html_form_input( array(
            'label'    => __( 'Location', 'wp-erp' ),
            'name'     => 'location',
            'value'    => '',
            'type'    => 'select',
            'options'  => array_merge( array( 0 => __( '- Select -', 'wp-erp' ) ), array() )
        ) ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.department }}">
        <?php erp_html_form_input( array(
            'label'   => __( 'Department', 'wp-erp' ),
            'name'    => 'department',
            'value'   => '',
            'type'    => 'select',
            'options' => erp_hr_get_departments_dropdown_raw( erp_get_current_company_id() )
        ) ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.designation }}">
        <?php erp_html_form_input( array(
            'label'   => __( 'Job Title', 'wp-erp' ),
            'name'    => 'designation',
            'value'   => '',
            'type'    => 'select',
            'options' => erp_hr_get_designation_dropdown_raw( erp_get_current_company_id() )
        ) ); ?>
    </div>

    <div class="row" data-selected="{{ data.work.reporting_to }}">
        <?php erp_html_form_input( array(
            'label'   => __( 'Reporting To', 'wp-erp' ),
            'name'    => 'reporting_to',
            'value'   => '',
            'type'    => 'select',
            'options' => erp_hr_get_employees_dropdown_raw( erp_get_current_company_id(), $employee_id )
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_jobinfo' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp-hr-emp-update-jobinfo">
    <input type="hidden" name="employee_id" id="emp-id" value="{{ data.id }}">
</div>