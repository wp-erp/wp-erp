<?php do_action( 'erp_email_header', $email_heading ); ?>

<?php echo apply_filters( 'erp_email_body', $email_body ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php do_action( 'erp_email_footer' ); ?>
