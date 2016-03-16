<?php
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
?>
<div id="tasks">

    <div class="assing-task-wrap">
        <p class="assign-taskes-users">
            <select name="selected_contact" id="erp-crm-activity-invite-contact" v-model="feedData.invite_contact" v-selecttwo="feedData.inviteContact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="Select Users...">
                <?php echo erp_crm_get_employess_with_own( get_current_user_id() ); ?>
            </select>
        </p>
        <div class="assing-task-datetime">
            <p class="erp-left assing-task-date">
                <input v-model="feedData.dt" type="text" v-datepicker="feedData.dt" datedisable="previous" placeholder="yy-mm-dd">
            </p>
            <p class="erp-left assing-task-time">
                <input v-model="feedData.tp"  type="text" v-timepicker="feedData.tp" placeholder="12.00pm" size="10">
            </p>
            <div class="clearfix"></div>
        </div>
    </div>

    <trix-editor v-if="!feed" id="tasks-text-editor" input="tasks_activity_message" placeholder="<?php _e( 'Type your description .....', 'wp-erp' ); ?>"></trix-editor>
    <input v-if="!feed" id="tasks_activity_message" v-model="feedData.message" type="hidden" name="tasks_activity_message" value="">

    <trix-editor v-if="feed" id="tasks-text-editor" input="tasks_activity_message_{{ feed.id }}" placeholder="<?php _e( 'Type your description .....', 'wp-erp' ); ?>"></trix-editor>
    <input v-if="feed" id="tasks_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="tasks_activity_message_{{ feed.id }}" value="{{ feed.message }}">

    <div class="submit-action">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer_id; ?>" >
        <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo get_current_user_id(); ?>">
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="tasks">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="add_tasks_activity" value="<?php _e( 'Create Task', 'wp-erp' ); ?>">
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_tasks_activity" value="<?php _e( 'Update Task', 'wp-erp' ); ?>">
        <input type="reset" v-if="!feed" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php _e( 'Cancel', 'wp-erp' ); ?></button>
    </div>
</div>
