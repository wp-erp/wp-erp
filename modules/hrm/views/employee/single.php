<div class="wrap erp erp-hr-employees erp-employee-single">

    <h2><?php _e( 'Employee', 'wp-erp' ); ?> <a href="#" id="erp-employee-new" class="add-new-h2"><?php _e( 'Add New', 'wp-erp' ); ?></a></h2>

    <div class="erp-single-container">
        <div class="erp-area-left full-width">
            <div id="erp-area-left-inner">

                <script type="text/javascript">
                    window.wpErpCurrentEmployee = <?php echo json_encode( $employee->to_array() ); ?>
                </script>

                <div class="erp-profile-top">
                    <div class="erp-avatar">
                        <?php echo $employee->get_avatar( 150 ); ?>
                    </div>

                    <div class="erp-user-info">
                        <h3><span class="title"><?php echo $employee->get_full_name(); ?></span></h3>

                        <ul class="lead-info">
                            <li>
                                <?php echo $employee->get_job_title(); ?> - <?php echo $employee->get_department_title(); ?>
                            </li>

                            <li>
                                <a href="mailto:<?php echo $employee->user_email; ?>"><?php echo $employee->user_email; ?></a>
                            </li>

                            <?php
                            $phones = array();
                            if ( $work_phone = $employee->get_phone( 'work' ) ) {
                                $phones[] = $work_phone;
                            }
                            if ( $mobile_phone = $employee->get_phone( 'mobile' ) ) {
                                $phones[] = $mobile_phone;
                            }

                            if ( $phones ) { ?>
                                <li>
                                    <ul class="erp-list list-inline">
                                        <?php foreach( $phones as $phone ) { ?>
                                            <li><a href="tel:<?php echo $phone; ?>"><span class="dashicons dashicons-smartphone"></span></a><?php echo $phone; ?></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>
                    </div><!-- .erp-user-info -->

                    <div class="erp-area-right">
                        <div class="postbox leads-actions">
                            <h3 class="hndle"><span><?php _e( 'Actions', 'wp-erp' ); ?></span></h3>
                            <div class="inside">
                                <span class="edit"><a class="button button-primary" data-id="<?php echo $employee->id; ?>" data-single="true" href="#"><?php _e( 'Edit', 'wp-erp' ); ?></a></span>
                                <a class="button" href="#"><?php _e( 'Terminate', 'wp-erp' ); ?></a>
                                <a class="button" href="#"><?php _e( 'Print', 'wp-erp' ); ?></a>
                            </div>
                        </div><!-- .postbox -->
                    </div><!-- .leads-right -->

                    <?php do_action( 'erp_hr_employee_single_after_info', $employee ); ?>

                </div><!-- .erp-profile-top -->

                <?php
                $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
                $tabs = apply_filters( 'erp_hr_employee_single_tabs', array(
                    'general' => array(
                        'title'    => __( 'General Info', 'wp-erp' ),
                        'callback' => 'erp_hr_employee_single_tab_general'
                    ),
                    'job' => array(
                        'title' => __( 'Job', 'wp-erp' ),
                        'callback' => 'erp_hr_employee_single_tab_job'
                    ),
                    'leave' => array(
                        'title' => __( 'Leave', 'wp-erp' ),
                        'callback' => 'erp_hr_employee_single_tab_leave'
                    ),
                    'notes' => array(
                        'title' => __( 'Notes', 'wp-erp' ),
                        'callback' => 'erp_hr_employee_single_tab_notes'
                    ),
                    'benefits' => array(
                        'title' => __( 'Benefits', 'wp-erp' ),
                        'callback' => ''
                    ),
                    'performance' => array(
                        'title' => __( 'Performance', 'wp-erp' ),
                        'callback' => ''
                    ),
                ) );
                ?>

                <h2 class="nav-tab-wrapper" style="margin-bottom: 15px;">
                    <?php foreach ($tabs as $key => $tab) {
                        $active_class = ( $key == $active_tab ) ? ' nav-tab-active' : '';
                        ?>
                        <a href="<?php echo add_query_arg( array( 'tab' => $key ), erp_hr_url_single_employee( $employee->id ) ); ?>" class="nav-tab<?php echo $active_class; ?>"><?php echo $tab['title'] ?></a>
                    <?php } ?>
                </h2>

                <?php
                // call the tab callback function
                if ( array_key_exists( $active_tab, $tabs ) && is_callable( $tabs[$active_tab]['callback'] ) ) {
                    call_user_func_array( $tabs[$active_tab]['callback'], array( $employee ) );
                }
                ?>

                <?php do_action( 'erp_hr_employee_single_bottom', $employee ); ?>

            </div><!-- #erp-area-left-inner -->
        </div><!-- .leads-left -->

    </div><!-- .erp-leads-wrap -->
</div>