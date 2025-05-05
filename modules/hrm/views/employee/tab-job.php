<div class="job-tab-wrap">
    <?php $histories = $employee->get_job_histories(); ?>

    <h3><?php esc_html_e( 'Employee Status', 'erp' ); ?></h3>
    <?php if ( current_user_can( 'erp_manage_jobinfo' ) ) { ?>
        <a href="#" id="erp-empl-status" class="action button" data-id="<?php echo esc_attr( $employee->get_user_id() ); ?>"
            data-template="erp-employment-status"
            data-title="<?php esc_attr_e( 'Employee Status', 'erp' ); ?>"><?php esc_html_e( 'Update Status', 'erp' ); ?></a>
    <?php } ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Date', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Employee Status', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Comment', 'erp' ); ?></th>
                <th class="action">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $histories['employee'] ) ) {
                $statuses = erp_hr_get_employee_statuses();

                foreach ( $histories['employee'] as $num => $employment_history ) {
                    ?>
                    <tr class="<?php echo $num % 2 === 0 ? 'alternate' : 'odd'; ?>">
                        <td>
                            <?php echo esc_html( erp_format_date( $employment_history['date'] ) ); ?>
                            <?php if ( 0 === $num ) : ?>
                                <span class="active_dot"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $employment_history['status'] ) && array_key_exists( $employment_history['status'], $statuses ) ) ? wp_kses_post( $statuses[ $employment_history['status'] ] ) : '--'; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $employment_history['comments'] ) ) ? wp_kses_post( $employment_history['comments'] ) : '--'; ?>
                        </td>
                        <td class="action">
                            <?php if ( current_user_can( 'erp_manage_jobinfo', $employee->get_user_id() ) && ( 0 !== $num ) ) { ?>
                                <a href="#" class="remove" data-id="<?php echo esc_attr( $employment_history['id'] ); ?>"><span class="dashicons dashicons-trash"></span></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="4"><?php esc_html_e( 'No history found!', 'erp' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <hr />

    <h3><?php esc_html_e( 'Employment Type', 'erp' ); ?></h3>
    <?php if ( current_user_can( 'erp_manage_jobinfo' ) ) { ?>
        <a href="#" id="erp-empl-type" class="action button" data-id="<?php echo esc_attr( $employee->get_user_id() ); ?>"
            data-template="erp-employment-type"
            data-title="<?php esc_html_e( 'Employment Type', 'erp' ); ?>"><?php esc_html_e( 'Update Type', 'erp' ); ?></a>
    <?php } ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Date', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Employment Type', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Comment', 'erp' ); ?></th>
                <th class="action">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $histories['employment'] ) ) {
                $types = erp_hr_get_employee_types() + [ 'terminated' => __( 'Terminated', 'erp' ) ];

                foreach ( $histories['employment'] as $num => $employment_history ) {
                    ?>
                    <tr class="<?php echo $num % 2 === 0 ? 'alternate' : 'odd'; ?>">
                        <td>
                            <?php echo esc_html( erp_format_date( $employment_history['date'] ) ); ?>
                            <?php if ( 0 === $num ) : ?>
                                <span class="active_dot"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $employment_history['type'] ) && array_key_exists( $employment_history['type'], $types ) ) ? wp_kses_post( $types[ $employment_history['type'] ] ) : '--'; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $employment_history['comments'] ) ) ? wp_kses_post( $employment_history['comments'] ) : '--'; ?>
                        </td>
                        <td class="action">
                            <?php if ( current_user_can( 'erp_manage_jobinfo', $employee->get_user_id() ) ) : ?>
                                <?php if ( 0 !== $num ) : ?>
                                    <a href="#"
                                        class="remove"
                                        data-id="<?php echo esc_attr( $employment_history['id'] ); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </a>
                                <?php else : ?>
                                    <a href="#"
                                        class="edit"
                                        data-id="<?php echo esc_attr( $employment_history['id'] ); ?>"
                                        data-title="<?php esc_html_e( 'Employment Type History', 'erp' ); ?>"
                                        data-template="erp-employment-type-history">
                                        <span class="dashicons dashicons-edit-large"></span>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="4"><?php esc_html_e( 'No history found!', 'erp' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <hr />

    <?php if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) { ?>

        <h3><?php esc_html_e( 'Compensation', 'erp' ); ?></h3>
        <?php if ( current_user_can( 'erp_manage_jobinfo' ) ) { ?>
            <a href="#" id="erp-empl-compensation" class="action button" data-id="<?php echo esc_attr( $employee->get_user_id() ); ?>" data-template="erp-employment-compensation" data-title="<?php esc_html_e( 'Update Compensation', 'erp' ); ?>"><?php esc_html_e( 'Update Compensation', 'erp' ); ?></a>
        <?php } ?>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'erp' ); ?></th>
                    <?php
                    $hide_pay_rate = get_option( 'erp_hrm_hide_pay_rate', 'no' );
                    if( $hide_pay_rate === 'yes' ){ ?>
                        <th onclick="document.querySelectorAll('.pay-rate').forEach(el => el.classList.toggle('blur'))"><?php esc_html_e( 'Pay Rate', 'erp' ); ?></th>
                   <?php } else {?>
                    <th><?php esc_html_e( 'Pay Rate', 'erp' ); ?></th>
                    <?php } ?>
                    <th><?php esc_html_e( 'Pay Type', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Change Reason', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Comment', 'erp' ); ?></th>
                    <th class="action">&nbsp;</th>
                </tr>
            </thead>
            <?php if( $hide_pay_rate === 'yes' ){ ?>
            <style>
                .pay-rate.blur {
                    filter: blur(6px);
                }
            </style>
            <?php } ?>
            <tbody>
                <?php
                if ( ! empty( $histories['compensation'] ) ) {
                    $pay_type = erp_hr_get_pay_type();
                    $reason   = erp_hr_get_pay_change_reasons();

                    foreach ( $histories['compensation'] as $num => $compensation ) {
                        ?>
                        <tr class="<?php echo $num % 2 === 0 ? 'alternate' : 'odd'; ?>">
                            <td>
                                <?php echo esc_html( erp_format_date( $compensation['date'] ) ); ?>
                                <?php if ( 0 === $num ) : ?>
                                    <span class="active_dot"></span>
                                <?php endif; ?>
                            </td>
                            <?php if( $hide_pay_rate  === 'yes' ){ ?>
                            <td style="display: flex; align-items: center;">
                                <span class="dashicons dashicons-visibility" style="cursor: pointer; margin-right: 5px;" onclick="this.parentNode.querySelector('.pay-rate').classList.toggle('blur')"></span>
                                <span class="pay-rate blur"><?php echo ( ! empty( $compensation['pay_rate'] ) ) ? wp_kses_post( $compensation['pay_rate'] ) : '--'; ?></span>
                            </td>
                            <?php } else { ?>
                            <td>
                                <?php echo ( ! empty( $compensation['pay_rate'] ) ) ? wp_kses_post( $compensation['pay_rate'] ) : '--'; ?>
                            </td>
                            <?php } ?>
                            <td>
                                <?php echo ( ! empty( $compensation['pay_type'] ) && array_key_exists( $compensation['pay_type'], $pay_type ) ) ? wp_kses_post( $pay_type[ $compensation['pay_type'] ] ) : '--'; ?>
                            </td>
                            <td>
                                <?php echo ( ! empty( $compensation['reason'] ) && array_key_exists( $compensation['reason'], $reason ) ) ? wp_kses_post( $reason[ $compensation['reason'] ] ) : '--'; ?>
                            </td>
                            <td>
                                <?php echo ( ! empty( $compensation['comment'] ) ) ? wp_kses_post( $compensation['comment'] ) : '--'; ?>
                            </td>
                            <td class="action">
                                <?php if ( current_user_can( 'erp_manage_jobinfo', $employee->get_user_id() ) ) : ?>
                                    <?php if ( 0 !== $num ) : ?>
                                        <a href="#"
                                            class="remove"
                                            data-id="<?php echo esc_attr( $compensation['id'] ); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </a>
                                    <?php else : ?>
                                        <a href="#"
                                            class="edit"
                                            data-id="<?php echo esc_attr( $compensation['id'] ); ?>"
                                            data-title="<?php esc_html_e( 'Compensation History', 'erp' ); ?>"
                                            data-template="erp-employment-compensation-history">
                                            <span class="dashicons dashicons-edit-large"></span>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr class="alternate">
                        <td colspan="6"><?php esc_html_e( 'No history found!', 'erp' ); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <hr />

    <?php } ?>

    <h3><?php esc_html_e( 'Job Information', 'erp' ); ?></h3>
    <?php if ( current_user_can( 'erp_manage_jobinfo' ) ) { ?>
        <a href="#" id="erp-empl-jobinfo" class="action button" data-id="<?php echo esc_attr( $employee->get_user_id() ); ?>" data-template="erp-employment-jobinfo" data-title="<?php esc_html_e( 'Update Job Information', 'erp' ); ?>"><?php esc_html_e( 'Update Job Information', 'erp' ); ?></a>
    <?php } ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Date', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Location', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Department', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Job Title', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Reports To', 'erp' ); ?></th>
                <th class="action">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ( ! empty( $histories['job'] ) ) {
            $types = erp_hr_get_pay_type();

            foreach ( $histories['job'] as $num => $row ) {
                ?>
                <tr class="<?php echo $num % 2 === 0 ? 'alternate' : 'odd'; ?>">
                    <td>
                        <?php echo esc_html( erp_format_date( $row['date'] ) ); ?>
                        <?php if ( 0 === $num ) : ?>
                            <span class="active_dot"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $locations = erp_company_get_location_dropdown_raw();

                        if ( ! empty( $row['location'] ) && array_key_exists( $row['location'], $locations ) ) {
                            echo esc_html( $locations[ $row['location'] ] );
                        } else {
                            echo esc_html( erp_get_company_default_location_name() );
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $departments = erp_hr_get_departments_dropdown_raw();

                        if ( empty( $row['department'] ) ) {
                            echo '--';
                        } elseif ( array_key_exists( $row['department'], $departments ) ) {
                            echo esc_html( $departments[ $row['department'] ] );
                        } else {
                            echo esc_html( $row['department'] );
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $designations = erp_hr_get_designation_dropdown_raw();

                        if ( empty( $row['designation'] ) ) {
                            echo '--';
                        } elseif ( array_key_exists( $row['designation'], $designations ) ) {
                            echo esc_html( $designations[ $row['designation'] ] );
                        } else {
                            echo esc_html( $row['designation'] );
                        }
                        ?>
                    </td>
                    <td>
                    <?php
                    if ( ! empty( $row['reporting_to'] ) ) {
                        $emp = new \WeDevs\ERP\HRM\Employee( intval( $row['reporting_to'] ) );

                        if ( $emp->is_employee() ) {
                            echo wp_kses_post( $emp->get_link() );
                        }
                    }
                    ?>
                    </td>
                    <td class="action">
                        <?php if ( current_user_can( 'erp_manage_jobinfo', $employee->get_user_id() ) ) : ?>
                            <?php if ( 0 !== $num ) : ?>
                                <a href="#"
                                    class="remove"
                                    data-id="<?php echo esc_attr( $row['id'] ); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            <?php else : ?>
                                <a href="#"
                                    class="edit"
                                    data-id="<?php echo esc_attr( $row['id'] ); ?>"
                                    data-title="<?php esc_html_e( 'Job Info History', 'erp' ); ?>"
                                    data-template="erp-employment-job-info-history">
                                    <span class="dashicons dashicons-edit-large"></span>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr class="alternate">
                <td colspan="6"><?php esc_html_e( 'No history found!', 'erp' ); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>
