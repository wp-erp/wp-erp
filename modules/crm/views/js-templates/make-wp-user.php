<div class="erp-crm-customer-make-wpuser" id="erp-crm-customer-make-wpuser">
    <# if ( '' == data.email ) { #>
        <div class="row">
            <label for="make-wp-user-customer-email"><?php esc_attr_e( 'Enter your email', 'erp' ); ?></label>
            <input type="email" name="customeresc_attr_email" id="make-wp-user-customer-email" placeholder="<?php echo 'Enter your email' ?>" required>
        </div>
    <# } #>
    <div class="row">
        <label for="wp-user-role"><?php esc_attr_e( 'Role', 'erp' ) ?></label>
        <select name="customer_role" id="wp-user-role">
            <?php erp_dropdown_roles( get_option('default_role') ); ?>
        </select>
    </div>

    <div class="row">
        <label for="send-password-to-email"><?php esc_attr_e( 'Send password' ) ?></label>
        <span class="checkbox">
            <label for="send-password-to-email">
                <input type="checkbox" id="send-password-to-email" name="send_password_notification"> <?php esc_attr_e( 'Send password to this user email', 'erp' ) ?>
            </label>
        </span>
    </div>
    <?php wp_nonce_field( 'erp-crm-make-wp-user' ); ?>
    <input type="hidden" name="action" value="erp-crm-make-wp-user">
    <input type="hidden" name="id" value="{{ data.id }}">
    <input type="hidden" name="type" value="{{ data.type }}">
</div>
