<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Payroll Reports.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=payroll-reports" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h2><?php esc_html_e( 'Payroll Reports', 'erp' ); ?></h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; margin-top: 20px;">
            <!-- Pay Run by Employee -->
            <div class="postbox" style="margin: 0;">
                <h2 class="hndle" style="padding: 3px 12px;"><span><?php esc_html_e( 'Pay Run by Employee', 'erp' ); ?></span></h2>
                <div class="inside">
                    <p><?php esc_html_e( 'Pay Run report detail by employee', 'erp' ); ?></p>
                    <a href="#" class="button button-primary erp-pro-preview-action"><?php esc_html_e( 'View Report', 'erp' ); ?></a>
                </div>
            </div>

            <!-- Pay Run Summary -->
            <div class="postbox" style="margin: 0;">
                <h2 class="hndle" style="padding: 3px 12px;"><span><?php esc_html_e( 'Pay Run Summary', 'erp' ); ?></span></h2>
                <div class="inside">
                    <p><?php esc_html_e( 'Pay Run Summary reports', 'erp' ); ?></p>
                    <a href="#" class="button button-primary erp-pro-preview-action"><?php esc_html_e( 'View Report', 'erp' ); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
