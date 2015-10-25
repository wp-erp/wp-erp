<?php do_action( 'erp-hr-employee-single-top', $employee ); ?>

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Basic Info', 'wp-erp' ); ?></span></h3>
    <div class="inside">
        <ul class="erp-list two-col separated">
            <li><?php erp_print_key_value( __( 'First Name', 'wp-erp' ), $employee->first_name ); ?></li>
            <li><?php erp_print_key_value( __( 'Last Name', 'wp-erp' ), $employee->last_name ); ?></li>
            <li><?php erp_print_key_value( __( 'Employee ID', 'wp-erp' ), $employee->employee_id ); ?></li>
            <li><?php erp_print_key_value( __( 'Email', 'wp-erp' ), erp_get_clickable( 'email', $employee->user_email ) ); ?></li>

            <?php do_action( 'erp-hr-employee-single-basic', $employee ); ?>
        </ul>
    </div>
</div><!-- .postbox -->

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Work', 'wp-erp' ); ?></span></h3>
    <div class="inside">
        <ul class="erp-list two-col separated">
            <li><?php erp_print_key_value( __( 'Department', 'wp-erp' ), $employee->get_department_title() ); ?></li>
            <li><?php erp_print_key_value( __( 'Title', 'wp-erp' ), $employee->get_job_title() ); ?></li>

            <?php
            $reporting_to = $employee->get_reporting_to();
            $reporting_to = $reporting_to ? $reporting_to->get_link() : '-';
            ?>
            <li><?php erp_print_key_value( __( 'Reporting To', 'wp-erp' ), $reporting_to ); ?></li>
            <li><?php erp_print_key_value( __( 'Date of Hire', 'wp-erp' ), $employee->get_joined_date() ); ?></li>
            <li><?php erp_print_key_value( __( 'Source of Hire', 'wp-erp' ), $employee->get_hiring_source() ); ?></li>
            <li><?php erp_print_key_value( __( 'Employee Status', 'wp-erp' ), $employee->get_status() ); ?></li>
            <li><?php erp_print_key_value( __( 'Work Phone', 'wp-erp' ), erp_get_clickable( 'phone', $employee->get_phone( 'work' ) ) ); ?></li>
            <li><?php erp_print_key_value( __( 'Employee Type', 'wp-erp' ), $employee->get_type() ); ?></li>

            <?php do_action( 'erp-hr-employee-single-work', $employee ); ?>
        </ul>
    </div>
</div><!-- .postbox -->

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Personal Details', 'wp-erp' ); ?></span></h3>
    <div class="inside">
        <ul class="erp-list two-col separated">
            <li><?php erp_print_key_value( __( 'Mobile', 'wp-erp' ), erp_get_clickable( 'phone', $employee->get_phone( 'personal' ) ) ); ?></li>
            <li><?php erp_print_key_value( __( 'Address', 'wp-erp' ), $employee->address ); ?></li>
            <li><?php erp_print_key_value( __( 'Other Email', 'wp-erp' ), erp_get_clickable( 'email', $employee->other_email ) ); ?></li>
            <li><?php erp_print_key_value( __( 'Date of Birth', 'wp-erp' ), $employee->get_birthday() ); ?></li>
            <li><?php erp_print_key_value( __( 'Gender', 'wp-erp' ), $employee->get_gender() ); ?></li>
            <li><?php erp_print_key_value( __( 'Nationality', 'wp-erp' ), $employee->get_nationality() ); ?></li>
            <li><?php erp_print_key_value( __( 'Marital Status', 'wp-erp' ), $employee->get_marital_status() ); ?></li>
            <li><?php erp_print_key_value( __( 'Driving License', 'wp-erp' ), $employee->driving_license ); ?></li>
            <li><?php erp_print_key_value( __( 'Hobbies', 'wp-erp' ), $employee->hobbies ); ?></li>

            <?php do_action( 'erp-hr-employee-single-personal', $employee ); ?>
        </ul>
    </div>
</div><!-- .postbox -->

<?php do_action( 'erp-hr-employee-single-after-personal', $employee ); ?>

<div class="postbox leads-actions erp-work-experience-wrap">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Work Experience', 'wp-erp' ); ?></span></h3>
    <div class="inside">

        <?php
        $experiences = $employee->get_experiences();

        if ( ! $experiences->isEmpty() ) {
            ?>
            <table class="widefat" style="margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th><?php _e( 'Previous Company', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Job Title', 'wp-erp' ); ?></th>
                        <th><?php _e( 'From', 'wp-erp' ); ?></th>
                        <th><?php _e( 'To', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Job Description', 'wp-erp' ); ?></th>
                        <th width="10%">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($experiences as $key => $experience) { ?>

                        <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td><?php echo esc_html( $experience->company_name ); ?></td>
                            <td><?php echo esc_html( $experience->job_title ); ?></td>
                            <td><?php echo erp_format_date( $experience->from ); ?></td>
                            <td><?php echo erp_format_date( $experience->to ); ?></td>
                            <td><?php echo esc_html( $experience->description ); ?></td>
                            <td width="10%">
                                <div class="row-actions erp-hide-print">
                                    <a href="#" class="work-experience-edit" data-template="erp-employment-work-experience" data-title="<?php esc_attr_e( 'Work Experience', 'wp-erp' ); ?>" data-data='<?php echo json_encode( $experience ); ?>' data-button="<?php esc_attr_e( 'Update Experience', 'wp-erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                    <a href="#" class="work-experience-delete" data-id="<?php echo $experience->id; ?>" data-action="erp-hr-emp-delete-exp"><span class="dashicons dashicons-trash"></span></a>
                                </div>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>

            <?php _e( 'No work experiences found.', 'wp-erp' ); ?>

        <?php } ?>

        <a class="button button-secondary erp-hide-print" id="erp-empl-add-exp" href="#" data-data='<?php echo json_encode( [ 'employee_id' => $employee->id ] ); ?>' data-button="<?php esc_attr_e( 'Create Experience', 'wp-erp' ); ?>"  data-template="erp-employment-work-experience" data-title="<?php esc_attr_e( 'Work Experience', 'wp-erp' ); ?>"><?php _e( '+ Add Experience', 'wp-erp' ); ?></a>

    </div>
</div><!-- .postbox -->

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Education', 'wp-erp' ); ?></span></h3>
    <div class="inside">
        <?php
        $educations = $employee->get_educations();

        if ( ! $educations->isEmpty() ) {
            ?>
            <table class="widefat" style="margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th><?php _e( 'School Name', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Degree', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Field(s) of Study', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Year of Completion', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Additional Notes', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Interests', 'wp-erp' ); ?></th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($educations as $key => $education) { ?>

                        <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td><?php echo esc_html( $education->school ); ?></td>
                            <td><?php echo esc_html( $education->degree ); ?></td>
                            <td><?php echo esc_html( $education->field ); ?></td>
                            <td><?php echo $education->finished; ?></td>
                            <td><?php echo $education->notes ? esc_html( $education->notes ) : '-'; ?></td>
                            <td><?php echo $education->interest ? esc_html( $education->interest ) : '-'; ?></td>
                            <td width="10%">
                                <div class="row-actions erp-hide-print">
                                    <a href="#" class="education-edit" data-template="erp-employment-education" data-title="<?php esc_attr_e( 'Education', 'wp-erp' ); ?>" data-data='<?php echo json_encode( $education ); ?>' data-button="<?php esc_attr_e( 'Update Info', 'wp-erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                    <a href="#" class="education-delete" data-id="<?php echo $education->id; ?>" data-action="erp-hr-emp-delete-education"><span class="dashicons dashicons-trash"></span></a>
                                </div>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>

            <?php _e( 'No education information found.', 'wp-erp' ); ?>

        <?php } ?>

        <a class="button button-secondary erp-hide-print" id="erp-empl-add-education" href="#" data-data='<?php echo json_encode( array( 'employee_id' => $employee->id ) ); ?>'  data-template="erp-employment-education" data-title="<?php esc_attr_e( 'Education', 'wp-erp' ); ?>" data-button="<?php esc_attr_e( 'Add Education', 'wp-erp' ); ?>"><?php _e( '+ Add Education', 'wp-erp' ); ?></a>
    </div>
</div><!-- .postbox -->

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Dependents', 'wp-erp' ); ?></span></h3>
    <div class="inside">

        <?php
        $dependents = $employee->get_dependents();

        if ( ! $dependents->isEmpty() ) {
            ?>
            <table class="widefat" style="margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th><?php _e( 'Name', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Relationship', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Date of Birth', 'wp-erp' ); ?></th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dependents as $key => $dependent) { ?>

                        <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td><?php echo esc_html( $dependent->name ); ?></td>
                            <td><?php echo esc_html( $dependent->relation ); ?></td>
                            <td><?php echo erp_format_date( $dependent->dob ); ?></td>
                            <td width="10%">
                                <div class="row-actions erp-hide-print">
                                    <a href="#" class="dependent-edit" data-template="erp-employment-dependent" data-title="<?php esc_attr_e( 'Dependents', 'wp-erp' ); ?>" data-data='<?php echo json_encode( $dependent ); ?>' data-button="<?php esc_attr_e( 'Update Dependent', 'wp-erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                    <a href="#" class="dependent-delete" data-id="<?php echo $dependent->id; ?>" data-action="erp-hr-emp-delete-dependent"><span class="dashicons dashicons-trash"></span></a>
                                </div>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>

            <?php _e( 'No dependent information found.', 'wp-erp' ); ?>

        <?php } ?>
        <p class="erp-hide-print">Hello nata</p>
        <a class="button button-secondary erp-hide-print" id="erp-empl-add-dependent" href="#" data-data='<?php echo json_encode( array( 'employee_id' => $employee->id ) ); ?>'  data-template="erp-employment-dependent" data-title="<?php esc_attr_e( 'Dependent', 'wp-erp' ); ?>" data-button="<?php esc_attr_e( 'Add Dependent', 'wp-erp' ); ?>"><?php _e( '+ Add Dependents', 'wp-erp' ); ?></a>

    </div>
</div><!-- .postbox -->

<?php if ( $employee->get_status() == 'Terminated' ): ?>

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php _e( 'Termination', 'wp-erp' ); ?></span></h3>
        <div class="inside">

            <?php $termination_data = get_user_meta( $employee->id, '_erp_hr_termination', true ); ?>

            <p><?php _e( 'Termination Date', 'wp-erp' ); ?> : <?php echo isset( $termination_data['terminate_date'] ) ? erp_format_date( $termination_data['terminate_date'] ) : ''; ?></p>
            <p><?php _e( 'Termination Type', 'wp-erp' ); ?> : <?php echo isset( $termination_data['termination_type'] ) ? erp_hr_get_terminate_type( $termination_data['termination_type'] ) : ''; ?></p>
            <p><?php _e( 'Termination Reason', 'wp-erp' ); ?> : <?php echo isset( $termination_data['termination_reason'] ) ? erp_hr_get_terminate_reason( $termination_data['termination_reason'] ) : ''; ?></p>
            <p><?php _e( 'Eligible for Hire', 'wp-erp' ); ?> : <?php echo isset( $termination_data['eligible_for_rehire'] ) ? erp_hr_get_terminate_rehire_options( $termination_data['eligible_for_rehire'] ) : ''; ?></p>


            <a class="button button-secondary erp-hide-print" id="erp-employee-terminate" href="#" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-terminate" data-data='<?php echo json_encode( $termination_data ); ?>' data-title="<?php esc_attr_e( 'Update Termination', 'wp-erp' ); ?>" data-button="<?php esc_attr_e( 'Change Termination', 'wp-erp' ); ?>"><?php _e( 'Change Termination', 'wp-erp' ); ?></a>

        </div>
    </div><!-- .postbox -->

<?php endif; ?>

<?php do_action( 'erp-hr-employee-single-bottom', $employee ); ?>
