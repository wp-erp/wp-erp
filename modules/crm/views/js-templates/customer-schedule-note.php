<?php
$customer_id        = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$notification_types = erp_crm_activity_schedule_notification_type();
?>

<div id="schedule">

    <p class="schedule-title">
        <input type="text" name="schedule-title" v-model="feedData.schedule_title" placeholder="<?php esc_attr_e( 'Enter Schedule Title', 'erp' ); ?>">
    </p>

    <div class="schedule-datetime">
        <p class="erp-left schedule-start">
            <label><?php esc_html_e( 'Start', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <input class="start-date" v-model="feedData.dtStart" type="text" v-datepicker="feedData.dtStart" datedisable="previous" placeholder="yy-mm-dd"><span v-show="!feedData.all_day">@</span>
                <input class="start-time" v-model="feedData.tpStart" type="text" v-timepicker="feedData.tpStart" placeholder="<?php esc_attr_e( '12.00pm', 'erp' ); ?>" size="10" v-show="!feedData.all_day">
            </span>
        </p>

        <p class="erp-left schedule-end">
            <label><?php esc_html_e( 'End', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <input class="start-date" v-model="feedData.dtEnd" type="text" v-datepicker="feedData.dtEnd" datedisable="previous" placeholder="yy-mm-dd"><span v-show="!feedData.all_day">@</span>
                <input class="start-time" v-model="feedData.tpEnd" type="text" v-timepicker="feedData.tpEnd" placeholder="<?php esc_attr_e( '12.00pm', 'erp' ); ?>" size="10" v-show="!feedData.all_day">
            </span>
        </p>

        <p class="erp-left schedule-all-day">
            <input type="checkbox" name="all_day" v-model="feedData.all_day"> <?php esc_html_e( 'All Day', 'erp' ); ?>
        </p>
        <div class="clearfix"></div>
    </div>
    <p v-if="!feed">
        <trix-editor input="schedule_activity_message" placeholder="<?php esc_attr_e( 'Enter your schedule description .....', 'erp' ); ?>"></trix-editor>
        <input id="schedule_activity_message" v-model="feedData.message" type="hidden" name="schedule_activity_message" value="">
    </p>

    <p v-if="feed">
        <trix-editor input="schedule_activity_message_{{ feed.id }}" placeholder="<?php esc_attr_e( 'Enter your schedule description .....', 'erp' ); ?>"></trix-editor>
        <input id="schedule_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="schedule_activity_message_{{ feed.id }}" value="{{{ feed.message }}}">
    </p>

    <div class="clearfix"></div>
    <p>
        <select name="invite_contact" id="erp-crm-activity-invite-contact" v-model="feedData.inviteContact" v-selecttwo="feedData.inviteContact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="<?php esc_attr_e( 'Agents or managers..', 'erp' ); ?>">
            <?php echo wp_kses( erp_crm_activity_assign_dropdown_html( $customer_id ), [
                'option' => [
                    'value'    => [],
                    'selected' => [],
                ],
            ] ); ?>
        </select>
    </p>

    <div class="schedule-notification">

        <p class="erp-left schedule-type">
            <label><?php esc_html_e( 'Schedule Type', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <select name="schedule_type" id="schedule_type" v-model="feedData.schedule_type">
                    <option value=""><?php esc_html_e( '--Select--', 'erp' ); ?></option>
                    <option value="meeting"><?php esc_html_e( 'Meeting', 'erp' ); ?></option>
                    <option value="call"><?php esc_html_e( 'Call', 'erp' ); ?></option>
                </select>
            </span>
        </p>

        <p class="erp-left schedule-notification-allow">
            <input type="checkbox" name="allow_notification" v-model="feedData.allow_notification"> <?php esc_html_e( 'Allow notification', 'erp' ); ?>
        </p>
        <div class="clearfix"></div>
    </div>

    <div class="schedule-notification" v-show="feedData.allow_notification">
        <p class="erp-left schedule-notification-via">
            <label><?php esc_html_e( 'Notify Via', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <select name="notification_via" id="notification_via" v-model="feedData.notification_via">
                    <option value=""><?php esc_html_e( '--Select--', 'erp' ); ?></option>
                    <?php foreach ( $notification_types as $key => $value ) { ?>
                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                    <?php } ?>
                </select>
            </span>
        </p>

        <p class="erp-left schedule-notification-before">
            <label><?php esc_html_e( 'Notify before', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <input type="text" name="notification_time_interval" v-model="feedData.notification_time_interval" placeholder="10" style="width:60px;">
                <select name="notification_time" id="notification_time" v-model="feedData.notification_time">
                    <option value=""><?php esc_html_e( '-Select-', 'erp' ); ?></option>
                    <option value="minute"><?php esc_html_e( 'minute', 'erp' ); ?></option>
                    <option value="hour"><?php esc_html_e( 'hour', 'erp' ); ?></option>
                    <option value="day"><?php esc_html_e( 'day', 'erp' ); ?></option>
                </select>
            </span>
        </p>
        <div class="clearfix"></div>
    </div>

    <div class="crm-attachments" id="{{feed ? 'crm-attachments-' + feed.id : 'crm-attachments'}}">
       <div id="progress-wrp"><div class="progress-bar"></div ><div class="status"><?php esc_html_e('0', 'erp' ); ?>%</div></div>
       <div id="crm-atch-output"></div>
    </div>

    <div class="timeline-body" v-if="feed">
        <div class="timeline-activity-attachments">
            <?php esc_html_e( 'Attachments : ', 'erp' ); ?>
            <ul v-if="(feed.extra.attachments && feed.extra.attachments.length > 0)">
                <li v-for="file in feed.extra.attachments">
                    <a id="activity-atch-name-{{{feed.id}}}-{{{$index}}}" target="_blank" href="{{{file.url}}}">{{{file.name}}}{{{updateAttachments(feed.id, file, $index, false)}}}</a>
                    <span id="btn-activity-atch-{{{feed.id}}}-{{{$index}}}" v-on:click.prevent="updateAttachments(feed.id, file, $index, removeAttch($index))" class="btn-activity-atch remove-atch dashicons dashicons-dismiss"></span>
                    <span class="crm-tooltips">{{tooltip}}</span>
                </li>
            </ul>
            <input type="file" name="attachments[]" class="crm-activity-attachment" id="activity-attachment-{{feed.id}}" v-on:change="addAttachments(feed)" multiple>
            <label for="activity-attachment-{{feed.id}}" class="attachments-label" title="<?php esc_attr_e( 'Add File', 'erp' ); ?>"><?php esc_html_e( '+ Add File', 'erp' ); ?></label>
        </div>
    </div>

    <div class="submit-action">
        <input type="hidden"  v-model="feedData.created_by" name="created_by" value="<?php echo esc_attr( get_current_user_id() ); ?>">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo esc_attr( $customer_id ); ?>" >
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="client_time_zone" v-model="feedData.client_time_zone">
        <input type="hidden" name="type" v-model="feedData.type" value="schedule">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="create_schedule" value="<?php esc_attr_e( 'Create Schedule', 'erp' ); ?>">
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_schedule" value="<?php esc_attr_e( 'Update Schedule', 'erp' ); ?>">
        <input v-if="!feed" type="file" name="attachments[]" class="crm-activity-attachment" id="activity-attachment" v-on:change="addAttachments()" multiple>
        <label v-if="!feed" for="activity-attachment" class="attachments-label" title="<?php esc_attr_e( 'Attach File', 'erp' ); ?>">
            <span class="btn-activity-atch dashicons dashicons-paperclip"></span><?php esc_html_e( 'Attach File', 'erp' ); ?>
        </label>
        <input type="reset" v-if="!feed" class="button button-default" @click="feedData.allow_notification = false" value="<?php esc_attr_e( 'Discard', 'erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php esc_html_e( 'Cancel', 'erp' ); ?></button>
    </div>
</div>
