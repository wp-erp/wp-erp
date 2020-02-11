<?php
$employee_types = erp_hr_get_assign_policy_from_entitlement( get_current_user_id() );
$types = $employee_types ? $employee_types : [];
?>
<div class="erp-hr-leave-request-new">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Leave Type', 'erp' ),
            'name'     => 'leave_policy',
            'id'       => 'erp-hr-leave-req-leave-policy',
            'value'    => '',
            'required' => true,
            'type'     => 'select',
            'options'  => array( '' => __( '- Select -', 'erp' ) ) + $types
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'From', 'erp' ),
            'name'     => 'leave_from',
            'id'       => 'erp-hr-leave-req-from-date',
            'value'    => '',
            'required' => true,
            'class'    => 'erp-leave-date-field',
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'To', 'erp' ),
            'name'     => 'leave_to',
            'id'       => 'erp-hr-leave-req-to-date',
            'value'    => '',
            'required' => true,
            'class'    => 'erp-leave-date-field',
        ) ); ?>
    </div>

    <div class="erp-hr-leave-req-show-days show-days" style="margin:20px 0px;"></div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Reason', 'erp' ),
            'name'        => 'leave_reason',
            'type'        => 'textarea',
            'required'    => true,
            'custom_attr' => array( 'cols' => 25, 'rows' => 3 )
        ) ); ?>
    </div>

    <div class="row">
        <label for="leave_document"><?php echo esc_html__( 'Document', 'wp-erp' );?></label>
        <input type="file" name="leave_document[]" id="leave_document" multiple>
    </div>

    <input type="hidden" name="employee_id" id="erp-hr-leave-req-employee-id" value="<?php echo esc_html( get_current_user_id() ); ?>">
    <input type="hidden" name="action" value="erp-hr-leave-req-new">
    <?php wp_nonce_field( 'erp-leave-req-new' ); ?>
</div>
