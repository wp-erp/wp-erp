<div class="job-tab-wrap">

    <?php $history = $employee->get_history(); ?>

    <?php
    if ( current_user_can( 'erp_manage_jobinfo' ) ) {
        ?>
        <h3><?php _e( 'Employee Main Status', 'erp' ); ?></h3>

        <form action="" method="post">
            <?php erp_html_form_input( array(
                'label'   => __( 'Employee Status : ', 'erp' ),
                'name'    => 'employee_status',
                'value'   => $employee->erp->status,
                'class'   => 'select2',
                'type'    => 'select',
                'id'      => 'erp-hr-employee-status-option',
                'custom_attr' => [ 'data-selected' => $employee->erp->status ],
                'options' => array( 0 => __( '- Select -', 'erp' ) ) + erp_hr_get_employee_statuses()
            ) ); ?>

            <input type="hidden" name="user_id" id="erp-employee-id" value="<?php echo $employee->id; ?>">
            <input type="hidden" name="action" id="erp-employee-status-action" value="erp-hr-employee-status">
            <?php wp_nonce_field( 'wp-erp-hr-employee-update-nonce' ); ?>
            <input type="submit" class="button" data-title="<?php _e( 'Terminate Employee', 'erp' ); ?>" id="erp-hr-employee-status-update" name="employee_update_status" value="<?php esc_attr_e( 'Update', 'erp' ); ?>">
        </form>
        <?php
    }
    ?>


    <h3><?php _e( 'Employment Status', 'erp' ) ?></h3>
    <?php if ( current_user_can( 'erp_manage_jobinfo' ) ) { ?>
        <a href="#" id="erp-empl-status" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-status" data-title="<?php _e( 'Employment Status', 'erp' ); ?>"><?php _e( 'Update Status', 'erp' ); ?></a>
    <?php } ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'erp' ) ?></th>
                <th><?php _e( 'Employment Status', 'erp' ) ?></th>
                <th><?php _e( 'Comment', 'erp' ) ?></th>
                <th class="action">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( $history['employment'] ) {

                $types = erp_hr_get_employee_types() + ['terminated' => __( 'Terminated', 'erp' ) ];
                foreach ($history['employment'] as $num => $employment_history) {
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <td><?php echo erp_format_date( $employment_history['date'] ); ?></td>
                        <td>
                            <?php echo ( ! empty( $employment_history['employment_status'] ) ) ? wp_kses_post( $employment_history['employment_status'] ) : '--'; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $employment_history['comment'] ) ) ? wp_kses_post( $employment_history['comment'] ) : '--'; ?>
                        </td>
                        <td class="action">
                            <?php if ( current_user_can( 'erp_manage_jobinfo', $employee->id ) ) : ?>
                                <a href="#" class="remove" data-id="<?php echo $employment_history['id']; ?>"><span class="dashicons dashicons-trash"></span></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="4"><?php _e( 'No history found!', 'erp' ); ?></td>
                </tr>
            <?php } ?>

        </tbody>
    </table>

    <hr />

    <?php if ( current_user_can( 'erp_edit_employee', $employee->id ) ) : ?>

        <h3><?php _e( 'Compensation', 'erp' ) ?></h3>
        <?php if ( current_user_can( 'erp_manage_jobinfo' ) ) { ?>
            <a href="#" id="erp-empl-compensation" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-compensation" data-title="<?php _e( 'Update Compensation', 'erp' ); ?>"><?php _e( 'Update Compensation', 'erp' ); ?></a>
        <?php } ?>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e( 'Date', 'erp' ) ?></th>
                    <th><?php _e( 'Pay Rate', 'erp' ) ?></th>
                    <th><?php _e( 'Pay Type', 'erp' ) ?></th>
                    <th><?php _e( 'Change Reason', 'erp' ) ?></th>
                    <th><?php _e( 'Comment', 'erp' ) ?></th>
                    <th class="action">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ( $history['compensation'] ) {

                    foreach ($history['compensation'] as $num => $compensation) {
                        ?>
                        <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                            <td><?php echo erp_format_date( $compensation['date'] ); ?></td>
                            <td>
                                <?php echo ( ! empty( $compensation['pay_rate'] ) ) ? wp_kses_post( $compensation['pay_rate'] ) : '--'; ?>
                            </td>
                            <td>
                                <?php echo ( ! empty( $compensation['pay_type'] ) ) ? wp_kses_post( $compensation['pay_type'] ) : '--'; ?>
                            </td>
                            <td>
                                <?php echo ( ! empty( $compensation['reason'] ) ) ? wp_kses_post( $compensation['reason'] ) : '--'; ?>
                            </td>
                            <td>
                                <?php echo ( ! empty( $compensation['comment'] ) ) ? wp_kses_post( $compensation['comment'] ) : '--'; ?>
                            </td>
                            <td class="action">
                                <?php if ( current_user_can( 'erp_manage_jobinfo', $employee->id ) ) : ?>
                                    <a href="#" class="remove" data-id="<?php echo $compensation['id']; ?>"><span class="dashicons dashicons-trash"></span></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr class="alternate">
                        <td colspan="6"><?php _e( 'No history found!', 'erp' ); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <hr />

    <?php endif; ?>

    <h3><?php _e( 'Job Information', 'erp' ) ?></h3>
    <?php if ( current_user_can( 'erp_manage_jobinfo' ) ) { ?>
        <a href="#" id="erp-empl-jobinfo" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-jobinfo" data-title="<?php _e( 'Update Job Information', 'erp' ); ?>"><?php _e( 'Update Job Information', 'erp' ); ?></a>
    <?php } ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'erp' ) ?></th>
                <th><?php _e( 'Location', 'erp' ) ?></th>
                <th><?php _e( 'Department', 'erp' ) ?></th>
                <th><?php _e( 'Job Title', 'erp' ) ?></th>
                <th><?php _e( 'Reports To', 'erp' ) ?></th>
                <th class="action">&nbsp;</th>
            </tr>
        </thead>
        <tbody>

            <?php
            if ( $history['job'] ) {
                $types = erp_hr_get_pay_type();

                foreach ($history['job'] as $num => $job) {
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <td><?php echo erp_format_date( $job['date'] ); ?></td>
                        <td>
                            <?php echo ( ! empty( $job['location'] ) ) ? wp_kses_post( $job['location'] ) : '--'; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $job['department'] ) ) ? wp_kses_post( $job['department'] ) : '--'; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $job['job_title'] ) ) ? wp_kses_post( $job['job_title'] ) : '--'; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $job['reporting_to'] ) ) ? $job['reporting_to']['link'] : '--'; ?>
                        </td>
                        <td class="action">
                            <?php if ( current_user_can( 'erp_manage_jobinfo', $employee->id ) ) : ?>
                                <a href="#" class="remove" data-id="<?php echo $job['id']; ?>"><span class="dashicons dashicons-trash"></span></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="6"><?php _e( 'No history found!', 'erp' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>
