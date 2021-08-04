<div class="wrap erp erp-hr-announcement">
    <h2>
        <?php esc_html_e( 'People', 'erp' ); ?>
        <a href="<?php echo admin_url( 'post-new.php?post_type=erp_hr_announcement' ); ?>" id="erp-new-announcement" data-single="1" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a>
    </h2>

    <?php if ( ! empty( $_GET['announcement_delete'] ) ) { ?>
        <div id="message" class="error notice is-dismissible below-h2">
            <p><?php esc_html_e( 'Some announcements doesn\'t delete', 'erp' ); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php } ?>

    <?php do_action( 'erp_hr_people_menu', 'announcement' ); ?>

    <div id="erp-announcement-table-wrap">

        <div class="list-table-inner">

            <form method="get">
                <input type="hidden" name="page" value="erp-hr">
                <input type="hidden" name="section" value="people">
                <input type="hidden" name="sub-section" value="announcement">
                <?php
                $announcement = new \WeDevs\ERP\HRM\Announcement_List_Table();
                $announcement->prepare_items();
                $announcement->views();
                $announcement->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
