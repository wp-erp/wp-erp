<div class="wrap erp-hr-leave-requests">
    <div class="erp-hr-leave-requests-inner">

        <h2><?php _e( 'Leave Requests', 'erp' ); ?> <a href="<?php echo add_query_arg( array( 'view' => 'new' ) ); ?>" class="add-new-h2"><?php _e( 'New Request', 'erp' ); ?></a></h2>

        <div class="list-table-wrap">
            <div class="list-table-inner">

                <form method="get">
                    <input type="hidden" name="page" value="erp-leave">
                    <?php
                    $requests_table = new \WeDevs\ERP\HRM\Leave_Requests_List_Table();
                    $requests_table->prepare_items();
                    $requests_table->views();

                    $requests_table->display();
                    ?>
                </form>

            </div><!-- .list-table-inner -->
        </div><!-- .list-table-wrap -->
    </div><!-- .erp-hr-leave-requests-inner -->
</div><!-- .wrap -->
