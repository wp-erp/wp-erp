<?php

/**
 * Dashboard metabox
 *
 * @param  string  title of the metabox
 * @param  string  function callback
 *
 * @return void
 */
function erp_admin_dash_metabox( $title = '', $callback = null ) {
    ?>
    <div class="postbox">
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

/**
 * ERP current user access by capability 
 *
 * @param  string  $capability
 *
 * @since 0.1
 *
 * @return boolean
 */
function erp_current_user_can( $capability ) {
    return current_user_can( $capability );
}
