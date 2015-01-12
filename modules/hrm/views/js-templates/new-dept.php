<div class="dept-form-wrap">
    <div class="row">
        <label for="dept-title"><?php _e( 'Department Title', 'wp-erp' ); ?> <span class="required">*</span></label>
        <span class="field">
            <input type="text" id="dept-title" name="title" value="" required="required">
        </span>
    </div>

    <div class="row">
        <label for="dept-desc"><?php _e( 'Description', 'wp-erp' ); ?></label>
        <span class="field">
            <textarea name="dept-desc" id="dept-desc" rows="2" cols="20" placeholder="<?php _e( 'Optional', 'wp-erp' ); ?>"></textarea>
        </span>
    </div>

    <div class="row">
        <label for="dept-lead"><?php _e( 'Department Lead', 'wp-erp' ); ?></label>
        <span class="field">
            <select name="lead" id="dept-lead">
                <?php echo erp_hr_get_employees_dropdown( erp_get_current_company_id() ); ?>
            </select>
        </span>
    </div>

    <div class="row">
        <label for="parent-dept"><?php _e( 'Parent Department', 'wp-erp' ); ?></label>
        <span class="field">
            <select name="parent" id="dept-parent">
                <?php echo erp_hr_get_departments_dropdown( erp_get_current_company_id() ); ?>
            </select>
        </span>
    </div>

    <?php wp_nonce_field( 'erp-new-dept' ); ?>
    <input type="hidden" name="action" id="dept-action" value="erp-hr-new-dept">
    <input type="hidden" name="dept_id" id="dept-id" value="0">
</div>