<div class="wrap erp-accounting">
    <?php
    if ( erp_ac_create_sales_payment() || erp_ac_create_sales_invoice() ) {
        ?>
        <h2>
            <?php _e( 'Sales Transactions', 'accounting' ); ?>

            <?php
            $form_types = erp_ac_get_sales_form_types();
            if ( $form_types ) {
                foreach ($form_types as $key => $form) {
                    if ( 'payment' == $key && erp_ac_create_sales_payment() ) {
                        printf( '<a class="add-new-h2" href="%s%s" title="%s">%s</a> ', admin_url( 'admin.php?page=erp-accounting-sales&action=new&type=' ), $key, esc_attr( $form['description'] ), $form['label'] );
                    }

                    if ( 'invoice' == $key && erp_ac_create_sales_invoice() ) {
                        printf( '<a class="add-new-h2" href="%s%s" title="%s">%s</a> ', admin_url( 'admin.php?page=erp-accounting-sales&action=new&type=' ), $key, esc_attr( $form['description'] ), $form['label'] );
                    }
                }
            }
            ?>
        </h2>

        <?php
    }

    if ( erp_ac_view_sales_summary() ) {
        include_once dirname( dirname( __FILE__ ) ) . '/common/transaction-chart.php';
    }
    ?>

    <form method="get">
        <input type="hidden" name="page" value="erp-accounting-sales">

        <?php
        $list_table = new WeDevs\ERP\Accounting\Sales_Transaction_List_Table();
        $list_table->prepare_items();
        $list_table->views();
        $list_table->display();
        ?>
    </form>
</div>