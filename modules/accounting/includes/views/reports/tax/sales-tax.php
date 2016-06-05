<div class="wrap erp-ac-tax-report-wrap">

	<h1><?php _e( 'Sales Tax Summary', 'accounting' ); ?></h1>
	<p class="erp-ac-report-tax-date">
	<?php 
	$start = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) );
	$end   = erp_format_date( date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) );
	printf( '<i class="fa fa-calendar"></i> %1$s %2$s %3$s %4$s', __( 'From', 'accounting' ), $start, __( 'to', 'accounting' ),  $end); ?>
	</p>
	<table class="wp-list-table widefat fixed striped erp-ac-tax-report-table">
		<thead>
			<tr>
				<th><?php _e( 'Tax', 'accounting' ); ?></th>
				<th colspan="2"><?php _e( 'Tax Payable', 'accounting' ); ?></th>
				<th colspan="2"><?php _e( 'Tax Receivable', 'accounting' ); ?></th>
				<th><?php _e( 'Net Tax', 'accounting' ); ?></th>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<th><?php _e( 'Transaction subtotal', 'accounting' ) ?></th>
				<th><?php _e( 'tax amount', 'accounting' ) ?></th>
				<th><?php _e( 'Transaction subtotal', 'accounting' ) ?></th>
				<th><?php _e( 'tax amount', 'accounting' ) ?></th>
				<th>&nbsp;</th>

			</tr>
		</thead>
		<tbody>
			
			<?php 
			foreach ( $taxs as $tax_id => $tax ) {
				$net_tax =  $tax['sales']['tax_credit'] - $tax['expense']['tax_debit'];

				?>
					<tr>
						<td><a href="<?php echo erp_ac_get_singe_tax_report_url( $tax_id ); ?>"><?php echo $tax['sales']['tax_name'] . ' (' . $tax['sales']['rate'] . '%)'; ?></a></td>
						<td><?php echo erp_ac_get_price( $tax['sales']['trns_subtotal'] ); ?></td>
						<td><?php echo erp_ac_get_price( $tax['sales']['tax_credit'] ); ?></td>
						<td><?php echo erp_ac_get_price( $tax['expense']['trns_subtotal'] ); ?></td>
						<td><?php echo erp_ac_get_price( $tax['expense']['tax_debit'] ); ?></td>
						<td><?php echo $net_tax < 0 ? '('.erp_ac_get_price( $net_tax ).')' : erp_ac_get_price( $net_tax ); ?></td>

					</tr>
				<?php
			}
			?>
		</tbody>
	</table>

</div>






