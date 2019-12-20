<div class="erp-badge-box box-ac">
    <h2><?php esc_html_e( 'Accounting', 'erp' ); ?> <a href="<?php echo esc_url_raw( admin_url( 'admin.php?page=erp-accounting' ) ); ?>" class="btn"><?php esc_html_e( 'Visit Dashboard', 'erp' ); ?></a></h2>

    <div class="erp-badge-ac-box">
        <h3><?php esc_html_e( 'Cash & Bank Accounts', 'erp' ); ?></h3>
        <?php
        $items = erp_acct_get_dashboard_banks();
        include_once ERP_ACCOUNTING_VIEWS . '/dashboard/bank.php';
        ?>
    </div>
</div>
