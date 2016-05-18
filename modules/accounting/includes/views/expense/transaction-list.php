<div class="wrap erp-accounting">
    <h2>
        <?php _e( 'Expense Transactions', 'accounting' ); ?>

        <?php
        $form_types = erp_ac_get_expense_form_types();
        if ( $form_types ) {
            foreach ($form_types as $key => $form) {
                printf( '<a class="add-new-h2" href="%s%s" title="%s">%s</a> ', admin_url( 'admin.php?page=erp-accounting-expense&action=new&type=' ), $key, esc_attr( $form['description'] ), $form['label'] );
            }
        }
        ?>
    </h2>

    <?php include_once dirname( dirname( __FILE__ ) ) . '/common/transaction-chart.php'; ?>

    <form method="post">
        <input type="hidden" name="page" value="ttest_list_table">

        <?php
        $list_table = new WeDevs\ERP\Accounting\Expense_Transaction_List_Table();
        $list_table->prepare_items();
        // $list_table->search_box( 'search', 'search_id' );
        $list_table->display();
        ?>
    </form>
</div>