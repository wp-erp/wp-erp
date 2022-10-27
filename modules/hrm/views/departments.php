<div class="wrap erp-hr-depts">

    <h2>
        <?php esc_html_e( 'People > Departments', 'erp' ); ?>
        <a href="#" id="erp-new-dept" class="add-new-h2" data-single="1"><?php esc_html_e( 'Add New', 'erp' ); ?></a>

        <form method="get">
            <input type="hidden" name="page" value="erp-hr">
            <input type="hidden" name="section" value="people">
            <input type="hidden" name="sub-section" value="department">
        </form>
    </h2>
    <?php if ( isset( $_GET['department_delete'] ) ) { ?>
        <div id="message" class="error notice is-dismissible below-h2">
            <p><?php esc_html_e( 'Some department doesn\'t delete because some employees work under those department', 'erp' ); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'erp' ); ?></span>
            </button>
        </div>
    <?php } ?>

    <?php do_action( 'erp_hr_people_menu', 'department' ); ?>

    <div id="erp-dept-table-wrap">

        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="people">
                <input type="hidden" name="sub-section" value="department">
                <?php
                $department_table = new \WeDevs\ERP\HRM\DepartmentListTable();
                $department_table->prepare_items();
                $department_table->views();
                $department_table->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
