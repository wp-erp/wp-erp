<div class="postbox">
    <h3>
        <?php esc_html_e( 'Admin Menu', 'erp' ); ?>
        <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo erp_help_tip( esc_html__( 'If you select any, the menu will be hidden for all users.', 'erp' ) );
        ?>
    </h3>

    <div class="inside">
        <p><?php esc_html_e( 'Remove default admin sidebar menus', 'erp' ); ?></p>

        <form method="post" class="erp-tools-form" action="<?php echo esc_url( admin_url( 'admin.php?page=erp-tools' ) ); ?>">
            <?php
            global $menu;
            $menus = [];

            foreach ( $menu as $single_menu ) {
                if ( ! empty ( $single_menu[0] ) && ! empty ( $single_menu[2] ) && $single_menu[2] !== 'erp' ) { // Without ERP
                    $single_menu[0] = explode( '<', $single_menu[0] )[0]; // Remove if any <span> inside title
                    array_push( $menus, $single_menu );
                }
            }

            $inactive_menu_options = get_option( '_erp_admin_menu', [] );
            $inactive_menus        = [];

            foreach ( (array) $inactive_menu_options as $inactive_menu ) {
                $single_menu = erp_serialize_string_to_array( $inactive_menu );

                if ( ! empty ( $single_menu[0] ) && ! empty ( $single_menu[2] ) && $single_menu[2] !== 'erp' ) {
                    array_push( $inactive_menus, $single_menu );
                }
            }

            $menus          = array_merge( $menus, $inactive_menus );
            $adminbar_menus = get_option( '_erp_adminbar_menu', [] );
            ?>
            <ul class="erp-list list-inline erp-toolbar-menu">
                <?php foreach ( $menus as $menu_item ): ?>
                    <li>
                        <label>
                            <input type="checkbox" name="menu[]" value="<?php
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            echo htmlspecialchars( serialize( $menu_item ) ); ?>" <?php checked( in_array( $menu_item, $inactive_menus ), true ); ?>>
                            <?php echo esc_html( $menu_item[0] ); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h4><?php esc_html_e( 'Admin Bar Menu', 'erp' ); ?></h4>
            <ul class="erp-list list-inline">
                <li><label><input type="checkbox" name="admin_menu[]" value="wp-logo" <?php checked( in_array( 'wp-logo', $adminbar_menus ), true ); ?>><?php esc_html_e( 'WordPress Logo', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="site-name" <?php checked( in_array( 'site-name', $adminbar_menus ), true ); ?>><?php esc_html_e( 'Site Name', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="updates" <?php checked( in_array( 'updates', $adminbar_menus ), true ); ?>><?php esc_html_e( 'Updates', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="comments" <?php checked( in_array( 'comments', $adminbar_menus ), true ); ?>><?php esc_html_e( 'Comments', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="new-content" <?php checked( in_array( 'new-content', $adminbar_menus ), true ); ?>><?php esc_html_e( 'New Posts', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="wp-erp-acct" <?php checked( in_array( 'wp-erp-acct', $adminbar_menus ), true ); ?>><?php esc_html_e( 'New Transaction', 'erp' ); ?></label></li>
                <li><label><input type="checkbox" name="admin_menu[]" value="wp-erp" <?php checked( in_array( 'wp-erp', $adminbar_menus ), true ); ?>><?php esc_html_e( 'WP ERP', 'erp' ); ?></label></li>
            </ul>

            <?php wp_nonce_field( 'erp-remove-menu-nonce' ); ?>
            <?php submit_button( esc_html__( 'Save Changes', 'erp' ), 'primary', 'erp_admin_menu' ); ?>
        </form>
    </div><!-- .inside -->
</div><!-- .postbox -->

<?php do_action( 'erp_tools_page' ); ?>
