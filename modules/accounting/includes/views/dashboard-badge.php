<div class="erp-badge-box box-ac">
    <h2><?php _e( 'Accounting', 'erp' ); ?> <a href="<?php echo admin_url( 'admin.php?page=erp-accounting' ); ?>" class="btn"><?php _e( 'Visit Dashboard', 'erp' ); ?></a></h2>

    <div class="erp-badge-ac-box">
        <h3><?php _e( 'Cash & Bank Balance', 'erp' ); ?></h3>
        <?php erp_ac_dashboard_banks(); ?>
    </div>

    <div class="erp-badge-ac-box">
        <h3><?php _e( 'Revenues', 'erp' ); ?></h3>
        <?php erp_ac_dashboard_net_income(); ?>
    </div>
</div>
