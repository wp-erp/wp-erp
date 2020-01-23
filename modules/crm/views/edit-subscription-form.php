<link rel="stylesheet" href="<?php echo esc_url_raw(WPERP_CRM_ASSETS . '/css/erp-subscription-edit.css?ver=' . WPERP_VERSION ); ?>">

<?php do_action( 'erp_edit_subscription_page_before_form' ); ?>

<?php if ( ! empty( $contact_lists ) ): ?>
    <form class="<?php echo esc_html( implode( ' ', $class_names ) ); ?>">
        <p><?php echo esc_html( $page_content ); ?></p>

        <ul>
            <?php foreach ( $contact_lists as $list_type => $lists ): ?>
                <?php $lists->each( function ( $list ) use ( $list_type ) { ?>
                    <li>
                        <label>
                            <input
                                <?php $checked = empty( $list->unsubscribe_at ) ? 'checked' : '' ?>
                                type="checkbox"
                                name="<?php echo esc_attr( $list_type ) ?>[<?php echo esc_attr( $list->id ) ?>]"
                                <?php echo esc_attr( $checked ) ?>
                            > <?php echo esc_attr( $list->name ); ?>
                        </label>
                    </li>
                <?php } ); ?>
            <?php endforeach; ?>
        </ul>

        <input type="hidden" name="id" value="<?php echo esc_attr( $hash ); ?>">

        <button type="submit">
            <span class="submit-btn-label"><?php echo esc_html( __( 'Update', 'erp' ) ); ?></span>
            <span class="erp-spinner"></span>
        </button>

        <div class="erp-subscription-edit-msg"></div>
    </form>
<?php endif; ?>
