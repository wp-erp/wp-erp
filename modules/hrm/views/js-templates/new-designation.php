<div class="row">
    <label for="desig-title"><?php esc_html_e( 'Designation Title', 'erp' ); ?> <span class="required">*</span></label>
    <span class="field">
        <input type="text" id="desig-title" name="title" value="" required="required">
    </span>
</div>

<div class="row">
    <label for="desig-desc"><?php esc_html_e( 'Description', 'erp' ); ?></label>
    <span class="field">
        <textarea name="desig-desc" id="desig-desc" rows="6" cols="25" placeholder="<?php esc_html_e( 'Optional', 'erp' ); ?>"></textarea>
    </span>
</div>

<?php wp_nonce_field( 'erp-new-desig' ); ?>
<input type="hidden" name="action" id="desig-action" value="erp-hr-new-desig">
<input type="hidden" name="desig_id" id="desig-id" value="0">
