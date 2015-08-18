<div class="holiday-form-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Holiday Name', 'wp-erp' ),
            'name'     => 'title',
            'value'    => '{{ data.title }}',
            'required' => true,
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Start Date', 'wp-erp' ),
            'name'     => 'start_date',
            'value'    => '{{ data.start_date }}',
            'required' => true,
            'class'    => 'erp-leave-date-picker-from',
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'End Date', 'wp-erp' ),
            'name'     => 'end_date',
            'value'    => '{{ data.end_date }}',
            'required' => true,
            'class'    => 'erp-leave-date-picker-to',
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'type'     => 'textarea',
            'label'    => __( 'Description', 'wp-erp' ),
            'name'     => 'description',
            'value'    => '{{ data.description }}',
            'required' => true,
            'class'    => 'erp-hr-leave-holiday-description'
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'erp-leave-holiday' ); ?>
    <input type="hidden" name="action" value="erp_hr_holiday_create">
    <input type="hidden" name="holiday_id" value="{{ data.id }}">
</div>