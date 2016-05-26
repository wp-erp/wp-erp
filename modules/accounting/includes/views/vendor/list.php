<div class="wrap erp-ac-vendor-list-table-wrap">
    <?php if ( erp_ac_create_vendor() ) { ?>
        <h2><?php _e( 'Vendors', 'erp-accounting' ); ?> <a href="<?php echo admin_url( 'admin.php?page=erp-accounting-vendors&action=new' ); ?>" class="add-new-h2"><?php _e( 'Add New', 'erp-accounting' ); ?></a></h2>
    <?php } ?>
    

    <?php
    if ( isset( $_GET['msg'] ) ) {
        switch ( $_GET['msg'] ) {
            case 'update':
                erp_html_show_notice( __( 'Vendor info has been updated!', 'erp-accounting' ) );
                break;

            case 'new':
                erp_html_show_notice( __( 'New vendor has been added!', 'erp-accounting' ) );
                break;
        }
    }
    ?>
    <div class="inner-table-wrap">
        <div class="list-table-inner">
            <form method="post">
                <input type="hidden" name="page" value="ttest_list_table">

                <?php
                $list_table = new WeDevs\ERP\Accounting\Vendor_List_Table();
                $list_table->prepare_items();
                $list_table->search_box( 'search', 'search_id' );
                $list_table->views();
                $list_table->display();
                ?>
            </form>
        </div>
    </div>
</div>
