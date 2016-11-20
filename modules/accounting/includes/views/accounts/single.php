<div class="wrap">
    <h2><?php echo esc_html( $ledger->name ); ?></h2>

    <form method="get" class="erp-ac-list-table-form">
        <input type="hidden" name="page" value="erp-accounting-charts">
        <input type="hidden" name="action" value="view">
        <input type="hidden" name="id" value="<?php echo $ledger->id; ?>">

        <?php
        $list_table = new WeDevs\ERP\Accounting\Journal_Transactions_List_Table();
        $list_table->prepare_items();
        $list_table->views();
        $list_table->display();
        ?>
    </form>
</div>
