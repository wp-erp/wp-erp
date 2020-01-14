<?php
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
?>
<div id="tasks">

    <div class="assign-task-wrap">
        <p class="assign-task-title">
            <input type="text" v-model="feedData.task_title" placeholder="<?php esc_attr_e( 'Enter your task title here..') ?>">
        </p>
        <p class="assign-taskes-users">
            <select name="selected_contact" v-model="feedData.inviteContact" v-selecttwo="feedData.inviteContact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="<?php esc_attr_e( 'Agents or managers..', 'erp' ) ?>">
                <?php echo wp_kses( erp_crm_get_crm_user_html_dropdown(), array(
                    'option' => array(
                        'value' => array(),
                        'selected' => array()
                    ),
                ) ); ?>
            </select>
        </p>
        <div class="assign-task-datetime">
            <p class="erp-left assign-task-date">
                <input v-model="feedData.dt" type="text" v-datepicker="feedData.dt" datedisable="previous" placeholder="yy-mm-dd">
            </p>
            <p class="erp-left assign-task-time">
                <input v-model="feedData.tp"  type="text" v-timepicker="feedData.tp" placeholder="12.00pm" size="10">
            </p>
            <div class="clearfix"></div>
        </div>
    </div>

    <trix-editor v-if="!feed" id="tasks-text-editor" input="tasks_activity_message" placeholder="<?php esc_attr_e( 'Type your description .....', 'erp' ); ?>"></trix-editor>
    <input v-if="!feed" id="tasks_activity_message" v-model="feedData.message" type="hidden" name="tasks_activity_message" value="">

    <trix-editor v-if="feed" id="tasks-text-editor" input="tasks_activity_message_{{ feed.id }}" placeholder="<?php esc_attr_e( 'Type your description .....', 'erp' ); ?>"></trix-editor>
    <input v-if="feed" id="tasks_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="tasks_activity_message_{{ feed.id }}" value="{{ feed.message }}">

    <div class="submit-action">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo esc_attr( $customer_id ); ?>" >
        <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo esc_attr( get_current_user_id() ); ?>">
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="tasks">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="add_tasks_activity" value="<?php esc_attr_e( 'Create Task', 'erp' ); ?>">
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_tasks_activity" value="<?php esc_attr_e( 'Update Task', 'erp' ); ?>">
        <input type="reset" v-if="!feed" class="button button-default" value="<?php esc_attr_e( 'Discard', 'erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php esc_attr_e( 'Cancel', 'erp' ); ?></button>
    </div>
</div>
