<div class="performance-tab-wrap">

    <?php $performance = $employee->get_performance(); ?>

    <?php $performance_rating = erp_performance_rating(); ?>

    <h3><?php _e( 'Performance Reviews', 'wp-erp' ); ?></h3>

    <a href="#" id="erp-empl-performance-reviews" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-performance-reviews" data-title="<?php _e( 'Performance Reviews', 'wp-erp' ); ?>"><span class="dashicons dashicons-plus erp-performance-dashicon"></span> <?php _e( 'Add Performance Reviews', 'wp-erp' ); ?></a>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'wp-erp' ) ?></th>
                <th><?php _e( 'Reporting To', 'wp-erp' ) ?></th>
                <th><?php _e( 'Job Knowledge', 'wp-erp' ) ?></th>
                <th><?php _e( 'Work Quality', 'wp-erp' ) ?></th>
                <th><?php _e( 'Attendance/Punctuality', 'wp-erp' ) ?></th>
                <th><?php _e( 'Communication/Listening', 'wp-erp' ) ?></th>
                <th><?php _e( 'Dependablity', 'wp-erp' ) ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
        <?php
            if ( $performance['reviews'] ) {

                foreach ( $performance['reviews'] as $num => $row ) {
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <td>
                            <?php echo erp_format_date( $row->performance_date ); ?>
                        </td>

                        <td>
                            <?php
                                $reporting_user = new \WeDevs\ERP\HRM\Employee( intval( $row->reporting_to ) );
                                echo $reporting_user->get_full_name();
                            ?>
                        </td>

                        <td><?php echo isset( $performance_rating[$row->job_knowledge] ) ? $performance_rating[$row->job_knowledge] : '-'; ?></td>

                        <td><?php echo isset( $performance_rating[$row->work_quality] ) ? $performance_rating[$row->work_quality] : '-'; ?></td>

                        <td><?php echo isset( $performance_rating[$row->attendance] ) ? $performance_rating[$row->attendance] : '-'; ?></td>

                        <td><?php echo isset( $performance_rating[$row->communication] ) ? $performance_rating[$row->communication] : '-'; ?></td>

                        <td><?php echo isset( $performance_rating[$row->dependablity] ) ? $performance_rating[$row->dependablity] : '-'; ?></td>

                        <td class="action">
                            <a href="#" class="performance-remove" data-id="<?php echo $row->id; ?>"><span class="dashicons dashicons-trash"></span></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="4"><?php _e( 'No performance reviews found!', 'wp-erp' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3><?php _e( 'Performance Comments', 'wp-erp' ); ?></h3>

    <a href="#" id="erp-empl-performance-comments" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-performance-comments" data-title="<?php _e( 'Performance Comments', 'wp-erp' ); ?>"><span class="dashicons dashicons-plus erp-performance-dashicon"></span> <?php _e( 'Add Performance Comments', 'wp-erp' ); ?></a>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'wp-erp' ) ?></th>
                <th><?php _e( 'Reviewer', 'wp-erp' ) ?></th>
                <th><?php _e( 'Comments', 'wp-erp' ) ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            <?php
            if ( $performance['comments'] ) {

                foreach ( $performance['comments'] as $num => $row ) {
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <td>
                            <?php echo erp_format_date( $row->performance_date ); ?>
                        </td>

                        <td>
                            <?php
                                $reporting_user = new \WeDevs\ERP\HRM\Employee( intval( $row->reviewer ) );
                                echo $reporting_user->get_full_name();
                            ?>
                        </td>

                        <td><?php echo esc_textarea( $row->comments ); ?></td>

                        <td class="action">
                            <a href="#" class="performance-remove" data-id="<?php echo $row->id; ?>"><span class="dashicons dashicons-trash"></span></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="4"><?php _e( 'No performance comments found!', 'wp-erp' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3><?php _e( 'Performance Goals', 'wp-erp' ); ?></h3>

    <a href="#" id="erp-empl-performance-goals" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-performance-goals" data-title="<?php _e( 'Performance Goals', 'wp-erp' ); ?>"><span class="dashicons dashicons-plus erp-performance-dashicon"></span> <?php _e( 'Add Performance Goals', 'wp-erp' ); ?></a>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Set Date', 'wp-erp' ) ?></th>
                <th><?php _e( 'Completion Date', 'wp-erp' ) ?></th>
                <th><?php _e( 'Goal Description', 'wp-erp' ) ?></th>
                <th><?php _e( 'Employee Assessment', 'wp-erp' ) ?></th>
                <th><?php _e( 'Supervisor', 'wp-erp' ) ?></th>
                <th><?php _e( 'Supervisor Assessment', 'wp-erp' ) ?></th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            <?php
            if ( $performance['goals'] ) {

                foreach ( $performance['goals'] as $num => $row ) {
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <td><?php echo erp_format_date( $row->performance_date ); ?></td>
                        <td><?php echo erp_format_date( $row->completion_date ); ?></td>
                        <td><?php echo esc_textarea( $row->goal_description ); ?></td>
                        <td><?php echo esc_textarea( $row->employee_assessment ); ?></td>
                        <td>
                            <?php
                                $reporting_user = new \WeDevs\ERP\HRM\Employee( intval( $row->supervisor ) );
                                echo $reporting_user->get_full_name();
                            ?>
                        </td>
                        <td><?php echo esc_textarea( $row->supervisor_assessment ); ?></td>

                        <td class="action">
                            <a href="#" class="performance-remove" data-id="<?php echo $row->id; ?>"><span class="dashicons dashicons-trash"></span></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="4"><?php _e( 'No performance goals found!', 'wp-erp' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>