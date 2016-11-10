<?php
/**
 * Showing accounting wp error message
 *
 * @since  1.1.7
 *
 * @return void
 */
function erp_ac_view_error_message() {
    $messages = isset( $_REQUEST['message'] ) && isset( $_REQUEST['message']['errors'] ) ? $_REQUEST['message']['errors'] : array();

    foreach( $messages as $key => $error ) { ?>
        <div id="message" class="error notice notice-success is-dismissible">
            <p><?php echo reset( $error ); ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'erp'); ?></span></button>
        </div>
    <?php }
}