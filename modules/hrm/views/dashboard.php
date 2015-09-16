<div class="wrap erp hrm-dashboard">
    <h2><?php _e( 'HR Management', '$domain' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">

            <?php
            $employees    = erp_hr_get_employees();
            $departments  = erp_hr_get_departments();
            $designations = erp_hr_get_designations();
            ?>

            <div class="badge-container">
                <div class="badge-wrap badge-green">
                    <div class="badge-inner">
                        <h3><?php echo number_format_i18n( count( $employees ), 0 ); ?></h3>
                        <p><?php _e( 'Employees', 'wp-erp' ); ?></p>
                    </div>

                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo admin_url( 'admin.php?page=erp-hr-employee' ); ?>"><?php _e( 'View Employees', 'wp-erp' ); ?></a>
                    </div>
                </div><!-- .badge-wrap -->

                <div class="badge-wrap badge-red">
                    <div class="badge-inner">
                        <h3><?php echo number_format_i18n( count( $departments ), 0 ); ?></h3>
                        <p><?php _e( 'Departments', 'wp-erp' ); ?></p>
                    </div>

                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo admin_url( 'admin.php?page=erp-hr-depts' ); ?>"><?php _e( 'View Departments', 'wp-erp' ); ?></a>
                    </div>
                </div><!-- .badge-wrap -->

                <div class="badge-wrap badge-aqua">
                    <div class="badge-inner">
                        <h3><?php echo number_format_i18n( count( $designations ), 0 ); ?></h3>
                        <p><?php _e( 'Designation', 'wp-erp' ); ?></p>
                    </div>

                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo admin_url( 'admin.php?page=erp-hr-designation' ); ?>"><?php _e( 'View Designation', 'wp-erp' ); ?></a>
                    </div>
                </div><!-- .badge-wrap -->
            </div><!-- .badge-container -->

            <?php do_action( 'erp_hr_dashboard_widgets_left' ); ?>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">

            <div class="postbox leads-actions">
                <h3 class="hndle"><span><?php _e( 'Birthday Buddies', 'wp-erp' ); ?></span></h3>
                <div class="inside">
                    <h4><?php _e( 'Today\'s Birthday', 'wp-erp' ); ?></h4>

                    <?php $todays_birthday = erp_hr_get_todays_birthday(); ?>

                    <?php if ( $todays_birthday ): ?>

                        <ul class="erp-list list-inline">

                            <?php foreach ( $todays_birthday as $key => $user ) : ?>
                                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) ); ?>
                                <li><a href="<?php echo $employee->get_details_url(); ?>" class="erp-tips" title="<?php echo $employee->get_full_name(); ?>"><?php echo $employee->get_avatar( 32 ); ?></a></li>
                            <?php  endforeach; ?>

                        </ul>

                    <?php else: ?>

                        <p><?php _e( 'No birthday Buddies found', 'wp-erp' ); ?></p>

                    <?php endif ?>


                    <h4><?php _e( 'Upcoming Birthday', 'wp-erp' ); ?></h4>

                    <?php $upcoming_birtday = erp_hr_get_next_seven_days_birthday(); ?>

                    <?php if ( $upcoming_birtday ): ?>

                        <ul class="erp-list list-two-side list-sep">

                            <?php foreach ( $upcoming_birtday as $key => $user ): ?>

                                <?php $employee = new \WeDevs\ERP\HRM\Employee( intval( $user->user_id ) ); ?>

                                <li>
                                    <a href="<?php echo $employee->get_details_url(); ?>"><?php echo $employee->get_full_name(); ?></a>
                                    <span><?php echo erp_format_date( $user->date_of_birth, 'M, d' ); ?></span>
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    <?php else: ?>

                        <p><?php _e( 'No birthday Buddies found', 'wp-erp' ); ?></p>

                    <?php endif ?>

                </div>
            </div><!-- .postbox -->

            <?php do_action( 'erp_hr_dashboard_widgets_right' ); ?>

        </div>

    </div>
</div>