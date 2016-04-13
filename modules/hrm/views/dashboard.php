<div class="wrap erp hrm-dashboard">
    <h2><?php _e( 'HR Management', 'erp' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">

            <?php if ( current_user_can('erp_create_employee') ): ?>

                <?php
                $employees    = erp_hr_get_employees( [ 'number'=> -1, 'status' => 'active' ] );
                $departments  = erp_hr_get_departments( [ 'number' => -1 ] );
                $designations = erp_hr_get_designations( [ 'number'=> -1 ] );
                ?>

                <div class="badge-container">
                    <div class="badge-wrap badge-green">
                        <div class="badge-inner">
                            <h3><?php echo number_format_i18n( count( $employees ), 0 ); ?></h3>
                            <p><?php _e( 'Employees', 'erp' ); ?></p>
                        </div>

                        <div class="badge-footer wp-ui-highlight">
                            <a href="<?php echo admin_url( 'admin.php?page=erp-hr-employee' ); ?>"><?php _e( 'View Employees', 'erp' ); ?></a>
                        </div>
                    </div><!-- .badge-wrap -->

                    <div class="badge-wrap badge-red">
                        <div class="badge-inner">
                            <h3><?php echo number_format_i18n( count( $departments ), 0 ); ?></h3>
                            <p><?php _e( 'Departments', 'erp' ); ?></p>
                        </div>

                        <div class="badge-footer wp-ui-highlight">
                            <a href="<?php echo admin_url( 'admin.php?page=erp-hr-depts' ); ?>"><?php _e( 'View Departments', 'erp' ); ?></a>
                        </div>
                    </div><!-- .badge-wrap -->

                    <div class="badge-wrap badge-aqua">
                        <div class="badge-inner">
                            <h3><?php echo number_format_i18n( count( $designations ), 0 ); ?></h3>
                            <p><?php _e( 'Designation', 'erp' ); ?></p>
                        </div>

                        <div class="badge-footer wp-ui-highlight">
                            <a href="<?php echo admin_url( 'admin.php?page=erp-hr-designation' ); ?>"><?php _e( 'View Designation', 'erp' ); ?></a>
                        </div>
                    </div><!-- .badge-wrap -->
                </div><!-- .badge-container -->

            <?php endif ?>

            <?php do_action( 'erp_hr_dashboard_widgets_left' ); ?>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">

            <?php do_action( 'erp_hr_dashboard_widgets_right' ); ?>

        </div>

    </div>
</div>
