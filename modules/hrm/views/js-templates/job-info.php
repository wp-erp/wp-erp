<?php $profile_user_id = isset($_GET['id']) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : null; ?>
<div class="info-form-wrap">
    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Date', 'erp' ),
            'name'     => 'date',
            'value'    => date( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field'
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Location', 'erp' ),
            'name'     => 'location',
            'value'    => '',
            'type'    => 'select',
            'options'  => array( 0 => __( '- Select -', 'erp' ) ) + erp_company_get_location_dropdown_raw()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Department', 'erp' ),
            'name'    => 'department',
            'value'   => '',
            'type'    => 'select',
            'options' => erp_hr_get_departments_dropdown_raw()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Job Title', 'erp' ),
            'name'    => 'designation',
            'value'   => '',
            'type'    => 'select',
            'options' => erp_hr_get_designation_dropdown_raw()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Reporting To', 'erp' ),
            'name'    => 'reporting_to',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_reporting_to',
            'options' => erp_hr_get_employees_dropdown_raw($profile_user_id)
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_jobinfo' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp-hr-emp-update-jobinfo">
    <input type="hidden" name="user_id" id="emp-id" value="{{ data.user_id }}">
</div>
