<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Attendance Report (Employee Based).', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=attendance-report-employee" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h2><?php esc_html_e( 'Attendance Report', 'erp' ); ?></h2>

        <!-- Filters -->
        <div style="display: flex; gap: 10px; align-items: center; margin: 15px 0; flex-wrap: wrap;">
            <select disabled style="min-width: 140px;">
                <option><?php esc_html_e( 'All Locations', 'erp' ); ?></option>
            </select>
            <select disabled style="min-width: 140px;">
                <option><?php esc_html_e( 'All Departments', 'erp' ); ?></option>
            </select>
            <select disabled style="min-width: 140px;">
                <option><?php esc_html_e( 'This Month', 'erp' ); ?></option>
            </select>
            <button class="button erp-pro-preview-action"><?php esc_html_e( 'Filter', 'erp' ); ?></button>
            <button class="button erp-pro-preview-action"><span class="dashicons dashicons-download" style="margin-top: 3px;"></span> <?php esc_html_e( 'Export CSV', 'erp' ); ?></button>
        </div>

        <!-- Employee Attendance Summary Table -->
        <table class="widefat striped fixed">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Name', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Present', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Leave', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Absent', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Avg Work', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Avg Checkin', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Avg Checkout', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $employees = [
                    [ 'name' => 'John Smith',     'present' => 19, 'leave' => 1, 'absent' => 0, 'avg_work' => '8:45', 'avg_in' => '08:55 AM', 'avg_out' => '05:40 PM' ],
                    [ 'name' => 'Sarah Johnson',  'present' => 18, 'leave' => 0, 'absent' => 2, 'avg_work' => '9:10', 'avg_in' => '09:12 AM', 'avg_out' => '06:22 PM' ],
                    [ 'name' => 'Mike Davis',     'present' => 20, 'leave' => 0, 'absent' => 0, 'avg_work' => '8:30', 'avg_in' => '08:48 AM', 'avg_out' => '05:18 PM' ],
                    [ 'name' => 'Emily Chen',     'present' => 16, 'leave' => 3, 'absent' => 1, 'avg_work' => '8:15', 'avg_in' => '09:05 AM', 'avg_out' => '05:20 PM' ],
                    [ 'name' => 'Alex Turner',    'present' => 19, 'leave' => 1, 'absent' => 0, 'avg_work' => '8:00', 'avg_in' => '01:58 PM', 'avg_out' => '09:58 PM' ],
                    [ 'name' => 'Rachel Kim',     'present' => 17, 'leave' => 2, 'absent' => 1, 'avg_work' => '7:50', 'avg_in' => '09:02 AM', 'avg_out' => '04:52 PM' ],
                    [ 'name' => 'David Park',     'present' => 20, 'leave' => 0, 'absent' => 0, 'avg_work' => '8:55', 'avg_in' => '08:30 AM', 'avg_out' => '05:25 PM' ],
                    [ 'name' => 'Lisa Wang',      'present' => 18, 'leave' => 1, 'absent' => 1, 'avg_work' => '8:20', 'avg_in' => '09:10 AM', 'avg_out' => '05:30 PM' ],
                ];
                foreach ( $employees as $emp ) :
                ?>
                <tr>
                    <td><a href="#" class="erp-pro-preview-action"><strong><?php echo esc_html( $emp['name'] ); ?></strong></a></td>
                    <td><span style="color: #166534;"><?php echo esc_html( $emp['present'] ); ?></span></td>
                    <td><span style="color: #1e40af;"><?php echo esc_html( $emp['leave'] ); ?></span></td>
                    <td><span style="color: <?php echo $emp['absent'] > 0 ? '#991b1b' : '#374151'; ?>;"><?php echo esc_html( $emp['absent'] ); ?></span></td>
                    <td><?php echo esc_html( $emp['avg_work'] ); ?></td>
                    <td><?php echo esc_html( $emp['avg_in'] ); ?></td>
                    <td><?php echo esc_html( $emp['avg_out'] ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">8 items</span>
            </div>
        </div>
    </div>
</div>
