<?php

/**
 * Dashboard metabox
 *
 * @param  string        title of the metabox
 * @param  string|array  function callback
 *
 * @return void
 */
function erp_admin_dash_metabox( $title = '', $callback = null, $class = '' ) {
    if ( is_array( $callback ) && isset( $callback[1] ) ) {
        $metabox_callback = $callback[1];
    } else {
        $metabox_callback = $callback;
    }

    if ( is_string( $metabox_callback ) && apply_filters( 'erp_admin_dash_metabox_hide_' . $metabox_callback, false ) ) {
        return;
    }

    $class_name = ! empty( $class ) ? ' ' . $class : '';
    ?>
    <div class="postbox<?php echo esc_attr( $class_name ); ?>">
        <h3 class="hndle"><span><?php echo wp_kses_post( $title ); ?></span></h3>
        <div class="inside">
            <div class="main">
                <?php if ( is_callable( $callback ) ) {
                    call_user_func( $callback );
                } ?>
            </div> <!-- .main -->
        </div> <!-- .inside -->
    </div> <!-- .postbox -->
    <?php
}
