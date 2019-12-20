<div class="wrap erp erp-hr-designation">

    <h2><?php esc_html_e( 'Designation', 'erp' ); ?> <a href="#" id="erp-new-designation" data-single="1" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a></h2>

    <?php if ( isset( $_GET['desig_delete'] ) ): ?>
        <div id="message" class="error notice is-dismissible below-h2">
            <p><?php esc_html_e( 'Some designation doesn\'t delete because those designation assign some employees.', 'erp' ) ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php endif ?>

    <div id="erp-desig-table-wrap">

        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="designation">
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
