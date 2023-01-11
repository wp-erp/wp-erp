<?php
$reason_text = '';
if ( ! empty( $_GET['bulk-operation-failed'] ) ) {
    $failed_reason = sanitize_text_field( wp_unslash( $_GET['bulk-operation-failed'] ) );
    $fail_count    = 0;
    if ( ! empty( $_GET['fail-count'] ) ) {
        $fail_count = absint( wp_unslash( $_GET['fail-count'] ) );
    }

    switch ( $failed_reason ) {
        case 'failed_some_trash':
            // translators: %s: the placeholder is the number of announcement that failed trash operation
            $reason_text = sprintf( _n( '%s announcement could not be trashed', '%s announcements could not be trashed', $fail_count, 'erp' ), number_format_i18n( $fail_count ) );
            break;

        case 'failed_some_delation':
            // translators: %s: the placeholder is the number of announcement that failed delete operation
            $reason_text = sprintf( _n( '%s announcement could not be deleted', '%s announcements could not be deleted', $fail_count, 'erp' ), number_format_i18n( $fail_count ) );
            break;

        case 'failed_some_restoration':
            // translators: %s: the placeholder is the number of announcement that failed restore operation
            $reason_text = sprintf( _n( '%s announcement could not be restored', '%s announcements could not be restored', $fail_count, 'erp' ), number_format_i18n( $fail_count ) );
            break;

        default:
            $reason_text = __( 'Unknow error happened', 'erp' );
    }
}
?>

<div class="wrap erp erp-hr-announcement">
    <h2>
        <?php esc_html_e( 'People > Announcements', 'erp' ); ?>
        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=erp_hr_announcement' ) ); ?>" id="erp-new-announcement" data-single="1" class="add-new-h2"><?php esc_html_e( 'Add New', 'erp' ); ?></a>
    </h2>

    <?php if ( ! empty( $reason_text ) ) { ?>
        <div id="message" class="error notice is-dismissible below-h2">
            <p><?php echo esc_html( $reason_text ); ?></p>
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
                $announcement = new \WeDevs\ERP\HRM\AnnouncementListTable();
                $announcement->prepare_items();
                $announcement->views();
                $announcement->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->

</div>
