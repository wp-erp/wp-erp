<div class="wrap erp-settings">

	<form method="post" id="mainform" action="" enctype="multipart/form-data">

	    <?php WeDevs\ERP\Framework\ERP_Admin_Settings::output(); ?>

	    <?php
	    global $current_class;

	    do_action( 'erp_after_admin_settings_' . $current_class->get_id() );
	    ?>

	    <p class="submit">
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'wp-erp' ); ?>" />
	    	<input type="hidden" name="subtab" id="last_tab" />

	    	<?php wp_nonce_field( 'erp-settings-nonce' ); ?>
	    </p>
   </form>
</div>