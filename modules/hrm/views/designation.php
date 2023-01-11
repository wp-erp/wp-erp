<div class="wrap erp erp-hr-designation">
    <h2>
        <?php esc_html_e( 'People > Designations', 'erp' ); ?>
        <a href="#" id="erp-new-designation" data-single="1" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a>
    </h2>

    <?php if ( isset( $_GET['desig_delete'] ) ) { ?>
        <div id="message" class="error notice is-dismissible below-h2">
            <p><?php esc_html_e( 'Some designation doesn\'t delete because those designation assign some employees.', 'erp' ); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'erp' ); ?></span>
            </button>
        </div>
    <?php } ?>

    <?php do_action( 'erp_hr_people_menu', 'designation' ); ?>

    <div id="erp-desig-table-wrap">

        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="people">
                <input type="hidden" name="sub-section" value="designation">
                <?php
                $designation = new \WeDevs\ERP\HRM\DesignationListTable();
                $designation->prepare_items();
                $designation->views();
                $designation->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
