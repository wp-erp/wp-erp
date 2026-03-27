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
        <h2>
            <?php esc_html_e( 'Payroll Overview', 'erp' ); ?>
            <a href="#" class="page-title-action erp-pro-preview-action" data-form="pro-form-new-payrun" data-form-title="<?php esc_attr_e( 'Start New Pay Run', 'erp' ); ?>"><?php esc_html_e( 'New Pay Run', 'erp' ); ?></a>
        </h2>

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
                            <td><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-payrun" data-form-title="<?php esc_attr_e( 'View Pay Run', 'erp' ); ?>">View</a></td>
                        </tr>
                        <tr>
                            <td>Feb 01 - Feb 28, 2026</td>
                            <td>Monthly Payroll</td>
                            <td>Feb 28, 2026</td>
                            <td>12</td>
                            <td>$4,150.00</td>
                            <td><span class="erp-badge" style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Approved</span></td>
                            <td><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-payrun" data-form-title="<?php esc_attr_e( 'View Pay Run', 'erp' ); ?>">View</a></td>
                        </tr>
                        <tr>
                            <td>Jan 01 - Jan 31, 2026</td>
                            <td>Monthly Payroll</td>
                            <td>Jan 31, 2026</td>
                            <td>11</td>
                            <td>$3,890.00</td>
                            <td><span class="erp-badge" style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Approved</span></td>
                            <td><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-payrun" data-form-title="<?php esc_attr_e( 'View Pay Run', 'erp' ); ?>">View</a></td>
                        </tr>
                        <tr>
                            <td>Dec 01 - Dec 31, 2025</td>
                            <td>Monthly Payroll</td>
                            <td>Dec 31, 2025</td>
                            <td>11</td>
                            <td>$3,890.00</td>
                            <td><span class="erp-badge" style="background: #fff7ed; color: #9a3412; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Pending</span></td>
                            <td><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-payrun" data-form-title="<?php esc_attr_e( 'View Pay Run', 'erp' ); ?>">View</a></td>
                        </tr>
                        <tr>
                            <td>Nov 01 - Nov 30, 2025</td>
                            <td>Monthly Payroll</td>
                            <td>Nov 30, 2025</td>
                            <td>10</td>
                            <td>$3,520.00</td>
                            <td><span class="erp-badge" style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Approved</span></td>
                            <td><a href="#" class="erp-pro-preview-action" data-form="pro-form-new-payrun" data-form-title="<?php esc_attr_e( 'View Pay Run', 'erp' ); ?>">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form template for New Pay Run -->
<div id="pro-form-new-payrun" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Pay Calendar', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option value=""><?php esc_html_e( '— Select Pay Calendar —', 'erp' ); ?></option>
                    <option>Monthly Payroll</option>
                    <option>Bi-Weekly Payroll</option>
                    <option>Weekly Payroll</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Pay Period Start', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Pay Period End', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Payment Date', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Employees', 'erp' ); ?></label></th>
            <td>
                <label><input type="radio" name="pay_employees" checked /> <?php esc_html_e( 'All Employees', 'erp' ); ?></label><br>
                <label><input type="radio" name="pay_employees" /> <?php esc_html_e( 'Select Department', 'erp' ); ?></label><br>
                <label><input type="radio" name="pay_employees" /> <?php esc_html_e( 'Select Individually', 'erp' ); ?></label>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Note', 'erp' ); ?></label></th>
            <td><textarea rows="2" class="large-text" placeholder="<?php esc_attr_e( 'Optional note for this pay run...', 'erp' ); ?>"></textarea></td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Start Pay Run', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
