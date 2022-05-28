<?php
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
?>
<div id="new_note">
    <trix-editor v-if="!feed" id="note-text-editor" input="note_activity_message" placeholder="<?php esc_attr_e( 'Type your note .....', 'erp' ); ?>"></trix-editor>
    <input v-if="!feed" id="note_activity_message" v-model="feedData.message" type="hidden" name="note_activity_message" value="">

    <trix-editor v-if="feed" id="note-text-editor" input="note_activity_message_{{ feed.id }}" placeholder="<?php esc_attr_e( 'Type your note .....', 'erp' ); ?>"></trix-editor>
    <input v-if="feed" id="note_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="note_activity_message_{{ feed.id }}" value="{{ feed.message }}">

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
        <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo esc_attr( get_current_user_id() ); ?>" >
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="new_note">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="save_notes" value="<?php esc_attr_e( 'Save Note', 'erp' ); ?>">
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_notes" value="<?php esc_attr_e( 'Update Note', 'erp' ); ?>">
        <input v-if="!feed" type="file" name="attachments[]" class="crm-activity-attachment" id="activity-attachment" v-on:change="addAttachments()" multiple>
        <label v-if="!feed" for="activity-attachment" class="attachments-label" title="Attach File"><span class="btn-activity-atch dashicons dashicons-paperclip"></span><?php esc_html_e( 'Attach File', 'erp' ); ?></label>
        <input type="reset" v-if="!feed" class="button button-default" @click="resetData()" value="<?php esc_attr_e( 'Discard', 'erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php esc_attr_e( 'Cancel', 'erp' ); ?></button>
    </div>
</div>
