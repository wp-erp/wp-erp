<?php
$files = $transaction->files ? maybe_unserialize( $transaction->files ) : [];
if ( ! $files ) {
    return;
}
?>
<div class="erp-hide-print erp-single-transaction-attachment-area">
        <h2><?php _e( 'Attachments', 'erp' ); ?></h2>
        <ul class="erp-attachment-list">
            <?php if ( ! $files ) { ?>

                <li><?php _e( 'No attachments found!', 'erp' ); ?></li>
            <?php } ?>


            <?php foreach ( $files as $file ) {

                    if (wp_attachment_is_image( $file)) {
                        $image = wp_get_attachment_image_src( $file, array( '80', '80' ) );
                        $image = $image[0];
                    } else {
                        $image = wp_mime_type_icon( $file );
                    }
            ?>
                <li class="erp-image-wrap thumbnail"><a href="<?php echo wp_get_attachment_url( $file ); ?>" ><img height="80" width="80" src="<?php echo $image; ?>"></li></a>
            <?php } ?>
        </ul>

</div>
