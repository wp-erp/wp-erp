<div class="leave-reject-form-wrap">
    <div id="leave-reject-form-error"></div>
    <div class="row">
        <?php erp_html_form_input( [
            'label'         => __( 'Reason', 'erp' ),
            'name'          => 'reason',
            'id'            => 'erp-hr-leave-reject-reason',
            'value'         => '',
            'type'          => 'textarea',
            'required'      => true,
            'custom_attr'   => [
                'rows' => 5,
                'cols' => 50,
            ],
        ] ); ?>
        <?php erp_html_form_input( [
            'type'     => 'hidden',
            'name'     => 'leave_request_id',
            'value'    => '{{ data.id }}',
        ] ); ?>
        <?php erp_html_form_input( [
            'name'     => 'action',
            'type'     => 'hidden',
            'value'    => 'erp_hr_leave_reject',
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'erp-leave-reject' ); ?>
    <input type="hidden" name="action" value="erp_hr_leave_reject">
</div>
