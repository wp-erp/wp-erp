<?php
$sales_total   = erp_ac_get_sales_total();
$goods_sold    = erp_ac_get_good_sold_total_amount();
$expense_total = erp_ac_get_expense_total();
$tax_total     = erp_ac_get_tax_total();

?>
<div class="wrap">

	<h1><?php _e( 'Accounting', 'erp' ); ?></h1>
	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="hndle"><span><?php _e( 'Income Statement', 'erp' ); ?></span></h2>
			<div class="inside">

				<table cellpadding="10" class="erp-ac-report-income-statement-table">
					<tr>
						<td><?php _e( 'Revenue', 'erp' ); ?></td>
						<td><a href=""><?php echo erp_ac_get_price( $sales_total ); ?></a></td>
					</tr>
					<tr class="erp-ac-even erp-ac-even-first">
						<td><?php _e( 'Cost of goods sold', 'erp' ); ?></td>
						<td><a href=""><?php echo erp_ac_get_price( $goods_sold ); ?></a></td>
					</tr>
					<tr class="erp-ac-odd">
						<td><strong><?php _e( 'Gross income', 'erp' ); ?></strong></td>
						<td>
							<?php $gross = $sales_total - $goods_sold; ?>
							<a href=""><?php echo  erp_ac_get_price( ( $gross ) ); ?></a>
						</td>
					</tr>
					<tr class="erp-ac-even">
						<td><?php _e( 'Overhead', 'erp' ); ?></td>
						<td><a href=""><?php echo erp_ac_get_price( $expense_total ); ?></a></td>
					</tr>
					<tr class="erp-ac-odd"> 
						<td><strong><?php _e( 'Operating income', 'erp' ); ?></strong></td>
						<td>
							<?php $operating = $gross - $expense_total; ?>
							<a href=""><?php echo erp_ac_get_price( $operating ); ?></a>
						</td>
					</tr>
					<tr class="erp-ac-even">
						<td><?php _e( 'Tax', 'erp' ); ?></td>
						<td><a href=""><?php echo erp_ac_get_price( $tax_total ); ?></a></td>
					</tr>
					<tr class="erp-ac-odd">
						<td><strong><?php _e( 'Net income', 'erp' ); ?></strong></td>
						<td>
							<?php $net = $operating - $tax_total; ?>
							<a  href=""><?php echo erp_ac_get_price( $net ); ?></a>
						</td>
					</tr>
				</table>

			</div>
		</div>

	</div>



</div>
