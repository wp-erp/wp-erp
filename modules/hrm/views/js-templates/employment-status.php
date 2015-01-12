<div class="status-form-wrap">
    <div class="row">
        <label for="status-date" class="block"><?php _e( 'Date', 'wp-erp' ); ?></label>
        <span class="field">
            <input type="text" id="status-date" name="date" value="<?php echo date( 'Y-m-d', current_time( 'timestamp' ) ); ?>" class="erp-date-field">
        </span>
    </div>

    <div class="row">
        <label for="status" class="block"><?php _e( 'Employment Status', 'wp-erp' ); ?></label>
        <span class="field">
            <select name="status" id="status">
                <?php
                $types = erp_hr_get_employee_types();

                foreach ($types as $key => $value) {
                    printf( "<option value='%s'>%s</option>\n", $key, $value );
                }
                ?>
            </select>
        </span>
    </div>

    <div class="row">
        <label for="comment" class="block"><?php _e( 'Comment', 'wp-erp' ); ?></label>
        <span class="field">
            <textarea name="comment" id="comment" rows="8" cols="30" placeholder="<?php _e( 'Optional comment', 'wp-erp' ); ?>"></textarea>
        </span>
    </div>

    <?php wp_nonce_field( 'employee_update_employment' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp-hr-emp-update-status">
    <input type="hidden" name="employee_id" id="emp-id" value="{{ data.id }}">
</div>