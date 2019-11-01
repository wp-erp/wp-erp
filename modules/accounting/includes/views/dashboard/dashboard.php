<div class="erp-badge-box box-ac">
    <h2><?php _e( 'Accounting', 'erp' ); ?> <a href="<?php echo admin_url( 'admin.php?page=erp-accounting' ); ?>" class="btn"><?php _e( 'Visit Dashboard', 'erp' ); ?></a></h2>

    <div class="erp-badge-ac-box">
        <h3><?php _e( 'Cash & Bank Accounts', 'erp' ); ?></h3>
        <?php
        $items = erp_acct_get_dashboard_banks();
        include_once ERP_ACCOUNTING_VIEWS . '/dashboard/bank.php';
        ?>
    </div>
</div>
