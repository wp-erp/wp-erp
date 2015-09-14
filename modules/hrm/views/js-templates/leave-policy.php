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
            'label'    => __( 'Description', 'wp-erp' ),
            'type'     => 'textarea',
            'name'     => 'description',
            'value'    => '{{ data.description }}',
            'placeholder' => __( '(optional)', 'wp-erp' ),
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Days', 'wp-erp' ),
            'name'     => 'days',
            'value'    => '{{ data.value }}',
            'required' => true,
            'help'     => __( 'Days in a calendar year.', 'wp-erp' ),
            'placeholder'     => 20
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Calendar Color', 'wp-erp' ),
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
            'options'     => erp_hr_get_departments_dropdown_raw( __( 'All Department', 'wp-erp' ) )
        ) ); ?>
    </div>

    <div class="row" data-selected="{{ data.designation }}">
        <?php erp_html_form_input( array(
            'label'       => __( 'Designation', 'wp-erp' ),
            'name'        => 'designation',
            'value'       => '{{ data.designation }}',
            'class'       => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
            'custom_attr' => array( 'data-id' => 'erp-new-designation' ),
            'type'        => 'select',
            'options'     => erp_hr_get_designation_dropdown_raw( __( 'All Designations', 'wp-erp' ) )
        ) ); ?>
    </div>

    <div class="row" data-selected="{{ data.designation }}">
        <?php erp_html_form_input( array(
            'label'   => __( 'Location', 'wp-erp' ),
            'name'    => 'location',
            'value'   => '{{ data.location }}',
            'type'    => 'select',
            'options' => erp_company_get_location_dropdown_raw( __( 'All Location', 'wp-erp' ) )
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
            'label'   => __( 'Marital Status', 'wp-erp' ),
            'name'    => 'maritial',
            'value'   => '{{ data.marital }}',
            'class'   => 'erp-hrm-select2-add-more erp-hr-desi-drop-down',
            'type'    => 'radio',
            'options' => array( '1' => __( 'All', 'wp-erp' ), '2' => __( 'Single', 'wp-erp' ), '3' => __( 'Married', 'wp-erp' ) )
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Effective Date', 'wp-erp' ),
            'name'     => 'effective_date',
            'value'    => '{{ data.effective_date }}',
            'class'    => 'erp-leave-date-field',
            'help'    => __( 'The date when the policy will be applicable from', 'wp-erp' )
        ) ); ?>
    </div>

    <div class="row" data-selected="{{ data.rate_transition }}">
        <?php erp_html_form_input( array(
            'label'   => __( 'Activate', 'wp-erp' ),
            'name'    => 'rateTransitions',
            'value'   => '{{ data.rate_transition }}',
            'class'   => 'erp-hrm-select2-add-more erp-hr-desi-drop-down erp-hr-leave-period',
            'type'    => 'select',
            'help'    => __( '', 'wp-erp' ),
            'options' => array( '1' => __( 'Immediately', 'wp-erp'), '2' => __('After X Days', 'wp-erp'), '3' => __( 'Manually', 'wp-erp') )
        ) ); ?>
    </div>

    <div class="row showifschedule erp-hide">
        <?php erp_html_form_input( array(
            'label'    => __( 'How many days', 'wp-erp' ),
            'name'     => 'no_of_days',
            'value'    => '',
            'required' => true,
            'help'     => __( 'No of days from hire', 'wp-erp' ),
            'placeholder' => 60
        ) ); ?>
    </div>

    <div class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'Apply', 'wp-erp' ),
            'name'     => 'apply',
            'type'     => 'checkbox',
            'help'    => __('Apply immediately for existing users', 'wp-erp')
        ) ); ?>
    </div>

    <?php wp_nonce_field( 'erp-leave-policy' ); ?>
    <input type="hidden" name="action" value="erp-hr-leave-policy-create">
    <input type="hidden" name="policy-id" value="{{ data.id }}">
</div>