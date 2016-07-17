<div class="wrap erp-accounting">
    <?php
    if ( erp_ac_create_expenses_voucher() || erp_ac_create_expenses_credit() ) {
        ?><h2>
            <?php _e( 'Expense Transactions', 'erp' ); ?>

            <?php
            $form_types = erp_ac_get_expense_form_types();
            if ( $form_types ) {
                foreach ( $form_types as $key => $form ) {
                    do_action( 'erp_ac_sales_transaction_action', $key, $form );
                    if ( 'payment_voucher' == $key && ( erp_ac_create_expenses_voucher() || erp_ac_publish_expenses_voucher() ) ) {
                        printf( '<a class="add-new-h2" href="%s%s" title="%s">%s</a> ', admin_url( 'admin.php?page=erp-accounting-expense&action=new&type=' ), $key, esc_attr( $form['description'] ), $form['label'] );
                    } elseif ( 'vendor_credit' == $key && ( erp_ac_create_expenses_credit() || erp_ac_publish_expenses_credit() ) ) {
                        printf( '<a class="add-new-h2" href="%s%s" title="%s">%s</a> ', admin_url( 'admin.php?page=erp-accounting-expense&action=new&type=' ), $key, esc_attr( $form['description'] ), $form['label'] );
                    } else {
                        do_action( 'erp_ac_sales_transaction_after_action', $key, $form );
                    }
                }
            }
            ?>
        </h2><?php
    }


    if ( erp_ac_view_expenses_summary() ) {
        include_once dirname( dirname( __FILE__ ) ) . '/common/transaction-chart.php';
    }
    ?>

    <form method="get">
        <input type="hidden" name="page" value="erp-accounting-expense">

        <?php
        $list_table = new WeDevs\ERP\Accounting\Expense_Transaction_List_Table();
        $list_table->prepare_items();
        // $list_table->search_box( 'search', 'search_id' );
        $list_table->display();
        ?>
    </form>
</div>