<div class="wrap erp-ac-users-wrap">
    <h2><?php _e( 'Add Vendor', 'erp-accounting' ); ?></h2>

    <form action="" method="post" class="erp-form">

        <?php
        $item = null;
        $item_type = 'vendor';
        include dirname( dirname( __FILE__ ) ) . '/user-form-rows.php';
        ?>

        <input type="hidden" name="field_id" value="0">
        <input type="hidden" name="type" value="vendor">

        <?php wp_nonce_field( 'erp-ac-customer' ); ?>
        <?php submit_button( __( 'Add Vendor', 'erp-accounting' ), 'primary', 'submit_erp_ac_customer' ); ?>

    </form>
</div>
