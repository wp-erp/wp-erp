<div class="erp-customer-feeds" id="erp-customer-feeds">
    <div class="activity-form">
        <ul class="erp-list list-inline nav-item">
            <li :class="'new_note' == tabShow ? 'active': ''">
                <a href="#new_note" @click.prevent="showTab('new_note')">
                    <i class="fa fa-file-text-o"></i> <?php _e( 'New Note', 'wp-erp' ); ?>
                </a>
            </li>
            <li :class="'email' == tabShow ? 'active': ''">
                <a href="#email" @click.prevent="showTab('email')">
                    <i class="fa fa-envelope-o"></i> <?php _e( 'Email', 'wp-erp' ); ?>
                </a>
            </li>
            <li :class="'log_activity' == tabShow ? 'active': ''">
                <a href="#log_activity" @click.prevent="showTab('log_activity')">
                    <i class="fa fa-list"></i> <?php _e( 'Log Activity', 'wp-erp' ); ?>
                </a>
            </li>
            <li :class="'call' == tabShow ? 'active': ''">
                <a href="#call" @click.prevent="showTab('call')">
                    <i class="fa fa-phone"></i> <?php _e( 'Call', 'wp-erp' ); ?>
                </a>
            </li>
            <li :class="'schedule' == tabShow ? 'active': ''">
                <a href="#schedule" @click.prevent="showTab('schedule')">
                    <i class="fa fa-calendar-check-o"></i>  <?php _e( 'Schedule', 'wp-erp' ); ?>
                </a>
            </li>
        </ul>

        <div class="nav-content">

            <div id="new_note" v-if="tabShow == 'new_note'">
                <form action="" method="post">
                    <input id="x" type="hidden" name="new_note">
                    <trix-editor id="text-editor" input="x" placeholder="<?php _e( 'Type your note .....', 'wp-erp' ); ?>"></trix-editor>

                    <div class="submit-action">
                        <input type="submit" class="button button-primary" name="save_notes" value="<?php _e( 'Save Note', 'wp-erp' ); ?>">
                        <input type="reset" class="button button-default" name="reset" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
                    </div>
                </form>
            </div>

            <div id="email" v-if="tabShow == 'email'">
                <form action="" method="post">
                    <p class="email-from">
                        <input type="hidden" name="email_from" value="1">
                        <label>Form</label>
                        <span class="sep">:</span>
                        <span class="value">Sabbir Ahmed</span>
                    </p>

                    <p class="email-to">
                        <input type="hidden" name="email_to" value="1">
                        <label>To</label>
                        <span class="sep">:</span>
                        <span class="value">Joy Mishu Harami</span>
                    </p>

                    <p class="email-subject">
                        <label>Subject</label>
                        <span class="sep">:</span>
                        <span class="value">
                            <input type="text" name="email_subject" placeholder="<?php _e( 'Subject...', 'wp-erp' ); ?>">
                        </span>
                    </p>

                    <input id="x" type="hidden" name="email_body">
                    <trix-editor input="x" placeholder="<?php _e( 'Type your email body .....', 'wp-erp' ); ?>"></trix-editor>

                    <div class="submit-action">
                        <input type="submit" class="button button-primary" name="send_email" value="<?php _e( 'Send Email', 'wp-erp' ); ?>">
                        <input type="reset" class="button button-default" value="<?php _e( 'Discard', 'wp-erp' ); ?>">
                    </div>
                </form>
            </div>

            <div id="log_activity" v-if="tabShow == 'log_activity'">
                <form action="" method="post">
                    <p>
                        <select name="log_type" id="log-type" class="erp-left">
                            <option value="log_call"><?php _e( 'Log a Call', 'wp-erp' ) ?></option>
                            <option value="log_metting"><?php _e( 'Log a Meeting', 'wp-erp' ) ?></option>
                            <option value="log_email"><?php _e( 'Log an Email', 'wp-erp' ) ?></option>
                            <option value="log_sms"><?php _e( 'Log an SMS', 'wp-erp' ) ?></option>
                        </select>

                        <input class="erp-right" type="text" v-datepicker="dt" placeholder="yy-mm-dd">
                    </p>
                </form>
            </div>

            <div id="call" v-if="tabShow == 'call'">
                Lorem call
            </div>

            <div id="schedule" v-if="tabShow == 'schedule'">
                Lorem schedule
            </div>

        </div>

    </div>

    <div class="activity-content">
        <ul class="timeline">

            <!-- timeline time label -->
            <li class="time-label">
                <span class="bg-red">December, 2015</span>
            </li>
            <!-- /.timeline-label -->

            <!-- timeline item -->
            <li>
                <i class="fa fa-envelope bg-blue"></i>

                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i> 12:05</span>

                    <h3 class="timeline-header">
                        <a href="#">Support Team</a> sent you an email
                    </h3>

                    <div class="timeline-body">
                        Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                        weebly ning heekya handango imeem plugg dopplr jibjab, movity
                        jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                        quora plaxo ideeli hulu weebly balihoo...
                    </div>
                    <div class="timeline-footer">
                        <a class="btn btn-primary btn-xs">Read more</a>
                        <a class="btn btn-danger btn-xs">Delete</a>
                    </div>
                </div>
            </li>
            <!-- END timeline item -->

            <!-- timeline item -->
            <li>
                <i class="fa fa-user bg-aqua"></i>

                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i> 5 mins ago</span>
                    <h3 class="timeline-header no-border"><a href="#">Sarah Young</a> accepted your friend request</h3>
                </div>
            </li>
            <!-- END timeline item -->

            <!-- timeline item -->
            <li>
                <i class="fa fa-comments bg-yellow"></i>
                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i> 27 mins ago</span>
                    <h3 class="timeline-header"><a href="#">Jay White</a> commented on your post</h3>
                    <div class="timeline-body">
                        Take me to your leader!
                        Switzerland is small and neutral!
                        We are more like Germany, ambitious and misunderstood!
                    </div>
                    <div class="timeline-footer">
                        <a class="btn btn-warning btn-flat btn-xs">View comment</a>
                    </div>
                </div>
            </li>
            <!-- END timeline item -->

            <!-- timeline time label -->
            <li class="time-label">
                <span class="bg-green">January, 2016</span>
            </li>
            <!-- /.timeline-label -->

            <!-- timeline item -->
            <li>
                <i class="fa fa-camera bg-purple"></i>

                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i> 2 days ago</span>
                    <h3 class="timeline-header"><a href="#">Mina Lee</a> uploaded new photos</h3>

                    <div class="timeline-body">
                        <img src="http://placehold.it/100x80" alt="..." class="margin">
                        <img src="http://placehold.it/100x80" alt="..." class="margin">
                        <img src="http://placehold.it/100x80" alt="..." class="margin">
                        <img src="http://placehold.it/100x80" alt="..." class="margin">
                    </div>
                </div>
            </li>
            <!-- END timeline item -->

            <!-- timeline item -->
            <li>
                <i class="fa fa-video-camera bg-maroon"></i>

                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i> 5 days ago</span>
                    <h3 class="timeline-header"><a href="#">Mr. Doe</a> shared a video</h3>

                    <div class="timeline-body">
                        <div class="embed-responsive embed-responsive-16by9">
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Officia modi atque magnam eos nesciunt reiciendis culpa, soluta voluptatem fuga debitis commodi consequatur nemo dolore aliquam expedita? Aliquam, adipisci enim recusandae.
                        </div>
                    </div>
                    <div class="timeline-footer">
                        <a href="#" class="btn btn-xs bg-maroon">See comments</a>
                    </div>
                </div>
            </li>
            <!-- END timeline item -->

        </ul>
    </div>
</div>