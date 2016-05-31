<?php
$currency_symbole = erp_ac_get_currency_symbol();
?>
<form method="get">
    <input type="hidden" name="page" value="erp-accounting-customers">
    <input type="hidden" name="id" value="<?php echo $vendor->id; ?>">

    <?php
    $list_table = new \WeDevs\ERP\Accounting\Vendor_Transaction_List_Table( $vendor->id );
    $list_table->prepare_items();
    $list_table->views();
    $list_table->display();
    ?>

    <input type="hidden" name="action" value="view">
</form>
<?php
// $received_money   = erp_ac_get_customer_received_money( $list_table->items );
// $due_money        = erp_ac_get_customer_due_amount( $list_table->items );
?>
<!-- <div id="dashboard-widgets"  class="metabox-holder">
	<div id="postbox-container-1" class="postbox-container">
		<div class="postbox">
			<h2 class="hndle ui-sortable-handle"><?php //_e( 'Received Money Total', 'erp-accounting' ); ?></h2>
			<div class="inside"><?php //echo $currency_symbole . $received_money; ?></div>
		</div>
	</div>

	<div id="postbox-container-2" class="postbox-container">
		<div class="postbox">
			<h2 class="hndle ui-sortable-handle"><?php //_e( 'Due Amount Total', 'erp-accounting' ); ?></h2>
			<div class="inside"><?php //echo $currency_symbole . $due_money; ?></div>
		</div>
	</div>
</div>
 -->
<?php //var_dump( $list_table ); ?>