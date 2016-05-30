<?php
$sales_total   = erp_ac_get_sales_total();
$goog_sold     = erp_ac_get_good_sold_total_amount();
$expense_total = erp_ac_get_expense_total();
$tax_total     = erp_ac_get_tax_total();

?>

Revenue <?php echo $sales_total; ?><br>
Cost of good sold <?php echo $goog_sold; ?>
<hr>
Gross Income <?php echo $operating = $sales_total - $goog_sold; ?><br>
Overhead <?php echo $expense_total; ?>
<hr>
Operating Income <?php echo $tax = $operating - $expense_total; ?><br>
Tax   <?php echo $tax_total; ?><br>
<hr>
Net Income <?php echo $tax - $tax_total; ?>
