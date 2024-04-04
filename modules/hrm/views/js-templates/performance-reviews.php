<?php $employee_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null; ?>

<div class="performance-form-wrap">
    <div class="row">
        <?php erp_html_form_input( [
            'label'    => __( 'Review Date', 'erp' ),
            'name'     => 'performance_date',
            'value'    => gmdate( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Reporting To', 'erp' ),
            'name'    => 'reporting_to',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_reporting_to',
            'options' => erp_hr_get_employees_dropdown_raw( $employee_id ),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Job Knowledge', 'erp' ),
            'name'    => 'job_knowledge',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_job_knowledge',
            'options' => [ 0 => __( '- Select -', 'erp' ) ] + erp_performance_rating(),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Work Quality', 'erp' ),
            'name'    => 'work_quality',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_work_quality',
            'options' => [ 0 => __( '- Select -', 'erp' ) ] + erp_performance_rating(),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Attendance/Punctuality', 'erp' ),
            'name'    => 'attendance',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_attendance',
            'options' => [ 0 => __( '- Select -', 'erp' ) ] + erp_performance_rating(),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Communication/Listening', 'erp' ),
            'name'    => 'communication',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_communication',
            'options' => [ 0 => __( '- Select -', 'erp' ) ] + erp_performance_rating(),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Dependability', 'erp' ),
            'name'    => 'dependablity',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_dependablity',
            'options' => [ 0 => __( '- Select -', 'erp' ) ] + erp_performance_rating(),
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_performance' ); ?>
    <input type="hidden" name="type" value="reviews">
    <input type="hidden" name="action" id="performance-reviews-action" value="erp-hr-emp-update-performance-reviews">
    <input type="hidden" name="employee_id" id="emp-id" value="{{ data.user_id }}">
</div>
