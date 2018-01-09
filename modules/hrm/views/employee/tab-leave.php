<h3><?php _e( 'Balances', 'erp' ) ?></h3>

<?php
$balance = $employee->get_leave_summary();

if ( $balance ) {
    ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Leave', 'erp' ) ?></th>
                <th><?php _e( 'Days', 'erp' ) ?></th>
                <th><?php _e( 'Spent', 'erp' ) ?></th>
                <th><?php _e( 'Scheduled', 'erp' ) ?></th>
                <th><?php _e( 'Available', 'erp' ) ?></th>
                <th><?php _e( 'Period', 'erp' ) ?></th>
            </tr>
        </thead>

        <tbody>

            <?php if( $balance ):?>
            <?php foreach ($balance as $num => $entitlement) { ?>

            <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                <td><?php echo esc_html( $entitlement->policy ); ?></td>
                <td><?php echo $entitlement->days ? sprintf( __( '%d days', 'erp' ), number_format_i18n( $entitlement->days ) ) : '-'; ?></td>
                <td><?php echo ($entitlement->spent - $entitlement->scheduled) ? sprintf( __( '%d days', 'erp' ), number_format_i18n( ($entitlement->spent - $entitlement->scheduled) ) ) : '-'; ?></td>
                <td><?php echo $entitlement->scheduled ? sprintf( __( '%d days', 'erp' ), number_format_i18n( $entitlement->scheduled ) ) : '-'; ?></td>
                <td>
                    <?php
                    if ( $entitlement->available < 0 ) {
                        printf( '<span class="red">%d %s</span>', number_format_i18n( $entitlement->available ), __( 'days', 'erp' ) );
                    } elseif ( $entitlement->available > 0 ) {
                        printf( '<span class="green">%d %s</span>', number_format_i18n( $entitlement->available ), __( 'days', 'erp' ) );
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    printf( '%s - %s', erp_format_date( $entitlement->from_date ), erp_format_date( $entitlement->to_date ) );
                    ?>
                </td>
            </tr>

            <?php } ?>
            <?php else :?>
            <tr class="alternate">
                <td colspan="4"><?php _e( 'No leave policy found!', 'erp' ); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>

    </table>

<?php } ?>

<h3><?php _e( 'History', 'erp' ) ?></h3>

<?php
$cur_year   = date( 'Y' );
$requests   = $employee->get_leave_requests();
?>

<form action="#" id="erp-hr-empl-leave-history">
    <?php erp_html_form_input( array(
        'name'     => 'leave_policy',
        'type'     => 'select',
        'options'  => array( 'all' => __( 'All Policy', 'erp' ) ) + erp_hr_leave_get_policies_dropdown_raw()
    ) ); ?>

    <select name="year" id="year">
        <?php for ( $i = $cur_year; $i > $cur_year - 5; $i-- ) { ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
        <?php } ?>
    </select>

    <input type="hidden" name="employee_id" value="<?php echo esc_attr( $employee->get_user_id() ); ?>">

    <?php wp_nonce_field( 'erp-hr-empl-leave-history' ); ?>
    <?php submit_button( __( 'Filter', 'erp' ), 'secondary', 'submit', false ); ?>
</form>

<table class="widefat" id="erp-hr-empl-leave-history-table">
    <thead>
        <tr>
            <th><?php _e( 'Date', 'erp' ) ?></th>
            <th><?php _e( 'Policy', 'erp' ) ?></th>
            <th><?php _e( 'Description', 'erp' ) ?></th>
            <th><?php _e( 'Days', 'erp' ) ?></th>
        </tr>
    </thead>

    <tbody>
        <?php include dirname( __FILE__ ) . '/tab-leave-history.php'; ?>
    </tbody>
</table>
