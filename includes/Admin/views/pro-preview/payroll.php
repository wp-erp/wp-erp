<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Payroll module.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=payroll" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap erp hrm-dashboard hrm-payroll-dashboard">
        <h2><?php esc_html_e( 'Payroll Overview', 'erp' ); ?></h2>

        <div class="erp-grid-container" style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
            <!-- Checklist -->
            <div class="postbox" style="flex: 1; min-width: 280px;">
                <h2 class="hndle"><span><?php esc_html_e( 'Checklist', 'erp' ); ?></span></h2>
                <div class="inside">
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                            <span class="dashicons dashicons-yes-alt" style="color: #139F84;"></span>
                            <?php esc_html_e( 'Setup Wizard', 'erp' ); ?>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px solid #eee;">
                            <span class="dashicons dashicons-yes-alt" style="color: #139F84;"></span>
                            <?php esc_html_e( 'Pay Calendar', 'erp' ); ?>
                        </li>
                        <li style="padding: 8px 0;">
                            <span class="dashicons dashicons-marker" style="color: #ddd;"></span>
                            <?php esc_html_e( 'Pay Run', 'erp' ); ?>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Badge Cards -->
            <div style="flex: 3; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px;">
                <div class="postbox" style="margin: 0;">
                    <div class="inside" style="text-align: center; padding: 20px;">
                        <h3 style="margin: 0; color: #1e1e1e; font-size: 28px;">$12,450.00</h3>
                        <p style="color: #656668; margin: 5px 0 0;"><?php esc_html_e( 'Total Expenses', 'erp' ); ?></p>
                    </div>
                </div>
                <div class="postbox" style="margin: 0;">
                    <div class="inside" style="text-align: center; padding: 20px;">
                        <h3 style="margin: 0; color: #1e1e1e; font-size: 28px;">3</h3>
                        <p style="color: #656668; margin: 5px 0 0;"><?php esc_html_e( 'Pay Calendars Created', 'erp' ); ?></p>
                    </div>
                </div>
                <div class="postbox" style="margin: 0;">
                    <div class="inside" style="text-align: center; padding: 20px;">
                        <h3 style="margin: 0; color: #1e1e1e; font-size: 28px;">2</h3>
                        <p style="color: #656668; margin: 5px 0 0;"><?php esc_html_e( 'Pay Calendars Approved', 'erp' ); ?></p>
                    </div>
                </div>
                <div class="postbox" style="margin: 0;">
                    <div class="inside" style="text-align: center; padding: 20px;">
                        <h3 style="margin: 0; color: #1e1e1e; font-size: 28px;">$4,150.00</h3>
                        <p style="color: #656668; margin: 5px 0 0;"><?php esc_html_e( 'Spent Previous Month', 'erp' ); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Pay Runs -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span><?php esc_html_e( 'Latest 5 Pay Run Records', 'erp' ); ?></span></h2>
            <div class="inside" style="padding: 0;">
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Pay Period', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Pay Run', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Payment Date', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Employees', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Net Pay', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'erp' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'erp' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Mar 01 - Mar 31, 2026</td>
                            <td>Monthly Payroll</td>
                            <td>Mar 31, 2026</td>
                            <td>12</td>
                            <td>$4,150.00</td>
                            <td><span class="erp-badge" style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Approved</span></td>
                            <td><a href="#" class="erp-pro-preview-action">View</a></td>
                        </tr>
                        <tr>
                            <td>Feb 01 - Feb 28, 2026</td>
                            <td>Monthly Payroll</td>
                            <td>Feb 28, 2026</td>
                            <td>12</td>
                            <td>$4,150.00</td>
                            <td><span class="erp-badge" style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Approved</span></td>
                            <td><a href="#" class="erp-pro-preview-action">View</a></td>
                        </tr>
                        <tr>
                            <td>Jan 01 - Jan 31, 2026</td>
                            <td>Monthly Payroll</td>
                            <td>Jan 31, 2026</td>
                            <td>11</td>
                            <td>$3,890.00</td>
                            <td><span class="erp-badge" style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Approved</span></td>
                            <td><a href="#" class="erp-pro-preview-action">View</a></td>
                        </tr>
                        <tr>
                            <td>Dec 01 - Dec 31, 2025</td>
                            <td>Monthly Payroll</td>
                            <td>Dec 31, 2025</td>
                            <td>11</td>
                            <td>$3,890.00</td>
                            <td><span class="erp-badge" style="background: #fff7ed; color: #9a3412; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Pending</span></td>
                            <td><a href="#" class="erp-pro-preview-action">View</a></td>
                        </tr>
                        <tr>
                            <td>Nov 01 - Nov 30, 2025</td>
                            <td>Monthly Payroll</td>
                            <td>Nov 30, 2025</td>
                            <td>10</td>
                            <td>$3,520.00</td>
                            <td><span class="erp-badge" style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Approved</span></td>
                            <td><a href="#" class="erp-pro-preview-action">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
