<div class="job-tab-wrap">

    <?php $history = $employee->get_history(); ?>

    <h3><?php _e( 'Employment Status', 'wp-erp' ) ?></h3>

    <a href="#" id="erp-empl-status" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-status" data-title="<?php _e( 'Employment Status', 'wp-erp' ); ?>"><?php _e( 'Update Status', 'wp-erp' ); ?></a>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'wp-erp' ) ?></th>
                <th><?php _e( 'Employment Status', 'wp-erp' ) ?></th>
                <th><?php _e( 'Comment', 'wp-erp' ) ?></th>
                <th class="action">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( $history['employment'] ) {
                $types = erp_hr_get_employee_types();

                foreach ($history['employment'] as $num => $row) {
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <td><?php echo erp_format_date( $row->date ); ?></td>
                        <td>
                            <?php if ( ! empty( $row->type ) && array_key_exists( $row->type, $types ) ) {
                                echo $types[ $row->type ];
                            } ?>
                        </td>
                        <td><?php echo ( ! empty( $row->comment ) ) ? wp_kses_post( $row->comment ) : '--'; ?></td>
                        <td class="action">
                            <a href="#" class="remove" data-id="<?php echo $row->id; ?>"><span class="dashicons dashicons-trash"></span></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="4"><?php _e( 'No history found!', 'wp-erp' ); ?></td>
                </tr>
            <?php } ?>

        </tbody>
    </table>

    <hr />

    <h3><?php _e( 'Compensation', 'wp-erp' ) ?></h3>

    <a href="#" id="erp-empl-compensation" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-compensation" data-title="<?php _e( 'Update Compensation', 'wp-erp' ); ?>"><?php _e( 'Update Compensation', 'wp-erp' ); ?></a>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'wp-erp' ) ?></th>
                <th><?php _e( 'Pay Rate', 'wp-erp' ) ?></th>
                <th><?php _e( 'Pay Type', 'wp-erp' ) ?></th>
                <th><?php _e( 'Change Reason', 'wp-erp' ) ?></th>
                <th><?php _e( 'Comment', 'wp-erp' ) ?></th>
                <th class="action">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( $history['compensation'] ) {
                $types = erp_hr_get_pay_type();

                foreach ($history['compensation'] as $num => $row) {
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <td><?php echo erp_format_date( $row->date ); ?></td>
                        <td><?php echo $row->type; ?></td>
                        <td>
                            <?php if ( ! empty( $row->category ) && array_key_exists( $row->category, $types ) ) {
                                echo $types[ $row->category ];
                            } ?>
                        </td>
                        <td><?php echo ( ! empty( $row->data ) ) ? $row->data : '--'; ?></td>
                        <td><?php echo ( ! empty( $row->comment ) ) ? wp_kses_post( $row->comment ) : '--'; ?></td>
                        <td class="action">
                            <a href="#" class="remove" data-id="<?php echo $row->id; ?>"><span class="dashicons dashicons-trash"></span></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="6"><?php _e( 'No history found!', 'wp-erp' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <hr />
    <h3><?php _e( 'Job Information', 'wp-erp' ) ?></h3>

    <a href="#" id="erp-empl-jobinfo" class="action button" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-jobinfo" data-title="<?php _e( 'Update Compensation', 'wp-erp' ); ?>"><?php _e( 'Update Job Information', 'wp-erp' ); ?></a>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'wp-erp' ) ?></th>
                <th><?php _e( 'Location', 'wp-erp' ) ?></th>
                <th><?php _e( 'Department', 'wp-erp' ) ?></th>
                <th><?php _e( 'Job Title', 'wp-erp' ) ?></th>
                <th><?php _e( 'Reports To', 'wp-erp' ) ?></th>
                <th class="action">&nbsp;</th>
            </tr>
        </thead>
        <tbody>

            <?php
            if ( $history['job'] ) {
                $types = erp_hr_get_pay_type();

                foreach ($history['job'] as $num => $row) {
                    ?>
                    <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                        <td><?php echo erp_format_date( $row->date ); ?></td>
                        <td>
                            <?php echo ( ! empty( $row->type ) ) ? $row->type : '--'; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $row->category ) ) ? $row->category : '--'; ?>
                        </td>
                        <td>
                            <?php echo ( ! empty( $row->comment ) ) ? $row->comment : '--'; ?>
                        </td>
                        <td>
                            <?php if ( ! empty( $row->data ) ) {
                                $emp = new \WeDevs\ERP\HRM\Employee( intval( $row->data ) );
                                if ( $emp->id ) {
                                    echo $emp->get_link();
                                }
                            } ?>
                        </td>
                        <td class="action">
                            <a href="#" class="remove" data-id="<?php echo $row->id; ?>"><span class="dashicons dashicons-trash"></span></a>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr class="alternate">
                    <td colspan="6"><?php _e( 'No history found!', 'wp-erp' ); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>