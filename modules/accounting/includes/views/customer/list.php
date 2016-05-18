<div class="wrap erp-ac-customer-list-table-wrap">
    <h2><?php _e( 'Customers', 'wp-erp-ac' ); ?> <a href="<?php echo admin_url( 'admin.php?page=erp-accounting-customers&action=new' ); ?>" class="add-new-h2"><?php _e( 'Add New', 'wp-erp-ac' ); ?></a></h2>

    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch ( $_GET['msg'] ) {
            case 'update':
                erp_html_show_notice( __( 'Customer info has been updated!', 'accounting' ) );
                break;

            case 'new':
                erp_html_show_notice( __( 'New customer has been added!', 'accounting' ) );
                break;
        }
    }
    ?>
    <div class="inner-table-wrap">
        <div class="list-table-inner">
            <form method="post">
                <input type="hidden" name="page" value="ttest_list_table">

                <?php
                $list_table = new WeDevs\ERP\Accounting\Customer_List_Table();
                $list_table->prepare_items();
                $list_table->search_box( 'search', 'search_id' );
                $list_table->views();
                $list_table->display();
                ?>
            </form>
        </div>
    </div>
</div>