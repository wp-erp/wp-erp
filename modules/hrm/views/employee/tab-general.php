<?php do_action( 'erp-hr-employee-single-top', $employee ); ?>

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php esc_html_e( 'Basic Info', 'erp' ); ?></span></h3>
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
<?php if ( current_user_can( 'erp_edit_employee' ) || get_current_user_id() == $employee->user_id ) : ?>

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e( 'Work', 'erp' ); ?></span></h3>
        <div class="inside">
            <ul class="erp-list two-col separated">
                <li><?php erp_print_key_value( __( 'Department', 'erp' ), $employee->get_department('view') ); ?></li>
                <li><?php erp_print_key_value( __( 'Title', 'erp' ), $employee->get_job_title() ); ?></li>

                <?php
                if ( $employee->get_reporting_to() ) {
                    $reporting_to = erp_hr_get_employee_name( $employee->get_reporting_to() );
                    $reporting_to_link =  erp_hr_get_single_link( $employee->get_reporting_to(), $reporting_to );
                }
                ?>

                <li><?php erp_print_key_value( __( 'Reporting To', 'erp' ), isset( $reporting_to_link ) ? $reporting_to_link : '-' ); ?></li>
                <li>
                    <?php erp_print_key_value( __( 'Date of Hire', 'erp' ), '<span style="font-weight: bold">' . $employee->get_hiring_date() . '</span>' ); ?>
                    <?php
                        $emp_hdate = new DateTime( $employee->get_hiring_date() );
                        $cur_date  = new DateTime( date( 'd-m-Y' ) );
                        $interval = $cur_date->diff( $emp_hdate );
                        echo '( '. $interval->y.' years, '.$interval->m.' months, '.$interval->d.' days )';
                    ?>
                </li>
                <li><?php erp_print_key_value( __( 'Source of Hire', 'erp' ), $employee->get_hiring_source( 'view' ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Employee Status', 'erp' ), $employee->get_status( 'view' ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Work Phone', 'erp' ), erp_get_clickable( 'phone', $employee->get_work_phone()) ); ?></li>
                <li><?php erp_print_key_value( __( 'Employee Type', 'erp' ), $employee->get_type( 'view' ) ); ?></li>

                <?php do_action( 'erp-hr-employee-single-work', $employee ); ?>
            </ul>
        </div>
    </div><!-- .postbox -->

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e( 'Personal Details', 'erp' ); ?></span></h3>
        <div class="inside">
            <ul class="erp-list two-col separated">
                <li><?php erp_print_key_value( __( 'Blood Group', 'erp' ), $employee->get_bloog_group() ); ?></li>
                <li><?php erp_print_key_value( __( 'Spouse\'s Name', 'erp' ), $employee->spouse_name ); ?></li>
                <li><?php erp_print_key_value( __( 'Father\'s Name', 'erp' ), $employee->father_name ); ?></li>
                <li><?php erp_print_key_value( __( 'Mother\'s Name', 'erp' ), $employee->mother_name ); ?></li>
                <li><?php erp_print_key_value( __( 'Address 1', 'erp' ), $employee->get_street_1() ); ?></li>
                <li><?php erp_print_key_value( __( 'Address 2', 'erp' ), $employee->get_street_2() ); ?></li>
                <li><?php erp_print_key_value( __( 'City', 'erp' ), $employee->get_city() ); ?></li>
                <li><?php erp_print_key_value( __( 'Country', 'erp' ), $employee->get_country( 'view' ) ); ?></li>
                <li><?php erp_print_key_value( __( 'State', 'erp' ), $employee->get_state( 'view' ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Postal Code', 'erp' ), $employee->get_postal_code() ); ?></li>

                <li><?php erp_print_key_value( __( 'Mobile', 'erp' ), erp_get_clickable( 'phone', $employee->get_mobile() ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Other Email', 'erp' ), erp_get_clickable( 'email', $employee->other_email ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Date of Birth', 'erp' ), $employee->get_date_of_birth() ); ?></li>
                <li><?php erp_print_key_value( __( 'Gender', 'erp' ), $employee->get_gender( 'view' ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Nationality', 'erp' ), $employee->get_nationality( 'view' ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Marital Status', 'erp' ), $employee->get_marital_status( 'view' ) ); ?></li>
                <li><?php erp_print_key_value( __( 'Driving License', 'erp' ), $employee->get_driving_license() ); ?></li>
                <li><?php erp_print_key_value( __( 'Hobbies', 'erp' ), $employee->hobbies ); ?></li>

                <?php do_action( 'erp-hr-employee-single-personal', $employee ); ?>
            </ul>
        </div>
    </div><!-- .postbox -->

    <?php do_action( 'erp-hr-employee-single-after-personal', $employee ); ?>

    <div class="postbox leads-actions erp-work-experience-wrap">
        <div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e( 'Work Experience', 'erp' ); ?></span></h3>
        <div class="inside">

            <?php
            $experiences = $employee->get_experiences();

            if ( ! $experiences->isEmpty() ) {
                ?>
                <table class="widefat" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Previous Company', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Job Title', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'From', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'To', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Job Description', 'erp' ); ?></th>
                            <th width="10%">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($experiences as $key => $experience) { ?>

                            <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                                <td><?php echo esc_html( $experience->company_name ); ?></td>
                                <td><?php echo esc_html( $experience->job_title ); ?></td>
                                <td><?php echo esc_html( erp_format_date( $experience->from ) ); ?></td>
                                <td><?php echo esc_html( erp_format_date( $experience->to ) ); ?></td>
                                <td><?php echo esc_html( $experience->description ); ?></td>
                                <td width="10%">
                                    <?php if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) : ?>
                                        <div class="row-actions erp-hide-print">
                                            <a href="#" class="work-experience-edit" data-template="erp-employment-work-experience" data-title="<?php esc_attr_e( 'Work Experience', 'erp' ); ?>" data-data='<?php echo json_encode( $experience ); ?>' data-button="<?php esc_attr_e( 'Update Experience', 'erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                            <a href="#" class="work-experience-delete" data-employee_id="<?php echo esc_html( $employee->get_user_id() ); ?>" data-id="<?php echo esc_html( $experience->id ); ?>" data-action="erp-hr-emp-delete-exp"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>

                <?php esc_html_e( 'No work experiences found.', 'erp' ); ?>

            <?php } ?>
            <?php if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) : ?>
                <a class="button button-secondary erp-hide-print" id="erp-empl-add-exp" href="#" data-data='<?php echo json_encode( [ 'employee_id' => $employee->get_user_id() ] ); ?>' data-button="<?php esc_attr_e( 'Create Experience', 'erp' ); ?>"  data-template="erp-employment-work-experience" data-title="<?php esc_attr_e( 'Work Experience', 'erp' ); ?>"><?php esc_html_e( '+ Add Experience', 'erp' ); ?></a>
            <?php endif; ?>
        </div>
    </div><!-- .postbox -->

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e( 'Education', 'erp' ); ?></span></h3>
        <div class="inside">
            <?php
            $educations = $employee->get_educations();

            if ( ! $educations->isEmpty() ) {
                ?>
                <table class="widefat" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'School Name', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Degree', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Field(s) of Study', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Year of Completion', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Additional Notes', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Interests', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Expiration date', 'erp' ); ?></th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($educations as $key => $education) { ?>
                            <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                                <td><?php echo esc_html( $education->school ); ?></td>
                                <td><?php echo esc_html( $education->degree ); ?></td>
                                <td><?php echo esc_html( $education->field ); ?></td>
                                <td><?php echo esc_html( $education->finished ); ?></td>
                                <td><?php echo $education->notes ? esc_html( $education->notes ) : '-'; ?></td>
                                <td><?php echo $education->interest ? esc_html( $education->interest ) : '-'; ?></td>
                                <td>
                                    <?php
                                    $education->expiration_date = get_user_meta($education->employee_id, 'education_' . $education->employee_id . '_' . $education->id, true );
                                    echo $education->expiration_date ? esc_html($education->expiration_date) : '-';
                                    ?>
                                </td>
                                <td width="10%">
                                    <?php if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) : ?>
                                        <div class="row-actions erp-hide-print">
                                            <a href="#" class="education-edit" data-template="erp-employment-education" data-title="<?php esc_attr_e( 'Education', 'erp' ); ?>" data-data='<?php echo json_encode( $education ); ?>' data-button="<?php esc_attr_e( 'Update Info', 'erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                            <a href="#" class="education-delete" data-employee_id="<?php echo esc_html( $employee->get_user_id() ); ?>" data-id="<?php echo esc_html( $education->id ); ?>" data-action="erp-hr-emp-delete-education"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>

                <?php esc_html_e( 'No education information found.', 'erp' ); ?>

            <?php } ?>

            <?php if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) : ?>
                <a class="button button-secondary erp-hide-print" id="erp-empl-add-education" href="#" data-data='<?php echo json_encode( array( 'employee_id' => $employee->get_user_id() ) ); ?>'  data-template="erp-employment-education" data-title="<?php esc_attr_e( 'Education', 'erp' ); ?>" data-button="<?php esc_attr_e( 'Add Education', 'erp' ); ?>"><?php esc_html_e( '+ Add Education', 'erp' ); ?></a>
            <?php endif; ?>
        </div>
    </div><!-- .postbox -->

    <div class="postbox leads-actions">
        <div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'erp' ); ?>"><br></div>
        <h3 class="hndle"><span><?php esc_html_e( 'Dependents', 'erp' ); ?></span></h3>
        <div class="inside">

            <?php
            $dependents = $employee->get_dependents();

            if ( ! $dependents->isEmpty() ) {
                ?>
                <table class="widefat" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Relationship', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Date of Birth', 'erp' ); ?></th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dependents as $key => $dependent) { ?>

                            <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                                <td><?php echo esc_html( $dependent->name ); ?></td>
                                <td><?php echo esc_html( $dependent->relation ); ?></td>
                                <td><?php echo esc_html( erp_format_date( $dependent->dob ) ); ?></td>
                                <td width="10%">
                                    <?php if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) : ?>
                                        <div class="row-actions erp-hide-print">
                                            <a href="#" class="dependent-edit" data-template="erp-employment-dependent" data-title="<?php esc_attr_e( 'Dependents', 'erp' ); ?>" data-data='<?php echo json_encode( $dependent ); ?>' data-button="<?php esc_attr_e( 'Update Dependent', 'erp' ); ?>"><span class="dashicons dashicons-edit"></span></a>
                                            <a href="#" class="dependent-delete" data-employee_id="<?php echo esc_html( $employee->get_user_id() ); ?>" data-id="<?php echo esc_html( $dependent->id ); ?>" data-action="erp-hr-emp-delete-dependent"><span class="dashicons dashicons-trash"></span></a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>

                <?php esc_html_e( 'No dependent information found.', 'erp' ); ?>

            <?php } ?>

            <?php if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) : ?>
                <a class="button button-secondary erp-hide-print" id="erp-empl-add-dependent" href="#" data-data='<?php echo json_encode( array( 'employee_id' => $employee->get_user_id() ) ); ?>'  data-template="erp-employment-dependent" data-title="<?php esc_attr_e( 'Dependent', 'erp' ); ?>" data-button="<?php esc_attr_e( 'Add Dependent', 'erp' ); ?>"><?php esc_html_e( '+ Add Dependents', 'erp' ); ?></a>
            <?php endif; ?>
        </div>
    </div><!-- .postbox -->

    <?php if ( $employee->get_status() == 'Terminated' ): ?>

        <div class="postbox leads-actions">
            <div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'erp' ); ?>"><br></div>
            <h3 class="hndle"><span><?php esc_html_e( 'Termination', 'erp' ); ?></span></h3>
            <div class="inside">

                <?php $termination_data = get_user_meta( $employee->get_user_id(), '_erp_hr_termination', true ); ?>

                <p><?php esc_html_e( 'Termination Date', 'erp' ); ?> : <?php echo isset( $termination_data['terminate_date'] ) ? esc_html( erp_format_date( $termination_data['terminate_date'] ) ) : ''; ?></p>
                <p><?php esc_html_e( 'Termination Type', 'erp' ); ?> : <?php echo isset( $termination_data['termination_type'] ) ? esc_html( erp_hr_get_terminate_type( $termination_data['termination_type'] ) ) : ''; ?></p>
                <p><?php esc_html_e( 'Termination Reason', 'erp' ); ?> : <?php echo isset( $termination_data['termination_reason'] ) ? esc_html( erp_hr_get_terminate_reason( $termination_data['termination_reason'] ) ) : ''; ?></p>
                <p><?php esc_html_e( 'Eligible for Hire', 'erp' ); ?> : <?php echo isset( $termination_data['eligible_for_rehire'] ) ? esc_html( erp_hr_get_terminate_rehire_options( $termination_data['eligible_for_rehire'] ) ) : ''; ?></p>

                <?php if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) : ?>
                    <a class="button button-secondary erp-hide-print" id="erp-employee-terminate" href="#" data-id="<?php echo esc_html( $employee->get_user_id() ); ?>" data-template="erp-employment-terminate" data-data='<?php echo json_encode( $termination_data ); ?>' data-title="<?php esc_attr_e( 'Update Termination', 'erp' ); ?>" data-button="<?php esc_attr_e( 'Change Termination', 'erp' ); ?>"><?php esc_html_e( 'Change Termination', 'erp' ); ?></a>
                <?php endif; ?>
            </div>
        </div><!-- .postbox -->

    <?php endif; ?>

<?php endif; ?>

<?php do_action( 'erp-hr-employee-single-bottom', $employee ); ?>
