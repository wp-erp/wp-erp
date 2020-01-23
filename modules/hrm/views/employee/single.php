<div class="wrap erp erp-hr-employees erp-employee-single">

    <?php
    if ( is_admin() ) {
        ?>
        <h2 class="erp-hide-print"><?php empty( $is_my_profile_page ) ? esc_html_e( 'Employee', 'erp' ) : esc_html_e( 'My Profile', 'erp' );

        if ( empty( $is_my_profile_page ) && current_user_can( 'erp_create_employee' ) ) {
            ?>
            <a href="#" id="erp-employee-new" class="add-new-h2 erp-hide-print"><?php esc_html_e( 'Add New', 'erp' ); ?></a>
            <?php
        }
    }
    ?>
    </h2>

    <?php if( isset( $_GET['msg'] ) && $_GET['msg'] == 'success'):  ?>
    <div class="notice notice-success is-dismissible">
        <p> <?php esc_html_e('Data Successfully saved.','erp'); ?> </p>
    </div>
    <?php endif;  ?>
    <div class="erp-single-container erp-hr-employees-wrap" id="erp-single-container-wrap">
        <div class="erp-area-left full-width erp-hr-employees-wrap-inner">
            <div id="erp-area-left-inner">

                <script type="text/javascript">
                    window.wpErpCurrentEmployee = <?php echo json_encode( $employee->to_array() ); ?>;
                </script>

                <div class="erp-profile-top">
                    <div class="erp-avatar">
                        <?php echo wp_kses_post( $employee->get_avatar( 150 ) ); ?>
                        <?php if ( $employee->get_status('view') !== 'Active' ): ?>
                            <span class="inactive">
                                <?php echo esc_html( $employee->get_status('view') ); ?>
                            </span>
                        <?php endif ?>
                    </div>

                    <div class="erp-user-info">
                        <h3><span class="title"><?php echo esc_html( $employee->get_full_name() ); ?></span></h3>

                        <ul class="lead-info">
                            <li>
                                <?php echo esc_html( $employee->get_job_title() ); ?> - <?php echo esc_html( $employee->get_department_title() ); ?>
                            </li>

                            <li>
                                <a href="mailto:<?php echo esc_html( $employee->user_email ); ?>"><?php echo esc_html( $employee->user_email ); ?></a>
                            </li>

                            <?php
                            $phones = array();
                            if ( $work_phone = $employee->get_work_phone() ) {
                                $phones[] = $work_phone;
                            }
                            if ( $mobile_phone = $employee->get_mobile() ) {
                                $phones[] = $mobile_phone;
                            }

                            if ( $phones ) { ?>
                                <li>
                                    <ul class="erp-list list-inline">
                                        <?php foreach( $phones as $phone ) { ?>
                                            <li><a href="tel:<?php echo esc_html( $phone ); ?>"><span class="dashicons dashicons-smartphone"></span></a><?php echo esc_html( $phone ); ?></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>
                    </div><!-- .erp-user-info -->

                    <div class="erp-area-right erp-hide-print">
                        <div class="postbox leads-actions">
                            <h3 class="hndle"><span><?php esc_html_e( 'Actions', 'erp' ); ?></span></h3>
                            <div class="inside">
                                <?php
                                if ( current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) {
                                    ?>
                                    <span class="edit"><a class="button button-primary" data-id="<?php echo esc_html( $employee->get_user_id() ); ?>" data-single="true" href="#"><?php esc_html_e( 'Edit', 'erp' ); ?></a></span>
                                    <?php
                                }
                                ?>

                                <?php if ( $employee->get_status() != 'Terminated' && current_user_can( 'erp_create_employee' ) ): ?>
                                    <a class="button" href="#" id="erp-employee-terminate" data-id="<?php echo esc_html( $employee->get_user_id() ); ?>" data-template="erp-employment-terminate" data-title="<?php esc_html_e( 'Terminate Employee', 'erp' ); ?>"><?php esc_html_e( 'Terminate', 'erp' ); ?></a>
                                <?php endif; ?>

                                <?php if ( ( isset( $_GET['tab'] ) && $_GET['tab'] == 'general' ) || !isset( $_GET['tab'] )  ): ?>
                                    <a class="button" id="erp-employee-print" href="#"><?php esc_html_e( 'Print', 'erp' ); ?></a>
                                <?php endif ?>
                            </div>
                        </div><!-- .postbox -->
                    </div><!-- .leads-right -->

                    <?php do_action( 'erp_hr_employee_single_after_info', $employee ); ?>

                </div><!-- .erp-profile-top -->

                <?php
                $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
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

                $department_lead_id = erp_hr_get_department_lead_by_user( $employee->get_user_id() );

                if ( ! current_user_can( 'erp_create_review', $employee->get_user_id() ) && isset( $tabs['permission'] ) && isset( $tabs['performance'] ) && isset( $tabs['notes'] ) ) {
                    unset( $tabs['permission'] );
                    unset( $tabs['notes'] );

                    if ( get_current_user_id() !== $department_lead_id ) {
                        unset( $tabs['performance'] );
                    }
                }

                if ( ! current_user_can( 'erp_edit_employee', $employee->get_user_id() ) ) {
                    unset( $tabs['leave'] );
                    unset( $tabs['job'] );
                }

                if ( absint( $employee->get_user_id() ) === get_current_user_id() ) {
                    unset( $tabs['permission'] );
                }
                ?>

                <h2 class="nav-tab-wrapper erp-hide-print" style="margin-bottom: 15px;">
                    <?php foreach ($tabs as $key => $tab) {
                        $active_class = ( $key == $active_tab ) ? ' nav-tab-active' : '';
                        ?>
                        <a href="<?php echo esc_url( erp_hr_employee_tab_url( $key, $employee->get_user_id() ) ); ?>" class="nav-tab<?php echo esc_html( $active_class ); ?>"><?php echo esc_html( $tab['title'] ) ; ?></a>
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
