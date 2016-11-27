<div class="wrap">
    <?php
    if ( erp_ac_create_journal() ) {
        ?>
        <h2><?php _e( 'Journals', 'erp' ); ?>
            <a href="<?php echo admin_url( 'admin.php?page=erp-accounting-journal&action=new' ); ?>" class="add-new-h2"><?php _e( 'New Entry', 'erp' ); ?></a>
        </h2>
        <?php
    }
    ?>

    <form method="get" action="" class="erp-ac-list-table-form">
        <input type="hidden" name="page" value="erp-accounting-journal">

        <?php
        $list_table = new \WeDevs\ERP\Accounting\Journal_Transactions_List_Table();
        $list_table->prepare_items();
        $list_table->display();
        ?>
    </form>
</div>
