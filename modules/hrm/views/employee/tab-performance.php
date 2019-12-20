<div class="performance-tab-wrap">

    <?php $performance = $employee->get_performances(); ?>
    <?php $performance_rating = erp_performance_rating(); ?>

    <h3><?php esc_html_e( 'Performance Reviews', 'erp' ); ?></h3>

    <?php
    $department_lead_id = erp_hr_get_department_lead_by_user( $employee->get_user_id() );

    if ( current_user_can( 'erp_create_review', $employee->get_user_id() ) ||
        ( get_current_user_id() === $department_lead_id )
    ) {
        ?>
        <a href="#" id="erp-empl-performance-reviews" class="action button"
           data-id="<?php echo esc_html( $employee->get_user_id() ); ?>" data-template="erp-employment-performance-reviews"
           data-title="<?php esc_html_e( 'Performance Reviews', 'erp' ); ?>"><span
                class="dashicons dashicons-plus erp-performance-dashicon"></span> <?php esc_html_e( 'Add Performance Reviews', 'erp' ); ?>
        </a>
        <?php
    }
    ?>


    <table class="widefat">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Date', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Reporting To', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Job Knowledge', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Work Quality', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Attendance/Punctuality', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Communication/Listening', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Dependability', 'erp' ) ?></th>
            <th>&nbsp;</th>
        </tr>
        </thead>

        <tbody>
        <?php

        if ( isset( $performance['reviews'] ) && ! empty( $performance['reviews'] ) ) {
            foreach ( $performance['reviews'] as $num => $row ) {
                ?>
                <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                    <td>
                        <?php echo esc_html( erp_format_date( $row->performance_date ) ); ?>
                    </td>
                    <td>
                        <?php
                        $reporting_user = new \WeDevs\ERP\HRM\Employee( intval( $row->reporting_to ) );
                        if ( $reporting_user->is_employee() ) {
                            echo esc_html( $reporting_user->get_full_name() );
                        }
                        ?>
                    </td>
                    <td><?php echo isset( $performance_rating[ $row->job_knowledge ] ) ? esc_html( $performance_rating[ $row->job_knowledge ] ) : '-'; ?></td>

                    <td><?php echo isset( $performance_rating[ $row->work_quality ] ) ? esc_html( $performance_rating[ $row->work_quality ] ) : '-'; ?></td>

                    <td><?php echo isset( $performance_rating[ $row->attendance ] ) ? esc_html( $performance_rating[ $row->attendance ] ) : '-'; ?></td>

                    <td><?php echo isset( $performance_rating[ $row->communication ] ) ? esc_html( $performance_rating[ $row->communication ] ) : '-'; ?></td>

                    <td><?php echo isset( $performance_rating[ $row->dependablity ] ) ? esc_html( $performance_rating[ $row->dependablity ] ) : '-'; ?></td>

                    <td class="action">
                        <?php if ( current_user_can( 'erp_delete_review', $employee->get_user_id() ) ||
                                    ( get_current_user_id() === $department_lead_id )
                                ) { ?>
                            <a href="#" class="performance-remove" data-userid="<?php echo esc_html( $employee->get_user_id() ); ?>" data-id="<?php echo esc_html( $row->id ); ?>"><span
                                    class="dashicons dashicons-trash"></span></a>
                        <?php } ?>
                    </td>
                </tr>
                <?php
            }

        } else { ?>
            <tr class="alternate">
                <td colspan="8"><?php esc_html_e( 'No performance reviews found!', 'erp' ); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <h3><?php esc_html_e( 'Performance Comments', 'erp' ); ?></h3>

    <?php

    if ( current_user_can( 'erp_create_review', $employee->get_user_id() ) ||
        ( get_current_user_id() === $department_lead_id )
    ) { ?>
        <a href="#" id="erp-empl-performance-comments" class="action button"
           data-id="<?php echo esc_html( $employee->get_user_id() ); ?>" data-template="erp-employment-performance-comments"
           data-title="<?php esc_html_e( 'Performance Comments', 'erp' ); ?>"><span
                class="dashicons dashicons-plus erp-performance-dashicon"></span> <?php esc_html_e( 'Add Performance Comments', 'erp' ); ?>
        </a>
    <?php } ?>

    <table class="widefat">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Date', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Reviewer', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Comments', 'erp' ) ?></th>
            <th>&nbsp;</th>
        </tr>
        </thead>

        <tbody>
        <?php
        if ( isset( $performance['comments'] ) ) {
            foreach ( $performance['comments'] as $num => $row ) {
                ?>
                <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                    <td>
                        <?php echo esc_html( erp_format_date( $row->performance_date ) ); ?>
                    </td>

                    <td>
                        <?php
                        $reporting_user = new \WeDevs\ERP\HRM\Employee( intval( $row->reviewer ) );
                        if ( $reporting_user->is_employee() ) {
                            echo esc_html( $reporting_user->get_full_name() );
                        }
                        ?>
                    </td>

                    <td><?php echo esc_textarea( $row->comments ); ?></td>

                    <td class="action">
                        <?php if ( current_user_can( 'erp_delete_review', $employee->get_user_id() ) ||
                                    ( get_current_user_id() === $department_lead_id )
                                ) { ?>
                            <a href="#" class="performance-remove" data-userid="<?php echo esc_html( $employee->get_user_id() ); ?>" data-id="<?php echo esc_html( $row->id ); ?>"><span
                                    class="dashicons dashicons-trash"></span></a>
                        <?php } ?>
                    </td>

                </tr>
                <?php
            }
        } else {
            ?>
            <tr class="alternate">
                <td colspan="4"><?php esc_html_e( 'No performance comments found!', 'erp' ); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <h3><?php esc_html_e( 'Performance Goals', 'erp' ); ?></h3>
    <?php if ( current_user_can( 'erp_create_review', $employee->get_user_id() ) ||
        ( get_current_user_id() === $department_lead_id )
    ) { ?>
        <a href="#" id="erp-empl-performance-goals" class="action button" data-id="<?php echo esc_html( $employee->get_user_id() ); ?>"
           data-template="erp-employment-performance-goals"
           data-title="<?php esc_html_e( 'Performance Goals', 'erp' ); ?>"><span
                class="dashicons dashicons-plus erp-performance-dashicon"></span> <?php esc_html_e( 'Add Performance Goals', 'erp' ); ?>
        </a>
    <?php } ?>
    <table class="widefat">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Set Date', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Completion Date', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Goal Description', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Employee Assessment', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Supervisor', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Supervisor Assessment', 'erp' ) ?></th>
            <th>&nbsp;</th>
        </tr>
        </thead>

        <tbody>
        <?php
        if ( isset( $performance['goals'] ) ) {

            foreach ( $performance['goals'] as $num => $row ) {
                ?>
                <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                    <td><?php echo esc_html( erp_format_date( $row->performance_date ) ); ?></td>
                    <td><?php echo esc_html( erp_format_date( $row->completion_date ) ); ?></td>
                    <td><?php echo esc_textarea( $row->goal_description ); ?></td>
                    <td><?php echo esc_textarea( $row->employee_assessment ); ?></td>
                    <td>
                        <?php
                        $reporting_user = new \WeDevs\ERP\HRM\Employee( intval( $row->supervisor ) );
                        if ( $reporting_user->is_employee() ) {
                            echo esc_html( $reporting_user->get_full_name() );
                        }
                        ?>
                    </td>
                    <td><?php echo esc_textarea( $row->supervisor_assessment ); ?></td>

                    <td class="action">
                        <?php if ( current_user_can( 'erp_delete_review', $employee->get_user_id() ) ||
                                    ( get_current_user_id() === $department_lead_id )
                                ) { ?>
                            <a href="#" class="performance-remove" data-userid="<?php echo esc_html( $employee->get_user_id() ); ?>" data-id="<?php echo esc_html( $row->id ); ?>"><span
                                    class="dashicons dashicons-trash"></span></a>
                        <?php } ?>
                    </td>

                </tr>
                <?php
            }
        } else {
            ?>
            <tr class="alternate">
                <td colspan="7"><?php esc_html_e( 'No performance goals found!', 'erp' ); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div>
