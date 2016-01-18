<div class="erp-crm-customer-feed-edit-wrapper ">
    <# if ( data.type == 'new_note' ) { #>

        <input id="activity_message_edit" value="{{ data.message }}" type="hidden" name="message">
        <trix-editor id="text-editor-eidt" input="activity_message_edit" placeholder="<?php _e( 'Type your note .....', 'wp-erp' ); ?>"></trix-editor>
        <input type="hidden" name="action" value="erp_customer_feeds_edit_notes">

    <# } else if ( data.type == 'log_activity' ) { #>

        <# if( new Date( data.start_date ) > new Date() ) { #>
            <div class="feed-schedule-wrapper">
                <p class="schedule-title">
                    <input type="text" name="schedule_title" value="{{ data.extra.schedule_title }}" placeholder="<?php _e( 'Enter Schedule Title', 'wp-erp' ); ?>">
                </p>

                <div class="schedule-datetime">
                    <p class="erp-left schedule-start">
                        <label><?php _e( 'Start', 'wp-erp' ); ?></label>
                        <span class="sep">:</span>
                        <span class="value">
                            <input class="start-date erp-date-field" name="start_date" value="{{ vm.$options.filters.formatDate( data.start_date ) }}" type="text" placeholder="yy-mm-dd"><span class="datetime-sep">@</span>
                            <input class="start-time erp-time-field" name="start_time" value="{{ vm.$options.filters.formatAMPM( data.start_date ) }}" type="text" placeholder="12.00pm" size="10">
                        </span>
                    </p>

                    <p class="erp-left schedule-end">
                        <label><?php _e( 'End', 'wp-erp' ); ?></label>
                        <span class="sep">:</span>
                        <span class="value">
                            <input class="start-date erp-date-field" name="end_date" value="{{ vm.$options.filters.formatDate( data.end_date ) }}" type="text" v-datepicker="dtEnd" datedisable="previous" placeholder="yy-mm-dd"><span class="datetime-sep">@</span>
                            <input class="start-time erp-time-field" name="end_time" value="{{ vm.$options.filters.formatAMPM( data.start_date ) }}" type="text" v-timepicker="tpEnd" placeholder="12.00pm" size="10">
                        </span>
                    </p>

                    <p class="erp-left schedule-all-day">
                        <input type="checkbox" data-checked="{{ data.extra.all_day }}" name="all_day" value="true"> <?php _e( 'All Day', 'wp-erp' ); ?>
                    </p>
                    <div class="clearfix"></div>
                </div>
                <p>
                    <input id="activity_message_edit" type="hidden" name="message" value="{{ data.message }}">
                    <trix-editor input="activity_message_edit" placeholder="<?php _e( 'Enter your schedule description .....', 'wp-erp' ); ?>"></trix-editor>
                </p>
                <div class="clearfix"></div>
                <p>
                    <# var invitedUser = data.extra.invited_user.map( function( elm ) { return elm.id } ).join(','); #>
                    <select name="invite_contact[]" id="erp-crm-activity-invite-contact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="Invite a contact" data-selected="{{ invitedUser }}">
                        <?php echo erp_crm_get_emplyees(); ?>
                    </select>
                </p>

                <div class="schedule-notification">
                    <p class="erp-left schedule-type">
                        <label><?php _e( 'Schedule Type', 'wp-erp' ) ?></label>
                        <span class="sep">:</span>
                        <span class="value">
                            <select name="schedule_type" id="schedule_type" data-selected="{{ data.log_type }}">
                                <option value="" selected><?php _e( '--Select--', 'wp-erp' ) ?></option>
                                <option value="meeting"><?php _e( 'Meeting', 'wp-erp' ); ?></option>
                                <option value="call"><?php _e( 'Call', 'wp-erp' ); ?></option>
                            </select>
                        </span>
                    </p>

                    <p class="erp-left schedule-notification-allow">
                        <input type="checkbox" data-checked="{{ data.extra.allow_notification }}" name="allow_notification" value="true"> <?php _e( 'Allow notification', 'wp-erp' ); ?>
                    </p>
                    <div class="clearfix"></div>
                </div>

                <div class="schedule-notification" id="schedule-notification-wrap" v-show="feedData.allow_notification">
                    <p class="erp-left schedule-notification-via">
                        <label><?php _e( 'Notify Via', 'wp-erp' ); ?></label>
                        <span class="sep">:</span>
                        <span class="value">
                            <select name="notification_via" id="notification_via" data-selected="{{ data.extra.notification_via }}">
                                <option value="" selected><?php _e( '--Select--', 'wp-erp' ); ?></option>
                                <option value="email"><?php _e( 'Email', 'wp-erp' ); ?></option>
                                <option value="sms"><?php _e( 'SMS', 'wp-erp' ); ?></option>
                            </select>
                        </span>
                    </p>

                    <p class="erp-left schedule-notification-before">
                        <label><?php _e( 'Notify before', 'wp-erp' ); ?></label>
                        <span class="sep">:</span>
                        <span class="value">
                            <input type="text" name="notification_time_interval" placeholder="10" style="width:60px;" value="{{ data.extra.notification_time_interval }}">
                            <select name="notification_time" id="notification_time" data-selected="{{ data.extra.notification_time }}">
                                <option value="" selected><?php _e( '-Select-', 'wp-erp' ); ?></option>
                                <option value="minute"><?php _e( 'minute', 'wp-erp' ); ?></option>
                                <option value="hour"><?php _e( 'hour', 'wp-erp' ); ?></option>
                                <option value="day"><?php _e( 'day', 'wp-erp' ); ?></option>
                            </select>
                        </span>
                    </p>
                    <div class="clearfix"></div>
                </div>

                <input type="hidden" name="action" value="erp_customer_feeds_edit_schedules">
            </div>
        <# } else { #>
            <p>
                <select name="log_type" required id="log-type" class="erp-left" data-selected="{{ data.log_type }}">
                    <option value=""><?php _e( '-- Select type --', 'wp-erp' ) ?></option>
                    <option value="call"><?php _e( 'Log a Call', 'wp-erp' ) ?></option>
                    <option value="meeting"><?php _e( 'Log a Meeting', 'wp-erp' ) ?></option>
                    <option value="email"><?php _e( 'Log an Email', 'wp-erp' ) ?></option>
                    <option value="sms"><?php _e( 'Log an SMS', 'wp-erp' ) ?></option>
                </select>
                <!-- <input class="erp-right" type="time" name="log_time" value="{{ data.log_time }}"> -->
                <input class="erp-right erp-time-field" type="text" required placeholder="12.00pm" value="{{ vm.$options.filters.formatAMPM( data.start_date ) }}" size="10" name="log_time">
                <input class="erp-right erp-date-field" name="log_date" required type="text" value="{{ vm.$options.filters.formatDate( data.start_date ) }}" placeholder="yy-mm-dd">
                <span class="clearfix"></span>
            </p>

            <input id="activity_message_edit" type="hidden" name="message" value="{{ data.message }}">
            <trix-editor input="activity_message_edit" placeholder="<?php _e( 'Add your log .....', 'wp-erp' ); ?>"></trix-editor>
            <input type="hidden" name="action" value="erp_customer_feeds_edit_notes">
        <# } #>

    <# } #>

    <div class="submit-action">
        <input type="hidden" name="id" value="{{ data.id }}" >
        <input type="hidden" name="user_id" value="{{ data.user_id }}" >
        <input type="hidden" name="created_by" value="{{ data.created_by.ID }}" >
        <input type="hidden" name="type" value="{{ data.type }}">
        <?php wp_nonce_field( 'wp-erp-crm-edit-customer-feed-nonce' ); ?>
    </div>
</div>