<form class="<?php echo wp_kses_post( implode( ' ', $class_names ) ); ?>">
    <?php do_action( 'erp_subscription_form_start', $args ); ?>

    <?php if ( ! empty( $args['full_name_lbl'] ) ): ?>
        <label class="full-name">
            <?php echo esc_html( $args['full_name_lbl'] ); ?>
            <input type="text" name="contact[full_name]" placeholder="<?php echo esc_html( $args['full_name_placeholder'] );?>">
        </label>

    <?php else: ?>
        <?php if ( ! empty( $args['first_name_lbl'] ) ): ?>
            <label class="first-name">
                <?php echo esc_html( $args['first_name_lbl'] ); ?>
                <input type="text" name="contact[first_name]" placeholder="<?php echo esc_html( $args['first_name_placeholder'] );?>">
            </label>
        <?php endif; ?>

        <?php if ( ! empty( $args['last_name_lbl'] ) ): ?>
            <label class="last-name">
                <?php echo esc_html( $args['last_name_lbl'] ); ?>
                <input type="text" name="contact[last_name]" placeholder="<?php echo esc_html( $args['last_name_placeholder'] );?>">
            </label>
        <?php endif; ?>
    <?php endif; ?>

    <?php do_action( 'erp_subscription_form_before_email', $args ); ?>

    <label class="email">
        <?php echo esc_html( $args['email_lbl'] ); ?>
        <input type="email" name="contact[email]" placeholder="<?php echo esc_html( $args['email_placeholder'] );?>">
    </label>

    <?php do_action( 'erp_subscription_form_after_email', $args ); ?>

    <?php foreach ( $contact_groups as $group_id ): ?>
        <input type="hidden" name="groups[]" value="<?php echo esc_attr( $group_id ); ?>">
    <?php endforeach; ?>

    <?php if ( $args['life_stage'] ): ?>
        <input type="hidden" name="life_stage" value="<?php echo esc_attr( $args['life_stage'] ); ?>">
    <?php endif; ?>

    <button type="submit">
        <span class="submit-btn-label"><?php echo esc_attr( $args['button_lbl'] ); ?></span>
        <span class="erp-spinner"></span>
    </button>

    <?php do_action( 'erp_subscription_form_end', $args ); ?>

    <div class="erp-subscription-form-msg"></div>
</form>
