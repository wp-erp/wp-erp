<div class="erp-crm-customer-feed-edit-wrapper">
    <# if ( data.type == 'new_note' ) { #>

        <input id="activity_message_edit" value="{{ data.message }}" type="hidden" name="message">
        <trix-editor id="text-editor-eidt" input="activity_message_edit" placeholder="<?php _e( 'Type your note .....', 'wp-erp' ); ?>"></trix-editor>

    <# } else if ( data.type == 'log_activity' ) { #>

        <p>
            <select name="log_type" id="log-type" class="erp-left" data-selected="{{ data.log_type }}">
                <option value=""><?php _e( '-- Select type --', 'wp-erp' ) ?></option>
                <option value="call"><?php _e( 'Log a Call', 'wp-erp' ) ?></option>
                <option value="metting"><?php _e( 'Log a Meeting', 'wp-erp' ) ?></option>
                <option value="email"><?php _e( 'Log an Email', 'wp-erp' ) ?></option>
                <option value="sms"><?php _e( 'Log an SMS', 'wp-erp' ) ?></option>
            </select>

            <input class="erp-right" type="time" name="log_time" value="{{ data.log_time }}">
            <input class="erp-right erp-date-field" name="log_date" type="text" value="{{ data.log_date }}" placeholder="yy-mm-dd">
            <span class="clearfix"></span>
        </p>

        <input id="activity_message_edit" type="hidden" name="message" value="{{ data.message }}">
        <trix-editor input="activity_message_edit" placeholder="<?php _e( 'Add your log .....', 'wp-erp' ); ?>"></trix-editor>

    <# } #>

    <div class="submit-action">
        <input type="hidden" name="id" value="{{ data.id }}" >
        <input type="hidden" name="user_id" value="{{ data.user_id }}" >
        <input type="hidden" name="created_by" value="{{ data.created_by.ID }}" >
        <input type="hidden" name="action" value="erp_customer_feeds_edit_notes">
        <input type="hidden" name="type" value="{{ data.type }}">
        <?php wp_nonce_field( 'wp-erp-crm-edit-customer-feed-nonce' ); ?>
    </div>
</div>