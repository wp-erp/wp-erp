<?php $employee_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null; ?>

<div class="performance-form-wrap">
    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Review Date', 'wp-erp' ),
            'name'     => 'performance_date',
            'value'    => date( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field'
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Reporting To', 'wp-erp' ),
            'name'    => 'reporting_to',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_reporting_to',
            'options' => erp_hr_get_employees_dropdown_raw( $employee_id )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Job Knowledge', 'wp-erp' ),
            'name'    => 'job_knowledge',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_job_knowledge',
            'options' => array( 0 => __( '- Select -', 'wp-erp' ) ) + erp_performance_rating()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Work Quality', 'wp-erp' ),
            'name'    => 'work_quality',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_work_quality',
            'options' => array( 0 => __( '- Select -', 'wp-erp' ) ) + erp_performance_rating()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Attendence/Punctuality', 'wp-erp' ),
            'name'    => 'attendance',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_attendance',
            'options' => array( 0 => __( '- Select -', 'wp-erp' ) ) + erp_performance_rating()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Communication/Listening', 'wp-erp' ),
            'name'    => 'communication',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_communication',
            'options' => array( 0 => __( '- Select -', 'wp-erp' ) ) + erp_performance_rating()
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Dependablity', 'wp-erp' ),
            'name'    => 'dependablity',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_dependablity',
            'options' => array( 0 => __( '- Select -', 'wp-erp' ) ) + erp_performance_rating()
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_performance' ); ?>
    <input type="hidden" name="type" value="reviews">
    <input type="hidden" name="action" id="performance-reviews-action" value="erp-hr-emp-update-performance-reviews">
    <input type="hidden" name="employee_id" id="emp-id" value="{{ data.id }}">
</div>