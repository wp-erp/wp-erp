<?php

    if ( ! $is_crm_activated && ! $is_hrm_activated ) {
        return;
    }

    $page           = '?page=erp-tools&tab=import&action=download_sample';
    $nonce          = 'erp-emport-export-sample-nonce';
    $csv_sample_url = wp_nonce_url( $page, $nonce );

    $users       = [];
    $life_stages = [];
    $groups      = [];

    if ( $is_crm_activated ) {
        $life_stages    = erp_crm_get_life_stages_dropdown_raw();
        $crm_users      = erp_crm_get_crm_user();

        foreach ( $crm_users as $user ) {
            $users[ $user->ID ] = $user->display_name . ' &lt;' . $user->user_email . '&gt;';
        }

        $contact_groups = erp_crm_get_contact_groups( [ 'number' => '-1' ] );

        $groups = ['' => __( '&mdash; Select Group &mdash;', 'erp' )];
        foreach ( $contact_groups as $group ) {
            $groups[ $group->id ] = $group->name;
        }
    }
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
                                <?php foreach ( $import_export_types as $key => $value ) { ?>
                                    <option value="<?php echo $key; ?>"><?php _e( $value, 'erp' ); ?></option>
                                <?php } ?>
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
                            <p id="download_sample_wrap">
                                <input type="hidden" value="<?php echo $csv_sample_url; ?>" />
                                <a href="<?php echo $csv_sample_url; ?>&type=contact">Download Sample CSV</a>
                            </p>
                        </td>
                    </tr>
                </tbody>
                <tbody id="crm_contact_lifestage_owner_wrap">
                    <tr>
                        <th>
                            <label for="contact_owner"><?php _e( 'Contact Owner', 'erp' ); ?></label>
                        </th>
                        <td>
                            <select name="contact_owner" id="contact_owner">
                                <?php
                                    $current_user = get_current_user_id();
                                    echo erp_html_generate_dropdown( $users, $current_user );
                                ?>
                            </select>
                            <p class="description"><?php _e( 'Contact owner for contact.', 'erp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="life_stage"><?php _e( 'Life Stage', 'erp' ); ?></label>
                        </th>
                        <td>
                            <select name="life_stage" id="life_stage">
                                <?php echo erp_html_generate_dropdown( $life_stages ); ?>
                            </select>
                            <p class="description"><?php _e( 'Life stage for contact.', 'erp' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="contact_group"><?php _e( 'Contact Group', 'erp' ); ?></label>
                        </th>
                        <td>
                            <select name="contact_group">
                                <?php echo erp_html_generate_dropdown( $groups ); ?>
                            </select>
                            <p class="description"><?php _e( 'Imported contacts will be subscribed in selected group.', 'erp' ); ?></p>
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

<?php if ( $is_crm_activated ) { ?>
    <div class="postbox">
        <div class="inside">
            <h3><?php _e( 'Import Users into CRM', 'erp' ); ?></h3>

            <form method="post" action="<?php echo admin_url( 'admin.php?page=erp-tools' ); ?>" enctype="multipart/form-data" id="users_import_form">

                <?php
                    global $wp_roles;
                    delete_option( 'erp_users_to_contacts_import_attempt' );
                    delete_option( 'erp_users_to_contacts_import_exists' );

                    $roles        = $wp_roles->get_names();
                    $default_role = get_option( 'default_role', '' );
                ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th>
                                <label for="user_role"><?php _e( 'User Role', 'erp' ); ?></label>
                            </th>
                            <td>
                                <select name="user_role" class="erp-select2" id="user_role" multiple="true">
                                    <?php echo erp_html_generate_dropdown( $roles, $default_role ); ?>
                                </select>
                                <p class="description"><?php _e( 'Selected user role are considered to import.', 'erp' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="contact_owner"><?php _e( 'Contact Owner', 'erp' ); ?></label>
                            </th>
                            <td>
                                <select name="contact_owner" id="contact_owner">
                                    <?php
                                        $current_user = get_current_user_id();
                                        echo erp_html_generate_dropdown( $users, $current_user );
                                    ?>
                                </select>
                                <p class="description"><?php _e( 'Contact owner for contact.', 'erp' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="life_stage"><?php _e( 'Life Stage', 'erp' ); ?></label>
                            </th>
                            <td>
                                <select name="life_stage" id="life_stage">
                                    <?php echo erp_html_generate_dropdown( $life_stages ); ?>
                                </select>
                                <p class="description"><?php _e( 'Life stage for contact.', 'erp' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="contact_group"><?php _e( 'Contact Group', 'erp' ); ?></label>
                            </th>
                            <td>
                                <select name="contact_group">
                                    <?php echo erp_html_generate_dropdown( $groups ); ?>
                                </select>
                                <p class="description"><?php _e( 'Imported contacts will be subscribed in selected group.', 'erp' ); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br />
                <div id="import-status-indicator" class="erp-progress-status-indicator" style="display: none;">
                    <div class="status">
                        <span id="progress-total">100%</span>
                    </div>
                    <div class="progress">
                        <progress id="progressbar-total" max="100" value="0"></progress>
                    </div>
                    <div class="status">
                        <span id="completed-total"></span>
                        <span id="failed-total"></span>
                    </div>
                </div>

                <?php wp_nonce_field( 'erp-import-export-nonce' ); ?>
                <?php submit_button( __( 'Import', 'erp' ), 'primary', 'erp_import_users' ); ?>
            </form>
        </div><!-- .inside -->
    </div><!-- .postbox -->
<?php
    }
?>
