<div class="wrap">
    <h2><?php echo esc_html( $ledger->name ); ?></h2>

    <form method="get">
        <input type="hidden" name="page" value="erp-accounting-charts">
        <input type="hidden" name="action" value="view">
        <input type="hidden" name="id" value="<?php echo $ledger->id; ?>">

        <?php
        $list_table = new WeDevs\ERP\Accounting\Journal_Transactions();
        $list_table->prepare_items();
        $list_table->views();
        $list_table->display();
        ?>
    </form>
</div>