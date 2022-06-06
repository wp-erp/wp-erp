<?php
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
?>
<div id="log-activity">
    <p>
        <select name="log_type" v-model="feedData.log_type" id="log-type" class="erp-left">
            <option value=""><?php esc_html_e( '-- Select type --', 'erp' ); ?></option>
            <option value="call"><?php esc_html_e( 'Log a Call', 'erp' ); ?></option>
            <option value="meeting"><?php esc_html_e( 'Log a Meeting', 'erp' ); ?></option>
            <option value="email"><?php esc_html_e( 'Log an Email', 'erp' ); ?></option>
            <option value="sms"><?php esc_html_e( 'Log an SMS', 'erp' ); ?></option>
        </select>

        <input class="erp-right" v-model="feedData.tp" type="text" v-timepicker="feedData.tp" placeholder="<?php esc_html_e( '12.00pm', 'erp' ); ?>" size="10">
        <input class="erp-right" v-model="feedData.dt" type="text" v-datepicker="feedData.dt" datedisable="upcomming" placeholder="yy-mm-dd">
        <span class="clearfix"></span>
    </p>

    <p v-if="feedData.log_type == 'email'">
        <label><?php esc_html_e( 'Subject', 'erp' ); ?></label>
        <span class="sep">:</span>
        <span class="value">
            <input type="text" class="email_subject" name="email_subject" v-model="feedData.email_subject" placeholder="<?php esc_attr_e( 'Subject log...', 'erp' ); ?>">
        </span>
    </p>

    <p v-if="feedData.log_type == 'meeting'">
        <select name="selected_contact" id="erp-crm-activity-invite-contact" v-model="feedData.inviteContact" v-selecttwo="feedData.inviteContact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="<?php esc_attr_e( 'Agents or managers..', 'erp' ); ?>">
            <?php echo wp_kses( erp_crm_get_crm_user_html_dropdown(), [
                'option' => [
                    'value'    => [],
                    'selected' => [],
                ],
            ] ); ?>
        </select>
    </p>

    <trix-editor v-if="!feed" id="log-text-editor" input="log_activity_message" placeholder="<?php esc_attr_e( 'Type your description .....', 'erp' ); ?>"></trix-editor>
    <input v-if="!feed" id="log_activity_message" v-model="feedData.message" type="hidden" name="log_activity_message" value="">

    <trix-editor v-if="feed" id="log-text-editor" input="log_activity_message_{{ feed.id }}" placeholder="<?php esc_attr_e( 'Type your description .....', 'erp' ); ?>"></trix-editor>
    <input v-if="feed" id="log_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="log_activity_message_{{ feed.id }}" value="{{ feed.message }}">

    <div class="crm-attachments" id="{{feed ? 'crm-attachments-' + feed.id : 'crm-attachments'}}">
       <div id="progress-wrp"><div class="progress-bar"></div ><div class="status"><?php esc_html_e( '0', 'erp' ); ?>%</div></div>
       <div id="crm-atch-output"></div>
    </div>

    <div class="timeline-body" v-if="feed">
        <div class="timeline-activity-attachments">
            <?php esc_html_e( 'Attachments : ', 'erp' ); ?>
            <ul v-if="(feed.extra.attachments && feed.extra.attachments.length > 0)">
                <li v-for="file in feed.extra.attachments">
                    <a id="activity-atch-name-{{{feed.id}}}-{{{$index}}}" target="_blank" href="{{{file.url}}}">{{{file.name}}}{{{updateAttachments(feed.id, file, $index, false)}}}</a>
                    <span id="btn-activity-atch-{{{feed.id}}}-{{{$index}}}" v-on:click.prevent="updateAttachments(feed.id, file, $index, removeAttch($index))" class="btn-activity-atch remove-atch dashicons dashicons-dismiss">
                        <span class="crm-tooltips">{{tooltip}}</span>
                    </span>
                </li>
            </ul>
            <input type="file" name="attachments[]" class="crm-activity-attachment" id="activity-attachment-{{feed.id}}" v-on:change="addAttachments(feed)" multiple>
            <label for="activity-attachment-{{feed.id}}" class="attachments-label" title="<?php esc_attr_e( 'Add File', 'erp' ); ?>"><?php esc_html_e( '+ Add File', 'erp' ); ?></label>
        </div>
    </div>

    <div class="submit-action">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo esc_attr( $customer_id ); ?>" >
        <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo esc_attr( get_current_user_id() ); ?>">
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="log_activity">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="add_log_activity" value="<?php esc_attr_e( 'Add Log', 'erp' ); ?>">
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_log_activity" value="<?php esc_attr_e( 'Update Log', 'erp' ); ?>">
        <input v-if="!feed" type="file" name="attachments[]" class="crm-activity-attachment" id="activity-attachment" v-on:change="addAttachments()" multiple>
        <label v-if="!feed" for="activity-attachment" class="attachments-label" title="Attach File">
            <span class="btn-activity-atch dashicons dashicons-paperclip"></span><?php esc_html_e( 'Attach File', 'erp' ); ?>
        </label>
        <input type="reset" v-if="!feed" class="button button-default" value="<?php esc_attr_e( 'Discard', 'erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php esc_html_e( 'Cancel', 'erp' ); ?></button>
    </div>
</div>
