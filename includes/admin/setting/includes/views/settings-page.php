<div class="wrap bediq-settings">

	<form method="post" id="mainform" action="" enctype="multipart/form-data">

	    <?php bedIQ_Admin_Settings::output(); ?>

	    <?php
	    global $current_class;

	    do_action( 'bediq_after_admin_settings_' . $current_class->get_id() );
	    ?>

	    <p class="submit">
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'bediq' ); ?>" />
	    	<input type="hidden" name="subtab" id="last_tab" />

	    	<?php wp_nonce_field( 'bediq-settings-nonce' ); ?>
	    </p>
   </form>
</div>