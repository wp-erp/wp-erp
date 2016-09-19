<div class="wrap erp erp-hr-employees erp-employee-single">

    <h2 class="erp-hide-print"><?php _e( 'Employee', 'erp' );

     if ( current_user_can( 'erp_create_employee' ) ) {
        ?>
        <a href="#" id="erp-employee-new" class="add-new-h2 erp-hide-print"><?php _e( 'Add New', 'erp' ); ?></a>
        <?php
    }
    ?>
    </h2>
    <div class="erp-single-container erp-hr-employees-wrap" id="erp-single-container-wrap">
        <div class="erp-area-left full-width erp-hr-employees-wrap-inner">
            <div id="erp-area-left-inner">

                <script type="text/javascript">
                    window.wpErpCurrentEmployee = <?php echo json_encode( $employee->to_array() ); ?>
                </script>

                <div class="erp-profile-top">
                    <div class="erp-avatar">
                        <?php echo $employee->get_avatar( 150 ); ?>

                        <?php if ( $employee->get_status() == 'Terminated' ): ?>
                            <span class="inactive"></span>
                        <?php endif ?>
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

                    <div class="erp-area-right erp-hide-print">
                        <div class="postbox leads-actions">
                            <h3 class="hndle"><span><?php _e( 'Actions', 'erp' ); ?></span></h3>
                            <div class="inside">
                                <?php
                                if ( current_user_can( 'erp_edit_employee', $employee->id ) ) {
                                    ?>
                                    <span class="edit"><a class="button button-primary" data-id="<?php echo $employee->id; ?>" data-single="true" href="#"><?php _e( 'Edit', 'erp' ); ?></a></span>
                                    <?php
                                }
                                ?>

                                <?php if ( $employee->get_status() != 'Terminated' && current_user_can( 'erp_create_employee' ) ): ?>
                                    <a class="button" href="#" id="erp-employee-terminate" data-id="<?php echo $employee->id; ?>" data-template="erp-employment-terminate" data-title="<?php _e( 'Terminate Employee', 'erp' ); ?>"><?php _e( 'Terminate', 'erp' ); ?></a>
                                <?php endif; ?>

                                <?php if ( ( isset( $_GET['tab'] ) && $_GET['tab'] == 'general' ) || !isset( $_GET['tab'] )  ): ?>
                                    <a class="button" id="erp-employee-print" href="#"><?php _e( 'Print', 'erp' ); ?></a>
                                <?php endif ?>
                            </div>
                        </div><!-- .postbox -->
                    </div><!-- .leads-right -->

                    <?php do_action( 'erp_hr_employee_single_after_info', $employee ); ?>

                </div><!-- .erp-profile-top -->

                <?php
                $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
                $tabs       = apply_filters( 'erp_hr_employee_single_tabs', array(
                    'general' => array(
                        'title'    => __( 'General Info', 'erp' ),
                        'callback' => 'erp_hr_employee_single_tab_general'
                    ),
                    'job' => array(
                        'title'    => __( 'Job', 'erp' ),
                        'callback' => 'erp_hr_employee_single_tab_job'
                    ),
                    'leave' => array(
                        'title'    => __( 'Leave', 'erp' ),
                        'callback' => 'erp_hr_employee_single_tab_leave'
                    ),
                    'notes' => array(
                        'title'    => __( 'Notes', 'erp' ),
                        'callback' => 'erp_hr_employee_single_tab_notes'
                    ),
                    'performance' => array(
                        'title'    => __( 'Performance', 'erp' ),
                        'callback' => 'erp_hr_employee_single_tab_performance'
                    ),
                    'permission' => array(
                        'title'    => __( 'Permission', 'erp' ),
                        'callback' => 'erp_hr_employee_single_tab_permission'
                    ),
                ), $employee );

                if ( ! current_user_can( 'erp_create_review' ) && isset( $tabs['permission'] ) && isset( $tabs['performance'] ) && isset( $tabs['notes'] ) ) {
                    unset( $tabs['permission'] );
                    unset( $tabs['performance'] );
                    unset( $tabs['notes'] );
                }

                if ( ! current_user_can( 'erp_edit_employee', $employee->id ) ) {
                    unset( $tabs['leave'] );
                    unset( $tabs['job'] );
                }
                ?>

                <h2 class="nav-tab-wrapper erp-hide-print" style="margin-bottom: 15px;">
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
