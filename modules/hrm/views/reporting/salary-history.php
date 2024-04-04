<?php
global $wpdb;

$all_user_id = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees WHERE status = 'active' ORDER BY hiring_date DESC" );
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Salary History', 'erp' ); ?></h1>

    <table class="widefat striped" style="margin-top: 20px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Employee', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Date', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Pay Rate', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Pay type', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Employee ID', 'erp' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( $all_user_id ) {
                foreach ( $all_user_id as $user_id ) {
                    $employee      = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );
                    $compensations = $employee->get_job_histories( 'compensation' );

                    if ( !empty( $compensations['compensation'] ) ) {
                        $line = 0;

                        foreach ( $compensations['compensation'] as $compensation ) {
                            $employee_url = '<a href="' . admin_url( 'admin.php?page=erp-hr&section=people&sub-section=employee&action=view&id=' . $employee->get_user_id() ) . '">' . $employee->display_name . '</a>';
                            $emp_url      = ( 0 == $line ? wp_kses_post( $employee_url ) : '' );
                            echo '<tr>';
                            echo '<td>' . wp_kses_post( $emp_url ) . '</td>';
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            echo '<td>' . erp_format_date( esc_attr( $compensation['date'] ) ) . '</td>';
                            echo '<td>' . esc_attr( $compensation['pay_rate'] ) . '</td>';
                            echo '<td>' . esc_attr( $compensation['pay_type'] ) . '</td>';
                            echo '<td>' . esc_attr( $employee->employee_id ) . '</td>';
                            echo '</tr>';

                            $line++;
                        }
                    }
                }
            } else {
                echo '<tr><td colspan="5">' . esc_html__( 'No employee found!', 'erp' ) . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>
