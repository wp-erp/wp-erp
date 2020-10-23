<div class="work-exp-form-wrap">

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Previous Company', 'erp' ),
            'name'        => 'company_name',
            'value'       => '{{ data.company_name }}',
            'required'    => true,
            'placeholder' => __( 'ABC Corporation', 'erp' ),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Job Title', 'erp' ),
            'name'        => 'job_title',
            'value'       => '{{ data.job_title }}',
            'required'    => true,
            'placeholder' => __( 'Project Manager', 'erp' ),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'From', 'erp' ),
            'name'        => 'from',
            'value'       => '{{ data.from }}',
            'required'    => true,
            'class'       => 'erp-date-field',
            'placeholder' => '1988-03-18',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'To', 'erp' ),
            'name'        => 'to',
            'value'       => '{{ data.to }}',
            'required'    => true,
            'class'       => 'erp-date-field',
            'placeholder' => '1988-03-18',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Job Description', 'erp' ),
            'name'        => 'description',
            'type'        => 'textarea',
            'value'       => '{{ data.description }}',
            'placeholder' => __( 'Details about the job', 'erp' ),
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'erp-work-exp-form' ); ?>

    <input type="hidden" name="action" value="erp-hr-create-work-exp">
    <input type="hidden" name="exp_id" value="{{ data.id }}">
    <input type="hidden" name="employee_id" value="{{ data.employee_id }}">
</div>