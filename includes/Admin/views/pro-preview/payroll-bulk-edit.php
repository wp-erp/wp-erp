<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="erp-pro-preview-banner">
    <span class="dashicons dashicons-lock"></span>
    <?php esc_html_e( 'This is a preview of Bulk Pay Item Edit.', 'erp' ); ?>
    <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-preview&utm_content=payroll-bulk-edit" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'erp' ); ?></a>
</div>

<div class="erp-pro-preview-wrapper">
    <div class="wrap">
        <h2><?php esc_html_e( 'Bulk Pay Item Edit', 'erp' ); ?></h2>

        <!-- Filter Section -->
        <div class="postbox" style="margin-top: 15px;">
            <div class="inside" style="padding: 15px;">
                <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;"><?php esc_html_e( 'Pay Item', 'erp' ); ?></label>
                        <select disabled style="min-width: 160px;">
                            <option><?php esc_html_e( 'Basic Salary', 'erp' ); ?></option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;"><?php esc_html_e( 'Department', 'erp' ); ?></label>
                        <select disabled style="min-width: 160px;">
                            <option><?php esc_html_e( 'All Departments', 'erp' ); ?></option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;"><?php esc_html_e( 'Designation', 'erp' ); ?></label>
                        <select disabled style="min-width: 160px;">
                            <option><?php esc_html_e( 'All Designations', 'erp' ); ?></option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;"><?php esc_html_e( 'Employee Name', 'erp' ); ?></label>
                        <input type="text" disabled placeholder="<?php esc_attr_e( 'Search employee...', 'erp' ); ?>" style="min-width: 160px;" />
                    </div>
                    <button class="button button-primary erp-pro-preview-action"><?php esc_html_e( 'Search', 'erp' ); ?></button>
                </div>
            </div>
        </div>

        <!-- Payment Type Section -->
        <div class="postbox" style="margin-top: 15px;">
            <div class="inside" style="padding: 15px;">
                <div style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;"><?php esc_html_e( 'Set Payment Type', 'erp' ); ?></label>
                        <select disabled style="min-width: 200px;">
                            <option><?php esc_html_e( 'Fixed Payment', 'erp' ); ?></option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 13px;"><?php esc_html_e( 'Fixed Value', 'erp' ); ?></label>
                        <input type="number" disabled value="3000" style="width: 120px;" />
                    </div>
                    <button class="button erp-pro-preview-action"><?php esc_html_e( 'Set', 'erp' ); ?></button>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <table class="widefat striped fixed" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th style="width: 5%;"><?php esc_html_e( 'SL', 'erp' ); ?></th>
                    <th style="width: 22%;"><?php esc_html_e( 'Employee', 'erp' ); ?></th>
                    <th style="width: 18%;"><?php esc_html_e( 'Department', 'erp' ); ?></th>
                    <th style="width: 18%;"><?php esc_html_e( 'Designation', 'erp' ); ?></th>
                    <th style="width: 17%;"><?php esc_html_e( 'Total Payment', 'erp' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $employees = [
                    [ 'name' => 'John Smith',     'dept' => 'Engineering',  'desig' => 'Sr. Developer',   'amount' => '3,500.00' ],
                    [ 'name' => 'Sarah Johnson',  'dept' => 'Marketing',   'desig' => 'Marketing Lead',  'amount' => '3,200.00' ],
                    [ 'name' => 'Mike Davis',     'dept' => 'Engineering',  'desig' => 'Developer',       'amount' => '3,000.00' ],
                    [ 'name' => 'Emily Chen',     'dept' => 'Design',      'desig' => 'UI Designer',     'amount' => '2,800.00' ],
                    [ 'name' => 'Alex Turner',    'dept' => 'Engineering',  'desig' => 'Jr. Developer',   'amount' => '2,500.00' ],
                    [ 'name' => 'Rachel Kim',     'dept' => 'HR',          'desig' => 'HR Executive',    'amount' => '2,600.00' ],
                    [ 'name' => 'David Park',     'dept' => 'Engineering',  'desig' => 'QA Engineer',     'amount' => '2,700.00' ],
                    [ 'name' => 'Lisa Wang',      'dept' => 'Marketing',   'desig' => 'Content Writer',  'amount' => '2,400.00' ],
                ];
                $sl = 1;
                foreach ( $employees as $emp ) :
                ?>
                <tr>
                    <td><?php echo esc_html( $sl++ ); ?></td>
                    <td><strong><?php echo esc_html( $emp['name'] ); ?></strong></td>
                    <td><?php echo esc_html( $emp['dept'] ); ?></td>
                    <td><?php echo esc_html( $emp['desig'] ); ?></td>
                    <td><input type="text" disabled value="$<?php echo esc_attr( $emp['amount'] ); ?>" style="width: 120px; text-align: right;" /></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align: right;"><strong><?php esc_html_e( 'Total', 'erp' ); ?></strong></th>
                    <th><strong>$22,700.00</strong></th>
                </tr>
            </tfoot>
        </table>

        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-top: 15px;">
            <span class="erp-pro-save-notice">
                <span class="dashicons dashicons-lock"></span>
                <?php esc_html_e( 'Upgrade to Pro to save', 'erp' ); ?>
                &mdash;
                <a href="https://wperp.com/pricing/?utm_source=wp-admin&utm_medium=pro-form&utm_content=payroll-bulk-edit" target="_blank"><?php esc_html_e( 'Get Pro', 'erp' ); ?></a>
            </span>
            <button type="button" class="button button-primary" disabled><?php esc_html_e( 'Update', 'erp' ); ?></button>
        </div>
    </div>
</div>
