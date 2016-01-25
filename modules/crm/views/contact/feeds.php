<?php
global $current_user;
$feeds_tab = erp_crm_get_customer_feeds_nav();
?>
<div class="erp-customer-feeds" id="erp-customer-feeds">
    <input type="hidden" v-model="customer_id" value="<?php echo $customer->id; ?>" name="customer_id">
    <div class="activity-form">
        <ul class="erp-list list-inline nav-item">
            <?php foreach ( $feeds_tab as $name => $value ) : ?>
                <li :class="'<?php echo $name; ?>' == tabShow ? 'active': ''">
                    <a href="#<?php echo $name; ?>" @click.prevent="showTab('<?php echo $name; ?>')">
                        <?php echo sprintf('%s %s', $value['icon'], $value['title'] ); ?>
                    </a>
                </li>
            <?php endforeach ?>
        </ul>

        <div class="nav-content" id="erp-crm-feed-nav-content">
            <form action="" method="post" @submit.prevent = "addCustomerFeed()" id="erp-crm-activity-feed-form">

                <new-note v-if="tabShow == 'new_note'" :feed="" keep-alive></new-note>

                <email-note v-if="tabShow == 'email'"></email-note>

                <log-activity v-if="tabShow == 'log_activity'"></log-activity>

                <schedule-note v-if="tabShow == 'schedule'"></schedule-note>

            </form>
        </div>
    </div>

    <div class="activity-content">

        <ul class="timeline" v-if = "feeds.length">

            <template v-for="( month, feed_obj ) in feeds | formatFeeds">

                <li class="time-label">
                    <span class="bg-red">{{ month | formatDate 'F, Y' }}</span>
                </li>

                <li v-for="feed in feed_obj">

                    <i v-if="feed.type == 'email'" class="fa fa-envelope-o" @click.prevent="toggleFooter"></i>
                    <i v-if="feed.type == 'new_note'" class="fa fa-file-text-o" @click.prevent="toggleFooter"></i>
                    <i v-if="feed.type == 'log_activity'" class="fa fa-list" @click.prevent="toggleFooter"></i>
                    <i v-if="( feed.type == 'log_activity' && isSchedule( feed.start_date )  )" class="fa fa-calendar-check-o" @click.prevent="toggleFooter"></i>

                    <timeline-item :feed="feed"></timeline-item>

                </li>

            </template>

        </ul>

        <div class="feed-load-more" v-if="feeds.length">
            <button @click="loadMoreContent( feeds )" class="button">
                <i class="fa fa-cog fa-spin" v-if="loading"></i>
                &nbsp;<span v-if="!loading"><?php _e( 'Load More', 'wp-erp' ); ?></span>
                &nbsp;<span v-else><?php _e( 'Loading..', 'wp-erp' ); ?></span>
            </button>
        </div>

        <div class="no-activity-found" v-if="!feeds.length">
            <?php _e( 'No Activity found for this Company', 'wp-erp' ); ?>
        </div>
    </div>
</div>

<script type="text/x-template" id="new-note-template">
    <div id="new_note">

        <trix-editor v-if="!feed" id="note-text-editor" input="note_activity_message" placeholder="<?php _e( 'Type your note .....', 'wp-erp' ); ?>"></trix-editor>
        <input v-if="!feed" id="note_activity_message" v-model="feedData.message" type="hidden" name="note_activity_message" value="">

        <trix-editor v-if="feed" id="note-text-editor" input="note_activity_message_{{ feed.id }}" placeholder="<?php _e( 'Type your note .....', 'wp-erp' ); ?>"></trix-editor>
        <input v-if="feed" id="note_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="note_activity_message_{{ feed.id }}" value="{{ feed.message }}">

        <div class="submit-action">
            <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer->id; ?>" >
            <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo get_current_user_id(); ?>" >
            <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
            <input type="hidden" name="type" v-model="feedData.type" value="new_note">
            <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="save_notes" value="<?php _e( 'Save Note', 'wp-erp' ); ?>">
            <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_notes" value="<?php _e( 'Update Note', 'wp-erp' ); ?>">
            <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
        </div>
    </div>
</script>
<script type="text/x-template" id="log-activity-template">
    <div id="log-activity">
        <p>
            <select name="log_type" v-model="feedData.log_type" id="log-type" class="erp-left">
                <option value=""><?php _e( '-- Select type --', 'wp-erp' ) ?></option>
                <option value="call"><?php _e( 'Log a Call', 'wp-erp' ) ?></option>
                <option value="meeting"><?php _e( 'Log a Meeting', 'wp-erp' ) ?></option>
                <option value="email"><?php _e( 'Log an Email', 'wp-erp' ) ?></option>
                <option value="sms"><?php _e( 'Log an SMS', 'wp-erp' ) ?></option>
            </select>

            <input class="erp-right" v-model="feedData.tp" value="{{{ feed.start_date | formatDate 'Y-m-d' }}}" type="text" v-timepicker="feedData.tp" placeholder="12.00pm" size="10">
            <input class="erp-right" v-model="feedData.dt" type="text" value="{{ feed.start_date | formatAMPM }}" v-datepicker="feedData.dt" datedisable="upcomming" placeholder="yy-mm-dd">
            <span class="clearfix"></span>
        </p>

        <trix-editor v-if="!feed" id="log-text-editor" input="log_activity_message" placeholder="<?php _e( 'Type your Log .....', 'wp-erp' ); ?>"></trix-editor>
        <input v-if="!feed" id="log_activity_message" v-model="feedData.message" type="hidden" name="log_activity_message" value="">

        <trix-editor v-if="feed" id="log-text-editor" input="log_activity_message_{{ feed.id }}" placeholder="<?php _e( 'Type your Log .....', 'wp-erp' ); ?>"></trix-editor>
        <input v-if="feed" id="log_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="log_activity_message_{{ feed.id }}" value="{{ feed.message }}">

        <div class="submit-action">
            <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer->id; ?>" >
            <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo get_current_user_id(); ?>">
            <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
            <input type="hidden" name="type" v-model="feedData.type" value="log_activity">
            <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="add_log_activity" value="<?php _e( 'Add Log', 'wp-erp' ); ?>">
            <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_log_activity" value="<?php _e( 'Update Log', 'wp-erp' ); ?>">
            <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
        </div>
    </div>
</script>
<script type="text/x-template" id="email-note-template">

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
                <input type="text" name="email_subject" v-model="feedData.email_subject" placeholder="<?php _e( 'Subject...', 'wp-erp' ); ?>">
            </span>
        </p>

        <input id="email_activity_message" type="hidden" v-model="feedData.message" name="email_activity_message">
        <trix-editor input="email_activity_message" placeholder="<?php _e( 'Type your email body .....', 'wp-erp' ); ?>"></trix-editor>

        <div class="submit-action">
            <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
            <input type="hidden" name="type" v-model="feedData.type" value="email">
            <input type="submit" :disabled = "!isValid" class="button button-primary" name="send_email" value="<?php _e( 'Send Email', 'wp-erp' ); ?>">
            <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
        </div>
    </div>
</script>
<script type="text/x-template" id="schedule-note-template">
    <div id="schedule">

        <p class="schedule-title">
            <input type="text" name="schedule-title" v-model="feedData.schedule_title" placeholder="<?php _e( 'Enter Schedule Title', 'wp-erp' ); ?>">
        </p>

        <div class="schedule-datetime">
            <p class="erp-left schedule-start">
                <label><?php _e( 'Start', 'wp-erp' ); ?></label>
                <span class="sep">:</span>
                <span class="value">
                    <input class="start-date" v-model="feedData.dtStart" type="text" v-datepicker="feedData.dtStart" datedisable="previous" placeholder="yy-mm-dd"><span v-show="!feedData.all_day">@</span>
                    <input class="start-time" v-model="feedData.tpStart" type="text" v-timepicker="feedData.tpStart" placeholder="12.00pm" size="10" v-show="!feedData.all_day">
                </span>
            </p>

            <p class="erp-left schedule-end">
                <label><?php _e( 'End', 'wp-erp' ); ?></label>
                <span class="sep">:</span>
                <span class="value">
                    <input class="start-date" v-model="feedData.dtEnd" type="text" v-datepicker="feedData.dtEnd" datedisable="previous" placeholder="yy-mm-dd"><span v-show="!feedData.all_day">@</span>
                    <input class="start-time" v-model="feedData.tpEnd" type="text" v-timepicker="feedData.tpEnd" placeholder="12.00pm" size="10" v-show="!feedData.all_day">
                </span>
            </p>

            <p class="erp-left schedule-all-day">
                <input type="checkbox" name="all_day" v-model="feedData.all_day"> <?php _e( 'All Day', 'wp-erp' ); ?>
            </p>
            <div class="clearfix"></div>
        </div>
        <p v-if="!feed">
            <trix-editor input="schedule_activity_message" placeholder="<?php _e( 'Enter your schedule description .....', 'wp-erp' ); ?>"></trix-editor>
            <input id="schedule_activity_message" v-model="feedData.message" type="hidden" name="schedule_activity_message" value="">
        </p>

        <p v-if="feed">
            <trix-editor input="schedule_activity_message_{{ feed.id }}" placeholder="<?php _e( 'Enter your schedule description .....', 'wp-erp' ); ?>"></trix-editor>
            <input id="schedule_activity_message_{{ feed.id }}" v-model="feedData.message" type="hidden" name="schedule_activity_message_{{ feed.id }}" value="{{{ feed.message }}}">
        </p>

        <div class="clearfix"></div>
        <p>
            <select name="invite_contact" id="erp-crm-activity-invite-contact" v-model="feedData.invite_contact" v-selecttwo="feedData.inviteContact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="Invite a contact">
                <?php echo erp_crm_get_emplyees(); ?>
            </select>
        </p>

        <div class="schedule-notification">

            <p class="erp-left schedule-type">
                <label><?php _e( 'Schedule Type', 'wp-erp' ) ?></label>
                <span class="sep">:</span>
                <span class="value">
                    <select name="schedule_type" id="schedule_type" v-model="feedData.schedule_type">
                        <option value=""><?php _e( '--Select--', 'wp-erp' ) ?></option>
                        <option value="meeting"><?php _e( 'Meeting', 'wp-erp' ); ?></option>
                        <option value="call"><?php _e( 'Call', 'wp-erp' ); ?></option>
                    </select>
                </span>
            </p>

            <p class="erp-left schedule-notification-allow">
                <input type="checkbox" name="allow_notification" v-model="feedData.allow_notification"> <?php _e( 'Allow notification', 'wp-erp' ); ?>
            </p>
            <div class="clearfix"></div>
        </div>

        <div class="schedule-notification" v-show="feedData.allow_notification">
            <p class="erp-left schedule-notification-via">
                <label><?php _e( 'Notify Via', 'wp-erp' ); ?></label>
                <span class="sep">:</span>
                <span class="value">
                    <select name="notification_via" id="notification_via" v-model="feedData.notification_via">
                        <option value=""><?php _e( '--Select--', 'wp-erp' ); ?></option>
                        <option value="email"><?php _e( 'Email', 'wp-erp' ); ?></option>
                        <option value="sms"><?php _e( 'SMS', 'wp-erp' ); ?></option>
                    </select>
                </span>
            </p>

            <p class="erp-left schedule-notification-before">
                <label><?php _e( 'Notify before', 'wp-erp' ); ?></label>
                <span class="sep">:</span>
                <span class="value">
                    <input type="text" name="notification_time_interval" v-model="feedData.notification_time_interval" placeholder="10" style="width:60px;">
                    <select name="notification_time" id="notification_time" v-model="feedData.notification_time">
                        <option value=""><?php _e( '-Select-', 'wp-erp' ); ?></option>
                        <option value="minute"><?php _e( 'minute', 'wp-erp' ); ?></option>
                        <option value="hour"><?php _e( 'hour', 'wp-erp' ); ?></option>
                        <option value="day"><?php _e( 'day', 'wp-erp' ); ?></option>
                    </select>
                </span>
            </p>
            <div class="clearfix"></div>
        </div>

        <div class="submit-action">
            <input type="hidden"  v-model="feedData.created_by" name="created_by" value="<?php echo $current_user->ID; ?>">
            <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer->id; ?>" >
            <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
            <input type="hidden" name="type" v-model="feedData.type" value="schedule">
            <input type="submit" v-if="!feed" :disabled = "!isValid" class="button button-primary" name="create_schedule" value="<?php _e( 'Create Schedule', 'wp-erp' ); ?>">
            <input type="submit" v-if="feed" :disabled = "!isValid" class="button button-primary" name="edit_schedule" value="<?php _e( 'Update Schedule', 'wp-erp' ); ?>">
            <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
        </div>
    </div>
</script>
<script type="text/x-template" id="timeline-item-template">

    <div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="!isEditable">

        <tooltip content="<i class='fa fa-clock-o'></i>" :title="feed.created_at | formatDateTime"></tooltip>

        <h3 class="timeline-header" @click.prevent="toggleFooter">
            <timeline-header :feed="feed"></timeline-header>
        </h3>

        <div class="timeline-body" @click.prevent="toggleFooter">
            {{{ feed.message | formatFeedContent feed }}}
        </div>
        <div class="timeline-footer" v-show="showFooter">
            <a href="#" v-if="feed.type != 'email'" @click.prevent="editFeed( feed )"><?php _e( 'Edit', 'wp-erp' ); ?> |</a>
            <a href="#" @click.prevent="deleteFeed( feed )"><?php _e( 'Delete', 'wp-erp' ); ?></a>
        </div>

    </div>

    <div class="timeline-item" id="timeline-item-{{ feed.id }}" v-if="isEditable">

        <span class="time erp-tips" @click.prevent="cancelUpdate"><i class="fa fa-times"></i></span>

        <h3 class="timeline-header" @click.prevent="toggleFooter">
            <?php _e( 'Edit this feed', 'wp-erp' ); ?>
        </h3>

        <div class="timeline-body">
            <form action="" method="post" @submit.prevent = "updateCustomerFeed( feed.id )" id="erp-crm-activity-edit-feed-form">

                <new-note v-if="feed.type == 'new_note'" :feed="feed"></new-note>

                <log-activity :feed="feed" v-if="( feed.type == 'log_activity' && !isSchedule( feed.start_date ) )"></log-activity>

                <schedule-note :feed="feed" v-if="( feed.type == 'log_activity' && isSchedule( feed.start_date ) )"></schedule-note>

                <!--<input type="hidden" name="feedData.id" value="{{ feed.id }}"> -->
            </form>
        </div>
    </div>
</script>

