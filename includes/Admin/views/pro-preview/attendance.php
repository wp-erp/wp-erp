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
        <h1 class="wp-heading-inline">
            <?php esc_html_e( 'Attendance', 'erp' ); ?>
            <a href="#" class="page-title-action erp-pro-preview-action" data-form="pro-form-manual-attendance" data-form-title="<?php esc_attr_e( 'Add Manual Entry', 'erp' ); ?>"><?php esc_html_e( 'Add Manual Entry', 'erp' ); ?></a>
        </h1>

        <!-- Tab Navigation -->
        <div style="margin: 15px 0; border-bottom: 1px solid #ccc;">
            <a href="#" class="erp-pro-preview-action" style="display: inline-block; padding: 8px 16px; text-decoration: none; font-weight: 600; border-bottom: 2px solid #2271b1; color: #2271b1; margin-bottom: -1px;"><?php esc_html_e( 'Attendance', 'erp' ); ?></a>
            <a href="#" class="erp-pro-preview-action" data-form="pro-form-new-shift" data-form-title="<?php esc_attr_e( 'Add New Shift', 'erp' ); ?>" style="display: inline-block; padding: 8px 16px; text-decoration: none; color: #656668;"><?php esc_html_e( 'Shifts', 'erp' ); ?></a>
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

<!-- Hidden form template for Manual Attendance Entry -->
<div id="pro-form-manual-attendance" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Employee', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option value=""><?php esc_html_e( '— Select Employee —', 'erp' ); ?></option>
                    <option>John Smith</option>
                    <option>Sarah Johnson</option>
                    <option>Mike Davis</option>
                    <option>Emily Chen</option>
                    <option>Alex Turner</option>
                    <option>Rachel Kim</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Date', 'erp' ); ?></label></th>
            <td><input type="date" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Clock In', 'erp' ); ?></label></th>
            <td><input type="time" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Clock Out', 'erp' ); ?></label></th>
            <td><input type="time" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Shift', 'erp' ); ?></label></th>
            <td>
                <select style="min-width: 200px;">
                    <option>Day Shift</option>
                    <option>Night Shift</option>
                    <option>Flexible</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Note', 'erp' ); ?></label></th>
            <td><textarea rows="2" class="large-text" placeholder="<?php esc_attr_e( 'Optional note...', 'erp' ); ?>"></textarea></td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Save Entry', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=attendance" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>

<!-- Hidden form template for New Shift -->
<div id="pro-form-new-shift" style="display:none;">
    <table class="form-table">
        <tr>
            <th><label><?php esc_html_e( 'Shift Name', 'erp' ); ?></label></th>
            <td><input type="text" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Morning Shift', 'erp' ); ?>" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Start Time', 'erp' ); ?></label></th>
            <td><input type="time" value="09:00" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'End Time', 'erp' ); ?></label></th>
            <td><input type="time" value="17:00" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Grace Period (minutes)', 'erp' ); ?></label></th>
            <td><input type="number" min="0" value="15" style="width: 80px;" /></td>
        </tr>
        <tr>
            <th><label><?php esc_html_e( 'Working Days', 'erp' ); ?></label></th>
            <td>
                <label style="margin-right: 10px;"><input type="checkbox" checked /> <?php esc_html_e( 'Mon', 'erp' ); ?></label>
                <label style="margin-right: 10px;"><input type="checkbox" checked /> <?php esc_html_e( 'Tue', 'erp' ); ?></label>
                <label style="margin-right: 10px;"><input type="checkbox" checked /> <?php esc_html_e( 'Wed', 'erp' ); ?></label>
                <label style="margin-right: 10px;"><input type="checkbox" checked /> <?php esc_html_e( 'Thu', 'erp' ); ?></label>
                <label style="margin-right: 10px;"><input type="checkbox" checked /> <?php esc_html_e( 'Fri', 'erp' ); ?></label>
                <label style="margin-right: 10px;"><input type="checkbox" /> <?php esc_html_e( 'Sat', 'erp' ); ?></label>
                <label><input type="checkbox" /> <?php esc_html_e( 'Sun', 'erp' ); ?></label>
            </td>
        </tr>
    </table>
    <div class="erp-pro-form-footer">
        <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Save Shift', 'erp' ); ?></button>
        <span class="erp-pro-save-notice">
            <span class="dashicons dashicons-lock"></span>
            <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
            &mdash;
            <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=attendance" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
        </span>
    </div>
</div>
