<h3><?php _e( 'Balances', 'wp-erp' ) ?></h3>

<?php
$policies         = erp_hr_leave_get_policies( erp_get_current_company_id() );
$entitlements     = erp_hr_leave_get_entitlements( array( 'employee_id' => $employee->id ) );
$entitlements_pol = wp_list_pluck( $entitlements, 'policy_id' );

if ( $policies ) {
    ?>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Leave', 'wp-erp' ) ?></th>
                <th><?php _e( 'Current', 'wp-erp' ) ?></th>
                <th><?php _e( 'Scheduled', 'wp-erp' ) ?></th>
                <th><?php _e( 'Available', 'wp-erp' ) ?></th>
                <th><?php _e( 'Accrual Policy', 'wp-erp' ) ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($policies as $num => $policy) {
                // var_dump( $policy );

                $key = array_search( $policy->id, $entitlements_pol );
                $en = false;

                if ( false !== $key ) {
                    $en = $entitlements[ $key ];
                }
                ?>

            <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                <td><?php echo esc_html( $policy->name ); ?></td>
                <td><?php echo $en ? number_format_i18n( $en->days ) : '-'; ?></td>
                <td><?php echo $en ? '' : '-'; ?></td>
                <td><?php echo $en ? '' : '-'; ?></td>
                <td><?php echo $en ? '-' : __( 'No Policy', 'wp-erp' ); ?></td>
            </tr>

            <?php } ?>
        </tbody>

    </table>

<?php } ?>