<div class="wrap">

    <?php
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

        $is_crm_activated = wperp()->modules->is_module_active( 'crm' );
        $is_hrm_activated = wperp()->modules->is_module_active( 'hrm' );

        $types = [];
        if ( $is_crm_activated ) {
            $types['contact'] = 'Contact';
            $types['company'] = 'Company';
        }

        if ( $is_hrm_activated ) {
            $types['employee'] = 'Employee';
        }

        $types = apply_filters( 'erp_import_export_item_types', $types );
    ?>

    <h2 class="nav-tab-wrapper erp-nav-tab-wrapper">
        <a class="nav-tab <?php echo ( $tab == 'general' ) ? 'nav-tab-active' : ''; ?>" href="admin.php?page=erp-tools">General</a>
        <?php
        if ( $is_crm_activated || $is_hrm_activated ) {
        ?>
            <a class="nav-tab <?php echo ( $tab == 'import' ) ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=erp-tools&tab=import' ); ?>">Import</a>
            <a class="nav-tab <?php echo ( $tab == 'export' ) ? 'nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=erp-tools&tab=export' ); ?>">Export</a>
        <?php
        }
        ?>
    </h2>

    <div class="metabox-holder">

        <?php
        if ( $tab == 'import' ) {
        ?>
            <div class="postbox">
                <div class="inside">
                        <h3><?php _e( 'Import CSV', 'erp' ); ?></h3>

                        <form method="post" action="<?php echo admin_url( 'admin.php?page=erp-tools' ); ?>" enctype="multipart/form-data" id="import_form">

                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th>
                                            <label for="type"><?php _e( 'Type', 'erp' ); ?></label>
                                        </th>
                                        <td>
                                            <select name="type" id="type">
                                                <?php
                                                    foreach ( $types as $key => $value ) {
                                                ?>
                                                        <option value="<?php echo $key; ?>"><?php _e( $value, 'erp' ); ?></option>
                                                <?php
                                                    }
                                                ?>
                                            </select>
                                            <p class="description"><?php _e( 'Select item type to import.', 'erp' ); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="type"><?php _e( 'CSV File', 'erp' ); ?> <span class="required">*</span></label>
                                        </th>
                                        <td>
                                            <input type="file" name="csv_file" id="csv_file" />
                                            <p class="description"><?php _e( 'Upload a csv file.', 'erp' ); ?></p>
                                        </td>
                                    </tr>
                                </tbody>

                                <tbody id="fields_container" style="display: none;">

                                </tbody>
                            </table>

                            <?php wp_nonce_field( 'erp-import-export-nonce' ); ?>
                            <?php submit_button( __( 'Import', 'erp' ), 'primary', 'erp_import_csv' ); ?>
                        </form>
                </div><!-- .inside -->
            </div><!-- .postbox -->

        <?php
        } elseif ( $tab == 'export' ) {
        ?>
            <div class="postbox">
                <div class="inside">
                        <h3><?php _e( 'Export CSV', 'erp' ); ?></h3>

                        <form method="post" action="<?php echo admin_url( 'admin.php?page=erp-tools' ); ?>" id="export_form">

                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th>
                                            <label for="type"><?php _e( 'Type', 'erp' ); ?></label>
                                        </th>
                                        <td>
                                            <select name="type" id="type">
                                                <?php
                                                    foreach ( $types as $key => $value ) {
                                                ?>
                                                        <option value="<?php echo $key; ?>"><?php _e( $value, 'erp' ); ?></option>
                                                <?php
                                                    }
                                                ?>
                                            </select>
                                            <p class="description"><?php _e( 'Select item type to export.', 'erp' ); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="fields"><?php _e( 'Fields', 'erp' ); ?> <span class="required">*</span></label>
                                        </th>
                                        <td>
                                            <label><input type="checkbox" id="selecctall"/> <strong><?php _e( 'Select All', 'erp' ); ?></strong></label>
                                            <br />
                                            <div id="fields"></div>
                                            <p class="description"><?php _e( 'Only selected field will be on the csv file.', 'erp' ); ?></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <?php wp_nonce_field( 'erp-import-export-nonce' ); ?>
                            <?php submit_button( __( 'Export', 'erp' ), 'primary', 'erp_export_csv' ); ?>
                        </form>
                </div><!-- .inside -->
            </div><!-- .postbox -->

        <?php
        } else {
        ?>

            <div class="postbox">
                <h3><?php _e( 'Admin Menu', 'erp' ); ?></h3>

                <div class="inside">
                    <p><?php _e( 'Remove default admin sidebar menus', 'erp' ); ?></p>

                    <form method="post" action="<?php echo admin_url( 'admin.php?page=erp-tools' ); ?>">
                        <?php
                        $menus          = get_option( '_erp_admin_menu', array() );
                        $adminbar_menus = get_option( '_erp_adminbar_menu', array() );
                        ?>
                        <ul class="erp-list list-inline">
                            <li><label><input type="checkbox" name="menu[]" value="index.php" <?php checked( in_array( 'index.php', $menus), true ); ?>><?php _e( 'Dashboard', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="edit.php" <?php checked( in_array( 'edit.php', $menus), true ); ?>><?php _e( 'Posts', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="upload.php" <?php checked( in_array( 'upload.php', $menus), true ); ?>><?php _e( 'Media', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="edit.php?post_type=page" <?php checked( in_array( 'edit.php?post_type=page', $menus), true ); ?>><?php _e( 'Pages', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="edit-comments.php" <?php checked( in_array( 'edit-comments.php', $menus), true ); ?>><?php _e( 'Comments', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="themes.php" <?php checked( in_array( 'themes.php', $menus), true ); ?>><?php _e( 'Themes', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="plugins.php" <?php checked( in_array( 'plugins.php', $menus), true ); ?>><?php _e( 'Plugins', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="users.php" <?php checked( in_array( 'users.php', $menus), true ); ?>><?php _e( 'Users', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="tools.php" <?php checked( in_array( 'tools.php', $menus), true ); ?>><?php _e( 'Tools', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="menu[]" value="options-general.php" <?php checked( in_array( 'options-general.php', $menus), true ); ?>><?php _e( 'Settings', 'erp' ); ?></label></li>
                        </ul>

                        <h4><?php _e( 'Admin Bar Menu', 'erp' ); ?></h4>
                        <ul class="erp-list list-inline">
                            <li><label><input type="checkbox" name="admin_menu[]" value="wp-logo" <?php checked( in_array( 'wp-logo', $adminbar_menus), true ); ?>><?php _e( 'WordPress Logo', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="admin_menu[]" value="site-name" <?php checked( in_array( 'site-name', $adminbar_menus), true ); ?>><?php _e( 'Site Name', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="admin_menu[]" value="updates" <?php checked( in_array( 'updates', $adminbar_menus), true ); ?>><?php _e( 'Updates', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="admin_menu[]" value="comments" <?php checked( in_array( 'comments', $adminbar_menus), true ); ?>><?php _e( 'Comments', 'erp' ); ?></label></li>
                            <li><label><input type="checkbox" name="admin_menu[]" value="new-content" <?php checked( in_array( 'new-content', $adminbar_menus), true ); ?>><?php _e( 'New Posts', 'erp' ); ?></label></li>
                        </ul>

                        <?php wp_nonce_field( 'erp-remove-menu-nonce' ); ?>
                        <?php submit_button( __( 'Save Changes', 'erp' ), 'primary', 'erp_admin_menu' ); ?>
                    </form>
                </div><!-- .inside -->
            </div><!-- .postbox -->

            <?php do_action( 'erp_tools_page' ); ?>
        <?php
        }
        ?>


    </div><!-- .metabox-holder -->
</div>
