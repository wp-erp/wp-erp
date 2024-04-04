<?php if ( is_admin() ) : ?>
    <script>
        window.erpSettings = JSON.parse('<?php echo wp_kses_post( wp_slash(
            wp_json_encode( apply_filters( 'erp_localized_data', [] ) )
        ) ); ?>');
    </script>
<?php endif; ?>

<div id="erp-settings"></div>