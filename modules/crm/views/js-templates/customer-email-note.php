<?php
global $current_user;
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$customer = new \WeDevs\ERP\CRM\Contact( $customer_id );
$save_replies = erp_crm_get_save_replies();
$block = !erp_crm_sync_is_active() ? 'crm-blocked' : '';
$settings_url = add_query_arg( [ 'page' => 'erp-settings', 'tab' => 'erp-crm', 'section' => 'email_connect' ], admin_url( 'admin.php' ) );
?>
<div id="email" class="<?php echo esc_html( $block ); ?>">

    <?php if( !erp_crm_sync_is_active() ) : ?>
        <a class="button button-primary" style="z-index: 2;position: relative;top: 150px;left: 43%;" href="<?php echo esc_url_raw( $settings_url ); ?>"><?php esc_attr_e( 'Configure Settings', 'erp' ); ?></a>
    <?php endif; ?>

    <p class="email-templates">
        <select name="select_templates" id="erp-crm-activity-insert-templates" v-model="emailTemplates" v-selecttwo="emailTemplates" class="select2" v-on:change="insertSaveReplies()" style="width: 100%" data-placeholder="Select a templates...">
            <option value=""><?php esc_attr_e( 'Select a templates', 'erp' ); ?></option>
            <?php foreach ( $save_replies as $key => $save_reply ) : ?>
                <option value="<?php echo esc_attr( $save_reply->id ) ?>"><?php echo esc_attr( $save_reply->name ) ?></option>
            <?php endforeach ?>
        </select>
    </p>

    <p class="email-from" v-if="!feed">
        <input type="hidden"  v-model="feedData.created_by" name="created_by" value="<?php echo esc_attr( $current_user->ID ); ?>">
        <label><?php esc_attr_e( 'From', 'erp' ); ?></label>
        <span class="sep">:</span>
        <span class="value"><?php echo esc_attr( $current_user->display_name ); ?></span>
    </p>

    <p class="email-to" v-if="!feed">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo esc_attr( $customer->id ); ?>">
        <label><?php esc_attr_e( 'To', 'erp' ) ?></label>
        <span class="sep">:</span>
        <span class="value"><?php echo esc_attr( $customer->get_full_name() ); ?></span>
    </p>

    <p class="email-subject" v-if="!feed">
        <label><?php esc_attr_e( 'Subject', 'erp' ); ?></label>
        <span class="sep">:</span>
        <span class="value">
            <input type="text" name="email_subject" v-model="feedData.email_subject" placeholder="<?php esc_attr_e( 'Subject...', 'erp' ); ?>">
        </span>
    </p>

    <trix-editor input="email_activity_message" placeholder="<?php esc_attr_e( 'Type your email body .....', 'erp' ); ?>"></trix-editor>
    <input id="email_activity_message" type="hidden" v-model="feedData.message" name="email_activity_message">

    <div class="crm-attachments">
       <div id="progress-wrp"><div class="progress-bar"></div ><div class="status">0%</div></div>
        <div id="output">
            <p v-for="file in files ">
                {{ file }}
            </p>
        </div>
    </div>

    <div class="submit-action">
        <div v-if="feed">
            <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo esc_attr( $customer->id ); ?>" >
            <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo esc_attr( $current_user->ID ); ?>" >
        </div>
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="email">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="save_notes" value="<?php esc_attr_e( 'Send Email', 'erp' ); ?>">
        <input type="file" name="attatchments[]" id="email-attachment" v-on:change="fileUpload()" multiple>
        <label for="email-attachment" class="attachments-label" title="Attach File"><?php esc_attr_e( 'Attach File', 'erp' ); ?>
        </label>
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_notes" value="<?php esc_attr_e( 'Reply Email', 'erp' ); ?>">
        <input type="reset" v-if="!feed" class="button button-default" value="<?php esc_attr_e( 'Discard', 'erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php esc_attr_e( 'Cancel', 'erp' ); ?></button>
    </div>
</div>
