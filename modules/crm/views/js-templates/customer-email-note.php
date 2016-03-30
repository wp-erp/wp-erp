<?php
global $current_user;
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$customer = new \WeDevs\ERP\CRM\Contact( $customer_id );
?>
<div id="email">
    <p class="email-from">
        <input type="hidden"  v-model="feedData.created_by" name="created_by" value="<?php echo $current_user->ID; ?>">
        <label>Form</label>
        <span class="sep">:</span>
        <span class="value"><?php echo $current_user->display_name; ?></span>
    </p>

    <p class="email-to">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer->id; ?>">
        <label>To</label>
        <span class="sep">:</span>
        <span class="value"><?php echo $customer->get_full_name(); ?></span>
    </p>

    <p class="email-subject">
        <label>Subject</label>
        <span class="sep">:</span>
        <span class="value">
            <input type="text" name="email_subject" v-model="feedData.email_subject" placeholder="<?php _e( 'Subject...', 'erp' ); ?>">
        </span>
    </p>

    <input id="email_activity_message" type="hidden" v-model="feedData.message" name="email_activity_message">
    <trix-editor input="email_activity_message" placeholder="<?php _e( 'Type your email body .....', 'erp' ); ?>"></trix-editor>

    <div class="submit-action">
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="email">
        <input type="submit" :disabled = "!isValid" class="button button-primary" name="send_email" value="<?php _e( 'Send Email', 'erp' ); ?>">
        <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'erp' ); ?>">
    </div>
</div>