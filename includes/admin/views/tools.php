<div class="wrap">
    <h2><?php _e( 'Tools', 'wp-erp' ); ?></h2>

    <div class="metabox-holder">
        <div class="postbox">
            <h3><?php _e( 'Admin Menu', 'wp-erp' ); ?></h3>

            <div class="inside">
                <p><?php _e( 'Remove default admin sidebar menus', 'wp-erp' ); ?></p>

                <form method="post" action="<?php echo admin_url( 'admin.php?page=erp-tools' ); ?>">
                    <?php $menus = get_option( '_erp_admin_menu', array() ); ?>
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

                    <?php wp_nonce_field( 'erp-remove-menu-nonce' ); ?>
                    <?php submit_button( __( 'Save Changes', 'wp-erp' ), 'primary', 'erp_admin_menu' ); ?>
                </form>
            </div><!-- .inside -->
        </div><!-- .postbox -->
    </div><!-- .metabox-holder -->
</div>