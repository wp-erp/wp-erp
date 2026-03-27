<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Pay Run List.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=payroll-payrun" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h2>
            <?php esc_html_e( 'Pay Run List', 'erp' ); ?>
            <a href="#" class="page-title-action erp-pro-preview-action" data-form="pro-form-new-payrun" data-form-title="<?php esc_attr_e( 'Start New Pay Run', 'erp' ); ?>"><?php esc_html_e( 'New Pay Run', 'erp' ); ?></a>
        </h2>

        <!-- Filters -->
        <div class="tablenav top" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <select disabled style="min-width: 140px;">
                <option><?php esc_html_e( 'All Statuses', 'erp' ); ?></option>
            </select>
            <input type="search" disabled placeholder="<?php esc_attr_e( 'Search pay runs...', 'erp' ); ?>" style="min-width: 200px;" />
            <button class="button erp-pro-preview-action"><?php esc_html_e( 'Filter', 'erp' ); ?></button>
        </div>

        <table class="wp-list-table widefat striped fixed">
            <thead>
                <tr>
                    <th style="width: 18%;"><?php esc_html_e( 'Pay Period', 'erp' ); ?></th>
                    <th style="width: 15%;"><?php esc_html_e( 'Pay Run', 'erp' ); ?></th>
                    <th style="width: 12%;"><?php esc_html_e( 'Payment Date', 'erp' ); ?></th>
                    <th style="width: 10%;"><?php esc_html_e( 'Employees', 'erp' ); ?></th>
                    <th style="width: 15%;"><?php esc_html_e( 'Net Pay + Tax', 'erp' ); ?></th>
                    <th style="width: 10%;"><?php esc_html_e( 'Status', 'erp' ); ?></th>
                    <th style="width: 20%;"><?php esc_html_e( 'Action', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $payruns = [
                    [
                        'period'   => 'Mar 01 – Mar 31, 2026',
                        'run'      => 'Monthly Payroll',
                        'pay_date' => 'Mar 31, 2026',
                        'emps'     => 12,
                        'net'      => '$4,150.00',
                        'status'   => 'Approved',
                        'color'    => '#166534',
                        'bg'       => '#dcfce7',
                    ],
                    [
                        'period'   => 'Feb 01 – Feb 28, 2026',
                        'run'      => 'Monthly Payroll',
                        'pay_date' => 'Feb 28, 2026',
                        'emps'     => 12,
                        'net'      => '$4,150.00',
                        'status'   => 'Approved',
                        'color'    => '#166534',
                        'bg'       => '#dcfce7',
                    ],
                    [
                        'period'   => 'Mar 01 – Mar 14, 2026',
                        'run'      => 'Bi-Weekly Payroll',
                        'pay_date' => 'Mar 14, 2026',
                        'emps'     => 3,
                        'net'      => '$1,820.00',
                        'status'   => 'Approved',
                        'color'    => '#166534',
                        'bg'       => '#dcfce7',
                    ],
                    [
                        'period'   => 'Jan 01 – Jan 31, 2026',
                        'run'      => 'Monthly Payroll',
                        'pay_date' => 'Jan 31, 2026',
                        'emps'     => 11,
                        'net'      => '$3,890.00',
                        'status'   => 'Approved',
                        'color'    => '#166534',
                        'bg'       => '#dcfce7',
                    ],
                    [
                        'period'   => 'Dec 01 – Dec 31, 2025',
                        'run'      => 'Monthly Payroll',
                        'pay_date' => 'Dec 31, 2025',
                        'emps'     => 11,
                        'net'      => '$3,890.00',
                        'status'   => 'Pending',
                        'color'    => '#9a3412',
                        'bg'       => '#fff7ed',
                    ],
                    [
                        'period'   => 'Nov 01 – Nov 30, 2025',
                        'run'      => 'Monthly Payroll',
                        'pay_date' => 'Nov 30, 2025',
                        'emps'     => 10,
                        'net'      => '$3,520.00',
                        'status'   => 'Approved',
                        'color'    => '#166534',
                        'bg'       => '#dcfce7',
                    ],
                    [
                        'period'   => 'Feb 15 – Feb 28, 2026',
                        'run'      => 'Bi-Weekly Payroll',
                        'pay_date' => 'Feb 28, 2026',
                        'emps'     => 3,
                        'net'      => '$1,820.00',
                        'status'   => 'Approved',
                        'color'    => '#166534',
                        'bg'       => '#dcfce7',
                    ],
                ];
                foreach ( $payruns as $pr ) :
                ?>
                <tr>
                    <td><?php echo esc_html( $pr['period'] ); ?></td>
                    <td><?php echo esc_html( $pr['run'] ); ?></td>
                    <td><?php echo esc_html( $pr['pay_date'] ); ?></td>
                    <td><?php echo esc_html( $pr['emps'] ); ?></td>
                    <td><?php echo esc_html( $pr['net'] ); ?></td>
                    <td>
                        <span style="background: <?php echo esc_attr( $pr['bg'] ); ?>; color: <?php echo esc_attr( $pr['color'] ); ?>; padding: 2px 8px; border-radius: 3px; font-size: 12px;">
                            <?php echo esc_html( $pr['status'] ); ?>
                        </span>
                    </td>
                    <td>
                        <a href="#" class="erp-pro-preview-action" data-form="pro-form-payrun-detail" data-form-title="<?php esc_attr_e( 'Pay Run Details', 'erp' ); ?>" title="<?php esc_attr_e( 'Edit', 'erp' ); ?>"><span class="dashicons dashicons-edit" style="font-size: 16px; width: 16px; height: 16px; color: #0073aa;"></span></a>
                        <a href="#" class="erp-pro-preview-action" title="<?php esc_attr_e( 'Delete', 'erp' ); ?>"><span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; color: #a00;"></span></a>
                        <a href="#" class="erp-pro-preview-action" title="<?php esc_attr_e( 'Copy', 'erp' ); ?>"><span class="dashicons dashicons-admin-page" style="font-size: 16px; width: 16px; height: 16px; color: #0073aa;"></span></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">7 items</span>
                <span class="pagination-links">
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>
                    <span class="paging-input">
                        <label class="screen-reader-text"><?php esc_html_e( 'Current Page', 'erp' ); ?></label>
                        <span class="tablenav-paging-text">1 of <span class="total-pages">1</span></span>
                    </span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>
                    <span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form: Pay Run Detail (multi-step preview) -->
<div id="pro-form-payrun-detail" style="display:none;">
    <div style="margin-bottom: 15px;">
        <div style="display: flex; gap: 0; border-bottom: 2px solid #ddd; margin-bottom: 15px;">
            <span style="padding: 8px 16px; background: #0073aa; color: #fff; font-size: 13px; border-radius: 3px 3px 0 0;"><?php esc_html_e( 'Employees', 'erp' ); ?></span>
            <span style="padding: 8px 16px; color: #656668; font-size: 13px;"><?php esc_html_e( 'Variable Input', 'erp' ); ?></span>
            <span style="padding: 8px 16px; color: #656668; font-size: 13px;"><?php esc_html_e( 'Payslips', 'erp' ); ?></span>
            <span style="padding: 8px 16px; color: #656668; font-size: 13px;"><?php esc_html_e( 'Approve', 'erp' ); ?></span>
        </div>
    </div>
    <table class="widefat striped" style="margin-bottom: 10px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Employee', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Department', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Pay Basic', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Payment', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Deduction', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Tax', 'erp' ); ?></th>
                <th><?php esc_html_e( 'Net Pay', 'erp' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>John Smith</td>
                <td>Engineering</td>
                <td>$3,500.00</td>
                <td>$3,750.00</td>
                <td>$200.00</td>
                <td>$375.00</td>
                <td><strong>$3,175.00</strong></td>
            </tr>
            <tr>
                <td>Sarah Johnson</td>
                <td>Marketing</td>
                <td>$3,200.00</td>
                <td>$3,400.00</td>
                <td>$150.00</td>
                <td>$340.00</td>
                <td><strong>$2,910.00</strong></td>
            </tr>
            <tr>
                <td>Mike Davis</td>
                <td>Engineering</td>
                <td>$3,000.00</td>
                <td>$3,200.00</td>
                <td>$180.00</td>
                <td>$320.00</td>
                <td><strong>$2,700.00</strong></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2"><strong><?php esc_html_e( 'Total', 'erp' ); ?></strong></th>
                <th><strong>$9,700.00</strong></th>
                <th><strong>$10,350.00</strong></th>
                <th><strong>$530.00</strong></th>
                <th><strong>$1,035.00</strong></th>
                <th><strong>$8,785.00</strong></th>
            </tr>
        </tfoot>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Next →', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to manage pay runs', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-payrun" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>

<!-- Hidden form: New Pay Run (reused from dashboard) -->
<div id="pro-form-new-payrun" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Pay Calendar', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option value=""><?php esc_html_e( '— Select Pay Calendar —', 'erp' ); ?></option>
                    <option>Monthly Payroll</option>
                    <option>Bi-Weekly Payroll</option>
                    <option>Hourly Contract</option>
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
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-payrun" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
