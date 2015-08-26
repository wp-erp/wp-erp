<div class="wrap erp-hr-depts">

    <h2><?php _e( 'Departments', 'wp-erp' ); ?> <a href="#" id="erp-new-dept" class="add-new-h2" data-single="1"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <div id="erp-dept-table-wrap">

        <div class="list-table-inner">

            <form method="post">
                <input type="hidden" name="page" value="erp-hr-depts">
                <?php
                $department_table = new \WeDevs\ERP\HRM\Deparment_List_Table();
                $department_table->prepare_items();
                $department_table->views();

                $department_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>