<div class="wrap erp-hr-employees" id="wp-erp">

    <h2>
        <?php _e( 'Employee', 'wp-erp' ); ?> 
        <?php
        if ( erp_current_user_can( 'erp_create_employee' ) ) {
            ?>
                <a href="#" id="erp-employee-new" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a>
            <?php
        }
        ?>
    </h2>

    <div class="list-table-wrap">
        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-hr-employee">
                <?php
                $employee_table = new \WeDevs\ERP\HRM\Employee_List_Table();
                $employee_table->prepare_items();
                $employee_table->search_box( __( 'Search Employee', 'wp-erp' ), 'erp-employee-search' );
                $employee_table->views();

                $employee_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>