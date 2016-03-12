<?php do_action( 'erp_email_header', $email_heading ); ?>

<p>Dear <?php echo $employee->get_full_name(); ?>!</p>

<?php //var_dump( $employee ); ?>

<p>
    Welcome to weDevs.
</p>

<p>We are excited about you joining us and want to ensure that you are successful in your new role. Please
don’t hesitate to contact me with any questions or concerns. We look forward to a positive working
relationship!</p>

Sincerely,

<p>
Nizam Uddin<br>
CEO, weDevs Limited
</p>

<?php do_action( 'erp_email_footer' ); ?>