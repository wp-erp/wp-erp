<h3><?php use WeDevs\ERP\HRM\Models\Financial_Year;

    esc_html_e( 'Balances', 'erp' ) ?></h3>

<?php
$balance = $employee->get_leave_summary();

if ( $balance ) {
    ?>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Leave', 'erp' ) ?></th>
                <th><?php esc_html_e( 'Days', 'erp' ) ?></th>
                <th><?php esc_html_e( 'Spent', 'erp' ) ?></th>
                <th><?php esc_html_e( 'Available', 'erp' ) ?></th>
                <th><?php esc_html_e( 'Extra', 'erp' ) ?></th>
                <th><?php esc_html_e( 'Period', 'erp' ) ?></th>
            </tr>
        </thead>

        <tbody>

            <?php if( $balance ):?>
            <?php foreach ($balance as $num => $entitlement) { ?>

            <tr class="<?php echo $num % 2 == 0 ? 'alternate' : 'odd'; ?>">
                <td><?php echo esc_html( $entitlement->policy ); ?></td>
                <td><?php echo $entitlement->days ? sprintf( esc_html__( '%d days', 'erp' ), esc_html( number_format_i18n( $entitlement->days ) ) ) : '-'; ?></td>
                <td><?php echo ($entitlement->day_out != 0) ? sprintf( esc_html__( '%d days', 'erp' ), esc_html( number_format_i18n( ($entitlement->day_out) ) ) ) : '-'; ?></td>
                <td>
                    <?php
                    if ( $entitlement->available > 0 ) {
                        printf( '<span class="green">%d %s</span>', esc_html( number_format_i18n( $entitlement->available ) ), esc_html__( 'days', 'erp' ) );
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ( $entitlement->extra_leave > 0 ) {
                        printf( '<span class="green">%d %s</span>', esc_html( number_format_i18n( $entitlement->extra_leave ) ), esc_html__( 'days', 'erp' ) );
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    printf( '%s - %s', esc_html( erp_format_date( $entitlement->from_date ) ), esc_html( erp_format_date( $entitlement->to_date ) ) );
                    ?>
                </td>
            </tr>

            <?php } ?>
            <?php else :?>
            <tr class="alternate">
                <td colspan="4"><?php esc_html_e( 'No leave policy found!', 'erp' ); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>

    </table>

<?php } ?>

<h3><?php esc_html_e( 'History', 'erp' ) ?></h3>

<?php
$cur_year   = date( 'Y' );
$requests   = $employee->get_leave_requests( array( 'status' => 'all' ) );
?>

<form action="#" id="erp-hr-empl-leave-history">
    <select name="f_year" id="f_year">
        <?php echo wp_kses( erp_html_generate_dropdown( array( '' => esc_attr__( 'select year', 'erp' ) ) + wp_list_pluck( Financial_Year::all(), 'fy_name', 'id' ), '' ), array(
            'option' => array(
                'value' => array(),
                'selected' => array()
            ),
        ) ); ?>
    </select>

    <?php erp_html_form_input( array(
        'name'     => 'leave_policy',
        'type'     => 'select',
        'options'  => array( 'all' => esc_attr__( 'All Policy', 'erp' ) ) + erp_hr_leave_get_policies_dropdown_raw()
    ) ); ?>

    <input type="hidden" name="employee_id" value="<?php echo esc_attr( $employee->get_user_id() ); ?>">

    <?php wp_nonce_field( 'erp-hr-empl-leave-history' ); ?>
    <?php submit_button( esc_html__( 'Filter', 'erp' ), 'secondary', 'submit', false ); ?>
</form>

<table class="widefat" id="erp-hr-empl-leave-history-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Date', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Policy', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Description', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Days', 'erp' ) ?></th>
            <th><?php esc_html_e( 'Status', 'erp' ) ?></th>
        </tr>
    </thead>

    <tbody>
        <?php include dirname( __FILE__ ) . '/tab-leave-history.php'; ?>
    </tbody>
</table>
<script type="text/javascript">
    ;jQuery(function( $ ) {
        var select_string = '<?php echo esc_attr__( 'All Policy', 'erp') ?>';
        var policies = <?php
            $policies = $employee->get_leave_policies();
            $result = array();
            foreach ( $policies as $policy ) {
                $result[ $policy['f_year'] ][] = $policy;
            }
            echo json_encode( $result );
            ?>;

        $('#erp-hr-empl-leave-history').on( 'change', '#f_year', function ( e) {

            var f_year = $(this).val();

            $('#leave_policy option').remove();
            var option = new Option( select_string, '' );
            $('#leave_policy').append(option);

            if ( policies[ f_year ] ) {
                $.each( policies[ f_year ], function ( id, policy ) {
                    var option = new Option(policy.name, policy.leave_id);
                    $('#leave_policy').append(option);
                } );
            }
        });

    })
</script>
