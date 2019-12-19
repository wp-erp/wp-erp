<?php
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
?>
<div id="new_note">
    <trix-editor v-if="!feed" id="note-text-editor" input="note_activity_message" placeholder="<?php esc_attr_e( 'Type your note .....', 'erp' ); ?>"></trix-editor>
    <input v-if="!feed" id="note_activity_message" v-model="feedData.message" type="hidden" name="note_activity_message" value="">

    <trix-editor v-if="feed" id="note-text-editor" input="note_activity_message_{{ feed.id }}" placeholder="<?php esc_attr_e( 'Type your note .....', 'erp' ); ?>"></trix-editor>
    <input v-if="feed" id="note_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="note_activity_message_{{ feed.id }}" value="{{ feed.message }}">

    <div class="submit-action">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo esc_attr( $customer_id ); ?>" >
        <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo esc_attr( get_current_user_id() ); ?>" >
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="new_note">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="save_notes" value="<?php esc_attr_e( 'Save Note', 'erp' ); ?>">
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_notes" value="<?php esc_attr_e( 'Update Note', 'erp' ); ?>">
        <input type="reset" v-if="!feed" class="button button-default" value="<?php esc_attr_e( 'Discard', 'erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php esc_attr_e( 'Cancel', 'erp' ); ?></button>
    </div>
</div>
