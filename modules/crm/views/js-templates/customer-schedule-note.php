<?php
$customer_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$notification_types = erp_crm_activity_schedule_notification_type();
?>

<div id="schedule">

    <p class="schedule-title">
        <input type="text" name="schedule-title" v-model="feedData.schedule_title" placeholder="<?php esc_attr_e( 'Enter Schedule Title', 'erp' ); ?>">
    </p>

    <div class="schedule-datetime">
        <p class="erp-left schedule-start">
            <label><?php esc_attr_e( 'Start', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <input class="start-date" v-model="feedData.dtStart" type="text" v-datepicker="feedData.dtStart" datedisable="previous" placeholder="yy-mm-dd"><span v-show="!feedData.all_day">@</span>
                <input class="start-time" v-model="feedData.tpStart" type="text" v-timepicker="feedData.tpStart" placeholder="12.00pm" size="10" v-show="!feedData.all_day">
            </span>
        </p>

        <p class="erp-left schedule-end">
            <label><?php esc_attr_e( 'End', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <input class="start-date" v-model="feedData.dtEnd" type="text" v-datepicker="feedData.dtEnd" datedisable="previous" placeholder="yy-mm-dd"><span v-show="!feedData.all_day">@</span>
                <input class="start-time" v-model="feedData.tpEnd" type="text" v-timepicker="feedData.tpEnd" placeholder="12.00pm" size="10" v-show="!feedData.all_day">
            </span>
        </p>

        <p class="erp-left schedule-all-day">
            <input type="checkbox" name="all_day" v-model="feedData.all_day"> <?php esc_attr_e( 'All Day', 'erp' ); ?>
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
        <select name="invite_contact" id="erp-crm-activity-invite-contact" v-model="feedData.inviteContact" v-selecttwo="feedData.inviteContact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="<?php esc_attr_e( 'Agents or managers..', 'erp' ) ?>">
            <?php echo wp_kses( erp_crm_get_crm_user_html_dropdown(), array(
                'option' => array(
                    'value' => array(),
                    'selected' => array()
                ),
            ) ); ?>
        </select>
    </p>

    <div class="schedule-notification">

        <p class="erp-left schedule-type">
            <label><?php esc_attr_e( 'Schedule Type', 'erp' ) ?></label>
            <span class="sep">:</span>
            <span class="value">
                <select name="schedule_type" id="schedule_type" v-model="feedData.schedule_type">
                    <option value=""><?php esc_attr_e( '--Select--', 'erp' ) ?></option>
                    <option value="meeting"><?php esc_attr_e( 'Meeting', 'erp' ); ?></option>
                    <option value="call"><?php esc_attr_e( 'Call', 'erp' ); ?></option>
                </select>
            </span>
        </p>

        <p class="erp-left schedule-notification-allow">
            <input type="checkbox" name="allow_notification" v-model="feedData.allow_notification"> <?php esc_attr_e( 'Allow notification', 'erp' ); ?>
        </p>
        <div class="clearfix"></div>
    </div>

    <div class="schedule-notification" v-show="feedData.allow_notification">
        <p class="erp-left schedule-notification-via">
            <label><?php esc_attr_e( 'Notify Via', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <select name="notification_via" id="notification_via" v-model="feedData.notification_via">
                    <option value=""><?php esc_attr_e( '--Select--', 'erp' ); ?></option>
                    <?php foreach ( $notification_types as $key => $value ) : ?>
                        <option value="<?php echo esc_attr( $key ) ?>"><?php echo esc_attr( $value ); ?></option>
                    <?php endforeach; ?>
                </select>
            </span>
        </p>

        <p class="erp-left schedule-notification-before">
            <label><?php esc_attr_e( 'Notify before', 'erp' ); ?></label>
            <span class="sep">:</span>
            <span class="value">
                <input type="text" name="notification_time_interval" v-model="feedData.notification_time_interval" placeholder="10" style="width:60px;">
                <select name="notification_time" id="notification_time" v-model="feedData.notification_time">
                    <option value=""><?php esc_attr_e( '-Select-', 'erp' ); ?></option>
                    <option value="minute"><?php esc_attr_e( 'minute', 'erp' ); ?></option>
                    <option value="hour"><?php esc_attr_e( 'hour', 'erp' ); ?></option>
                    <option value="day"><?php esc_attr_e( 'day', 'erp' ); ?></option>
                </select>
            </span>
        </p>
        <div class="clearfix"></div>
    </div>

    <div class="submit-action">
        <input type="hidden"  v-model="feedData.created_by" name="created_by" value="<?php echo esc_attr( get_current_user_id() ); ?>">
        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo esc_attr( $customer_id ); ?>" >
        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
        <input type="hidden" name="type" v-model="feedData.type" value="schedule">
        <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="create_schedule" value="<?php esc_attr_e( 'Create Schedule', 'erp' ); ?>">
        <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_schedule" value="<?php esc_attr_e( 'Update Schedule', 'erp' ); ?>">
        <input type="reset" v-if="!feed" class="button button-default" @click="feedData.allow_notification = false" value="<?php esc_attr_e( 'Discard', 'erp' ); ?>">
        <button class="button" v-if="feed" @click.prevent="cancelUpdateFeed"><?php esc_attr_e( 'Cancel', 'erp' ); ?></button>
    </div>
</div>
