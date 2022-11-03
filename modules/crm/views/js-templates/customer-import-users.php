<?php
    global $wp_roles;

    delete_option( 'erp_users_to_contacts_import_attempt' );
    delete_option( 'erp_users_to_contacts_import_exists' );

    $roles        = $wp_roles->get_names();
    $default_role = get_option( 'default_role', '' );

    $users        = [];
    $life_stages  = [];
    $groups       = [];

    $life_stages  = erp_crm_get_life_stages_dropdown_raw();
    $crm_users    = erp_crm_get_crm_user();

    foreach ( $crm_users as $user ) {
        $users[ $user->ID ] = $user->display_name . ' &lt;' . $user->user_email . '&gt;';
    }

    $contact_groups = erp_crm_get_contact_groups( [ 'number' => '-1' ] );
    $groups         = [ '' => __( '&mdash; Select Group &mdash;', 'erp' ) ];

    foreach ( $contact_groups as $group ) {
        $groups[ $group->id ] = $group->name;
    }
?>

<table class="form-table">
    <tbody>
        <tr>
            <th>
                <label for="user_role"><?php esc_html_e( 'User Role', 'erp' ); ?></label>
            </th>
            <td>
                <select name="user_role" class="erp-select2" id="user_role" multiple="true">
                    <?php
                    echo wp_kses( erp_html_generate_dropdown( $roles, $default_role ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] );
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'Selected user role are considered to import.', 'erp' ); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="contact_owner"><?php esc_html_e( 'Contact Owner', 'erp' ); ?></label>
            </th>
            <td>
                <select name="contact_owner" id="contact_owner">
                    <?php
                    $current_user = get_current_user_id();
                    echo wp_kses( erp_html_generate_dropdown( $users, $current_user ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] );
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'Contact owner for contact.', 'erp' ); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="life_stage"><?php esc_html_e( 'Life Stage', 'erp' ); ?></label>
            </th>
            <td>
                <select name="life_stage" id="life_stage">
                    <?php
                    echo wp_kses( erp_html_generate_dropdown( $life_stages ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] );
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'Life stage for contact.', 'erp' ); ?></p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="contact_group"><?php esc_html_e( 'Contact Group', 'erp' ); ?></label>
            </th>
            <td>
                <select name="contact_group">
                    <?php
                    echo wp_kses( erp_html_generate_dropdown( $groups ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] );
                    ?>
                </select>
                <p class="description"><?php esc_html_e( 'Imported contacts will be subscribed in selected group.', 'erp' ); ?></p>
            </td>
        </tr>
    </tbody>
</table>

<br />

<div id="import-status-indicator" class="erp-progress-status-indicator" style="display: none;">
    <div class="status">
        <span id="progress-total"><?php esc_html_e( '100', 'erp' ); ?>%</span>
    </div>
    <div class="progress">
        <progress id="progressbar-total" max="100" value="0"></progress>
    </div>
    <div class="status">
        <span id="completed-total"></span>
        <span id="failed-total"></span>
    </div>
</div>

<input type="hidden" name="action" value="erp_import_users_as_contacts">
<?php wp_nonce_field( 'erp-import-export-nonce' ); ?>