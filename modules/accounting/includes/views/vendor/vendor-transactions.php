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
