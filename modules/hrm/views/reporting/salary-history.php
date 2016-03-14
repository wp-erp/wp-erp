<?php
global $wpdb;

$all_user_id = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees" );
$date_format = get_option( 'date_format' );
?>
<div class="wrap">
	<h2><?php _e( 'Salary History', 'wp-erp' ); ?></h2>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php _e( 'Employee', 'wp-erp' ); ?></th>
				<th><?php _e( 'Date', 'wp-erp' ); ?></th>
				<th><?php _e( 'Pay Rate', 'wp-erp' ); ?></th>
				<th><?php _e( 'Pay type', 'wp-erp' ); ?></th>
				<th><?php _e( 'Employee #', 'wp-erp' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ( $all_user_id as $user_id ) {

					$employee      = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );
					$compensations = $employee->get_history( 'compensation' );
					
					if ( $compensations ) {

						$line = 0;

						foreach ( $compensations as $compensation ) {

							$employee_url = '<a href="'. admin_url( 'admin.php?page=erp-hr-employee&action=view&id=' . $employee->id ) . '">' . $employee->display_name . '</a>';
							echo '<tr>';
							echo '<td>' . ( 0 == $line ? wp_kses_post( $employee_url ) : '' ) . '</td>';
							echo '<td>' . date( $date_format, strtotime( esc_attr( $compensation->date ) ) ) . '</td>';
							echo '<td>' . esc_attr( $compensation->type ) . '</td>';
							echo '<td>' . esc_attr( $compensation->category ) . '</td>';
							echo '<td>' . esc_attr( $employee->id ) . '</td>';
							echo '</tr>';

							$line++;
						}
					}
				}
			?>
		</tbody>
	</table>
</div>



