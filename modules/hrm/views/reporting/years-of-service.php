<div class="wrap">
	<h2><?php _e( 'Years of Service', 'wp-erp' ); ?></h2>

	<?php
		global $wpdb;

		$all_user_id = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees" );

		foreach ( $all_user_id as $user_id ) {

			$employee = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );
			$date     = date_parse_from_format( 'Y-m-d', $employee->hiring_date );
			$month    = $date['month'];
			$day      = $date['day'];

			if ( $month > 0 ) {

				$hire_data[$month][$day][] = [
					'emp_name'    => $employee->display_name,
					'hiring_date' => $employee->hiring_date
				];
			}
		}

		ksort( $hire_data );
	?>

	<div class="postbox">

		<div class="inside">
			<div class="main">
			<?php
				foreach ( $hire_data as $month => $data_month ) {

					$dateObj = DateTime::createFromFormat( '!m', $month );
					echo '<h3>' . $dateObj->format( 'F' ) . '</h3>';
					echo '<hr>';

					ksort( $data_month );

					foreach ( $data_month as $date => $data_date ) {
						echo '<strong>' . esc_attr( $date ) . '</strong>&nbsp;';

						$count = count( $data_date );
						$i     = 0;

						foreach ( $data_date as $single_data ) {
							
							$age = date( 'Y', time() ) - date( 'Y', strtotime( esc_attr( $single_data['hiring_date'] ) ) );
							echo $single_data['emp_name'] . ' ('. $age .')';
							
							if ( ++$i != $count ) {
								echo ', ';
							}
						}

						echo '<br>';
					}
				}
			?>
			</div>
		</div>
	</div>

	
</div>