<ul class="edit-key">
    <li class="row">
        <?php erp_html_form_input( array(
            'label'    => __( 'App Name', 'erp' ),
            'name'     => 'name',
            'value'    => '{{ data.name }}',
            'required' => true
        ) ); ?>
    </li>

    <li class="row" data-selected="{{ data.user_id }}">
        <label for="user_id"><?php _e( 'User', 'erp' ); ?> <span class="required">*</span></label>
        <select name="user_id" class="erp-api-user-select select2" data-parent="ul">
            <?php
                $crm_users = erp_crm_get_crm_user();

                foreach ( $crm_users as $user ) {
                    echo '<option value="' . $user->ID . '">' . $user->display_name . ' &lt;' . $user->user_email . '&gt;' . '</option>';
                }
            ?>
        </select>
    </li>

    <br />
    <li class="row api_access">
        <label for="api_key"><?php _e( 'Consumer Key', 'erp' ); ?> </label>
        <code>{{ data.api_key }}</code>
    </li>

    <li class="row api_access">
        <label for="api_secret"><?php _e( 'Consumer Secret', 'erp' ); ?> </label>
        <code>{{ data.api_secret }}</code>
    </li>

    <input type="hidden" name="id" value="{{ data.id }}">
    <input type="hidden" name="action" value="erp-api-key">
    <?php wp_nonce_field( 'erp-api-key' ); ?>
</ul>
