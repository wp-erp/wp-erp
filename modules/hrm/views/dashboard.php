<div class="wrap erp hrm-dashboard">
    <h2><?php _e( 'HR Management', '$domain' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">

            <?php
            $company_id   = erp_get_current_company_id();
            $employees    = erp_hr_get_employees( $company_id, true );
            $departments  = erp_hr_get_departments( $company_id );
            $designations = erp_hr_get_designations( $company_id );
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

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">
            <div class="postbox leads-actions">
                <h3 class="hndle"><span>Birthday Buddies</span></h3>
                <div class="inside">
                    <ul class="erp-list list-inline">
                        <li><a href="#"><?php echo get_avatar( 'john@doe.com', 32 ); ?></a></li>
                        <li><a href="#"><?php echo get_avatar( 'john@doe.com', 32 ); ?></a></li>
                        <li><a href="#"><?php echo get_avatar( 'john@doe.com', 32 ); ?></a></li>
                    </ul>
                </div>
            </div><!-- .postbox -->
        </div>

    </div>
</div>