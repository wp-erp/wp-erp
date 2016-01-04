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

                <div id="new_note" v-if="tabShow == 'new_note'">
                    <trix-editor id="text-editor" input="activity_message" placeholder="<?php _e( 'Type your note .....', 'wp-erp' ); ?>"></trix-editor>
                    <input id="activity_message" v-model="feedData.message" type="hidden" name="activity_message">

                    <!-- <textarea name="activity_message"  id="message" cols="90" rows="4"></textarea> -->

                    <div class="submit-action">
                        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer->id; ?>" >
                        <input type="hidden" name="created_by" v-model="feedData.created_by" value="<?php echo get_current_user_id(); ?>" >
                        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
                        <input type="hidden" name="type" v-model="feedData.type" value="new_note">
                        <input type="submit" :disabled = "!isValid" class="button button-primary" name="save_notes" value="<?php _e( 'Save Note', 'wp-erp' ); ?>">
                        <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
                    </div>
                </div>

                <div id="email" v-if="tabShow == 'email'">
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

                    <input id="activity_message" type="hidden" v-model="feedData.message" name="email_body">
                    <trix-editor input="activity_message" placeholder="<?php _e( 'Type your email body .....', 'wp-erp' ); ?>"></trix-editor>

                    <div class="submit-action">
                        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
                        <input type="hidden" name="type" v-model="feedData.type" value="email">
                        <input type="submit" :disabled = "!isValid" class="button button-primary" name="send_email" value="<?php _e( 'Send Email', 'wp-erp' ); ?>">
                        <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
                    </div>
                </div>

                <div id="log-activity" v-if="tabShow == 'log_activity'">
                    <p>
                        <select name="log_type" v-model="feedData.log_type" id="log-type" class="erp-left">
                            <option value=""><?php _e( '-- Select type --', 'wp-erp' ) ?></option>
                            <option value="log_call"><?php _e( 'Log a Call', 'wp-erp' ) ?></option>
                            <option value="log_metting"><?php _e( 'Log a Meeting', 'wp-erp' ) ?></option>
                            <option value="log_email"><?php _e( 'Log an Email', 'wp-erp' ) ?></option>
                            <option value="log_sms"><?php _e( 'Log an SMS', 'wp-erp' ) ?></option>
                        </select>

                        <input class="erp-right" v-model="feedData.log_time" type="time" placeholder="yy-mm-dd">
                        <input class="erp-right" v-model="dt" type="text" v-datepicker="dt" placeholder="yy-mm-dd">
                        <span class="clearfix"></span>
                    </p>


                    <input id="activity_message" v-model="feedData.message" type="hidden" name="log_activity">
                    <trix-editor input="activity_message" placeholder="<?php _e( 'Add your log .....', 'wp-erp' ); ?>"></trix-editor>

                    <div class="submit-action">
                        <input type="hidden" name="user_id" v-model="feedData.user_id" value="<?php echo $customer->id; ?>" >
                        <input type="hidden" name="action" v-model="feedData.action" value="erp_customer_feeds_save_notes">
                        <input type="hidden" name="type" v-model="feedData.type" value="log_activity">
                        <input type="submit" :disabled = "!isValid" class="button button-primary" name="add_log_activity" value="<?php _e( 'Add Log', 'wp-erp' ); ?>">
                        <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
                    </div>

                </div>

                <div id="call" v-if="tabShow == 'call'">
                    <div class="text-center">
                        <input type="submit" class="button button-primary" value="<?php _e( 'Connect to Call Service', 'wp-erp' ); ?>">
                    </div>
                </div>

                <div id="schedule" v-if="tabShow == 'schedule'">
                    Schedule service....
                </div>
            </form>
        </div>
    </div>


    <!-- <pre>@{{ $data.feeds }}</pre> -->
    <div class="activity-content">

        <ul class="timeline" v-if = "feeds.length">
            <template v-for="( month, feed_obj ) in feeds | formatFeeds">

                <li class="time-label">
                    <span class="bg-red">{{ month | formatDate 'F, Y' }}</span>
                </li>

                <li v-for="feed in feed_obj">

                    <i v-if="feed.type == 'email'" class="fa fa-envelope-o"></i>
                    <i v-if="feed.type == 'new_note'" class="fa fa-file-text-o"></i>
                    <i v-if="feed.type == 'log_activity'" class="fa fa-list"></i>

                    <div class="timeline-item">
                        <span class="time"><i class="fa fa-calendar"></i> {{ feed.created_date | formatDate 'F,j' }} @ {{ feed.created_at | formatAMPM }}</span>

                        <h3 class="timeline-header" @click.prevent="showFooter = !showFooter">
                            {{{ feed | formatFeedHeader }}}
                        </h3>

                        <div class="timeline-body">
                            {{{ feed.message | formatFeedContent feed }}}
                        </div>
                        <div class="timeline-footer" v-show="showFooter">
                            <a href="#" v-if="feed.type != 'email'"><?php _e( 'Edit', 'wp-erp' ); ?> |</a>
                            <a href="#" @click.prevent="deleteFeed( feed )"><?php _e( 'Delete', 'wp-erp' ); ?></a>
                        </div>
                    </div>
                </li>

            </template>
        </ul>

        <div class="no-activity-found" v-else>
            <?php _e( 'No Activity found for this Company', 'wp-erp' ); ?>
        </div>
    </div>

</div>