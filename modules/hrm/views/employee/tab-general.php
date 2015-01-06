<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Basic Info', 'wp-erp' ); ?></span></h3>
    <div class="inside">
        <ul class="erp-list two-col separated">
            <li><?php erp_print_key_value( __( 'First Name', 'wp-erp' ), $employee->first_name ); ?></li>
            <li><?php erp_print_key_value( __( 'Last Name', 'wp-erp' ), $employee->last_name ); ?></li>
            <li><?php erp_print_key_value( __( 'Employee ID', 'wp-erp' ), $employee->employee_id ); ?></li>
            <li><?php erp_print_key_value( __( 'Email', 'wp-erp' ), erp_get_clickable( 'email', $employee->user_email ) ); ?></li>
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
            <li><?php erp_print_key_value( __( 'Joined', 'wp-erp' ), $employee->get_joined_date() ); ?></li>
            <li><?php erp_print_key_value( __( 'Source of Hire', 'wp-erp' ), $employee->get_hiring_source() ); ?></li>
            <li><?php erp_print_key_value( __( 'Employee Status', 'wp-erp' ), $employee->get_status() ); ?></li>
            <li><?php erp_print_key_value( __( 'Work Phone', 'wp-erp' ), erp_get_clickable( 'phone', $employee->get_phone( 'work' ) ) ); ?></li>
        </ul>
    </div>
</div><!-- .postbox -->

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Personal Details', 'wp-erp' ); ?></span></h3>
    <div class="inside">
        <ul class="erp-list two-col separated">
            <li><?php erp_print_key_value( __( 'Mobile', 'wp-erp' ), erp_get_clickable( 'phone', $employee->get_phone( 'personal' ) ) ); ?></li>
            <li><?php erp_print_key_value( __( 'Address', 'wp-erp' ), $employee->personal_address ); ?></li>
            <li><?php erp_print_key_value( __( 'Other Email', 'wp-erp' ), erp_get_clickable( 'email', $employee->personal_other_email ) ); ?></li>
            <li><?php erp_print_key_value( __( 'Date of Birth', 'wp-erp' ), $employee->get_birthday() ); ?></li>
            <li><?php erp_print_key_value( __( 'Gender', 'wp-erp' ), $employee->get_gender() ); ?></li>
            <li><?php erp_print_key_value( __( 'Nationality', 'wp-erp' ), $employee->get_nationality() ); ?></li>
            <li><?php erp_print_key_value( __( 'Marital Status', 'wp-erp' ), $employee->get_marital_status() ); ?></li>
            <li><?php erp_print_key_value( __( 'Driving License', 'wp-erp' ), $employee->personal_driving_license ); ?></li>
            <li><?php erp_print_key_value( __( 'Hobbies', 'wp-erp' ), $employee->personal_hobbies ); ?></li>
        </ul>
    </div>
</div><!-- .postbox -->

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Work Experience', 'wp-erp' ); ?></span></h3>
    <div class="inside">

        <?php
        $experiences = $employee->get_experiences();

        if ( $experiences ) {
            ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e( 'Previous Company', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Job Title', 'wp-erp' ); ?></th>
                        <th><?php _e( 'From', 'wp-erp' ); ?></th>
                        <th><?php _e( 'To', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Job Description', 'wp-erp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($experiences as $key => $experience) { ?>

                        <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td><?php echo $experience['company_name'] ?></td>
                            <td><?php echo $experience['job_title'] ?></td>
                            <td><?php echo $experience['from'] ?></td>
                            <td><?php echo $experience['to'] ?></td>
                            <td><?php echo $experience['description'] ?></td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>

            <?php _e( 'No work experiences found.', 'wp-erp' ); ?>

        <?php } ?>

    </div>
</div><!-- .postbox -->

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Education', 'wp-erp' ); ?></span></h3>
    <div class="inside">
        <?php
        $educations = $employee->get_educations();

        if ( $educations ) {
            ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e( 'School Name', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Degree', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Field(s) of Study', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Date of Completion', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Additional Notes', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Interests', 'wp-erp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($educations as $key => $education) { ?>

                        <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td><?php echo $education['school'] ?></td>
                            <td><?php echo $education['degree'] ?></td>
                            <td><?php echo $education['field'] ?></td>
                            <td><?php echo $education['finished'] ?></td>
                            <td><?php echo $education['notes'] ?></td>
                            <td><?php echo $education['interests'] ?></td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>

            <?php _e( 'No education information found.', 'wp-erp' ); ?>

        <?php } ?>
    </div>
</div><!-- .postbox -->

<div class="postbox leads-actions">
    <div class="handlediv" title="<?php _e( 'Click to toggle', 'wp-erp' ); ?>"><br></div>
    <h3 class="hndle"><span><?php _e( 'Dependents', 'wp-erp' ); ?></span></h3>
    <div class="inside">

        <?php
        $dependents = $employee->get_dependents();

        if ( $dependents ) {
            ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php _e( 'Name', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Relationship', 'wp-erp' ); ?></th>
                        <th><?php _e( 'Date of Birth', 'wp-erp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dependents as $key => $dependent) { ?>

                        <tr class="<?php echo $key % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td><?php echo $dependent['name'] ?></td>
                            <td><?php echo $dependent['relation'] ?></td>
                            <td><?php echo $dependent['dob'] ?></td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>

            <?php _e( 'No dependent information found.', 'wp-erp' ); ?>

        <?php } ?>

    </div>
</div><!-- .postbox -->