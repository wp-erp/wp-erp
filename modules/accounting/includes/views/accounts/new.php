<div class="wrap">
    <h2><?php _e( 'Add Account', 'erp' ); ?></h2>

    <form action="" method="post" class="erp-form" id="erp-ac-accounts-form">

        <?php $item = null; ?>

        <?php include dirname( __FILE__ ) . '/form-fields.php'; ?>

        <?php wp_nonce_field( 'erp-ac-chart' ); ?>
        <?php submit_button( __( 'Add Account', 'erp' ), 'primary', 'submit_erp_ac_chart' ); ?>

    </form>
</div>