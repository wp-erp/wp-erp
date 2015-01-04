<div class="erp-employee-form">

    <fieldset class="no-border">
        <ol class="form-fields">
            <li>
                <?php erp_html_form_label( __( 'Employee Photo', 'wp-erp' ), 'full-name' ); ?>

                <div class="photo-container">
                    <input type="hidden" name="photo_id" id="emp-photo-id" value="0">
                    <a href="#" id="erp-set-emp-photo" class="button button-small"><?php _e( 'Upload Employee Photo', 'wp-erp' ); ?></a>
                </div>
            </li>

            <li class="full-width name-container clearfix">
                <?php erp_html_form_label( __( 'Full Name', 'wp-erp' ), 'full-name', true ); ?>

                <ol class="fields-inline">
                    <li>
                        <?php erp_html_form_input( array(
                            'label'       => __( 'First Name', 'wp-erp' ),
                            'name'        => 'name[first_name]',
                            'id'          => 'first_name',
                            'value'       => '',
                            'required'    => true,
                            'custom_attr' => array( 'maxlength' => 30 )
                        ) ); ?>
                    </li>
                    <li class="middle-name">
                        <?php erp_html_form_input( array(
                            'label'       => __( 'Middle Name', 'wp-erp' ),
                            'name'        => 'name[middle_name]',
                            'id'          => 'middle_name',
                            'value'       => '',
                            'custom_attr' => array( 'maxlength' => 30 )
                        ) ); ?>
                    </li>
                    <li>
                        <?php erp_html_form_input( array(
                            'label'       => __( 'Last Name', 'wp-erp' ),
                            'name'        => 'name[last_name]',
                            'id'          => 'last_name',
                            'value'       => '',
                            'required'    => true,
                            'custom_attr' => array( 'maxlength' => 30 )
                        ) ); ?>
                    </li>
                </ol>
            </li>
        </ol>

        <ol class="form-fields two-col">
            <li>
                <?php erp_html_form_input( array(
                    'label' => __( 'Employee ID', 'wp-erp' ),
                    'name'  => 'employee_id',
                    'value' => ''
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'    => __( 'Email', 'wp-erp' ),
                    'name'     => 'user_email',
                    'value'    => '',
                    'required' => true
                ) ); ?>
            </li>
        </ol>
    </fieldset>

    <fieldset>
        <legend><?php _e( 'Work', 'wp-erp' ) ?></legend>

        <ol class="form-fields two-col">
            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Department', 'wp-erp' ),
                    'name'    => 'department',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => erp_hr_get_departments_dropdown_raw( erp_get_current_company_id() )
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Job Title', 'wp-erp' ),
                    'name'    => 'job_title',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => erp_hr_get_designation_dropdown_raw( erp_get_current_company_id() )
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Reporting To', 'wp-erp' ),
                    'name'    => 'reporting_to',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => array( 0 => '- Select -' )
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Joining Date', 'wp-erp' ),
                    'name'    => 'joining_date',
                    'value'   => '',
                    'type'    => 'text',
                    'class'   => 'erp-date-field'
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Source of Hire', 'wp-erp' ),
                    'name'    => 'hiring_source',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => array_merge( array( 0 => __( '- Select -', 'wp-erp' ) ), erp_hr_get_employee_sources() )
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Employee Status', 'wp-erp' ),
                    'name'    => 'employee_status',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => array_merge( array( 0 => __( '- Select -', 'wp-erp' ) ), erp_hr_get_employee_statuses() )
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Work Phone', 'wp-erp' ),
                    'name'    => 'work_phone',
                    'value'   => ''
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Employee Type', 'wp-erp' ),
                    'name'    => 'employee_type',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => array_merge( array( 0 => __( '- Select -', 'wp-erp' ) ), erp_hr_get_employee_types() )
                ) ); ?>
            </li>
        </ol>
    </fieldset>

    <fieldset>
        <legend><?php _e( 'Personal Details', 'wp-erp' ) ?></legend>

        <ol class="form-fields two-col">
            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Mobile', 'wp-erp' ),
                    'name'    => 'mobile',
                    'value'   => ''
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Address', 'wp-erp' ),
                    'name'    => 'address',
                    'value'   => ''
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Other Email', 'wp-erp' ),
                    'name'    => 'other_email',
                    'value'   => ''
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Date of Birth', 'wp-erp' ),
                    'name'    => 'dob',
                    'value'   => ''
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Gender', 'wp-erp' ),
                    'name'    => 'gender',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => erp_hr_get_genders()
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Nationality', 'wp-erp' ),
                    'name'    => 'nationality',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => \WeDevs\ERP\Countries::instance()->get_countries()
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Marital Status', 'wp-erp' ),
                    'name'    => 'marital_status',
                    'value'   => '',
                    'type'    => 'select',
                    'options' => erp_hr_get_marital_statuses()
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Driving License', 'wp-erp' ),
                    'name'    => 'driving_license',
                    'value'   => ''
                ) ); ?>
            </li>

            <li>
                <?php erp_html_form_input( array(
                    'label'   => __( 'Hobbies', 'wp-erp' ),
                    'name'    => 'hobbies',
                    'value'   => ''
                ) ); ?>
            </li>

        </ol>
    </fieldset>

    <?php do_action( 'erp_hr_employee_form' ); ?>
</div>