<div class="wrap">
    <h2><?php _e( 'Update Account', 'accounting' ); ?></h2>

    <form action="" method="post" class="erp-form">

        <?php $item = erp_ac_get_chart( $id ); ?>

        <?php include dirname( __FILE__ ) . '/form-fields.php'; ?>

        <?php wp_nonce_field( 'erp-ac-chart' ); ?>
        <?php submit_button( __( 'Update Account', 'accounting' ), 'primary', 'submit_erp_ac_chart' ); ?>

    </form>
</div>