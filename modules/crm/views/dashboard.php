<div class="wrap erp crm-dashboard">
    <h2><?php _e( 'CRM Dashboard', 'erp' ); ?></h2>

    <div class="erp-single-container">
        <div class="erp-area-left">

            <?php 
            include WPERP_CRM_VIEWS . '/dashboard-badge.php';

            do_action( 'erp_crm_dashboard_widgets_left' );

            ?>

        </div><!-- .erp-area-left -->

        <div class="erp-area-right">
            <?php do_action( 'erp_crm_dashboard_widgets_right' ); ?>
        </div>
    </div>
</div>
