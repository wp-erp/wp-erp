<?php
$sales_total   = erp_ac_get_sales_total();
$goog_sold     = erp_ac_get_good_sold_total_amount();
$expense_total = erp_ac_get_expense_total();
$tax_total     = erp_ac_get_tax_total();

?>
<div class="wrap">

	<h1><?php _e( 'Accounting', 'erp' ); ?></h1>
	<div class="metabox-holder">
	<div class="postbox">
		<h2 class="hndle"><span><?php _e( 'Income Statement', 'erp' ); ?></span></h2>
		<div class="inside">

			<table width="100">
				<tr>
					<td><?php _e( 'Revenue', 'erp' ); ?></td>
					<td>$500</td>
				</tr>
				<tr>
					<td><?php _e( 'Cost of good sold', 'erp' ); ?></td>
					<td>$500</td>
				</tr>
				<tr>
					<td><?php _e( 'Gross income', 'erp' ); ?></td>
					<td>$500</td>
				</tr>
				<tr>
					<td><?php _e( 'Overhead', 'erp' ); ?></td>
					<td>$500</td>
				</tr>
				<tr>
					<td><?php _e( 'Operating income', 'erp' ); ?></td>
					<td>$500</td>
				</tr>
				<tr>
					<td><?php _e( 'Tax', 'erp' ); ?></td>
					<td>$500</td>
				</tr>
				<tr>
					<td><?php _e( 'Net income', 'erp' ); ?></td>
					<td>$500</td>
				</tr>
			</table>

		</div>
	<!-- 	Revenue <?php echo $sales_total; ?><br>
		Cost of good sold <?php echo $goog_sold; ?>
		<hr>
		Gross Income <?php echo $operating = $sales_total - $goog_sold; ?><br>
		Overhead <?php echo $expense_total; ?>
		<hr>
		Operating Income <?php echo $tax = $operating - $expense_total; ?><br>
		Tax   <?php echo $tax_total; ?><br>
		<hr>
		Net Income <?php echo $tax - $tax_total; ?> -->
	</div>

	</div>



</div>
