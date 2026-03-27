<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Asset Management module.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=asset" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap erp-hr-assets">
        <h2>
            <?php esc_html_e( 'Assets', 'erp' ); ?>
            <a href="#" class="add-new-h2 erp-pro-preview-action"><?php esc_html_e( 'New Entry', 'erp' ); ?></a>
        </h2>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th style="width: 30px;"><input type="checkbox" disabled /></th>
                    <th><?php esc_html_e( 'Asset Name', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Category', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Serial No.', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Purchase Date', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Purchase Price', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Assigned To', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action">MacBook Pro 16"</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Laptop</td>
                    <td>MBP-2026-001</td>
                    <td>Jan 15, 2026</td>
                    <td>$2,499.00</td>
                    <td><span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 3px; font-size: 12px;">In Use</span></td>
                    <td>John Smith</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action">Dell Monitor 27"</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Monitor</td>
                    <td>DL-MON-042</td>
                    <td>Feb 01, 2026</td>
                    <td>$449.00</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Available</span></td>
                    <td>&mdash;</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action">Herman Miller Chair</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Furniture</td>
                    <td>HM-CHR-017</td>
                    <td>Nov 20, 2025</td>
                    <td>$1,295.00</td>
                    <td><span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 3px; font-size: 12px;">In Use</span></td>
                    <td>Sarah Johnson</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action">iPhone 15 Pro</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Mobile</td>
                    <td>IP15-PRO-008</td>
                    <td>Dec 10, 2025</td>
                    <td>$999.00</td>
                    <td><span style="background: #fef9c3; color: #854d0e; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Maintenance</span></td>
                    <td>Mike Davis</td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td>
                        <strong><a href="#" class="erp-pro-preview-action">ThinkPad X1 Carbon</a></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Edit', 'erp' ); ?></a> | </span>
                            <span class="trash"><a href="#" class="erp-pro-preview-action"><?php esc_html_e( 'Delete', 'erp' ); ?></a></span>
                        </div>
                    </td>
                    <td>Laptop</td>
                    <td>TP-X1C-023</td>
                    <td>Mar 05, 2026</td>
                    <td>$1,849.00</td>
                    <td><span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 3px; font-size: 12px;">In Use</span></td>
                    <td>Emily Chen</td>
                </tr>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">5 items</span>
            </div>
        </div>
    </div>
</div>
