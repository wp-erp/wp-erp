<div class="wrap erp hrm-dashboard">
    <h2><?php esc_html_e( 'HR Management', 'erp' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">

            <?php if ( current_user_can( 'erp_create_employee' ) ) { ?>

                <?php
                $employees    = \WeDevs\ERP\HRM\Models\Employee::where( 'status', 'active' )->count();
                $departments  = \WeDevs\ERP\HRM\Models\Department::count();
                $designations = \WeDevs\ERP\HRM\Models\Designation::count();
                ?>

                <div class="badge-container">
                    <div class="badge-wrap badge-green">
                        <div class="badge-inner">
                            <h3><?php echo esc_html( number_format_i18n( $employees, 0 ) ); ?></h3>
                            <p><?php esc_html_e( 'Employees', 'erp' ); ?></p>
                        </div>

                        <div class="badge-footer wp-ui-highlight">
                            <a href="<?php echo esc_url( erp_hr_employee_list_url() ); ?>"><?php esc_html_e( 'View Employees', 'erp' ); ?></a>
                        </div>
                    </div><!-- .badge-wrap -->

                    <div class="badge-wrap badge-red">
                        <div class="badge-inner">
                            <h3><?php echo esc_html( number_format_i18n( $departments, 0 ) ); ?></h3>
                            <p><?php esc_html_e( 'Departments', 'erp' ); ?></p>
                        </div>
                        <?php
                        if ( is_admin() ) {
                            ?>
                            <div class="badge-footer wp-ui-highlight">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr&section=people&sub-section=department' ) ); ?>"><?php esc_html_e( 'View Departments', 'erp' ); ?></a>                            </div>
                            <?php
                        }
                        ?>

                    </div><!-- .badge-wrap -->

                    <div class="badge-wrap badge-aqua">
                        <div class="badge-inner">
                            <h3><?php echo esc_html( number_format_i18n( $designations, 0 ) ); ?></h3>
                            <p><?php esc_html_e( 'Designation', 'erp' ); ?></p>
                        </div>
                        <?php
                        if ( is_admin() ) {
                            ?>
                            <div class="badge-footer wp-ui-highlight">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-hr&section=people&sub-section=designation' ) ); ?>"><?php esc_html_e( 'View Designation', 'erp' ); ?></a>
                            </div>
                            <?php
                        }
                        ?>
                    </div><!-- .badge-wrap -->
                </div><!-- .badge-container -->

            <?php } ?>

            <?php do_action( 'erp_hr_dashboard_widgets_left' ); ?>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">

            <?php do_action( 'erp_hr_dashboard_widgets_right' ); ?>

        </div>

    </div>
</div>
