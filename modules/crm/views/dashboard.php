<div class="wrap erp crm-dashboard">
    <h2><?php _e( 'CRM Dashboard', 'wp-erp' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">

                <div class="erp-grid-container badge-container">
                    <div class="row">
                        <div class="col-3 badge-wrap badge-green">
                            <div class="badge-inner">
                                <h3><?php echo number_format_i18n( 10, 0 ); ?></h3>
                                <p><?php _e( 'Employees', 'wp-erp' ); ?></p>
                            </div>

                            <div class="badge-footer wp-ui-highlight">
                                <a href="<?php echo admin_url( 'admin.php?page=erp-hr-employee' ); ?>"><?php _e( 'View Employees', 'wp-erp' ); ?></a>
                            </div>
                        </div><!-- .badge-wrap -->

                        <div class="col-3 badge-wrap badge-green">
                            <div class="badge-inner">
                                <h3><?php echo number_format_i18n( 10, 0 ); ?></h3>
                                <p><?php _e( 'Department', 'wp-erp' ); ?></p>
                            </div>

                            <div class="badge-footer wp-ui-highlight">
                                <a href="<?php echo admin_url( 'admin.php?page=erp-hr-employee' ); ?>"><?php _e( 'View Employees', 'wp-erp' ); ?></a>
                            </div>
                        </div><!-- .badge-wrap -->
                    </div>
                </div><!-- .badge-container -->

            <?php do_action( 'erp_crm_dashboard_widgets_left' ); ?>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">

            <?php do_action( 'erp_crm_dashboard_widgets_right' ); ?>

        </div>

    </div>
</div>
