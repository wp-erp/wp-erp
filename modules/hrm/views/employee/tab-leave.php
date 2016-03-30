<h3><?php _e( 'Balances', 'erp' ) ?></h3>

<?php
$policies         = erp_hr_leave_get_policies();
$entitlements     = erp_hr_leave_get_entitlements( array( 'employee_id' => $employee->id ) );
$entitlements_pol = wp_list_pluck( $entitlements, 'policy_id' );
$balance          = erp_hr_leave_get_balance( $employee->id );

if ( $policies ) {
    ?>

    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'Leave', 'erp' ) ?></th>
                <th><?php _e( 'Current', 'erp' ) ?></th>
                <th><?php _e( 'Scheduled', 'erp' ) ?></th>
                <th><?php _e( 'Available', 'erp' ) ?></th>
                <th><?php _e( 'Period', 'erp' ) ?></th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($policies as $num => $policy) {

                $key       = array_search( $policy->id, $entitlements_pol );
                $en        = false;
                $name      = esc_html( $policy->name );
                $current   = 0;
                $scheduled = 0;
                $available = 0;


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
                <td><?php echo $en ? sprintf( __( '%d days', 'erp' ), number_format_i18n( $en->days ) ) : '-'; ?></td>
                <td><?php echo $scheduled ? sprintf( __( '%d days', 'erp' ), number_format_i18n( $scheduled ) ) : '-'; ?></td>
                <td>
                    <?php
                    if ( $available < 0 ) {
                        printf( '<span class="red">%d %s</span>', number_format_i18n( $available ), __( 'days', 'erp' ) );
                    } elseif ( $available > 0 ) {
                        printf( '<span class="green">%d %s</span>', number_format_i18n( $available ), __( 'days', 'erp' ) );
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
                        _e( 'No Policy', 'erp' );
                    } ?>
                </td>
            </tr>

            <?php } ?>
        </tbody>

    </table>

<?php } ?>

<h3><?php _e( 'History', 'erp' ) ?></h3>

<?php
$cur_year   = date( 'Y' );
$requests   = erp_hr_get_leave_requests( array(
    'year'    => $cur_year,
    'user_id' => $employee->id,
    'status'  => 1,
    'orderby' => 'req.start_date',
    'number'  => -1
) );
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

    <input type="hidden" name="employee_id" value="<?php echo esc_attr( $employee->id ); ?>">

    <?php wp_nonce_field( 'erp-hr-empl-leave-history' ); ?>
    <?php submit_button( __( 'Filter', 'erp' ), 'secondary', 'submit', false ); ?>
</form>

<table class="widefat" id="erp-hr-empl-leave-history">
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