<?php
global $current_user;
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$customer = new \WeDevs\ERP\CRM\Contact( $customer_id );
$save_replies = erp_crm_get_save_replies();
?>
<div id="email">
    <p class="email-templates">
        <select name="select_templates" id="erp-crm-activity-insert-templates" v-model="emailTemplates" v-selecttwo="emailTemplates" class="select2" v-on:change="insertSaveReplies()" style="width: 100%" data-placeholder="Select a templates...">
            <option value=""><?php _e( 'Select a templates', 'erp' ); ?></option>
            <?php foreach ( $save_replies as $key => $save_reply ) : ?>
                <option value="<?php echo $save_reply->id ?>"><?php echo $save_reply->name ?></option>
            <?php endforeach ?>
        </select>
    </p>

    <p class="email-from" v-if="!feed">
        <input type="hidden"  v-model="feedData.created_by" name="created_by" value="<?php echo $current_user->ID; ?>">
        <label><?php _e( 'From', 'erp' ); ?></label>
        <span class="sep">:</span>
        <span class="value"><?php echo $current_user->display_name; ?></span>
    </p>

    <p class="email-to" v-if="!feed">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer->id; ?>">
        <label><?php _e( 'To', 'erp' ) ?></label>
        <span class="sep">:</span>
        <span class="value"><?php echo $customer->get_full_name(); ?></span>
    </p>

    <p class="email-subject" v-if="!feed">
        <label><?php _e( 'Subject', 'erp' ); ?></label>
        <span class="sep">:</span>
        <span class="value">
            <input type="text" name="email_subject" v-model="feedData.email_subject" placeholder="<?php _e( 'Subject...', 'erp' ); ?>">
        </span>
    </p>

    <trix-editor input="email_activity_message" placeholder="<?php _e( 'Type your email body .....', 'erp' ); ?>"></trix-editor>
    <input id="email_activity_message" type="hidden" v-model="feedData.message" name="email_activity_message">

    <div class="submit-action">
        <div v-if="feed">
            <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer->id; ?>" >
            <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo $current_user->ID; ?>" >
        </div>
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="email">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="save_notes" value="<?php _e( 'Send Email', 'erp' ); ?>">
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_notes" value="<?php _e( 'Reply Email', 'erp' ); ?>">
        <input type="reset" v-if="!feed" class="button button-default" value="<?php _e( 'Discard', 'erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php _e( 'Cancel', 'erp' ); ?></button>
    </div>
</div>
