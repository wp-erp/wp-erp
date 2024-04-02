<div class="erp-crm-new-schedule-wrapper">
<?php
    $tab              = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'own';
    $selected_user_id = ( $tab === 'own' ) ? get_current_user_id() : '';
?>
    <# if( new Date( data.current_date ) >= new Date() ) { #>
        <div class="feed-schedule-wrapper">

            <div class="schedule-title-assign-user">
                <p class="erp-left schedule-title">
                    <input type="text" required name="schedule_title" autocomplete="off" placeholder="<?php esc_attr_e( 'Enter Schedule Title', 'erp' ); ?>">
                </p>

                <p class="erp-left schedule-assign-user">
                    <select name="user_id" required class="erp-crm-contact-list-dropdown" id="assign-contact" style="width: 100%" data-types="contact,company" data-placeholder="<?php esc_attr_e( 'Assign to a contact..', 'erp' ); ?>">
                        <option value=""></option>
                    </select>
                </p>
                <div class="clearfix"></div>
            </div>

            <div class="schedule-datetime">
                <p class="erp-left schedule-start">
                    <label><?php esc_html_e( 'Start', 'erp' ); ?></label>
                    <span class="sep">:</span>
                    <span class="value">
                        <input name="start_date" type="hidden" value="{{ data.current_date }}">
                        <input class="start-date erp-date-field" name="start_date" type="text" value="{{ data.current_date }}" disabled="disabled" placeholder="yy-mm-dd"><span class="datetime-sep">@</span>
                        <input class="start-time erp-time-field" required name="start_time" type="text" placeholder="<?php esc_attr_e( '12.00pm', 'erp' ); ?>" size="10">
                    </span>
                </p>

                <p class="erp-left schedule-end">
                    <label><?php esc_html_e( 'End', 'erp' ); ?></label>
                    <span class="sep">:</span>
                    <span class="value">
                        <input class="start-date erp-date-field" required name="end_date" type="text" value="{{ data.current_date }}"  placeholder="yy-mm-dd"><span class="datetime-sep">@</span>
                        <input class="start-time erp-time-field" required name="end_time" type="text" placeholder="<?php esc_attr_e( '12.00pm', 'erp' ); ?>" size="10">
                    </span>
                </p>

                <p class="erp-left schedule-all-day">
                    <input type="checkbox" name="all_day" value="true"> <?php esc_html_e( 'All Day', 'erp' ); ?>
                </p>
                <div class="clearfix"></div>
            </div>
            <p>
                <input id="activity_messageesc_attr_edit" type="hidden" name="message" required value="">
                <trix-editor class="trix-content" input="activity_messageesc_attr_edit" placeholder="<?php esc_attr_e( 'Enter your schedule description .....', 'erp' ); ?>"></trix-editor>
            </p>
            <div class="clearfix"></div>

            <p>
                <select name="invite_contact[]" id="erp-crm-activity-invite-contact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="<?php esc_attr_e( 'Agents or managers..', 'erp' ); ?>">
                    <?php echo wp_kses( erp_crm_get_crm_user_html_dropdown( $selected_user_id ), [
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
                        <select name="schedule_type" id="schedule_type" required>
                            <option value="" selected><?php esc_html_e( '--Select--', 'erp' ); ?></option>
                            <option value="meeting"><?php esc_html_e( 'Meeting', 'erp' ); ?></option>
                            <option value="call"><?php esc_html_e( 'Call', 'erp' ); ?></option>
                        </select>
                    </span>
                </p>

                <p class="erp-left schedule-notification-allow">
                    <input type="checkbox" name="allow_notification" value="true"> <?php esc_html_e( 'Allow notification', 'erp' ); ?>
                    <?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo erp_help_tip( esc_html__( 'You can send reminder notification through SMS or Email.', 'erp' ) );
                    ?>
                </p>
                <div class="clearfix"></div>
            </div>

            <div class="schedule-notification" id="schedule-notification-wrap">
                <p class="erp-left schedule-notification-via">
                    <label><?php esc_html_e( 'Notify Via', 'erp' ); ?></label>
                    <span class="sep">:</span>
                    <span class="value">
                        <select name="notification_via" id="notification_via">
                            <option value="" selected><?php esc_html_e( '--Select--', 'erp' ); ?></option>
                            <option value="email"><?php esc_html_e( 'Email', 'erp' ); ?></option>
                            <option value="sms" value="disabled"><?php esc_html_e( 'SMS', 'erp' ); ?></option>
                        </select>
                    </span>
                </p>

                <p class="erp-left schedule-notification-before">
                    <label><?php esc_html_e( 'Notify before', 'erp' ); ?></label>
                    <span class="sep">:</span>
                    <span class="value">
                        <input type="text" name="notification_time_interval" placeholder="<?php esc_attr_e( '10', 'erp' ); ?>" style="width:60px;">
                        <select name="notification_time" id="notification_time">
                            <option value="" selected><?php esc_html_e( '-Select-', 'erp' ); ?></option>
                            <option value="minute"><?php esc_html_e( 'minute', 'erp' ); ?></option>
                            <option value="hour"><?php esc_html_e( 'hour', 'erp' ); ?></option>
                            <option value="day"><?php esc_html_e( 'day', 'erp' ); ?></option>
                        </select>
                    </span>
                </p>
                <div class="clearfix"></div>
            </div>
        </div>
        <input type="hidden" name="type" value="schedule">
    <# } else { #>
        <div class="feed-log-activity">
            <p>
                <select required name="user_id" class="erp-crm-contact-list-dropdown" id="assign-contact"  data-types="contact,company" style="width: 100%" data-placeholder="<?php esc_attr_e( 'Assign to a contact..', 'erp' ); ?>">
                    <option value=""></option>
                </select>
            </p>

            <p>
                <select name="log_type" required id="erp-crm-feed-log-type" class="erp-left">
                    <option value=""><?php esc_html_e( '-- Select type --', 'erp' ); ?></option>
                    <option value="call"><?php esc_html_e( 'Log a Call', 'erp' ); ?></option>
                    <option value="meeting"><?php esc_html_e( 'Log a Meeting', 'erp' ); ?></option>
                    <option value="email"><?php esc_html_e( 'Log an Email', 'erp' ); ?></option>
                    <option value="sms"><?php esc_html_e( 'Log an SMS', 'erp' ); ?></option>
                </select>
                <input class="erp-right erp-time-field" type="text" required placeholder="<?php esc_attr_e( '12.00pm', 'erp' ); ?>" size="10" name="log_time">
                <input class="erp-right erp-date-field" disabled="disabled" name="log_date" value="{{ data.current_date }}" type="text" placeholder="yy-mm-dd">
                <input name="log_date" type="hidden" value="{{ data.current_date }}">
                <span class="clearfix"></span>
            </p>

            <p class="log-email-subject erp-hide">
                <label><?php esc_html_e( 'Subject', 'erp' ); ?></label>
                <span class="sep">:</span>
                <span class="value">
                    <input type="text" class="email_subject" name="email_subject" placeholder="<?php esc_attr_e( 'Subject log...', 'erp' ); ?>">
                </span>
            </p>

            <p class="log-selected-contact erp-hide">
                <select name="invite_contact[]" id="erp-crm-activity-invite-contact" class="select2" multiple="multiple" style="width: 100%" data-placeholder="<?php esc_attr_e( 'Agents or managers..', 'erp' ); ?>">
                    <?php echo wp_kses( erp_crm_get_crm_user_html_dropdown( $selected_user_id ), [
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                    ] ); ?>
                </select>
            </p>

            <input id="activity_messageesc_attr_edit" type="hidden" name="message" value="">
            <trix-editor input="activity_messageesc_attr_edit" placeholder="<?php esc_attr_e( 'Add your log .....', 'erp' ); ?>"></trix-editor>
        </div>
        <input type="hidden" name="type" value="log_activity">
    <# } #>

    <div class="submit-action">
        <input type="hidden" name="action" value="erp_crm_add_schedules_action">
        <input type="hidden" name="created_by" value="<?php echo esc_attr( get_current_user_id() ); ?>" >
        <?php wp_nonce_field( 'wp-erp-crm-customer-feed' ); ?>
    </div>
</div>

<style>
    .erp-help-tip {
        font-size: 1.1em;
        top      : 0;
        left     : 0;
    }
    .schedule-datetime .erp-date-field {
        padding: 0px 0px !important;
    }
</style>
