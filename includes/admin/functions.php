<?php

/**
 * Dashboard metabox
 *
 * @param  string  title of the metabox
 * @param  string  function callback
 *
 * @return void
 */
function erp_admin_dash_metabox( $title = '', $callback = null, $class = '' ) {
    $class_name = ! empty( $class ) ? ' ' . $class : '';
    ?>
    <div class="postbox<?php echo $class_name; ?>">
        <h3 class="hndle"><span><?php echo $title; ?></span></h3>
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
