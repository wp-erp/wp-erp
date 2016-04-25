<?php $employee_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : null; ?>

<div class="performance-form-wrap">
    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Reference Date', 'erp' ),
            'name'     => 'performance_date',
            'value'    => date( 'Y-m-d', current_time( 'timestamp' ) ),
            'required' => true,
            'class'    => 'erp-date-field'
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Reviewer', 'erp' ),
            'name'    => 'reviewer',
            'value'   => '',
            'class'   => 'erp-hrm-select2',
            'type'    => 'select',
            'id'      => 'performance_reviewer',
            'options' => erp_hr_get_employees_dropdown_raw( $employee_id )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'   => __( 'Comments', 'erp' ),
            'name'    => 'comments',
            'value'   => '',
            'type'    => 'textarea',
            'id'      => 'performance_comments',
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'employee_update_performance' ); ?>
    <input type="hidden" name="type" value="comments">
    <input type="hidden" name="action" id="performance-comments-action" value="erp-hr-emp-update-performance-comments">
    <input type="hidden" name="employee_id" id="emp-id" value="{{ data.id }}">
</div>