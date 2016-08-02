<?php do_action( 'erp-hr-employee-single-top', $employee ); ?>

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Basic Info', 'erp' ); ?></span></h3>
    <div class="inside">
        <ul class="erp-list two-col separated">
            <li><?php erp_print_key_value( __( 'First Name', 'erp' ), $employee->first_name ); ?></li>
            <li><?php erp_print_key_value( __( 'Last Name', 'erp' ), $employee->last_name ); ?></li>
            <li><?php erp_print_key_value( __( 'Employee ID', 'erp' ), $employee->employee_id ); ?></li>
            <li><?php erp_print_key_value( __( 'Email', 'erp' ), erp_get_clickable( 'email', $employee->user_email ) ); ?></li>

            <?php do_action( 'erp-hr-employee-single-basic', $employee ); ?>
        </ul>
    </div>
</div><!-- .postbox -->
<?php if ( current_user_can( 'erp_edit_employee' ) || get_current_user_id() == $employee->id ) : ?>

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php _e( 'Work', 'erp' ); ?></span></h3>
        <div class="inside">
            <ul class="erp-list two-col separated">
                <li><?php erp_print_key_value( __( 'Department', 'erp' ), $employee->get_department_title() ); ?></li>
                <li><?php erp_print_key_value( __( 'Title', 'erp' ), $employee->get_job_title() ); ?></li>

                <?php
                $reporting_to = $employee->get_reporting_to();
                $reporting_to = $reporting_to ? $reporting_to->get_link() : '-';
                ?>
                <li><?php erp_print_key_value( __( 'Reporting To', 'erp' ), $reporting_to ); ?></li>
                <li><?php erp_print_key_value( __( 'Date of Hire', 'erp' ), $employee->get_joined_date() ); ?></li>
                <li><?php erp_print_key_value( __( 'Source of Hire', 'erp' ), $employee->get_hiring_source() ); ?></li>
                <li><?php erp_print_key_value( __( 'Employee Status', 'erp' ), $employee->get_status() ); ?></li>
                <li><?php erp_print_key_value( __( 'Work Phone', 'erp' ), erp_get_clickable( 'phone', $employee->get_phone( 'work' ) ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Employee Type', 'erp' ), $employee->get_type() ); ?></li>

                <?php do_action( 'erp-hr-employee-single-work', $employee ); ?>
            </ul>
        </div>
    </div><!-- .postbox -->

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php _e( 'Personal Details', 'erp' ); ?></span></h3>
        <div class="inside">
            <ul class="erp-list two-col separated">
                <li><?php erp_print_key_value( __( 'Address 1', 'erp' ), $employee->get_street_1() ); ?></li>
                <li><?php erp_print_key_value( __( 'Address 2', 'erp' ), $employee->get_street_2() ); ?></li>
                <li><?php erp_print_key_value( __( 'City', 'erp' ), $employee->get_city() ); ?></li>
                <li><?php erp_print_key_value( __( 'Country', 'erp' ), $employee->get_country() ); ?></li>
                <li><?php erp_print_key_value( __( 'State', 'erp' ), $employee->get_state() ); ?></li>
                <li><?php erp_print_key_value( __( 'Postal Code', 'erp' ), $employee->get_postal_code() ); ?></li>

                <li><?php erp_print_key_value( __( 'Mobile', 'erp' ), erp_get_clickable( 'phone', $employee->get_phone( 'mobile' ) ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Other Email', 'erp' ), erp_get_clickable( 'email', $employee->other_email ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Date of Birth', 'erp' ), $employee->get_birthday() ); ?></li>
                <li><?php erp_print_key_value( __( 'Gender', 'erp' ), $employee->get_gender() ); ?></li>
                <li><?php erp_print_key_value( __( 'Nationality', 'erp' ), $employee->get_nationality() ); ?></li>
                <li><?php erp_print_key_value( __( 'Marital Status', 'erp' ), $employee->get_marital_status() ); ?></li>
                <li><?php erp_print_key_value( __( 'Driving License', 'erp' ), $employee->driving_license ); ?></li>
                <li><?php erp_print_key_value( __( 'Hobbies', 'erp' ), $employee->hobbies ); ?></li>

                <?php do_action( 'erp-hr-employee-single-personal', $employee ); ?>
            </ul>
        </div>
    </div><!-- .postbox -->

    <?php do_action( 'erp-hr-employee-single-after-personal', $employee ); ?>

    <div class="postbox leads-actions erp-work-experience-wrap">
        <div class="handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php _e( 'Work Experience', 'erp' ); ?></span></h3>
        <div class="inside">

            <?php
            $experiences = $employee->get_experiences();

            if ( ! $experiences->isEmpty() ) {
                ?>
                <table class="widefat" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th><?php _e( 'Previous Company', 'erp' ); ?></th>
                            <th><?php _e( 'Job Title', 'erp' ); ?></th>
                            <th><?php _e( 'From', 'erp' ); ?></th>
                            <th><?php _e( 'To', 'erp' ); ?></th>
                            <th><?php _e( 'Job Description', 'erp' ); ?></th>
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
                                    <?php if ( current_user_can( 'erp_edit_employee', $employee->id ) ) : ?>
                                        <div class="row-actions erp-hide-print">
                                            <a href="#" class="work-experience-edit" data-template="erp-employment-work-experience" data-title="<?php esc_attr_e( 'Work Experience', 'erp' ); ?>" data-data='<?php echo json_encode( $experience ); ?>' data-button="<?php esc_attr_e( 'Update Experience', 'erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                            <a href="#" class="work-experience-delete" data-employee_id="<?php echo $employee->id; ?>" data-id="<?php echo $experience->id; ?>" data-action="erp-hr-emp-delete-exp"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>

                <?php _e( 'No work experiences found.', 'erp' ); ?>

            <?php } ?>
            <?php if ( current_user_can( 'erp_edit_employee', $employee->id ) ) : ?>
                <a class="button button-secondary erp-hide-print" id="erp-empl-add-exp" href="#" data-data='<?php echo json_encode( [ 'employee_id' => $employee->id ] ); ?>' data-button="<?php esc_attr_e( 'Create Experience', 'erp' ); ?>"  data-template="erp-employment-work-experience" data-title="<?php esc_attr_e( 'Work Experience', 'erp' ); ?>"><?php _e( '+ Add Experience', 'erp' ); ?></a>
            <?php endif; ?>
        </div>
    </div><!-- .postbox -->

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php _e( 'Education', 'erp' ); ?></span></h3>
        <div class="inside">
            <?php
            $educations = $employee->get_educations();

            if ( ! $educations->isEmpty() ) {
                ?>
                <table class="widefat" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th><?php _e( 'School Name', 'erp' ); ?></th>
                            <th><?php _e( 'Degree', 'erp' ); ?></th>
                            <th><?php _e( 'Field(s) of Study', 'erp' ); ?></th>
                            <th><?php _e( 'Year of Completion', 'erp' ); ?></th>
                            <th><?php _e( 'Additional Notes', 'erp' ); ?></th>
                            <th><?php _e( 'Interests', 'erp' ); ?></th>
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
                                    <?php if ( current_user_can( 'erp_edit_employee', $employee->id ) ) : ?>
                                        <div class="row-actions erp-hide-print">
                                            <a href="#" class="education-edit" data-template="erp-employment-education" data-title="<?php esc_attr_e( 'Education', 'erp' ); ?>" data-data='<?php echo json_encode( $education ); ?>' data-button="<?php esc_attr_e( 'Update Info', 'erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                            <a href="#" class="education-delete" data-employee_id="<?php echo $employee->id; ?>" data-id="<?php echo $education->id; ?>" data-action="erp-hr-emp-delete-education"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>

                <?php _e( 'No education information found.', 'erp' ); ?>

            <?php } ?>

            <?php if ( current_user_can( 'erp_edit_employee', $employee->id ) ) : ?>
                <a class="button button-secondary erp-hide-print" id="erp-empl-add-education" href="#" data-data='<?php echo json_encode( array( 'employee_id' => $employee->id ) ); ?>'  data-template="erp-employment-education" data-title="<?php esc_attr_e( 'Education', 'erp' ); ?>" data-button="<?php esc_attr_e( 'Add Education', 'erp' ); ?>"><?php _e( '+ Add Education', 'erp' ); ?></a>
            <?php endif; ?>
        </div>
    </div><!-- .postbox -->

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php _e( 'Dependents', 'erp' ); ?></span></h3>
        <div class="inside">

            <?php
            $dependents = $employee->get_dependents();

            if ( ! $dependents->isEmpty() ) {
                ?>
                <table class="widefat" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th><?php _e( 'Name', 'erp' ); ?></th>
                            <th><?php _e( 'Relationship', 'erp' ); ?></th>
                            <th><?php _e( 'Date of Birth', 'erp' ); ?></th>
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
                                    <?php if ( current_user_can( 'erp_edit_employee', $employee->id ) ) : ?>
                                        <div class="row-actions erp-hide-print">
                                            <a href="#" class="dependent-edit" data-template="erp-employment-dependent" data-title="<?php esc_attr_e( 'Dependents', 'erp' ); ?>" data-data='<?php echo json_encode( $dependent ); ?>' data-button="<?php esc_attr_e( 'Update Dependent', 'erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                            <a href="#" class="dependent-delete" data-employee_id="<?php echo $employee->id; ?>" data-id="<?php echo $dependent->id; ?>" data-action="erp-hr-emp-delete-dependent"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>

                <?php _e( 'No dependent information found.', 'erp' ); ?>

            <?php } ?>

            <?php if ( current_user_can( 'erp_edit_employee', $employee->id ) ) : ?>
                <a class="button button-secondary erp-hide-print" id="erp-empl-add-dependent" href="#" data-data='<?php echo json_encode( array( 'employee_id' => $employee->id ) ); ?>'  data-template="erp-employment-dependent" data-title="<?php esc_attr_e( 'Dependent', 'erp' ); ?>" data-button="<?php esc_attr_e( 'Add Dependent', 'erp' ); ?>"><?php _e( '+ Add Dependents', 'erp' ); ?></a>
            <?php endif; ?>
        </div>
    </div><!-- .postbox -->

    <?php if ( $employee->get_status() == 'Terminated' ): ?>

        <div class="postbox leads-actions">
            <div class="handlediv" title="<?php _e( 'Click to toggle', 'erp' ); ?>"><br></div>
            <h3 class="hndle"><span><?php _e( 'Termination', 'erp' ); ?></span></h3>
            <div class="inside">

                <?php $termination_data = get_user_meta( $employee->id, '_erp_hr_termination', true ); ?>

                <p><?php _e( 'Termination Date', 'erp' ); ?> : <?php echo isset( $termination_data['terminate_date'] ) ? erp_format_date( $termination_data['terminate_date'] ) : ''; ?></p>
                <p><?php _e( 'Termination Type', 'erp' ); ?> : <?php echo isset( $termination_data['termination_type'] ) ? erp_hr_get_terminate_type( $termination_data['termination_type'] ) : ''; ?></p>
                <p><?php _e( 'Termination Reason', 'erp' ); ?> : <?php echo isset( $termination_data['termination_reason'] ) ? erp_hr_get_terminate_reason( $termination_data['termination_reason'] ) : ''; ?></p>
                <p><?php _e( 'Eligible for Hire', 'erp' ); ?> : <?php echo isset( $termination_data['eligible_for_rehire'] ) ? erp_hr_get_terminate_rehire_options( $termination_data['eligible_for_rehire'] ) : ''; ?></p>

                <?php if ( current_user_can( 'erp_edit_employee', $employee->id ) ) : ?>
                    <a class="button button-secondary erp-hide-print" id="erp-employee-terminate" href="#" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-terminate" data-data='<?php echo json_encode( $termination_data ); ?>' data-title="<?php esc_attr_e( 'Update Termination', 'erp' ); ?>" data-button="<?php esc_attr_e( 'Change Termination', 'erp' ); ?>"><?php _e( 'Change Termination', 'erp' ); ?></a>
                <?php endif; ?>
            </div>
        </div><!-- .postbox -->

    <?php endif; ?>

<?php endif; ?>

<?php do_action( 'erp-hr-employee-single-bottom', $employee ); ?>
