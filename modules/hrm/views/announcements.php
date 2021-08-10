<?php
$reason_text = '';
if ( ! empty( $_GET['bulk-operation-failed'] ) ) {
    $failed_reason = sanitize_text_field( wp_unslash( $_GET['bulk-operation-failed'] ) );

    switch ( $failed_reason ) {
        case 'failed_some_trash':
            $reason_text = esc_html__( 'Some announcements could not be trashed', 'erp' );
            break;

        case 'failed_some_delation':
            $reason_text = esc_html__( 'Some announcements could not be deleted', 'erp' );
            break;

        case 'failed_some_restoration':
            $reason_text = esc_html__( 'Some announcements could not be restored', 'erp' );
            break;

        default:
            $reason_text = esc_html__( 'Unknow error happened', 'erp' );
    }
}
?>

<div class="wrap erp erp-hr-announcement">
    <h2>
        <?php esc_html_e( 'People', 'erp' ); ?>
        <a href="<?php echo admin_url( 'post-new.php?post_type=erp_hr_announcement' ); ?>" id="erp-new-announcement" data-single="1" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a>
    </h2>

    <?php if ( ! empty( $reason_text ) ) { ?>
        <div id="message" class="error notice is-dismissible below-h2">
            <p><?php echo $reason_text; ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"> <?php esc_html_e( 'Dismiss this notice.', 'erp' ); ?> </span></button>
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
