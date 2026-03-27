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
                <h2 class="hndle"><span><?php esc_html_e( 'Pay Run by Employee', 'erp' ); ?></span></h2>
                <div class="inside">
                    <p><?php esc_html_e( 'Pay Run report detail by employee', 'erp' ); ?></p>
                    <a href="#" class="button button-primary erp-pro-preview-action" data-form="pro-form-employee-report" data-form-title="<?php esc_attr_e( 'Pay Run by Employee', 'erp' ); ?>"><?php esc_html_e( 'View Report', 'erp' ); ?></a>
                </div>
            </div>

            <!-- Pay Run Summary -->
            <div class="postbox" style="margin: 0;">
                <h2 class="hndle"><span><?php esc_html_e( 'Pay Run Summary', 'erp' ); ?></span></h2>
                <div class="inside">
                    <p><?php esc_html_e( 'Pay Run Summary reports', 'erp' ); ?></p>
                    <a href="#" class="button button-primary erp-pro-preview-action" data-form="pro-form-summary-report" data-form-title="<?php esc_attr_e( 'Pay Run Summary', 'erp' ); ?>"><?php esc_html_e( 'View Report', 'erp' ); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form: Employee Report Preview -->
<div id="pro-form-employee-report" style="display:none;">
    <div style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 15px; flex-wrap: wrap;">
        <div>
            <label style="display: block; margin-bottom: 4px; font-size: 13px;"><?php esc_html_e( 'From Date', 'erp' ); ?></label>
            <input type="date" value="2026-01-01" />
        </div>
        <div>
            <label style="display: block; margin-bottom: 4px; font-size: 13px;"><?php esc_html_e( 'To Date', 'erp' ); ?></label>
            <input type="date" value="2026-03-31" />
        </div>
        <button class="button" disabled><?php esc_html_e( 'Search', 'erp' ); ?></button>
        <span style="margin-left: auto;">
            <a href="#" class="erp-pro-preview-action" style="text-decoration: none; font-size: 13px;"><?php esc_html_e( 'Export to CSV', 'erp' ); ?></a>
        </span>
    </div>
    <table class="widefat striped" style="font-size: 13px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Employee / Date', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Gross Wages', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Allowance', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Deduction', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Tax', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Net Pay', 'erp' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>John Smith</strong><br><small style="color: #888;">Jan 01 – Jan 31, 2026</small></td>
                <td>$3,500.00</td>
                <td>$250.00</td>
                <td>$200.00</td>
                <td>$375.00</td>
                <td><strong>$3,175.00</strong></td>
            </tr>
            <tr>
                <td><strong>Sarah Johnson</strong><br><small style="color: #888;">Jan 01 – Jan 31, 2026</small></td>
                <td>$3,200.00</td>
                <td>$200.00</td>
                <td>$150.00</td>
                <td>$340.00</td>
                <td><strong>$2,910.00</strong></td>
            </tr>
            <tr>
                <td><strong>Mike Davis</strong><br><small style="color: #888;">Jan 01 – Jan 31, 2026</small></td>
                <td>$3,000.00</td>
                <td>$200.00</td>
                <td>$180.00</td>
                <td>$320.00</td>
                <td><strong>$2,700.00</strong></td>
            </tr>
        </tbody>
    </table>
    <div class="erp-pro-form-footer">
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro for full reports', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-reports" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>

<!-- Hidden form: Summary Report Preview -->
<div id="pro-form-summary-report" style="display:none;">
    <div style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 15px; flex-wrap: wrap;">
        <div>
            <label style="display: block; margin-bottom: 4px; font-size: 13px;"><?php esc_html_e( 'From Date', 'erp' ); ?></label>
            <input type="date" value="2026-01-01" />
        </div>
        <div>
            <label style="display: block; margin-bottom: 4px; font-size: 13px;"><?php esc_html_e( 'To Date', 'erp' ); ?></label>
            <input type="date" value="2026-03-31" />
        </div>
        <button class="button" disabled><?php esc_html_e( 'Search', 'erp' ); ?></button>
        <span style="margin-left: auto; display: flex; gap: 8px;">
            <a href="#" class="erp-pro-preview-action" style="text-decoration: none; font-size: 13px;"><?php esc_html_e( 'Details to CSV', 'erp' ); ?></a>
            <a href="#" class="erp-pro-preview-action" style="text-decoration: none; font-size: 13px;"><?php esc_html_e( 'Bank report to CSV', 'erp' ); ?></a>
            <a href="#" class="erp-pro-preview-action" style="text-decoration: none; font-size: 13px;"><?php esc_html_e( 'Export to CSV', 'erp' ); ?></a>
        </span>
    </div>
    <table class="widefat striped" style="font-size: 13px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Employee', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Basic', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Allowances', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Deductions', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Taxes', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Net Pay', 'erp' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>John Smith</td>
                <td>$10,500.00</td>
                <td>$750.00</td>
                <td>$600.00</td>
                <td>$1,125.00</td>
                <td><strong>$9,525.00</strong></td>
            </tr>
            <tr>
                <td>Sarah Johnson</td>
                <td>$9,600.00</td>
                <td>$600.00</td>
                <td>$450.00</td>
                <td>$1,020.00</td>
                <td><strong>$8,730.00</strong></td>
            </tr>
            <tr>
                <td>Mike Davis</td>
                <td>$9,000.00</td>
                <td>$600.00</td>
                <td>$540.00</td>
                <td>$960.00</td>
                <td><strong>$8,100.00</strong></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align: right;"><strong><?php esc_html_e( 'Gross Total', 'erp' ); ?></strong></th>
                <th><strong>$26,355.00</strong></th>
            </tr>
        </tfoot>
    </table>
    <div class="erp-pro-form-footer">
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro for full reports', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-reports" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
