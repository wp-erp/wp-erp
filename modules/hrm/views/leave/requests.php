<div class="wrap erp-hr-leave-requests">
    <h2><?php esc_html_e( 'Leave Requests', 'erp' ); ?> <a href="<?php echo esc_url( add_query_arg( array( 'view' => 'new' ) ) ); ?>" class="add-new-h2"><?php esc_html_e( 'New Request', 'erp' ); ?></a></h2>

    <div class="erp-hr-leave-requests-inner">
        <div class="list-table-wrap">
            <div class="list-table-inner">

                <form method="get">
                    <input type="hidden" name="page" value="erp-hr">
                    <input type="hidden" name="section" value="leave">
                    <input type="hidden" name="sub-section" value="leave-requests">
                    <?php
                    $requests_table = new \WeDevs\ERP\HRM\Leave_Requests_List_Table();
                    $requests_table->prepare_items();
                    $requests_table->search_box( __( 'Search Employee', 'erp' ), 'search_request' );
                    $requests_table->views();

                    $requests_table->display();
                    ?>
                </form>

            </div><!-- .list-table-inner -->
        </div><!-- .list-table-wrap -->
    </div><!-- .erp-hr-leave-requests-inner -->
</div><!-- .wrap -->
