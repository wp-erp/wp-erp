<div class="compensation-form-wrap">
    <div class="row">
        <label for="comp-date" class=""><?php _e( 'Date', 'wp-erp' ); ?></label>
        <span class="field">
            <input type="text" id="comp-date" name="date" value="<?php echo date( 'Y-m-d', current_time( 'timestamp' ) ); ?>" class="erp-date-field">
        </span>
    </div>

    <div class="row">
        <label for="pay-rate" class=""><?php _e( 'Pay Rate', 'wp-erp' ); ?></label>
        <span class="field">
            <input type="text" id="pay-rate" name="pay_rate" value="{{ data.work.pay_rate }}">
        </span>
    </div>

    <div class="row">
        <label for="pay-type" class=""><?php _e( 'Pay Type', 'wp-erp' ); ?></label>
        <span class="field">
            <select name="pay_type" id="pay-type">
                <?php
                $types = erp_hr_get_pay_type();

                foreach ($types as $key => $value) {
                    printf( "<option value='%s'>%s</option>\n", $key, $value );
                }
                ?>
            </select>
        </span>
    </div>

    <div class="row">
        <label for="change-reason" class=""><?php _e( 'Change Reason', 'wp-erp' ); ?></label>
        <span class="field">
            <select name="change-reason" id="change-reason">
                <?php
                $types = erp_hr_get_pay_change_reasons();

                foreach ($types as $key => $value) {
                    printf( "<option value='%s'>%s</option>\n", $key, $value );
                }
                ?>
            </select>
        </span>
    </div>

    <div class="row">
        <label for="comment" class=""><?php _e( 'Comment', 'wp-erp' ); ?></label>
        <span class="field">
            <textarea name="comment" id="comment" rows="5" cols="25" placeholder="<?php _e( 'Optional comment', 'wp-erp' ); ?>"></textarea>
        </span>
    </div>

    <?php wp_nonce_field( 'employee_update_compensation' ); ?>
    <input type="hidden" name="action" id="status-action" value="erp-hr-emp-update-comp">
    <input type="hidden" name="employee_id" id="emp-id" value="{{ data.id }}">
</div>