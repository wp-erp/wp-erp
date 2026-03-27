<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Attendance module.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=attendance" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e( 'Attendance', 'erp' ); ?></h1>

        <!-- Tab Navigation -->
        <div style="margin: 15px 0; border-bottom: 1px solid #ccc;">
            <a href="#" class="erp-pro-preview-action" style="display: inline-block; padding: 8px 16px; text-decoration: none; font-weight: 600; border-bottom: 2px solid #2271b1; color: #2271b1; margin-bottom: -1px;"><?php esc_html_e( 'Attendance', 'erp' ); ?></a>
            <a href="#" class="erp-pro-preview-action" style="display: inline-block; padding: 8px 16px; text-decoration: none; color: #656668;"><?php esc_html_e( 'Shifts', 'erp' ); ?></a>
            <a href="#" class="erp-pro-preview-action" style="display: inline-block; padding: 8px 16px; text-decoration: none; color: #656668;"><?php esc_html_e( 'Tools', 'erp' ); ?></a>
        </div>

        <!-- Filters -->
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px; flex-wrap: wrap;">
            <select disabled style="min-width: 150px;">
                <option><?php esc_html_e( 'All Departments', 'erp' ); ?></option>
            </select>
            <select disabled style="min-width: 150px;">
                <option><?php esc_html_e( 'All Designations', 'erp' ); ?></option>
            </select>
            <input type="date" disabled value="2026-03-01" style="min-width: 130px;" />
            <input type="date" disabled value="2026-03-27" style="min-width: 130px;" />
            <button class="button erp-pro-preview-action"><?php esc_html_e( 'Filter', 'erp' ); ?></button>
        </div>

        <!-- Attendance Table -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><input type="checkbox" disabled /></th>
                    <th><?php esc_html_e( 'Employee', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Department', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Clock In', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Clock Out', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Total Hours', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Shift', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td><strong>John Smith</strong></td>
                    <td>Engineering</td>
                    <td>Mar 27, 2026</td>
                    <td>09:00 AM</td>
                    <td>06:00 PM</td>
                    <td>9h 00m</td>
                    <td>Day Shift</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Present</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td><strong>Sarah Johnson</strong></td>
                    <td>Marketing</td>
                    <td>Mar 27, 2026</td>
                    <td>09:15 AM</td>
                    <td>06:30 PM</td>
                    <td>9h 15m</td>
                    <td>Day Shift</td>
                    <td><span style="background: #fff7ed; color: #9a3412; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Late</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td><strong>Mike Davis</strong></td>
                    <td>Engineering</td>
                    <td>Mar 27, 2026</td>
                    <td>08:45 AM</td>
                    <td>05:45 PM</td>
                    <td>9h 00m</td>
                    <td>Day Shift</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Present</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td><strong>Emily Chen</strong></td>
                    <td>Design</td>
                    <td>Mar 27, 2026</td>
                    <td>&mdash;</td>
                    <td>&mdash;</td>
                    <td>&mdash;</td>
                    <td>Day Shift</td>
                    <td><span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Absent</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td><strong>Alex Turner</strong></td>
                    <td>Sales</td>
                    <td>Mar 27, 2026</td>
                    <td>02:00 PM</td>
                    <td>10:00 PM</td>
                    <td>8h 00m</td>
                    <td>Night Shift</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Present</span></td>
                </tr>
                <tr>
                    <td><input type="checkbox" disabled /></td>
                    <td><strong>Rachel Kim</strong></td>
                    <td>HR</td>
                    <td>Mar 27, 2026</td>
                    <td>09:00 AM</td>
                    <td>05:00 PM</td>
                    <td>8h 00m</td>
                    <td>Day Shift</td>
                    <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 3px; font-size: 12px;">Present</span></td>
                </tr>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">6 items</span>
            </div>
        </div>
    </div>
</div>
