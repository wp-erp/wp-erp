<div class="holiday-form-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Holiday Name', 'wp-erp' ),
            'name'     => 'name',
            'value'    => '{{ data.name }}',
            'required' => true,
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Start Date', 'wp-erp' ),
            'name'     => 'start_date',
            'value'    => '{{ data.value }}',
            'required' => true,
            'class'    => 'erp-date-field',
            'help'     => __( 'Days in a calendar year.', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'End Date', 'wp-erp' ),
            'name'     => 'end_date',
            'value'    => '{{ data.value }}',
            'required' => true,
            'class'    => 'erp-date-field',
            'help'     => __( 'Days in a calendar year.', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'type'     => 'textarea',
            'label'    => __( 'Description', 'wp-erp' ),
            'name'     => 'description',
            'value'    => '{{ data.color }}',
            'required' => true,
            'class'    => 'erp-hr-leave-holiday-description'
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'erp-leave-holiday' ); ?>
    <input type="hidden" name="action" value="erp-hr-leave-holiday-create">
    <input type="hidden" name="holiday-id" value="{{ data.id }}">
</div>