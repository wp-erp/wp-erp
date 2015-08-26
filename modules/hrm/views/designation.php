<div class="wrap erp erp-hr-designation">

    <h2><?php _e( 'Designation', 'wp-erp' ); ?> <a href="#" id="erp-new-designation" data-single="1" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <div id="erp-desig-table-wrap">

        <div class="list-table-inner">

            <form method="post">
                <input type="hidden" name="page" value="erp-hr-designation">
                <?php
                $designation = new \WeDevs\ERP\HRM\Designation_List_Table();
                $designation->prepare_items();
                $designation->views();

                $designation->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>