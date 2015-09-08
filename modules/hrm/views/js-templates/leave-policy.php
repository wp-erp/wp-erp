<div class="policy-form-wrap">

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Policy Name', 'wp-erp' ),
            'name'     => 'name',
            'value'    => '{{ data.name }}',
            'required' => true,
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Days', 'wp-erp' ),
            'name'     => 'days',
            'value'    => '{{ data.value }}',
            'required' => true,
            'help'     => __( 'Days in a calendar year.', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Color', 'wp-erp' ),
            'name'     => 'color',
            'value'    => '{{ data.color }}',
            'required' => true,
            'class'    => 'erp-color-picker'
        ) ); ?>
    </div>
   <div class="row" data-selected="{{ data.department }}">
        <?php erp_html_form_input( array(
            'label'       => __( 'Department', 'wp-erp' ),
            'name'        => 'department',
            'value'       => '{{ data.department }}',
            'class'       => 'erp-hrm-select2-add-more erp-hr-dept-drop-down',
            'custom_attr' => array( 'data-id' => 'erp-new-dept' ),
            'type'        => 'select',
            'options'     => erp_hr_get_departments_dropdown_raw()
        ) ); ?>
    </div>
    <div class="row" data-selected="{{ data.designation }}">
        <?php erp_html_form_input( array(
            'label'       => __( 'Job Title', 'wp-erp' ),
            'name'        => 'designation',
            'value'       => '{{ data.designation }}',
            'class'       => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
            'custom_attr' => array( 'data-id' => 'erp-new-designation' ),
            'type'        => 'select',
            'options'     => erp_hr_get_designation_dropdown_raw()
        ) ); ?>
    </div>
    <div class="row" data-checked="{{ data.gender }}">
        <?php erp_html_form_input( array(
            'label'       => __( 'Gender', 'wp-erp' ),
            'name'        => 'gender',
            'value'       => '{{ data.gender }}',
            'type'        => 'radio',
            'options' => array( '1' => __('All', 'wp-erp'), '2' => __('Male', 'wp-erp'), '3' => __( 'Female', 'wp-erp' ) )
        ) ); ?>
    </div>
    <div class="row" data-checked="{{ data.marital }}">
        <?php erp_html_form_input( array(
            'label'   => __( 'Maritial Status', 'wp-erp' ),
            'name'    => 'maritial',
            'value'   => '{{ data.marital }}',
            'class'   => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
            'type'    => 'radio',
            'options' => array( '1' => __('All', 'wp-erp'), '2' => __('Single', 'wp-erp'), '3' => __( 'Married', 'wp-erp' ) )
        ) ); ?>
    </div>

    <div class="row" data-selected="{{ data.rate_transition }}">
        <?php erp_html_form_input( array(
            'label'   => __( 'Rate Transitions Happen', 'wp-erp' ),
            'name'    => 'rateTransitions',
            'value'   => '{{ data.rate_transition }}',
            'class'   => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
            'type'    => 'select',
            'help'    => __( 'New rate will apply immediately when the employee is eligible', 'wp-erp' ),
            'options' => array( '1' => __('Immediately', 'wp-erp'), '2' => __('At the end of each period', 'wp-erp') )
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'erp-leave-policy' ); ?>
    <input type="hidden" name="action" value="erp-hr-leave-policy-create">
    <input type="hidden" name="policy-id" value="{{ data.id }}">
</div>