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
        <h2><?php esc_html_e( 'Pay Run List', 'erp' ); ?></h2>

        <div class="tablenav top" style="margin-bottom: 10px;">
            <select disabled style="min-width: 150px;">
                <option><?php esc_html_e( 'All Statuses', 'erp' ); ?></option>
                <option><?php esc_html_e( 'Approved', 'erp' ); ?></option>
                <option><?php esc_html_e( 'Not Approved', 'erp' ); ?></option>
            </select>
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
                        <a href="#" class="erp-pro-preview-action" title="<?php esc_attr_e( 'Edit', 'erp' ); ?>"><span class="dashicons dashicons-edit" style="font-size: 16px; width: 16px; height: 16px; color: #0073aa;"></span></a>
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
