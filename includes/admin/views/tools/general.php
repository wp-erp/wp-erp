
<div class="postbox">
    <h3><?php esc_html_e( 'Admin Menu', 'erp' ); ?></h3>

    <div class="inside">
        <p><?php esc_html_e( 'Remove default admin sidebar menus', 'erp' ); ?></p>

        <form method="post" class="erp-tools-form" action="<?php echo esc_url( admin_url( 'admin.php?page=erp-tools' ) ); ?>">
            <?php
            $menus          = get_option( '_erp_admin_menu', array() );
            $adminbar_menus = get_option( '_erp_adminbar_menu', array() );
            ?>
            <ul class="erp-list list-inline">
                <li><label><input type="checkbox" name="menu[]" value="index.php" <?php checked( in_array( 'index.php', $menus), true ); ?>><?php esc_html_e( 'Dashboard', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="edit.php" <?php checked( in_array( 'edit.php', $menus), true ); ?>><?php esc_html_e( 'Posts', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="upload.php" <?php checked( in_array( 'upload.php', $menus), true ); ?>><?php esc_html_e( 'Media', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="edit.php?post_type=page" <?php checked( in_array( 'edit.php?post_type=page', $menus), true ); ?>><?php esc_html_e( 'Pages', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="edit-comments.php" <?php checked( in_array( 'edit-comments.php', $menus), true ); ?>><?php esc_html_e( 'Comments', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="themes.php" <?php checked( in_array( 'themes.php', $menus), true ); ?>><?php esc_html_e( 'Themes', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="plugins.php" <?php checked( in_array( 'plugins.php', $menus), true ); ?>><?php esc_html_e( 'Plugins', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="users.php" <?php checked( in_array( 'users.php', $menus), true ); ?>><?php esc_html_e( 'Users', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="tools.php" <?php checked( in_array( 'tools.php', $menus), true ); ?>><?php esc_html_e( 'Tools', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="menu[]" value="options-general.php" <?php checked( in_array( 'options-general.php', $menus), true ); ?>><?php esc_html_e( 'Settings', 'erp' ); ?></label></li>
            </ul>

            <h4><?php esc_html_e( 'Admin Bar Menu', 'erp' ); ?></h4>
            <ul class="erp-list list-inline">
                <li><label><input type="checkbox" name="admin_menu[]" value="wp-logo" <?php checked( in_array( 'wp-logo', $adminbar_menus), true ); ?>><?php esc_html_e( 'WordPress Logo', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="site-name" <?php checked( in_array( 'site-name', $adminbar_menus), true ); ?>><?php esc_html_e( 'Site Name', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="updates" <?php checked( in_array( 'updates', $adminbar_menus), true ); ?>><?php esc_html_e( 'Updates', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="comments" <?php checked( in_array( 'comments', $adminbar_menus), true ); ?>><?php esc_html_e( 'Comments', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="new-content" <?php checked( in_array( 'new-content', $adminbar_menus), true ); ?>><?php esc_html_e( 'New Posts', 'erp' ); ?></label></li>
            </ul>

            <?php wp_nonce_field( 'erp-remove-menu-nonce' ); ?>
            <?php submit_button( esc_html__( 'Save Changes', 'erp' ), 'primary', 'erp_admin_menu' ); ?>
        </form>
    </div><!-- .inside -->
</div><!-- .postbox -->

<?php do_action( 'erp_tools_page' ); ?>
