<div class="wrap">
    <h2><?php _e( 'Update Vendor', 'erp-accounting' ); ?></h2>

    <?php $item = erp_ac_get_customer( $id ); ?>

    <form action="" method="post" class="erp-form">

        <?php include dirname( dirname( __FILE__ ) ) . '/user-form-rows.php'; ?>

        <input type="hidden" name="field_id" value="<?php echo $item->id; ?>">
        <input type="hidden" name="type" value="vendor">

        <?php wp_nonce_field( 'erp-ac-customer' ); ?>
        <?php submit_button( __( 'Update Vendor', 'erp-accounting' ), 'primary', 'submit_erp_ac_customer' ); ?>

    </form>
</div>