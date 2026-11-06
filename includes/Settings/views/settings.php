<?php if ( is_admin() ) : ?>
    <script>
        window.erpSettings = <?php echo wp_json_encode( apply_filters( 'erp_localized_data', [] ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
    </script>
<?php endif; ?>

<div id="erp-settings"></div>