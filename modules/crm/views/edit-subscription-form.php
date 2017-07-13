<link rel="stylesheet" href="<?php echo WPERP_CRM_ASSETS . '/css/erp-subscription-edit.css?ver=' . WPERP_VERSION; ?>">

<?php do_action( 'erp_edit_subscription_page_before_form' ); ?>

<?php if ( ! empty( $contact_lists ) ): ?>
    <form class="<?php echo implode( ' ', $class_names ); ?>">
        <p><?php echo $page_content; ?></p>

        <ul>
            <?php foreach ( $contact_lists as $list_type => $lists ): ?>
                <?php $lists->each( function ( $list ) use ( $list_type ) { ?>
                    <li>
                        <label>
                            <input
                                type="checkbox"
                                name="<?php echo $list_type ?>[<?php echo $list->id ?>]"
                                <?php echo empty( $list->unsubscribe_at ) ? 'checked' : '' ?>
                            > <?php echo $list->name; ?>
                        </label>
                    </li>
                <?php } ); ?>
            <?php endforeach; ?>
        </ul>

        <input type="hidden" name="id" value="<?php echo $hash; ?>">

        <button type="submit">
            <span class="submit-btn-label"><?php echo __( 'Update', 'erp' ); ?></span>
            <span class="erp-spinner"></span>
        </button>

        <div class="erp-subscription-edit-msg"></div>
    </form>
<?php endif; ?>
