<div class="wrap">
	<h3><?php _e( 'Headcount', 'wp-erp' ); ?></h3>

	<?php
		$this_month     = strtotime( date( 'Y-m-01' ) );
		$js_this_month  = strtotime( date( 'Y-m-01' ) ) * 1000 + ( 15*24*60*60*1000 );
		$js_year_before = strtotime( '-11 month', $this_month ) * 1000 + ( 15*24*60*60*1000 );

		for ( $i = 0; $i <= 11; $i++ ) {

			$month    = date( "Y-m", strtotime( date( 'Y-m-01' )." -$i months" ) );
			$js_month = strtotime( $month. '-01' ) * 1000;
			$count    = erp_hr_get_headcount( $month, 'month' );

			$chart_data[] = [$js_month, $count];
		}

		//$chart_data = [$chart_data];
		//var_dump(json_encode($chart_data));
	?>

	<?php
		global $wpdb;

		$all_user_id = $wpdb->get_col( "SELECT user_id FROM {$wpdb->prefix}erp_hr_employees" );
	?>

	<div class="postbox">
		<div id="emp-headcount" style="width:800px;height:400px;"></div>
	</div>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php _e( 'Name', 'wp-erp'); ?></th>
				<th><?php _e( 'Hire Date', 'wp-erp'); ?></th>
				<th><?php _e( 'Job Title', 'wp-erp'); ?></th>
				<th><?php _e( 'Department', 'wp-erp'); ?></th>
				<th><?php _e( 'Location', 'wp-erp'); ?></th>
				<th><?php _e( 'Status', 'wp-erp'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ( $all_user_id as $user_id ) {

					$employee     = new \WeDevs\ERP\HRM\Employee( intval( $user_id ) );
					$employee_url = '<a href="'. admin_url( 'admin.php?page=erp-hr-employee&action=view&id=' . $employee->id ) . '">' . $employee->display_name . '</a>';
					$date_format  = get_option( 'date_format' );
			?>
					<tr>
						<td><?php echo wp_kses_post( $employee_url ); ?></td>
						<td><?php echo date( $date_format, strtotime( esc_attr( $employee->hiring_date ) ) ); ?></td>
						<td><?php echo esc_attr( $employee->designation_title ); ?></td>
						<td><?php echo esc_attr( $employee->department_title ); ?></td>
						<td><?php echo esc_attr( $employee->location_name ); ?></td>
						<td><?php echo esc_attr( $employee->status ); ?></td>
					</tr>
			<?php
				}
			 ?>
		</tbody>
	</table>
</div>

<script>
	;
	(function($){
		var data = [ [
	    [1456790400000, 15],
	    [1454284800000, 15],
	    [1451606400000, 14],
	    [1448928000000, 14],
	    [1446336000000, 14],
	    [1443657600000, 8],
	    [1441065600000, 8],
	    [1438387200000, 8],
	    [1435708800000, 8],
	    [1433116800000, 8],
	    [1430438400000, 8],
	    [1427846400000, 8]
	  ] ];


	  $.plot($("#emp-headcount"), data, {
	    xaxis: {
	      mode: 'time',
	      tickLength: 0,
	      tickSize: [1, 'month'],
	      min: <?php echo $js_year_before; ?>,
	      max: <?php echo $js_this_month; ?>
	    },
	    yaxis: {
	      show: false
	    },
	    series: {
	      bars: {
	      	show: true,
	        fill: 1,
	        color: '#8BA958',
	        barWidth: 20*24*60*60*1000
	      },
	      valueLabels: {
            show: true,
            font: "9pt 'Trebuchet MS'",
            align: 'center'
          }
	    },
	    bars: {
	      	align: 'center',
	      	fillColor: '#32CD32',
	      	lineWidth: 0
	    },
	    grid: {
	      	hoverable: true,
	      	clickable: true,
	      	borderWidth: 0
	    }
	  });

	})(jQuery);

</script>