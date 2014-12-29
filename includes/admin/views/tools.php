<div class="wrap">
    <h2><?php _e( 'Tools', 'wp-erp' ); ?></h2>

    <div class="metabox-holder">
        <div class="postbox">
            <h3><?php _e( 'Admin Menu', 'wp-erp' ); ?></h3>

            <div class="inside">
                <p><?php _e( 'Remove default admin sidebar menus', 'wp-erp' ); ?></p>

                <form method="post" action="<?php echo admin_url( 'admin.php?page=erp-tools' ); ?>">
                    <?php
                    $menus          = get_option( '_erp_admin_menu', array() );
                    $adminbar_menus = get_option( '_erp_adminbar_menu', array() );
                    ?>
                    <p>
                        <label><input type="checkbox" name="menu[]" value="index.php" <?php checked( in_array( 'index.php', $menus), true ); ?>><?php _e( 'Dashboard', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="edit.php" <?php checked( in_array( 'edit.php', $menus), true ); ?>><?php _e( 'Posts', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="upload.php" <?php checked( in_array( 'upload.php', $menus), true ); ?>><?php _e( 'Media', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="edit.php?post_type=page" <?php checked( in_array( 'edit.php?post_type=page', $menus), true ); ?>><?php _e( 'Pages', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="edit-comments.php" <?php checked( in_array( 'edit-comments.php', $menus), true ); ?>><?php _e( 'Comments', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="themes.php" <?php checked( in_array( 'themes.php', $menus), true ); ?>><?php _e( 'Themes', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="plugins.php" <?php checked( in_array( 'plugins.php', $menus), true ); ?>><?php _e( 'Plugins', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="users.php" <?php checked( in_array( 'users.php', $menus), true ); ?>><?php _e( 'Users', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="tools.php" <?php checked( in_array( 'tools.php', $menus), true ); ?>><?php _e( 'Tools', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="menu[]" value="options-general.php" <?php checked( in_array( 'options-general.php', $menus), true ); ?>><?php _e( 'Settings', 'wp-erp' ); ?></label>&nbsp;
                    </p>

                    <h4><?php _e( 'Admin Bar Menu', 'wp-erp' ); ?></h4>
                    <p>
                        <label><input type="checkbox" name="admin_menu[]" value="wp-logo" <?php checked( in_array( 'wp-logo', $adminbar_menus), true ); ?>><?php _e( 'WordPress Logo', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="admin_menu[]" value="site-name" <?php checked( in_array( 'site-name', $adminbar_menus), true ); ?>><?php _e( 'Site Name', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="admin_menu[]" value="updates" <?php checked( in_array( 'updates', $adminbar_menus), true ); ?>><?php _e( 'Updates', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="admin_menu[]" value="comments" <?php checked( in_array( 'comments', $adminbar_menus), true ); ?>><?php _e( 'Comments', 'wp-erp' ); ?></label>&nbsp;
                        <label><input type="checkbox" name="admin_menu[]" value="new-content" <?php checked( in_array( 'new-content', $adminbar_menus), true ); ?>><?php _e( 'New Posts', 'wp-erp' ); ?></label>&nbsp;
                    </p>

                    <?php wp_nonce_field( 'erp-remove-menu-nonce' ); ?>
                    <?php submit_button( __( 'Save Changes', 'wp-erp' ), 'primary', 'erp_admin_menu' ); ?>
                </form>
            </div><!-- .inside -->
        </div><!-- .postbox -->
    </div><!-- .metabox-holder -->
</div>