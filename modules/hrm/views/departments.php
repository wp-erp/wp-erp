<div class="wrap erp-hr-depts">

    <h2><?php esc_html_e( 'Departments', 'erp' ); ?> <a href="#" id="erp-new-dept" class="add-new-h2" data-single="1"><?php esc_html_e( 'Add New', 'erp' ); ?></a></h2>

    <?php if ( isset( $_GET['department_delete'] ) ): ?>
        <div id="message" class="error notice is-dismissible below-h2">
            <p><?php esc_html_e( 'Some department doesn\'t delete because some employees work under those department', 'erp' ) ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php endif ?>

    <div id="erp-dept-table-wrap">

        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="department">
                <?php
                $department_table = new \WeDevs\ERP\HRM\Department_List_Table();
                $department_table->prepare_items();
                $department_table->views();

                $department_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
