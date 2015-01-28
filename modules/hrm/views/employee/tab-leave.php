<h3><?php _e( 'Balances', 'wp-erp' ) ?></h3>

<?php
$policies         = erp_hr_leave_get_policies( erp_get_current_company_id() );
$entitlements     = erp_hr_leave_get_entitlements( array( 'employee_id' => $employee->id ) );
$entitlements_pol = wp_list_pluck( $entitlements, 'policy_id' );
$balance          = erp_hr_leave_get_balance( $employee->id );

if ( $policies ) {
    ?>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Leave', 'wp-erp' ) ?></th>
                <th><?php _e( 'Current', 'wp-erp' ) ?></th>
                <th><?php _e( 'Scheduled', 'wp-erp' ) ?></th>
                <th><?php _e( 'Available', 'wp-erp' ) ?></th>
                <th><?php _e( 'Period', 'wp-erp' ) ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($policies as $num => $policy) {
                // var_dump( $policy );

                $key       = array_search( $policy->id, $entitlements_pol );
                $en        = false;
                $name      = esc_html( $policy->name );
                $current   = 0;
                $scheduled = 0;
                $available = 0;
                $accrual   = 0;

                if ( array_key_exists( $policy->id, $balance ) ) {
                    $current   = $balance[ $policy->id ]['entitlement'];
                    $scheduled = $balance[ $policy->id ]['scheduled'];
                    $available = $balance[ $policy->id ]['entitlement'] - $balance[ $policy->id ]['total'];
                }

                if ( false !== $key ) {
                    $en = $entitlements[ $key ];
                }
                ?>

            <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                <td><?php echo esc_html( $policy->name ); ?></td>
                <td><?php echo $en ? sprintf( __( '%d days', 'wp-erp' ), number_format_i18n( $en->days ) ) : '-'; ?></td>
                <td><?php echo $scheduled ? sprintf( __( '%d days', 'wp-erp' ), number_format_i18n( $scheduled ) ) : '-'; ?></td>
                <td>
                    <?php
                    if ( $available < 0 ) {
                        printf( '<span class="red">%d %s</span>', number_format_i18n( $available ), __( 'days', 'wp-erp' ) );
                    } elseif ( $available > 0 ) {
                        printf( '<span class="green">%d %s</span>', number_format_i18n( $available ), __( 'days', 'wp-erp' ) );
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ( $en ) {
                        printf( '%s - %s', erp_format_date( $en->from_date ), erp_format_date( $en->to_date ) );
                    } else {
                        _e( 'No Policy', 'wp-erp' );
                    } ?>
                </td>
            </tr>

            <?php } ?>
        </tbody>

    </table>

<?php } ?>

<h3><?php _e( 'History', 'wp-erp' ) ?></h3>

<?php
$cur_year   = date( 'Y' );
$company_id = erp_get_current_company_id();
$requests   = erp_hr_leave_get_requests( array(
    'year'    => $cur_year,
    'user_id' => $employee->id,
    'status'  => 1,
    'year'    => $cur_year,
    'orderby' => 'req.start_date'
) );
?>

<form action="#">
    <?php erp_html_form_input( array(
        'name'     => 'leave_policy',
        'type'     => 'select',
        'options'  => array( 'all' => __( 'All Policy', 'wp-erp' ) ) + erp_hr_leave_get_policies_dropdown_raw( $company_id )
    ) ); ?>

    <select name="year" id="year">
        <?php for ( $i = $cur_year; $i > $cur_year - 5; $i-- ) { ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
        <?php } ?>
    </select>

    <?php submit_button( __( 'Filter', 'wp-erp' ), 'secondary', 'submit', false ); ?>
</form>

<table class="widefat">
    <thead>
        <tr>
            <th><?php _e( 'Date', 'wp-erp' ) ?></th>
            <th><?php _e( 'Policy', 'wp-erp' ) ?></th>
            <th><?php _e( 'Description', 'wp-erp' ) ?></th>
            <th><?php _e( 'Days', 'wp-erp' ) ?></th>
        </tr>
    </thead>

    <tbody>
        <?php
        if ( $requests ) {

            foreach ($requests as $num => $request) {
                ?>
                <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                    <td>
                        <?php
                        printf( '%s - %s', erp_format_date( $request->start_date, 'd M' ), erp_format_date( $request->end_date, 'd M' ) );
                        ?>
                    </td>
                    <td><?php echo esc_html( $request->policy_name ); ?></td>
                    <td><?php echo !empty( $request->reason ) ? esc_html( $request->reason ) : '-'; ?></td>
                    <td><?php echo number_format_i18n( $request->days ); ?></td>
                </tr>

            <?php } ?>

        <?php } else { ?>
            <tr class="alternate">
                <td colspan="4">
                    <?php _e( 'No history found!', 'wp-erp' ); ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>