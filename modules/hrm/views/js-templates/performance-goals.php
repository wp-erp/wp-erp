<?php $employee_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null; ?>

<div class="performance-form-wrap">
    <div class="row">
        <?php erp_html_form_input( [
            'label'    => __( 'Set Date', 'erp' ),
            'name'     => 'performance_date',
            'value'    => gmdate( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'    => __( 'Completion Date', 'erp' ),
            'name'     => 'completion_date',
            'value'    => gmdate( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field',
            'id'       => 'performance_completion_date',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Goal Description', 'erp' ),
            'name'    => 'goal_description',
            'value'   => '',
            'type'    => 'textarea',
            'id'      => 'performance_goal_description',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Employee Assessment', 'erp' ),
            'name'    => 'employee_assessment',
            'value'   => '',
            'type'    => 'textarea',
            'id'      => 'performance_employee_assessment',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Supervisor', 'erp' ),
            'name'    => 'supervisor',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_supervisor',
            'options' => erp_hr_get_employees_dropdown_raw( $employee_id ),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'   => __( 'Supervisor Assessment', 'erp' ),
            'name'    => 'supervisor_assessment',
            'value'   => '',
            'type'    => 'textarea',
            'id'      => 'performance_supervisor_assessment',
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_performance' ); ?>
    <input type="hidden" name="type" value="goals">
    <input type="hidden" name="action" id="performance-goals-action" value="erp-hr-emp-update-performance-goals">
    <input type="hidden" name="employee_id" id="emp-id" value="{{ data.id }}">
</div>