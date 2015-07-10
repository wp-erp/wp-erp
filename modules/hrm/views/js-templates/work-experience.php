<div class="work-exp-form-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Previous Company', 'wp-erp' ),
            'name'     => 'company_name',
            'value'    => '{{ data.company_name }}',
            'required' => true,
            'placeholder' => __( 'ABC Corporation', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Job Title', 'wp-erp' ),
            'name'        => 'job_title',
            'value'       => '{{ data.job_title }}',
            'required'    => true,
            'placeholder' => __( 'Project Manager', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'From', 'wp-erp' ),
            'name'        => 'from',
            'value'       => '{{ data.from }}',
            'required'    => true,
            'class'       => 'erp-date-field',
            'placeholder' => '2012-01-20'
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'To', 'wp-erp' ),
            'name'        => 'to',
            'value'       => '{{ data.to }}',
            'required'    => true,
            'class'       => 'erp-date-field',
            'placeholder' => '2014-09-12'
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'       => __( 'Job Description', 'wp-erp' ),
            'name'        => 'description',
            'type'        => 'textarea',
            'value'       => '{{ data.description }}',
            'placeholder' => __( 'Details about the job', 'wp-erp' )
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'erp-work-exp-form' ); ?>

    <input type="hidden" name="action" value="erp-hr-create-work-exp">
    <input type="hidden" name="exp_id" value="{{ data.id }}">
    <input type="hidden" name="employee_id" value="{{ data.employee_id }}">
</div>