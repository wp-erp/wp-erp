<div class="wrap erp hrm-dashboard">
    <h2><?php _e( 'HR Management', '$domain' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">

            <div class="badge-container">
                <div class="badge-wrap badge-green">
                    <div class="badge-inner">
                        <h3>26</h3>
                        <p>Employees</p>
                    </div>

                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo admin_url( 'admin.php?page=erp-hr-employee' ); ?>">View Employees</a>
                    </div>
                </div><!-- .badge-wrap -->

                <div class="badge-wrap badge-red">
                    <div class="badge-inner">
                        <h3>5</h3>
                        <p>Departments</p>
                    </div>

                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo admin_url( 'admin.php?page=erp-hr-depts' ); ?>">View Departments</a>
                    </div>
                </div><!-- .badge-wrap -->

                <div class="badge-wrap badge-aqua">
                    <div class="badge-inner">
                        <h3>10</h3>
                        <p>Designation</p>
                    </div>

                    <div class="badge-footer wp-ui-highlight">
                        <a href="<?php echo admin_url( 'admin.php?page=erp-hr-designation' ); ?>">View Designation</a>
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