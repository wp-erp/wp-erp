<div class="work-exp-form-wrap">

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'School Name', 'erp' ),
            'name'        => 'school',
            'value'       => '{{ data.school }}',
            'required'    => true,
            'placeholder' => __( 'ABC School', 'erp' ),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Degree', 'erp' ),
            'name'        => 'degree',
            'value'       => '{{ data.degree }}',
            'required'    => true,
            'placeholder' => __( 'Bachelor in Science', 'erp' ),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Field of Study', 'erp' ),
            'name'        => 'field',
            'value'       => '{{ data.field }}',
            'required'    => true,
            'placeholder' => __( 'Physics', 'erp' ),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Year of Completion', 'erp' ),
            'name'        => 'finished',
            'type'        => 'number',
            'value'       => '{{ data.finished }}',
            'required'    => true,
            'placeholder' => date( 'Y' ),
            'custom_attr' => [
                'min'  => 1970,
                'max'  => 2099,
                'step' => 1,
            ],
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Result', 'erp' ),
            'name'        => 'result',
            'type'        => 'text',
            'value'       => '{{ data.result }}',
            'required'    => true,
            'placeholder' => '5.0',
            'custom_attr' => [
                'min'  => 0,
                'step' => '0.01'
            ],
        ] ); ?>
    </div>

    <div class="row" data-selected="{{ data.result_type }}">
        <?php erp_html_form_input( [
            'label'    => __( 'Result in', 'erp' ),
            'name'     => 'result_type',
            'value'    => '{{ data.result_type }}',
            'required' => true,
            'type'     => 'select',
            'id'       => 'result_type',
            'options'  => [ '' => __( '- Select -', 'erp' ) ] + erp_hr_get_education_result_type_options(),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Notes', 'erp' ),
            'name'        => 'notes',
            'type'        => 'textarea',
            'value'       => '{{ data.notes }}',
            'placeholder' => __( 'Additional notes', 'erp' ),
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Interests', 'erp' ),
            'name'        => 'interest',
            'type'        => 'textarea',
            'value'       => '{{ data.interest }}',
        ] ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( [
            'label'       => __( 'Expiration date', 'erp' ),
            'name'        => 'expiration_date',
            'type'        => 'text',
            'value'       => '{{ data.expiration_date }}',
            'class'       => 'erp-date-field',
        ] ); ?>
    </div>

    <?php wp_nonce_field( 'erp-hr-education-form' ); ?>

    <input type="hidden" name="action" value="erp-hr-create-education">
    <input type="hidden" name="edu_id" value="{{ data.id }}">
    <input type="hidden" name="employee_id" value="{{ data.employee_id }}">
</div>
