<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of the Attendance Report (Date Based).', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=attendance-report" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h2><?php esc_html_e( 'Attendance Reports', 'erp' ); ?></h2>

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

        <!-- Attendance Summary Table -->
        <table class="widefat striped fixed">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Total', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Present', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Leave', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Absent', 'erp' ); ?></th>
                    <th><?php esc_html_e( 'Comment', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $dates = [
                    [ 'date' => 'March 27, 2026', 'total' => 12, 'present' => 10, 'leave' => 1, 'absent' => 1 ],
                    [ 'date' => 'March 26, 2026', 'total' => 12, 'present' => 11, 'leave' => 0, 'absent' => 1 ],
                    [ 'date' => 'March 25, 2026', 'total' => 12, 'present' => 12, 'leave' => 0, 'absent' => 0 ],
                    [ 'date' => 'March 24, 2026', 'total' => 12, 'present' => 9,  'leave' => 2, 'absent' => 1 ],
                    [ 'date' => 'March 21, 2026', 'total' => 12, 'present' => 11, 'leave' => 1, 'absent' => 0 ],
                    [ 'date' => 'March 20, 2026', 'total' => 12, 'present' => 10, 'leave' => 1, 'absent' => 1 ],
                    [ 'date' => 'March 19, 2026', 'total' => 12, 'present' => 12, 'leave' => 0, 'absent' => 0 ],
                    [ 'date' => 'March 18, 2026', 'total' => 12, 'present' => 11, 'leave' => 0, 'absent' => 1 ],
                    [ 'date' => 'March 17, 2026', 'total' => 12, 'present' => 10, 'leave' => 2, 'absent' => 0 ],
                ];
                foreach ( $dates as $row ) :
                ?>
                <tr>
                    <td><?php echo esc_html( $row['date'] ); ?></td>
                    <td><?php echo esc_html( $row['total'] ); ?></td>
                    <td><span style="color: #166534;"><?php echo esc_html( $row['present'] ); ?></span></td>
                    <td><span style="color: #1e40af;"><?php echo esc_html( $row['leave'] ); ?></span></td>
                    <td><span style="color: <?php echo $row['absent'] > 0 ? '#991b1b' : '#374151'; ?>;"><?php echo esc_html( $row['absent'] ); ?></span></td>
                    <td>&mdash;</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">9 items</span>
            </div>
        </div>
    </div>
</div>
