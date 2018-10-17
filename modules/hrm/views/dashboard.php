<div class="wrap erp hrm-dashboard">
    <h2><?php _e( 'HR Management', 'erp' ); ?></h2>

    <div class="erp-single-container">

        <div class="erp-area-left">
            <?php 
                include WPERP_HRM_VIEWS . '/dashboard-badge.php';
                
                do_action( 'erp_hr_dashboard_widgets_left' ); ?>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">

            <?php do_action( 'erp_hr_dashboard_widgets_right' ); ?>

        </div>

    </div>
</div>
